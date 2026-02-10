<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class BcvService
{
    private const CACHE_KEY = 'bcv_tasa_usd';
    private const CACHE_TTL_SECONDS = 86400; // 24 horas
    private const BCV_URL = 'https://www.bcv.org.ve/';

    public function obtenerTasaUsd(): ?float
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): ?float {
            return $this->scrapeTasa();
        });
    }

    public function refrescarTasa(): ?float
    {
        Cache::forget(self::CACHE_KEY);

        return $this->obtenerTasaUsd();
    }

    private function scrapeTasa(): ?float
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get(self::BCV_URL);

            if (! $response->successful()) {
                Log::warning('BCV: HTTP error', ['status' => $response->status()]);

                return null;
            }

            $html = $response->body();

            return $this->parsearTasaDelHtml($html);
        } catch (\Throwable $e) {
            Log::error('BCV: Error al obtener tasa', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function parsearTasaDelHtml(string $html): ?float
    {
        // El div#dolar contiene un <strong> con el valor, ej: "51,14777369"
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);

        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Buscar el div con id="dolar" y extraer el strong
        $nodes = $xpath->query('//div[@id="dolar"]//strong');

        if ($nodes === false || $nodes->length === 0) {
            Log::warning('BCV: No se encontró el elemento #dolar strong en el HTML');

            return null;
        }

        $texto = trim($nodes->item(0)->textContent);

        // El BCV usa coma como separador decimal: "51,14777369"
        $texto = str_replace('.', '', $texto); // quitar separador de miles si existe
        $texto = str_replace(',', '.', $texto); // coma -> punto decimal

        $valor = (float) $texto;

        if ($valor <= 0) {
            Log::warning('BCV: Valor parseado inválido', ['texto' => $texto, 'valor' => $valor]);

            return null;
        }

        Log::info('BCV: Tasa USD obtenida', ['tasa' => $valor]);

        return $valor;
    }
}
