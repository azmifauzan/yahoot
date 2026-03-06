<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminGameController extends Controller
{
    public function index(Request $request): Response
    {
        $query = GameSession::query()
            ->with(['quiz:id,title', 'host:id,name'])
            ->withCount('players');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('game_code', 'like', "%{$search}%")
                    ->orWhereHas('quiz', function ($q) use ($search): void {
                        $q->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $games = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Games/Index', [
            'games' => $games,
            'filters' => [
                'status' => $request->input('status', 'all'),
                'search' => $request->input('search', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function show(GameSession $gameSession): Response
    {
        $gameSession->load([
            'quiz:id,title',
            'host:id,name,email',
            'players.playerAnswers',
        ]);

        $totalQuestions = $gameSession->quiz ? $gameSession->quiz->questions()->count() : 0;

        $playerStats = $gameSession->players->map(function ($player) use ($totalQuestions) {
            $answers = $player->playerAnswers;

            return [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar,
                'score' => $player->score,
                'is_connected' => $player->is_connected,
                'correct_answers' => $answers->where('is_correct', true)->count(),
                'total_questions' => $totalQuestions,
                'avg_time' => $answers->where('answer_id', '!=', null)->avg('time_taken') ?? 0,
            ];
        })->sortByDesc('score')->values();

        return Inertia::render('Admin/Games/Show', [
            'gameSession' => $gameSession,
            'playerStats' => $playerStats,
        ]);
    }

    public function destroy(GameSession $gameSession): RedirectResponse
    {
        $gameSession->players()->delete();
        $gameSession->delete();

        return redirect()->route('admin.games.index');
    }
}
