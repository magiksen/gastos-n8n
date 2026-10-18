<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MetodoPagoRequest;
use App\Models\MetodoPago;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class MetodoPagoController extends Controller
{
    public function index(): View
    {
        $metodosPago = MetodoPago::withCount('gastos')->orderBy('nombre')->get();

        return view('metodos-pago.index', compact('metodosPago'));
    }

    public function store(MetodoPagoRequest $request): RedirectResponse
    {
        $metodoPago = MetodoPago::create($request->validated());

        return redirect()->route('metodos-pago.index')
            ->with('success', "Método de pago '{$metodoPago->nombre}' agregado.");
    }

    public function update(MetodoPagoRequest $request, MetodoPago $metodoPago): RedirectResponse
    {
        $metodoPago->update($request->validated());

        return redirect()->route('metodos-pago.index')
            ->with('success', "Método de pago '{$metodoPago->nombre}' actualizado.");
    }

    public function toggleActivo(MetodoPago $metodoPago): RedirectResponse
    {
        $metodoPago->update(['activo' => ! $metodoPago->activo]);

        $estado = $metodoPago->activo ? 'activado' : 'desactivado';

        return redirect()->route('metodos-pago.index')
            ->with('success', "Método de pago '{$metodoPago->nombre}' {$estado}.");
    }

    public function destroy(MetodoPago $metodoPago): RedirectResponse
    {
        if ($metodoPago->estaEnUso()) {
            return redirect()->route('metodos-pago.index')
                ->withErrors(['metodo_pago' => "'{$metodoPago->nombre}' está asignado a servicios o pagos registrados. Desactívalo en lugar de eliminarlo."]);
        }

        $metodoPago->delete();

        return redirect()->route('metodos-pago.index')
            ->with('success', "Método de pago '{$metodoPago->nombre}' eliminado.");
    }
}
