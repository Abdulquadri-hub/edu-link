<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Homepage\HomeController;
use App\Http\Controllers\Auth\VerificationController;


Route::controller(HomeController::class)->group(function () {
   Route::get('/', 'index')->name('home');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'index')->name('register');
    Route::post('/register', 'save')->name('register.save');
    Route::get('/register/success', 'success')->name('register.success');
});

Route::controller(VerificationController::class)->middleware('auth')->group(function () {
    Route::get('/email/verify', 'notice')
        ->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', 'verify')
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::post('/email/verification-notification', 'resend')
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

