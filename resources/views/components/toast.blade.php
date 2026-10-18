@props(['tipo' => 'success', 'autocerrar' => null])

@php
    $estilos = [
        'success' => ['caja' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'icono' => 'text-emerald-500', 'ruta' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'warning' => ['caja' => 'bg-amber-50 border-amber-200 text-amber-800', 'icono' => 'text-amber-500', 'ruta' => 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
        'error' => ['caja' => 'bg-rose-50 border-rose-200 text-rose-800', 'icono' => 'text-rose-500', 'ruta' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ][$tipo];
@endphp

<div
    id="toast-{{ \Illuminate\Support\Str::random(10) }}"
    data-toast="{{ $tipo }}"
    x-data="{ visible: true }"
    @if($autocerrar) x-init="setTimeout(() => visible = false, {{ (int) $autocerrar }})" @endif
    x-show="visible"
    x-transition.opacity.duration.300ms
    role="{{ $tipo === 'error' ? 'alert' : 'status' }}"
    class="pointer-events-auto flex items-start gap-3 border px-4 py-3 rounded-xl text-sm shadow-lg {{ $estilos['caja'] }}"
>
    <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $estilos['icono'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $estilos['ruta'] }}"/></svg>
    <div class="flex-1 min-w-0">{{ $slot }}</div>
    <button type="button" @click="visible = false" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity" aria-label="Cerrar">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
