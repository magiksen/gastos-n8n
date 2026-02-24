<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Services\BcvService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class GastoDashboardController extends Controller
{
    public function __construct(
        private readonly BcvService $bcvService,
    ) {}

    public function index(Request $request): View
    {
        $mes = (int) $request->query('mes', (string) Carbon::now()->month);
        $anio = (int) $request->query('anio', (string) Carbon::now()->year);

        $this->generarMesSiNoExiste($mes, $anio);

        $mensuales = GastoMensual::with('gasto')
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->get()
            ->sortBy(fn (GastoMensual $gm) => $gm->gasto->dia_pago)
            ->values();

        $totalUsd = $mensuales->filter(fn (GastoMensual $gm) => ($gm->gasto->moneda ?? 'USD') === 'USD')->sum(fn (GastoMensual $gm) => (float) ($gm->gasto->monto ?? 0));
        $pagadoUsd = $mensuales->where('pagado', true)->filter(fn (GastoMensual $gm) => ($gm->gasto->moneda ?? 'USD') === 'USD')->sum(fn (GastoMensual $gm) => (float) ($gm->gasto->monto ?? 0));
        $pendienteUsd = $totalUsd - $pagadoUsd;

        $totalBs = $mensuales->filter(fn (GastoMensual $gm) => ($gm->gasto->moneda ?? 'USD') === 'VES')->sum(fn (GastoMensual $gm) => (float) ($gm->gasto->monto ?? 0));
        $pagadoBs = $mensuales->where('pagado', true)->filter(fn (GastoMensual $gm) => ($gm->gasto->moneda ?? 'USD') === 'VES')->sum(fn (GastoMensual $gm) => (float) ($gm->gasto->monto ?? 0));
        $pendienteBs = $totalBs - $pagadoBs;

        $hoy = Carbon::now();
        $diaActual = $hoy->day;

        $proximos = $mensuales->filter(function (GastoMensual $gm) use ($diaActual, $mes, $anio, $hoy) {
            if ($gm->pagado) {
                return false;
            }
            if ($mes !== $hoy->month || $anio !== $hoy->year) {
                return false;
            }
            $dia = $gm->gasto->dia_pago;

            return $dia >= $diaActual && $dia <= ($diaActual + 3);
        });

        $vencidos = $mensuales->filter(function (GastoMensual $gm) use ($diaActual, $mes, $anio, $hoy) {
            if ($gm->pagado) {
                return false;
            }
            if ($mes !== $hoy->month || $anio !== $hoy->year) {
                return false;
            }

            return $gm->gasto->dia_pago < $diaActual;
        });

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $tasaBcv = $this->bcvService->obtenerTasaUsd();

        return view('gastos.index', compact(
            'mensuales', 'totalUsd', 'pagadoUsd', 'pendienteUsd',
            'totalBs', 'pagadoBs', 'pendienteBs',
            'proximos', 'vencidos', 'mes', 'anio', 'meses', 'tasaBcv'
        ));
    }

    public function servicios(): View
    {
        $gastos = Gasto::orderBy('dia_pago')->get();
        $tasaBcv = $this->bcvService->obtenerTasaUsd();

        return view('gastos.servicios', compact('gastos', 'tasaBcv'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'servicio' => 'required|string|max:255',
            'dia_pago' => 'required|integer|min:1|max:31',
            'monto' => 'nullable|numeric|min:0',
            'moneda' => 'required|in:USD,VES',
        ]);

        Gasto::create($validated);

        return redirect()->route('gastos.servicios')
            ->with('success', "Servicio '{$validated['servicio']}' agregado correctamente.");
    }

    public function update(Request $request, Gasto $gasto): RedirectResponse
    {
        $validated = $request->validate([
            'servicio' => 'required|string|max:255',
            'dia_pago' => 'required|integer|min:1|max:31',
            'monto' => 'nullable|numeric|min:0',
            'moneda' => 'required|in:USD,VES',
        ]);

        $gasto->update($validated);

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
        $mensual->update([
            'pagado' => ! $mensual->pagado,
            'fecha_pago' => ! $mensual->pagado ? Carbon::now()->toDateString() : null,
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

    public function verComprobante(GastoMensual $mensual): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! $mensual->comprobante_path || ! Storage::disk('public')->exists($mensual->comprobante_path)) {
            abort(404, 'Comprobante no encontrado.');
        }

        return Storage::disk('public')->download($mensual->comprobante_path);
    }

    private function generarMesSiNoExiste(int $mes, int $anio): void
    {
        $existe = GastoMensual::where('mes', $mes)->where('anio', $anio)->exists();

        if ($existe) {
            return;
        }

        $gastos = Gasto::where('activo', true)->get();

        foreach ($gastos as $gasto) {
            GastoMensual::create([
                'gasto_id' => $gasto->id,
                'mes' => $mes,
                'anio' => $anio,
            ]);
        }
    }
}
