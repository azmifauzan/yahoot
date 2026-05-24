<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GameEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  Collection<int, array<string, mixed>>  $finalLeaderboard
     * @param  array<int, array<string, mixed>>  $podium
     * @param  Collection<int, array<string, mixed>>  $playerStats
     */
    public function __construct(
        public int $gameSessionId,
        public mixed $finalLeaderboard,
        public array $podium,
        public mixed $playerStats = null
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('game.'.$this->gameSessionId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'finalLeaderboard' => $this->finalLeaderboard,
            'podium' => $this->podium,
            'playerStats' => $this->playerStats,
        ];
    }
}
