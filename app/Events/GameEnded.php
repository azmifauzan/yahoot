<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $finalLeaderboard
     * @param  array<int, array<string, mixed>>  $podium
     */
    public function __construct(
        public int $gameSessionId,
        public mixed $finalLeaderboard,
        public array $podium
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
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
        ];
    }
}
