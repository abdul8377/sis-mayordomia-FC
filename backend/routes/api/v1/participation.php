<?php

use App\Modules\Participation\Http\Controllers\CommitmentController;
use Illuminate\Support\Facades\Route;

Route::post('/opportunities/{opportunity}/commitments', [CommitmentController::class, 'store']);
Route::get('/opportunities/{opportunity}/recommendations', [CommitmentController::class, 'recommendations']);
Route::post('/commitments/{commitment}/cancel', [CommitmentController::class, 'cancel']);
