<?php

use App\Modules\Configuration\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/catalogs', [SettingsController::class, 'catalogs']);
Route::post('/catalogs/{catalog}', [SettingsController::class, 'catalogStore']);
Route::put('/catalogs/{catalog}/{id}', [SettingsController::class, 'catalogUpdate']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::put('/settings', [SettingsController::class, 'update']);
