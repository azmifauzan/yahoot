<?php

namespace App\Http\Requests\Game;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StartGameSessionRequest extends FormRequest
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
            'mode' => ['nullable', 'in:individual,team'],
            'team_count' => ['nullable', 'integer', 'min:2', 'max:6'],
            'team_selection' => ['nullable', 'in:auto,manual'],
            'powerups_enabled' => ['nullable', 'boolean'],
            'reactions_enabled' => ['nullable', 'boolean'],
        ];
    }
}
