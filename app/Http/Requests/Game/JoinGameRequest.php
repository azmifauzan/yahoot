<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class JoinGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'game_code' => ['required', 'string', 'size:6', 'exists:game_sessions,game_code'],
            'nickname' => ['required', 'string', 'min:2', 'max:20'],
            'avatar' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'game_code.required' => 'Game code is required.',
            'game_code.size' => 'Game code must be 6 digits.',
            'game_code.exists' => 'Game not found. Check the code and try again.',
            'nickname.required' => 'Please enter a nickname.',
            'nickname.min' => 'Nickname must be at least 2 characters.',
            'nickname.max' => 'Nickname must not exceed 20 characters.',
            'avatar.required' => 'Please select an avatar.',
        ];
    }
}
