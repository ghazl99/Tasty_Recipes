<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [Modules\Auth\Http\Controllers\Api\UserController::class, 'register'])->name('register');
    Route::post('verify-otp', [Modules\Auth\Http\Controllers\Api\OtpController::class, 'verifyOtp'])->name('verify-otp');
    Route::post('resend-otp', [Modules\Auth\Http\Controllers\Api\OtpController::class, 'resendOtp'])->name('resend-otp');
    Route::post('login', [Modules\Auth\Http\Controllers\Api\UserController::class, 'login'])->name('login');
    Route::post('update-profile', [Modules\Auth\Http\Controllers\Api\UserController::class, 'updateProfile'])->name('update-profile')->middleware('auth:api');
    Route::post('logout', [Modules\Auth\Http\Controllers\Api\UserController::class, 'logout'])->middleware(['auth:api'])->name('logout');
});
