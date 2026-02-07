<?php

use App\Http\Controllers\Api\GastoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Token middleware para n8n
Route::middleware('api.token')->prefix('gastos')->group(function () {
    Route::get('/', [GastoController::class, 'index']);
    Route::post('/', [GastoController::class, 'store']);
    Route::delete('/{id}', [GastoController::class, 'destroy']);

    Route::get('/mensuales', [GastoController::class, 'mensuales']);
    Route::post('/generar-mes', [GastoController::class, 'generarMes']);

    Route::put('/mensuales/{id}/pagar', [GastoController::class, 'marcarPagado']);
    Route::put('/mensuales/{id}/no-pagar', [GastoController::class, 'marcarNoPagado']);
    Route::post('/mensuales/{id}/comprobante', [GastoController::class, 'subirComprobante']);

    Route::get('/proximos', [GastoController::class, 'proximos']);
    Route::get('/vencidos', [GastoController::class, 'vencidos']);
    Route::get('/buscar', [GastoController::class, 'buscarPorServicio']);
});
