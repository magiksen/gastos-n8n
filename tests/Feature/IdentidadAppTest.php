<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class IdentidadAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_is_named_mis_gastos_by_default(): void
    {
        $this->assertSame('Mis Gastos', config('app.name'));
    }

    public function test_guest_pages_show_name_and_favicons(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>Mis Gastos · Acceso</title>', false)
            ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
            ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false);
    }

    public function test_authenticated_pages_use_app_name_in_title(): void
    {
        Http::preventStrayRequests();
        Cache::put('bcv_tasa_usd', 36.5, 3600);

        $this->actingAs(User::factory()->create())
            ->get(route('metodos-pago.index'))
            ->assertOk()
            ->assertSee('<title>Métodos de pago · Mis Gastos</title>', false)
            ->assertSee('rel="icon"', false);
    }

    public function test_favicon_files_exist(): void
    {
        foreach (['favicon.svg', 'favicon.ico', 'apple-touch-icon.png'] as $archivo) {
            $this->assertFileExists(public_path($archivo));
        }
    }
}
