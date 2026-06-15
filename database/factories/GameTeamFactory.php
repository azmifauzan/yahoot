<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\GameTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameTeam>
 */
class GameTeamFactory extends Factory
{
    protected $model = GameTeam::class;

    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(),
            'name' => fake()->unique()->word(),
            'color' => fake()->randomElement(['red', 'blue', 'green', 'yellow']),
            'score' => 0,
        ];
    }
}
