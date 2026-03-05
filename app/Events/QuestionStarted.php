<?php

namespace App\Events;

use App\Models\Question;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $gameSessionId,
        public Question $question,
        public int $questionNumber,
        public int $totalQuestions,
        public int $timeLimit
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
            'question' => [
                'id' => $this->question->id,
                'question_text' => $this->question->question_text,
                'type' => $this->question->type->value,
                'image_url' => $this->question->image_url,
                'answers' => $this->question->answers->map(fn ($a) => [
                    'id' => $a->id,
                    'answer_text' => $a->answer_text,
                    'color' => $a->color->value,
                    'shape' => $a->shape,
                    'order' => $a->order,
                ]),
            ],
            'questionNumber' => $this->questionNumber,
            'totalQuestions' => $this->totalQuestions,
            'timeLimit' => $this->timeLimit,
        ];
    }
}
