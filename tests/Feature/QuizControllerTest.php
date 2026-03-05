<?php

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

test('guests cannot access dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful();
});

test('dashboard shows user quizzes', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['title' => 'My Quiz']);
    Quiz::factory()->create(['title' => 'Other Quiz']); // another user's quiz

    $response = $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes)->toHaveCount(1)
        ->and($quizzes[0]['title'])->toBe('My Quiz');
});

test('dashboard filters by draft', function () {
    $user = User::factory()->create();
    Quiz::factory()->for($user)->create(['is_published' => false]);
    Quiz::factory()->for($user)->published()->create();

    $response = $this->actingAs($user)
        ->get('/dashboard?filter=draft')
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes)->toHaveCount(1)
        ->and($quizzes[0]['is_published'])->toBeFalse();
});

test('dashboard filters by published', function () {
    $user = User::factory()->create();
    Quiz::factory()->for($user)->create(['is_published' => false]);
    Quiz::factory()->for($user)->published()->create();

    $response = $this->actingAs($user)
        ->get('/dashboard?filter=published')
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes)->toHaveCount(1)
        ->and($quizzes[0]['is_published'])->toBeTrue();
});

test('dashboard searches by title', function () {
    $user = User::factory()->create();
    Quiz::factory()->for($user)->create(['title' => 'Science Quiz']);
    Quiz::factory()->for($user)->create(['title' => 'Math Quiz']);

    $response = $this->actingAs($user)
        ->get('/dashboard?search=Science')
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes)->toHaveCount(1)
        ->and($quizzes[0]['title'])->toBe('Science Quiz');
});

test('guests cannot access quiz create page', function () {
    $this->get('/quizzes/create')->assertRedirect('/login');
});

test('authenticated users can access quiz create page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/quizzes/create')
        ->assertSuccessful();
});

test('users can create a quiz', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/quizzes', [
            'title' => 'New Quiz',
            'description' => 'A test quiz',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('quizzes', [
        'user_id' => $user->id,
        'title' => 'New Quiz',
        'description' => 'A test quiz',
        'is_published' => false,
    ]);
});

test('quiz title is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/quizzes', ['title' => ''])
        ->assertSessionHasErrors('title');
});

test('users can edit their own quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->get("/quizzes/{$quiz->id}/edit")
        ->assertSuccessful();
});

test('users cannot edit other users quizzes', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $quiz = Quiz::factory()->for($other)->create();

    $this->actingAs($user)
        ->get("/quizzes/{$quiz->id}/edit")
        ->assertForbidden();
});

test('users can update their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['title' => 'Old Title']);

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", ['title' => 'New Title'])
        ->assertRedirect();

    expect($quiz->fresh()->title)->toBe('New Title');
});

test('users cannot update other users quizzes', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $quiz = Quiz::factory()->for($other)->create();

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", ['title' => 'Hacked'])
        ->assertForbidden();
});

test('users can delete their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete("/quizzes/{$quiz->id}")
        ->assertRedirect();

    expect(Quiz::withTrashed()->find($quiz->id)->trashed())->toBeTrue();
});

test('users cannot delete other users quizzes', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $quiz = Quiz::factory()->for($other)->create();

    $this->actingAs($user)
        ->delete("/quizzes/{$quiz->id}")
        ->assertForbidden();
});

test('users can duplicate their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['title' => 'Original Quiz']);

    $question = Question::factory()->for($quiz)->create([
        'question_text' => 'Test question?',
        'order' => 0,
    ]);
    Answer::factory()->for($question)->correct()->red()->create(['answer_text' => 'Yes']);
    Answer::factory()->for($question)->blue()->create(['answer_text' => 'No']);

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/duplicate")
        ->assertRedirect();

    $duplicated = Quiz::where('title', 'Original Quiz (Salinan)')->first();
    expect($duplicated)->not->toBeNull()
        ->and($duplicated->is_published)->toBeFalse()
        ->and($duplicated->questions)->toHaveCount(1)
        ->and($duplicated->questions->first()->answers)->toHaveCount(2);
});

test('users can publish a valid quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['is_published' => false]);

    $question = Question::factory()->for($quiz)->create([
        'question_text' => 'What is 1+1?',
        'order' => 0,
    ]);
    Answer::factory()->for($question)->correct()->red()->create(['answer_text' => '2']);
    Answer::factory()->for($question)->blue()->create(['answer_text' => '3']);

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/publish")
        ->assertRedirect();

    expect($quiz->fresh()->is_published)->toBeTrue();
});

test('users can unpublish a published quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->published()->create();

    $question = Question::factory()->for($quiz)->create([
        'question_text' => 'What is 1+1?',
        'order' => 0,
    ]);
    Answer::factory()->for($question)->correct()->red()->create(['answer_text' => '2']);
    Answer::factory()->for($question)->blue()->create(['answer_text' => '3']);

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/publish")
        ->assertRedirect();

    expect($quiz->fresh()->is_published)->toBeFalse();
});

test('cannot publish quiz without questions', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['is_published' => false]);

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/publish")
        ->assertRedirect()
        ->assertSessionHasErrors('publish');
});

test('cannot publish quiz with question missing text', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['is_published' => false]);

    $question = Question::factory()->for($quiz)->create([
        'question_text' => '',
        'order' => 0,
    ]);
    Answer::factory()->for($question)->correct()->red()->create(['answer_text' => 'A']);
    Answer::factory()->for($question)->blue()->create(['answer_text' => 'B']);

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/publish")
        ->assertRedirect()
        ->assertSessionHasErrors('publish');
});

test('cannot publish quiz with question missing correct answer', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['is_published' => false]);

    $question = Question::factory()->for($quiz)->create([
        'question_text' => 'What?',
        'order' => 0,
    ]);
    Answer::factory()->for($question)->red()->create(['answer_text' => 'A', 'is_correct' => false]);
    Answer::factory()->for($question)->blue()->create(['answer_text' => 'B', 'is_correct' => false]);

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/publish")
        ->assertRedirect()
        ->assertSessionHasErrors('publish');
});
