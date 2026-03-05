<?php

use App\Enums\GameStatus;
use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

function createGameSetup(): array
{
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($user)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0, 'time_limit' => 20]);
    Answer::factory()->correct()->red()->for($question)->create();
    Answer::factory()->blue()->for($question)->create();

    return [$user, $quiz, $question];
}

test('host can create a game session', function () {
    [$user, $quiz] = createGameSetup();

    $this->actingAs($user)
        ->post(route('game.store', $quiz))
        ->assertRedirect();

    $this->assertDatabaseHas('game_sessions', [
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
        'status' => GameStatus::Waiting->value,
    ]);
});

test('non-owner cannot create a game session', function () {
    [$user, $quiz] = createGameSetup();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->post(route('game.store', $quiz))
        ->assertForbidden();
});

test('unpublished quiz cannot start a game', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['is_published' => false]);
    Question::factory()->for($quiz)->create();

    $this->actingAs($user)
        ->post(route('game.store', $quiz))
        ->assertSessionHasErrors('quiz');
});

test('quiz without questions cannot start a game', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($user)->create();

    $this->actingAs($user)
        ->post(route('game.store', $quiz))
        ->assertSessionHasErrors('quiz');
});

test('host can view game lobby', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('game.host', $session))
        ->assertSuccessful();
});

test('non-host cannot view game lobby', function () {
    $session = GameSession::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get(route('game.host', $session))
        ->assertForbidden();
});

test('host can start a game with players', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);
    GamePlayer::factory()->create(['game_session_id' => $session->id]);

    $this->actingAs($user)
        ->post(route('game.start', $session))
        ->assertRedirect();

    expect($session->fresh()->status)->toBe(GameStatus::Playing);
});

test('host cannot start game without players', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('game.start', $session))
        ->assertSessionHasErrors('game');
});

test('host cannot start game that already started', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('game.start', $session))
        ->assertSessionHasErrors('game');
});

test('host can reveal answer', function () {
    [$user, $quiz, $question] = createGameSetup();

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
        'current_question_index' => 0,
    ]);

    $this->actingAs($user)
        ->post(route('game.reveal', $session))
        ->assertRedirect();

    expect($session->fresh()->status)->toBe(GameStatus::Reviewing);
});

test('host can end game', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('game.end', $session))
        ->assertRedirect();

    expect($session->fresh()->status)->toBe(GameStatus::Finished)
        ->and($session->fresh()->finished_at)->not->toBeNull();
});

test('host can view results page', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->finished()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('game.results', $session))
        ->assertSuccessful();
});

test('host can export CSV results', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->finished()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('game.export', $session))
        ->assertSuccessful()
        ->assertDownload();
});

test('game code is unique 6 digits', function () {
    [$user, $quiz] = createGameSetup();

    $this->actingAs($user)
        ->post(route('game.store', $quiz));

    $session = GameSession::query()->latest('id')->first();

    expect($session->game_code)->toHaveLength(6)
        ->and($session->game_code)->toMatch('/^\d{6}$/');
});
