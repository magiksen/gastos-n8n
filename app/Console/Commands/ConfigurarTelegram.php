<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use App\Support\ArchivoEnv;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ConfigurarTelegram extends Command
{
    protected $signature = 'telegram:configurar {--intentos=6 : Veces que se espera el mensaje /start (unos 20 segundos cada una)}';

    protected $description = 'Conecta el bot de Telegram: guarda el token y el chat, y registra el webhook';

    public function handle(TelegramClient $telegram): int
    {
        $token = trim((string) $this->secret('Pega el token que te dio @BotFather'));
        $bot = $telegram->conToken($token);
        $nombre = $bot->nombreDelBot();

        if ($nombre === null) {
            $this->error('El token no es válido. Revísalo en @BotFather e inténtalo de nuevo.');

            return self::FAILURE;
        }

        $bot->quitarWebhook();
        $this->info("Bot @{$nombre} encontrado.");
        $this->line("Abre Telegram, busca @{$nombre} y envíale /start. Te espero...");

        $chatId = $this->esperarChat($bot);

        if ($chatId === null) {
            $this->error('No recibí ningún mensaje /start. Vuelve a ejecutar el comando cuando estés listo.');

            return self::FAILURE;
        }

        $secreto = Str::random(48);

        (new ArchivoEnv($this->laravel->environmentFilePath()))->establecer([
            'TELEGRAM_BOT_TOKEN' => $token,
            'TELEGRAM_CHAT_ID' => (string) $chatId,
            'TELEGRAM_WEBHOOK_SECRET' => $secreto,
        ]);

        $urlWebhook = rtrim((string) config('app.url'), '/').route('telegram.webhook', absolute: false);

        if (! $bot->configurarWebhook($urlWebhook, $secreto)) {
            $this->error('Guardé la configuración, pero Telegram rechazó el webhook. Revisa que APP_URL sea la URL pública con https.');

            return self::FAILURE;
        }

        $bot->enviarMensaje($chatId, 'Listo, <b>'.e(config('app.name')).'</b> está conectado. Cada día a las 8:00 te avisaré de los pagos vencidos y de los que vencen pronto.');

        if ($this->laravel->configurationIsCached()) {
            $this->call('config:cache');
        }

        $this->info('Telegram configurado. Revisa el mensaje de prueba en tu chat.');

        return self::SUCCESS;
    }

    private function esperarChat(TelegramClient $bot): ?int
    {
        for ($intento = 0; $intento < (int) $this->option('intentos'); $intento++) {
            if ($chatId = $bot->chatQueEnvioStart()) {
                return $chatId;
            }
        }

        return null;
    }
}
