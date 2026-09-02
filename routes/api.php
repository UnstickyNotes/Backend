<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\http\Middleware\AuthMiddleware;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
//google OAuth routes
Route::get('/OAuth/{provider}/redirect', [AuthController::class, 'googleOAuth']);
Route::get('/OAuth/{provider}/callback', [AuthController::class, 'googleOAuthCallback']);

Route::middleware(['role:user', 'auth:sanctum'])->prefix('/profile')->group(function(){
    Route::get('/', [ProfileController::class, 'getProfile']);
    Route::post('/pfp', [ProfileController::class, 'setPfp']);
    Route::put('/', [ProfileController::class, 'updateProfile']);
    Route::delete('/', [ProfileController::class, 'deleteProfile']);
});
