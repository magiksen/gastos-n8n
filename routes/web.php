<?php

use App\Http\Controllers\GastoDashboardController;
use App\Http\Controllers\MetodoPagoController;
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
    Route::patch('/gastos/{mensual}/metodo-pago', [GastoDashboardController::class, 'actualizarMetodoPago'])->name('gastos.metodo-pago');

    Route::get('/metodos-pago', [MetodoPagoController::class, 'index'])->name('metodos-pago.index');
    Route::post('/metodos-pago', [MetodoPagoController::class, 'store'])->name('metodos-pago.store');
    Route::put('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'update'])->name('metodos-pago.update');
    Route::delete('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'destroy'])->name('metodos-pago.destroy');
    Route::patch('/metodos-pago/{metodoPago}/toggle-activo', [MetodoPagoController::class, 'toggleActivo'])->name('metodos-pago.toggle-activo');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
