<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /** @var array<string> */
    private const VALID_AVATARS = [
        'cat', 'dog', 'panda', 'rabbit', 'fox', 'owl',
        'robot_blue', 'robot_red', 'robot_green', 'robot_yellow', 'robot_purple', 'robot_pink',
        'monster_1', 'monster_2', 'monster_3', 'monster_4', 'monster_5', 'monster_6',
        'star', 'moon', 'sun', 'cloud', 'rainbow', 'lightning',
    ];

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'avatar' => ['nullable', 'string', Rule::in(self::VALID_AVATARS)],
            'locale' => ['nullable', 'string', Rule::in(['id', 'en'])],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
                'avatar' => $input['avatar'] ?? $user->avatar,
                'locale' => $input['locale'] ?? $user->locale,
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
            'avatar' => $input['avatar'] ?? $user->avatar,
            'locale' => $input['locale'] ?? $user->locale,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
