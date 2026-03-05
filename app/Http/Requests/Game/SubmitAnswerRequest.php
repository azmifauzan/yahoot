<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
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
            'player_id' => ['required', 'integer', 'exists:game_players,id'],
            'answer_id' => ['nullable', 'integer', 'exists:answers,id'],
            'time_taken' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'player_id.required' => 'Player identification is required.',
            'player_id.exists' => 'Invalid player.',
            'time_taken.required' => 'Time taken is required.',
            'time_taken.min' => 'Time taken cannot be negative.',
        ];
    }
}
