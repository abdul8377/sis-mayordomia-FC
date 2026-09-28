<?php

use App\Modules\SmallGroups\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::get('/groups', [GroupController::class, 'index']);
Route::post('/groups', [GroupController::class, 'store']);
Route::put('/groups/{group}', [GroupController::class, 'update']);
