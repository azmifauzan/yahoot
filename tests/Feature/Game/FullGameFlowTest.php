<?php

use App\Enums\GameStatus;
use App\Jobs\AutoRevealAnswer;
use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

test('full game flow: host creates session, player joins, host starts, ends game', function () {
    Bus::fake(AutoRevealAnswer::class);

    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0, 'time_limit' => 20]);
    Answer::factory()->correct()->red()->for($question)->create();
    Answer::factory()->blue()->for($question)->create();

    // Host creates session
    $response = $this->actingAs($host)
        ->post(route('game.store', $quiz));
    $response->assertRedirect();

    $session = GameSession::query()->latest('id')->first();
    expect($session)->not->toBeNull()
        ->and($session->status)->toBe(GameStatus::Waiting);

    // Player joins via API
    $joinResponse = $this->postJson('/api/games/join', [
        'game_code' => $session->game_code,
        'nickname' => 'TestPlayer',
        'avatar' => 'cat',
    ]);
    $joinResponse->assertSuccessful();
    $playerId = $joinResponse->json('player.id');

    expect(GamePlayer::find($playerId))->not->toBeNull();

    // Host starts the game
    $this->actingAs($host)
        ->post(route('game.start', $session))
        ->assertRedirect();

    expect($session->fresh()->status)->toBe(GameStatus::Playing);

    Bus::assertDispatched(AutoRevealAnswer::class);

    // Player checks status
    $this->getJson("/api/games/{$session->id}/status?player_id={$playerId}")
        ->assertSuccessful()
        ->assertJsonPath('status', GameStatus::Playing->value);

    // Host ends the game
    $this->actingAs($host)
        ->post(route('game.end', $session))
        ->assertRedirect();

    expect($session->fresh()->status)->toBe(GameStatus::Finished);

    // Host views results
    $this->actingAs($host)
        ->get(route('game.results', $session))
        ->assertSuccessful();
});

test('host cannot access another hosts game session', function () {
    $host = User::factory()->create();
    $other = User::factory()->create();
    $session = GameSession::factory()->create(['host_id' => $host->id]);

    $this->actingAs($other)
        ->get(route('game.host', $session))
        ->assertForbidden();
});

test('game session policy covers all host actions', function () {
    $host = User::factory()->create();
    $other = User::factory()->create();
    $session = GameSession::factory()->playing()->create(['host_id' => $host->id]);

    $actions = [
        fn () => $this->actingAs($other)->post(route('game.start', $session)),
        fn () => $this->actingAs($other)->post(route('game.reveal', $session)),
        fn () => $this->actingAs($other)->post(route('game.end', $session)),
    ];

    foreach ($actions as $action) {
        $action()->assertForbidden();
    }
});

test('player can answer question', function () {
    Bus::fake(AutoRevealAnswer::class);

    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0, 'time_limit' => 20]);
    $correctAnswer = Answer::factory()->correct()->red()->for($question)->create();
    Answer::factory()->blue()->for($question)->create();

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
        'current_question_index' => 0,
        'question_started_at' => now(),
    ]);

    $player = GamePlayer::factory()->create(['game_session_id' => $session->id]);

    $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'question_id' => $question->id,
        'answer_id' => $correctAnswer->id,
    ])->assertSuccessful();

    $this->assertDatabaseHas('player_answers', [
        'game_player_id' => $player->id,
        'question_id' => $question->id,
        'is_correct' => true,
    ]);
});
