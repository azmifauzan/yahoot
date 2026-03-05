<?php

use App\Enums\PointType;
use App\Models\GamePlayer;
use App\Models\Question;
use App\Services\ScoringService;

function makeQuestion(PointType $points = PointType::Standard, int $timeLimit = 20): Question
{
    $question = new Question;
    $question->points = $points;
    $question->time_limit = $timeLimit;

    return $question;
}

function makePlayer(int $streak = 0): GamePlayer
{
    $player = new GamePlayer;
    $player->streak = $streak;

    return $player;
}

test('correct answer with instant response gives full base points', function () {
    $service = new ScoringService;
    $result = $service->calculate(makeQuestion(), makePlayer(), true, 0);

    expect($result['points_earned'])->toBe(1000)
        ->and($result['new_streak'])->toBe(1);
});

test('incorrect answer gives zero points and resets streak', function () {
    $service = new ScoringService;
    $result = $service->calculate(makeQuestion(), makePlayer(5), false, 5000);

    expect($result['points_earned'])->toBe(0)
        ->and($result['streak_bonus'])->toBe(0)
        ->and($result['new_streak'])->toBe(0);
});

test('correct answer at half time gives 75% base points', function () {
    $service = new ScoringService;
    // time_taken = 10s out of 20s → factor = 1 - (10000/20000/2) = 0.75
    $result = $service->calculate(makeQuestion(), makePlayer(), true, 10000);

    expect($result['points_earned'])->toBe(750);
});

test('correct answer at time limit gives 50% base points', function () {
    $service = new ScoringService;
    // time_taken = 20s out of 20s → factor = 1 - (20000/20000/2) = 0.5
    $result = $service->calculate(makeQuestion(), makePlayer(), true, 20000);

    expect($result['points_earned'])->toBe(500);
});

test('double points question gives 2000 base', function () {
    $service = new ScoringService;
    $result = $service->calculate(makeQuestion(PointType::Double), makePlayer(), true, 0);

    expect($result['points_earned'])->toBe(2000);
});

test('no points question gives zero but increments streak', function () {
    $service = new ScoringService;
    $result = $service->calculate(makeQuestion(PointType::None), makePlayer(2), true, 5000);

    expect($result['points_earned'])->toBe(0)
        ->and($result['new_streak'])->toBe(3);
});

test('streak bonus is capped at 500', function () {
    $service = new ScoringService;
    $result = $service->calculate(makeQuestion(), makePlayer(10), true, 0);

    // streak = 11, bonus = min(11*100, 500) = 500
    expect($result['streak_bonus'])->toBe(500)
        ->and($result['new_streak'])->toBe(11);
});
