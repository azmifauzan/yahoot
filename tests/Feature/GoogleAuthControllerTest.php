<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

function mockGoogleUser(string $id, string $email, string $name, ?string $avatar = null): void
{
    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn($id);
    $googleUser->shouldReceive('getEmail')->andReturn($email);
    $googleUser->shouldReceive('getName')->andReturn($name);
    $googleUser->shouldReceive('getAvatar')->andReturn($avatar);

    Socialite::shouldReceive('driver->user')->andReturn($googleUser);
}

test('redirects to google', function () {
    Socialite::shouldReceive('driver->redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('creates and logs in a new user from google', function () {
    mockGoogleUser('1234567890', 'newuser@example.com', 'New User');

    $response = $this->get(route('auth.google.callback'));

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
        'name' => 'New User',
        'google_id' => '1234567890',
    ]);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->password)->toBeNull();
});

test('downloads google avatar for new user', function () {
    Storage::fake('public');

    Http::fake([
        'https://example.com/avatar.jpg' => Http::response('fake-image-bytes', 200),
    ]);

    mockGoogleUser('1234567890', 'newuser@example.com', 'New User', 'https://example.com/avatar.jpg');

    $this->get(route('auth.google.callback'));

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user->profile_photo_path)->not->toBeNull();
});

test('links google account to existing user with matching email', function () {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'google_id' => null,
    ]);

    mockGoogleUser('1234567890', 'existing@example.com', $user->name);

    $response = $this->get(route('auth.google.callback'));

    $this->assertAuthenticated();
    $this->assertAuthenticatedAs($user->fresh());
    $response->assertRedirect(route('dashboard', absolute: false));

    expect($user->fresh()->google_id)->toBe('1234567890');
});

test('does not link google account to existing user with unverified email', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'unverified@example.com',
        'google_id' => null,
    ]);

    mockGoogleUser('1234567890', 'unverified@example.com', $user->name);

    $response = $this->get(route('auth.google.callback'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');

    expect($user->fresh()->google_id)->toBeNull();
});

test('logs in existing user already linked to google', function () {
    $user = User::factory()->create([
        'google_id' => '1234567890',
    ]);

    mockGoogleUser('1234567890', $user->email, $user->name);

    $response = $this->get(route('auth.google.callback'));

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('redirects to login when google authentication fails', function () {
    Socialite::shouldReceive('driver->user')->andThrow(new Exception('invalid state'));

    $response = $this->get(route('auth.google.callback'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});
