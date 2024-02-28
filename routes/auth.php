<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginUserController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ResentEmailVerificationController;
use App\Http\Controllers\Api\Auth\RegisterUserController;


Route::post('/register', RegisterUserController::class)
    ->middleware('guest')
    ->name('register');

Route::post('/login', LoginUserController::class)
    ->middleware('guest')
    ->name('login');

Route::post('/logout', LogoutController::class)
    ->middleware('auth:sanctum')
    ->name('logout');

Route::post('/reset-password', ResetPasswordController::class)
    ->middleware('guest')
    ->name('password.store');

Route::post('/forgot-password', ForgotPasswordController::class)
    ->middleware('guest')
    ->name('password.email');

Route::get('/verify-email/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification/resend', ResentEmailVerificationController::class)
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');
