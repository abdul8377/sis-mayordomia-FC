<?php

use App\Modules\Surveys\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/surveys', [SurveyController::class, 'index']);
Route::post('/surveys', [SurveyController::class, 'store']);
Route::put('/surveys/{survey}/response', [SurveyController::class, 'respond']);
