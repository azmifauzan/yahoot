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

test('host lobby has no resume state when waiting', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('game.host', $session))
        ->assertInertia(fn ($page) => $page->where('resumeState', null));
});

test('host can resume an in-progress question', function () {
    [$user, $quiz, $question] = createGameSetup();

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
        'current_question_index' => 0,
        'question_started_at' => now()->subSeconds(5),
    ]);

    $this->actingAs($user)
        ->get(route('game.host', $session))
        ->assertInertia(fn ($page) => $page
            ->where('resumeState.status', 'playing')
            ->where('resumeState.question.id', $question->id)
            ->where('resumeState.questionNumber', 1)
            ->where('resumeState.timeLimit', $question->time_limit)
        );
});

test('host can resume a reviewing question with reveal data', function () {
    [$user, $quiz, $question] = createGameSetup();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
        'status' => GameStatus::Reviewing,
        'current_question_index' => 0,
        'question_started_at' => now()->subSeconds(25),
    ]);
    GamePlayer::factory()->create(['game_session_id' => $session->id]);

    $this->actingAs($user)
        ->get(route('game.host', $session))
        ->assertInertia(fn ($page) => $page
            ->where('resumeState.status', 'reviewing')
            ->has('resumeState.reveal.correctAnswer')
            ->has('resumeState.reveal.leaderboard')
        );
});

test('non-host cannot view game lobby', function () {
    $session = GameSession::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get(route('game.host', $session))
        ->assertForbidden();
});

test('host can start a game with players', function () {
    Bus::fake(AutoRevealAnswer::class);

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

    Bus::assertDispatched(AutoRevealAnswer::class);
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

test('host cannot start game with only disconnected players', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);
    GamePlayer::factory()->disconnected()->create(['game_session_id' => $session->id]);

    $this->actingAs($user)
        ->post(route('game.start', $session))
        ->assertSessionHasErrors('game');
});

test('host lobby does not show disconnected players', function () {
    [$user, $quiz] = createGameSetup();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);
    $connected = GamePlayer::factory()->create(['game_session_id' => $session->id, 'nickname' => 'Connected']);
    GamePlayer::factory()->disconnected()->create(['game_session_id' => $session->id, 'nickname' => 'Disconnected']);

    $this->actingAs($user)
        ->get(route('game.host', $session))
        ->assertInertia(fn ($page) => $page
            ->has('players', 1)
            ->where('players.0.id', $connected->id)
        );
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

test('host can view quiz game history', function () {
    [$user, $quiz] = createGameSetup();

    $finished = GameSession::factory()->finished()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);
    GamePlayer::factory()->create(['game_session_id' => $finished->id, 'nickname' => 'Winner', 'score' => 100]);
    GamePlayer::factory()->create(['game_session_id' => $finished->id, 'nickname' => 'Loser', 'score' => 10]);

    // An in-progress session for the same quiz should still appear, as resumable
    $playing = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('quizzes.history', $quiz))
        ->assertInertia(fn ($page) => $page
            ->component('Host/History')
            ->has('sessions', 2)
            ->where('sessions.0.id', $playing->id)
            ->where('sessions.0.status', 'playing')
            ->where('sessions.1.id', $finished->id)
            ->where('sessions.1.status', 'finished')
            ->where('sessions.1.players_count', 2)
            ->where('sessions.1.winner.nickname', 'Winner')
        );
});

test('non-owner cannot view quiz game history', function () {
    [$user, $quiz] = createGameSetup();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get(route('quizzes.history', $quiz))
        ->assertForbidden();
});

test('game code is unique 6 digits', function () {
    [$user, $quiz] = createGameSetup();

    $this->actingAs($user)
        ->post(route('game.store', $quiz));

    $session = GameSession::query()->latest('id')->first();

    expect($session->game_code)->toHaveLength(6)
        ->and($session->game_code)->toMatch('/^\d{6}$/');
});
