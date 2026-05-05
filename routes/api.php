<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/behavior/verify', [\App\Http\Controllers\BehaviorController::class, 'store']);

Route::middleware('auth:sanctum')->post('/security-chat', \App\Http\Controllers\SecurityChatController::class);
