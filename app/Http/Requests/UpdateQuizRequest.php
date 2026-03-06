<?php

namespace App\Http\Requests;

use App\Enums\QuizTheme;
use App\Enums\QuizVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'visibility' => ['nullable', Rule::enum(QuizVisibility::class)],
            'theme' => ['nullable', Rule::enum(QuizTheme::class)],
        ];
    }
}
