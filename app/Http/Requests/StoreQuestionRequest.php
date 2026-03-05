<?php

namespace App\Http\Requests;

use App\Enums\AnswerColor;
use App\Enums\PointType;
use App\Enums\QuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
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
            'type' => ['required', Rule::enum(QuestionType::class)],
            'question_text' => ['required', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:2048'],
            'time_limit' => ['required', 'integer', Rule::in([5, 10, 20, 30, 60, 90, 120])],
            'points' => ['required', Rule::enum(PointType::class)],
            'answers' => ['required', 'array', 'min:2', 'max:4'],
            'answers.*.answer_text' => ['required', 'string', 'max:255'],
            'answers.*.is_correct' => ['required', 'boolean'],
            'answers.*.color' => ['required', Rule::enum(AnswerColor::class)],
        ];
    }
}
