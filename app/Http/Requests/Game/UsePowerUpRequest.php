<?php

namespace App\Http\Requests\Game;

use App\Enums\PowerUpType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsePowerUpRequest extends FormRequest
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
            'powerup' => ['required', Rule::enum(PowerUpType::class)],
            'question_id' => ['required', 'integer', 'exists:questions,id'],
        ];
    }
}
