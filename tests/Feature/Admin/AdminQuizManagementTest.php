<?php

use App\Models\Quiz;
use App\Models\User;

test('admin can list all quizzes', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.quizzes.index'))
        ->assertSuccessful();
});

test('non-admin cannot list quizzes in admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.quizzes.index'))
        ->assertForbidden();
});

test('guest is redirected from admin quizzes', function () {
    $this->get(route('admin.quizzes.index'))
        ->assertRedirect(route('login'));
});

test('admin can view quiz detail', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $quiz = Quiz::factory()->for(User::factory())->create();

    $this->actingAs($admin)
        ->get(route('admin.quizzes.show', $quiz))
        ->assertSuccessful();
});

test('admin can force delete a quiz', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $quiz = Quiz::factory()->for(User::factory())->create();

    $this->actingAs($admin)
        ->delete(route('admin.quizzes.destroy', $quiz->id))
        ->assertRedirect(route('admin.quizzes.index'));

    $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
});

test('admin can force delete a soft-deleted quiz', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $quiz = Quiz::factory()->for(User::factory())->create();
    $quiz->delete();

    $this->actingAs($admin)
        ->delete(route('admin.quizzes.destroy', $quiz->id))
        ->assertRedirect(route('admin.quizzes.index'));

    $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
});

test('admin can restore a soft-deleted quiz', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $quiz = Quiz::factory()->for(User::factory())->create();
    $quiz->delete();

    $this->assertSoftDeleted($quiz);

    $this->actingAs($admin)
        ->post(route('admin.quizzes.restore', $quiz->id));

    $this->assertNotSoftDeleted($quiz);
});

test('admin can filter trashed quizzes', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $activeQuiz = Quiz::factory()->for(User::factory())->create();
    $deletedQuiz = Quiz::factory()->for(User::factory())->create();
    $deletedQuiz->delete();

    $response = $this->actingAs($admin)
        ->get(route('admin.quizzes.index', ['filter' => 'trashed']))
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes']['data'];
    $ids = collect($quizzes)->pluck('id');
    expect($ids)->toContain($deletedQuiz->id)
        ->not->toContain($activeQuiz->id);
});
