<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BcvService;
use Illuminate\Console\Command;

final class ActualizarTasaBcv extends Command
{
    protected $signature = 'bcv:actualizar-tasa';

    protected $description = 'Actualiza la tasa USD/Bs desde el BCV';

    public function handle(BcvService $bcvService): int
    {
        $this->info('Consultando tasa BCV...');

        $tasa = $bcvService->refrescarTasa();

        if ($tasa === null) {
            $this->error('No se pudo obtener la tasa del BCV.');

            return self::FAILURE;
        }

        $this->info("Tasa actualizada: Bs {$tasa}");

        return self::SUCCESS;
    }
}
