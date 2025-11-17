<?php

use App\Http\Controllers\Auth\RegisterController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Homepage\HomeController;


Route::controller(HomeController::class)->group(function () {
   Route::get('/', 'index')->name('home');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'index')->name('register');
    Route::post('/register', 'save')->name('register.save');
    Route::get('/register/success', 'success')->name('register.success');
});
