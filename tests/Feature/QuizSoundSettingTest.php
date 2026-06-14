<?php

use App\Models\Quiz;
use App\Models\User;

test('users can update the sound theme and music setting of their quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'settings' => ['sound_theme' => 'chill', 'music_enabled' => false],
        ])
        ->assertRedirect();

    expect($quiz->fresh()->settings)
        ->toMatchArray(['sound_theme' => 'chill', 'music_enabled' => false]);
});

test('invalid sound theme is rejected', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'settings' => ['sound_theme' => 'invalid-theme'],
        ])
        ->assertSessionHasErrors('settings.sound_theme');
});

test('non boolean music enabled value is rejected', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'settings' => ['music_enabled' => 'not-a-bool'],
        ])
        ->assertSessionHasErrors('settings.music_enabled');
});

test('updating one settings key preserves previously saved settings', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create(['settings' => ['sound_theme' => 'energetic', 'music_enabled' => true]]);

    $this->actingAs($user)
        ->put("/quizzes/{$quiz->id}", [
            'settings' => ['music_enabled' => false],
        ])
        ->assertRedirect();

    expect($quiz->fresh()->settings)
        ->toMatchArray(['sound_theme' => 'energetic', 'music_enabled' => false]);
});
