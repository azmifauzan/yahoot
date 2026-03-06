<?php

namespace App\Jobs;

use App\Enums\GameStatus;
use App\Models\GameSession;
use App\Services\RevealService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AutoRevealAnswer implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $gameSessionId,
        public int $questionIndex
    ) {}

    /**
     * Auto-reveal the answer if the question is still active.
     */
    public function handle(RevealService $revealService): void
    {
        $gameSession = GameSession::query()->find($this->gameSessionId);

        if (! $gameSession) {
            return;
        }

        // Only reveal if still on the same question and still playing
        if ($gameSession->status !== GameStatus::Playing) {
            return;
        }

        if ($gameSession->current_question_index !== $this->questionIndex) {
            return;
        }

        $revealService->reveal($gameSession);
    }
}
