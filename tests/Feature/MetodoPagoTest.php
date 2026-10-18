<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Models\MetodoPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MetodoPagoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_metodos_pago(): void
    {
        $this->get(route('metodos-pago.index'))->assertRedirect(route('login'));
    }

    public function test_index_lists_metodos_pago(): void
    {
        MetodoPago::factory()->create(['nombre' => 'Zelle', 'detalle' => 'Cuenta personal']);

        $this->actingAs($this->user)
            ->get(route('metodos-pago.index'))
            ->assertOk()
            ->assertSee('Zelle')
            ->assertSee('Cuenta personal');
    }

    public function test_user_can_create_metodo_pago(): void
    {
        $this->actingAs($this->user)
            ->post(route('metodos-pago.store'), ['nombre' => 'Pago Móvil', 'detalle' => 'Banesco'])
            ->assertRedirect(route('metodos-pago.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('metodos_pago', [
            'nombre' => 'Pago Móvil',
            'detalle' => 'Banesco',
            'activo' => true,
        ]);
    }

    public function test_nombre_is_required_and_unique(): void
    {
        MetodoPago::factory()->create(['nombre' => 'Zelle']);

        $this->actingAs($this->user)
            ->post(route('metodos-pago.store'), ['nombre' => ''])
            ->assertSessionHasErrors('nombre');

        $this->actingAs($this->user)
            ->post(route('metodos-pago.store'), ['nombre' => 'Zelle'])
            ->assertSessionHasErrors('nombre');

        $this->assertDatabaseCount('metodos_pago', 1);
    }

    public function test_user_can_update_metodo_pago_keeping_same_name(): void
    {
        $metodo = MetodoPago::factory()->create(['nombre' => 'Zelle']);

        $this->actingAs($this->user)
            ->put(route('metodos-pago.update', $metodo), ['nombre' => 'Zelle', 'detalle' => 'Chase'])
            ->assertRedirect(route('metodos-pago.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('Chase', $metodo->fresh()->detalle);
    }

    public function test_user_can_toggle_metodo_pago_activo(): void
    {
        $metodo = MetodoPago::factory()->create(['activo' => true]);

        $this->actingAs($this->user)
            ->patch(route('metodos-pago.toggle-activo', $metodo))
            ->assertRedirect(route('metodos-pago.index'));

        $this->assertFalse($metodo->fresh()->activo);
    }

    public function test_unused_metodo_pago_can_be_deleted(): void
    {
        $metodo = MetodoPago::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('metodos-pago.destroy', $metodo))
            ->assertRedirect(route('metodos-pago.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($metodo);
    }

    public function test_metodo_pago_assigned_to_service_cannot_be_deleted(): void
    {
        $metodo = MetodoPago::factory()->create();
        Gasto::factory()->create(['metodo_pago_id' => $metodo->id]);

        $this->actingAs($this->user)
            ->delete(route('metodos-pago.destroy', $metodo))
            ->assertRedirect(route('metodos-pago.index'))
            ->assertSessionHasErrors('metodo_pago');

        $this->assertModelExists($metodo);
    }

    public function test_metodo_pago_used_in_payment_history_cannot_be_deleted(): void
    {
        $metodo = MetodoPago::factory()->create();
        GastoMensual::factory()->create(['metodo_pago_id' => $metodo->id, 'pagado' => true]);

        $this->actingAs($this->user)
            ->delete(route('metodos-pago.destroy', $metodo))
            ->assertSessionHasErrors('metodo_pago');

        $this->assertModelExists($metodo);
    }
}
