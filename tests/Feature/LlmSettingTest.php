<?php

use App\Enums\LlmProvider;
use App\Models\LlmSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('guest is redirected from ai settings page', function () {
    $this->get(route('settings.ai.edit'))
        ->assertRedirect(route('login'));
});

test('user can view ai settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.ai.edit'))
        ->assertSuccessful();
});

test('user can save llm settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('settings.ai.update'), [
            'provider' => 'anthropic',
            'model' => 'claude-3-5-haiku-latest',
            'base_url' => 'https://api.anthropic.com/v1',
            'api_key' => 'sk-ant-test',
        ])
        ->assertRedirect();

    $setting = $user->llmSetting;

    expect($setting->provider)->toBe(LlmProvider::Anthropic)
        ->and($setting->model)->toBe('claude-3-5-haiku-latest')
        ->and($setting->api_key)->toBe('sk-ant-test')
        ->and($setting->isConfigured())->toBeTrue();
});

test('blank api key keeps the existing key on update', function () {
    $user = User::factory()->create();
    $user->llmSetting()->create([
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'api_key' => 'sk-original',
    ]);

    $this->actingAs($user)->put(route('settings.ai.update'), [
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'api_key' => '',
    ]);

    $setting = $user->llmSetting()->first()->fresh();

    expect($setting->model)->toBe('gpt-4o')
        ->and($setting->api_key)->toBe('sk-original');
});

test('api key is encrypted at rest', function () {
    $user = User::factory()->create();
    $setting = $user->llmSetting()->create([
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'api_key' => 'sk-secret-key',
    ]);

    $raw = DB::table('llm_settings')->where('id', $setting->id)->value('api_key');

    expect($raw)->not->toBe('sk-secret-key')
        ->and($setting->fresh()->api_key)->toBe('sk-secret-key');
});

test('provider must be a valid enum value', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('settings.ai.update'), [
            'provider' => 'not-a-provider',
            'model' => 'gpt-4o-mini',
        ])
        ->assertSessionHasErrors('provider');
});

test('llm setting is not configured without an api key', function () {
    $setting = new LlmSetting([
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
    ]);

    expect($setting->isConfigured())->toBeFalse();
});

test('base url cannot point to a private or internal address', function (string $url) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('settings.ai.update'), [
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'base_url' => $url,
            'api_key' => 'sk-test',
        ])
        ->assertSessionHasErrors('base_url');
})->with([
    'loopback IP' => 'http://127.0.0.1:11434',
    'link-local metadata IP' => 'http://169.254.169.254/latest/meta-data',
    'localhost hostname' => 'http://localhost:8080',
    'internal docker hostname' => 'http://reverb:8080',
]);
