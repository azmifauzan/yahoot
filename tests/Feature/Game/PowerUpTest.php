<?php

use App\Enums\PointType;
use App\Jobs\AutoRevealAnswer;
use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

function createPowerUpSetup(array $settings = [], int $wrongCount = 3): array
{
    Bus::fake(AutoRevealAnswer::class);

    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $question = Question::factory()->for($quiz)->create([
        'order' => 0,
        'time_limit' => 20,
        'points' => PointType::Standard,
    ]);
    $correct = Answer::factory()->correct()->red()->for($question)->create();
    $wrong = collect(range(1, $wrongCount))->map(
        fn () => Answer::factory()->blue()->for($question)->create(['is_correct' => false])
    );

    $session = GameSession::factory()->playing()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
        'current_question_index' => 0,
        'question_started_at' => now(),
        'settings' => array_merge(['powerups_enabled' => true], $settings),
    ]);

    $player = GamePlayer::factory()->create(['game_session_id' => $session->id]);

    return [$session, $player, $question, $correct, $wrong];
}

test('double points powerup doubles the score on the next answer', function () {
    [$session, $player, $question, $correct] = createPowerUpSetup();

    $this->postJson("/api/games/{$session->id}/powerup", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'powerup' => 'double_points',
        'question_id' => $question->id,
    ])->assertSuccessful();

    $response = $this->postJson("/api/games/{$session->id}/answer", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'answer_id' => $correct->id,
        'time_taken' => 0,
    ])->assertSuccessful();

    // base 1000 doubled = 2000
    expect($response->json('points_earned'))->toBe(2000);

    $this->assertDatabaseHas('player_answers', [
        'game_player_id' => $player->id,
        'question_id' => $question->id,
        'powerup_used' => 'double_points',
    ]);
});

test('fifty-fifty powerup returns exactly two wrong answer ids', function () {
    [$session, $player, $question, $correct, $wrong] = createPowerUpSetup();

    $response = $this->postJson("/api/games/{$session->id}/powerup", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'powerup' => 'fifty_fifty',
        'question_id' => $question->id,
    ])->assertSuccessful();

    $hidden = $response->json('hidden_answers');

    expect($hidden)->toHaveCount(2);
    expect($wrong->pluck('id')->all())->toContain(...$hidden);
    expect($hidden)->not->toContain($correct->id);
});

test('a powerup can only be used once per game', function () {
    [$session, $player, $question] = createPowerUpSetup();

    $this->postJson("/api/games/{$session->id}/powerup", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'powerup' => 'double_points',
        'question_id' => $question->id,
    ])->assertSuccessful();

    $this->postJson("/api/games/{$session->id}/powerup", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'powerup' => 'double_points',
        'question_id' => $question->id,
    ])->assertUnprocessable();
});

test('powerups are rejected when disabled via session settings', function () {
    [$session, $player, $question] = createPowerUpSetup(['powerups_enabled' => false]);

    $this->postJson("/api/games/{$session->id}/powerup", [
        'player_id' => $player->id,
        'player_token' => $player->player_token,
        'powerup' => 'double_points',
        'question_id' => $question->id,
    ])->assertForbidden();
});

test('powerup with invalid player token is rejected', function () {
    [$session, $player, $question] = createPowerUpSetup();

    $this->postJson("/api/games/{$session->id}/powerup", [
        'player_id' => $player->id,
        'player_token' => 'nope',
        'powerup' => 'double_points',
        'question_id' => $question->id,
    ])->assertForbidden();
});
