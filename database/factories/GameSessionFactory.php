<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Models\GameSession;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameSession>
 */
class GameSessionFactory extends Factory
{
    protected $model = GameSession::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'host_id' => User::factory(),
            'game_code' => str_pad((string) fake()->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT),
            'status' => GameStatus::Waiting,
            'current_question_index' => 0,
            'question_started_at' => null,
            'settings' => null,
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    public function playing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Playing,
            'started_at' => now(),
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Finished,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now(),
        ]);
    }
}
