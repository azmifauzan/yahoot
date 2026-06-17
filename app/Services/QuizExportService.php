<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;

class QuizExportService
{
    /**
     * Serialise a quiz to the portable exchange array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Quiz $quiz): array
    {
        $quiz->loadMissing('questions.answers');

        return [
            'title' => $quiz->title,
            'description' => $quiz->description,
            'theme' => $quiz->theme->value,
            'questions' => $quiz->questions->map(fn (Question $question) => [
                'type' => $question->type->value,
                'question_text' => $question->question_text,
                'time_limit' => $question->time_limit,
                'points' => $question->points->value,
                'answers' => $question->answers->map(fn (Answer $answer) => [
                    'answer_text' => $answer->answer_text,
                    'is_correct' => (bool) $answer->is_correct,
                    'color' => $answer->color->value,
                    'shape' => $answer->shape,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    public function toJson(Quiz $quiz): string
    {
        return json_encode($this->toArray($quiz), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Flatten a quiz to CSV: one row per question, up to 4 answer/correct pairs.
     */
    public function toCsv(Quiz $quiz): string
    {
        $data = $this->toArray($quiz);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, [
            'type', 'question_text', 'time_limit', 'points',
            'answer_1', 'correct_1', 'answer_2', 'correct_2',
            'answer_3', 'correct_3', 'answer_4', 'correct_4',
        ]);

        foreach ($data['questions'] as $question) {
            $row = [
                $question['type'],
                $question['question_text'],
                $question['time_limit'],
                $question['points'],
            ];

            for ($i = 0; $i < 4; $i++) {
                $answer = $question['answers'][$i] ?? null;
                $row[] = $answer['answer_text'] ?? '';
                $row[] = $answer ? ($answer['is_correct'] ? '1' : '0') : '';
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
