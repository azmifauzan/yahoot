<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\QuizAnalyticsService;

function answersFor(Question $question, int $correct, int $wrong): void
{
    $session = GameSession::factory()->finished()->create(['quiz_id' => $question->quiz_id]);

    for ($i = 0; $i < $correct; $i++) {
        $player = GamePlayer::factory()->create(['game_session_id' => $session->id]);
        PlayerAnswer::factory()->correct()->create([
            'game_player_id' => $player->id,
            'question_id' => $question->id,
        ]);
    }

    for ($i = 0; $i < $wrong; $i++) {
        $player = GamePlayer::factory()->create(['game_session_id' => $session->id]);
        PlayerAnswer::factory()->create([
            'game_player_id' => $player->id,
            'question_id' => $question->id,
            'is_correct' => false,
        ]);
    }
}

test('difficulty is classified from the correct rate across sessions', function () {
    $quiz = Quiz::factory()->create();
    $easy = Question::factory()->for($quiz)->create(['order' => 0]);
    $hard = Question::factory()->for($quiz)->create(['order' => 1]);

    // Easy: 9 correct / 1 wrong across two sessions = 0.9
    answersFor($easy, 5, 0);
    answersFor($easy, 4, 1);

    // Hard: 1 correct / 4 wrong = 0.2
    answersFor($hard, 1, 4);

    $result = app(QuizAnalyticsService::class)->difficulty($quiz)->keyBy('question_id');

    expect($result[$easy->id]['total_attempts'])->toBe(10)
        ->and($result[$easy->id]['correct_count'])->toBe(9)
        ->and($result[$easy->id]['correct_rate'])->toBe(0.9)
        ->and($result[$easy->id]['difficulty'])->toBe('easy');

    expect($result[$hard->id]['correct_rate'])->toBe(0.2)
        ->and($result[$hard->id]['difficulty'])->toBe('hard');
});

test('questions with no attempts are marked unknown', function () {
    $quiz = Quiz::factory()->create();
    $question = Question::factory()->for($quiz)->create();

    $result = app(QuizAnalyticsService::class)->difficulty($quiz);

    expect($result->first()['difficulty'])->toBe('unknown')
        ->and($result->first()['total_attempts'])->toBe(0);
});

test('only the quiz owner can view analytics', function () {
    $owner = User::factory()->create();
    $quiz = Quiz::factory()->for($owner)->create();
    Question::factory()->for($quiz)->create();

    $this->actingAs($owner)
        ->get(route('quizzes.analytics', $quiz))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Quiz/Analytics')->has('questions', 1));

    $this->actingAs(User::factory()->create())
        ->get(route('quizzes.analytics', $quiz))
        ->assertForbidden();
});
