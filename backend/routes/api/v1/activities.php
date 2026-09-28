<?php

use App\Modules\YouthMinistry\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/activities', [ActivityController::class, 'index']);
Route::post('/activities', [ActivityController::class, 'store']);
Route::get('/rotation', [ActivityController::class, 'rotation']);
Route::get('/activities/{activity}', [ActivityController::class, 'show']);
Route::put('/activities/{activity}', [ActivityController::class, 'update']);
Route::post('/activities/{activity}/opportunities', [ActivityController::class, 'opportunity']);
Route::post('/activities/{activity}/close', [ActivityController::class, 'close']);
Route::post('/activities/{activity}/reopen', [ActivityController::class, 'reopen']);
