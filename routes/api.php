<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Player game API (stateless — guests can use these)
Route::prefix('games')->group(function () {
    Route::post('/join', [PlayerController::class, 'apiJoin']);
    Route::post('/{gameSession}/answer', [PlayerController::class, 'apiAnswer']);
    Route::get('/{gameSession}/status', [PlayerController::class, 'apiStatus']);
});
