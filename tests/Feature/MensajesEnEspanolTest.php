<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Models\MetodoPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class MensajesEnEspanolTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_uses_spanish_by_default(): void
    {
        $this->assertSame('es', config('app.locale'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function archivosDeIdioma(): array
    {
        return [
            'validación' => ['validation'],
            'autenticación' => ['auth'],
            'contraseñas' => ['passwords'],
            'paginación' => ['pagination'],
        ];
    }

    #[DataProvider('archivosDeIdioma')]
    public function test_spanish_file_translates_every_framework_line(string $archivo): void
    {
        $ingles = Arr::except(require base_path("vendor/laravel/framework/src/Illuminate/Translation/lang/en/{$archivo}.php"), ['custom', 'attributes']);
        $espanol = require lang_path("es/{$archivo}.php");

        $this->assertSame([], array_diff(array_keys($this->aplanar($ingles)), array_keys($this->aplanar($espanol))));
    }

    public function test_service_form_errors_are_in_spanish(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('gastos.store'), ['servicio' => '', 'dia_pago' => 40, 'moneda' => 'EUR'])
            ->assertSessionHasErrors([
                'servicio' => 'El campo servicio es obligatorio.',
                'dia_pago' => 'El campo día de pago no debe ser mayor que 31.',
                'moneda' => 'El campo moneda tiene un valor no válido.',
            ]);
    }

    public function test_duplicated_payment_method_error_is_in_spanish(): void
    {
        MetodoPago::factory()->create(['nombre' => 'Zelle']);

        $this->actingAs(User::factory()->create())
            ->post(route('metodos-pago.store'), ['nombre' => 'Zelle'])
            ->assertSessionHasErrors(['nombre' => 'Ya existe un registro con ese nombre.']);
    }

    public function test_receipt_must_be_an_image(): void
    {
        $mensual = GastoMensual::factory()->for(Gasto::factory())->create();

        $this->actingAs(User::factory()->create())
            ->post(route('gastos.comprobante', $mensual), ['comprobante' => UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors(['comprobante' => 'El campo comprobante debe ser una imagen.']);
    }

    public function test_wrong_login_message_is_in_spanish(): void
    {
        $usuario = User::factory()->create();

        $this->post('/login', ['email' => $usuario->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors(['email' => 'Estas credenciales no coinciden con nuestros registros.']);
    }

    public function test_wrong_current_password_message_is_in_spanish(): void
    {
        $this->actingAs(User::factory()->create())
            ->from('/profile')
            ->put('/password', ['current_password' => 'incorrecta', 'password' => 'nueva-clave-123', 'password_confirmation' => 'nueva-clave-123'])
            ->assertSessionHasErrorsIn('updatePassword', ['current_password' => 'La contraseña es incorrecta.']);
    }

    public function test_password_reset_screen_is_in_spanish(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Enviar enlace para restablecer')
            ->assertDontSee('Email Password Reset Link');
    }

    /**
     * @param  array<string, mixed>  $lineas
     * @return array<string, string>
     */
    private function aplanar(array $lineas, string $prefijo = ''): array
    {
        $planas = [];

        foreach ($lineas as $clave => $valor) {
            if (is_array($valor)) {
                $planas += $this->aplanar($valor, $prefijo.$clave.'.');
            } else {
                $planas[$prefijo.$clave] = $valor;
            }
        }

        return $planas;
    }
}
