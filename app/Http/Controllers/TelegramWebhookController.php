<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GastoMensual;
use App\Services\Telegram\AvisosDePago;
use App\Services\Telegram\TelegramClient;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly TelegramClient $telegram,
        #[Config('services.telegram.chat_id')] private readonly ?string $chatId,
        #[Config('services.telegram.webhook_secret')] private readonly ?string $secreto,
    ) {}

    public function __invoke(Request $request): Response
    {
        abort_unless($this->secretoValido((string) $request->header('X-Telegram-Bot-Api-Secret-Token')), 403);

        $boton = $request->input('callback_query');

        if (is_array($boton) && $this->vieneDelChatConfigurado($boton)) {
            $this->procesarBoton($boton);
        }

        return response()->noContent();
    }

    private function secretoValido(string $secretoRecibido): bool
    {
        return filled($this->secreto) && hash_equals($this->secreto, $secretoRecibido);
    }

    /**
     * @param  array<string, mixed>  $boton
     */
    private function vieneDelChatConfigurado(array $boton): bool
    {
        return filled($this->chatId) && (string) data_get($boton, 'message.chat.id') === (string) $this->chatId;
    }

    /**
     * @param  array<string, mixed>  $boton
     */
    private function procesarBoton(array $boton): void
    {
        $datos = (string) data_get($boton, 'data');

        if (! str_starts_with($datos, AvisosDePago::PREFIJO_PAGAR)) {
            return;
        }

        $mensual = GastoMensual::with('gasto')->find((int) substr($datos, strlen(AvisosDePago::PREFIJO_PAGAR)));

        $this->telegram->responderBoton((string) data_get($boton, 'id'), $this->marcarPagado($mensual));
        $this->telegram->reemplazarBotones(
            data_get($boton, 'message.chat.id'),
            (int) data_get($boton, 'message.message_id'),
            $this->tecladoSinBoton((array) data_get($boton, 'message.reply_markup.inline_keyboard', []), $datos),
        );
    }

    private function marcarPagado(?GastoMensual $mensual): string
    {
        if ($mensual === null) {
            return 'Ese pago ya no existe';
        }

        if ($mensual->pagado) {
            return "{$mensual->gasto->servicio} ya estaba pagado";
        }

        $mensual->marcarPagado();

        return "{$mensual->gasto->servicio} marcado como pagado";
    }

    /**
     * @param  array<int, array<int, array<string, string>>>  $teclado
     * @return list<list<array<string, string>>>
     */
    private function tecladoSinBoton(array $teclado, string $datos): array
    {
        return collect($teclado)
            ->map(fn (array $fila) => array_values(array_filter($fila, fn (array $boton) => ($boton['callback_data'] ?? null) !== $datos)))
            ->reject(fn (array $fila) => $fila === [])
            ->values()
            ->all();
    }
}
