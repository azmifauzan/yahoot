<?php

namespace App\Http\Controllers;

use App\Enums\QuizVisibility;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    /**
     * Display a listing of public quizzes.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $categoryId = $request->input('category');
        $tagSlug = $request->input('tag');
        $sort = $request->input('sort', 'newest');

        $quizzesQuery = Quiz::query()
            ->with(['user', 'category', 'tags'])
            ->withCount('questions')
            ->where('is_published', true)
            ->where('visibility', QuizVisibility::Public);

        if ($search) {
            $quizzesQuery->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $quizzesQuery->where('category_id', $categoryId);
        }

        if ($tagSlug) {
            $quizzesQuery->whereHas('tags', function (Builder $query) use ($tagSlug): void {
                $query->where('slug', $tagSlug);
            });
        }

        switch ($sort) {
            case 'popular':
                $quizzesQuery->orderBy('plays_count', 'desc');
                break;
            case 'featured':
                $quizzesQuery->orderBy('featured', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $quizzesQuery->orderBy('created_at', 'desc');
                break;
        }

        $quizzes = $quizzesQuery->paginate(12)->withQueryString();

        $favoriteIds = [];
        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();
            $favoriteIds = $user->favoriteQuizzes()->pluck('quizzes.id')->toArray();
        }

        $quizzes->getCollection()->transform(function (Quiz $quiz) use ($favoriteIds): Quiz {
            $quiz->has_favorited = in_array($quiz->id, $favoriteIds);

            return $quiz;
        });

        $categories = Category::all();
        $tags = Tag::all();

        return Inertia::render('Explore', [
            'quizzes' => $quizzes,
            'categories' => $categories,
            'tags' => $tags,
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
                'tag' => $tagSlug,
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Toggle favorite status for a quiz.
     */
    public function favorite(Quiz $quiz): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $quiz->is_published || ($quiz->visibility !== QuizVisibility::Public && $quiz->user_id !== $user->id)) {
            abort(403);
        }

        $user->favoriteQuizzes()->toggle($quiz->id);

        return back();
    }
}
