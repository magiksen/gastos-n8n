<?php

use App\Http\Controllers\GastoDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('gastos.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('gastos.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/gastos', [GastoDashboardController::class, 'index'])->name('gastos.index');
    Route::get('/servicios', [GastoDashboardController::class, 'servicios'])->name('gastos.servicios');
    Route::post('/servicios', [GastoDashboardController::class, 'store'])->name('gastos.store');
    Route::put('/servicios/{gasto}', [GastoDashboardController::class, 'update'])->name('gastos.update');
    Route::delete('/servicios/{gasto}', [GastoDashboardController::class, 'destroy'])->name('gastos.destroy');
    Route::patch('/servicios/{gasto}/toggle-activo', [GastoDashboardController::class, 'toggleActivo'])->name('gastos.toggle-activo');

    Route::post('/gastos/{mensual}/toggle', [GastoDashboardController::class, 'togglePagado'])->name('gastos.toggle');
    Route::post('/gastos/{mensual}/comprobante', [GastoDashboardController::class, 'subirComprobante'])->name('gastos.comprobante');
    Route::get('/gastos/{mensual}/comprobante', [GastoDashboardController::class, 'verComprobante'])->name('gastos.ver-comprobante');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
