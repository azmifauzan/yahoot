<?php

use App\Models\Answer;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\ScoringService;

test('getLeaderboard includes correct_count and best_streak', function () {
    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0]);
    $answer = Answer::factory()->correct()->red()->for($question)->create();

    $session = GameSession::factory()->finished()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
    ]);

    $player = GamePlayer::factory()->create([
        'game_session_id' => $session->id,
        'score' => 1000,
    ]);

    PlayerAnswer::factory()->create([
        'game_player_id' => $player->id,
        'question_id' => $question->id,
        'answer_id' => $answer->id,
        'is_correct' => true,
        'time_taken' => 3000,
        'points_earned' => 1000,
        'streak_bonus' => 0,
    ]);

    $service = new ScoringService;
    $leaderboard = $service->getLeaderboard($session->id);

    expect($leaderboard)->toHaveCount(1)
        ->and($leaderboard[0]['correct_count'])->toBe(1)
        ->and($leaderboard[0]['best_streak'])->toBe(1)
        ->and($leaderboard[0]['avg_time'])->toBe(3000.0);
});

test('getPlayerStats returns per-player stats', function () {
    $host = User::factory()->create();
    $quiz = Quiz::factory()->published()->for($host)->create();
    $question = Question::factory()->for($quiz)->create(['order' => 0]);
    $answer = Answer::factory()->correct()->red()->for($question)->create();

    $session = GameSession::factory()->finished()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
    ]);

    $player = GamePlayer::factory()->create(['game_session_id' => $session->id]);

    PlayerAnswer::factory()->create([
        'game_player_id' => $player->id,
        'question_id' => $question->id,
        'answer_id' => $answer->id,
        'is_correct' => true,
        'time_taken' => 5000,
        'points_earned' => 800,
        'streak_bonus' => 0,
    ]);

    $service = new ScoringService;
    $stats = $service->getPlayerStats($session->id);

    expect($stats)->toHaveKey($player->id)
        ->and($stats[$player->id]['correct_answers'])->toBe(1)
        ->and($stats[$player->id]['total_answers'])->toBe(1)
        ->and($stats[$player->id]['longest_streak'])->toBe(1);
});
