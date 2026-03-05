<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\PlayerAnswer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerAnswer>
 */
class PlayerAnswerFactory extends Factory
{
    protected $model = PlayerAnswer::class;

    public function definition(): array
    {
        return [
            'game_player_id' => GamePlayer::factory(),
            'question_id' => Question::factory(),
            'answer_id' => Answer::factory(),
            'is_correct' => false,
            'time_taken' => fake()->numberBetween(1000, 20000),
            'points_earned' => 0,
            'streak_bonus' => 0,
        ];
    }

    public function correct(int $points = 950): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
            'points_earned' => $points,
        ]);
    }

    public function unanswered(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_id' => null,
            'is_correct' => false,
            'time_taken' => 0,
            'points_earned' => 0,
        ]);
    }
}
