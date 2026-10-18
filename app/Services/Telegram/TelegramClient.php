<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class TelegramClient
{
    private const API = 'https://api.telegram.org/bot';

    private const SEGUNDOS_DE_ESPERA = 20;

    public function __construct(
        #[Config('services.telegram.token')] private ?string $token,
    ) {}

    public function conToken(string $token): self
    {
        return new self($token);
    }

    public function configurado(): bool
    {
        return filled($this->token);
    }

    /**
     * @param  list<list<array{text: string, callback_data: string}>>  $botones
     */
    public function enviarMensaje(int|string $chatId, string $texto, array $botones = []): bool
    {
        $datos = [
            'chat_id' => $chatId,
            'text' => $texto,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($botones !== []) {
            $datos['reply_markup'] = ['inline_keyboard' => $botones];
        }

        return $this->llamar('sendMessage', $datos)->successful();
    }

    public function responderBoton(string $callbackId, string $texto): void
    {
        $this->llamar('answerCallbackQuery', ['callback_query_id' => $callbackId, 'text' => $texto]);
    }

    /**
     * @param  list<list<array{text: string, callback_data: string}>>  $botones
     */
    public function reemplazarBotones(int|string $chatId, int $mensajeId, array $botones): void
    {
        $this->llamar('editMessageReplyMarkup', [
            'chat_id' => $chatId,
            'message_id' => $mensajeId,
            'reply_markup' => ['inline_keyboard' => $botones],
        ]);
    }

    public function nombreDelBot(): ?string
    {
        $respuesta = $this->llamar('getMe');

        return $respuesta->successful() ? $respuesta->json('result.username') : null;
    }

    public function chatQueEnvioStart(): ?int
    {
        $respuesta = $this->llamar('getUpdates', [
            'timeout' => self::SEGUNDOS_DE_ESPERA,
            'allowed_updates' => ['message'],
        ], self::SEGUNDOS_DE_ESPERA + 10);

        $chats = collect($respuesta->json('result', []))
            ->filter(fn (array $actualizacion): bool => str_starts_with((string) data_get($actualizacion, 'message.text'), '/start'))
            ->map(fn (array $actualizacion): int => (int) data_get($actualizacion, 'message.chat.id'));

        return $chats->last();
    }

    public function quitarWebhook(): void
    {
        $this->llamar('deleteWebhook');
    }

    public function configurarWebhook(string $url, string $secreto): bool
    {
        return $this->llamar('setWebhook', [
            'url' => $url,
            'secret_token' => $secreto,
            'allowed_updates' => ['callback_query'],
        ])->successful();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function llamar(string $metodo, array $datos = [], int $timeout = 15): Response
    {
        return Http::asJson()
            ->timeout($timeout)
            ->post(self::API.$this->token.'/'.$metodo, $datos);
    }
}
