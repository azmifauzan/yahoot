<?php

namespace Database\Seeders;

use App\Enums\AnswerColor;
use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Enums\QuizVisibility;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleQuizSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@yahoot.app'],
            [
                'name' => 'Demo Kreator',
                'password' => bcrypt('password'),
                'avatar' => 'owl',
                'locale' => 'id',
            ]
        );

        $this->createGeographyQuiz($user);
        $this->createScienceQuiz($user);
    }

    private function createGeographyQuiz(User $user): void
    {
        $quiz = Quiz::create([
            'user_id' => $user->id,
            'title' => 'Geografi Indonesia',
            'description' => 'Uji pengetahuanmu tentang geografi Indonesia!',
            'visibility' => QuizVisibility::Public,
            'is_published' => true,
        ]);

        $questions = [
            [
                'type' => QuestionType::MultipleChoice,
                'question_text' => 'Apa ibu kota Indonesia?',
                'time_limit' => 20,
                'points' => PointType::Standard,
                'answers' => [
                    ['text' => 'Surabaya', 'correct' => false],
                    ['text' => 'Jakarta', 'correct' => true],
                    ['text' => 'Bandung', 'correct' => false],
                    ['text' => 'Medan', 'correct' => false],
                ],
            ],
            [
                'type' => QuestionType::MultipleChoice,
                'question_text' => 'Pulau terbesar di Indonesia?',
                'time_limit' => 20,
                'points' => PointType::Standard,
                'answers' => [
                    ['text' => 'Sumatra', 'correct' => false],
                    ['text' => 'Sulawesi', 'correct' => false],
                    ['text' => 'Kalimantan', 'correct' => true],
                    ['text' => 'Papua', 'correct' => false],
                ],
            ],
            [
                'type' => QuestionType::TrueFalse,
                'question_text' => 'Indonesia memiliki lebih dari 17.000 pulau.',
                'time_limit' => 10,
                'points' => PointType::Standard,
                'answers' => [
                    ['text' => 'Benar', 'correct' => true],
                    ['text' => 'Salah', 'correct' => false],
                ],
            ],
            [
                'type' => QuestionType::MultipleChoice,
                'question_text' => 'Gunung tertinggi di Indonesia?',
                'time_limit' => 30,
                'points' => PointType::Double,
                'answers' => [
                    ['text' => 'Gunung Semeru', 'correct' => false],
                    ['text' => 'Gunung Rinjani', 'correct' => false],
                    ['text' => 'Puncak Jaya', 'correct' => true],
                    ['text' => 'Gunung Kerinci', 'correct' => false],
                ],
            ],
            [
                'type' => QuestionType::TrueFalse,
                'question_text' => 'Bali adalah pulau terbesar di Indonesia.',
                'time_limit' => 10,
                'points' => PointType::Standard,
                'answers' => [
                    ['text' => 'Benar', 'correct' => false],
                    ['text' => 'Salah', 'correct' => true],
                ],
            ],
        ];

        $this->createQuestions($quiz, $questions);
    }

    private function createScienceQuiz(User $user): void
    {
        $quiz = Quiz::create([
            'user_id' => $user->id,
            'title' => 'Sains Dasar',
            'description' => 'Pertanyaan sains untuk semua umur!',
            'visibility' => QuizVisibility::Public,
            'is_published' => true,
        ]);

        $questions = [
            [
                'type' => QuestionType::MultipleChoice,
                'question_text' => 'Planet apa yang paling dekat dengan matahari?',
                'time_limit' => 20,
                'points' => PointType::Standard,
                'answers' => [
                    ['text' => 'Venus', 'correct' => false],
                    ['text' => 'Merkurius', 'correct' => true],
                    ['text' => 'Mars', 'correct' => false],
                    ['text' => 'Bumi', 'correct' => false],
                ],
            ],
            [
                'type' => QuestionType::TrueFalse,
                'question_text' => 'Air mendidih pada suhu 100°C di permukaan laut.',
                'time_limit' => 10,
                'points' => PointType::Standard,
                'answers' => [
                    ['text' => 'Benar', 'correct' => true],
                    ['text' => 'Salah', 'correct' => false],
                ],
            ],
            [
                'type' => QuestionType::MultipleChoice,
                'question_text' => 'Apa rumus kimia air?',
                'time_limit' => 15,
                'points' => PointType::Standard,
                'answers' => [
                    ['text' => 'CO2', 'correct' => false],
                    ['text' => 'H2O', 'correct' => true],
                    ['text' => 'O2', 'correct' => false],
                    ['text' => 'NaCl', 'correct' => false],
                ],
            ],
        ];

        $this->createQuestions($quiz, $questions);
    }

    /**
     * @param  array<int, array{type: QuestionType, question_text: string, time_limit: int, points: PointType, answers: array<int, array{text: string, correct: bool}>}>  $questions
     */
    private function createQuestions(Quiz $quiz, array $questions): void
    {
        $colors = [AnswerColor::Red, AnswerColor::Blue, AnswerColor::Yellow, AnswerColor::Green];

        foreach ($questions as $index => $questionData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'type' => $questionData['type'],
                'question_text' => $questionData['question_text'],
                'time_limit' => $questionData['time_limit'],
                'points' => $questionData['points'],
                'order' => $index,
            ]);

            foreach ($questionData['answers'] as $answerIndex => $answerData) {
                $color = $colors[$answerIndex];

                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerData['text'],
                    'is_correct' => $answerData['correct'],
                    'color' => $color,
                    'shape' => $color->shape(),
                    'order' => $answerIndex,
                ]);
            }
        }
    }
}
