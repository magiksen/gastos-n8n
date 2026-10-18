<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Respaldos\RespaldosBaseDatos;
use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

final class EnviarRespaldo extends Command
{
    protected $signature = 'respaldos:enviar';

    protected $description = 'Envía por Telegram el respaldo más reciente de la base de datos';

    public function handle(RespaldosBaseDatos $respaldos, TelegramClient $telegram): int
    {
        $chatId = config('services.telegram.chat_id');

        if (! $telegram->configurado() || blank($chatId)) {
            $this->warn('Telegram no está configurado. Ejecuta: php artisan telegram:configurar');

            return self::SUCCESS;
        }

        $respaldo = $respaldos->masReciente();

        if ($respaldo === null) {
            $this->error('No hay respaldos para enviar. Ejecuta: php artisan respaldos:crear');

            return self::FAILURE;
        }

        $texto = sprintf('Respaldo semanal de <b>%s</b> · %s', e(config('app.name')), $respaldos->tamanoLegible($respaldo));

        if (! $telegram->enviarArchivo($chatId, $respaldos->rutaCompleta($respaldo), $texto)) {
            $this->error('Telegram rechazó el archivo.');

            return self::FAILURE;
        }

        $this->info('Respaldo enviado: '.basename($respaldo));

        return self::SUCCESS;
    }
}
