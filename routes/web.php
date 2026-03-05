<?php

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('landing');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [QuizController::class, 'index'])->name('dashboard');

    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/create', [QuizController::class, 'create'])->name('create');
        Route::post('/', [QuizController::class, 'store'])->name('store');
        Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit');
        Route::put('/{quiz}', [QuizController::class, 'update'])->name('update');
        Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy');
        Route::post('/{quiz}/duplicate', [QuizController::class, 'duplicate'])->name('duplicate');
        Route::post('/{quiz}/publish', [QuizController::class, 'publish'])->name('publish');

        Route::post('/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');
    });

    Route::prefix('questions')->name('questions.')->group(function () {
        Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
        Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');
        Route::delete('/{question}/image', [QuestionController::class, 'removeImage'])->name('remove-image');
    });

    Route::post('/questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
});
