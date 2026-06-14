<?php

namespace App\Services;

use App\Models\GamePlayer;
use App\Models\PlayerAnswer;
use App\Models\Question;
use Illuminate\Support\Collection;

class ScoringService
{
    /**
     * Calculate points for a player's answer.
     *
     * @return array{points_earned: int, streak_bonus: int, new_streak: int}
     */
    public function calculate(
        Question $question,
        GamePlayer $player,
        bool $isCorrect,
        int $timeTakenMs
    ): array {
        if (! $question->type->isScored()) {
            return [
                'points_earned' => 0,
                'streak_bonus' => 0,
                'new_streak' => $player->streak,
            ];
        }

        if (! $isCorrect) {
            return [
                'points_earned' => 0,
                'streak_bonus' => 0,
                'new_streak' => 0,
            ];
        }

        $basePoints = $question->points->basePoints();

        if ($basePoints === 0) {
            return [
                'points_earned' => 0,
                'streak_bonus' => 0,
                'new_streak' => $player->streak + 1,
            ];
        }

        $timeLimitMs = $question->time_limit * 1000;
        $timeBonusFactor = 1 - ($timeTakenMs / $timeLimitMs / 2);
        $timeBonusFactor = max(0.5, min(1.0, $timeBonusFactor));

        $newStreak = $player->streak + 1;
        $streakBonus = min($newStreak * 100, 500);

        $pointsEarned = (int) floor($basePoints * $timeBonusFactor);

        return [
            'points_earned' => $pointsEarned,
            'streak_bonus' => $streakBonus,
            'new_streak' => $newStreak,
        ];
    }

    /**
     * Get the leaderboard for a game session.
     *
     * @return Collection<int, array{rank: int, player_id: int, nickname: string, avatar: string, score: int, correct_count: int, best_streak: int}>
     */
    public function getLeaderboard(int $gameSessionId, int $limit = 0): Collection
    {
        $query = GamePlayer::query()
            ->with('playerAnswers')
            ->where('game_session_id', $gameSessionId)
            ->orderByDesc('score')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get()->values()->map(function (GamePlayer $player, int $index) {
            $answers = $player->playerAnswers;
            $correctCount = $answers->where('is_correct', true)->count();
            $bestStreak = $this->computeBestStreak($answers);

            return [
                'rank' => $index + 1,
                'player_id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar,
                'score' => $player->score,
                'correct_count' => $correctCount,
                'best_streak' => $bestStreak,
                'avg_time' => (float) ($answers->whereNotNull('answer_id')->avg('time_taken') ?? 0),
            ];
        });
    }

    /**
     * Get per-player stats for a game session (keyed by player_id).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getPlayerStats(int $gameSessionId): Collection
    {
        return GamePlayer::query()
            ->with('playerAnswers')
            ->where('game_session_id', $gameSessionId)
            ->get()
            ->map(function (GamePlayer $player) {
                $answers = $player->playerAnswers;

                return [
                    'player_id' => $player->id,
                    'correct_answers' => $answers->where('is_correct', true)->count(),
                    'total_answers' => $answers->count(),
                    'longest_streak' => $this->computeBestStreak($answers),
                    'avg_time' => (float) ($answers->whereNotNull('answer_id')->avg('time_taken') ?? 0),
                ];
            })
            ->keyBy('player_id');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, PlayerAnswer>  $answers
     */
    private function computeBestStreak(\Illuminate\Database\Eloquent\Collection $answers): int
    {
        $best = 0;
        $current = 0;

        foreach ($answers as $answer) {
            if ($answer->is_correct) {
                $current++;
                $best = max($best, $current);
            } else {
                $current = 0;
            }
        }

        return $best;
    }
}
