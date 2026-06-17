<?php

use App\Models\BankQuestion;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function bankPayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'multiple_choice',
        'question_text' => 'What is the capital of Japan?',
        'time_limit' => 20,
        'points' => 'standard',
        'answers' => [
            ['answer_text' => 'Tokyo', 'is_correct' => true, 'color' => 'red'],
            ['answer_text' => 'Osaka', 'is_correct' => false, 'color' => 'blue'],
        ],
    ], $overrides);
}

test('guests cannot access the question bank', function () {
    $this->get('/question-bank')->assertRedirect('/login');
});

test('user sees only their own bank questions', function () {
    $user = User::factory()->create();
    BankQuestion::factory()->for($user)->create(['question_text' => 'Mine']);
    BankQuestion::factory()->create(['question_text' => 'Theirs']);

    $response = $this->actingAs($user)->get('/question-bank')->assertSuccessful();

    $items = $response->original->getData()['page']['props']['items'];
    expect($items)->toHaveCount(1)
        ->and($items[0]['question_text'])->toBe('Mine');
});

test('user can save a question to the bank', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/question-bank', bankPayload())
        ->assertRedirect();

    $item = BankQuestion::where('user_id', $user->id)->first();
    expect($item)->not->toBeNull()
        ->and($item->question_text)->toBe('What is the capital of Japan?')
        ->and($item->answers)->toHaveCount(2)
        ->and($item->answers[0]['answer_text'])->toBe('Tokyo');
});

test('saving a bank question requires text and answers', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/question-bank', ['type' => 'multiple_choice'])
        ->assertJsonValidationErrors(['question_text', 'answers']);
});

test('user can delete their own bank question', function () {
    $user = User::factory()->create();
    $item = BankQuestion::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete("/question-bank/{$item->id}")
        ->assertRedirect();

    $this->assertModelMissing($item);
});

test('user cannot delete another users bank question', function () {
    $user = User::factory()->create();
    $item = BankQuestion::factory()->create();

    $this->actingAs($user)
        ->delete("/question-bank/{$item->id}")
        ->assertForbidden();
});

test('user can pull bank questions into their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $a = BankQuestion::factory()->for($user)->create();
    $b = BankQuestion::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/from-bank", ['item_ids' => [$a->id, $b->id]])
        ->assertRedirect();

    $quiz->refresh()->load('questions.answers');
    expect($quiz->questions)->toHaveCount(2)
        ->and($quiz->questions->first()->answers->count())->toBeGreaterThan(0);
});

test('pulling bank questions appends after existing questions', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    Question::factory()->for($quiz)->create(['order' => 0]);
    $item = BankQuestion::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/from-bank", ['item_ids' => [$item->id]])
        ->assertRedirect();

    $orders = $quiz->refresh()->questions->pluck('order')->sort()->values()->toArray();
    expect($orders)->toBe([0, 1]);
});

test('user cannot pull bank questions into another users quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->create(); // other owner
    $item = BankQuestion::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/from-bank", ['item_ids' => [$item->id]])
        ->assertForbidden();
});

test('user cannot pull another users bank questions', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $item = BankQuestion::factory()->create(); // other owner

    $this->actingAs($user)
        ->post("/quizzes/{$quiz->id}/from-bank", ['item_ids' => [$item->id]])
        ->assertRedirect();

    expect($quiz->refresh()->questions)->toHaveCount(0);
});

test('bank list endpoint returns only own items as json', function () {
    $user = User::factory()->create();
    BankQuestion::factory()->for($user)->create(['question_text' => 'Mine']);
    BankQuestion::factory()->create(['question_text' => 'Theirs']);

    $this->actingAs($user)
        ->getJson('/question-bank/list')
        ->assertSuccessful()
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('items.0.question_text', 'Mine');
});

test('bank index filters by type and category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    BankQuestion::factory()->for($user)->create(['type' => 'multiple_choice', 'category_id' => $category->id]);
    BankQuestion::factory()->for($user)->trueFalse()->create();

    $response = $this->actingAs($user)
        ->get('/question-bank?type=true_false')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('items', 1));

    $items = $response->original->getData()['page']['props']['items'];
    expect($items[0]['type'])->toBe('true_false');
});
