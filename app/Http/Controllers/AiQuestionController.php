<?php

namespace App\Http\Controllers;

use App\Enums\AnswerColor;
use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Exceptions\AiGenerationException;
use App\Http\Requests\GenerateAiQuestionsRequest;
use App\Models\Quiz;
use App\Services\AiQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AiQuestionController extends Controller
{
    public function __construct(private AiQuestionService $aiQuestionService) {}

    public function generate(GenerateAiQuestionsRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $user = $request->user();
        $setting = $user->llmSetting;

        if (! $setting?->isConfigured()) {
            return back()->withErrors(['ai' => __('Lengkapi pengaturan LLM di Pengaturan AI sebelum menggunakan generator soal AI.')]);
        }

        $data = $request->validated();

        try {
            $questions = $this->aiQuestionService->generate(
                $setting,
                $data['topic'],
                $data['count'],
                $data['difficulty'],
                $data['type'],
                $user->locale ?? 'id'
            );
        } catch (AiGenerationException $e) {
            return back()->withErrors(['ai' => $e->getMessage()]);
        }

        $type = QuestionType::from($data['type']);
        $answerColors = $type === QuestionType::TrueFalse
            ? [AnswerColor::Blue, AnswerColor::Red]
            : [AnswerColor::Red, AnswerColor::Blue, AnswerColor::Yellow, AnswerColor::Green];

        DB::transaction(function () use ($quiz, $questions, $type, $answerColors) {
            $order = $quiz->questions()->max('order') ?? -1;

            foreach ($questions as $questionData) {
                $order++;

                $question = $quiz->questions()->create([
                    'type' => $type,
                    'question_text' => $questionData['question_text'],
                    'time_limit' => 20,
                    'points' => PointType::Standard,
                    'order' => $order,
                ]);

                foreach ($questionData['answers'] as $index => $answerData) {
                    $color = $answerColors[$index];
                    $question->answers()->create([
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct'],
                        'color' => $color,
                        'shape' => $color->shape(),
                        'order' => $index,
                    ]);
                }
            }
        });

        return back();
    }
}
