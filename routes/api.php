<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContextController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\WeeklyReviewController;
use App\Http\Controllers\Api\GTDDashboardController;

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // GTD Dashboard
    Route::get('/dashboard', [GTDDashboardController::class, 'index']);
    
    // Contexts
    Route::apiResource('contexts', ContextController::class);
    
    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::get('/projects/{project}/next-actions', [ProjectController::class, 'nextActions']);
    
    // Items (GTD Items)
    Route::apiResource('items', ItemController::class);
    Route::get('/inbox', [ItemController::class, 'inbox']);
    Route::get('/next-actions', [ItemController::class, 'nextActions']);
    Route::get('/waiting-for', [ItemController::class, 'waitingFor']);
    Route::get('/someday-maybe', [ItemController::class, 'somedayMaybe']);
    Route::get('/reference', [ItemController::class, 'reference']);
    Route::post('/items/{item}/complete', [ItemController::class, 'complete']);
    Route::post('/items/{item}/clarify', [ItemController::class, 'clarify']);
    Route::get('/items/by-context/{context}', [ItemController::class, 'byContext']);
    
    // Weekly Reviews
    Route::apiResource('weekly-reviews', WeeklyReviewController::class);
    Route::get('/weekly-reviews/current', [WeeklyReviewController::class, 'current']);
});
