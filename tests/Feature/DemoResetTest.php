<?php

use App\Models\GameSession;
use App\Models\LlmSetting;
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

test('demo reset is idempotent and does not duplicate quizzes', function () {
    $this->artisan('demo:reset')->assertSuccessful();
    $first = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first()->quizzes()->count();

    $this->artisan('demo:reset')->assertSuccessful();
    $second = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first()->quizzes()->count();

    expect($second)->toBe($first);
});
