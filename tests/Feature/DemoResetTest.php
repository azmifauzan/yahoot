<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\LlmSetting;
use App\Models\PlayerAnswer;
use App\Models\Quiz;
use App\Models\User;
use Database\Seeders\SampleQuizSeeder;

test('demo reset seeds the demo account with sample quizzes', function () {
    $this->artisan('demo:reset')->assertSuccessful();

    $demo = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first();

    expect($demo)->not->toBeNull()
        ->and($demo->quizzes()->count())->toBeGreaterThanOrEqual(5);

    $quiz = $demo->quizzes()->with('questions.answers')->first();
    expect($quiz->questions()->count())->toBeGreaterThan(0)
        ->and($quiz->questions->first()->answers()->count())->toBeGreaterThan(0);
});

test('demo reset wipes data a visitor created on the demo account', function () {
    // Seed the demo account first.
    $this->artisan('demo:reset')->assertSuccessful();
    $demo = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first();

    // Simulate visitor activity on the demo account.
    $junkQuiz = Quiz::factory()->for($demo)->create(['title' => 'Visitor Junk Quiz']);
    GameSession::factory()->for($demo, 'host')->for($junkQuiz)->create();
    LlmSetting::factory()->for($demo)->create();

    $baselineCount = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first()->quizzes()->count();

    $this->artisan('demo:reset')->assertSuccessful();

    $demo->refresh();

    expect(Quiz::where('title', 'Visitor Junk Quiz')->exists())->toBeFalse()
        ->and($demo->quizzes()->count())->toBe($baselineCount - 1)
        ->and($demo->llmSetting()->exists())->toBeFalse()
        ->and(GameSession::where('host_id', $demo->id)->count())->toBe(0);
});

test('demo reset wipes games visitors played on the demo quizzes', function () {
    $this->artisan('demo:reset')->assertSuccessful();
    $demo = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first();

    // A visitor (their own account) hosts a game on one of the demo quizzes,
    // and a guest joins and answers.
    $visitor = User::factory()->create();
    $demoQuiz = $demo->quizzes()->with('questions.answers')->first();
    $question = $demoQuiz->questions->first();

    $session = GameSession::factory()->for($visitor, 'host')->for($demoQuiz)->create();
    $player = GamePlayer::factory()->for($session)->create(['user_id' => null]);
    PlayerAnswer::factory()
        ->for($player)
        ->for($question)
        ->for($question->answers->first())
        ->create();

    $this->artisan('demo:reset')->assertSuccessful();

    expect(GameSession::whereKey($session->id)->exists())->toBeFalse()
        ->and(GamePlayer::whereKey($player->id)->exists())->toBeFalse()
        ->and(PlayerAnswer::count())->toBe(0)
        ->and($demo->quizzes()->count())->toBeGreaterThanOrEqual(5);
});

test('demo reset is idempotent and does not duplicate quizzes', function () {
    $this->artisan('demo:reset')->assertSuccessful();
    $first = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first()->quizzes()->count();

    $this->artisan('demo:reset')->assertSuccessful();
    $second = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first()->quizzes()->count();

    expect($second)->toBe($first);
});
