<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $totalUsers = User::query()->count();
        $totalQuizzes = Quiz::query()->count();
        $totalQuizzesTrashed = Quiz::onlyTrashed()->count();
        $totalPublished = Quiz::query()->where('is_published', true)->count();
        $totalDraft = Quiz::query()->where('is_published', false)->count();

        $totalGameSessions = GameSession::query()->count();
        $gamesToday = GameSession::query()->whereDate('created_at', today())->count();
        $gamesThisMonth = GameSession::query()->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalRegisteredPlayers = User::query()->count();
        $totalGuestPlayers = \App\Models\GamePlayer::query()->whereNull('user_id')->count();

        // Games per day for the last 30 days
        $dailyGames = GameSession::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'count' => $row->count,
            ]);

        $recentGames = GameSession::query()
            ->with(['quiz:id,title', 'host:id,name'])
            ->withCount('players')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_quizzes' => $totalQuizzes,
                'total_quizzes_trashed' => $totalQuizzesTrashed,
                'total_published' => $totalPublished,
                'total_draft' => $totalDraft,
                'total_game_sessions' => $totalGameSessions,
                'games_today' => $gamesToday,
                'games_this_month' => $gamesThisMonth,
                'total_registered_players' => $totalRegisteredPlayers,
                'total_guest_players' => $totalGuestPlayers,
            ],
            'dailyGames' => $dailyGames,
            'recentGames' => $recentGames,
        ]);
    }
}
