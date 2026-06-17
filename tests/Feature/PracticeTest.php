<?php

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('owner can start practice on own private quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0]);
    Answer::factory()->for($question)->correct()->red()->create();
    Answer::factory()->for($question)->blue()->create();

    $this->actingAs($user)
        ->get("/quizzes/{$quiz->id}/practice")
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Practice/Play')
            ->where('quiz.id', $quiz->id)
            ->has('quiz.questions', 1)
            ->has('quiz.questions.0.answers', 2)
        );
});

test('guest can practice a published public quiz', function () {
    $quiz = Quiz::factory()->published()->public()->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0]);
    Answer::factory()->for($question)->correct()->red()->create();

    $this->get("/quizzes/{$quiz->id}/practice")
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Practice/Play'));
});

test('guest cannot practice an unpublished quiz', function () {
    $quiz = Quiz::factory()->public()->create(['is_published' => false]);

    $this->get("/quizzes/{$quiz->id}/practice")->assertForbidden();
});

test('guest cannot practice a private quiz', function () {
    $quiz = Quiz::factory()->published()->create(); // private by default

    $this->get("/quizzes/{$quiz->id}/practice")->assertForbidden();
});

test('non-owner cannot practice another users private quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->create(); // private, other owner

    $this->actingAs($user)
        ->get("/quizzes/{$quiz->id}/practice")
        ->assertForbidden();
});

test('authenticated practice result is saved', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->public()->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/practice", [
            'score' => 1500,
            'correct_count' => 8,
            'total_questions' => 10,
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('practice_sessions', [
        'quiz_id' => $quiz->id,
        'user_id' => $user->id,
        'score' => 1500,
        'correct_count' => 8,
        'total_questions' => 10,
    ]);
});

test('guest practice result is saved with null user', function () {
    $quiz = Quiz::factory()->published()->public()->create();

    $this->post("/quizzes/{$quiz->id}/practice", [
        'score' => 300,
        'correct_count' => 2,
        'total_questions' => 5,
    ])->assertSuccessful();

    $this->assertDatabaseHas('practice_sessions', [
        'quiz_id' => $quiz->id,
        'user_id' => null,
        'score' => 300,
    ]);
});

test('practice submit validates required fields', function () {
    $quiz = Quiz::factory()->published()->public()->create();

    $this->postJson("/quizzes/{$quiz->id}/practice", [
        'score' => -1,
        'total_questions' => 0,
    ])->assertJsonValidationErrors(['score', 'correct_count', 'total_questions']);
});

test('cannot submit practice result for a private quiz as guest', function () {
    $quiz = Quiz::factory()->published()->create(); // private

    $this->post("/quizzes/{$quiz->id}/practice", [
        'score' => 100,
        'correct_count' => 1,
        'total_questions' => 2,
    ])->assertForbidden();
});
