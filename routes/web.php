<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DiagnosticController;

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/login', [AuthController::class, 'index'])->name('login.index');
Route::post('/login', [AuthController::class, 'login'])->name('login.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('login.logout');

Route::middleware(['auth'])->group(function () {
    Route::resource('/dashboard', DashboardController::class);    
    Route::get('/usuario', [UserController::class, 'index'])->name('usuario.index');
    Route::get('/usuario/{usuario}/edit', [UserController::class, 'edit'])->name('usuario.edit');
    Route::patch('/usuario/{usuario}', [UserController::class, 'update'])->name('usuario.update');
});

Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::resource('/empresa', TenantController::class);
    Route::get('/diagnostico/create', [DiagnosticController::class, 'create'])->name('diagnostico.create');
    Route::post('/diagnostico', [DiagnosticController::class, 'store'])->name('diagnostico.store');
    Route::get('/diagnostico/{diagnostico}/edit', [DiagnosticController::class, 'edit'])->name('diagnostico.edit');
    Route::patch('/diagnostico/{diagnostico}', [DiagnosticController::class, 'update'])->name('diagnostico.update');
    Route::delete('/diagnostico/{diagnostico}', [DiagnosticController::class, 'destroy'])->name('diagnostico.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/diagnostico/disponiveis', [DiagnosticController::class, 'available'])->name('diagnostico.available');
    Route::get('/diagnostico/{diagnostico}/answer', [DiagnosticController::class, 'showAnswerForm'])->name('diagnostico.answer.form');
    Route::post('/diagnostico/{diagnostico}/answer', [DiagnosticController::class, 'submitAnswer'])->name('diagnostico.answer');
});

Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::post('/usuario', [UserController::class, 'store'])->name('usuario.store');
    Route::get('/usuario/create', [UserController::class, 'create'])->name('usuario.create');
    Route::delete('/usuario/{usuario}', [UserController::class, 'destroy'])->name('usuario.destroy');
    Route::get('/diagnostico', [DiagnosticController::class, 'index'])->name('diagnostico.index');
});
