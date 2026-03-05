<?php

use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionImageController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('landing');

Route::get('/images/{path}', [QuestionImageController::class, 'show'])
    ->where('path', '.*')
    ->name('question-images.show');

// Player routes (no auth required — guests can play)
Route::get('/play', [PlayerController::class, 'join'])->name('play');
Route::get('/play/{code}', [PlayerController::class, 'play'])->name('play.game');

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

    // Game session routes (host)
    Route::prefix('game-sessions')->name('game.')->group(function () {
        Route::post('/{quiz}', [GameSessionController::class, 'store'])->name('store');
        Route::get('/{gameSession}/host', [GameSessionController::class, 'host'])->name('host');
        Route::post('/{gameSession}/start', [GameSessionController::class, 'start'])->name('start');
        Route::post('/{gameSession}/next', [GameSessionController::class, 'next'])->name('next');
        Route::post('/{gameSession}/reveal', [GameSessionController::class, 'reveal'])->name('reveal');
        Route::post('/{gameSession}/end', [GameSessionController::class, 'end'])->name('end');
        Route::get('/{gameSession}/results', [GameSessionController::class, 'results'])->name('results');
        Route::get('/{gameSession}/export', [GameSessionController::class, 'export'])->name('export');
    });
});
