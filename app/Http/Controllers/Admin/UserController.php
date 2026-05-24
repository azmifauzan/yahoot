<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::withCount(['quizzes', 'gamePlayers'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request): void {
                $q->where('name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('email', 'like', '%'.$request->input('search').'%');
            });
        }

        if ($request->filled('filter')) {
            match ($request->input('filter')) {
                'admin' => $query->where('is_admin', true),
                'active' => $query->whereNotNull('email_verified_at'),
                default => null,
            };
        }

        $users = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search', 'filter'),
        ]);
    }

    public function show(User $user): Response
    {
        $user->load([
            'quizzes' => fn ($q) => $q->withCount('questions')->withTrashed()->latest()->limit(10),
        ]);

        $gameHistory = $user->gamePlayers()
            ->with('gameSession.quiz:id,title')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($player) => [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'score' => $player->score,
                'quiz_title' => $player->gameSession?->quiz?->title,
                'played_at' => $player->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'gameHistory' => $gameHistory,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => 'sometimes|boolean',
        ]);

        if ($user->id === $request->user()->id && isset($validated['is_admin']) && ! $validated['is_admin']) {
            return back()->withErrors(['is_admin' => 'You cannot remove your own admin status.']);
        }

        $user->update($validated);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index');
    }
}
