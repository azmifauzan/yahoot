<?php

namespace App\Services;

use App\Models\PlayerAnswer;
use App\Models\Quiz;
use Illuminate\Support\Collection;

class QuizAnalyticsService
{
    /**
     * Per-question difficulty insights aggregated across every game session
     * ever played for this quiz.
     *
     * @return Collection<int, array{question_id: int, question_text: string, type: string, order: int, total_attempts: int, correct_count: int, correct_rate: float, avg_time: float, difficulty: string}>
     */
    public function difficulty(Quiz $quiz): Collection
    {
        $questions = $quiz->questions()->get();

        if ($questions->isEmpty()) {
            return collect();
        }

        // Aggregate answered responses per question in a single grouped query.
        $stats = PlayerAnswer::query()
            ->selectRaw('question_id')
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as correct_count')
            ->selectRaw('AVG(time_taken) as avg_time')
            ->whereIn('question_id', $questions->pluck('id'))
            ->whereNotNull('answer_id')
            ->groupBy('question_id')
            ->get()
            ->keyBy('question_id');

        return $questions->map(function ($question) use ($stats): array {
            $row = $stats->get($question->id);
            $attempts = (int) ($row->total_attempts ?? 0);
            $correct = (int) ($row->correct_count ?? 0);
            $correctRate = $attempts > 0 ? $correct / $attempts : 0.0;

            return [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type->value,
                'order' => $question->order,
                'total_attempts' => $attempts,
                'correct_count' => $correct,
                'correct_rate' => round($correctRate, 3),
                'avg_time' => round((float) ($row->avg_time ?? 0), 0),
                'difficulty' => $this->classify($attempts, $correctRate),
            ];
        })->values();
    }

    /**
     * Classify difficulty from the correct rate. Questions with no attempts
     * are "unknown".
     */
    private function classify(int $attempts, float $correctRate): string
    {
        if ($attempts === 0) {
            return 'unknown';
        }

        return match (true) {
            $correctRate >= 0.8 => 'easy',
            $correctRate >= 0.4 => 'medium',
            default => 'hard',
        };
    }
}
