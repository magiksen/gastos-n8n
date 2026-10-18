<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'servicio' => ['required', 'string', 'max:255'],
            'dia_pago' => ['required', 'integer', 'min:1', 'max:31'],
            'monto' => ['nullable', 'numeric', 'min:0'],
            'moneda' => ['required', 'in:USD,VES'],
            'metodo_pago_id' => ['nullable', 'integer', 'exists:metodos_pago,id'],
        ];
    }
}
