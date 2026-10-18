<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

final class AppLayout extends Component
{
    public function __construct(public ?string $title = null) {}

    /**
     * @return list<array{ruta: string, activa: string, etiqueta: string, corta: string, icono: string}>
     */
    public function navegacion(): array
    {
        return [
            ['ruta' => 'gastos.index', 'activa' => 'gastos.index', 'etiqueta' => 'Gastos del mes', 'corta' => 'Mes', 'icono' => 'home'],
            ['ruta' => 'gastos.servicios', 'activa' => 'gastos.servicios', 'etiqueta' => 'Servicios', 'corta' => 'Servicios', 'icono' => 'servicios'],
            ['ruta' => 'metodos-pago.index', 'activa' => 'metodos-pago.*', 'etiqueta' => 'Métodos de pago', 'corta' => 'Métodos', 'icono' => 'tarjeta'],
            ['ruta' => 'profile.edit', 'activa' => 'profile.*', 'etiqueta' => 'Mi perfil', 'corta' => 'Perfil', 'icono' => 'usuario'],
        ];
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
