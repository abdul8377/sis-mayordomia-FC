<?php

use App\Modules\Reporting\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/my-group/dashboard', [DashboardController::class, 'index']);
Route::get('/reports', [DashboardController::class, 'reports']);
