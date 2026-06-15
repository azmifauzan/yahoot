<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Enums\PowerUpType;
use App\Events\AnswerSubmitted;
use App\Events\PlayerJoined;
use App\Events\PlayerLeft;
use App\Events\PowerUpUsed;
use App\Events\ReactionSent;
use App\Http\Requests\Game\JoinGameRequest;
use App\Http\Requests\Game\LeaveGameRequest;
use App\Http\Requests\Game\SendReactionRequest;
use App\Http\Requests\Game\SubmitAnswerRequest;
use App\Http\Requests\Game\UsePowerUpRequest;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Services\RevealService;
use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    public function __construct(
        private ScoringService $scoringService,
        private RevealService $revealService
    ) {}

    /**
     * Show the join game page.
     */
    public function join(Request $request): Response
    {
        return Inertia::render('Player/Join', [
            'gameCode' => $request->query('code', ''),
        ]);
    }

    /**
     * Show the player lobby / game page for a specific game code.
     */
    public function play(string $code): Response
    {
        $session = GameSession::query()
            ->where('game_code', $code)
            ->whereIn('status', [GameStatus::Waiting, GameStatus::Playing, GameStatus::Reviewing])
            ->firstOrFail();

        return Inertia::render('Player/Game', [
            'gameSession' => [
                'id' => $session->id,
                'game_code' => $session->game_code,
                'status' => $session->status->value,
                'quiz_title' => $session->quiz->title,
                'sound_theme' => $session->quiz->settings['sound_theme'] ?? 'classic',
                'music_enabled' => $session->quiz->settings['music_enabled'] ?? true,
                'reactions_enabled' => $session->settings['reactions_enabled'] ?? true,
                'powerups_enabled' => $session->settings['powerups_enabled'] ?? true,
                'mode' => $session->settings['mode'] ?? 'individual',
            ],
        ]);
    }

    /**
     * API: Look up a waiting game session by code for the join flow
     * (mode + team selection info, used to drive the join UI).
     */
    public function apiInfo(string $code): JsonResponse
    {
        $session = GameSession::query()
            ->where('game_code', $code)
            ->where('status', GameStatus::Waiting)
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Game not found or already started.'], 404);
        }

        $mode = $session->settings['mode'] ?? 'individual';

        return response()->json([
            'mode' => $mode,
            'team_selection' => $session->settings['team_selection'] ?? 'auto',
            'teams' => $mode === 'team'
                ? $session->teams()->withCount('players')->get()->map(fn ($team) => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'color' => $team->color,
                    'player_count' => $team->players_count,
                ])
                : [],
        ]);
    }

    /**
     * API: Join a game session.
     */
    public function apiJoin(JoinGameRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $session = GameSession::query()
            ->where('game_code', $validated['game_code'])
            ->where('status', GameStatus::Waiting)
            ->first();

        if (! $session) {
            return response()->json([
                'message' => 'Game not found or already started.',
            ], 422);
        }

        // Check for duplicate nickname in same session
        $existingPlayer = $session->players()
            ->where('nickname', $validated['nickname'])
            ->where('is_connected', true)
            ->first();

        if ($existingPlayer) {
            return response()->json([
                'message' => 'Nickname already taken in this game.',
            ], 422);
        }

        // Team mode: let the player pick a team (manual) or auto-balance into the smallest team.
        $team = null;
        if (($session->settings['mode'] ?? 'individual') === 'team') {
            if (($session->settings['team_selection'] ?? 'auto') === 'manual' && ! empty($validated['team_id'])) {
                $team = $session->teams()->find($validated['team_id']);
            }

            if (! $team) {
                $team = $session->teams()->withCount('players')->orderBy('players_count')->orderBy('id')->first();
            }
        }

        $player = GamePlayer::query()->create([
            'game_session_id' => $session->id,
            'game_team_id' => $team?->id,
            'user_id' => $request->user()?->id,
            'nickname' => $validated['nickname'],
            'avatar' => $validated['avatar'],
            'score' => 0,
            'streak' => 0,
            'powerups_available' => PowerUpType::defaults(),
            'powerups_used' => [],
            'is_connected' => true,
            'player_token' => Str::random(40),
        ]);

        $totalPlayers = $session->players()->where('is_connected', true)->count();

        broadcast(new PlayerJoined(
            $session->id,
            $player,
            $totalPlayers
        ));

        return response()->json([
            'player' => [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar,
                'player_token' => $player->player_token,
                'powerups_available' => $player->powerups_available,
                'team' => $team ? [
                    'id' => $team->id,
                    'name' => $team->name,
                    'color' => $team->color,
                ] : null,
            ],
            'gameSession' => [
                'id' => $session->id,
                'game_code' => $session->game_code,
                'status' => $session->status->value,
            ],
        ], 201);
    }

    /**
     * API: Mark a player as disconnected (left the game).
     */
    public function apiLeave(LeaveGameRequest $request, GameSession $gameSession): JsonResponse
    {
        $validated = $request->validated();

        $player = GamePlayer::query()
            ->where('id', $validated['player_id'])
            ->where('game_session_id', $gameSession->id)
            ->where('is_connected', true)
            ->first();

        if (! $player) {
            return response()->json(['message' => 'Player not found.'], 404);
        }

        if (! hash_equals((string) $player->player_token, (string) $validated['player_token'])) {
            return response()->json(['message' => 'Invalid player token.'], 403);
        }

        $player->update(['is_connected' => false]);

        $totalPlayers = $gameSession->players()->where('is_connected', true)->count();

        broadcast(new PlayerLeft(
            $gameSession->id,
            $player,
            $totalPlayers
        ));

        return response()->json(['message' => 'Left game.']);
    }

    /**
     * API: Send an ephemeral emoji reaction (broadcast only, not persisted).
     */
    public function apiReact(SendReactionRequest $request, GameSession $gameSession): JsonResponse
    {
        $validated = $request->validated();

        if (($gameSession->settings['reactions_enabled'] ?? true) === false) {
            return response()->json(['message' => 'Reactions are disabled.'], 403);
        }

        $player = GamePlayer::query()
            ->where('id', $validated['player_id'])
            ->where('game_session_id', $gameSession->id)
            ->firstOrFail();

        if (! hash_equals((string) $player->player_token, (string) $validated['player_token'])) {
            return response()->json(['message' => 'Invalid player token.'], 403);
        }

        $throttleKey = 'react:'.$player->id;

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            return response()->json(['message' => 'Slow down.'], 429);
        }

        RateLimiter::hit($throttleKey, (int) config('reactions.throttle_seconds', 1));

        broadcast(new ReactionSent(
            $gameSession->id,
            $player->id,
            $player->nickname,
            $validated['emoji']
        ));

        return response()->json(['message' => 'Reaction sent.']);
    }

    /**
     * API: Use a power-up for the current question.
     */
    public function apiUsePowerup(UsePowerUpRequest $request, GameSession $gameSession): JsonResponse
    {
        $validated = $request->validated();

        if (($gameSession->settings['powerups_enabled'] ?? true) === false) {
            return response()->json(['message' => 'Power-ups are disabled.'], 403);
        }

        $player = GamePlayer::query()
            ->where('id', $validated['player_id'])
            ->where('game_session_id', $gameSession->id)
            ->firstOrFail();

        if (! hash_equals((string) $player->player_token, (string) $validated['player_token'])) {
            return response()->json(['message' => 'Invalid player token.'], 403);
        }

        $questions = $gameSession->quiz->questions()->orderBy('order')->get();
        $currentQuestion = $questions[$gameSession->current_question_index] ?? null;

        if (! $currentQuestion || (int) $validated['question_id'] !== $currentQuestion->id) {
            return response()->json(['message' => 'Power-up can only be used on the current question.'], 422);
        }

        $powerup = PowerUpType::from($validated['powerup']);
        $available = $player->powerups_available ?? PowerUpType::defaults();
        $used = $player->powerups_used ?? [];

        if (! in_array($powerup->value, $available, true)) {
            return response()->json(['message' => 'Power-up not available.'], 422);
        }

        $available = array_values(array_filter($available, fn ($p) => $p !== $powerup->value));
        $used[] = ['type' => $powerup->value, 'question_id' => $currentQuestion->id];

        $player->update([
            'powerups_available' => $available,
            'powerups_used' => $used,
        ]);

        $response = ['powerups_available' => $available];

        if ($powerup === PowerUpType::FiftyFifty) {
            $hidden = $currentQuestion->answers()
                ->where('is_correct', false)
                ->inRandomOrder()
                ->limit(2)
                ->pluck('id')
                ->all();

            $response['hidden_answers'] = $hidden;
        }

        broadcast(new PowerUpUsed(
            $gameSession->id,
            $player->id,
            $player->nickname,
            $powerup->value
        ));

        return response()->json($response);
    }

    /**
     * API: Submit an answer.
     */
    public function apiAnswer(SubmitAnswerRequest $request, GameSession $gameSession): JsonResponse
    {
        $validated = $request->validated();

        if ($gameSession->status !== GameStatus::Playing) {
            return response()->json(['message' => 'Game is not accepting answers.'], 422);
        }

        $player = GamePlayer::query()
            ->where('id', $validated['player_id'])
            ->where('game_session_id', $gameSession->id)
            ->firstOrFail();

        if (! hash_equals((string) $player->player_token, (string) $validated['player_token'])) {
            return response()->json(['message' => 'Invalid player token.'], 403);
        }

        $questions = $gameSession->quiz->questions()->orderBy('order')->get();
        $currentQuestion = $questions[$gameSession->current_question_index] ?? null;

        if (! $currentQuestion) {
            return response()->json(['message' => 'No active question.'], 422);
        }

        // Check if already answered
        $existing = PlayerAnswer::query()
            ->where('game_player_id', $player->id)
            ->where('question_id', $currentQuestion->id)
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Already answered this question.'], 422);
        }

        // Determine correctness
        $isCorrect = false;
        if ($validated['answer_id']) {
            $answer = $currentQuestion->answers()->find($validated['answer_id']);
            $isCorrect = $answer?->is_correct ?? false;
        }

        // Resolve any power-up this player activated for the current question.
        $powerup = null;
        foreach ($player->powerups_used ?? [] as $entry) {
            if (($entry['question_id'] ?? null) === $currentQuestion->id) {
                $powerup = PowerUpType::tryFrom($entry['type'] ?? '');
                break;
            }
        }

        // Calculate score
        $scoringResult = $this->scoringService->calculate(
            $currentQuestion,
            $player,
            $isCorrect,
            $validated['time_taken'],
            $powerup
        );

        // Save player answer
        PlayerAnswer::query()->create([
            'game_player_id' => $player->id,
            'question_id' => $currentQuestion->id,
            'answer_id' => $validated['answer_id'],
            'is_correct' => $isCorrect,
            'time_taken' => $validated['time_taken'],
            'points_earned' => $scoringResult['points_earned'],
            'streak_bonus' => $scoringResult['streak_bonus'],
            'powerup_used' => $powerup?->value,
        ]);

        // Update player score and streak
        $gained = $scoringResult['points_earned'] + $scoringResult['streak_bonus'];
        $player->update([
            'score' => $player->score + $gained,
            'streak' => $scoringResult['new_streak'],
        ]);

        // Mirror the gain onto the player's team total (team mode).
        if ($player->game_team_id && $gained > 0) {
            $player->team()->increment('score', $gained);
        }

        // Broadcast answer count
        $answeredCount = PlayerAnswer::query()
            ->whereIn('game_player_id', $gameSession->players()->pluck('id'))
            ->where('question_id', $currentQuestion->id)
            ->count();

        $totalPlayers = $gameSession->players()->where('is_connected', true)->count();

        broadcast(new AnswerSubmitted(
            $gameSession->id,
            $answeredCount,
            $totalPlayers
        ));

        // Auto-reveal when all connected players have answered
        if ($answeredCount >= $totalPlayers) {
            $this->revealService->reveal($gameSession->fresh());
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'points_earned' => $scoringResult['points_earned'],
            'streak_bonus' => $scoringResult['streak_bonus'],
            'total_score' => $player->fresh()->score,
            'streak' => $scoringResult['new_streak'],
        ]);
    }

    /**
     * API: Get current game status.
     */
    public function apiStatus(GameSession $gameSession): JsonResponse
    {
        $gameSession->load('players');

        return response()->json([
            'id' => $gameSession->id,
            'game_code' => $gameSession->game_code,
            'status' => $gameSession->status->value,
            'current_question_index' => $gameSession->current_question_index,
            'question_started_at' => $gameSession->question_started_at?->toISOString(),
            'players' => $gameSession->players->map(fn ($p) => [
                'id' => $p->id,
                'nickname' => $p->nickname,
                'avatar' => $p->avatar,
                'score' => $p->score,
                'is_connected' => $p->is_connected,
            ]),
        ]);
    }
}
