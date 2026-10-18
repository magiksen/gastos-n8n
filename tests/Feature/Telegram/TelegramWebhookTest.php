<?php

declare(strict_types=1);

namespace Tests\Feature\Telegram;

use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Models\MetodoPago;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRETO = 'secreto-del-webhook';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 10, 15, 9));
        config([
            'services.telegram.token' => 'TOKEN-PRUEBA',
            'services.telegram.chat_id' => '555',
            'services.telegram.webhook_secret' => self::SECRETO,
        ]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);
    }

    public function test_rejects_requests_without_valid_secret(): void
    {
        $this->postJson(route('telegram.webhook'), [])->assertForbidden();
        $this->postJson(route('telegram.webhook'), [], ['X-Telegram-Bot-Api-Secret-Token' => 'otro'])->assertForbidden();

        config(['services.telegram.webhook_secret' => null]);
        $this->postJson(route('telegram.webhook'), [], ['X-Telegram-Bot-Api-Secret-Token' => ''])->assertForbidden();
    }

    public function test_pay_button_marks_payment_as_paid_and_removes_button(): void
    {
        $zelle = MetodoPago::factory()->create();
        $netflix = $this->mensual(['servicio' => 'Netflix', 'metodo_pago_id' => $zelle->id]);
        $vnet = $this->mensual(['servicio' => 'Vnet']);

        $this->pulsar("pagar:{$netflix->id}", [[
            ['text' => 'Pagar Netflix', 'callback_data' => "pagar:{$netflix->id}"],
            ['text' => 'Pagar Vnet', 'callback_data' => "pagar:{$vnet->id}"],
        ]])->assertSuccessful();

        $netflix->refresh();
        $this->assertTrue($netflix->pagado);
        $this->assertSame('2026-10-15', $netflix->fecha_pago->toDateString());
        $this->assertSame($zelle->id, $netflix->metodo_pago_id);
        $this->assertFalse($vnet->refresh()->pagado);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/answerCallbackQuery')
            && $request['callback_query_id'] === 'cb-1'
            && $request['text'] === 'Netflix marcado como pagado');
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/editMessageReplyMarkup')
            && $request['chat_id'] === 555
            && $request['message_id'] === 77
            && $request['reply_markup']['inline_keyboard'] === [[
                ['text' => 'Pagar Vnet', 'callback_data' => "pagar:{$vnet->id}"],
            ]]);
    }

    public function test_already_paid_payment_is_not_changed(): void
    {
        $mensual = $this->mensual(['servicio' => 'Netflix'], ['pagado' => true, 'fecha_pago' => '2026-10-02']);

        $this->pulsar("pagar:{$mensual->id}")->assertSuccessful();

        $this->assertSame('2026-10-02', $mensual->refresh()->fecha_pago->toDateString());
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/answerCallbackQuery')
            && $request['text'] === 'Netflix ya estaba pagado');
    }

    public function test_ignores_buttons_pressed_from_other_chats(): void
    {
        $mensual = $this->mensual(['servicio' => 'Netflix']);

        $this->pulsar("pagar:{$mensual->id}", chatId: 999)->assertSuccessful();

        $this->assertFalse($mensual->refresh()->pagado);
        Http::assertNothingSent();
    }

    public function test_answers_when_payment_no_longer_exists(): void
    {
        $this->pulsar('pagar:9999')->assertSuccessful();

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/answerCallbackQuery')
            && $request['text'] === 'Ese pago ya no existe');
    }

    public function test_ignores_updates_without_buttons(): void
    {
        $this->postJson(route('telegram.webhook'), ['message' => ['chat' => ['id' => 555], 'text' => 'hola']], $this->cabeceras())
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    /**
     * @param  list<list<array{text: string, callback_data: string}>>  $teclado
     */
    private function pulsar(string $datos, array $teclado = [], int $chatId = 555): TestResponse
    {
        return $this->postJson(route('telegram.webhook'), [
            'update_id' => 1,
            'callback_query' => [
                'id' => 'cb-1',
                'data' => $datos,
                'message' => [
                    'message_id' => 77,
                    'chat' => ['id' => $chatId],
                    'reply_markup' => ['inline_keyboard' => $teclado],
                ],
            ],
        ], $this->cabeceras());
    }

    /**
     * @return array<string, string>
     */
    private function cabeceras(): array
    {
        return ['X-Telegram-Bot-Api-Secret-Token' => self::SECRETO];
    }

    /**
     * @param  array<string, mixed>  $gasto
     * @param  array<string, mixed>  $mensual
     */
    private function mensual(array $gasto, array $mensual = []): GastoMensual
    {
        return GastoMensual::factory()
            ->for(Gasto::factory()->state($gasto))
            ->create(['mes' => 10, 'anio' => 2026, ...$mensual]);
    }
}
