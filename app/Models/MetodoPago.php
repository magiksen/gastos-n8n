<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'nombre',
        'detalle',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'metodo_pago_id');
    }

    public function gastosMensuales(): HasMany
    {
        return $this->hasMany(GastoMensual::class, 'metodo_pago_id');
    }

    public function estaEnUso(): bool
    {
        return $this->gastos()->exists() || $this->gastosMensuales()->exists();
    }
}
