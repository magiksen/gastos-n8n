<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Gasto;
use App\Models\GastoMensual;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class GastoSeeder extends Seeder
{
    public function run(): void
    {
        $gastos = [
            ['servicio' => 'Vnet', 'dia_pago' => 1, 'monto' => 50.00, 'moneda' => 'USD'],
            ['servicio' => 'Google Drive', 'dia_pago' => 4, 'monto' => 10.00, 'moneda' => 'USD'],
            ['servicio' => 'Movistar', 'dia_pago' => 5, 'monto' => 10.00, 'moneda' => 'USD'],
            ['servicio' => 'Windsurf AI', 'dia_pago' => 8, 'monto' => 15.00, 'moneda' => 'USD'],
            ['servicio' => 'Condominio', 'dia_pago' => 10, 'monto' => 50.00, 'moneda' => 'USD'],
            ['servicio' => 'Netflix', 'dia_pago' => 10, 'monto' => 14.00, 'moneda' => 'USD'],
            ['servicio' => 'ABA ULTRA', 'dia_pago' => 13, 'monto' => 35.00, 'moneda' => 'USD'],
            ['servicio' => 'Camaras', 'dia_pago' => 18, 'monto' => 6.00, 'moneda' => 'USD'],
            ['servicio' => 'Disney+ Star+', 'dia_pago' => 18, 'monto' => 18.00, 'moneda' => 'USD'],
            ['servicio' => 'Youtube', 'dia_pago' => 18, 'monto' => 8.00, 'moneda' => 'USD'],
            ['servicio' => 'Vultr', 'dia_pago' => 18, 'monto' => 5.00, 'moneda' => 'USD'],
            ['servicio' => 'Spotify Familiar', 'dia_pago' => 20, 'monto' => 11.50, 'moneda' => 'USD'],
            ['servicio' => 'Prime Video', 'dia_pago' => 23, 'monto' => 6.00, 'moneda' => 'USD'],
            ['servicio' => 'Digitel', 'dia_pago' => 23, 'monto' => null, 'moneda' => 'VES'],
            ['servicio' => 'Crunchyroll', 'dia_pago' => 27, 'monto' => 10.00, 'moneda' => 'USD'],
            ['servicio' => 'HBO Max', 'dia_pago' => 28, 'monto' => 10.00, 'moneda' => 'USD'],
            ['servicio' => 'Luz', 'dia_pago' => 28, 'monto' => null, 'moneda' => 'VES'],
        ];

        $now = Carbon::now();

        foreach ($gastos as $data) {
            $gasto = Gasto::create($data);

            // Generar entrada para el mes actual
            GastoMensual::create([
                'gasto_id' => $gasto->id,
                'mes' => $now->month,
                'anio' => $now->year,
            ]);
        }
    }
}
