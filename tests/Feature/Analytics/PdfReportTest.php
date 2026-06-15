<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

test('the host can view the printable game report', function () {
    $host = User::factory()->create();
    $quiz = Quiz::factory()->for($host)->create();
    Question::factory()->for($quiz)->create();
    $session = GameSession::factory()->finished()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
    ]);
    GamePlayer::factory()->create(['game_session_id' => $session->id, 'score' => 500]);

    $this->actingAs($host)
        ->get(route('game.report', $session))
        ->assertOk()
        ->assertSee($quiz->title)
        ->assertSee('Leaderboard');
});

test('non-hosts cannot view the report', function () {
    $session = GameSession::factory()->finished()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('game.report', $session))
        ->assertForbidden();
});
