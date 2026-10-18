<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EstadoPago;
use App\Models\Gasto;
use App\Models\GastoMensual;
use App\Services\ResumenMensualService;
use App\Support\ResumenMensual;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ResumenMensualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 10, 15, 10));
    }

    public function test_generates_month_from_active_services_when_missing(): void
    {
        Gasto::factory()->create(['activo' => true]);
        Gasto::factory()->create(['activo' => false]);

        $resumen = $this->resumen(11, 2026);

        $this->assertCount(1, $resumen->mensuales);
        $this->assertDatabaseCount('gastos_mensuales', 1);
    }

    public function test_calculates_totals_and_progress(): void
    {
        $this->mensual(['monto' => 10]);
        $this->mensual(['monto' => 20, 'moneda' => 'VES']);
        $this->mensual(['monto' => 30], ['pagado' => true]);
        $this->mensual(['monto' => null]);

        $resumen = $this->resumen();

        $this->assertSame(60.0, $resumen->total());
        $this->assertSame(30.0, $resumen->pagado());
        $this->assertSame(30.0, $resumen->pendiente());
        $this->assertSame(20.0, $resumen->pendienteEnBolivares());
        $this->assertSame(1, $resumen->cantidadPagados());
        $this->assertSame(25, $resumen->porcentajePagado());
    }

    public function test_classifies_payment_status_in_current_month(): void
    {
        $vencido = $this->mensual(['dia_pago' => 10]);
        $venceHoy = $this->mensual(['dia_pago' => 15]);
        $venceEnTresDias = $this->mensual(['dia_pago' => 18]);
        $pendiente = $this->mensual(['dia_pago' => 19]);
        $pagado = $this->mensual(['dia_pago' => 1], ['pagado' => true]);

        $resumen = $this->resumen();

        $this->assertSame(EstadoPago::Vencido, $resumen->estadoDe($vencido));
        $this->assertSame(EstadoPago::PorVencer, $resumen->estadoDe($venceHoy));
        $this->assertSame(EstadoPago::PorVencer, $resumen->estadoDe($venceEnTresDias));
        $this->assertSame(EstadoPago::Pendiente, $resumen->estadoDe($pendiente));
        $this->assertSame(EstadoPago::Pagado, $resumen->estadoDe($pagado));
        $this->assertSame([$vencido->id], $resumen->vencidos()->pluck('id')->all());
        $this->assertSame([$venceHoy->id, $venceEnTresDias->id], $resumen->proximos()->pluck('id')->all());
    }

    public function test_unpaid_payments_from_past_months_are_overdue(): void
    {
        $mensual = $this->mensual(['dia_pago' => 28], ['mes' => 9]);

        $this->assertSame(EstadoPago::Vencido, $this->resumen(9, 2026)->estadoDe($mensual));
    }

    public function test_payments_in_future_months_are_pending(): void
    {
        $mensual = $this->mensual(['dia_pago' => 1], ['mes' => 11]);

        $this->assertSame(EstadoPago::Pendiente, $this->resumen(11, 2026)->estadoDe($mensual));
    }

    public function test_describes_due_date_relative_to_today(): void
    {
        $ayer = $this->mensual(['dia_pago' => 14]);
        $haceTres = $this->mensual(['dia_pago' => 12]);
        $hoy = $this->mensual(['dia_pago' => 15]);
        $manana = $this->mensual(['dia_pago' => 16]);
        $enCinco = $this->mensual(['dia_pago' => 20]);
        $pagado = $this->mensual(['dia_pago' => 3], ['pagado' => true, 'fecha_pago' => '2026-10-02']);

        $resumen = $this->resumen();

        $this->assertSame('Venció ayer', $resumen->vencimientoDe($ayer));
        $this->assertSame('Venció hace 3 días', $resumen->vencimientoDe($haceTres));
        $this->assertSame('Vence hoy', $resumen->vencimientoDe($hoy));
        $this->assertSame('Vence mañana', $resumen->vencimientoDe($manana));
        $this->assertSame('Vence en 5 días', $resumen->vencimientoDe($enCinco));
        $this->assertSame('Pagado el 02/10', $resumen->vencimientoDe($pagado));
    }

    public function test_items_are_sorted_by_payment_day(): void
    {
        $this->mensual(['servicio' => 'Luz', 'dia_pago' => 28]);
        $this->mensual(['servicio' => 'Internet', 'dia_pago' => 1]);
        $this->mensual(['servicio' => 'Netflix', 'dia_pago' => 10]);

        $servicios = $this->resumen()->mensuales->map(fn (GastoMensual $gm) => $gm->gasto->servicio)->all();

        $this->assertSame(['Internet', 'Netflix', 'Luz'], $servicios);
    }

    public function test_exposes_month_name_and_neighbours(): void
    {
        $resumen = $this->resumen(1, 2026);

        $this->assertSame('Enero 2026', $resumen->nombreMes());
        $this->assertSame(['mes' => 12, 'anio' => 2025], $resumen->mesAnterior());
        $this->assertSame(['mes' => 2, 'anio' => 2026], $resumen->mesSiguiente());
        $this->assertFalse($resumen->esMesActual());
        $this->assertTrue($this->resumen()->esMesActual());
    }

    private function resumen(int $mes = 10, int $anio = 2026): ResumenMensual
    {
        return app(ResumenMensualService::class)->delMes($mes, $anio);
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
