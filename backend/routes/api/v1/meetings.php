<?php
use App\Modules\SmallGroups\Http\Controllers\MeetingController;
use App\Modules\SmallGroups\Http\Controllers\FollowUpController;
use Illuminate\Support\Facades\Route;
Route::get('/meetings',[MeetingController::class,'index']);
Route::post('/meetings',[MeetingController::class,'store']);
Route::put('/meetings/{meeting}/attendance',[MeetingController::class,'update']);
Route::post('/meetings/{meeting}/guests',[MeetingController::class,'guest']);
Route::get('/meetings/{meeting}/prayers',[MeetingController::class,'prayers']);
Route::post('/meetings/{meeting}/prayers',[MeetingController::class,'prayer']);
Route::get('/absence-alerts',[FollowUpController::class,'index']);
Route::post('/absence-alerts/{alert}/follow-ups',[FollowUpController::class,'store']);
