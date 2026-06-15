<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiGenerateRequest;
use App\Jobs\GenerateQuestionsJob;
use App\Models\AppSetting;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;

class AiQuestionController extends Controller
{
    /**
     * Dispatch a background job to generate quiz questions via Claude API.
     */
    public function generate(AiGenerateRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz);

        $enabled = (bool) AppSetting::get('ai_generation_enabled', '1');
        if (! $enabled) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI Question Generation is disabled by the administrator.',
            ], 403);
        }

        $validated = $request->validated();

        GenerateQuestionsJob::dispatch(
            auth()->id(),
            $quiz->id,
            $validated['topic'],
            (int) $validated['count'],
            $validated['difficulty'],
            $validated['type'],
            app()->getLocale()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'AI Question Generation started.',
        ]);
    }
}
