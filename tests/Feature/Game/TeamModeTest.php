<?php

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\GameTeam;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\ScoringService;

function publishedQuizForTeams(User $host): Quiz
{
    $quiz = Quiz::factory()->published()->for($host)->create();
    Question::factory()->for($quiz)->create(['order' => 0]);

    return $quiz;
}

test('starting a game in team mode creates the configured number of teams', function () {
    $host = User::factory()->create();
    $quiz = publishedQuizForTeams($host);

    $this->actingAs($host)
        ->post(route('game.store', $quiz), ['mode' => 'team', 'team_count' => 3])
        ->assertRedirect();

    $session = GameSession::query()->latest('id')->first();

    expect($session->settings['mode'])->toBe('team');
    expect(GameTeam::query()->where('game_session_id', $session->id)->count())->toBe(3);
});

test('players auto-balance into the emptiest team on join', function () {
    $host = User::factory()->create();
    $quiz = publishedQuizForTeams($host);

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
        'settings' => ['mode' => 'team', 'team_count' => 2],
    ]);
    $teamA = GameTeam::factory()->for($session)->create();
    $teamB = GameTeam::factory()->for($session)->create();

    foreach (['Aa', 'Bb', 'Cc'] as $nick) {
        $this->postJson('/api/games/join', [
            'game_code' => $session->game_code,
            'nickname' => $nick,
            'avatar' => 'cat',
        ])->assertCreated();
    }

    // 3 players across 2 teams must split 2 / 1, never 3 / 0.
    expect($teamA->players()->count())->toBeGreaterThanOrEqual(1);
    expect($teamB->players()->count())->toBeGreaterThanOrEqual(1);
    expect($teamA->players()->count() + $teamB->players()->count())->toBe(3);
});

test('team leaderboard sums member scores and ranks teams', function () {
    $host = User::factory()->create();
    $quiz = publishedQuizForTeams($host);

    $session = GameSession::factory()->create([
        'quiz_id' => $quiz->id,
        'host_id' => $host->id,
        'settings' => ['mode' => 'team', 'team_count' => 2],
    ]);
    $teamA = GameTeam::factory()->for($session)->create(['name' => 'Red']);
    $teamB = GameTeam::factory()->for($session)->create(['name' => 'Blue']);

    GamePlayer::factory()->for($session)->create(['game_team_id' => $teamA->id, 'score' => 300]);
    GamePlayer::factory()->for($session)->create(['game_team_id' => $teamA->id, 'score' => 200]);
    GamePlayer::factory()->for($session)->create(['game_team_id' => $teamB->id, 'score' => 100]);

    $leaderboard = app(ScoringService::class)->getTeamLeaderboard($session->id);

    expect($leaderboard->first()['team_id'])->toBe($teamA->id)
        ->and($leaderboard->first()['score'])->toBe(500)
        ->and($leaderboard->first()['rank'])->toBe(1)
        ->and($leaderboard->last()['score'])->toBe(100);
});

test('individual mode creates no teams and leaves players unassigned', function () {
    $host = User::factory()->create();
    $quiz = publishedQuizForTeams($host);

    $this->actingAs($host)
        ->post(route('game.store', $quiz), ['mode' => 'individual'])
        ->assertRedirect();

    $session = GameSession::query()->latest('id')->first();

    expect(GameTeam::query()->where('game_session_id', $session->id)->count())->toBe(0);

    $this->postJson('/api/games/join', [
        'game_code' => $session->game_code,
        'nickname' => 'Solo',
        'avatar' => 'cat',
    ])->assertCreated();

    expect(GamePlayer::query()->where('game_session_id', $session->id)->first()->game_team_id)->toBeNull();
});
