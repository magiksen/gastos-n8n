@php
    $formularioAnterior = old('_formulario');
    $activos = $gastos->where('activo', true);
    $inactivos = $gastos->where('activo', false);
    $totalMensual = (float) $activos->sum(fn ($gasto) => (float) ($gasto->monto ?? 0));
@endphp

<x-app-layout title="Servicios">
    <div x-data="{ creando: @js($formularioAnterior === 'crear') }">
        <x-ui.page-header title="Servicios"
            subtitle="{{ $activos->count() }} activos · ${{ number_format($totalMensual, 2) }} al mes">
            <x-slot:actions>
                <x-ui.button type="button" icon="mas" @click="creando = ! creando" data-new-service>Nuevo servicio</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div x-show="creando" x-cloak x-transition.opacity class="mb-6">
            <x-ui.card>
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-900 dark:text-white">Nuevo servicio</h2>
                    <x-ui.icon-button icon="cerrar" label="Cerrar" @click="creando = false" />
                </div>
                <form method="POST" action="{{ route('gastos.store') }}">
                    @csrf
                    <input type="hidden" name="_formulario" value="crear">
                    @include('gastos.partials.campos-servicio', [
                        'gasto' => null,
                        'prefijo' => 'nuevo',
                        'conValoresAnteriores' => $formularioAnterior === 'crear',
                    ])
                    <div class="mt-5 flex justify-end gap-2">
                        <x-ui.button type="button" variant="ghost" @click="creando = false">Cancelar</x-ui.button>
                        <x-ui.button icon="check">Guardar servicio</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        @if ($gastos->isEmpty())
            <x-ui.card>
                <x-ui.empty-state icon="servicios" title="Aún no tienes servicios" description="Agrega tus gastos fijos (internet, luz, streaming…) y se generarán cada mes automáticamente.">
                    <x-ui.button type="button" icon="mas" @click="creando = true">Agregar el primero</x-ui.button>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @foreach ([['titulo' => 'Activos', 'lista' => $activos], ['titulo' => 'Inactivos', 'lista' => $inactivos]] as $seccion)
            @continue($seccion['lista']->isEmpty())
            <section class="mb-6">
                <h2 class="mb-3 px-1 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    {{ $seccion['titulo'] }} · {{ $seccion['lista']->count() }}
                </h2>
                <x-ui.card :padding="false" class="overflow-hidden">
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($seccion['lista'] as $gasto)
                            @php($formulario = 'editar-'.$gasto->id)
                            <li id="servicio-{{ $gasto->id }}" x-data="{ editando: @js($formularioAnterior === $formulario) }" data-service>
                                <div x-show="! editando" @class(['flex items-center gap-3 px-4 py-4 sm:gap-4 sm:px-6', 'opacity-60' => ! $gasto->activo])>
                                    <x-ui.avatar :name="$gasto->servicio" />
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $gasto->servicio }}</p>
                                            @if ($gasto->moneda === 'VES')
                                                <x-ui.badge color="amber">Bs</x-ui.badge>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                            <span class="inline-flex items-center gap-1"><x-icon name="calendario" class="h-3.5 w-3.5" />Día {{ $gasto->dia_pago }}</span>
                                            <span class="text-slate-300 dark:text-slate-600">·</span>
                                            <span class="inline-flex items-center gap-1"><x-icon name="tarjeta" class="h-3.5 w-3.5" />{{ $gasto->metodoPago?->nombre ?? 'Sin método' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-slate-900 sm:text-base dark:text-white">{{ $gasto->monto ? '$'.number_format((float) $gasto->monto, 2) : '—' }}</p>
                                        @if ($gasto->monto && $gasto->moneda === 'VES' && $tasaBcv)
                                            <p class="text-[11px] font-medium text-amber-600 dark:text-amber-400">Bs {{ number_format((float) $gasto->monto * $tasaBcv, 2) }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center">
                                        <x-ui.icon-button icon="lapiz" label="Editar" tone="indigo" @click="editando = true" />
                                        <form method="POST" action="{{ route('gastos.toggle-activo', $gasto) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.icon-button type="submit" icon="encendido" :label="$gasto->activo ? 'Desactivar' : 'Activar'" :tone="$gasto->activo ? 'amber' : 'emerald'" />
                                        </form>
                                        <form method="POST" action="{{ route('gastos.destroy', $gasto) }}" onsubmit="return confirm(@js('¿Eliminar '.$gasto->servicio.'?'))">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.icon-button type="submit" icon="papelera" label="Eliminar" tone="rose" />
                                        </form>
                                    </div>
                                </div>

                                <form x-show="editando" x-cloak method="POST" action="{{ route('gastos.update', $gasto) }}" class="bg-slate-50/70 px-4 py-5 sm:px-6 dark:bg-slate-800/30">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_formulario" value="{{ $formulario }}">
                                    @include('gastos.partials.campos-servicio', [
                                        'prefijo' => $formulario,
                                        'conValoresAnteriores' => $formularioAnterior === $formulario,
                                    ])
                                    <div class="mt-5 flex justify-end gap-2">
                                        <x-ui.button type="button" variant="ghost" @click="editando = false">Cancelar</x-ui.button>
                                        <x-ui.button icon="check">Guardar cambios</x-ui.button>
                                    </div>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            </section>
        @endforeach
    </div>
</x-app-layout>
