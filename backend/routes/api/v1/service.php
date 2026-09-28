<?php

use App\Modules\Service\Http\Controllers\ServiceNeedController;
use Illuminate\Support\Facades\Route;

Route::get('/service-needs', [ServiceNeedController::class, 'index']);
Route::post('/service-needs', [ServiceNeedController::class, 'store']);
Route::post('/service-needs/{need}/convert', [ServiceNeedController::class, 'convert']);
