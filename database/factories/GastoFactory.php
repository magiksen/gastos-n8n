<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Gasto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gasto>
 */
final class GastoFactory extends Factory
{
    protected $model = Gasto::class;

    public function definition(): array
    {
        return [
            'servicio' => fake()->unique()->company(),
            'dia_pago' => fake()->numberBetween(1, 28),
            'monto' => 10,
            'moneda' => 'USD',
            'activo' => true,
            'metodo_pago_id' => null,
        ];
    }
}
