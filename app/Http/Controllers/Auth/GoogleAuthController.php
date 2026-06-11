<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google and log the user in.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Failed to sign in with Google. Please try again.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                if (! $existingUser->email_verified_at) {
                    return redirect()->route('login')->withErrors([
                        'email' => 'An account with this email already exists. Please log in with your password to link Google.',
                    ]);
                }

                $existingUser->forceFill(['google_id' => $googleUser->getId()])->save();
                $user = $existingUser;
            }
        }

        if (! $user) {
            $emailVerified = method_exists($googleUser, 'getRaw')
                ? (bool) ($googleUser->getRaw()['email_verified'] ?? true)
                : true;

            $user = User::forceCreate([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $emailVerified ? now() : null,
                'password' => null,
            ]);

            $this->storeAvatar($user, $googleUser);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Download and store the user's Google profile photo, ignoring failures.
     */
    private function storeAvatar(User $user, SocialiteUser $googleUser): void
    {
        $avatarUrl = $googleUser->getAvatar();

        if (! $avatarUrl) {
            return;
        }

        try {
            $response = Http::timeout(5)->get($avatarUrl);

            if (! $response->successful()) {
                return;
            }

            $path = 'profile-photos/'.Str::random(40).'.jpg';

            Storage::disk(config('jetstream.profile_photo_disk', 'public'))->put($path, $response->body());

            $user->forceFill(['profile_photo_path' => $path])->save();
        } catch (Throwable $e) {
            Log::warning('Failed to download Google profile photo', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
