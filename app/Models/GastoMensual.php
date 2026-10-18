<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GastoMensual extends Model
{
    use HasFactory;

    protected $table = 'gastos_mensuales';

    protected $fillable = [
        'gasto_id',
        'mes',
        'anio',
        'pagado',
        'fecha_pago',
        'metodo_pago_id',
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

    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }

    public function metodoPagoAplicado(): ?MetodoPago
    {
        return $this->metodoPago ?? $this->gasto->metodoPago;
    }

    public function marcarPagado(): void
    {
        $this->update([
            'pagado' => true,
            'fecha_pago' => Carbon::now()->toDateString(),
            'metodo_pago_id' => $this->gasto->metodo_pago_id,
        ]);
    }

    public function marcarPendiente(): void
    {
        $this->update([
            'pagado' => false,
            'fecha_pago' => null,
            'metodo_pago_id' => null,
        ]);
    }
}
