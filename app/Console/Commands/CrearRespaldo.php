<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Respaldos\RespaldosBaseDatos;
use Illuminate\Console\Command;

final class CrearRespaldo extends Command
{
    protected $signature = 'respaldos:crear {--conservar=14 : Cantidad de respaldos a guardar}';

    protected $description = 'Crea un respaldo comprimido de la base de datos y borra los más viejos';

    public function handle(RespaldosBaseDatos $respaldos): int
    {
        $respaldo = $respaldos->crear();
        $eliminados = $respaldos->conservarUltimos(max(1, (int) $this->option('conservar')));

        $this->info("Respaldo creado: {$respaldo} ({$respaldos->tamanoLegible($respaldo)}).");

        if ($eliminados > 0) {
            $this->line("Respaldos viejos eliminados: {$eliminados}.");
        }

        return self::SUCCESS;
    }
}
