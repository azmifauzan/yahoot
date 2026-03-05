<?php

namespace Database\Factories;

use App\Enums\AnswerColor;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'answer_text' => fake()->sentence(2),
            'is_correct' => false,
            'color' => AnswerColor::Red,
            'shape' => 'triangle',
            'order' => 0,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
        ]);
    }

    public function red(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => AnswerColor::Red,
            'shape' => 'triangle',
            'order' => 0,
        ]);
    }

    public function blue(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => AnswerColor::Blue,
            'shape' => 'diamond',
            'order' => 1,
        ]);
    }

    public function yellow(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => AnswerColor::Yellow,
            'shape' => 'circle',
            'order' => 2,
        ]);
    }

    public function green(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => AnswerColor::Green,
            'shape' => 'square',
            'order' => 3,
        ]);
    }
}
