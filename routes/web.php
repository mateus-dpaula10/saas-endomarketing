<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Http;

// landing page
Route::get('/', [HomeController::class, 'index'])->name('index');
Route::post('/send-email', [HomeController::class, 'store'])->name('home.send.email');

// autenticacao
Route::get('/login', [AuthController::class, 'index'])->name('login.index');
Route::post('/login', [AuthController::class, 'login'])->name('login.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('login.logout');

Route::get('/consulta-cnpj/{cnpj}', function ($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    $response = Http::get("https://www.receitaws.com.br/v1/cnpj/$cnpj");
    return response()->json($response->json());
});

// todo usuario logado
Route::middleware(['auth'])->group(function () {
    Route::resource('/dashboard', DashboardController::class);  

    Route::get('/usuarios', [UserController::class, 'index'])->name('usuario.index');
    Route::get('/usuarios/{usuario}/edit', [UserController::class, 'edit'])->name('usuario.edit');
    Route::patch('/usuarios/{usuario}', [UserController::class, 'update'])->name('usuario.update');

    Route::get('/diagnostico', [DiagnosticController::class, 'index'])->name('diagnostico.index');
    Route::get('/diagnostico/{diagnostic}/answer', [DiagnosticController::class, 'answer'])->name('diagnostico.answer');
    Route::post('/diagnostico/{diagnostic}/answer', [DiagnosticController::class, 'submitAnswers'])->name('diagnostico.submitAnswers');

    // apenas superadmin
    Route::middleware(['auth', 'role:superadmin'])->group(function () {
        Route::get('/diagnostico/{diagnostico}/edit', [DiagnosticController::class, 'edit'])->name('diagnostico.edit');
        Route::patch('/diagnostico/{diagnostico}', [DiagnosticController::class, 'update'])->name('diagnostico.update');
        Route::delete('/diagnostico/{diagnostico}', [DiagnosticController::class, 'destroy'])->name('diagnostico.destroy'); 

        Route::resource('/empresa', TenantController::class);
        Route::resource('/campanha', CampaignController::class);
    });
    
    // superadmin e admin
    Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
        Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuario.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuario.store');    
        Route::delete('/usuarios/{usuario}', [UserController::class, 'destroy'])->name('usuario.destroy');    
    });
});
