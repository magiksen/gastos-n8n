<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GastoMensual extends Model
{
    protected $table = 'gastos_mensuales';

    protected $fillable = [
        'gasto_id',
        'mes',
        'anio',
        'pagado',
        'fecha_pago',
        'comprobante_path',
        'notas',
    ];

    protected $casts = [
        'mes' => 'integer',
        'anio' => 'integer',
        'pagado' => 'boolean',
        'fecha_pago' => 'date',
    ];

    public function gasto(): BelongsTo
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }
}
