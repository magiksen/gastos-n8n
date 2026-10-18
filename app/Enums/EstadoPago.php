<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoPago: string
{
    case Pagado = 'pagado';
    case Vencido = 'vencido';
    case PorVencer = 'por_vencer';
    case Pendiente = 'pendiente';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pagado => 'Pagado',
            self::Vencido => 'Vencido',
            self::PorVencer => 'Por vencer',
            self::Pendiente => 'Pendiente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pagado => 'emerald',
            self::Vencido => 'rose',
            self::PorVencer => 'amber',
            self::Pendiente => 'slate',
        };
    }
}
