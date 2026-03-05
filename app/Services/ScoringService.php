<?php

namespace App\Services;

use App\Models\GamePlayer;
use App\Models\Question;

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
     * @return \Illuminate\Support\Collection<int, array{rank: int, player_id: int, nickname: string, avatar: string, score: int}>
     */
    public function getLeaderboard(int $gameSessionId, int $limit = 0): \Illuminate\Support\Collection
    {
        $query = GamePlayer::query()
            ->where('game_session_id', $gameSessionId)
            ->orderByDesc('score')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get()->values()->map(function (GamePlayer $player, int $index) {
            return [
                'rank' => $index + 1,
                'player_id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar,
                'score' => $player->score,
            ];
        });
    }
}
