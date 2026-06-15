<?php

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function buildQuizWithQuestions(User $user): Quiz
{
    $quiz = Quiz::factory()->for($user)->create(['title' => 'Export Me', 'theme' => 'ocean']);
    $q1 = Question::factory()->for($quiz)->create([
        'question_text' => 'Capital of France?',
        'time_limit' => 20,
        'order' => 0,
    ]);
    Answer::factory()->for($q1)->correct()->red()->create(['answer_text' => 'Paris']);
    Answer::factory()->for($q1)->blue()->create(['answer_text' => 'Lyon']);

    $q2 = Question::factory()->for($quiz)->trueFalse()->create([
        'question_text' => 'Earth is flat',
        'order' => 1,
    ]);
    Answer::factory()->for($q2)->red()->create(['answer_text' => 'True', 'is_correct' => false]);
    Answer::factory()->for($q2)->blue()->create(['answer_text' => 'False', 'is_correct' => true]);

    return $quiz;
}

test('owner can export a quiz as JSON', function () {
    $user = User::factory()->create();
    $quiz = buildQuizWithQuestions($user);

    $response = $this->actingAs($user)
        ->get("/quizzes/{$quiz->id}/export?format=json")
        ->assertSuccessful();

    $data = json_decode($response->streamedContent(), true);

    expect($data['title'])->toBe('Export Me')
        ->and($data['theme'])->toBe('ocean')
        ->and($data['questions'])->toHaveCount(2)
        ->and($data['questions'][0]['question_text'])->toBe('Capital of France?')
        ->and($data['questions'][0]['answers'])->toHaveCount(2)
        ->and($data['questions'][0]['answers'][0]['answer_text'])->toBe('Paris')
        ->and($data['questions'][0]['answers'][0]['is_correct'])->toBeTrue();
});

test('owner can export a quiz as CSV', function () {
    $user = User::factory()->create();
    $quiz = buildQuizWithQuestions($user);

    $response = $this->actingAs($user)
        ->get("/quizzes/{$quiz->id}/export?format=csv")
        ->assertSuccessful();

    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain('Capital of France?');
});

test('non-owner cannot export a quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->create(); // other owner

    $this->actingAs($user)
        ->get("/quizzes/{$quiz->id}/export?format=json")
        ->assertForbidden();
});

test('guests cannot import', function () {
    $file = UploadedFile::fake()->createWithContent('quiz.json', json_encode(['title' => 'X', 'questions' => []]));

    $this->post('/quizzes/import', ['file' => $file])->assertRedirect('/login');
});

test('import valid JSON creates a complete draft quiz', function () {
    $user = User::factory()->create();

    $payload = [
        'title' => 'Imported Quiz',
        'description' => 'Hello',
        'theme' => 'sunset',
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question_text' => 'What is 2+2?',
                'time_limit' => 30,
                'points' => 'standard',
                'answers' => [
                    ['answer_text' => '4', 'is_correct' => true],
                    ['answer_text' => '5', 'is_correct' => false],
                ],
            ],
        ],
    ];

    $file = UploadedFile::fake()->createWithContent('quiz.json', json_encode($payload));

    $this->actingAs($user)
        ->post('/quizzes/import', ['file' => $file])
        ->assertRedirect();

    $quiz = Quiz::where('title', 'Imported Quiz')->first();
    expect($quiz)->not->toBeNull()
        ->and($quiz->user_id)->toBe($user->id)
        ->and($quiz->is_published)->toBeFalse()
        ->and($quiz->theme->value)->toBe('sunset')
        ->and($quiz->questions)->toHaveCount(1)
        ->and($quiz->questions->first()->answers)->toHaveCount(2)
        ->and($quiz->questions->first()->answers->where('is_correct', true)->first()->answer_text)->toBe('4');
});

test('import assigns default answer colors when omitted', function () {
    $user = User::factory()->create();

    $payload = [
        'title' => 'Colorless',
        'questions' => [[
            'type' => 'multiple_choice',
            'question_text' => 'Pick',
            'answers' => [
                ['answer_text' => 'A', 'is_correct' => true],
                ['answer_text' => 'B', 'is_correct' => false],
                ['answer_text' => 'C', 'is_correct' => false],
                ['answer_text' => 'D', 'is_correct' => false],
            ],
        ]],
    ];

    $file = UploadedFile::fake()->createWithContent('q.json', json_encode($payload));

    $this->actingAs($user)->post('/quizzes/import', ['file' => $file])->assertRedirect();

    $answers = Quiz::where('title', 'Colorless')->first()->questions->first()->answers;
    expect($answers->pluck('color')->map->value->toArray())->toBe(['red', 'blue', 'yellow', 'green']);
});

test('import rejects invalid JSON structure', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('bad.json', '{not valid json');

    $this->actingAs($user)
        ->post('/quizzes/import', ['file' => $file])
        ->assertSessionHasErrors('file');
});

test('import rejects JSON missing required fields', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('bad.json', json_encode(['description' => 'no title']));

    $this->actingAs($user)
        ->post('/quizzes/import', ['file' => $file])
        ->assertSessionHasErrors('file');
});

test('import rejects disallowed file types', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('quiz.pdf', 10, 'application/pdf');

    $this->actingAs($user)
        ->post('/quizzes/import', ['file' => $file])
        ->assertSessionHasErrors('file');
});

test('exported JSON round-trips back to an equivalent quiz', function () {
    $user = User::factory()->create();
    $quiz = buildQuizWithQuestions($user);

    $exported = $this->actingAs($user)->get("/quizzes/{$quiz->id}/export?format=json")->streamedContent();

    $file = UploadedFile::fake()->createWithContent('rt.json', $exported);
    $this->actingAs($user)->post('/quizzes/import', ['file' => $file])->assertRedirect();

    $imported = Quiz::where('id', '!=', $quiz->id)->where('title', $quiz->title)->latest('id')->first();

    expect($imported->questions)->toHaveCount($quiz->questions->count())
        ->and($imported->questions->first()->answers->count())->toBe($quiz->questions->first()->answers->count())
        ->and($imported->theme->value)->toBe($quiz->theme->value);
});
