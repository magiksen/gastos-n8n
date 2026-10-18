<?php

declare(strict_types=1);

namespace App\Support;

final class Paleta
{
    private const DEGRADADOS = [
        'from-indigo-500 to-violet-600',
        'from-fuchsia-500 to-pink-600',
        'from-sky-500 to-indigo-600',
        'from-emerald-500 to-teal-600',
        'from-amber-500 to-orange-600',
        'from-rose-500 to-red-600',
        'from-cyan-500 to-blue-600',
        'from-violet-500 to-purple-700',
    ];

    public static function degradadoPara(string $nombre): string
    {
        return self::DEGRADADOS[crc32(mb_strtolower(trim($nombre))) % count(self::DEGRADADOS)];
    }

    public static function iniciales(string $nombre): string
    {
        $palabras = preg_split('/\s+/u', trim($nombre), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];
        $iniciales = count($palabras) > 1
            ? mb_substr($palabras[0], 0, 1).mb_substr($palabras[1], 0, 1)
            : mb_substr($palabras[0], 0, 2);

        return mb_strtoupper($iniciales);
    }
}
