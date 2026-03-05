<?php

namespace Database\Factories;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GamePlayer>
 */
class GamePlayerFactory extends Factory
{
    protected $model = GamePlayer::class;

    public function definition(): array
    {
        $avatars = [
            'cat', 'dog', 'panda', 'rabbit', 'fox', 'owl',
            'robot-blue', 'robot-red', 'robot-green', 'robot-yellow', 'robot-purple', 'robot-pink',
            'monster-1', 'monster-2', 'monster-3', 'monster-4', 'monster-5', 'monster-6',
            'star', 'moon', 'sun', 'cloud', 'rainbow', 'lightning',
        ];

        return [
            'game_session_id' => GameSession::factory(),
            'user_id' => null,
            'nickname' => fake()->userName(),
            'avatar' => fake()->randomElement($avatars),
            'score' => 0,
            'streak' => 0,
            'is_connected' => true,
            'finished_at' => null,
        ];
    }

    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function disconnected(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_connected' => false,
        ]);
    }
}
