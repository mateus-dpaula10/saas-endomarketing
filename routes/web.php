<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;

Route::resource('/', HomeController::class);
Route::get('/login', [AuthController::class, 'index'])->name('login.index');
Route::post('/login', [AuthController::class, 'login'])->name('login.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('login.logout');

Route::middleware(['auth'])->group(function () {
    Route::resource('/dashboard', DashboardController::class);    
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    
});

Route::middleware(['auth', 'role:user'])->group(function () {

});

Route::resource('/empresa', TenantController::class);
Route::resource('/usuario', UserController::class);