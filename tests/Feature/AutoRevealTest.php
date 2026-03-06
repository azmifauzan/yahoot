<?php

use App\Enums\GameStatus;
use App\Events\AnswerRevealed;
use App\Events\ScoreboardUpdated;
use App\Jobs\AutoRevealAnswer;
use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

function createAutoRevealSetup(): array
{
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($user)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0, 'time_limit' => 20]);
    $correctAnswer = Answer::factory()->correct()->red()->for($question)->create();
    $wrongAnswer = Answer::factory()->blue()->for($question)->create();

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
        'current_question_index' => 0,
        'question_started_at' => now(),
    ]);

    return [$user, $quiz, $question, $correctAnswer, $wrongAnswer, $session];
}

test('auto-reveal triggers when all players have answered', function () {
    [$user, $quiz, $question, $correctAnswer, , $session] = createAutoRevealSetup();

    $player1 = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 0,
        'streak' => 0,
        'is_connected' => true,
    ]);

    $player2 = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 0,
        'streak' => 0,
        'is_connected' => true,
    ]);

    Event::fake([AnswerRevealed::class, ScoreboardUpdated::class]);

    // Player 1 answers - should NOT trigger reveal yet
    $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player1->id,
        'answer_id' => $correctAnswer->id,
        'time_taken' => 5000,
    ])->assertSuccessful();

    expect($session->fresh()->status)->toBe(GameStatus::Playing);
    Event::assertNotDispatched(AnswerRevealed::class);

    // Player 2 answers - should trigger auto-reveal
    $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player2->id,
        'answer_id' => $correctAnswer->id,
        'time_taken' => 3000,
    ])->assertSuccessful();

    expect($session->fresh()->status)->toBe(GameStatus::Reviewing);
    Event::assertDispatched(AnswerRevealed::class);
    Event::assertDispatched(ScoreboardUpdated::class);
});

test('auto-reveal does not trigger when some players have not answered', function () {
    [$user, $quiz, $question, $correctAnswer, , $session] = createAutoRevealSetup();

    $player1 = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 0,
        'streak' => 0,
        'is_connected' => true,
    ]);

    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 0,
        'streak' => 0,
        'is_connected' => true,
    ]);

    Event::fake([AnswerRevealed::class]);

    $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player1->id,
        'answer_id' => $correctAnswer->id,
        'time_taken' => 5000,
    ])->assertSuccessful();

    expect($session->fresh()->status)->toBe(GameStatus::Playing);
    Event::assertNotDispatched(AnswerRevealed::class);
});

test('AutoRevealAnswer job reveals answer for active question', function () {
    [$user, $quiz, $question, , , $session] = createAutoRevealSetup();

    GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'is_connected' => true,
    ]);

    Event::fake([AnswerRevealed::class, ScoreboardUpdated::class]);

    $job = new AutoRevealAnswer($session->id, 0);
    $job->handle(app(\App\Services\RevealService::class));

    expect($session->fresh()->status)->toBe(GameStatus::Reviewing);
    Event::assertDispatched(AnswerRevealed::class);
});

test('AutoRevealAnswer job skips if question already revealed', function () {
    [$user, $quiz, $question, , , $session] = createAutoRevealSetup();
    $session->update(['status' => GameStatus::Reviewing]);

    Event::fake([AnswerRevealed::class]);

    $job = new AutoRevealAnswer($session->id, 0);
    $job->handle(app(\App\Services\RevealService::class));

    expect($session->fresh()->status)->toBe(GameStatus::Reviewing);
    Event::assertNotDispatched(AnswerRevealed::class);
});

test('AutoRevealAnswer job skips if question index changed', function () {
    [$user, $quiz, $question, , , $session] = createAutoRevealSetup();

    Event::fake([AnswerRevealed::class]);

    // Job dispatched for question 0, but game moved to question 1
    $job = new AutoRevealAnswer($session->id, 1);
    $job->handle(app(\App\Services\RevealService::class));

    expect($session->fresh()->status)->toBe(GameStatus::Playing);
    Event::assertNotDispatched(AnswerRevealed::class);
});

test('AutoRevealAnswer job is dispatched when question starts', function () {
    Bus::fake(AutoRevealAnswer::class);

    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($user)->create();
    Question::factory()->for($quiz)->create(['order' => 0, 'time_limit' => 20]);
    Answer::factory()->correct()->red()->for($quiz->questions->first())->create();

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $user->id,
    ]);
    GamePlayer::factory()->create(['game_session_id' => $session->id]);

    $this->actingAs($user)
        ->post(route('game.start', $session))
        ->assertRedirect();

    Bus::assertDispatched(AutoRevealAnswer::class, function ($job) use ($session) {
        return $job->gameSessionId === $session->id
            && $job->questionIndex === 0;
    });
});
