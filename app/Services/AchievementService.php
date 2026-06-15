<?php

namespace App\Services;

use App\Models\PlayerAnswer;
use App\Models\User;
use App\Models\UserBadge;

class AchievementService
{
    /**
     * Evaluate every badge against the user's stats and award the ones whose
     * criteria are met and not yet earned.
     *
     * @return array<int, string> The badge keys newly awarded.
     */
    public function evaluate(User $user): array
    {
        $stats = $this->statsFor($user);
        $alreadyEarned = $user->badges()->pluck('badge_key')->all();

        /** @var array<string, array{icon: string, criteria: array<string, int>}> $badges */
        $badges = config('badges', []);

        $newlyEarned = [];

        foreach ($badges as $key => $badge) {
            if (in_array($key, $alreadyEarned, true)) {
                continue;
            }

            if (! $this->meetsCriteria($badge['criteria'] ?? [], $stats)) {
                continue;
            }

            UserBadge::query()->firstOrCreate(
                ['user_id' => $user->id, 'badge_key' => $key],
                ['earned_at' => now()],
            );

            $newlyEarned[] = $key;
        }

        return $newlyEarned;
    }

    /**
     * Aggregate the stats used to evaluate badge criteria.
     *
     * @return array{games_played: int, games_won: int, total_correct: int, best_streak: int, total_xp: int}
     */
    public function statsFor(User $user): array
    {
        $totalCorrect = PlayerAnswer::query()
            ->where('is_correct', true)
            ->whereHas('gamePlayer', fn ($query) => $query->where('user_id', $user->id))
            ->count();

        return [
            'games_played' => $user->games_played,
            'games_won' => $user->games_won,
            'total_correct' => $totalCorrect,
            'best_streak' => $this->bestStreak($user),
            'total_xp' => $user->total_xp,
        ];
    }

    /**
     * The user's longest correct-answer streak across all sessions played.
     */
    private function bestStreak(User $user): int
    {
        $best = 0;

        $user->gamePlayers()
            ->with(['playerAnswers' => fn ($query) => $query->orderBy('id')])
            ->get()
            ->each(function ($player) use (&$best): void {
                $current = 0;

                foreach ($player->playerAnswers as $answer) {
                    if ($answer->is_correct) {
                        $current++;
                        $best = max($best, $current);
                    } else {
                        $current = 0;
                    }
                }
            });

        return $best;
    }

    /**
     * @param  array<string, int>  $criteria
     * @param  array<string, int>  $stats
     */
    private function meetsCriteria(array $criteria, array $stats): bool
    {
        foreach ($criteria as $stat => $minimum) {
            if (($stats[$stat] ?? 0) < $minimum) {
                return false;
            }
        }

        return true;
    }
}
