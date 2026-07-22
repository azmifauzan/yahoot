<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\AchievementService;

test('column-based badge criteria are awarded when met', function () {
    $user = User::factory()->create([
        'games_played' => 30,
        'games_won' => 12,
        'total_xp' => 12000,
    ]);

    $earned = app(AchievementService::class)->evaluate($user);

    expect($earned)->toContain('first_game', 'first_win', 'veteran', 'champion', 'high_scorer');
    $this->assertDatabaseHas('user_badges', ['user_id' => $user->id, 'badge_key' => 'champion']);
});

test('badges are not awarded twice', function () {
    $user = User::factory()->create(['games_played' => 1]);

    app(AchievementService::class)->evaluate($user);
    $second = app(AchievementService::class)->evaluate($user->fresh());

    expect($second)->not->toContain('first_game')
        ->and(UserBadge::where('user_id', $user->id)->where('badge_key', 'first_game')->count())->toBe(1);
});

test('total correct and best streak are computed from player answers', function () {
    $user = User::factory()->create(['games_played' => 1]);
    $session = GameSession::factory()->finished()->create();
    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $user->id,
    ]);

    // 10 correct in a row → streak_master; also counts toward total_correct.
    for ($i = 0; $i < 10; $i++) {
        PlayerAnswer::factory()->correct()->create(['game_player_id' => $player->id]);
    }

    $stats = app(AchievementService::class)->statsFor($user->fresh());

    expect($stats['total_correct'])->toBe(10)
        ->and($stats['best_streak'])->toBe(10);

    $earned = app(AchievementService::class)->evaluate($user->fresh());
    expect($earned)->toContain('streak_master');
});

test('best streak resets on a wrong answer', function () {
    $user = User::factory()->create();
    $session = GameSession::factory()->finished()->create();
    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $user->id,
    ]);

    PlayerAnswer::factory()->correct()->create(['game_player_id' => $player->id]);
    PlayerAnswer::factory()->correct()->create(['game_player_id' => $player->id]);
    PlayerAnswer::factory()->create(['game_player_id' => $player->id, 'is_correct' => false]);
    PlayerAnswer::factory()->correct()->create(['game_player_id' => $player->id]);

    expect(app(AchievementService::class)->statsFor($user->fresh())['best_streak'])->toBe(2);
});

test('ending a game awards first game and first win to the registered winner', function () {
    $host = User::factory()->create();
    $session = GameSession::factory()->playing()->create(['host_id' => $host->id]);
    $registered = User::factory()->create();
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $registered->id,
        'score' => 800,
    ]);

    $this->actingAs($host)->post(route('game.end', $session));

    $this->assertDatabaseHas('user_badges', ['user_id' => $registered->id, 'badge_key' => 'first_game']);
    $this->assertDatabaseHas('user_badges', ['user_id' => $registered->id, 'badge_key' => 'first_win']);
});

test('guest players never receive badges', function () {
    $host = User::factory()->create();
    $session = GameSession::factory()->playing()->create(['host_id' => $host->id]);
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => null,
        'score' => 800,
    ]);

    $this->actingAs($host)->post(route('game.end', $session));

    expect(UserBadge::count())->toBe(0);
});

test('the progress page lists earned and locked badges', function () {
    $user = User::factory()->create(['games_played' => 2, 'games_won' => 1, 'total_xp' => 1500]);
    UserBadge::factory()->create(['user_id' => $user->id, 'badge_key' => 'first_game']);

    $this->actingAs($user)
        ->get(route('progress'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Progress')
            ->where('earnedCount', 1)
            ->has('badges', count(config('badges')))
            ->where('badges.0.icon', 'play')
        );
});
