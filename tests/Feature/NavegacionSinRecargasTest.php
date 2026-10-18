<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class NavegacionSinRecargasTest extends TestCase
{
    use RefreshDatabase;

    private const META_MORPH = '<meta name="turbo-refresh-method" content="morph">';

    private const META_SCROLL = '<meta name="turbo-refresh-scroll" content="preserve">';

    public function test_authenticated_pages_refresh_preserving_scroll(): void
    {
        Http::preventStrayRequests();
        Cache::put('bcv_tasa_usd', 36.5, 3600);
        $user = User::factory()->create();

        foreach (['gastos.index', 'gastos.servicios', 'metodos-pago.index', 'profile.edit'] as $ruta) {
            $this->actingAs($user)
                ->get(route($ruta))
                ->assertOk()
                ->assertSee(self::META_MORPH, false)
                ->assertSee(self::META_SCROLL, false);
        }
    }

    public function test_success_message_is_shown_as_floating_notification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['success' => "Servicio 'Netflix' actualizado."])
            ->get(route('metodos-pago.index'))
            ->assertOk()
            ->assertSee('data-toast="success"', false)
            ->assertSee("Servicio 'Netflix' actualizado.");
    }

    public function test_validation_errors_are_shown_as_floating_notification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('metodos-pago.index'))
            ->followingRedirects()
            ->post(route('metodos-pago.store'), ['nombre' => ''])
            ->assertOk()
            ->assertSee('data-toast="error"', false)
            ->assertSee('nombre');
    }

    public function test_login_page_refreshes_preserving_scroll(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(self::META_MORPH, false)
            ->assertSee(self::META_SCROLL, false);
    }
}
