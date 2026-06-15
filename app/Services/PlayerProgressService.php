<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\User;

class PlayerProgressService
{
    /**
     * Cross-session progress for a registered user: a per-game time series of
     * score & accuracy plus summary totals.
     *
     * @return array{
     *     totals: array{games_played: int, games_won: int, total_xp: int, avg_accuracy: float},
     *     games: array<int, array{game_session_id: int, quiz_title: string|null, played_at: string|null, score: int, correct: int, total: int, accuracy: float}>
     * }
     */
    public function forUser(User $user): array
    {
        $players = $user->gamePlayers()
            ->whereHas('gameSession', fn ($query) => $query->where('status', GameStatus::Finished))
            ->with(['gameSession.quiz:id,title', 'playerAnswers'])
            ->get()
            ->sortBy(fn ($player) => $player->gameSession?->finished_at)
            ->values();

        $games = $players->map(function ($player): array {
            $answers = $player->playerAnswers;
            $total = $answers->whereNotNull('answer_id')->count();
            $correct = $answers->where('is_correct', true)->count();
            $accuracy = $total > 0 ? round($correct / $total * 100, 1) : 0.0;

            return [
                'game_session_id' => $player->game_session_id,
                'quiz_title' => $player->gameSession?->quiz?->title,
                'played_at' => $player->gameSession?->finished_at?->toDateString(),
                'score' => $player->score,
                'correct' => $correct,
                'total' => $total,
                'accuracy' => $accuracy,
            ];
        });

        $avgAccuracy = $games->isNotEmpty()
            ? round($games->avg('accuracy'), 1)
            : 0.0;

        return [
            'totals' => [
                'games_played' => $user->games_played,
                'games_won' => $user->games_won,
                'total_xp' => $user->total_xp,
                'avg_accuracy' => $avgAccuracy,
            ],
            'games' => $games->all(),
        ];
    }
}
