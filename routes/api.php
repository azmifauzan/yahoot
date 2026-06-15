<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Player game API (stateless — guests can use these)
Route::prefix('games')->group(function () {
    Route::get('/code/{code}/info', [PlayerController::class, 'apiInfo']);
    Route::post('/join', [PlayerController::class, 'apiJoin']);
    Route::post('/{gameSession}/answer', [PlayerController::class, 'apiAnswer']);
    Route::post('/{gameSession}/leave', [PlayerController::class, 'apiLeave']);
    Route::post('/{gameSession}/react', [PlayerController::class, 'apiReact']);
    Route::post('/{gameSession}/powerup', [PlayerController::class, 'apiUsePowerup']);
    Route::get('/{gameSession}/status', [PlayerController::class, 'apiStatus']);
});
