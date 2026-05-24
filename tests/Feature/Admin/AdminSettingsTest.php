<?php

use App\Models\AppSetting;
use App\Models\User;

test('admin can view settings page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.settings.index'))
        ->assertSuccessful();
});

test('non-admin cannot access settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.settings.index'))
        ->assertForbidden();
});

test('guest is redirected from settings', function () {
    $this->get(route('admin.settings.index'))
        ->assertRedirect(route('login'));
});

test('admin can update settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'app_name' => 'MyQuiz',
            'default_language' => 'en',
            'allow_registration' => true,
            'allow_guest_play' => false,
        ]);

    expect(AppSetting::get('app_name'))->toBe('MyQuiz')
        ->and(AppSetting::get('default_language'))->toBe('en');
});

test('settings update validates language field', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'default_language' => 'fr',
        ])
        ->assertSessionHasErrors('default_language');
});

test('app setting model can get and set values', function () {
    AppSetting::set('test_key', 'test_value');
    expect(AppSetting::get('test_key'))->toBe('test_value');
});

test('app setting returns default when key missing', function () {
    expect(AppSetting::get('nonexistent_key', 'fallback'))->toBe('fallback');
});
