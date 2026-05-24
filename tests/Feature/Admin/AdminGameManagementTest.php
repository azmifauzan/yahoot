<?php

use App\Models\GameSession;
use App\Models\Quiz;
use App\Models\User;

test('admin can list game sessions', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.games.index'))
        ->assertSuccessful();
});

test('non-admin cannot list game sessions', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.games.index'))
        ->assertForbidden();
});

test('guest is redirected from admin games', function () {
    $this->get(route('admin.games.index'))
        ->assertRedirect(route('login'));
});

test('admin can view game session detail', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $session = GameSession::factory()->finished()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.games.show', $session))
        ->assertSuccessful();
});

test('admin can delete a game session', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $session = GameSession::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.games.destroy', $session))
        ->assertRedirect(route('admin.games.index'));

    $this->assertModelMissing($session);
});

test('admin can filter game sessions by status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $finished = GameSession::factory()->finished()->create();
    $waiting = GameSession::factory()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.games.index', ['status' => 'finished']))
        ->assertSuccessful();

    $games = $response->original->getData()['page']['props']['games']['data'];
    $ids = collect($games)->pluck('id');
    expect($ids)->toContain($finished->id)
        ->not->toContain($waiting->id);
});
