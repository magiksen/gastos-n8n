<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Telegram\AvisosDePago;
use Illuminate\Console\Command;

final class EnviarAvisosTelegram extends Command
{
    protected $signature = 'telegram:avisos';

    protected $description = 'Envía por Telegram los pagos que vencen hoy, los vencidos y los próximos';

    public function handle(AvisosDePago $avisos): int
    {
        if (! $avisos->disponibles()) {
            $this->warn('Telegram no está configurado. Ejecuta: php artisan telegram:configurar');

            return self::SUCCESS;
        }

        $enviados = $avisos->enviar();

        $this->info($enviados === 0 ? 'Todo al día, no se envió ningún aviso.' : "Avisos enviados: {$enviados}.");

        return self::SUCCESS;
    }
}
