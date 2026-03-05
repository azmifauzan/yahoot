<?php

use App\Enums\GameStatus;
use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

function createPlayerGameSetup(): array
{
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($user)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0, 'time_limit' => 20]);
    $correctAnswer = Answer::factory()->correct()->red()->for($question)->create();
    $wrongAnswer = Answer::factory()->blue()->for($question)->create();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    return [$user, $quiz, $question, $correctAnswer, $wrongAnswer, $session];
}

test('player can view join page', function () {
    $this->get('/play')->assertSuccessful();
});

test('player can view join page with code query param', function () {
    $this->get('/play?code=123456')->assertSuccessful();
});

test('player can join a waiting game via API', function () {
    [,,,, , $session] = createPlayerGameSetup();

    $this->postJson('/api/games/join', [
        'game_code' => $session->game_code,
        'nickname' => 'TestPlayer',
        'avatar' => 'cat',
    ])->assertCreated()
        ->assertJsonStructure([
            'player' => ['id', 'nickname', 'avatar'],
            'gameSession' => ['id', 'game_code', 'status'],
        ]);

    $this->assertDatabaseHas('game_players', [
        'game_session_id' => $session->id,
        'nickname' => 'TestPlayer',
        'avatar' => 'cat',
    ]);
});

test('player cannot join with invalid game code', function () {
    $this->postJson('/api/games/join', [
        'game_code' => '000000',
        'nickname' => 'TestPlayer',
        'avatar' => 'cat',
    ])->assertUnprocessable();
});

test('player cannot join an already started game', function () {
    [,,,, , $session] = createPlayerGameSetup();
    $session->update(['status' => GameStatus::Playing]);

    $this->postJson('/api/games/join', [
        'game_code' => $session->game_code,
        'nickname' => 'TestPlayer',
        'avatar' => 'cat',
    ])->assertUnprocessable();
});

test('player cannot join with duplicate nickname', function () {
    [,,,, , $session] = createPlayerGameSetup();

    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'nickname' => 'TakenName',
        'is_connected' => true,
    ]);

    $this->postJson('/api/games/join', [
        'game_code' => $session->game_code,
        'nickname' => 'TakenName',
        'avatar' => 'cat',
    ])->assertUnprocessable();
});

test('player can submit a correct answer', function () {
    [$user, $quiz, $question, $correctAnswer, , $session] = createPlayerGameSetup();

    $session->update([
        'status' => GameStatus::Playing,
        'started_at' => now(),
        'current_question_index' => 0,
    ]);

    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 0,
        'streak' => 0,
    ]);

    $response = $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'answer_id' => $correctAnswer->id,
        'time_taken' => 5000,
    ])->assertSuccessful();

    $response->assertJson(['is_correct' => true]);

    expect($player->fresh()->score)->toBeGreaterThan(0)
        ->and($player->fresh()->streak)->toBe(1);
});

test('player can submit a wrong answer', function () {
    [$user, $quiz, $question, , $wrongAnswer, $session] = createPlayerGameSetup();

    $session->update([
        'status' => GameStatus::Playing,
        'started_at' => now(),
        'current_question_index' => 0,
    ]);

    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 0,
        'streak' => 2,
    ]);

    $response = $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'answer_id' => $wrongAnswer->id,
        'time_taken' => 5000,
    ])->assertSuccessful();

    $response->assertJson(['is_correct' => false]);

    expect($player->fresh()->score)->toBe(0)
        ->and($player->fresh()->streak)->toBe(0);
});

test('player cannot answer the same question twice', function () {
    [$user, $quiz, $question, $correctAnswer, , $session] = createPlayerGameSetup();

    $session->update([
        'status' => GameStatus::Playing,
        'started_at' => now(),
        'current_question_index' => 0,
    ]);

    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
    ]);

    $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'answer_id' => $correctAnswer->id,
        'time_taken' => 5000,
    ])->assertSuccessful();

    $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'answer_id' => $correctAnswer->id,
        'time_taken' => 3000,
    ])->assertUnprocessable();
});

test('player can get game status', function () {
    [,,,, , $session] = createPlayerGameSetup();

    $this->getJson("/api/games/{$session->id}/status")
        ->assertSuccessful()
        ->assertJsonStructure(['status', 'players']);
});

test('player can view game page with valid code', function () {
    [,,,, , $session] = createPlayerGameSetup();

    $this->get("/play/{$session->game_code}")
        ->assertSuccessful();
});
