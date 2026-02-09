<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\ResumenGastosDiario;
use App\Models\Gasto;
use App\Models\GastoMensual;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

final class EnviarResumenGastos extends Command
{
    protected $signature = 'gastos:resumen-diario';

    protected $description = 'Envía un resumen diario de gastos vencidos y próximos por email';

    public function handle(): int
    {
        $hoy = Carbon::now();
        $mes = $hoy->month;
        $anio = $hoy->year;
        $diaActual = $hoy->day;

        // Auto-generar mes si no existe
        $existe = GastoMensual::where('mes', $mes)->where('anio', $anio)->exists();
        if (! $existe) {
            $gastos = Gasto::where('activo', true)->get();
            foreach ($gastos as $gasto) {
                GastoMensual::create([
                    'gasto_id' => $gasto->id,
                    'mes' => $mes,
                    'anio' => $anio,
                ]);
            }
            $this->info("Mes {$mes}/{$anio} generado automáticamente.");
        }

        $mensuales = GastoMensual::with('gasto')
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->where('pagado', false)
            ->get();

        $vencidos = $mensuales->filter(fn (GastoMensual $gm) => $gm->gasto->dia_pago < $diaActual)
            ->sortBy(fn (GastoMensual $gm) => $gm->gasto->dia_pago)
            ->values();

        $proximos = $mensuales->filter(function (GastoMensual $gm) use ($diaActual) {
            $dia = $gm->gasto->dia_pago;

            return $dia >= $diaActual && $dia <= ($diaActual + 3);
        })
            ->sortBy(fn (GastoMensual $gm) => $gm->gasto->dia_pago)
            ->values();

        if ($vencidos->count() === 0 && $proximos->count() === 0) {
            $this->info('No hay pagos vencidos ni próximos. No se envía email.');

            return self::SUCCESS;
        }

        $totalPendiente = $mensuales->sum(fn (GastoMensual $gm) => (float) ($gm->gasto->monto ?? 0));

        $destinatario = config('services.notification_email');

        if (! $destinatario) {
            $this->error('NOTIFICATION_EMAIL no configurado en .env');

            return self::FAILURE;
        }

        Mail::to($destinatario)->send(new ResumenGastosDiario(
            vencidos: $vencidos,
            proximos: $proximos,
            mes: $mes,
            anio: $anio,
            totalPendiente: $totalPendiente,
        ));

        $this->info("Resumen enviado a {$destinatario}. Vencidos: {$vencidos->count()}, Próximos: {$proximos->count()}");

        return self::SUCCESS;
    }
}
