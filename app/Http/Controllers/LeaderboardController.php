<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __construct(private RankingService $rankingService) {}

    /**
     * Show the global player leaderboard (public) plus the current user's rank.
     */
    public function index(Request $request): Response
    {
        return $this->renderPage($request, 'Leaderboard');
    }

    public function userIndex(Request $request): Response
    {
        return $this->renderPage($request, 'User/Leaderboard');
    }

    private function renderPage(Request $request, string $component): Response
    {
        $user = $request->user();

        $myRank = null;

        if ($user) {
            $myRank = [
                'rank' => $this->rankingService->rankFor($user),
                'name' => $user->name,
                'avatar' => $user->profile_photo_url,
                'total_xp' => $user->total_xp,
                'games_played' => $user->games_played,
                'games_won' => $user->games_won,
            ];
        }

        return Inertia::render($component, [
            'topPlayers' => $this->rankingService->topPlayers(50),
            'myRank' => $myRank,
        ]);
    }
}
