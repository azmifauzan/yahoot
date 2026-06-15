<?php

namespace App\Http\Requests\Game;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'player_id' => ['required', 'integer', 'exists:game_players,id'],
            'player_token' => ['required', 'string'],
            'emoji' => ['required', 'string', Rule::in(config('reactions.emojis'))],
        ];
    }
}
