<?php

namespace Database\Factories;

use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Models\BankQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankQuestion>
 */
class BankQuestionFactory extends Factory
{
    protected $model = BankQuestion::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'type' => QuestionType::MultipleChoice,
            'question_text' => fake()->sentence().'?',
            'image' => null,
            'time_limit' => 20,
            'points' => PointType::Standard,
            'answers' => [
                ['answer_text' => 'A', 'is_correct' => true, 'color' => 'red', 'shape' => 'triangle'],
                ['answer_text' => 'B', 'is_correct' => false, 'color' => 'blue', 'shape' => 'diamond'],
            ],
        ];
    }

    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::TrueFalse,
            'answers' => [
                ['answer_text' => 'True', 'is_correct' => true, 'color' => 'blue', 'shape' => 'diamond'],
                ['answer_text' => 'False', 'is_correct' => false, 'color' => 'red', 'shape' => 'triangle'],
            ],
        ]);
    }
}
