<?php

use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Quiz;
use App\Models\User;

// ──────────────────────────────────────────────────────────────
// Admin Dashboard
// ──────────────────────────────────────────────────────────────

test('guests cannot access admin dashboard', function () {
    $this->get('/admin')->assertRedirect('/login');
});

test('non-admin users cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertSuccessful();
});

test('admin dashboard shows statistics', function () {
    $admin = User::factory()->admin()->create();

    Quiz::factory()->count(2)->published()->for($admin)->create();
    Quiz::factory()->count(1)->for($admin)->create(['is_published' => false]);

    $response = $this->actingAs($admin)->get('/admin')->assertSuccessful();

    $stats = $response->original->getData()['page']['props']['stats'];
    expect($stats['total_users'])->toBe(1)
        ->and($stats['total_quizzes'])->toBe(3)
        ->and($stats['total_published'])->toBe(2)
        ->and($stats['total_draft'])->toBe(1);
});

// ──────────────────────────────────────────────────────────────
// Admin User Management
// ──────────────────────────────────────────────────────────────

test('guests cannot access admin users page', function () {
    $this->get('/admin/users')->assertRedirect('/login');
});

test('non-admin users cannot access admin users page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/users')->assertForbidden();
});

test('admin can list users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/admin/users')->assertSuccessful();

    $users = $response->original->getData()['page']['props']['users'];
    expect($users['total'])->toBe(4); // 3 + admin
});

test('admin can search users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'John Smith']);
    User::factory()->create(['name' => 'Jane Doe']);

    $response = $this->actingAs($admin)
        ->get('/admin/users?search=John')
        ->assertSuccessful();

    $users = $response->original->getData()['page']['props']['users'];
    expect($users['total'])->toBe(1);
});

test('admin can filter users by role', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)
        ->get('/admin/users?role=admin')
        ->assertSuccessful();

    $users = $response->original->getData()['page']['props']['users'];
    expect($users['total'])->toBe(2);
});

test('admin can view user detail', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->get("/admin/users/{$user->id}")
        ->assertSuccessful();
});

test('admin can toggle admin status', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    expect($user->is_admin)->toBeFalse();

    $this->actingAs($admin)
        ->post("/admin/users/{$user->id}/toggle-admin")
        ->assertRedirect();

    expect($user->fresh()->is_admin)->toBeTrue();

    $this->actingAs($admin)
        ->post("/admin/users/{$user->id}/toggle-admin")
        ->assertRedirect();

    expect($user->fresh()->is_admin)->toBeFalse();
});

test('admin cannot toggle own admin status', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$admin->id}/toggle-admin")
        ->assertRedirect()
        ->assertSessionHasErrors('user');

    expect($admin->fresh()->is_admin)->toBeTrue();
});

test('admin can delete a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/users/{$user->id}")
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete("/admin/users/{$admin->id}")
        ->assertRedirect()
        ->assertSessionHasErrors('user');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

// ──────────────────────────────────────────────────────────────
// Admin Quiz Management
// ──────────────────────────────────────────────────────────────

test('guests cannot access admin quizzes page', function () {
    $this->get('/admin/quizzes')->assertRedirect('/login');
});

test('non-admin users cannot access admin quizzes page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/quizzes')->assertForbidden();
});

test('admin can list all quizzes', function () {
    $admin = User::factory()->admin()->create();
    Quiz::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/admin/quizzes')->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes['total'])->toBe(3);
});

test('admin can search quizzes', function () {
    $admin = User::factory()->admin()->create();
    Quiz::factory()->create(['title' => 'Science Quiz']);
    Quiz::factory()->create(['title' => 'Math Quiz']);

    $response = $this->actingAs($admin)
        ->get('/admin/quizzes?search=Science')
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes['total'])->toBe(1);
});

test('admin can filter quizzes by status', function () {
    $admin = User::factory()->admin()->create();
    Quiz::factory()->published()->count(2)->create();
    Quiz::factory()->create(['is_published' => false]);

    $response = $this->actingAs($admin)
        ->get('/admin/quizzes?status=published')
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes['total'])->toBe(2);
});

test('admin can filter trashed quizzes', function () {
    $admin = User::factory()->admin()->create();
    Quiz::factory()->count(2)->create();
    $trashed = Quiz::factory()->create();
    $trashed->delete();

    $response = $this->actingAs($admin)
        ->get('/admin/quizzes?status=trashed')
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes['total'])->toBe(1);
});

test('admin can view quiz detail', function () {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();

    $this->actingAs($admin)
        ->get("/admin/quizzes/{$quiz->id}")
        ->assertSuccessful();
});

test('admin can view trashed quiz detail', function () {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();
    $quizId = $quiz->id;
    $quiz->delete();

    $this->actingAs($admin)
        ->get("/admin/quizzes/{$quizId}")
        ->assertSuccessful();
});

test('admin can soft delete a quiz', function () {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/quizzes/{$quiz->id}")
        ->assertRedirect(route('admin.quizzes.index'));

    expect(Quiz::withTrashed()->find($quiz->id)->trashed())->toBeTrue();
});

test('admin can permanently delete a trashed quiz', function () {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();
    $quizId = $quiz->id;
    $quiz->delete();

    $this->actingAs($admin)
        ->delete("/admin/quizzes/{$quizId}")
        ->assertRedirect(route('admin.quizzes.index'));

    expect(Quiz::withTrashed()->find($quizId))->toBeNull();
});

test('admin can restore a trashed quiz', function () {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();
    $quizId = $quiz->id;
    $quiz->delete();

    expect(Quiz::withTrashed()->find($quizId)->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->post("/admin/quizzes/{$quizId}/restore")
        ->assertRedirect();

    expect(Quiz::find($quizId)->trashed())->toBeFalse();
});

// ──────────────────────────────────────────────────────────────
// Admin Game History
// ──────────────────────────────────────────────────────────────

test('guests cannot access admin games page', function () {
    $this->get('/admin/games')->assertRedirect('/login');
});

test('non-admin users cannot access admin games page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/games')->assertForbidden();
});

test('admin can list game sessions', function () {
    $admin = User::factory()->admin()->create();
    GameSession::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/admin/games')->assertSuccessful();

    $games = $response->original->getData()['page']['props']['games'];
    expect($games['total'])->toBe(3);
});

test('admin can filter game sessions by status', function () {
    $admin = User::factory()->admin()->create();
    GameSession::factory()->count(2)->finished()->create();
    GameSession::factory()->create(['status' => GameStatus::Waiting]);

    $response = $this->actingAs($admin)
        ->get('/admin/games?status=finished')
        ->assertSuccessful();

    $games = $response->original->getData()['page']['props']['games'];
    expect($games['total'])->toBe(2);
});

test('admin can view game session detail', function () {
    $admin = User::factory()->admin()->create();
    $game = GameSession::factory()->create();

    $this->actingAs($admin)
        ->get("/admin/games/{$game->id}")
        ->assertSuccessful();
});

test('admin can delete a game session', function () {
    $admin = User::factory()->admin()->create();
    $game = GameSession::factory()->create();
    GamePlayer::factory()->for($game)->count(2)->create();

    $this->actingAs($admin)
        ->delete("/admin/games/{$game->id}")
        ->assertRedirect(route('admin.games.index'));

    $this->assertDatabaseMissing('game_sessions', ['id' => $game->id]);
    $this->assertDatabaseCount('game_players', 0);
});

// ──────────────────────────────────────────────────────────────
// Admin Settings
// ──────────────────────────────────────────────────────────────

test('guests cannot access admin settings', function () {
    $this->get('/admin/settings')->assertRedirect('/login');
});

test('non-admin users cannot access admin settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/settings')->assertForbidden();
});

test('admin can access settings page', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/settings')->assertSuccessful();

    $settings = $response->original->getData()['page']['props']['settings'];
    expect($settings)->toHaveKey('app_name')
        ->and($settings)->toHaveKey('app_locale');
});
