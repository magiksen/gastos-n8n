<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Models\GastoMensual;
use App\Services\BcvService;
use App\Services\ResumenMensualService;
use App\Support\ResumenMensual;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Collection;

final readonly class AvisosDePago
{
    public const PREFIJO_PAGAR = 'pagar:';

    private const BOTONES_POR_FILA = 2;

    public function __construct(
        private ResumenMensualService $resumenes,
        private BcvService $bcv,
        private TelegramClient $telegram,
        #[Config('services.telegram.chat_id')] private ?string $chatId,
    ) {}

    public function disponibles(): bool
    {
        return $this->telegram->configurado() && filled($this->chatId);
    }

    public function enviar(): int
    {
        $hoy = CarbonImmutable::now();
        $resumen = $this->resumenes->delMes($hoy->month, $hoy->year);
        $tasa = $this->bcv->obtenerTasaUsd();
        $enviados = 0;

        $vencenHoy = $resumen->vencenHoy();

        foreach ($vencenHoy as $mensual) {
            $this->telegram->enviarMensaje(
                $this->chatId,
                'Hoy vence <b>'.$this->escapar($mensual->gasto->servicio).'</b>'."\n".$this->detalle($mensual, $tasa),
                [[$this->botonPagar($mensual, 'Marcar como pagado')]],
            );
            $enviados++;
        }

        $vencidos = $resumen->vencidos();
        $proximos = $resumen->proximos()->reject(fn (GastoMensual $gm) => $vencenHoy->contains($gm))->values();

        if ($vencidos->isNotEmpty() || $proximos->isNotEmpty()) {
            $this->telegram->enviarMensaje(
                $this->chatId,
                $this->resumenDePendientes($resumen, $vencidos, $proximos, $tasa),
                $vencidos->concat($proximos)
                    ->map(fn (GastoMensual $gm) => $this->botonPagar($gm, 'Pagar '.$gm->gasto->servicio))
                    ->chunk(self::BOTONES_POR_FILA)
                    ->map(fn (Collection $fila) => $fila->values()->all())
                    ->values()
                    ->all(),
            );
            $enviados++;
        }

        return $enviados;
    }

    /**
     * @param  Collection<int, GastoMensual>  $vencidos
     * @param  Collection<int, GastoMensual>  $proximos
     */
    private function resumenDePendientes(ResumenMensual $resumen, Collection $vencidos, Collection $proximos, ?float $tasa): string
    {
        $secciones = ['<b>Pagos pendientes · '.$resumen->nombreMes().'</b>'];

        foreach (['Vencidos' => $vencidos, 'Próximos' => $proximos] as $titulo => $lista) {
            if ($lista->isEmpty()) {
                continue;
            }

            $lineas = $lista->map(fn (GastoMensual $gm) => '• <b>'.$this->escapar($gm->gasto->servicio).'</b> · '
                .$resumen->vencimientoDe($gm)."\n   ".$this->detalle($gm, $tasa));

            $secciones[] = "<b>{$titulo}</b>\n".$lineas->implode("\n");
        }

        $secciones[] = 'Pendiente del mes: <b>$'.number_format($resumen->pendiente(), 2).'</b>';

        return implode("\n\n", $secciones);
    }

    private function detalle(GastoMensual $mensual, ?float $tasa): string
    {
        $partes = [$this->monto($mensual, $tasa)];

        if ($metodo = $mensual->metodoPagoAplicado()) {
            $partes[] = $this->escapar($metodo->nombre);
        }

        return implode(' · ', $partes);
    }

    private function monto(GastoMensual $mensual, ?float $tasa): string
    {
        $monto = $mensual->gasto->monto;

        if ($monto === null) {
            return 'Sin monto';
        }

        $texto = '$'.number_format((float) $monto, 2);

        if ($mensual->gasto->moneda === 'VES' && $tasa) {
            $texto .= ' (Bs '.number_format((float) $monto * $tasa, 2).')';
        }

        return $texto;
    }

    /**
     * @return array{text: string, callback_data: string}
     */
    private function botonPagar(GastoMensual $mensual, string $texto): array
    {
        return ['text' => $texto, 'callback_data' => self::PREFIJO_PAGAR.$mensual->id];
    }

    private function escapar(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
