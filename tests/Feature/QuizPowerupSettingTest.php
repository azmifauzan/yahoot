<?php

use App\Models\Quiz;
use App\Models\User;

test('powerups are disabled by default for a new quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    expect($quiz->settings['powerups_enabled'] ?? false)->toBeFalse();
});

test('host can enable powerups for their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'settings' => ['powerups_enabled' => true],
        ])
        ->assertRedirect();

    expect($quiz->fresh()->settings)->toMatchArray(['powerups_enabled' => true]);
});

test('non boolean powerups enabled value is rejected', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'settings' => ['powerups_enabled' => 'not-a-bool'],
        ])
        ->assertSessionHasErrors('settings.powerups_enabled');
});

test('updating powerups setting preserves previously saved settings', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['settings' => ['sound_theme' => 'energetic', 'music_enabled' => true]]);

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'settings' => ['powerups_enabled' => true],
        ])
        ->assertRedirect();

    expect($quiz->fresh()->settings)
        ->toMatchArray(['sound_theme' => 'energetic', 'music_enabled' => true, 'powerups_enabled' => true]);
});
