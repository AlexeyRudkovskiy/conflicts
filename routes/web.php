<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/api')->group(function () {
    Route::post('create-game', [ \App\Http\Controllers\ApiController::class, 'createGame' ]);
    Route::post('join', [ \App\Http\Controllers\ApiController::class, 'joinGame' ]);
    Route::post('next-question', [ \App\Http\Controllers\ApiController::class, 'nextQuestion' ]);
    Route::post('start', [ \App\Http\Controllers\ApiController::class, 'startGame' ]);
    Route::post('place-answer', [\App\Http\Controllers\ApiController::class, 'placeAnswer']);
    Route::post('select-answer', [\App\Http\Controllers\ApiController::class, 'selectAnswer']);
    Route::post('restart', [\App\Http\Controllers\ApiController::class, 'restart']);
});
