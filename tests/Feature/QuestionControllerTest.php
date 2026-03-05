<?php

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

test('users can add a question to their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/questions", [
            'type' => 'multiple_choice',
            'question_text' => 'What is 2+2?',
            'time_limit' => 20,
            'points' => 'standard',
            'answers' => [
                ['answer_text' => '4', 'is_correct' => true, 'color' => 'red'],
                ['answer_text' => '5', 'is_correct' => false, 'color' => 'blue'],
            ],
        ])
        ->assertRedirect();

    expect($quiz->questions()->count())->toBe(1)
        ->and($quiz->questions->first()->question_text)->toBe('What is 2+2?')
        ->and($quiz->questions->first()->answers)->toHaveCount(2);
});

test('users cannot add questions to other users quizzes', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $quiz = Quiz::factory()->for($other)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/questions", [
            'type' => 'multiple_choice',
            'question_text' => 'Hacked?',
            'time_limit' => 20,
            'points' => 'standard',
            'answers' => [
                ['answer_text' => 'A', 'is_correct' => true, 'color' => 'red'],
                ['answer_text' => 'B', 'is_correct' => false, 'color' => 'blue'],
            ],
        ])
        ->assertForbidden();
});

test('question requires valid type', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/questions", [
            'type' => 'invalid_type',
            'question_text' => 'Test?',
            'time_limit' => 20,
            'points' => 'standard',
            'answers' => [
                ['answer_text' => 'A', 'is_correct' => true, 'color' => 'red'],
                ['answer_text' => 'B', 'is_correct' => false, 'color' => 'blue'],
            ],
        ])
        ->assertSessionHasErrors('type');
});

test('question requires at least 2 answers', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/questions", [
            'type' => 'multiple_choice',
            'question_text' => 'Test?',
            'time_limit' => 20,
            'points' => 'standard',
            'answers' => [
                ['answer_text' => 'A', 'is_correct' => true, 'color' => 'red'],
            ],
        ])
        ->assertSessionHasErrors('answers');
});

test('question time limit must be valid', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/questions", [
            'type' => 'multiple_choice',
            'question_text' => 'Test?',
            'time_limit' => 15, // not a valid option
            'points' => 'standard',
            'answers' => [
                ['answer_text' => 'A', 'is_correct' => true, 'color' => 'red'],
                ['answer_text' => 'B', 'is_correct' => false, 'color' => 'blue'],
            ],
        ])
        ->assertSessionHasErrors('time_limit');
});

test('users can update their question', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $question = Question::factory()->for($quiz)->create(['question_text' => 'Old?']);
    Answer::factory()->for($question)->correct()->red()->create(['answer_text' => 'A']);
    Answer::factory()->for($question)->blue()->create(['answer_text' => 'B']);

    $this->actingAs($user)
        ->put("/questions/{$question->id}", [
            'type' => 'multiple_choice',
            'question_text' => 'Updated?',
            'time_limit' => 30,
            'points' => 'double',
            'answers' => [
                ['id' => $question->answers[0]->id, 'answer_text' => 'A updated', 'is_correct' => true, 'color' => 'red'],
                ['id' => $question->answers[1]->id, 'answer_text' => 'B updated', 'is_correct' => false, 'color' => 'blue'],
            ],
        ])
        ->assertRedirect();

    $question->refresh();
    expect($question->question_text)->toBe('Updated?')
        ->and($question->time_limit)->toBe(30);
});

test('users cannot update other users questions', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $quiz = Quiz::factory()->for($other)->create();
    $question = Question::factory()->for($quiz)->create();
    Answer::factory()->for($question)->correct()->red()->create();
    Answer::factory()->for($question)->blue()->create();

    $this->actingAs($user)
        ->put("/questions/{$question->id}", [
            'question_text' => 'Hacked!',
        ])
        ->assertForbidden();
});

test('users can delete their question', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $question = Question::factory()->for($quiz)->create();

    $this->actingAs($user)
        ->delete("/questions/{$question->id}")
        ->assertRedirect();

    expect(Question::find($question->id))->toBeNull();
});

test('users cannot delete other users questions', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $quiz = Quiz::factory()->for($other)->create();
    $question = Question::factory()->for($quiz)->create();

    $this->actingAs($user)
        ->delete("/questions/{$question->id}")
        ->assertForbidden();
});

test('users can reorder questions', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $q1 = Question::factory()->for($quiz)->create(['order' => 0]);
    $q2 = Question::factory()->for($quiz)->create(['order' => 1]);
    $q3 = Question::factory()->for($quiz)->create(['order' => 2]);

    $this->actingAs($user)
        ->post('/questions/reorder', [
            'questions' => [
                ['id' => $q3->id, 'order' => 0],
                ['id' => $q1->id, 'order' => 1],
                ['id' => $q2->id, 'order' => 2],
            ],
        ])
        ->assertRedirect();

    expect($q3->fresh()->order)->toBe(0)
        ->and($q1->fresh()->order)->toBe(1)
        ->and($q2->fresh()->order)->toBe(2);
});

test('question order auto-increments', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    // Add first question
    $this->actingAs($user)->post("/quizzes/{$quiz->id}/questions", [
        'type' => 'multiple_choice',
        'question_text' => 'First?',
        'time_limit' => 20,
        'points' => 'standard',
        'answers' => [
            ['answer_text' => 'A', 'is_correct' => true, 'color' => 'red'],
            ['answer_text' => 'B', 'is_correct' => false, 'color' => 'blue'],
        ],
    ]);

    // Add second question
    $this->actingAs($user)->post("/quizzes/{$quiz->id}/questions", [
        'type' => 'multiple_choice',
        'question_text' => 'Second?',
        'time_limit' => 20,
        'points' => 'standard',
        'answers' => [
            ['answer_text' => 'A', 'is_correct' => true, 'color' => 'red'],
            ['answer_text' => 'B', 'is_correct' => false, 'color' => 'blue'],
        ],
    ]);

    $questions = $quiz->questions()->orderBy('order')->get();
    expect($questions[0]->order)->toBe(0)
        ->and($questions[1]->order)->toBe(1);
});

test('can add true false question', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/questions", [
            'type' => 'true_false',
            'question_text' => 'Is the sky blue?',
            'time_limit' => 10,
            'points' => 'standard',
            'answers' => [
                ['answer_text' => 'True', 'is_correct' => true, 'color' => 'blue'],
                ['answer_text' => 'False', 'is_correct' => false, 'color' => 'red'],
            ],
        ])
        ->assertRedirect();

    $question = $quiz->questions->first();
    expect($question->type->value)->toBe('true_false')
        ->and($question->answers)->toHaveCount(2);
});
