<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Enums\QuestionType;
use App\Events\AnswerRevealed;
use App\Events\ScoreboardUpdated;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\Question;
use Illuminate\Support\Collection;

class RevealService
{
    public function __construct(
        private ScoringService $scoringService
    ) {}

    /**
     * Reveal the answer for the current question of the given game session.
     *
     * Returns false if the session is not in a revealable state.
     */
    public function reveal(GameSession $gameSession): bool
    {
        if ($gameSession->status !== GameStatus::Playing) {
            return false;
        }

        $questions = $gameSession->quiz->questions()->orderBy('order')->get();
        $currentQuestion = $questions[$gameSession->current_question_index] ?? null;

        if (! $currentQuestion) {
            return false;
        }

        $currentQuestion->load('answers');

        $data = $this->getRevealData($gameSession, $currentQuestion);

        $gameSession->update(['status' => GameStatus::Reviewing]);

        broadcast(new AnswerRevealed(
            $gameSession->id,
            $data['correctAnswer'],
            $data['stats'],
            $data['playerResults'],
            $data['isPoll']
        ));

        $teams = ($gameSession->settings['mode'] ?? 'individual') === 'team'
            ? $this->scoringService->getTeamLeaderboard($gameSession->id)
            : null;

        broadcast(new ScoreboardUpdated(
            $gameSession->id,
            $data['leaderboard'],
            $data['playerPositions'],
            $teams
        ));

        return true;
    }

    /**
     * Compute the reveal data (correct answer, stats, player results, leaderboard)
     * for the given question without broadcasting or mutating state.
     *
     * @return array{correctAnswer: array, stats: array, playerResults: array, leaderboard: Collection, playerPositions: array, isPoll: bool}
     */
    public function getRevealData(GameSession $gameSession, Question $currentQuestion): array
    {
        $isPoll = $currentQuestion->type === QuestionType::Poll;

        $correctAnswers = $currentQuestion->answers->where('is_correct', true);
        $correctAnswerData = $isPoll ? [] : $correctAnswers->map(fn ($a) => [
            'id' => $a->id,
            'answer_text' => $a->answer_text,
            'color' => $a->color->value,
        ])->values()->toArray();

        // Get answer stats
        $answerStats = [];
        foreach ($currentQuestion->answers as $answer) {
            $count = PlayerAnswer::query()
                ->whereIn('game_player_id', $gameSession->players()->pluck('id'))
                ->where('question_id', $currentQuestion->id)
                ->where('answer_id', $answer->id)
                ->count();

            $answerStats[] = [
                'answer_id' => $answer->id,
                'color' => $answer->color->value,
                'count' => $count,
            ];
        }

        $noAnswerCount = $gameSession->players()->count() - PlayerAnswer::query()
            ->whereIn('game_player_id', $gameSession->players()->pluck('id'))
            ->where('question_id', $currentQuestion->id)
            ->whereNotNull('answer_id')
            ->count();

        // Per-player results
        $playerResults = [];
        foreach ($gameSession->players as $player) {
            $playerAnswer = PlayerAnswer::query()
                ->where('game_player_id', $player->id)
                ->where('question_id', $currentQuestion->id)
                ->first();

            $playerResults[] = [
                'player_id' => $player->id,
                'is_correct' => $playerAnswer?->is_correct ?? false,
                'points_earned' => $playerAnswer?->points_earned ?? 0,
                'streak_bonus' => $playerAnswer?->streak_bonus ?? 0,
                'answer_id' => $playerAnswer?->answer_id,
            ];
        }

        // Build scoreboard
        $leaderboard = $this->scoringService->getLeaderboard($gameSession->id, 5);
        $allPositions = $this->scoringService->getLeaderboard($gameSession->id);

        $playerPositions = [];
        foreach ($allPositions as $entry) {
            $playerPositions[$entry['player_id']] = [
                'rank' => $entry['rank'],
                'score' => $entry['score'],
            ];
        }

        return [
            'correctAnswer' => ['answers' => $correctAnswerData],
            'stats' => ['answer_counts' => $answerStats, 'no_answer_count' => $noAnswerCount],
            'playerResults' => $playerResults,
            'leaderboard' => $leaderboard,
            'playerPositions' => $playerPositions,
            'isPoll' => $isPoll,
        ];
    }
}
