<?php

declare(strict_types=1);

namespace Tests\Feature\Telegram;

use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Models\MetodoPago;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AvisosTelegramTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api.telegram.org/botTOKEN-PRUEBA/sendMessage';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 10, 15, 8));
        Cache::put('bcv_tasa_usd', 40.0, 3600);
        config([
            'services.telegram.token' => 'TOKEN-PRUEBA',
            'services.telegram.chat_id' => '555',
        ]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);
    }

    public function test_app_uses_caracas_timezone_by_default(): void
    {
        $this->assertSame('America/Caracas', config('app.timezone'));
    }

    public function test_daily_notices_are_scheduled_at_eight(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('telegram:avisos')
            ->assertSuccessful();
    }

    public function test_does_nothing_when_telegram_is_not_configured(): void
    {
        config(['services.telegram.token' => null]);
        $this->mensual(['servicio' => 'Netflix', 'dia_pago' => 15]);

        $this->artisan('telegram:avisos')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_sends_one_message_per_payment_due_today_with_pay_button(): void
    {
        $zelle = MetodoPago::factory()->create(['nombre' => 'Zelle']);
        $netflix = $this->mensual(['servicio' => 'Netflix', 'dia_pago' => 15, 'monto' => 14, 'metodo_pago_id' => $zelle->id]);

        $this->artisan('telegram:avisos')->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request->url() === self::API
            && $request['chat_id'] === '555'
            && $request['parse_mode'] === 'HTML'
            && str_contains($request['text'], 'Hoy vence <b>Netflix</b>')
            && str_contains($request['text'], '$14.00')
            && str_contains($request['text'], 'Zelle')
            && $request['reply_markup']['inline_keyboard'] === [[
                ['text' => 'Marcar como pagado', 'callback_data' => "pagar:{$netflix->id}"],
            ]]);
    }

    public function test_sends_summary_of_overdue_and_upcoming_payments(): void
    {
        $vnet = $this->mensual(['servicio' => 'Vnet', 'dia_pago' => 10, 'monto' => 50]);
        $luz = $this->mensual(['servicio' => 'Luz', 'dia_pago' => 17, 'monto' => 5, 'moneda' => 'VES']);
        $this->mensual(['servicio' => 'Pagado', 'dia_pago' => 12], ['pagado' => true]);
        $this->mensual(['servicio' => 'Lejano', 'dia_pago' => 25]);

        $this->artisan('telegram:avisos')->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) use ($vnet, $luz): bool {
            $texto = $request['text'];

            return str_contains($texto, 'Vencidos')
                && str_contains($texto, 'Vnet')
                && str_contains($texto, 'Venció hace 5 días')
                && str_contains($texto, 'Próximos')
                && str_contains($texto, 'Luz')
                && str_contains($texto, 'Bs 200.00')
                && ! str_contains($texto, 'Pagado')
                && ! str_contains($texto, 'Lejano')
                && $request['reply_markup']['inline_keyboard'] === [[
                    ['text' => 'Pagar Vnet', 'callback_data' => "pagar:{$vnet->id}"],
                    ['text' => 'Pagar Luz', 'callback_data' => "pagar:{$luz->id}"],
                ]];
        });
    }

    public function test_due_today_payments_are_not_repeated_in_summary(): void
    {
        $this->mensual(['servicio' => 'Netflix', 'dia_pago' => 15]);
        $this->mensual(['servicio' => 'Vnet', 'dia_pago' => 10]);

        $this->artisan('telegram:avisos')->assertSuccessful();

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => str_contains($request['text'], 'Vencidos')
            && ! str_contains($request['text'], 'Netflix'));
    }

    public function test_escapes_service_names_for_html(): void
    {
        $this->mensual(['servicio' => 'Agua <b>&</b>', 'dia_pago' => 15]);

        $this->artisan('telegram:avisos')->assertSuccessful();

        Http::assertSent(fn (Request $request): bool => str_contains($request['text'], 'Agua &lt;b&gt;&amp;&lt;/b&gt;'));
    }

    public function test_sends_nothing_when_everything_is_up_to_date(): void
    {
        $this->mensual(['dia_pago' => 10], ['pagado' => true]);
        $this->mensual(['dia_pago' => 25]);

        $this->artisan('telegram:avisos')->assertSuccessful();

        Http::assertNothingSent();
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
