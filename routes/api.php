<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/set-name', [\App\Http\Controllers\OnboardingController::class, 'setName']);
Route::post('/set-interests', [\App\Http\Controllers\OnboardingController::class, 'setInterests']);
Route::get('/interests', [\App\Http\Controllers\InterestController::class, 'index']);
Route::get('/question', [\App\Http\Controllers\QuestionController::class, 'getQuestion']);
Route::get('/me', fn () => Auth::user()->load('interests'));

