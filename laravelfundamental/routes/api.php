<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ReadingController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);

Route::middleware('api.token')->group(function (): void {
    Route::post('/books/upload', [BookController::class, 'upload']);
    Route::patch('/books/{book}', [BookController::class, 'update']);
    Route::delete('/books/{book}', [BookController::class, 'destroy']);
    Route::get('/books/{book}/pages/meta', [BookController::class, 'pageMeta']);
    Route::get('/books/{book}/pages/{page}', [BookController::class, 'pageImage']);
    Route::get('/books/{book}/stream', [BookController::class, 'stream']);

    Route::get('/users', [UserController::class, 'index']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    Route::post('/reading/start', [ReadingController::class, 'start']);
    Route::post('/reading/progress', [ReadingController::class, 'progress']);
    Route::get('/reading/report', [ReadingController::class, 'report']);

    Route::post('/quizzes', [QuizController::class, 'store']);
    Route::get('/quizzes', [QuizController::class, 'index']);
    Route::patch('/quizzes/{quiz}', [QuizController::class, 'update']);
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy']);
    Route::get('/books/{book}/quiz', [QuizController::class, 'showBookQuiz']);
    Route::post('/quizzes/submit', [QuizController::class, 'submit']);
});
