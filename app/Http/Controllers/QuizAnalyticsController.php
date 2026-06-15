<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Services\QuizAnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class QuizAnalyticsController extends Controller
{
    public function __construct(private QuizAnalyticsService $analyticsService) {}

    /**
     * Show per-question difficulty insights for a quiz across all sessions.
     */
    public function index(Quiz $quiz): Response
    {
        $this->authorize('view', $quiz);

        $sessionsCount = $quiz->gameSessions()->count();

        return Inertia::render('Quiz/Analytics', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
            ],
            'questions' => $this->analyticsService->difficulty($quiz),
            'sessionsCount' => $sessionsCount,
        ]);
    }
}
