<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\User;
use App\Services\RankingService;

test('recording a game result accumulates xp, games played and a win for the leader', function () {
    $winner = User::factory()->create();
    $loser = User::factory()->create();

    $session = GameSession::factory()->finished()->create();
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $winner->id,
        'score' => 1200,
    ]);
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $loser->id,
        'score' => 400,
    ]);

    app(RankingService::class)->recordGameResult($session->fresh());

    expect($winner->fresh())
        ->total_xp->toBe(1200)
        ->games_played->toBe(1)
        ->games_won->toBe(1);

    expect($loser->fresh())
        ->total_xp->toBe(400)
        ->games_played->toBe(1)
        ->games_won->toBe(0);
});

test('guest players do not accumulate ranking stats', function () {
    $session = GameSession::factory()->finished()->create();
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => null,
        'score' => 999,
    ]);

    app(RankingService::class)->recordGameResult($session->fresh());

    expect(User::sum('total_xp'))->toBe(0);
});

test('ending a game updates the host-side ranking via the queued job', function () {
    $host = User::factory()->create();
    $session = GameSession::factory()->playing()->create(['host_id' => $host->id]);

    $registered = User::factory()->create();
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $registered->id,
        'score' => 750,
    ]);

    $this->actingAs($host)
        ->post(route('game.end', $session))
        ->assertRedirect(route('game.results', $session));

    expect($registered->fresh())
        ->total_xp->toBe(750)
        ->games_played->toBe(1)
        ->games_won->toBe(1);
});

test('ending an already finished game does not double-count xp', function () {
    $host = User::factory()->create();
    $session = GameSession::factory()->playing()->create(['host_id' => $host->id]);

    $registered = User::factory()->create();
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $registered->id,
        'score' => 500,
    ]);

    $this->actingAs($host)->post(route('game.end', $session));
    $this->actingAs($host)->post(route('game.end', $session));

    expect($registered->fresh()->total_xp)->toBe(500);
});

test('top players are ordered by xp and exclude users who never played', function () {
    User::factory()->create(['total_xp' => 5000, 'games_played' => 3, 'games_won' => 1]);
    User::factory()->create(['total_xp' => 9000, 'games_played' => 5, 'games_won' => 4]);
    User::factory()->create(['total_xp' => 0, 'games_played' => 0]);

    $top = app(RankingService::class)->topPlayers();

    expect($top)->toHaveCount(2)
        ->and($top->first()['total_xp'])->toBe(9000)
        ->and($top->first()['rank'])->toBe(1)
        ->and($top->last()['total_xp'])->toBe(5000);
});

test('the global leaderboard page is publicly accessible', function () {
    $this->get(route('leaderboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Leaderboard'));
});

test('rankFor returns the correct 1-based position', function () {
    $top = User::factory()->create(['total_xp' => 9000, 'games_played' => 5]);
    $me = User::factory()->create(['total_xp' => 5000, 'games_played' => 3]);

    expect(app(RankingService::class)->rankFor($me))->toBe(2)
        ->and(app(RankingService::class)->rankFor($top))->toBe(1);
});
