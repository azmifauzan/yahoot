<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function index(Request $request): Response
    {
        $query = GameSession::with(['quiz:id,title', 'host:id,name'])
            ->withCount('players')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $games = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Games/Index', [
            'games' => $games,
            'filters' => $request->only('status', 'from', 'to'),
        ]);
    }

    public function show(GameSession $gameSession): Response
    {
        $gameSession->load(['quiz:id,title', 'host:id,name', 'players.playerAnswers']);

        $players = $gameSession->players->map(fn ($player) => [
            'id' => $player->id,
            'nickname' => $player->nickname,
            'avatar' => $player->avatar,
            'score' => $player->score,
            'correct_answers' => $player->playerAnswers->where('is_correct', true)->count(),
            'total_answers' => $player->playerAnswers->count(),
        ]);

        return Inertia::render('Admin/Games/Show', [
            'gameSession' => $gameSession,
            'players' => $players,
        ]);
    }

    public function destroy(GameSession $gameSession): RedirectResponse
    {
        $gameSession->delete();

        return redirect()->route('admin.games.index');
    }
}
