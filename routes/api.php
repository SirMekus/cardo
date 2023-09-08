<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);

Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {

    Route::get('user', function () {
        return request()->user();
    });

    Route::get('logout', [App\Http\Controllers\AuthController::class, 'logout']);

    Route::post('create-card', [App\Http\Controllers\CardController::class, 'createCard']);

    Route::get('get-merchants', [App\Http\Controllers\MerchantController::class, 'getMerchants']);

    Route::post('create-task', [App\Http\Controllers\TaskController::class, 'createTask']);

    Route::post('mark-task', [App\Http\Controllers\TaskController::class, 'marktask']);

    Route::get('latest-finished-tasks', [App\Http\Controllers\MerchantController::class, 'latestFinishedTask']);

});
