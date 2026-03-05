<?php

use App\Models\User;

test('default locale is indonesian', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    expect(app()->getLocale())->toBe('id');
});

test('authenticated user locale is used', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get('/');

    expect(app()->getLocale())->toBe('en');
});

test('locale cookie is respected for guests', function () {
    $this->withCookie('locale', 'en')->get('/');

    expect(app()->getLocale())->toBe('en');
});

test('invalid locale falls back to default', function () {
    $this->withCookie('locale', 'fr')->get('/');

    expect(app()->getLocale())->toBe('id');
});

test('user locale takes precedence over cookie', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->withCookie('locale', 'id')->get('/');

    expect(app()->getLocale())->toBe('en');
});
