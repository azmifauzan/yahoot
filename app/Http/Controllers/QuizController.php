<?php

namespace App\Http\Controllers;

use App\Enums\AnswerColor;
use App\Enums\PointType;
use App\Enums\QuestionType;
use App\Enums\QuizVisibility;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = $user->quizzes()->withCount('questions');

        if ($request->filled('filter') && in_array($request->input('filter'), ['draft', 'published'])) {
            $query->where('is_published', $request->input('filter') === 'published');
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        $quizzes = $query->latest()->get();

        return Inertia::render('Dashboard', [
            'quizzes' => $quizzes,
            'filters' => [
                'filter' => $request->input('filter', 'all'),
                'search' => $request->input('search', ''),
            ],
            'stats' => [
                'total_quizzes' => $user->quizzes()->count(),
                'total_games' => $user->quizzes()->withCount('gameSessions')->get()->sum('game_sessions_count'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Quiz/Editor', [
            'quiz' => null,
            'questionTypes' => QuestionType::cases(),
            'pointTypes' => PointType::cases(),
            'answerColors' => collect(AnswerColor::cases())->map(fn (AnswerColor $color) => [
                'value' => $color->value,
                'hex' => $color->hex(),
                'shape' => $color->shape(),
            ]),
        ]);
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('quiz-covers', 's3');
        }

        $quiz = $request->user()->quizzes()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'visibility' => $data['visibility'] ?? QuizVisibility::Private,
            'is_published' => false,
        ]);

        return redirect()->route('quizzes.edit', $quiz);
    }

    public function edit(Quiz $quiz): Response
    {
        $this->authorize('update', $quiz);

        $quiz->load('questions.answers');

        return Inertia::render('Quiz/Editor', [
            'quiz' => $quiz,
            'questionTypes' => QuestionType::cases(),
            'pointTypes' => PointType::cases(),
            'answerColors' => collect(AnswerColor::cases())->map(fn (AnswerColor $color) => [
                'value' => $color->value,
                'hex' => $color->hex(),
                'shape' => $color->shape(),
            ]),
        ]);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($quiz->cover_image) {
                Storage::disk('s3')->delete($quiz->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('quiz-covers', 's3');
        }

        $quiz->update($data);

        return redirect()->back();
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->authorize('delete', $quiz);

        if ($quiz->cover_image) {
            Storage::disk('s3')->delete($quiz->cover_image);
        }

        $quiz->delete();

        return redirect()->route('dashboard');
    }

    public function duplicate(Quiz $quiz): RedirectResponse
    {
        $this->authorize('duplicate', $quiz);

        $newQuiz = DB::transaction(function () use ($quiz) {
            $newQuiz = $quiz->replicate(['id', 'created_at', 'updated_at']);
            $newQuiz->title = $quiz->title.' (Salinan)';
            $newQuiz->is_published = false;
            $newQuiz->save();

            foreach ($quiz->questions()->with('answers')->get() as $question) {
                $newQuestion = $question->replicate(['id', 'quiz_id', 'created_at', 'updated_at']);
                $newQuestion->quiz_id = $newQuiz->id;
                $newQuestion->save();

                foreach ($question->answers as $answer) {
                    $newAnswer = $answer->replicate(['id', 'question_id', 'created_at', 'updated_at']);
                    $newAnswer->question_id = $newQuestion->id;
                    $newAnswer->save();
                }
            }

            return $newQuiz;
        });

        return redirect()->route('quizzes.edit', $newQuiz);
    }

    public function publish(Quiz $quiz): RedirectResponse
    {
        $this->authorize('publish', $quiz);

        $quiz->load('questions.answers');

        $errors = $this->validateQuizForPublishing($quiz);

        if (count($errors) > 0) {
            return redirect()->back()->withErrors(['publish' => $errors]);
        }

        $quiz->update(['is_published' => ! $quiz->is_published]);

        return redirect()->back();
    }

    /**
     * Validate quiz meets publishing requirements.
     *
     * @return array<int, string>
     */
    private function validateQuizForPublishing(Quiz $quiz): array
    {
        $errors = [];

        if ($quiz->questions->isEmpty()) {
            $errors[] = __('Minimal 1 pertanyaan');

            return $errors;
        }

        foreach ($quiz->questions as $index => $question) {
            $num = $index + 1;

            if (empty($question->question_text)) {
                $errors[] = __("Pertanyaan {$num}: harus memiliki teks");
            }

            if ($question->type === QuestionType::MultipleChoice) {
                if ($question->answers->count() < 2) {
                    $errors[] = __("Pertanyaan {$num}: minimal 2 jawaban");
                }

                if (! $question->answers->contains('is_correct', true)) {
                    $errors[] = __("Pertanyaan {$num}: harus ada jawaban benar");
                }
            }

            if ($question->type === QuestionType::TrueFalse) {
                if ($question->answers->count() !== 2) {
                    $errors[] = __("Pertanyaan {$num}: harus tepat 2 jawaban");
                }

                if (! $question->answers->contains('is_correct', true)) {
                    $errors[] = __("Pertanyaan {$num}: harus ada jawaban benar");
                }
            }
        }

        return $errors;
    }
}
