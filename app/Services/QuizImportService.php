<?php

namespace App\Services;

use App\Enums\AnswerColor;
use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Enums\QuizTheme;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizImportService
{
    private const MAX_QUESTIONS = 100;

    private const DEFAULT_COLORS = ['red', 'blue', 'yellow', 'green'];

    /**
     * Parse an uploaded file into the exchange array, honouring its extension.
     *
     * @return array<string, mixed>
     */
    public function parse(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $contents = (string) file_get_contents($file->getRealPath());

        return match ($extension) {
            'csv', 'txt' => $this->parseCsv($contents),
            default => $this->parseJson($contents),
        };
    }

    /**
     * Import an exchange array as a new draft quiz owned by the user.
     */
    public function import(array $data, User $user): Quiz
    {
        $this->assertValid($data);

        return DB::transaction(function () use ($data, $user) {
            $quiz = $user->quizzes()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'theme' => $this->normaliseTheme($data['theme'] ?? null),
                'is_published' => false,
            ]);

            foreach (array_values($data['questions'] ?? []) as $order => $rawQuestion) {
                $question = $quiz->questions()->create([
                    'type' => $this->normaliseType($rawQuestion['type'] ?? null),
                    'question_text' => $rawQuestion['question_text'],
                    'time_limit' => (int) ($rawQuestion['time_limit'] ?? 20),
                    'points' => $this->normalisePoints($rawQuestion['points'] ?? null),
                    'order' => $order,
                ]);

                foreach (array_values($rawQuestion['answers'] ?? []) as $index => $rawAnswer) {
                    $color = $rawAnswer['color'] ?? (self::DEFAULT_COLORS[$index] ?? 'red');
                    $question->answers()->create([
                        'answer_text' => $rawAnswer['answer_text'] ?? '',
                        'is_correct' => (bool) ($rawAnswer['is_correct'] ?? false),
                        'color' => $color,
                        'shape' => $rawAnswer['shape'] ?? AnswerColor::from($color)->shape(),
                        'order' => $index,
                    ]);
                }
            }

            return $quiz;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJson(string $contents): array
    {
        $data = json_decode($contents, true);

        if (! is_array($data)) {
            throw ValidationException::withMessages([
                'file' => __('File JSON tidak valid.'),
            ]);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseCsv(string $contents): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($contents)) ?: [];
        $header = str_getcsv((string) array_shift($lines));

        if ($header === null || ! in_array('question_text', $header, true)) {
            throw ValidationException::withMessages([
                'file' => __('Header CSV tidak valid.'),
            ]);
        }

        $questions = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = array_combine($header, str_getcsv($line));

            $answers = [];
            for ($i = 1; $i <= 4; $i++) {
                $text = $row["answer_{$i}"] ?? '';
                if ($text === '') {
                    continue;
                }
                $answers[] = [
                    'answer_text' => $text,
                    'is_correct' => in_array(strtolower((string) ($row["correct_{$i}"] ?? '')), ['1', 'true', 'yes'], true),
                ];
            }

            $questions[] = [
                'type' => $row['type'] ?? 'multiple_choice',
                'question_text' => $row['question_text'] ?? '',
                'time_limit' => (int) ($row['time_limit'] ?? 20),
                'points' => $row['points'] ?? 'standard',
                'answers' => $answers,
            ];
        }

        return [
            'title' => __('Kuis Impor'),
            'questions' => $questions,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertValid(array $data): void
    {
        if (empty($data['title']) || ! is_string($data['title'])) {
            throw ValidationException::withMessages([
                'file' => __('Judul kuis wajib diisi.'),
            ]);
        }

        if (! isset($data['questions']) || ! is_array($data['questions'])) {
            throw ValidationException::withMessages([
                'file' => __('Daftar pertanyaan tidak valid.'),
            ]);
        }

        if (count($data['questions']) > self::MAX_QUESTIONS) {
            throw ValidationException::withMessages([
                'file' => __('Terlalu banyak pertanyaan (maks :max).', ['max' => self::MAX_QUESTIONS]),
            ]);
        }

        foreach ($data['questions'] as $question) {
            if (! is_array($question) || empty($question['question_text'])) {
                throw ValidationException::withMessages([
                    'file' => __('Setiap pertanyaan harus memiliki teks.'),
                ]);
            }
        }
    }

    private function normaliseType(?string $value): QuestionType
    {
        return QuestionType::tryFrom((string) $value) ?? QuestionType::MultipleChoice;
    }

    private function normalisePoints(?string $value): PointType
    {
        return PointType::tryFrom((string) $value) ?? PointType::Standard;
    }

    private function normaliseTheme(?string $value): QuizTheme
    {
        return QuizTheme::tryFrom((string) $value) ?? QuizTheme::Standard;
    }
}
