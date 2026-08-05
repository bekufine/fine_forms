<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/public/forms/{form}', [FormController::class, 'showPublic']);
Route::post('/forms/{form}/responses', [ResponseController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::patch('/user', [AuthController::class, 'updateProfile']);

    Route::get('/admins', [AdminController::class, 'index']);
    Route::post('/admins', [AdminController::class, 'store']);

    Route::apiResource('forms', FormController::class)->except(['show'])->parameters(['forms' => 'form']);
    Route::get('/forms/{form}', [FormController::class, 'show']);
    Route::post('/forms/{form}/duplicate', [FormController::class, 'duplicate']);

    Route::get('/forms/{form}/questions', [QuestionController::class, 'index']);
    Route::post('/forms/{form}/questions', [QuestionController::class, 'store']);
    Route::patch('/forms/{form}/questions/reorder', [QuestionController::class, 'reorder']);
    Route::patch('/forms/{form}/questions/{question}', [QuestionController::class, 'update']);
    Route::delete('/forms/{form}/questions/{question}', [QuestionController::class, 'destroy']);

    Route::get('/forms/{form}/responses', [ResponseController::class, 'index']);
    Route::get('/forms/{form}/stats', [StatsController::class, 'show']);
});
