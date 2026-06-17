<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitPracticeRequest;
use App\Models\PracticeSession;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PracticeController extends Controller
{
    public function start(Quiz $quiz): Response
    {
        $this->authorize('practice', $quiz);

        $quiz->load(['questions.answers', 'category']);

        return Inertia::render('Practice/Play', [
            'quiz' => $quiz,
            'theme' => [
                'value' => $quiz->theme->value,
                'gradients' => $quiz->theme->gradients(),
            ],
        ]);
    }

    public function submit(SubmitPracticeRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('practice', $quiz);

        $data = $request->validated();

        $session = PracticeSession::create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user()?->id,
            'score' => $data['score'],
            'correct_count' => $data['correct_count'],
            'total_questions' => $data['total_questions'],
            'completed_at' => now(),
        ]);

        return response()->json(['practice_session' => $session]);
    }
}
