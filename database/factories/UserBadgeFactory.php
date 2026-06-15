<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserBadge>
 */
class UserBadgeFactory extends Factory
{
    protected $model = UserBadge::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'badge_key' => fake()->randomElement(array_keys(config('badges', ['first_game' => []]))),
            'earned_at' => now(),
        ];
    }
}
