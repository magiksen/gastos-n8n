<?php

declare(strict_types=1);

namespace App\Support;

final readonly class ArchivoEnv
{
    public function __construct(private string $ruta) {}

    /**
     * @param  array<string, string>  $valores
     */
    public function establecer(array $valores): void
    {
        $contenido = is_file($this->ruta) ? (string) file_get_contents($this->ruta) : '';

        foreach ($valores as $clave => $valor) {
            $linea = "{$clave}={$valor}";
            $patron = '/^'.preg_quote($clave, '/').'=.*$/m';

            $contenido = preg_match($patron, $contenido) === 1
                ? (string) preg_replace_callback($patron, fn (): string => $linea, $contenido)
                : rtrim($contenido, "\n")."\n{$linea}\n";
        }

        file_put_contents($this->ruta, $contenido);
    }
}
