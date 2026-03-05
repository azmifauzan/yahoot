<?php

use App\Enums\AnswerColor;
use App\Enums\GameStatus;
use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Enums\QuizVisibility;

test('GameStatus has correct cases', function () {
    expect(GameStatus::cases())->toHaveCount(4)
        ->and(GameStatus::Waiting->value)->toBe('waiting')
        ->and(GameStatus::Playing->value)->toBe('playing')
        ->and(GameStatus::Reviewing->value)->toBe('reviewing')
        ->and(GameStatus::Finished->value)->toBe('finished');
});

test('QuestionType has correct cases', function () {
    expect(QuestionType::cases())->toHaveCount(2)
        ->and(QuestionType::MultipleChoice->value)->toBe('multiple_choice')
        ->and(QuestionType::TrueFalse->value)->toBe('true_false');
});

test('PointType returns correct base points', function () {
    expect(PointType::Standard->basePoints())->toBe(1000)
        ->and(PointType::Double->basePoints())->toBe(2000)
        ->and(PointType::None->basePoints())->toBe(0);
});

test('QuizVisibility has correct cases', function () {
    expect(QuizVisibility::cases())->toHaveCount(2)
        ->and(QuizVisibility::Public->value)->toBe('public')
        ->and(QuizVisibility::Private->value)->toBe('private');
});

test('AnswerColor has correct colors and shapes', function () {
    expect(AnswerColor::Red->hex())->toBe('#FF6B6B')
        ->and(AnswerColor::Red->shape())->toBe('triangle')
        ->and(AnswerColor::Blue->hex())->toBe('#5B8DEF')
        ->and(AnswerColor::Blue->shape())->toBe('diamond')
        ->and(AnswerColor::Yellow->hex())->toBe('#FECA57')
        ->and(AnswerColor::Yellow->shape())->toBe('circle')
        ->and(AnswerColor::Green->hex())->toBe('#48DBAB')
        ->and(AnswerColor::Green->shape())->toBe('square');
});
