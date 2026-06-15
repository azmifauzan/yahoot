<?php

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function aiQuestionsPayload(int $count, string $type): array
{
    $answers = $type === 'true_false'
        ? [
            ['answer_text' => 'True', 'is_correct' => true],
            ['answer_text' => 'False', 'is_correct' => false],
        ]
        : [
            ['answer_text' => '4', 'is_correct' => true],
            ['answer_text' => '5', 'is_correct' => false],
            ['answer_text' => '6', 'is_correct' => false],
            ['answer_text' => '7', 'is_correct' => false],
        ];

    return [
        'questions' => array_fill(0, $count, [
            'question_text' => 'What is 2 + 2?',
            'answers' => $answers,
        ]),
    ];
}

test('generating questions fails when llm settings are not configured', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('quizzes.ai-generate', $quiz), [
            'topic' => 'Math',
            'count' => 2,
            'difficulty' => 'medium',
            'type' => 'multiple_choice',
        ])
        ->assertSessionHasErrors('ai');

    expect($quiz->questions()->count())->toBe(0);
});

test('users cannot generate questions for other users quizzes', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $quiz = Quiz::factory()->for($other)->create();

    $user->llmSetting()->create([
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'api_key' => 'sk-test',
    ]);

    $this->actingAs($user)
        ->post(route('quizzes.ai-generate', $quiz), [
            'topic' => 'Math',
            'count' => 1,
            'difficulty' => 'medium',
            'type' => 'multiple_choice',
        ])
        ->assertForbidden();
});

test('openai provider generates multiple choice questions', function () {
    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => json_encode(aiQuestionsPayload(2, 'multiple_choice'))]],
            ],
        ]),
    ]);

    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $user->llmSetting()->create([
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'api_key' => 'sk-test',
    ]);

    $this->actingAs($user)
        ->post(route('quizzes.ai-generate', $quiz), [
            'topic' => 'Math',
            'count' => 2,
            'difficulty' => 'medium',
            'type' => 'multiple_choice',
        ])
        ->assertRedirect();

    $quiz->refresh();

    expect($quiz->questions()->count())->toBe(2);

    $question = $quiz->questions()->with('answers')->first();
    expect($question->question_text)->toBe('What is 2 + 2?')
        ->and($question->answers)->toHaveCount(4)
        ->and($question->answers->where('is_correct', true))->toHaveCount(1);
});

test('anthropic provider generates true false questions', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [
                ['type' => 'text', 'text' => json_encode(aiQuestionsPayload(1, 'true_false'))],
            ],
        ]),
    ]);

    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $user->llmSetting()->create([
        'provider' => 'anthropic',
        'model' => 'claude-3-5-haiku-latest',
        'api_key' => 'sk-ant-test',
    ]);

    $this->actingAs($user)
        ->post(route('quizzes.ai-generate', $quiz), [
            'topic' => 'Geography',
            'count' => 1,
            'difficulty' => 'easy',
            'type' => 'true_false',
        ])
        ->assertRedirect();

    $quiz->refresh();

    $question = $quiz->questions()->with('answers')->first();
    expect($quiz->questions()->count())->toBe(1)
        ->and($question->type->value)->toBe('true_false')
        ->and($question->answers)->toHaveCount(2);
});

test('ai provider failure surfaces an error', function () {
    Http::fake([
        'api.openai.com/*' => Http::response(['error' => 'invalid api key'], 401),
    ]);

    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $user->llmSetting()->create([
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'api_key' => 'sk-invalid',
    ]);

    $this->actingAs($user)
        ->post(route('quizzes.ai-generate', $quiz), [
            'topic' => 'Math',
            'count' => 1,
            'difficulty' => 'medium',
            'type' => 'multiple_choice',
        ])
        ->assertSessionHasErrors('ai');

    expect($quiz->questions()->count())->toBe(0);
});
