<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnswerRevealed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $correctAnswer
     * @param  array<string, mixed>  $stats
     * @param  array<int, array<string, mixed>>  $playerResults
     */
    public function __construct(
        public int $gameSessionId,
        public array $correctAnswer,
        public array $stats,
        public array $playerResults
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
            'correctAnswer' => $this->correctAnswer,
            'stats' => $this->stats,
            'playerResults' => $this->playerResults,
        ];
    }
}
