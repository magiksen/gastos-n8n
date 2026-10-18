<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\EstadoPago;
use App\Models\GastoMensual;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ResumenMensual
{
    private const DIAS_DE_AVISO = 3;

    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    /**
     * @param  Collection<int, GastoMensual>  $mensuales
     */
    public function __construct(
        public Collection $mensuales,
        public int $mes,
        public int $anio,
        private CarbonImmutable $hoy,
    ) {}

    public function nombreMes(): string
    {
        return self::MESES[$this->mes].' '.$this->anio;
    }

    /**
     * @return array<int, string>
     */
    public static function meses(): array
    {
        return self::MESES;
    }

    public function esMesActual(): bool
    {
        return $this->inicioDelMes()->equalTo($this->hoy->startOfMonth());
    }

    /**
     * @return array{mes: int, anio: int}
     */
    public function mesAnterior(): array
    {
        return $this->periodo($this->inicioDelMes()->subMonth());
    }

    /**
     * @return array{mes: int, anio: int}
     */
    public function mesSiguiente(): array
    {
        return $this->periodo($this->inicioDelMes()->addMonth());
    }

    public function total(): float
    {
        return $this->sumarMontos($this->mensuales);
    }

    public function pagado(): float
    {
        return $this->sumarMontos($this->pagados());
    }

    public function pendiente(): float
    {
        return $this->sumarMontos($this->pendientes());
    }

    public function pendienteEnBolivares(): float
    {
        return $this->sumarMontos($this->pendientes()->filter(fn (GastoMensual $gm) => $gm->gasto->moneda === 'VES'));
    }

    public function cantidadPagados(): int
    {
        return $this->pagados()->count();
    }

    public function porcentajePagado(): int
    {
        if ($this->mensuales->isEmpty()) {
            return 0;
        }

        return (int) round($this->cantidadPagados() / $this->mensuales->count() * 100);
    }

    /**
     * @return Collection<int, GastoMensual>
     */
    public function pagados(): Collection
    {
        return $this->mensuales->filter(fn (GastoMensual $gm) => $gm->pagado)->values();
    }

    /**
     * @return Collection<int, GastoMensual>
     */
    public function pendientes(): Collection
    {
        return $this->mensuales->reject(fn (GastoMensual $gm) => $gm->pagado)->values();
    }

    /**
     * @return Collection<int, GastoMensual>
     */
    public function vencidos(): Collection
    {
        return $this->conEstado(EstadoPago::Vencido);
    }

    /**
     * @return Collection<int, GastoMensual>
     */
    public function proximos(): Collection
    {
        return $this->conEstado(EstadoPago::PorVencer);
    }

    /**
     * @return Collection<int, GastoMensual>
     */
    public function vencenHoy(): Collection
    {
        if (! $this->esMesActual()) {
            return new Collection;
        }

        return $this->pendientes()->filter(fn (GastoMensual $gm) => $this->diasRestantes($gm) === 0)->values();
    }

    /**
     * @return Collection<string, float>
     */
    public function pendientePorMetodo(): Collection
    {
        return $this->pendientes()
            ->groupBy(fn (GastoMensual $gm) => $gm->metodoPagoAplicado()?->nombre ?? 'Sin método')
            ->map(fn (Collection $grupo) => $this->sumarMontos($grupo))
            ->filter(fn (float $total) => $total > 0)
            ->sortDesc();
    }

    public function estadoDe(GastoMensual $mensual): EstadoPago
    {
        if ($mensual->pagado) {
            return EstadoPago::Pagado;
        }

        if ($this->inicioDelMes()->lessThan($this->hoy->startOfMonth())) {
            return EstadoPago::Vencido;
        }

        if (! $this->esMesActual()) {
            return EstadoPago::Pendiente;
        }

        $diasRestantes = $this->diasRestantes($mensual);

        return match (true) {
            $diasRestantes < 0 => EstadoPago::Vencido,
            $diasRestantes <= self::DIAS_DE_AVISO => EstadoPago::PorVencer,
            default => EstadoPago::Pendiente,
        };
    }

    public function vencimientoDe(GastoMensual $mensual): string
    {
        if ($mensual->pagado) {
            return $mensual->fecha_pago ? 'Pagado el '.$mensual->fecha_pago->format('d/m') : 'Pagado';
        }

        if (! $this->esMesActual()) {
            return 'Día '.$mensual->gasto->dia_pago;
        }

        $dias = $this->diasRestantes($mensual);

        return match (true) {
            $dias === 0 => 'Vence hoy',
            $dias === 1 => 'Vence mañana',
            $dias === -1 => 'Venció ayer',
            $dias < 0 => 'Venció hace '.abs($dias).' días',
            default => "Vence en {$dias} días",
        };
    }

    /**
     * @return Collection<int, GastoMensual>
     */
    private function conEstado(EstadoPago $estado): Collection
    {
        return $this->mensuales->filter(fn (GastoMensual $gm) => $this->estadoDe($gm) === $estado)->values();
    }

    private function diasRestantes(GastoMensual $mensual): int
    {
        $diaDeVencimiento = min($mensual->gasto->dia_pago, $this->inicioDelMes()->daysInMonth);

        return $diaDeVencimiento - $this->hoy->day;
    }

    /**
     * @param  Collection<int, GastoMensual>  $mensuales
     */
    private function sumarMontos(Collection $mensuales): float
    {
        return (float) $mensuales->sum(fn (GastoMensual $gm) => (float) ($gm->gasto->monto ?? 0));
    }

    private function inicioDelMes(): CarbonImmutable
    {
        return CarbonImmutable::create($this->anio, $this->mes, 1)->startOfDay();
    }

    /**
     * @return array{mes: int, anio: int}
     */
    private function periodo(CarbonImmutable $fecha): array
    {
        return ['mes' => $fecha->month, 'anio' => $fecha->year];
    }
}
