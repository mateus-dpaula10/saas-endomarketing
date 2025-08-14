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
use App\Http\Controllers\AdministrationController;
use Illuminate\Support\Facades\Http;

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/login', [AuthController::class, 'index'])->name('login.index');
Route::post('/login', [AuthController::class, 'login'])->name('login.login');
Route::post('/password-reset', [AuthController::class, 'passwordReset'])->name('password.reset');
Route::get('/admin/password-reset/{user}', [AuthController::class, 'showResetForm'])->name('admin.reset.password.form');
Route::post('/admin/password-reset/{user}', [AuthController::class, 'resetPassword'])->name('admin.reset.password');
Route::post('/logout', [AuthController::class, 'logout'])->name('login.logout');
Route::post('/send-email', [HomeController::class, 'store'])->name('home.send.email');

Route::middleware(['auth'])->group(function () {
    Route::resource('/dashboard', DashboardController::class);  
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuario.index');
    Route::get('/usuarios/{usuario}/edit', [UserController::class, 'edit'])->name('usuario.edit');
    Route::patch('/usuarios/{usuario}', [UserController::class, 'update'])->name('usuario.update');
    Route::get('/diagnostico', [DiagnosticController::class, 'index'])->name('diagnostico.index');
    Route::get('/diagnostico/{diagnostico}/answer', [DiagnosticController::class, 'showAnswerForm'])->name('diagnostico.answer.form');
    Route::post('/diagnostico/{diagnostico}/answer', [DiagnosticController::class, 'submitAnswer'])->name('diagnostico.answer');
    Route::get('/notificacoes', [DashboardController::class, 'notification'])->name('notification.index');

    Route::middleware(['auth', 'role:superadmin'])->group(function () {
        Route::resource('/empresa', TenantController::class);    
        Route::get('/diagnostico/create', [DiagnosticController::class, 'create'])->name('diagnostico.create');
        Route::post('/diagnostico', [DiagnosticController::class, 'store'])->name('diagnostico.store');
        Route::get('/diagnostico/{diagnostico}/edit', [DiagnosticController::class, 'edit'])->name('diagnostico.edit');
        Route::patch('/diagnostico/{diagnostico}', [DiagnosticController::class, 'update'])->name('diagnostico.update');
        Route::delete('/diagnostico/{diagnostico}', [DiagnosticController::class, 'destroy'])->name('diagnostico.destroy');
        Route::post('/diagnostico/{id}/reabrir', [DiagnosticController::class, 'reabrir'])->name('diagnostico.reabrir');    
        Route::get('/diagnostico/empresas-por-plano/{plainId}', [DiagnosticController::class, 'empresasPorPlano'])->name('diagnostico.empresas.plano');    
        Route::get('/diagnostico/periodos-por-plano/{plainId}', [DiagnosticController::class, 'getPeriodsByPlain'])->name('diagnostico.empresas.periodos.plano');    
        Route::get('/diagnostico/perguntas-por-plano/{plainId}/{diagnosticId}', [DiagnosticController::class, 'getPerguntasPorPlano'])->name('diagnostico.empresas.perguntas.plano');  
        Route::get('/consulta-cnpj/{cnpj}', function ($cnpj) {
            $cnpj = preg_replace('/\D/', '', $cnpj);
            $response = Http::get("https://www.receitaws.com.br/v1/cnpj/$cnpj");
            return response()->json($response->json());
        });
    });
    
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/diagnostico/disponiveis', [DiagnosticController::class, 'available'])->name('diagnostico.available');    
        Route::post('/admin/notify-pending', [AdminNotificationController::class, 'notifyPendingUsers'])->name('admin.notify.pending');
        Route::get('/administracao', [AdministrationController::class, 'index'])->name('administration.index');
    });
    
    Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
        Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuario.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuario.store');    
        Route::delete('/usuarios/{usuario}', [UserController::class, 'destroy'])->name('usuario.destroy');    
    });
});
