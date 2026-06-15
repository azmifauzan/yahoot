<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Enums\QuizTheme;
use App\Events\GameCancelled;
use App\Events\GameEnded;
use App\Events\GameStarted;
use App\Events\QuestionStarted;
use App\Http\Requests\Game\StartGameSessionRequest;
use App\Jobs\AutoRevealAnswer;
use App\Jobs\ProcessGameResults;
use App\Models\GameSession;
use App\Models\GameTeam;
use App\Models\PlayerAnswer;
use App\Models\Quiz;
use App\Services\GameCodeService;
use App\Services\RevealService;
use App\Services\ScoringService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function store(StartGameSessionRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        if (! $quiz->is_published) {
            return back()->withErrors(['quiz' => 'Quiz must be published before starting a game.']);
        }

        if ($quiz->questions()->count() === 0) {
            return back()->withErrors(['quiz' => 'Quiz must have at least one question.']);
        }

        $validated = $request->validated();
        $mode = $validated['mode'] ?? 'individual';
        $teamCount = (int) ($validated['team_count'] ?? 2);

        $session = GameSession::query()->create([
            'quiz_id' => $quiz->id,
            'host_id' => $request->user()->id,
            'game_code' => $this->gameCodeService->generate(),
            'status' => GameStatus::Waiting,
            'current_question_index' => 0,
            'settings' => $mode === 'team'
                ? ['mode' => 'team', 'team_count' => $teamCount]
                : ['mode' => 'individual'],
        ]);

        if ($mode === 'team') {
            foreach (array_slice(GameTeam::PALETTE, 0, $teamCount) as $palette) {
                $session->teams()->create([
                    'name' => $palette['name'],
                    'color' => $palette['color'],
                    'score' => 0,
                ]);
            }
        }

        return redirect()->route('game.host', $session);
    }

    /**
     * Show the host view for a game session.
     */
    public function host(GameSession $gameSession): Response
    {
        $this->authorize('view', $gameSession);

        $gameSession->load(['quiz.questions.answers', 'players' => function ($query) {
            $query->where('is_connected', true);
        }, 'teams']);

        $theme = $gameSession->quiz->theme ?? QuizTheme::Standard;

        return Inertia::render('Host/Game', [
            'gameSession' => $gameSession,
            'quiz' => $gameSession->quiz,
            'questions' => $gameSession->quiz->questions,
            'players' => $gameSession->players,
            'mode' => $gameSession->settings['mode'] ?? 'individual',
            'teams' => $gameSession->teams,
            'theme' => [
                'value' => $theme->value,
                'gradients' => $theme->gradients(),
            ],
            'resumeState' => $this->buildResumeState($gameSession),
        ]);
    }

    /**
     * Build the current question/reveal state for a host re-opening an in-progress session.
     *
     * @return array<string, mixed>|null
     */
    private function buildResumeState(GameSession $gameSession): ?array
    {
        if (! in_array($gameSession->status, [GameStatus::Playing, GameStatus::Reviewing], true)) {
            return null;
        }

        $questions = $gameSession->quiz->questions()->orderBy('order')->get();
        $currentQuestion = $questions[$gameSession->current_question_index] ?? null;

        if (! $currentQuestion) {
            return null;
        }

        $currentQuestion->load('answers');

        $answeredCount = PlayerAnswer::query()
            ->whereIn('game_player_id', $gameSession->players()->pluck('id'))
            ->where('question_id', $currentQuestion->id)
            ->count();

        $state = [
            'status' => $gameSession->status->value,
            'question' => $currentQuestion,
            'questionNumber' => $gameSession->current_question_index + 1,
            'totalQuestions' => $questions->count(),
            'timeLimit' => $currentQuestion->time_limit,
            'answeredCount' => $answeredCount,
            'totalPlayers' => $gameSession->players()->where('is_connected', true)->count(),
            'elapsedSeconds' => $gameSession->question_started_at
                ? now()->diffInSeconds($gameSession->question_started_at)
                : 0,
        ];

        if ($gameSession->status === GameStatus::Reviewing) {
            $state['reveal'] = $this->revealService->getRevealData($gameSession, $currentQuestion);
        }

        return $state;
    }

    /**
     * Start the game.
     */
    public function start(GameSession $gameSession): RedirectResponse
    {
        $this->authorize('start', $gameSession);

        if ($gameSession->status !== GameStatus::Waiting) {
            return back()->withErrors(['game' => 'Game has already started.']);
        }

        if ($gameSession->players()->where('is_connected', true)->count() === 0) {
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
        $this->authorize('next', $gameSession);

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
        $this->authorize('reveal', $gameSession);

        $this->revealService->reveal($gameSession);

        return back();
    }

    /**
     * End the game.
     */
    public function end(GameSession $gameSession): RedirectResponse
    {
        $this->authorize('end', $gameSession);

        // Guard against ending (and re-accumulating ranking/achievements) twice.
        if ($gameSession->status === GameStatus::Finished) {
            return redirect()->route('game.results', $gameSession);
        }

        $gameSession->update([
            'status' => GameStatus::Finished,
            'finished_at' => now(),
        ]);

        $gameSession->players()->update(['finished_at' => now()]);

        $finalLeaderboard = $this->scoringService->getLeaderboard($gameSession->id);
        $podium = $finalLeaderboard->take(3)->values()->toArray();
        $playerStats = $this->scoringService->getPlayerStats($gameSession->id);

        $teams = ($gameSession->settings['mode'] ?? 'individual') === 'team'
            ? $this->scoringService->getTeamLeaderboard($gameSession->id)
            : null;

        broadcast(new GameEnded(
            $gameSession->id,
            $finalLeaderboard,
            $podium,
            $playerStats,
            $teams
        ));

        // Accumulate lifetime XP/ranking + award achievement badges (heavy → queued).
        ProcessGameResults::dispatch($gameSession->id);

        return redirect()->route('game.results', $gameSession);
    }

    /**
     * Cancel the game (delete session and notify players).
     */
    public function cancel(GameSession $gameSession): RedirectResponse
    {
        $this->authorize('cancel', $gameSession);

        broadcast(new GameCancelled($gameSession->id));

        $gameSession->delete();

        return redirect()->route('dashboard');
    }

    /**
     * Show game results.
     */
    public function results(GameSession $gameSession): Response
    {
        $this->authorize('results', $gameSession);

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
     * Show game session detailed statistics inside the user panel (with AppLayout).
     */
    public function stats(GameSession $gameSession): Response
    {
        $this->authorize('results', $gameSession);

        $gameSession->load(['quiz.questions.answers', 'players.playerAnswers']);

        $leaderboard = $this->scoringService->getLeaderboard($gameSession->id);
        $totalQuestions = $gameSession->quiz->questions()->count();

        // Calculate stats per player
        $questions = $gameSession->quiz->questions->map(function ($question) use ($gameSession) {
            $answers = PlayerAnswer::query()
                ->where('question_id', $question->id)
                ->whereIn('game_player_id', $gameSession->players->pluck('id'))
                ->get();

            $correctCount = $answers->where('is_correct', true)->count();
            $incorrectCount = $answers->where('is_correct', false)->whereNotNull('answer_id')->count();
            $noAnswerCount = $gameSession->players->count() - $answers->whereNotNull('answer_id')->count();

            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type->value,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'no_answer_count' => $noAnswerCount,
            ];
        });

        return Inertia::render('Host/Stats', [
            'gameSession' => [
                'id' => $gameSession->id,
                'game_code' => $gameSession->game_code,
                'finished_at' => $gameSession->finished_at,
            ],
            'quiz' => $gameSession->quiz,
            'leaderboard' => $leaderboard,
            'totalQuestions' => $totalQuestions,
            'questions' => $questions,
        ]);
    }

    /**
     * Render a print-friendly report (leaderboard + per-question analysis)
     * the host can save as PDF via the browser's print dialog.
     */
    public function report(GameSession $gameSession): View
    {
        $this->authorize('results', $gameSession);

        $gameSession->load(['quiz.questions', 'players.playerAnswers']);

        $leaderboard = $this->scoringService->getLeaderboard($gameSession->id);
        $totalQuestions = $gameSession->quiz->questions->count();
        $playerCount = $gameSession->players->count();

        $questions = $gameSession->quiz->questions->map(function ($question) use ($gameSession, $playerCount) {
            $answers = $gameSession->players
                ->flatMap->playerAnswers
                ->where('question_id', $question->id);

            $correct = $answers->where('is_correct', true)->count();
            $answered = $answers->whereNotNull('answer_id')->count();

            return [
                'question_text' => $question->question_text,
                'correct_count' => $correct,
                'incorrect_count' => $answered - $correct,
                'no_answer_count' => $playerCount - $answered,
                'correct_pct' => $playerCount > 0 ? round($correct / $playerCount * 100) : 0,
            ];
        });

        return view('reports.game', [
            'gameSession' => $gameSession,
            'quiz' => $gameSession->quiz,
            'leaderboard' => $leaderboard,
            'questions' => $questions,
            'totalQuestions' => $totalQuestions,
            'playerCount' => $playerCount,
        ]);
    }

    /**
     * Show the game session history for a quiz, including resumable sessions.
     */
    public function history(Quiz $quiz): Response
    {
        $this->authorize('view', $quiz);

        $sessions = $quiz->gameSessions()
            ->with('players')
            ->withCount('players')
            ->latest('id')
            ->get()
            ->map(function (GameSession $session) {
                $winner = $session->players->sortByDesc('score')->first();

                return [
                    'id' => $session->id,
                    'game_code' => $session->game_code,
                    'status' => $session->status->value,
                    'players_count' => $session->players_count,
                    'finished_at' => $session->finished_at,
                    'winner' => $winner ? [
                        'nickname' => $winner->nickname,
                        'avatar' => $winner->avatar,
                        'score' => $winner->score,
                    ] : null,
                ];
            });

        return Inertia::render('Host/History', [
            'quiz' => $quiz,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Export game results as CSV.
     */
    public function export(GameSession $gameSession): StreamedResponse
    {
        $this->authorize('export', $gameSession);

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
