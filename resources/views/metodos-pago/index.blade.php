@php
    $formularioAnterior = old('_formulario');
    $campoConError = fn (string $formulario, string $campo) => $formularioAnterior === $formulario ? $errors->first($campo) : null;
@endphp

<x-app-layout title="Métodos de pago">
    <div x-data="{ creando: @js($formularioAnterior === 'crear') }">
        <x-ui.page-header title="Métodos de pago" subtitle="Cuentas o medios con los que pagas tus servicios">
            <x-slot:actions>
                <x-ui.button type="button" icon="mas" @click="creando = ! creando" data-new-method>Nuevo método</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div x-show="creando" x-cloak x-transition.opacity class="mb-6">
            <x-ui.card>
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-900 dark:text-white">Nuevo método de pago</h2>
                    <x-ui.icon-button icon="cerrar" label="Cerrar" @click="creando = false" />
                </div>
                <form method="POST" action="{{ route('metodos-pago.store') }}">
                    @csrf
                    <input type="hidden" name="_formulario" value="crear">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-ui.field label="Nombre" for="nuevo-nombre" :error="$campoConError('crear', 'nombre')">
                            <x-ui.input type="text" name="nombre" id="nuevo-nombre" required maxlength="100" placeholder="Ej: Zelle"
                                :value="$formularioAnterior === 'crear' ? old('nombre') : null" :invalid="(bool) $campoConError('crear', 'nombre')" />
                        </x-ui.field>
                        <x-ui.field label="Detalle (opcional)" for="nuevo-detalle" :error="$campoConError('crear', 'detalle')" class="sm:col-span-2">
                            <x-ui.input type="text" name="detalle" id="nuevo-detalle" maxlength="255" placeholder="Ej: Banesco terminada en 1234"
                                :value="$formularioAnterior === 'crear' ? old('detalle') : null" />
                        </x-ui.field>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <x-ui.button type="button" variant="ghost" @click="creando = false">Cancelar</x-ui.button>
                        <x-ui.button icon="check">Guardar método</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        @if ($metodosPago->isEmpty())
            <x-ui.card>
                <x-ui.empty-state icon="tarjeta" title="Todavía no tienes métodos de pago" description="Registra Zelle, Pago Móvil, tarjetas o efectivo para saber con qué pagas cada servicio.">
                    <x-ui.button type="button" icon="mas" @click="creando = true">Agregar el primero</x-ui.button>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($metodosPago as $metodo)
                    @php($formulario = 'editar-'.$metodo->id)
                    <div id="metodo-{{ $metodo->id }}" x-data="{ editando: @js($formularioAnterior === $formulario) }" data-method>
                        <div x-show="! editando" @class([
                            'relative flex aspect-[1.6/1] flex-col justify-between overflow-hidden rounded-3xl bg-gradient-to-br p-5 text-white shadow-xl transition',
                            \App\Support\Paleta::degradadoPara($metodo->nombre),
                            'shadow-slate-900/10' => $metodo->activo,
                            'opacity-50 grayscale' => ! $metodo->activo,
                        ])>
                            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/15"></div>
                            <div class="pointer-events-none absolute -bottom-16 -left-6 h-40 w-40 rounded-full bg-black/10"></div>

                            <div class="relative flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-lg font-bold tracking-tight">{{ $metodo->nombre }}</p>
                                    <p class="truncate text-sm text-white/75">{{ $metodo->detalle ?? 'Sin detalle' }}</p>
                                </div>
                                <div class="h-8 w-11 shrink-0 rounded-md bg-gradient-to-br from-amber-200 to-amber-400 opacity-90 shadow-inner"></div>
                            </div>

                            <div class="relative flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-medium uppercase tracking-wider text-white/60">Servicios</p>
                                    <p class="text-2xl font-bold">{{ $metodo->gastos_count }}</p>
                                    @unless ($metodo->activo)
                                        <span class="mt-1 inline-block rounded-full bg-black/25 px-2 py-0.5 text-[11px] font-semibold">Inactivo</span>
                                    @endunless
                                </div>
                                <div class="flex items-center gap-1 rounded-2xl bg-black/15 p-1 backdrop-blur">
                                    <button type="button" @click="editando = true" title="Editar" aria-label="Editar {{ $metodo->nombre }}" class="flex h-8 w-8 items-center justify-center rounded-xl text-white/80 transition hover:bg-white/20 hover:text-white">
                                        <x-icon name="lapiz" class="h-4 w-4" />
                                    </button>
                                    <form method="POST" action="{{ route('metodos-pago.toggle-activo', $metodo) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="{{ $metodo->activo ? 'Desactivar' : 'Activar' }}" aria-label="{{ $metodo->activo ? 'Desactivar' : 'Activar' }} {{ $metodo->nombre }}" class="flex h-8 w-8 items-center justify-center rounded-xl text-white/80 transition hover:bg-white/20 hover:text-white">
                                            <x-icon name="encendido" class="h-4 w-4" />
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('metodos-pago.destroy', $metodo) }}" onsubmit="return confirm(@js('¿Eliminar '.$metodo->nombre.'?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar" aria-label="Eliminar {{ $metodo->nombre }}" class="flex h-8 w-8 items-center justify-center rounded-xl text-white/80 transition hover:bg-white/20 hover:text-white">
                                            <x-icon name="papelera" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <x-ui.card x-show="editando" x-cloak>
                            <form method="POST" action="{{ route('metodos-pago.update', $metodo) }}" class="flex h-full flex-col">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_formulario" value="{{ $formulario }}">
                                <div class="space-y-3">
                                    <x-ui.field label="Nombre" :for="$formulario.'-nombre'" :error="$campoConError($formulario, 'nombre')">
                                        <x-ui.input type="text" name="nombre" :id="$formulario.'-nombre'" required maxlength="100"
                                            :value="$formularioAnterior === $formulario ? old('nombre') : $metodo->nombre" :invalid="(bool) $campoConError($formulario, 'nombre')" />
                                    </x-ui.field>
                                    <x-ui.field label="Detalle" :for="$formulario.'-detalle'">
                                        <x-ui.input type="text" name="detalle" :id="$formulario.'-detalle'" maxlength="255"
                                            :value="$formularioAnterior === $formulario ? old('detalle') : $metodo->detalle" />
                                    </x-ui.field>
                                </div>
                                <div class="mt-auto flex justify-end gap-2 pt-4">
                                    <x-ui.button type="button" variant="ghost" size="sm" @click="editando = false">Cancelar</x-ui.button>
                                    <x-ui.button size="sm" icon="check">Guardar</x-ui.button>
                                </div>
                            </form>
                        </x-ui.card>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
