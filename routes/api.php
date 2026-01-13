<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\OTPController;
use App\Http\Controllers\API\AuthController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', [OTPController::class, 'sendOtp']);
Route::post('/verify-otp', [OTPController::class, 'verifyOtp']);
Route::post('/resend-otp', [OTPController::class, 'resendOtp']);

Route::post('/create/khata', [KhataController::class, 'create'])->middleware('auth:sanctum');