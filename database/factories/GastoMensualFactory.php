<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Gasto;
use App\Models\GastoMensual;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GastoMensual>
 */
final class GastoMensualFactory extends Factory
{
    protected $model = GastoMensual::class;

    public function definition(): array
    {
        return [
            'gasto_id' => Gasto::factory(),
            'mes' => now()->month,
            'anio' => now()->year,
            'pagado' => false,
            'fecha_pago' => null,
            'metodo_pago_id' => null,
        ];
    }
}
