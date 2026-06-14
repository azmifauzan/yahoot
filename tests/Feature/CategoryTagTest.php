<?php

use App\Models\Category;
use App\Models\Quiz;
use App\Models\Tag;
use App\Models\User;

test('users can assign a category and tags to their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $category = Category::factory()->create(['name' => 'Math']);

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'category_id' => $category->id,
            'tags' => ['fun', 'school'],
        ])
        ->assertRedirect();

    $quiz->refresh();
    expect($quiz->category_id)->toBe($category->id)
        ->and($quiz->tags->pluck('name')->sort()->values()->all())->toBe(['fun', 'school']);
});

test('assigning a tag does not duplicate an existing tag', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $existingTag = Tag::factory()->create(['name' => 'science', 'slug' => 'science']);

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'tags' => ['science'],
        ])
        ->assertRedirect();

    expect(Tag::where('slug', 'science')->count())->toBe(1)
        ->and($quiz->fresh()->tags->first()->id)->toBe($existingTag->id);
});

test('invalid category id is rejected', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", ['category_id' => 9999])
        ->assertSessionHasErrors('category_id');
});

test('dashboard filters quizzes by category', function () {
    $user = User::factory()->create();
    $math = Category::factory()->create(['name' => 'Math']);
    $science = Category::factory()->create(['name' => 'Science']);

    Quiz::factory()->for($user)->create(['title' => 'Math Quiz', 'category_id' => $math->id]);
    Quiz::factory()->for($user)->create(['title' => 'Science Quiz', 'category_id' => $science->id]);

    $response = $this->actingAs($user)
        ->get("/dashboard?category={$math->id}")
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes)->toHaveCount(1)
        ->and($quizzes[0]['title'])->toBe('Math Quiz');
});

test('dashboard filters quizzes by tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['name' => 'fun']);

    $quizWithTag = Quiz::factory()->for($user)->create(['title' => 'Fun Quiz']);
    $quizWithTag->tags()->attach($tag);
    Quiz::factory()->for($user)->create(['title' => 'Other Quiz']);

    $response = $this->actingAs($user)
        ->get("/dashboard?tag={$tag->id}")
        ->assertSuccessful();

    $quizzes = $response->original->getData()['page']['props']['quizzes'];
    expect($quizzes)->toHaveCount(1)
        ->and($quizzes[0]['title'])->toBe('Fun Quiz');
});

test('admin can manage categories', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), ['name' => 'History', 'icon' => '📜'])
        ->assertRedirect();

    $category = Category::where('name', 'History')->first();
    expect($category)->not->toBeNull();

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $category->id), ['name' => 'World History', 'icon' => '🌍'])
        ->assertRedirect();

    expect($category->fresh()->name)->toBe('World History');

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category->id))
        ->assertRedirect();

    expect(Category::find($category->id))->toBeNull();
});

test('non-admin cannot manage categories', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});
