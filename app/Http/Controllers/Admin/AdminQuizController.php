<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminQuizController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Quiz::withTrashed()
            ->with('user:id,name,email')
            ->withCount('questions');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'published' => $query->where('is_published', true)->whereNull('deleted_at'),
                'draft' => $query->where('is_published', false)->whereNull('deleted_at'),
                'trashed' => $query->onlyTrashed(),
                default => null,
            };
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->input('visibility'));
        }

        $quizzes = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Quizzes/Index', [
            'quizzes' => $quizzes,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', 'all'),
                'visibility' => $request->input('visibility', 'all'),
            ],
        ]);
    }

    public function show(string $quizId): Response
    {
        $quiz = Quiz::withTrashed()
            ->with(['user:id,name,email', 'questions.answers'])
            ->withCount(['questions', 'gameSessions'])
            ->findOrFail($quizId);

        return Inertia::render('Admin/Quizzes/Show', [
            'quiz' => $quiz,
        ]);
    }

    public function destroy(string $quizId): RedirectResponse
    {
        $quiz = Quiz::withTrashed()->findOrFail($quizId);

        if ($quiz->trashed()) {
            // Force delete if already soft-deleted
            if ($quiz->cover_image) {
                Storage::disk('s3')->delete($quiz->cover_image);
            }
            $quiz->forceDelete();
        } else {
            $quiz->delete();
        }

        return redirect()->route('admin.quizzes.index');
    }

    public function restore(string $quizId): RedirectResponse
    {
        $quiz = Quiz::onlyTrashed()->findOrFail($quizId);
        $quiz->restore();

        return back();
    }
}
