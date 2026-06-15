<?php

namespace App\Http\Requests;

use App\Enums\PointType;
use App\Enums\QuestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankQuestionRequest extends FormRequest
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
            'type' => ['required', Rule::enum(QuestionType::class)],
            'question_text' => ['required', 'string', 'max:500'],
            'time_limit' => ['nullable', 'integer', 'min:5', 'max:120'],
            'points' => ['nullable', Rule::enum(PointType::class)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'answers' => ['required', 'array', 'min:1', 'max:4'],
            'answers.*.answer_text' => ['required', 'string', 'max:255'],
            'answers.*.is_correct' => ['boolean'],
            'answers.*.color' => ['nullable', 'string'],
        ];
    }
}
