<?php

namespace App\Services;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Collection;

class RankingService
{
    public function __construct(private ScoringService $scoringService) {}

    /**
     * Accumulate global ranking stats for every registered player of a
     * finished game session. Guests (no user_id) are ignored.
     */
    public function recordGameResult(GameSession $gameSession): void
    {
        $leaderboard = $this->scoringService->getLeaderboard($gameSession->id);

        if ($leaderboard->isEmpty()) {
            return;
        }

        $winnerPlayerId = $leaderboard->first()['player_id'];

        $players = $gameSession->players()
            ->whereNotNull('user_id')
            ->get();

        foreach ($players as $player) {
            $user = User::query()->find($player->user_id);

            if (! $user) {
                continue;
            }

            $entry = $leaderboard->firstWhere('player_id', $player->id);

            $user->increment('total_xp', (int) ($entry['score'] ?? $player->score));
            $user->increment('games_played');

            if ($player->id === $winnerPlayerId) {
                $user->increment('games_won');
            }
        }
    }

    /**
     * Top ranked players by lifetime XP.
     *
     * @return Collection<int, array{rank: int, user_id: int, name: string, avatar: string|null, total_xp: int, games_played: int, games_won: int}>
     */
    public function topPlayers(int $limit = 50): Collection
    {
        return User::query()
            ->where('games_played', '>', 0)
            ->orderByDesc('total_xp')
            ->orderByDesc('games_won')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->values()
            ->map(fn (User $user, int $index): array => [
                'rank' => $index + 1,
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->profile_photo_url,
                'total_xp' => $user->total_xp,
                'games_played' => $user->games_played,
                'games_won' => $user->games_won,
            ]);
    }

    /**
     * The 1-based global rank of a user (by XP). Null if they haven't played.
     */
    public function rankFor(User $user): ?int
    {
        if ($user->games_played === 0) {
            return null;
        }

        return User::query()
            ->where('games_played', '>', 0)
            ->where('total_xp', '>', $user->total_xp)
            ->count() + 1;
    }
}
