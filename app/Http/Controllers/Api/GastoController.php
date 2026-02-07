<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gasto;
use App\Models\GastoMensual;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class GastoController extends Controller
{
    public function index(): JsonResponse
    {
        $gastos = Gasto::where('activo', true)
            ->orderBy('dia_pago')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gastos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'servicio' => 'required|string|max:255',
            'dia_pago' => 'required|integer|min:1|max:31',
            'monto' => 'nullable|numeric|min:0',
        ]);

        $gasto = Gasto::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Gasto '{$gasto->servicio}' agregado correctamente.",
            'data' => $gasto,
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $gasto = Gasto::findOrFail($id);
        $nombre = $gasto->servicio;
        $gasto->delete();

        return response()->json([
            'success' => true,
            'message' => "Gasto '{$nombre}' eliminado correctamente.",
        ]);
    }

    public function mensuales(Request $request): JsonResponse
    {
        $mes = (int) $request->query('mes', (string) Carbon::now()->month);
        $anio = (int) $request->query('anio', (string) Carbon::now()->year);

        $mensuales = GastoMensual::with('gasto')
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->get()
            ->sortBy(fn (GastoMensual $gm) => $gm->gasto->dia_pago)
            ->values();

        $total = $mensuales->sum(fn (GastoMensual $gm) => (float) $gm->gasto->monto);
        $pagado = $mensuales->where('pagado', true)->sum(fn (GastoMensual $gm) => (float) $gm->gasto->monto);
        $pendiente = $total - $pagado;

        return response()->json([
            'success' => true,
            'data' => [
                'mes' => $mes,
                'anio' => $anio,
                'total' => round($total, 2),
                'pagado' => round($pagado, 2),
                'pendiente' => round($pendiente, 2),
                'gastos' => $mensuales->map(fn (GastoMensual $gm) => [
                    'id' => $gm->id,
                    'gasto_id' => $gm->gasto_id,
                    'servicio' => $gm->gasto->servicio,
                    'dia_pago' => $gm->gasto->dia_pago,
                    'monto' => $gm->gasto->monto,
                    'pagado' => $gm->pagado,
                    'fecha_pago' => $gm->fecha_pago?->format('Y-m-d'),
                    'comprobante' => $gm->comprobante_path ? true : false,
                    'notas' => $gm->notas,
                ]),
            ],
        ]);
    }

    public function generarMes(Request $request): JsonResponse
    {
        $mes = (int) $request->input('mes', Carbon::now()->month);
        $anio = (int) $request->input('anio', Carbon::now()->year);

        $gastos = Gasto::where('activo', true)->get();
        $creados = 0;

        foreach ($gastos as $gasto) {
            $exists = GastoMensual::where('gasto_id', $gasto->id)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->exists();

            if (! $exists) {
                GastoMensual::create([
                    'gasto_id' => $gasto->id,
                    'mes' => $mes,
                    'anio' => $anio,
                ]);
                $creados++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Mes {$mes}/{$anio} generado. {$creados} gastos nuevos creados.",
        ]);
    }

    public function marcarPagado(Request $request, int $id): JsonResponse
    {
        $mensual = GastoMensual::with('gasto')->findOrFail($id);
        $mensual->update([
            'pagado' => true,
            'fecha_pago' => Carbon::now()->toDateString(),
            'notas' => $request->input('notas'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "'{$mensual->gasto->servicio}' marcado como pagado.",
            'data' => $mensual,
        ]);
    }

    public function marcarNoPagado(int $id): JsonResponse
    {
        $mensual = GastoMensual::with('gasto')->findOrFail($id);
        $mensual->update([
            'pagado' => false,
            'fecha_pago' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "'{$mensual->gasto->servicio}' marcado como NO pagado.",
            'data' => $mensual,
        ]);
    }

    public function subirComprobante(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'comprobante' => 'required|image|max:5120',
        ]);

        $mensual = GastoMensual::with('gasto')->findOrFail($id);

        if ($mensual->comprobante_path) {
            Storage::disk('public')->delete($mensual->comprobante_path);
        }

        $path = $request->file('comprobante')->store(
            "comprobantes/{$mensual->anio}/{$mensual->mes}",
            'public'
        );

        $mensual->update(['comprobante_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => "Comprobante subido para '{$mensual->gasto->servicio}'.",
            'data' => [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ],
        ]);
    }

    public function proximos(Request $request): JsonResponse
    {
        $diasAnticipacion = (int) $request->query('dias', '3');
        $hoy = Carbon::now();
        $mes = $hoy->month;
        $anio = $hoy->year;
        $diaActual = $hoy->day;

        $proximos = GastoMensual::with('gasto')
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->where('pagado', false)
            ->get()
            ->filter(function (GastoMensual $gm) use ($diaActual, $diasAnticipacion) {
                $diaPago = $gm->gasto->dia_pago;

                return $diaPago >= $diaActual && $diaPago <= ($diaActual + $diasAnticipacion);
            })
            ->sortBy(fn (GastoMensual $gm) => $gm->gasto->dia_pago)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $proximos->map(fn (GastoMensual $gm) => [
                'id' => $gm->id,
                'servicio' => $gm->gasto->servicio,
                'dia_pago' => $gm->gasto->dia_pago,
                'monto' => $gm->gasto->monto,
            ]),
        ]);
    }

    public function vencidos(): JsonResponse
    {
        $hoy = Carbon::now();
        $mes = $hoy->month;
        $anio = $hoy->year;
        $diaActual = $hoy->day;

        $vencidos = GastoMensual::with('gasto')
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->where('pagado', false)
            ->get()
            ->filter(fn (GastoMensual $gm) => $gm->gasto->dia_pago < $diaActual)
            ->sortBy(fn (GastoMensual $gm) => $gm->gasto->dia_pago)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $vencidos->map(fn (GastoMensual $gm) => [
                'id' => $gm->id,
                'servicio' => $gm->gasto->servicio,
                'dia_pago' => $gm->gasto->dia_pago,
                'monto' => $gm->gasto->monto,
                'dias_vencido' => $diaActual - $gm->gasto->dia_pago,
            ]),
        ]);
    }

    public function buscarPorServicio(Request $request): JsonResponse
    {
        $nombre = $request->query('nombre', '');
        $mes = (int) $request->query('mes', (string) Carbon::now()->month);
        $anio = (int) $request->query('anio', (string) Carbon::now()->year);

        $gasto = Gasto::where('servicio', 'LIKE', "%{$nombre}%")
            ->where('activo', true)
            ->first();

        if (! $gasto) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró el servicio '{$nombre}'.",
            ], 404);
        }

        $mensual = GastoMensual::where('gasto_id', $gasto->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'gasto' => $gasto,
                'mensual' => $mensual,
            ],
        ]);
    }
}
