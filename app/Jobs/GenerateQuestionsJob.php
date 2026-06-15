<?php

namespace App\Jobs;

use App\Events\QuestionsGenerated;
use App\Services\AiQuestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateQuestionsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public int $quizId,
        public string $topic,
        public int $count,
        public string $difficulty,
        public string $type,
        public string $locale
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AiQuestionService $aiQuestionService): void
    {
        try {
            $questions = $aiQuestionService->generate(
                $this->topic,
                $this->count,
                $this->difficulty,
                $this->type,
                $this->locale
            );

            broadcast(new QuestionsGenerated(
                $this->userId,
                $this->quizId,
                $questions,
                true
            ));
        } catch (\Throwable $e) {
            Log::error('Error generating questions via AI: '.$e->getMessage());

            broadcast(new QuestionsGenerated(
                $this->userId,
                $this->quizId,
                [],
                false,
                $e->getMessage()
            ));
        }
    }
}
