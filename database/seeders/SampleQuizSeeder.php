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
    /**
     * Shared demo account. Public demo data is reset hourly by the
     * `demo:reset` command back to whatever this seeder produces.
     */
    public const DEMO_EMAIL = 'demo@yahoot.app';

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo Kreator',
                'password' => bcrypt('password'),
                'avatar' => 'owl',
                'locale' => 'id',
            ]
        );

        // Idempotent: wipe any existing demo quizzes (cascades questions,
        // answers, and game sessions) so re-seeding never duplicates.
        $user->quizzes()->delete();

        foreach ($this->quizzes() as $quizData) {
            $quiz = Quiz::create([
                'user_id' => $user->id,
                'title' => $quizData['title'],
                'description' => $quizData['description'],
                'visibility' => QuizVisibility::Public,
                'is_published' => true,
            ]);

            $this->createQuestions($quiz, $quizData['questions']);
        }
    }

    /**
     * @return array<int, array{title: string, description: string, questions: array<int, array{type: QuestionType, question_text: string, time_limit: int, points: PointType, answers: array<int, array{text: string, correct: bool}>}>}>
     */
    private function quizzes(): array
    {
        return [
            [
                'title' => 'Geografi Indonesia',
                'description' => 'Uji pengetahuanmu tentang geografi Indonesia!',
                'questions' => [
                    $this->mc('Apa ibu kota Indonesia?', [
                        ['Surabaya', false], ['Jakarta', true], ['Bandung', false], ['Medan', false],
                    ]),
                    $this->mc('Pulau terbesar di Indonesia?', [
                        ['Sumatra', false], ['Sulawesi', false], ['Kalimantan', true], ['Papua', false],
                    ]),
                    $this->tf('Indonesia memiliki lebih dari 17.000 pulau.', true, 10),
                    $this->mc('Gunung tertinggi di Indonesia?', [
                        ['Gunung Semeru', false], ['Gunung Rinjani', false], ['Puncak Jaya', true], ['Gunung Kerinci', false],
                    ], 30, PointType::Double),
                    $this->tf('Bali adalah pulau terbesar di Indonesia.', false, 10),
                ],
            ],
            [
                'title' => 'Sains Dasar',
                'description' => 'Pertanyaan sains untuk semua umur!',
                'questions' => [
                    $this->mc('Planet apa yang paling dekat dengan matahari?', [
                        ['Venus', false], ['Merkurius', true], ['Mars', false], ['Bumi', false],
                    ]),
                    $this->tf('Air mendidih pada suhu 100°C di permukaan laut.', true, 10),
                    $this->mc('Apa rumus kimia air?', [
                        ['CO2', false], ['H2O', true], ['O2', false], ['NaCl', false],
                    ], 15),
                    $this->mc('Gas apa yang paling banyak di atmosfer Bumi?', [
                        ['Oksigen', false], ['Nitrogen', true], ['Karbon dioksida', false], ['Hidrogen', false],
                    ]),
                    $this->tf('Manusia memiliki 206 tulang saat dewasa.', true, 10),
                ],
            ],
            [
                'title' => 'Sejarah Indonesia',
                'description' => 'Seberapa jauh kamu mengenal sejarah bangsa?',
                'questions' => [
                    $this->mc('Tahun berapa Indonesia memproklamasikan kemerdekaan?', [
                        ['1942', false], ['1945', true], ['1949', false], ['1950', false],
                    ]),
                    $this->mc('Siapa proklamator kemerdekaan Indonesia?', [
                        ['Soeharto', false], ['Soekarno & Hatta', true], ['Sudirman', false], ['Tan Malaka', false],
                    ]),
                    $this->tf('Hari Kemerdekaan Indonesia diperingati setiap 17 Agustus.', true, 10),
                    $this->mc('Kerajaan maritim terbesar di Nusantara adalah?', [
                        ['Majapahit', false], ['Sriwijaya', true], ['Mataram', false], ['Singasari', false],
                    ], 25),
                    $this->tf('Sumpah Pemuda dideklarasikan pada tahun 1928.', true, 10),
                ],
            ],
            [
                'title' => 'Matematika Dasar',
                'description' => 'Asah otak dengan soal matematika cepat!',
                'questions' => [
                    $this->mc('Berapakah 7 × 8?', [
                        ['54', false], ['56', true], ['64', false], ['48', false],
                    ], 15),
                    $this->mc('Berapakah 144 ÷ 12?', [
                        ['11', false], ['12', true], ['14', false], ['10', false],
                    ], 15),
                    $this->tf('Bilangan prima terkecil adalah 2.', true, 10),
                    $this->mc('Berapa hasil dari 15% dari 200?', [
                        ['20', false], ['25', false], ['30', true], ['35', false],
                    ], 20),
                    $this->tf('Jumlah sudut dalam segitiga adalah 180 derajat.', true, 10),
                ],
            ],
            [
                'title' => 'Teknologi & Internet',
                'description' => 'Kuis seputar dunia teknologi modern.',
                'questions' => [
                    $this->mc('Apa kepanjangan dari "HTTP"?', [
                        ['HyperText Transfer Protocol', true], ['High Transfer Text Protocol', false], ['HyperText Transmission Path', false], ['Host Transfer Protocol', false],
                    ], 25),
                    $this->mc('Perusahaan apa yang membuat sistem operasi Android?', [
                        ['Apple', false], ['Microsoft', false], ['Google', true], ['Samsung', false],
                    ]),
                    $this->tf('CPU sering disebut sebagai "otak" komputer.', true, 10),
                    $this->mc('Bahasa pemrograman apa yang dipakai untuk web styling?', [
                        ['HTML', false], ['CSS', true], ['SQL', false], ['JSON', false],
                    ], 20),
                    $this->tf('RAM menyimpan data secara permanen saat komputer dimatikan.', false, 10),
                ],
            ],
            [
                'title' => 'Olahraga Dunia',
                'description' => 'Untuk para penggemar olahraga sejati!',
                'questions' => [
                    $this->mc('Berapa jumlah pemain dalam satu tim sepak bola di lapangan?', [
                        ['9', false], ['10', false], ['11', true], ['12', false],
                    ]),
                    $this->mc('Olahraga apa yang menggunakan istilah "smash" dan "rally"?', [
                        ['Basket', false], ['Bulu tangkis', true], ['Renang', false], ['Tinju', false],
                    ]),
                    $this->tf('Olimpiade modern diselenggarakan setiap 4 tahun.', true, 10),
                    $this->mc('Negara mana yang paling banyak memenangkan Piala Dunia FIFA?', [
                        ['Jerman', false], ['Brasil', true], ['Argentina', false], ['Italia', false],
                    ], 25),
                    $this->tf('Dalam bola basket, satu lemparan bebas bernilai 3 poin.', false, 10),
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: bool}>  $answers
     * @return array{type: QuestionType, question_text: string, time_limit: int, points: PointType, answers: array<int, array{text: string, correct: bool}>}
     */
    private function mc(string $text, array $answers, int $timeLimit = 20, PointType $points = PointType::Standard): array
    {
        return [
            'type' => QuestionType::MultipleChoice,
            'question_text' => $text,
            'time_limit' => $timeLimit,
            'points' => $points,
            'answers' => array_map(fn (array $a) => ['text' => $a[0], 'correct' => $a[1]], $answers),
        ];
    }

    /**
     * @return array{type: QuestionType, question_text: string, time_limit: int, points: PointType, answers: array<int, array{text: string, correct: bool}>}
     */
    private function tf(string $text, bool $correct, int $timeLimit = 10, PointType $points = PointType::Standard): array
    {
        return [
            'type' => QuestionType::TrueFalse,
            'question_text' => $text,
            'time_limit' => $timeLimit,
            'points' => $points,
            'answers' => [
                ['text' => 'Benar', 'correct' => $correct],
                ['text' => 'Salah', 'correct' => ! $correct],
            ],
        ];
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
