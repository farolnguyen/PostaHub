<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\UserRegisterController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Auth\AdminRegisterController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest:web')->group(function () {
    Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('user.login.form');
    Route::post('/login', [UserAuthController::class, 'login'])->name('user.login.submitsubmit');
    Route::get('/register', [UserRegisterController::class, 'showRegistrationForm'])->name('user.register.form');
    Route::post('/register', [UserRegisterController::class, 'register'])->name('user.register.submit');
});

Route::middleware('auth:web')->group(function () {
    Route::get('/logout', [UserAuthController::class, 'logout'])->name('user.logout');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login.form');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [AdminRegisterController::class, 'showRegisterForm'])->name('register.form');
        Route::post('/register', [AdminRegisterController::class, 'register'])->name('register.submit');
    });
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });
});