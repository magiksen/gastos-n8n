@php
    $anterior = $resumen->mesAnterior();
    $siguiente = $resumen->mesSiguiente();
    $pendientes = $resumen->pendientes();
    $vencidos = $resumen->vencidos();
    $proximos = $resumen->proximos();
    $pendientePorMetodo = $resumen->pendientePorMetodo();
    $maximoPorMetodo = max($pendientePorMetodo->max() ?? 0, 1);
    $coloresEstado = [
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        'rose' => 'text-rose-600 dark:text-rose-400',
        'amber' => 'text-amber-600 dark:text-amber-400',
        'slate' => 'text-slate-500 dark:text-slate-400',
    ];
@endphp

<x-app-layout :title="$resumen->nombreMes()">
    <x-ui.page-header :title="$resumen->nombreMes()" subtitle="Resumen de tus pagos del mes">
        <x-slot:actions>
            <div class="flex items-center gap-1 rounded-2xl bg-white p-1 ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800" data-month-nav>
                <x-ui.icon-button icon="izquierda" label="Mes anterior" :href="route('gastos.index', $anterior)" />
                @unless ($resumen->esMesActual())
                    <a href="{{ route('gastos.index') }}" class="rounded-xl px-3 py-2 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10">Mes actual</a>
                @endunless
                <x-ui.icon-button icon="derecha" label="Mes siguiente" :href="route('gastos.index', $siguiente)" />
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-5 lg:grid-cols-3">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 p-6 text-white shadow-xl shadow-violet-500/20 sm:p-8 lg:col-span-2 dark:shadow-none" data-hero>
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-fuchsia-400/30 blur-3xl"></div>

            <div class="relative flex h-full flex-col">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-white/80">Pendiente por pagar</p>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                        {{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'pago pendiente' : 'pagos pendientes' }}
                    </span>
                </div>

                <p class="mt-3 text-4xl font-extrabold tracking-tight sm:text-5xl">${{ number_format($resumen->pendiente(), 2) }}</p>

                @if ($resumen->pendienteEnBolivares() > 0)
                    <p class="mt-2 text-sm text-white/80">
                        ${{ number_format($resumen->pendienteEnBolivares(), 2) }} se pagan en bolívares
                        @if ($tasaBcv)
                            <span class="font-semibold text-white">≈ Bs {{ number_format($resumen->pendienteEnBolivares() * $tasaBcv, 2) }}</span>
                        @endif
                    </p>
                @endif

                <div class="mt-6 lg:mt-auto lg:pt-6">
                    <div class="h-2 overflow-hidden rounded-full bg-white/20">
                        <div class="h-full rounded-full bg-white transition-all duration-700" style="width: {{ $resumen->porcentajePagado() }}%"></div>
                    </div>
                    <p class="mt-2 text-xs font-medium text-white/80">
                        {{ $resumen->cantidadPagados() }} de {{ $resumen->mensuales->count() }} pagados · {{ $resumen->porcentajePagado() }}%
                    </p>
                </div>

                <dl class="mt-6 grid grid-cols-3 gap-2 sm:gap-3">
                    <div class="rounded-2xl bg-white/10 p-3 backdrop-blur sm:p-4">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-white/70">Total</dt>
                        <dd class="mt-1 text-base font-bold sm:text-lg">${{ number_format($resumen->total(), 2) }}</dd>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-3 backdrop-blur sm:p-4">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-white/70">Pagado</dt>
                        <dd class="mt-1 text-base font-bold sm:text-lg">${{ number_format($resumen->pagado(), 2) }}</dd>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-3 backdrop-blur sm:p-4">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-white/70">Vencidos</dt>
                        <dd class="mt-1 text-base font-bold sm:text-lg">{{ $vencidos->count() }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="flex flex-col gap-5">
            <x-ui.card data-bcv>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-emerald-500/25">
                        <x-icon name="cambio" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tasa BCV</p>
                        @if ($tasaBcv)
                            <p class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Bs {{ number_format($tasaBcv, 2) }}</p>
                        @else
                            <p class="text-sm font-medium text-slate-400">No disponible</p>
                        @endif
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">Por cada dólar · fuente bcv.org.ve</p>
            </x-ui.card>

            <x-ui.card class="flex-1">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Pendiente por método de pago</h2>
                @forelse ($pendientePorMetodo as $nombreMetodo => $totalPendiente)
                    <div class="mt-4">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-gradient-to-br {{ $nombreMetodo === 'Sin método' ? 'from-slate-300 to-slate-400 dark:from-slate-600 dark:to-slate-500' : \App\Support\Paleta::degradadoPara($nombreMetodo) }}"></span>
                                <span class="truncate font-medium {{ $nombreMetodo === 'Sin método' ? 'text-slate-400' : 'text-slate-700 dark:text-slate-200' }}">{{ $nombreMetodo }}</span>
                            </span>
                            <span class="font-semibold text-slate-900 dark:text-white">${{ number_format($totalPendiente, 2) }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-gradient-to-r {{ $nombreMetodo === 'Sin método' ? 'from-slate-300 to-slate-400 dark:from-slate-600 dark:to-slate-500' : \App\Support\Paleta::degradadoPara($nombreMetodo) }}" style="width: {{ round($totalPendiente / $maximoPorMetodo * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No queda nada pendiente este mes.</p>
                @endforelse
            </x-ui.card>
        </div>
    </div>

    <section class="mt-5" data-attention>
        @if ($vencidos->isEmpty() && $proximos->isEmpty())
            <div class="flex items-center gap-4 rounded-2xl bg-emerald-50 p-5 ring-1 ring-emerald-600/10 dark:bg-emerald-500/10 dark:ring-emerald-500/20">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white">
                    <x-icon name="destellos" class="h-5 w-5" />
                </div>
                <div>
                    <p class="font-semibold text-emerald-900 dark:text-emerald-300">Todo al día</p>
                    <p class="text-sm text-emerald-700 dark:text-emerald-400/80">No tienes pagos vencidos ni por vencer en los próximos días.</p>
                </div>
            </div>
        @else
            <div class="grid gap-5 md:grid-cols-2">
                @foreach ([
                    ['lista' => $vencidos, 'titulo' => 'Vencidos', 'icono' => 'alerta', 'color' => 'bg-rose-500', 'anillo' => 'ring-rose-200 dark:ring-rose-500/20'],
                    ['lista' => $proximos, 'titulo' => 'Por vencer', 'icono' => 'reloj', 'color' => 'bg-amber-500', 'anillo' => 'ring-amber-200 dark:ring-amber-500/20'],
                ] as $grupo)
                    @continue($grupo['lista']->isEmpty())
                    <x-ui.card :padding="false" class="overflow-hidden {{ $grupo['anillo'] }}">
                        <div class="flex items-center gap-3 px-5 pb-2 pt-5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl text-white {{ $grupo['color'] }}">
                                <x-icon :name="$grupo['icono']" class="h-4 w-4" />
                            </span>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $grupo['titulo'] }}</h2>
                            <span class="ml-auto text-xs font-semibold text-slate-400">{{ $grupo['lista']->count() }}</span>
                        </div>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($grupo['lista'] as $gm)
                                <li class="flex items-center gap-3 px-5 py-3">
                                    <x-ui.avatar :name="$gm->gasto->servicio" size="sm" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $gm->gasto->servicio }}</p>
                                        <p class="text-xs {{ $coloresEstado[$resumen->estadoDe($gm)->color()] }}">{{ $resumen->vencimientoDe($gm) }}</p>
                                    </div>
                                    @if ($gm->gasto->monto)
                                        <span class="text-sm font-semibold text-slate-900 dark:text-white">${{ number_format((float) $gm->gasto->monto, 2) }}</span>
                                    @endif
                                    <form method="POST" action="{{ route('gastos.toggle', $gm) }}">
                                        @csrf
                                        <x-ui.icon-button type="submit" icon="check" label="Marcar {{ $gm->gasto->servicio }} como pagado" tone="emerald" />
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </section>

    <x-ui.card :padding="false" class="mt-5 overflow-hidden"
        x-data="{ filtro: sessionStorage.getItem('filtroPagos') ?? 'todos' }"
        x-init="$watch('filtro', valor => sessionStorage.setItem('filtroPagos', valor))"
        data-payments>
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800">
            <h2 class="font-semibold text-slate-900 dark:text-white">Pagos del mes</h2>
            <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800" role="tablist">
                @foreach (['todos' => 'Todos', 'pendientes' => 'Pendientes', 'pagados' => 'Pagados'] as $valor => $etiqueta)
                    <button type="button" role="tab" @click="filtro = '{{ $valor }}'"
                        :class="filtro === '{{ $valor }}' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="rounded-lg px-3 py-1.5 transition">{{ $etiqueta }}</button>
                @endforeach
            </div>
        </div>

        @if ($resumen->mensuales->isEmpty())
            <x-ui.empty-state icon="servicios" title="No hay pagos este mes" description="Agrega servicios activos para que aparezcan aquí cada mes.">
                <x-ui.button :href="route('gastos.servicios')" icon="mas">Agregar servicio</x-ui.button>
            </x-ui.empty-state>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($resumen->mensuales as $gm)
                    @php
                        $estado = $resumen->estadoDe($gm);
                        $metodoPago = $gm->metodoPagoAplicado();
                        $enBolivares = $gm->gasto->moneda === 'VES';
                    @endphp
                    <li id="pago-{{ $gm->id }}" x-data="{ subiendo: false }"
                        x-show="filtro === 'todos' || filtro === '{{ $gm->pagado ? 'pagados' : 'pendientes' }}'"
                        data-payment="{{ $estado->value }}"
                        @class(['transition-colors', 'bg-emerald-50/40 dark:bg-emerald-500/[0.04]' => $gm->pagado])>
                        <div class="flex items-center gap-3 px-4 py-4 sm:gap-4 sm:px-6">
                            <form method="POST" action="{{ route('gastos.toggle', $gm) }}">
                                @csrf
                                <button type="submit" title="{{ $gm->pagado ? 'Marcar pendiente' : 'Marcar pagado' }}" aria-label="{{ $gm->pagado ? 'Marcar pendiente' : 'Marcar pagado' }}" aria-pressed="{{ $gm->pagado ? 'true' : 'false' }}"
                                    @class([
                                        'flex h-9 w-9 items-center justify-center rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900',
                                        'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/30 hover:from-emerald-500 hover:to-emerald-700' => $gm->pagado,
                                        'text-transparent ring-2 ring-inset ring-slate-300 hover:bg-emerald-50 hover:text-emerald-500 hover:ring-emerald-400 dark:ring-slate-600 dark:hover:bg-emerald-500/10' => ! $gm->pagado,
                                    ])>
                                    <x-icon name="check" class="h-5 w-5" />
                                </button>
                            </form>

                            <x-ui.avatar :name="$gm->gasto->servicio" class="hidden sm:flex" />

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p @class(['truncate font-semibold', 'text-slate-400 line-through dark:text-slate-500' => $gm->pagado, 'text-slate-900 dark:text-white' => ! $gm->pagado])>{{ $gm->gasto->servicio }}</p>
                                    @if ($enBolivares)
                                        <x-ui.badge color="amber">Bs</x-ui.badge>
                                    @endif
                                </div>
                                <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                    <span class="font-medium {{ $coloresEstado[$estado->color()] }}">{{ $resumen->vencimientoDe($gm) }}</span>
                                    @if ($metodoPago)
                                        <span class="hidden text-slate-300 sm:inline dark:text-slate-600">·</span>
                                        <span class="inline-flex items-center gap-1 text-slate-500 dark:text-slate-400">
                                            <x-icon name="tarjeta" class="h-3.5 w-3.5" />{{ $metodoPago->nombre }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right">
                                <p @class(['text-sm font-bold sm:text-base', 'text-slate-400 dark:text-slate-500' => $gm->pagado, 'text-slate-900 dark:text-white' => ! $gm->pagado])>
                                    {{ $gm->gasto->monto ? '$'.number_format((float) $gm->gasto->monto, 2) : '—' }}
                                </p>
                                @if ($gm->gasto->monto && $enBolivares && $tasaBcv)
                                    <p class="text-[11px] font-medium text-amber-600 dark:text-amber-400">Bs {{ number_format((float) $gm->gasto->monto * $tasaBcv, 2) }}</p>
                                @endif
                            </div>

                            <div class="flex items-center">
                                @if ($gm->comprobante_path)
                                    <x-ui.icon-button icon="ojo" label="Ver comprobante" tone="emerald" :href="route('gastos.ver-comprobante', $gm)" target="_blank" class="!text-emerald-500" />
                                @endif
                                <x-ui.icon-button icon="subir" :label="$gm->comprobante_path ? 'Reemplazar comprobante' : 'Subir comprobante'" tone="indigo" @click="subiendo = ! subiendo" />
                            </div>
                        </div>

                        <div x-show="subiendo" x-cloak x-transition class="px-4 pb-4 sm:px-6">
                            <form method="POST" action="{{ route('gastos.comprobante', $gm) }}" enctype="multipart/form-data"
                                class="flex flex-col gap-3 rounded-2xl bg-slate-50 p-3 sm:flex-row sm:items-center dark:bg-slate-800/50">
                                @csrf
                                <input type="file" name="comprobante" accept="image/*" required
                                    class="min-w-0 flex-1 text-sm text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-700 dark:text-slate-400 dark:file:bg-white dark:file:text-slate-900">
                                <div class="flex gap-2">
                                    <x-ui.button type="button" variant="ghost" size="sm" @click="subiendo = false">Cancelar</x-ui.button>
                                    <x-ui.button size="sm" icon="subir">Guardar</x-ui.button>
                                </div>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
</x-app-layout>
