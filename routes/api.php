<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\OTPController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KhataController;
use App\Http\Controllers\API\TransactionController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', [OTPController::class, 'sendOtp']);
Route::post('/verify-otp', [OTPController::class, 'verifyOtp']);
Route::post('/resend-otp', [OTPController::class, 'resendOtp']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/edit/account', [AuthController::class, 'edit'])->middleware('auth:sanctum');
Route::post('/create/khata', [KhataController::class, 'create'])->middleware('auth:sanctum');
Route::post('/edit/khata/{id}', [KhataController::class, 'editKhata'])->middleware('auth:sanctum');
Route::get('khata/details/{id}', [KhataController::class, 'khataDetails'])->middleware('auth:sanctum');

Route::post('/khata/{khata_id}/transaction/received', [TransactionController::class, 'received'])->middleware('auth:sanctum');
Route::post('/khata/{khata_id}/transaction/payment', [TransactionController::class, 'payment'])->middleware('auth:sanctum');