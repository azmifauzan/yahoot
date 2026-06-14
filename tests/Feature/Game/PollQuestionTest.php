<?php

use App\Enums\QuestionType;
use App\Jobs\AutoRevealAnswer;
use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\RevealService;
use Illuminate\Support\Facades\Bus;

test('answering a poll question gives no points and does not change streak', function () {
    Bus::fake(AutoRevealAnswer::class);

    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $question = Question::factory()->for($quiz)->create([
        'order' => 0,
        'time_limit' => 20,
        'type' => QuestionType::Poll,
    ]);
    $optionA = Answer::factory()->red()->for($question)->create(['is_correct' => false]);
    Answer::factory()->blue()->for($question)->create(['is_correct' => false]);

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
        'current_question_index' => 0,
        'question_started_at' => now(),
    ]);

    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 500,
        'streak' => 2,
    ]);

    $response = $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'answer_id' => $optionA->id,
        'time_taken' => 5000,
    ])->assertSuccessful();

    expect($response->json('points_earned'))->toBe(0)
        ->and($response->json('streak_bonus'))->toBe(0)
        ->and($response->json('streak'))->toBe(2)
        ->and($response->json('total_score'))->toBe(500);

    $this->assertDatabaseHas('player_answers', [
        'game_player_id' => $player->id,
        'question_id' => $question->id,
        'answer_id' => $optionA->id,
        'is_correct' => false,
    ]);

    expect($player->fresh()->streak)->toBe(2)
        ->and($player->fresh()->score)->toBe(500);
});

test('reveal data for a poll question has no correct answer but counts votes', function () {
    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $question = Question::factory()->for($quiz)->create([
        'order' => 0,
        'time_limit' => 20,
        'type' => QuestionType::Poll,
    ]);
    $optionA = Answer::factory()->red()->for($question)->create(['is_correct' => false]);
    Answer::factory()->blue()->for($question)->create(['is_correct' => false]);

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
        'current_question_index' => 0,
        'question_started_at' => now(),
    ]);

    $player = GamePlayer::factory()->create(['game_session_id' => $session->id]);

    $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'answer_id' => $optionA->id,
        'time_taken' => 5000,
    ])->assertSuccessful();

    $question->load('answers');

    $data = app(RevealService::class)->getRevealData($session->fresh(), $question);

    expect($data['isPoll'])->toBeTrue()
        ->and($data['correctAnswer']['answers'])->toBe([]);

    $voteCount = collect($data['stats']['answer_counts'])
        ->firstWhere('answer_id', $optionA->id)['count'];

    expect($voteCount)->toBe(1);
});
