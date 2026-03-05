<?php

namespace Database\Factories;

use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'type' => QuestionType::MultipleChoice,
            'question_text' => fake()->sentence().'?',
            'image' => null,
            'time_limit' => fake()->randomElement([5, 10, 20, 30, 60]),
            'points' => PointType::Standard,
            'order' => 0,
        ];
    }

    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::TrueFalse,
        ]);
    }

    public function doublePoints(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => PointType::Double,
        ]);
    }
}
