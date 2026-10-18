<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Models\MetodoPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GastoMetodoPagoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Cache::put('bcv_tasa_usd', 36.5, 3600);

        $this->user = User::factory()->create();
    }

    public function test_service_can_be_created_with_default_metodo_pago(): void
    {
        $metodo = MetodoPago::factory()->create();

        $this->actingAs($this->user)
            ->post(route('gastos.store'), [
                'servicio' => 'Netflix',
                'dia_pago' => 10,
                'monto' => 14,
                'moneda' => 'USD',
                'metodo_pago_id' => $metodo->id,
            ])
            ->assertRedirect(route('gastos.servicios'));

        $this->assertDatabaseHas('gastos', ['servicio' => 'Netflix', 'metodo_pago_id' => $metodo->id]);
    }

    public function test_service_metodo_pago_can_be_changed_and_removed(): void
    {
        $zelle = MetodoPago::factory()->create();
        $pagoMovil = MetodoPago::factory()->create();
        $gasto = Gasto::factory()->create(['metodo_pago_id' => $zelle->id]);
        $datos = ['servicio' => $gasto->servicio, 'dia_pago' => $gasto->dia_pago, 'monto' => 10, 'moneda' => 'USD'];

        $this->actingAs($this->user)
            ->put(route('gastos.update', $gasto), [...$datos, 'metodo_pago_id' => $pagoMovil->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($pagoMovil->id, $gasto->fresh()->metodo_pago_id);

        $this->actingAs($this->user)
            ->put(route('gastos.update', $gasto), [...$datos, 'metodo_pago_id' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($gasto->fresh()->metodo_pago_id);
    }

    public function test_service_rejects_nonexistent_metodo_pago(): void
    {
        $this->actingAs($this->user)
            ->post(route('gastos.store'), [
                'servicio' => 'Netflix',
                'dia_pago' => 10,
                'moneda' => 'USD',
                'metodo_pago_id' => 999,
            ])
            ->assertSessionHasErrors('metodo_pago_id');

        $this->assertDatabaseMissing('gastos', ['servicio' => 'Netflix']);
    }

    public function test_marking_paid_uses_service_default_metodo_pago(): void
    {
        $metodo = MetodoPago::factory()->create();
        $mensual = GastoMensual::factory()
            ->for(Gasto::factory()->state(['metodo_pago_id' => $metodo->id]))
            ->create();

        $this->actingAs($this->user)->post(route('gastos.toggle', $mensual));

        $mensual->refresh();
        $this->assertTrue($mensual->pagado);
        $this->assertSame($metodo->id, $mensual->metodo_pago_id);
    }

    public function test_marking_paid_keeps_metodo_pago_chosen_for_that_month(): void
    {
        $habitual = MetodoPago::factory()->create();
        $elegido = MetodoPago::factory()->create();
        $mensual = GastoMensual::factory()
            ->for(Gasto::factory()->state(['metodo_pago_id' => $habitual->id]))
            ->create(['metodo_pago_id' => $elegido->id]);

        $this->actingAs($this->user)->post(route('gastos.toggle', $mensual));

        $this->assertSame($elegido->id, $mensual->fresh()->metodo_pago_id);
    }

    public function test_metodo_pago_of_monthly_payment_can_be_changed(): void
    {
        $metodo = MetodoPago::factory()->create();
        $mensual = GastoMensual::factory()->create();

        $this->actingAs($this->user)
            ->from(route('gastos.index'))
            ->patch(route('gastos.metodo-pago', $mensual), ['metodo_pago_id' => $metodo->id])
            ->assertRedirect(route('gastos.index'))
            ->assertSessionHas('success');

        $this->assertSame($metodo->id, $mensual->fresh()->metodo_pago_id);
    }

    public function test_monthly_payment_rejects_nonexistent_metodo_pago(): void
    {
        $mensual = GastoMensual::factory()->create();

        $this->actingAs($this->user)
            ->patch(route('gastos.metodo-pago', $mensual), ['metodo_pago_id' => 999])
            ->assertSessionHasErrors('metodo_pago_id');

        $this->assertNull($mensual->fresh()->metodo_pago_id);
    }

    public function test_monthly_view_shows_pending_total_by_metodo_pago(): void
    {
        $zelle = MetodoPago::factory()->create(['nombre' => 'Zelle']);
        $pagoMovil = MetodoPago::factory()->create(['nombre' => 'Pago Móvil']);

        $this->crearMensual(['servicio' => 'Netflix', 'monto' => 14, 'metodo_pago_id' => $zelle->id]);
        $this->crearMensual(['servicio' => 'Luz', 'monto' => 20, 'metodo_pago_id' => $zelle->id], ['metodo_pago_id' => $pagoMovil->id]);
        $this->crearMensual(['servicio' => 'Spotify', 'monto' => 11.5, 'metodo_pago_id' => $zelle->id], ['pagado' => true]);
        $this->crearMensual(['servicio' => 'Vultr', 'monto' => 5]);

        $this->actingAs($this->user)
            ->get(route('gastos.index'))
            ->assertOk()
            ->assertSee('Pendiente por método de pago')
            ->assertViewHas('pendientePorMetodo', fn (Collection $pendiente): bool => $pendiente->all() === [
                'Pago Móvil' => 20.0,
                'Zelle' => 14.0,
                'Sin método' => 5.0,
            ]);
    }

    /**
     * @param  array<string, mixed>  $gasto
     * @param  array<string, mixed>  $mensual
     */
    private function crearMensual(array $gasto, array $mensual = []): GastoMensual
    {
        return GastoMensual::factory()
            ->for(Gasto::factory()->state($gasto))
            ->create($mensual);
    }
}
