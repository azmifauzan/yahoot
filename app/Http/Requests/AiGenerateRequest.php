<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AiGenerateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'topic' => 'required|string|max:200',
            'count' => 'required|integer|min:1|max:20',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'type' => 'required|string|in:multiple_choice,true_false,all',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'topic.required' => 'Topic is required.',
            'topic.max' => 'Topic cannot exceed 200 characters.',
            'count.required' => 'Question count is required.',
            'count.min' => 'You must generate at least 1 question.',
            'count.max' => 'You can generate at most 20 questions at a time.',
            'difficulty.in' => 'Invalid difficulty selected.',
            'type.in' => 'Invalid question type selected.',
        ];
    }
}
