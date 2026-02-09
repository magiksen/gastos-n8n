<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'servicio',
        'dia_pago',
        'monto',
        'moneda',
        'activo',
    ];

    protected $casts = [
        'dia_pago' => 'integer',
        'monto' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function mensuales(): HasMany
    {
        return $this->hasMany(GastoMensual::class, 'gasto_id');
    }
}
