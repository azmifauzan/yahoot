<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportQuizRequest;
use App\Models\Quiz;
use App\Services\QuizExportService;
use App\Services\QuizImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizImportExportController extends Controller
{
    public function __construct(
        private readonly QuizImportService $importService,
        private readonly QuizExportService $exportService,
    ) {}

    public function import(ImportQuizRequest $request): RedirectResponse
    {
        $data = $this->importService->parse($request->file('file'));
        $quiz = $this->importService->import($data, $request->user());

        return redirect()->route('quizzes.edit', $quiz);
    }

    public function export(Request $request, Quiz $quiz): StreamedResponse
    {
        $this->authorize('view', $quiz);

        $format = $request->query('format', 'json') === 'csv' ? 'csv' : 'json';
        $slug = Str::slug($quiz->title) ?: 'quiz';

        if ($format === 'csv') {
            $content = $this->exportService->toCsv($quiz);

            return response()->streamDownload(
                fn () => print ($content),
                "{$slug}.csv",
                ['Content-Type' => 'text/csv'],
            );
        }

        $content = $this->exportService->toJson($quiz);

        return response()->streamDownload(
            fn () => print ($content),
            "{$slug}.json",
            ['Content-Type' => 'application/json'],
        );
    }
}
