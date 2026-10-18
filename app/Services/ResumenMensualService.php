<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Support\ResumenMensual;
use Carbon\CarbonImmutable;

final readonly class ResumenMensualService
{
    public function delMes(int $mes, int $anio): ResumenMensual
    {
        $this->generarMesSiNoExiste($mes, $anio);

        $mensuales = GastoMensual::with(['gasto.metodoPago', 'metodoPago'])
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->get()
            ->sortBy(fn (GastoMensual $gm) => $gm->gasto->dia_pago)
            ->values();

        return new ResumenMensual($mensuales, $mes, $anio, CarbonImmutable::now());
    }

    private function generarMesSiNoExiste(int $mes, int $anio): void
    {
        if (GastoMensual::where('mes', $mes)->where('anio', $anio)->exists()) {
            return;
        }

        Gasto::where('activo', true)->each(function (Gasto $gasto) use ($mes, $anio): void {
            GastoMensual::create([
                'gasto_id' => $gasto->id,
                'mes' => $mes,
                'anio' => $anio,
            ]);
        });
    }
}
