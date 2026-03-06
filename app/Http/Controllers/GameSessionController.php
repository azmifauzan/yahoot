<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Events\GameEnded;
use App\Events\GameStarted;
use App\Events\QuestionStarted;
use App\Jobs\AutoRevealAnswer;
use App\Models\GameSession;
use App\Models\Quiz;
use App\Services\GameCodeService;
use App\Services\RevealService;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameSessionController extends Controller
{
    public function __construct(
        private GameCodeService $gameCodeService,
        private ScoringService $scoringService,
        private RevealService $revealService
    ) {}

    /**
     * Create a new game session for a quiz and redirect to the host lobby.
     */
    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        if ($quiz->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $quiz->is_published) {
            return back()->withErrors(['quiz' => 'Quiz must be published before starting a game.']);
        }

        if ($quiz->questions()->count() === 0) {
            return back()->withErrors(['quiz' => 'Quiz must have at least one question.']);
        }

        $session = GameSession::query()->create([
            'quiz_id' => $quiz->id,
            'host_id' => $request->user()->id,
            'game_code' => $this->gameCodeService->generate(),
            'status' => GameStatus::Waiting,
            'current_question_index' => 0,
        ]);

        return redirect()->route('game.host', $session);
    }

    /**
     * Show the host view for a game session.
     */
    public function host(GameSession $gameSession): Response
    {
        if ($gameSession->host_id !== auth()->id()) {
            abort(403);
        }

        $gameSession->load(['quiz.questions.answers', 'players']);

        $theme = $gameSession->quiz->theme ?? \App\Enums\QuizTheme::Standard;

        return Inertia::render('Host/Game', [
            'gameSession' => $gameSession,
            'quiz' => $gameSession->quiz,
            'questions' => $gameSession->quiz->questions,
            'players' => $gameSession->players,
            'theme' => [
                'value' => $theme->value,
                'gradients' => $theme->gradients(),
            ],
        ]);
    }

    /**
     * Start the game.
     */
    public function start(GameSession $gameSession): RedirectResponse
    {
        if ($gameSession->host_id !== auth()->id()) {
            abort(403);
        }

        if ($gameSession->status !== GameStatus::Waiting) {
            return back()->withErrors(['game' => 'Game has already started.']);
        }

        if ($gameSession->players()->count() === 0) {
            return back()->withErrors(['game' => 'At least one player is required to start.']);
        }

        $gameSession->update([
            'status' => GameStatus::Playing,
            'started_at' => now(),
            'current_question_index' => 0,
        ]);

        $totalQuestions = $gameSession->quiz->questions()->count();

        broadcast(new GameStarted(
            $gameSession->id,
            $totalQuestions
        ));

        // Automatically start the first question
        $this->broadcastCurrentQuestion($gameSession);

        return back();
    }

    /**
     * Move to the next question or end the game.
     */
    public function next(GameSession $gameSession): RedirectResponse
    {
        if ($gameSession->host_id !== auth()->id()) {
            abort(403);
        }

        if ($gameSession->status !== GameStatus::Playing && $gameSession->status !== GameStatus::Reviewing) {
            return back()->withErrors(['game' => 'Game is not in progress.']);
        }

        $questions = $gameSession->quiz->questions()->orderBy('order')->get();
        $nextIndex = $gameSession->current_question_index + 1;

        if ($nextIndex >= $questions->count()) {
            return $this->end($gameSession);
        }

        $gameSession->update([
            'current_question_index' => $nextIndex,
            'status' => GameStatus::Playing,
            'question_started_at' => now(),
        ]);

        $this->broadcastCurrentQuestion($gameSession->fresh());

        return back();
    }

    /**
     * Reveal the correct answer for the current question.
     */
    public function reveal(GameSession $gameSession): RedirectResponse
    {
        if ($gameSession->host_id !== auth()->id()) {
            abort(403);
        }

        $this->revealService->reveal($gameSession);

        return back();
    }

    /**
     * End the game.
     */
    public function end(GameSession $gameSession): RedirectResponse
    {
        if ($gameSession->host_id !== auth()->id()) {
            abort(403);
        }

        $gameSession->update([
            'status' => GameStatus::Finished,
            'finished_at' => now(),
        ]);

        $gameSession->players()->update(['finished_at' => now()]);

        $finalLeaderboard = $this->scoringService->getLeaderboard($gameSession->id);
        $podium = $finalLeaderboard->take(3)->values()->toArray();

        broadcast(new GameEnded(
            $gameSession->id,
            $finalLeaderboard,
            $podium
        ));

        return redirect()->route('game.results', $gameSession);
    }

    /**
     * Show game results.
     */
    public function results(GameSession $gameSession): Response
    {
        if ($gameSession->host_id !== auth()->id()) {
            abort(403);
        }

        $gameSession->load(['quiz', 'players.playerAnswers']);

        $leaderboard = $this->scoringService->getLeaderboard($gameSession->id);

        // Calculate stats per player
        $playerStats = $gameSession->players->map(function ($player) use ($gameSession) {
            $answers = $player->playerAnswers;
            $totalQuestions = $gameSession->quiz->questions()->count();

            return [
                'player_id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar,
                'score' => $player->score,
                'correct_answers' => $answers->where('is_correct', true)->count(),
                'total_questions' => $totalQuestions,
                'avg_time' => $answers->where('answer_id', '!=', null)->avg('time_taken') ?? 0,
            ];
        });

        return Inertia::render('Host/Results', [
            'gameSession' => $gameSession,
            'quiz' => $gameSession->quiz,
            'leaderboard' => $leaderboard,
            'playerStats' => $playerStats,
        ]);
    }

    /**
     * Export game results as CSV.
     */
    public function export(GameSession $gameSession): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if ($gameSession->host_id !== auth()->id()) {
            abort(403);
        }

        $gameSession->load(['quiz', 'players.playerAnswers']);

        $headers = ['Rank', 'Nickname', 'Score', 'Correct Answers', 'Total Questions', 'Avg Time (ms)'];

        $leaderboard = $this->scoringService->getLeaderboard($gameSession->id);
        $totalQuestions = $gameSession->quiz->questions()->count();

        return response()->streamDownload(function () use ($leaderboard, $headers, $gameSession, $totalQuestions): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($leaderboard as $entry) {
                $player = $gameSession->players->firstWhere('id', $entry['player_id']);
                $answers = $player?->playerAnswers ?? collect();

                fputcsv($file, [
                    $entry['rank'],
                    $entry['nickname'],
                    $entry['score'],
                    $answers->where('is_correct', true)->count(),
                    $totalQuestions,
                    round($answers->where('answer_id', '!=', null)->avg('time_taken') ?? 0),
                ]);
            }

            fclose($file);
        }, "yahoot-results-{$gameSession->game_code}.csv");
    }

    /**
     * Broadcast the current question to all players.
     */
    private function broadcastCurrentQuestion(GameSession $gameSession): void
    {
        $questions = $gameSession->quiz->questions()->orderBy('order')->get();
        $currentQuestion = $questions[$gameSession->current_question_index] ?? null;

        if (! $currentQuestion) {
            return;
        }

        $currentQuestion->load('answers');

        $gameSession->update(['question_started_at' => now()]);

        broadcast(new QuestionStarted(
            $gameSession->id,
            $currentQuestion,
            $gameSession->current_question_index + 1,
            $questions->count(),
            $currentQuestion->time_limit
        ));

        // Dispatch auto-reveal job after time limit expires (+ 3s countdown + 2s buffer)
        AutoRevealAnswer::dispatch($gameSession->id, $gameSession->current_question_index)
            ->delay(now()->addSeconds($currentQuestion->time_limit + 5));
    }
}
