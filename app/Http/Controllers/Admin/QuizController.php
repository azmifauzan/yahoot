<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Quiz::withTrashed()
            ->with('user:id,name')
            ->withCount('questions')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('filter')) {
            match ($request->input('filter')) {
                'published' => $query->whereNull('deleted_at')->where('is_published', true),
                'draft' => $query->whereNull('deleted_at')->where('is_published', false),
                'trashed' => $query->onlyTrashed(),
                default => null,
            };
        }

        $quizzes = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Quizzes/Index', [
            'quizzes' => $quizzes,
            'filters' => $request->only('search', 'filter'),
        ]);
    }

    public function show(Quiz $quiz): Response
    {
        $quiz->loadMissing(['user:id,name', 'questions.answers']);

        return Inertia::render('Admin/Quizzes/Show', [
            'quiz' => $quiz,
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $quiz = Quiz::withTrashed()->findOrFail($id);
        $quiz->forceDelete();

        return redirect()->route('admin.quizzes.index');
    }

    public function restore(int $id): RedirectResponse
    {
        $quiz = Quiz::onlyTrashed()->findOrFail($id);
        $quiz->restore();

        return back();
    }
}
