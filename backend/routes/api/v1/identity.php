<?php

use App\Modules\Identity\Http\Controllers\AccountController;
use App\Modules\Identity\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/me', [AuthController::class, 'me']);
Route::put('/me/password', [AuthController::class, 'password']);
Route::get('/accounts', [AccountController::class, 'index']);
Route::post('/accounts', [AccountController::class, 'store']);
Route::put('/accounts/{user}', [AccountController::class, 'update']);
Route::post('/accounts/{user}/reset-password', [AccountController::class, 'resetPassword']);
