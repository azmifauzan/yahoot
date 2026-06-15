<?php

use App\Events\ReactionSent;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;

function createReactionSetup(array $settings = []): array
{
    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
        'settings' => $settings,
    ]);

    $player = GamePlayer::factory()->create(['game_session_id' => $session->id]);

    return [$session, $player];
}

test('player can send a whitelisted emoji reaction', function () {
    Event::fake([ReactionSent::class]);
    [$session, $player] = createReactionSetup();

    $this->postJson("/api/games/{$session->id}/react", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'emoji' => '🎉',
    ])->assertSuccessful();

    Event::assertDispatched(ReactionSent::class, function (ReactionSent $event) use ($session, $player) {
        return $event->gameSessionId === $session->id
            && $event->emoji === '🎉'
            && $event->nickname === $player->nickname;
    });
});

test('non-whitelisted emoji is rejected', function () {
    [$session, $player] = createReactionSetup();

    $this->postJson("/api/games/{$session->id}/react", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'emoji' => '💩',
    ])->assertUnprocessable();
});

test('reaction with invalid player token is rejected', function () {
    [$session, $player] = createReactionSetup();

    $this->postJson("/api/games/{$session->id}/react", [
        'player_id' => $player->id,
        'player_token' => 'wrong-token',
        'emoji' => '🎉',
    ])->assertForbidden();
});

test('reactions are rejected when disabled via session settings', function () {
    [$session, $player] = createReactionSetup(['reactions_enabled' => false]);

    $this->postJson("/api/games/{$session->id}/react", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'emoji' => '🎉',
    ])->assertForbidden();
});

test('reactions are rate limited per player', function () {
    Event::fake([ReactionSent::class]);
    [$session, $player] = createReactionSetup();

    RateLimiter::clear('react:'.$player->id);

    // First reaction allowed.
    $this->postJson("/api/games/{$session->id}/react", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'emoji' => '🎉',
    ])->assertSuccessful();

    // Immediate second reaction throttled.
    $this->postJson("/api/games/{$session->id}/react", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'emoji' => '😂',
    ])->assertStatus(429);
});
