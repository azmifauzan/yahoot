<?php

use App\Enums\AnswerColor;
use App\Enums\GameStatus;
use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Enums\QuizVisibility;
use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

test('user can create a quiz', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();

    expect($quiz->user_id)->toBe($user->id)
        ->and($quiz->user->id)->toBe($user->id);
});

test('user quizzes relationship works', function () {
    $user = User::factory()->create();
    Quiz::factory()->count(3)->for($user)->create();

    expect($user->quizzes)->toHaveCount(3);
});

test('quiz has questions relationship', function () {
    $quiz = Quiz::factory()->create();
    Question::factory()->count(5)->for($quiz)->create();

    expect($quiz->questions)->toHaveCount(5);
});

test('question has answers relationship', function () {
    $question = Question::factory()->create();
    Answer::factory()->count(4)->for($question)->create();

    expect($question->answers)->toHaveCount(4);
});

test('question type is cast to enum', function () {
    $question = Question::factory()->create(['type' => 'multiple_choice']);

    expect($question->type)->toBe(QuestionType::MultipleChoice);
});

test('question true_false factory state works', function () {
    $question = Question::factory()->trueFalse()->create();

    expect($question->type)->toBe(QuestionType::TrueFalse);
});

test('question double points factory state works', function () {
    $question = Question::factory()->doublePoints()->create();

    expect($question->points)->toBe(PointType::Double);
});

test('answer color is cast to enum', function () {
    $answer = Answer::factory()->create(['color' => 'red']);

    expect($answer->color)->toBe(AnswerColor::Red);
});

test('quiz visibility is cast to enum', function () {
    $quiz = Quiz::factory()->create(['visibility' => 'public']);

    expect($quiz->visibility)->toBe(QuizVisibility::Public);
});

test('quiz soft deletes work', function () {
    $quiz = Quiz::factory()->create();
    $quiz->delete();

    expect(Quiz::count())->toBe(0)
        ->and(Quiz::withTrashed()->count())->toBe(1);
});

test('game session belongs to quiz and host', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->for($user)->create();
    $session = GameSession::factory()->for($quiz)->create(['host_id' => $user->id]);

    expect($session->quiz->id)->toBe($quiz->id)
        ->and($session->host->id)->toBe($user->id);
});

test('game session status is cast to enum', function () {
    $session = GameSession::factory()->create();

    expect($session->status)->toBe(GameStatus::Waiting);
});

test('game session has unique game code', function () {
    $session = GameSession::factory()->create();

    expect($session->game_code)->toHaveLength(6)
        ->and($session->game_code)->toMatch('/^[A-Z0-9]{6}$/');
});

test('game player belongs to session', function () {
    $session = GameSession::factory()->create();
    $player = GamePlayer::factory()->for($session, 'gameSession')->create();

    expect($player->gameSession->id)->toBe($session->id);
});

test('game player can be guest (no user)', function () {
    $player = GamePlayer::factory()->create(['user_id' => null]);

    expect($player->user_id)->toBeNull()
        ->and($player->user)->toBeNull();
});

test('player answer belongs to player and question', function () {
    $player = GamePlayer::factory()->create();
    $question = Question::factory()->create();
    $answer = Answer::factory()->for($question)->correct()->create();
    $playerAnswer = PlayerAnswer::factory()->create([
        'game_player_id' => $player->id,
        'question_id' => $question->id,
        'answer_id' => $answer->id,
    ]);

    expect($playerAnswer->gamePlayer->id)->toBe($player->id)
        ->and($playerAnswer->question->id)->toBe($question->id)
        ->and($playerAnswer->answer->id)->toBe($answer->id);
});

test('user factory creates avatar and locale', function () {
    $user = User::factory()->create();

    expect($user->avatar)->not->toBeNull()
        ->and($user->locale)->toBe('id');
});
