<?php

namespace Database\Factories;

use App\Enums\LlmProvider;
use App\Models\LlmSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LlmSetting>
 */
class LlmSettingFactory extends Factory
{
    protected $model = LlmSetting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => LlmProvider::OpenAI,
            'model' => LlmProvider::OpenAI->defaultModel(),
            'base_url' => null,
            'api_key' => 'sk-test-'.fake()->uuid(),
        ];
    }

    public function anthropic(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => LlmProvider::Anthropic,
            'model' => LlmProvider::Anthropic->defaultModel(),
        ]);
    }

    public function withoutApiKey(): static
    {
        return $this->state(fn (array $attributes) => [
            'api_key' => null,
        ]);
    }
}
