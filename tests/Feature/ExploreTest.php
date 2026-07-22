<?php

use App\Models\Category;
use App\Models\Quiz;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guest can access explore page and see public published quizzes', function () {
    $quizPublic = Quiz::factory()->published()->public()->create(['title' => 'Public Published Quiz']);
    $quizPrivate = Quiz::factory()->published()->create(['title' => 'Private Quiz']); // private by default
    $quizDraft = Quiz::factory()->public()->create(['title' => 'Draft Quiz', 'is_published' => false]);

    $this->get('/explore')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Explore')
            ->has('quizzes.data', 1)
            ->where('quizzes.data.0.title', 'Public Published Quiz')
        );
});

test('authenticated user gets the dedicated explore panel page', function () {
    $user = User::factory()->create();
    Quiz::factory()->published()->public()->create(['title' => 'Panel Quiz']);

    $this->actingAs($user)
        ->get(route('user.explore'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('User/Explore')
            ->where('quizzes.data.0.title', 'Panel Quiz')
        );
});

test('guest cannot access the explore panel page', function () {
    $this->get(route('user.explore'))->assertRedirect(route('login'));
});

test('explore page filters by category', function () {
    $category = Category::factory()->create();
    $quizWithCategory = Quiz::factory()->published()->public()->create([
        'title' => 'With Category',
        'category_id' => $category->id,
    ]);
    $quizWithoutCategory = Quiz::factory()->published()->public()->create([
        'title' => 'Without Category',
    ]);

    $this->get("/explore?category={$category->id}")
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Explore')
            ->has('quizzes.data', 1)
            ->where('quizzes.data.0.title', 'With Category')
        );
});

test('explore page filters by tag', function () {
    $tag = Tag::factory()->create(['slug' => 'science']);
    $quizWithTag = Quiz::factory()->published()->public()->create(['title' => 'Science Quiz']);
    $quizWithTag->tags()->attach($tag);

    $quizWithoutTag = Quiz::factory()->published()->public()->create(['title' => 'Math Quiz']);

    $this->get('/explore?tag=science')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Explore')
            ->has('quizzes.data', 1)
            ->where('quizzes.data.0.title', 'Science Quiz')
        );
});

test('explore page sorts quizzes', function () {
    $quizNew = Quiz::factory()->published()->public()->create(['title' => 'New', 'created_at' => now(), 'plays_count' => 5]);
    $quizPopular = Quiz::factory()->published()->public()->create(['title' => 'Popular', 'created_at' => now()->subDay(), 'plays_count' => 100]);
    $quizFeatured = Quiz::factory()->published()->public()->create(['title' => 'Featured', 'created_at' => now()->subDays(2), 'featured' => true]);

    // Popular sort
    $responsePopular = $this->get('/explore?sort=popular');
    $quizzesPopular = $responsePopular->original->getData()['page']['props']['quizzes']['data'];
    expect($quizzesPopular[0]['title'])->toBe('Popular');

    // Featured sort
    $responseFeatured = $this->get('/explore?sort=featured');
    $quizzesFeatured = $responseFeatured->original->getData()['page']['props']['quizzes']['data'];
    expect($quizzesFeatured[0]['title'])->toBe('Featured');

    // Newest sort
    $responseNewest = $this->get('/explore?sort=newest');
    $quizzesNewest = $responseNewest->original->getData()['page']['props']['quizzes']['data'];
    expect($quizzesNewest[0]['title'])->toBe('New');
});

test('authenticated user can toggle favorite on a public published quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->public()->create();

    $this->actingAs($user)
        ->from('/explore')
        ->post("/quizzes/{$quiz->id}/favorite")
        ->assertRedirect('/explore');

    $this->assertDatabaseHas('quiz_favorites', [
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
    ]);

    // Untoggle
    $this->actingAs($user)
        ->from('/explore')
        ->post("/quizzes/{$quiz->id}/favorite")
        ->assertRedirect('/explore');

    $this->assertDatabaseMissing('quiz_favorites', [
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
    ]);
});

test('guest cannot favorite a quiz', function () {
    $quiz = Quiz::factory()->published()->public()->create();

    $this->post("/quizzes/{$quiz->id}/favorite")
        ->assertRedirect('/login');
});

test('authenticated user can duplicate public published quiz of another user', function () {
    $owner = User::factory()->create();
    $quiz = Quiz::factory()->published()->public()->for($owner)->create();
    $duplicator = User::factory()->create();

    $this->actingAs($duplicator)
        ->post("/quizzes/{$quiz->id}/duplicate")
        ->assertRedirect();

    $this->assertDatabaseHas('quizzes', [
        'user_id' => $duplicator->id,
        'title' => $quiz->title.' (Salinan)',
        'is_published' => false,
    ]);

    expect($quiz->fresh()->duplicates_count)->toBe(1);
});
