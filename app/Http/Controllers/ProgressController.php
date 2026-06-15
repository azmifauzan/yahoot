<?php

namespace App\Http\Controllers;

use App\Services\PlayerProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function __construct(private PlayerProgressService $progressService) {}

    /**
     * Show the authenticated user's cross-session progress and earned badges.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $earned = $user->badges()
            ->orderBy('earned_at')
            ->get()
            ->mapWithKeys(fn ($badge) => [$badge->badge_key => $badge->earned_at?->toDateString()]);

        /** @var array<string, array{icon: string, criteria: array<string, int>}> $catalog */
        $catalog = config('badges', []);

        $badges = collect($catalog)->map(fn (array $badge, string $key): array => [
            'key' => $key,
            'icon' => $badge['icon'],
            'earned' => $earned->has($key),
            'earned_at' => $earned->get($key),
        ])->values();

        return Inertia::render('Progress', [
            'progress' => $this->progressService->forUser($user),
            'badges' => $badges,
            'earnedCount' => $earned->count(),
        ]);
    }
}
