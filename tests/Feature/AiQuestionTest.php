<?php

use App\Events\QuestionsGenerated;
use App\Jobs\GenerateQuestionsJob;
use App\Models\AppSetting;
use App\Models\Quiz;
use App\Models\User;
use App\Services\AiQuestionService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

test('authenticated user can request AI question generation', function () {
    Queue::fake();

    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->postJson("/quizzes/{$quiz->id}/ai-generate", [
            'topic' => 'Solar System',
            'count' => 5,
            'difficulty' => 'easy',
            'type' => 'multiple_choice',
        ]);

    $response->assertSuccessful()
        ->assertJson([
            'status' => 'success',
        ]);

    Queue::assertPushed(GenerateQuestionsJob::class);
});

test('non-owner cannot generate AI questions for a quiz', function () {
    $owner = User::factory()->create();
    $quiz = Quiz::factory()->for($owner)->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->postJson("/quizzes/{$quiz->id}/ai-generate", [
            'topic' => 'Solar System',
            'count' => 5,
            'difficulty' => 'easy',
            'type' => 'multiple_choice',
        ])
        ->assertForbidden();
});

test('guest cannot request AI question generation', function () {
    $quiz = Quiz::factory()->create();

    $this->postJson("/quizzes/{$quiz->id}/ai-generate", [
        'topic' => 'Solar System',
        'count' => 5,
        'difficulty' => 'easy',
        'type' => 'multiple_choice',
    ])
        ->assertUnauthorized();
});

test('ai-generate endpoint validates inputs', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->postJson("/quizzes/{$quiz->id}/ai-generate", [
            'topic' => '',
            'count' => 25, // max 20
            'difficulty' => 'super_hard', // invalid
            'type' => 'short_answer', // invalid
        ])
        ->assertJsonValidationErrors(['topic', 'count', 'difficulty', 'type']);
});

test('ai-generate returns 403 when feature is disabled globally', function () {
    AppSetting::set('ai_generation_enabled', '0');

    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $this->actingAs($user)
        ->postJson("/quizzes/{$quiz->id}/ai-generate", [
            'topic' => 'Solar System',
            'count' => 5,
            'difficulty' => 'easy',
            'type' => 'multiple_choice',
        ])
        ->assertStatus(403);

    AppSetting::set('ai_generation_enabled', '1'); // reset
});

test('AiQuestionService formats prompt and handles successful Claude response', function () {
    config(['services.anthropic.key' => 'fake-key']);
    config(['services.anthropic.url' => 'https://fake-anthropic.com']);

    $mockResponse = [
        'content' => [
            [
                'text' => json_encode([
                    [
                        'type' => 'multiple_choice',
                        'question_text' => 'What is the closest planet to the Sun?',
                        'time_limit' => 20,
                        'points' => 'standard',
                        'answers' => [
                            ['answer_text' => 'Mercury', 'is_correct' => true, 'color' => 'red'],
                            ['answer_text' => 'Venus', 'is_correct' => false, 'color' => 'blue'],
                            ['answer_text' => 'Earth', 'is_correct' => false, 'color' => 'yellow'],
                            ['answer_text' => 'Mars', 'is_correct' => false, 'color' => 'green'],
                        ],
                    ],
                ]),
            ],
        ],
    ];

    Http::fake([
        'https://fake-anthropic.com' => Http::response($mockResponse, 200),
    ]);

    $service = new AiQuestionService;
    $questions = $service->generate('Solar System', 1, 'easy', 'multiple_choice', 'en');

    expect($questions)->toHaveCount(1)
        ->and($questions[0]['question_text'])->toBe('What is the closest planet to the Sun?')
        ->and($questions[0]['answers'][0]['answer_text'])->toBe('Mercury');
});

test('GenerateQuestionsJob calls service and broadcasts QuestionsGenerated event', function () {
    Event::fake();

    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    $questions = [
        [
            'type' => 'multiple_choice',
            'question_text' => 'What is the closest planet to the Sun?',
            'time_limit' => 20,
            'points' => 'standard',
            'answers' => [
                ['answer_text' => 'Mercury', 'is_correct' => true, 'color' => 'red'],
                ['answer_text' => 'Venus', 'is_correct' => false, 'color' => 'blue'],
                ['answer_text' => 'Earth', 'is_correct' => false, 'color' => 'yellow'],
                ['answer_text' => 'Mars', 'is_correct' => false, 'color' => 'green'],
            ],
        ],
    ];

    $mockService = Mockery::mock(AiQuestionService::class);
    $mockService->shouldReceive('generate')
        ->once()
        ->with('Solar System', 1, 'easy', 'multiple_choice', 'en')
        ->andReturn($questions);

    $job = new GenerateQuestionsJob(
        $user->id,
        $quiz->id,
        'Solar System',
        1,
        'easy',
        'multiple_choice',
        'en'
    );

    $job->handle($mockService);

    Event::assertDispatched(QuestionsGenerated::class, function ($event) use ($user, $quiz, $questions) {
        return $event->userId === $user->id
            && $event->quizId === $quiz->id
            && $event->questions === $questions
            && $event->success === true;
    });
});
