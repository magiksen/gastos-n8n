<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $meses[$mes] }} {{ $anio }}</h1>
                <p class="text-sm text-slate-500 mt-1">Resumen de gastos del mes</p>
            </div>
            <form method="GET" action="{{ route('gastos.index') }}" class="flex items-center gap-2">
                <select name="mes" class="rounded-xl border-slate-200 bg-white text-sm text-slate-700 focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                    @foreach($meses as $num => $nombre)
                        <option value="{{ $num }}" {{ $mes === $num ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
                <input type="number" name="anio" value="{{ $anio }}" min="2024" max="2030" class="w-24 rounded-xl border-slate-200 bg-white text-sm text-slate-700 focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Ver
                </button>
            </form>
        </div>
    </x-slot>

    <div class="space-y-6">

        @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/60 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/60 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total</p>
                        <p class="text-xl font-bold text-slate-900">${{ number_format($total, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/60 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pagado</p>
                        <p class="text-xl font-bold text-emerald-600">${{ number_format($pagado, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/60 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pendiente</p>
                        <p class="text-xl font-bold text-rose-600">${{ number_format($pendiente, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress bar --}}
        @if($total > 0)
            <div class="bg-white rounded-2xl border border-slate-200/60 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700">Progreso del mes</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $total > 0 ? round(($pagado / $total) * 100) : 0 }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5">
                    <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $total > 0 ? round(($pagado / $total) * 100) : 0 }}%"></div>
                </div>
            </div>
        @endif

        {{-- Alerts --}}
        @if($vencidos->count() > 0)
            <div class="bg-rose-50 border border-rose-200/60 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <h3 class="text-rose-800 font-semibold text-sm">Pagos Vencidos</h3>
                </div>
                <div class="space-y-2">
                    @foreach($vencidos as $gm)
                        <div class="flex items-center justify-between bg-white/60 rounded-xl px-4 py-2.5">
                            <span class="text-sm font-medium text-rose-800">{{ $gm->gasto->servicio }}</span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-rose-500">Dia {{ $gm->gasto->dia_pago }}</span>
                                @if($gm->gasto->monto)
                                    <span class="text-sm font-semibold text-rose-700">${{ number_format((float)$gm->gasto->monto, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($proximos->count() > 0)
            <div class="bg-amber-50 border border-amber-200/60 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-amber-800 font-semibold text-sm">Proximos a Vencer (3 dias)</h3>
                </div>
                <div class="space-y-2">
                    @foreach($proximos as $gm)
                        <div class="flex items-center justify-between bg-white/60 rounded-xl px-4 py-2.5">
                            <span class="text-sm font-medium text-amber-800">{{ $gm->gasto->servicio }}</span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-amber-500">Dia {{ $gm->gasto->dia_pago }}</span>
                                @if($gm->gasto->monto)
                                    <span class="text-sm font-semibold text-amber-700">${{ number_format((float)$gm->gasto->monto, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Expenses table --}}
        <div class="bg-white rounded-2xl border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Servicio</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Dia</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Monto</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Pago</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($mensuales as $gm)
                            <tr class="group hover:bg-slate-50/50 transition-colors {{ $gm->pagado ? 'bg-emerald-50/30' : '' }}">
                                <td class="px-5 py-3.5">
                                    <form method="POST" action="{{ route('gastos.toggle', $gm) }}">
                                        @csrf
                                        <button type="submit" class="group/btn flex items-center justify-center w-8 h-8 rounded-lg transition-all {{ $gm->pagado ? 'bg-emerald-100 hover:bg-emerald-200' : 'bg-slate-100 hover:bg-slate-200' }}" title="{{ $gm->pagado ? 'Marcar pendiente' : 'Marcar pagado' }}">
                                            @if($gm->pagado)
                                                <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <div class="w-4 h-4 rounded border-2 border-slate-300 group-hover/btn:border-slate-400"></div>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-sm font-medium {{ $gm->pagado ? 'text-slate-400 line-through' : 'text-slate-900' }}">{{ $gm->gasto->servicio }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-xs font-semibold text-slate-600">{{ $gm->gasto->dia_pago }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-sm font-semibold {{ $gm->pagado ? 'text-slate-400' : 'text-slate-900' }}">
                                        {{ $gm->gasto->monto ? '$' . number_format((float)$gm->gasto->monto, 2) : '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="text-sm text-slate-400">
                                        {{ $gm->fecha_pago ? $gm->fecha_pago->format('d/m/Y') : '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($gm->comprobante_path)
                                            <a href="{{ route('gastos.ver-comprobante', $gm) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-medium hover:bg-emerald-100 transition-colors" target="_blank">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Ver
                                            </a>
                                        @endif
                                        <button onclick="document.getElementById('comprobante-{{ $gm->id }}').classList.toggle('hidden')" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium hover:bg-slate-200 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Subir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr id="comprobante-{{ $gm->id }}" class="hidden">
                                <td colspan="6" class="px-5 py-3 bg-slate-50/50">
                                    <form method="POST" action="{{ route('gastos.comprobante', $gm) }}" enctype="multipart/form-data" class="flex items-center gap-3">
                                        @csrf
                                        <input type="file" name="comprobante" accept="image/*" required class="text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer file:transition-colors">
                                        <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-emerald-500 text-white text-sm font-medium rounded-xl hover:bg-emerald-600 transition-colors">
                                            Guardar
                                        </button>
                                        <button type="button" onclick="document.getElementById('comprobante-{{ $gm->id }}').classList.add('hidden')" class="text-sm text-slate-400 hover:text-slate-600">
                                            Cancelar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
