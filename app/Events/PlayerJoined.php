<?php

namespace App\Events;

use App\Models\GamePlayer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $gameSessionId,
        public GamePlayer $player,
        public int $totalPlayers
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
            'player' => [
                'id' => $this->player->id,
                'nickname' => $this->player->nickname,
                'avatar' => $this->player->avatar,
                'team' => $this->player->team ? [
                    'id' => $this->player->team->id,
                    'name' => $this->player->team->name,
                    'color' => $this->player->team->color,
                ] : null,
            ],
            'totalPlayers' => $this->totalPlayers,
        ];
    }
}
