<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $leaderboard
     * @param  array<int, array<string, mixed>>  $playerPositions
     */
    public function __construct(
        public int $gameSessionId,
        public mixed $leaderboard,
        public array $playerPositions
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('game.'.$this->gameSessionId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'leaderboard' => $this->leaderboard,
            'playerPositions' => $this->playerPositions,
        ];
    }
}
