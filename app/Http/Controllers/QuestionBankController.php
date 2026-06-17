<?php

namespace App\Http\Controllers;

use App\Enums\AnswerColor;
use App\Http\Requests\FromBankRequest;
use App\Http\Requests\StoreBankQuestionRequest;
use App\Models\BankQuestion;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QuestionBankController extends Controller
{
    private const DEFAULT_COLORS = ['red', 'blue', 'yellow', 'green'];

    public function index(Request $request): Response
    {
        $query = $request->user()->bankQuestions()->with('category')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%'.$request->input('search').'%');
        }

        return Inertia::render('QuestionBank/Index', [
            'items' => $query->get(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'type' => $request->input('type', ''),
                'category' => $request->input('category', ''),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $query = $request->user()->bankQuestions()->with('category')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%'.$request->input('search').'%');
        }

        return response()->json(['items' => $query->limit(100)->get()]);
    }

    public function store(StoreBankQuestionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $request->user()->bankQuestions()->create([
            'type' => $data['type'],
            'question_text' => $data['question_text'],
            'time_limit' => $data['time_limit'] ?? 20,
            'points' => $data['points'] ?? 'standard',
            'category_id' => $data['category_id'] ?? null,
            'answers' => $this->normaliseAnswers($data['answers']),
        ]);

        return redirect()->back();
    }

    public function destroy(BankQuestion $bankQuestion): RedirectResponse
    {
        $this->authorize('delete', $bankQuestion);

        $bankQuestion->delete();

        return redirect()->back();
    }

    public function fromBank(FromBankRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $items = $request->user()->bankQuestions()
            ->whereIn('id', $request->validated()['item_ids'])
            ->get();

        DB::transaction(function () use ($quiz, $items) {
            $order = (int) $quiz->questions()->max('order');
            $order = $quiz->questions()->count() > 0 ? $order + 1 : 0;

            foreach ($items as $item) {
                $question = $quiz->questions()->create([
                    'type' => $item->type,
                    'question_text' => $item->question_text,
                    'image' => $item->image,
                    'time_limit' => $item->time_limit,
                    'points' => $item->points,
                    'order' => $order++,
                ]);

                foreach (array_values($item->answers) as $index => $answer) {
                    $color = $answer['color'] ?? (self::DEFAULT_COLORS[$index] ?? 'red');
                    $question->answers()->create([
                        'answer_text' => $answer['answer_text'] ?? '',
                        'is_correct' => (bool) ($answer['is_correct'] ?? false),
                        'color' => $color,
                        'shape' => $answer['shape'] ?? AnswerColor::from($color)->shape(),
                        'order' => $index,
                    ]);
                }
            }
        });

        return redirect()->back();
    }

    /**
     * @param  array<int, array<string, mixed>>  $answers
     * @return array<int, array<string, mixed>>
     */
    private function normaliseAnswers(array $answers): array
    {
        return collect(array_values($answers))->map(function (array $answer, int $index) {
            $color = $answer['color'] ?? (self::DEFAULT_COLORS[$index] ?? 'red');

            return [
                'answer_text' => $answer['answer_text'] ?? '',
                'is_correct' => (bool) ($answer['is_correct'] ?? false),
                'color' => $color,
                'shape' => AnswerColor::from($color)->shape(),
            ];
        })->all();
    }
}
