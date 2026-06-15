<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\Quiz;
use App\Models\User;
use App\Services\PlayerProgressService;

test('progress is reported per finished game for the user only', function () {
    $user = User::factory()->create(['games_played' => 1, 'games_won' => 1, 'total_xp' => 600]);
    $other = User::factory()->create();

    $quiz = Quiz::factory()->create(['title' => 'Geography']);
    $session = GameSession::factory()->finished()->create(['quiz_id' => $quiz->id]);

    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $user->id,
        'score' => 600,
    ]);
    PlayerAnswer::factory()->correct()->create(['game_player_id' => $player->id]);
    PlayerAnswer::factory()->create(['game_player_id' => $player->id, 'is_correct' => false]);

    // Another user's game must not leak in.
    $otherPlayer = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $other->id,
    ]);
    PlayerAnswer::factory()->correct()->create(['game_player_id' => $otherPlayer->id]);

    $progress = app(PlayerProgressService::class)->forUser($user->fresh());

    expect($progress['games'])->toHaveCount(1)
        ->and($progress['games'][0]['quiz_title'])->toBe('Geography')
        ->and($progress['games'][0]['score'])->toBe(600)
        ->and($progress['games'][0]['accuracy'])->toBe(50.0)
        ->and($progress['totals']['total_xp'])->toBe(600);
});

test('unfinished games are excluded from progress', function () {
    $user = User::factory()->create();
    $session = GameSession::factory()->playing()->create();
    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'user_id' => $user->id,
    ]);

    $progress = app(PlayerProgressService::class)->forUser($user);

    expect($progress['games'])->toHaveCount(0);
});

test('the progress page requires authentication', function () {
    $this->get(route('progress'))->assertRedirect(route('login'));
});
