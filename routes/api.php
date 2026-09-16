<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SyncController;
use Illuminate\Http\Request;
use Illuminate\Queue\Connectors\SyncConnector;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function (){
    return response([],200);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// google OAuth routes
Route::get('/OAuth/{provider}/redirect', [AuthController::class, 'googleOAuth']);
Route::get('/OAuth/{provider}/callback', [AuthController::class, 'googleOAuthCallback']);

// profile routes
Route::middleware(['auth:sanctum', 'role:user'])->prefix('/profile')->group(function () {
    Route::get('/', [ProfileController::class, 'getProfile']);
    Route::post('/pfp', [ProfileController::class, 'setPfp']);
    Route::put('/', [ProfileController::class, 'updateProfile']);
    Route::delete('/{password}', [ProfileController::class, 'deleteAccount']);
});

// Collection routes
Route::middleware(['auth:sanctum', 'role:user'])->prefix('/collections')->group(function () {
    Route::get('/', [CollectionController::class, 'index']);
    Route::post('/', [CollectionController::class, 'store']);
    Route::put('/{id}', [CollectionController::class, 'update']);
    Route::delete('/{id}', [CollectionController::class, 'destroy']);
});

// Note routes
Route::middleware(['auth:sanctum', 'role:user'])->prefix('/notes')->group(function () {
    Route::apiResource('', NoteController::class)->parameters(['' => 'id']);
});

Route::middleware(['auth:sanctum', 'role:user'])->prefix('/sync')->group(function() {
    Route::get('/pull/{user_id}/{last_synced_at}', [SyncController::class, 'pull']);
    Route::post('/push', [SyncController::class, 'push']);
});

 