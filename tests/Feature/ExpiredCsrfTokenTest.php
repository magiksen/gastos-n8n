<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ExpiredCsrfTokenTest extends TestCase
{
    use RefreshDatabase;

    private const MENSAJE_SESION_EXPIRADA = 'Tu sesión expiró. Vuelve a intentarlo.';

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Route::middleware('web')->post('/_test/token-expirado', function (): never {
            throw new TokenMismatchException('CSRF token mismatch.');
        });
    }

    public function test_expired_token_redirects_back_with_warning_instead_of_419_page(): void
    {
        $response = $this->from(route('gastos.index'))->post('/_test/token-expirado');

        $response->assertRedirect(route('gastos.index'));
        $response->assertSessionHas('warning', self::MENSAJE_SESION_EXPIRADA);
    }

    public function test_expired_token_keeps_input_except_sensitive_fields(): void
    {
        $response = $this->from(route('login'))->post('/_test/token-expirado', [
            'email' => 'yo@ejemplo.com',
            'password' => 'secreto',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasInput('email', 'yo@ejemplo.com');
        $response->assertSessionMissing('_old_input.password');
    }

    public function test_expired_token_on_json_request_keeps_419_status(): void
    {
        $this->postJson('/_test/token-expirado')->assertStatus(419);
    }

    public function test_warning_is_displayed_on_login_page(): void
    {
        $this->withSession(['warning' => self::MENSAJE_SESION_EXPIRADA])
            ->get(route('login'))
            ->assertSee(self::MENSAJE_SESION_EXPIRADA);
    }

    public function test_warning_is_displayed_on_authenticated_pages(): void
    {
        Cache::put('bcv_tasa_usd', 36.5, 60);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['warning' => self::MENSAJE_SESION_EXPIRADA])
            ->get(route('gastos.servicios'))
            ->assertSee(self::MENSAJE_SESION_EXPIRADA);
    }

    public function test_forms_use_https_when_proxy_forwards_https(): void
    {
        $this->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get(route('login'))
            ->assertSee('action="https://localhost/login"', false);
    }
}
