<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Events\AnswerRevealed;
use App\Events\ScoreboardUpdated;
use App\Models\GameSession;
use App\Models\PlayerAnswer;

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

        $correctAnswers = $currentQuestion->answers->where('is_correct', true);
        $correctAnswerData = $correctAnswers->map(fn ($a) => [
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

        $gameSession->update(['status' => GameStatus::Reviewing]);

        broadcast(new AnswerRevealed(
            $gameSession->id,
            ['answers' => $correctAnswerData],
            ['answer_counts' => $answerStats, 'no_answer_count' => $noAnswerCount],
            $playerResults
        ));

        // Build and broadcast scoreboard
        $leaderboard = $this->scoringService->getLeaderboard($gameSession->id, 5);
        $allPositions = $this->scoringService->getLeaderboard($gameSession->id);

        $playerPositions = [];
        foreach ($allPositions as $entry) {
            $playerPositions[$entry['player_id']] = [
                'rank' => $entry['rank'],
                'score' => $entry['score'],
            ];
        }

        broadcast(new ScoreboardUpdated(
            $gameSession->id,
            $leaderboard,
            $playerPositions
        ));

        return true;
    }
}
