@props(['tipo' => 'success', 'autocerrar' => null])

@php
    $estilos = [
        'success' => ['icono' => 'check-circulo', 'color' => 'bg-emerald-500'],
        'warning' => ['icono' => 'alerta', 'color' => 'bg-amber-500'],
        'error' => ['icono' => 'alerta', 'color' => 'bg-rose-500'],
    ][$tipo];
@endphp

<div
    id="toast-{{ \Illuminate\Support\Str::random(10) }}"
    data-toast="{{ $tipo }}"
    x-data="{ visible: true }"
    @if ($autocerrar) x-init="setTimeout(() => visible = false, {{ (int) $autocerrar }})" @endif
    x-show="visible"
    x-transition:enter="transition duration-300 ease-out"
    x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:leave="transition duration-200 ease-in"
    x-transition:leave-end="opacity-0"
    role="{{ $tipo === 'error' ? 'alert' : 'status' }}"
    class="pointer-events-auto flex items-start gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-sm text-white shadow-2xl shadow-slate-900/20 ring-1 ring-white/10 dark:bg-slate-800"
>
    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $estilos['color'] }}">
        <x-icon :name="$estilos['icono']" class="h-3.5 w-3.5" />
    </span>
    <div class="min-w-0 flex-1 leading-relaxed">{{ $slot }}</div>
    <button type="button" @click="visible = false" class="shrink-0 text-white/50 transition hover:text-white" aria-label="Cerrar">
        <x-icon name="cerrar" class="h-4 w-4" />
    </button>
</div>
