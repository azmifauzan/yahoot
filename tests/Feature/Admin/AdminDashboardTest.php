<?php

use App\Models\User;

test('admin can access dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful();
});

test('non-admin cannot access dashboard', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('guest is redirected to login', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('dashboard stats are returned', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful();

    $props = $response->original->getData()['page']['props'];
    expect($props['stats'])->toHaveKeys([
        'total_users',
        'total_quizzes',
        'active_quizzes',
        'deleted_quizzes',
        'total_games',
        'games_today',
        'total_players',
    ]);
});
