<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GastoRequest;
use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Models\MetodoPago;
use App\Services\BcvService;
use App\Services\ResumenMensualService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GastoDashboardController extends Controller
{
    public function __construct(
        private readonly BcvService $bcvService,
        private readonly ResumenMensualService $resumenes,
    ) {}

    public function index(Request $request): View
    {
        $periodo = $request->validate([
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'anio' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $resumen = $this->resumenes->delMes(
            (int) ($periodo['mes'] ?? Carbon::now()->month),
            (int) ($periodo['anio'] ?? Carbon::now()->year),
        );

        return view('gastos.index', [
            'resumen' => $resumen,
            'tasaBcv' => $this->bcvService->obtenerTasaUsd(),
        ]);
    }

    public function servicios(): View
    {
        $gastos = Gasto::with('metodoPago')->orderBy('dia_pago')->get();
        $tasaBcv = $this->bcvService->obtenerTasaUsd();
        $metodosPago = MetodoPago::orderBy('nombre')->get();

        return view('gastos.servicios', compact('gastos', 'tasaBcv', 'metodosPago'));
    }

    public function store(GastoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Gasto::create($validated);

        return redirect()->route('gastos.servicios')
            ->with('success', "Servicio '{$validated['servicio']}' agregado correctamente.");
    }

    public function update(GastoRequest $request, Gasto $gasto): RedirectResponse
    {
        $gasto->update($request->validated());

        return redirect()->route('gastos.servicios')
            ->with('success', "Servicio '{$gasto->servicio}' actualizado.");
    }

    public function destroy(Gasto $gasto): RedirectResponse
    {
        $nombre = $gasto->servicio;
        $gasto->delete();

        return redirect()->route('gastos.servicios')
            ->with('success', "Servicio '{$nombre}' eliminado.");
    }

    public function toggleActivo(Gasto $gasto): RedirectResponse
    {
        $gasto->update(['activo' => ! $gasto->activo]);

        $estado = $gasto->activo ? 'activado' : 'desactivado';

        return redirect()->route('gastos.servicios')
            ->with('success', "Servicio '{$gasto->servicio}' {$estado}.");
    }

    public function togglePagado(GastoMensual $mensual): RedirectResponse
    {
        $marcarPagado = ! $mensual->pagado;

        $mensual->update([
            'pagado' => $marcarPagado,
            'fecha_pago' => $marcarPagado ? Carbon::now()->toDateString() : null,
            'metodo_pago_id' => $marcarPagado ? $mensual->gasto->metodo_pago_id : null,
        ]);

        $estado = $mensual->pagado ? 'pagado' : 'pendiente';

        return redirect()->back()
            ->with('success', "'{$mensual->gasto->servicio}' marcado como {$estado}.");
    }

    public function subirComprobante(Request $request, GastoMensual $mensual): RedirectResponse
    {
        $request->validate([
            'comprobante' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($mensual->comprobante_path) {
            Storage::disk('public')->delete($mensual->comprobante_path);
        }

        $path = $request->file('comprobante')->store(
            "comprobantes/{$mensual->anio}/{$mensual->mes}",
            'public'
        );

        $mensual->update(['comprobante_path' => $path]);

        return redirect()->back()
            ->with('success', "Comprobante subido para '{$mensual->gasto->servicio}'.");
    }

    public function verComprobante(GastoMensual $mensual): StreamedResponse
    {
        if (! $mensual->comprobante_path || ! Storage::disk('public')->exists($mensual->comprobante_path)) {
            abort(404, 'Comprobante no encontrado.');
        }

        return Storage::disk('public')->download($mensual->comprobante_path);
    }
}
