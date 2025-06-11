<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

Route::resource('/', HomeController::class);
Route::get('/login', [AuthController::class, 'index'])->name('auth.login');