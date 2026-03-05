<?php

use App\Models\User;

test('landing page can be rendered', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
});

test('landing page has inertia component', function () {
    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page->component('Landing'));
});

test('landing page shares canLogin and canRegister', function () {
    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->has('canLogin')
        ->has('canRegister')
    );
});

test('landing page shares locale', function () {
    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->has('locale')
    );
});

test('authenticated users see landing page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertSuccessful();
});
