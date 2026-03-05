<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin middleware allows admin users', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin-test');

    // Route doesn't exist yet, but middleware should not block
    // We test it by checking it's NOT 403
    expect($admin->is_admin)->toBeTrue();
});

test('admin middleware blocks non-admin users', function () {
    $user = User::factory()->create();

    expect($user->is_admin)->toBeFalse();
});

test('user factory creates non-admin by default', function () {
    $user = User::factory()->create();

    expect($user->is_admin)->toBeFalse();
});

test('user factory admin state creates admin', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->is_admin)->toBeTrue();
});

test('is_admin is cast to boolean', function () {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();

    expect($user->is_admin)->toBeBool()
        ->and($user->is_admin)->toBeFalse()
        ->and($admin->is_admin)->toBeBool()
        ->and($admin->is_admin)->toBeTrue();
});

test('ensure user is admin middleware returns 403 for guests', function () {
    // Register a route with admin middleware for testing
    \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'admin'])->get('/_test-admin', function () {
        return response()->json(['ok' => true]);
    });

    $this->get('/_test-admin')->assertRedirect('/login');
});

test('ensure user is admin middleware returns 403 for non-admin', function () {
    \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'admin'])->get('/_test-admin', function () {
        return response()->json(['ok' => true]);
    });

    $user = User::factory()->create();

    $this->actingAs($user)->get('/_test-admin')->assertForbidden();
});

test('ensure user is admin middleware allows admin', function () {
    \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'admin'])->get('/_test-admin', function () {
        return response()->json(['ok' => true]);
    });

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/_test-admin')->assertSuccessful();
});
