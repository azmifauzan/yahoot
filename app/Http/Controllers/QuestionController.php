<?php

namespace App\Http\Controllers;

use App\Enums\AnswerColor;
use App\Http\Requests\ReorderQuestionsRequest;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function store(StoreQuestionRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $data = $request->validated();

        $maxOrder = $quiz->questions()->max('order') ?? -1;

        DB::transaction(function () use ($quiz, $data, $request, $maxOrder) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $stored = $request->file('image')->store('question-images', config('quiz.image_disk'));
                $imagePath = $stored ?: null;
            }

            $question = $quiz->questions()->create([
                'type' => $data['type'],
                'question_text' => $data['question_text'] ?? '',
                'image' => $imagePath,
                'time_limit' => $data['time_limit'],
                'points' => $data['points'],
                'order' => $maxOrder + 1,
            ]);

            foreach ($data['answers'] as $index => $answerData) {
                $color = AnswerColor::from($answerData['color']);
                $question->answers()->create([
                    'answer_text' => $answerData['answer_text'] ?? '',
                    'is_correct' => $answerData['is_correct'],
                    'color' => $color,
                    'shape' => $color->shape(),
                    'order' => $index,
                ]);
            }
        });

        return redirect()->back();
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $this->authorize('update', $question->quiz);

        $data = $request->validated();

        DB::transaction(function () use ($question, $data, $request) {
            $updateData = collect($data)->only(['type', 'question_text', 'time_limit', 'points'])->toArray();

            if (array_key_exists('question_text', $updateData)) {
                $updateData['question_text'] = $updateData['question_text'] ?? '';
            }

            if ($request->hasFile('image')) {
                $disk = config('quiz.image_disk');
                $stored = $request->file('image')->store('question-images', $disk);

                if ($stored) {
                    if ($question->image) {
                        Storage::disk($disk)->delete($question->image);
                    }
                    $updateData['image'] = $stored;
                }
            }

            $question->update($updateData);

            if (isset($data['answers'])) {
                $existingIds = collect($data['answers'])->pluck('id')->filter()->toArray();

                $question->answers()->whereNotIn('id', $existingIds)->delete();

                foreach ($data['answers'] as $index => $answerData) {
                    $color = AnswerColor::from($answerData['color']);
                    $attributes = [
                        'answer_text' => $answerData['answer_text'] ?? '',
                        'is_correct' => $answerData['is_correct'],
                        'color' => $color,
                        'shape' => $color->shape(),
                        'order' => $index,
                    ];

                    if (! empty($answerData['id'])) {
                        $question->answers()->where('id', $answerData['id'])->update($attributes);
                    } else {
                        $question->answers()->create($attributes);
                    }
                }
            }
        });

        return redirect()->back();
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('update', $question->quiz);

        if ($question->image) {
            Storage::disk(config('quiz.image_disk'))->delete($question->image);
        }

        $question->delete();

        return redirect()->back();
    }

    public function reorder(ReorderQuestionsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['questions'] as $item) {
                $question = Question::findOrFail($item['id']);
                $this->authorize('update', $question->quiz);
                $question->update(['order' => $item['order']]);
            }
        });

        return redirect()->back();
    }

    public function removeImage(Question $question): RedirectResponse
    {
        $this->authorize('update', $question->quiz);

        if ($question->image) {
            Storage::disk(config('quiz.image_disk'))->delete($question->image);
            $question->update(['image' => null]);
        }

        return redirect()->back();
    }
}
