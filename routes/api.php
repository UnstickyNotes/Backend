<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UpdatedNoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// google OAuth routes
Route::get('/OAuth/{provider}/redirect', [AuthController::class, 'googleOAuth']);
Route::get('/OAuth/{provider}/callback', [AuthController::class, 'googleOAuthCallback']);

// profile routes
Route::middleware(['role:user', 'auth:sanctum'])->prefix('/profile')->group(function(){
    Route::get('/', [ProfileController::class, 'getProfile']);
    Route::post('/pfp', [ProfileController::class, 'setPfp']);
    Route::put('/', [ProfileController::class, 'updateProfile']);
    Route::delete('/', [ProfileController::class, 'deleteAccount']);
});

// Collection routes
Route::middleware(['role:user', 'auth:sanctum'])->prefix('/collections')->group(function(){
    Route::get('/', [CollectionController::class, 'index']);
    Route::post('/', [CollectionController::class, 'store']);
    Route::put('/{id}', [CollectionController::class, 'update']);
    Route::delete('/{id}', [CollectionController::class, 'destroy']);
});

// Note routes
Route::middleware(['role:user', 'auth:sanctum'])->prefix('/notes')->group(function () {
    Route::apiResource('',NoteController::class)->parameters(['' => 'id']);
});

