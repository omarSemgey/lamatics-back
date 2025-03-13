<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientLogController;
use App\Http\Controllers\CountController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserQuizSubmissionController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

// Users

Route::get('users', [UserController::class, 'index'])->middleware('EnsureUserIsAdmin');

Route::get('users/search', [UserController::class, 'search'])->middleware('EnsureUserIsAdmin');

Route::get('users/{user}', [UserController::class, 'show'])->middleware('EnsureUserIsAuthenticated');

Route::post('users', [UserController::class, 'store'])->middleware('EnsureUserIsAdmin');

Route::put('users/{user}', [UserController::class, 'update'])->middleware('EnsureUserIsAuthenticated');

Route::delete('users/{user}',[UserController::class, 'destroy'])->middleware('EnsureUserIsAdmin');

// Quizzes

Route::get('quizzes',[QuizController::class, 'index'])->middleware('EnsureUserIsAuthenticated');

Route::get('quizzes/search',[QuizController::class, 'search'])->middleware('EnsureUserIsAuthenticated');

Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->middleware('EnsureUserIsAuthenticated');

Route::post('quizzes', [QuizController::class, 'store'])->middleware('EnsureUserIsAdmin');

Route::put('quizzes/{quiz}', [QuizController::class, 'update'])->middleware('EnsureUserIsAdmin');

Route::delete('quizzes',[QuizController::class, 'destroy'])->middleware('EnsureUserIsAdmin');

// User quiz submission

Route::post('submissions', [UserQuizSubmissionController::class, 'store'])->middleware('EnsureUserIsAuthenticated');

Route::get('submissions', [UserQuizSubmissionController::class, 'index'])->middleware('EnsureUserIsAuthenticated');

Route::get('submissions/search', [UserQuizSubmissionController::class, 'search'])->middleware('EnsureUserIsAuthenticated');

Route::get('submissions/{quizId}/{userId?}', [UserQuizSubmissionController::class, 'show'])->middleware('EnsureUserIsAuthenticated');

// Counts

Route::get('counts', [CountController::class, 'getCounts'])->middleware('EnsureUserIsAdmin');

Route::get('counts/users', [ CountController::class, 'getUserCount']);

// Logger
Route::post('/client-logs', [ClientLogController::class, 'store'])
    ->middleware(['ParseJWTCookies', 'throttle:30,1']);

// Auth

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('register', [AuthController::class, 'register']);

    Route::post('logout', [AuthController::class, 'logout'])->middleware('ParseJWTCookies');

    Route::post('login', [AuthController::class, 'login']);

    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('ParseJWTCookies');

    Route::get('me', [AuthController::class, 'me'])->middleware('ParseJWTCookies');
});