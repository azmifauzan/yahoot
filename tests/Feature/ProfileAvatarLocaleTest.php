<?php

use App\Models\User;

test('profile information can be updated with avatar and locale', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->put('/user/profile-information', [
        'name' => 'Test User',
        'email' => $user->email,
        'avatar' => 'fox',
        'locale' => 'en',
    ]);

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->avatar)->toBe('fox')
        ->and($user->locale)->toBe('en');
});

test('invalid avatar is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->put('/user/profile-information', [
        'name' => 'Test User',
        'email' => $user->email,
        'avatar' => 'invalid_avatar',
        'locale' => 'id',
    ]);

    $response->assertSessionHasErrors(['avatar'], errorBag: 'updateProfileInformation');
});

test('invalid locale is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->put('/user/profile-information', [
        'name' => 'Test User',
        'email' => $user->email,
        'avatar' => 'cat',
        'locale' => 'fr',
    ]);

    $response->assertSessionHasErrors(['locale'], errorBag: 'updateProfileInformation');
});

test('avatar and locale can be null in update', function () {
    $user = User::factory()->create(['avatar' => 'cat', 'locale' => 'id']);

    $this->actingAs($user);

    $this->put('/user/profile-information', [
        'name' => 'Test User',
        'email' => $user->email,
    ]);

    $user->refresh();

    expect($user->avatar)->toBe('cat')
        ->and($user->locale)->toBe('id');
});

test('all valid avatars are accepted', function () {
    $validAvatars = [
        'cat', 'dog', 'panda', 'rabbit', 'fox', 'owl',
        'robot_blue', 'robot_red', 'robot_green', 'robot_yellow', 'robot_purple', 'robot_pink',
        'monster_1', 'monster_2', 'monster_3', 'monster_4', 'monster_5', 'monster_6',
        'star', 'moon', 'sun', 'cloud', 'rainbow', 'lightning',
    ];

    $user = User::factory()->create();
    $this->actingAs($user);

    foreach ($validAvatars as $avatar) {
        $this->put('/user/profile-information', [
            'name' => 'Test User',
            'email' => $user->email,
            'avatar' => $avatar,
            'locale' => 'id',
        ]);

        $user->refresh();
        expect($user->avatar)->toBe($avatar);
    }
});
