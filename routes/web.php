<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionImageController;
use App\Http\Controllers\QuizAnalyticsController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizImportExportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('landing');

Route::get('/images/{path}', [QuestionImageController::class, 'show'])
    ->where('path', 'question-images/[A-Za-z0-9]+\.(jpg|jpeg|png|gif|bmp|webp)')
    ->name('question-images.show');

// Google OAuth routes (guests only)
Route::middleware('guest')->prefix('auth/google')->name('auth.google.')->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [GoogleAuthController::class, 'callback'])->name('callback');
});

// Player routes (no auth required — guests can play)
Route::get('/play', [PlayerController::class, 'join'])->name('play');
Route::get('/play/{code}', [PlayerController::class, 'play'])->name('play.game');

// Global leaderboard (public)
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

// Practice / solo mode (public for published+public quizzes; owner for private)
Route::get('/quizzes/{quiz}/practice', [PracticeController::class, 'start'])->name('quizzes.practice');
Route::post('/quizzes/{quiz}/practice', [PracticeController::class, 'submit'])->name('quizzes.practice.submit');

// Admin routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'admin',
])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('users')->name('users.')->group(function (): void {
        Route::get('/', [Admin\UserController::class, 'index'])->name('index');
        Route::get('/{user}', [Admin\UserController::class, 'show'])->name('show');
        Route::put('/{user}', [Admin\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [Admin\UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('quizzes')->name('quizzes.')->group(function (): void {
        Route::get('/', [Admin\QuizController::class, 'index'])->name('index');
        Route::get('/{quiz}', [Admin\QuizController::class, 'show'])->name('show');
        Route::delete('/{id}', [Admin\QuizController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [Admin\QuizController::class, 'restore'])->name('restore');
    });

    Route::prefix('games')->name('games.')->group(function (): void {
        Route::get('/', [Admin\GameController::class, 'index'])->name('index');
        Route::get('/{gameSession}', [Admin\GameController::class, 'show'])->name('show');
        Route::delete('/{gameSession}', [Admin\GameController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('categories')->name('categories.')->group(function (): void {
        Route::get('/', [Admin\CategoryController::class, 'index'])->name('index');
        Route::post('/', [Admin\CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [Admin\CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [Admin\CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::get('/settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [QuizController::class, 'index'])->name('dashboard');

    // Cross-session progress + earned achievement badges
    Route::get('/me/progress', [ProgressController::class, 'index'])->name('progress');

    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/create', [QuizController::class, 'create'])->name('create');
        Route::post('/import', [QuizImportExportController::class, 'import'])->name('import');
        Route::get('/{quiz}/export', [QuizImportExportController::class, 'export'])->name('export');
        Route::get('/{quiz}/analytics', [QuizAnalyticsController::class, 'index'])->name('analytics');
        Route::post('/', [QuizController::class, 'store'])->name('store');
        Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit');
        Route::put('/{quiz}', [QuizController::class, 'update'])->name('update');
        Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy');
        Route::post('/{quiz}/duplicate', [QuizController::class, 'duplicate'])->name('duplicate');
        Route::post('/{quiz}/publish', [QuizController::class, 'publish'])->name('publish');
        Route::get('/{quiz}/history', [GameSessionController::class, 'history'])->name('history');

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
        Route::post('/{gameSession}/cancel', [GameSessionController::class, 'cancel'])->name('cancel');
        Route::get('/{gameSession}/results', [GameSessionController::class, 'results'])->name('results');
        Route::get('/{gameSession}/stats', [GameSessionController::class, 'stats'])->name('stats');
        Route::get('/{gameSession}/report', [GameSessionController::class, 'report'])->name('report');
        Route::get('/{gameSession}/export', [GameSessionController::class, 'export'])->name('export');
    });
});
