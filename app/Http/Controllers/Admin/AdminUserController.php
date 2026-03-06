<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()
            ->withCount(['quizzes', 'hostedGameSessions']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            if ($request->input('role') === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->input('role') === 'user') {
                $query->where('is_admin', false);
            }
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search', ''),
                'role' => $request->input('role', 'all'),
            ],
        ]);
    }

    public function show(User $user): Response
    {
        $user->loadCount(['quizzes', 'hostedGameSessions']);

        $quizzes = $user->quizzes()->withCount('questions')->latest()->limit(10)->get();

        $gameSessions = $user->hostedGameSessions()
            ->with('quiz:id,title')
            ->withCount('players')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'quizzes' => $quizzes,
            'gameSessions' => $gameSessions,
        ]);
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot change your own admin status.']);
        }

        $user->update(['is_admin' => ! $user->is_admin]);

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
