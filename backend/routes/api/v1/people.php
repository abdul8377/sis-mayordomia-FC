<?php
use App\Modules\People\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;
Route::get('/people',[PersonController::class,'index']);
Route::post('/people',[PersonController::class,'store']);
Route::put('/people/{person}',[PersonController::class,'update']);
Route::put('/people/{person}/participation-profile',[PersonController::class,'profile']);
