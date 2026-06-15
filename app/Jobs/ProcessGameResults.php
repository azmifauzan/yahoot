<?php

namespace App\Jobs;

use App\Models\GameSession;
use App\Services\AchievementService;
use App\Services\RankingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessGameResults implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $gameSessionId) {}

    /**
     * Accumulate global ranking stats and award achievement badges for every
     * registered player of a finished game session.
     */
    public function handle(RankingService $rankingService, AchievementService $achievementService): void
    {
        $gameSession = GameSession::query()->with('players')->find($this->gameSessionId);

        if (! $gameSession) {
            return;
        }

        $rankingService->recordGameResult($gameSession);

        $gameSession->players
            ->whereNotNull('user_id')
            ->each(function ($player) use ($achievementService): void {
                if ($player->user) {
                    $achievementService->evaluate($player->user->fresh());
                }
            });
    }
}
