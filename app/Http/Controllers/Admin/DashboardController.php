<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Quiz;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'total_quizzes' => Quiz::withTrashed()->count(),
            'active_quizzes' => Quiz::count(),
            'deleted_quizzes' => Quiz::onlyTrashed()->count(),
            'total_games' => GameSession::count(),
            'games_today' => GameSession::whereDate('created_at', today())->count(),
            'games_this_month' => GameSession::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_players' => GamePlayer::count(),
            'guest_players' => GamePlayer::whereNull('user_id')->count(),
        ];

        $activityChart = GameSession::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'count' => $row->count]);

        $recentGames = GameSession::with(['quiz:id,title', 'host:id,name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'game_code' => $session->game_code,
                'status' => $session->status->value,
                'quiz_title' => $session->quiz?->title,
                'host_name' => $session->host?->name,
                'players_count' => $session->players()->count(),
                'created_at' => $session->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Dashboard', compact('stats', 'activityChart', 'recentGames'));
    }
}
