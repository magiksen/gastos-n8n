<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Servicios</h1>
            <p class="text-sm text-slate-500 mt-1">Gestiona tus gastos fijos mensuales</p>
        </div>
    </x-slot>

    <div class="space-y-6">

        @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/60 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="flex items-start gap-3 bg-rose-50 border border-rose-200/60 text-rose-700 px-4 py-3 rounded-xl text-sm">
                <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Add form --}}
        <div class="bg-white rounded-2xl border border-slate-200/60 p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Agregar Servicio</h3>
            </div>
            <form method="POST" action="{{ route('gastos.store') }}" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                @csrf
                <div class="w-full sm:flex-1">
                    <label for="servicio" class="block text-sm font-medium text-slate-700 mb-1.5">Servicio</label>
                    <input type="text" name="servicio" id="servicio" required placeholder="Ej: Netflix"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5" value="{{ old('servicio') }}">
                </div>
                <div class="w-full sm:w-28">
                    <label for="dia_pago" class="block text-sm font-medium text-slate-700 mb-1.5">Dia de Pago</label>
                    <input type="number" name="dia_pago" id="dia_pago" required min="1" max="31" placeholder="1-31"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5" value="{{ old('dia_pago') }}">
                </div>
                <div class="w-full sm:w-32">
                    <label for="monto" class="block text-sm font-medium text-slate-700 mb-1.5">Monto</label>
                    <input type="number" name="monto" id="monto" step="0.01" min="0" placeholder="0.00"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5" value="{{ old('monto') }}">
                </div>
                <div class="w-full sm:w-28">
                    <label for="moneda" class="block text-sm font-medium text-slate-700 mb-1.5">Moneda</label>
                    <select name="moneda" id="moneda" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5">
                        <option value="USD" {{ old('moneda') === 'VES' ? '' : 'selected' }}>$ USD</option>
                        <option value="VES" {{ old('moneda') === 'VES' ? 'selected' : '' }}>Bs</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 text-white text-sm font-semibold rounded-xl hover:bg-emerald-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Agregar
                </button>
            </form>
        </div>

        {{-- Services list --}}
        <div class="bg-white rounded-2xl border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Tus Servicios</h3>
                    <span class="text-sm text-slate-500">{{ $gastos->count() }} servicios</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Servicio</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Dia</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Monto</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($gastos as $gasto)
                            <tr id="row-{{ $gasto->id }}" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">
                                            <span class="text-xs font-bold text-slate-500">{{ strtoupper(substr($gasto->servicio, 0, 2)) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-slate-900">{{ $gasto->servicio }}</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ ($gasto->moneda ?? 'USD') === 'VES' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">{{ ($gasto->moneda ?? 'USD') === 'VES' ? 'Bs' : 'USD' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-xs font-semibold text-slate-600">{{ $gasto->dia_pago }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-sm font-semibold text-slate-900">
                                        @if($gasto->monto)
                                            {{ ($gasto->moneda ?? 'USD') === 'VES' ? 'Bs' : '$' }} {{ number_format((float)$gasto->monto, 2) }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($gasto->activo)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            onclick="document.getElementById('edit-{{ $gasto->id }}').classList.remove('hidden'); document.getElementById('row-{{ $gasto->id }}').classList.add('hidden')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                                            title="Editar"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('gastos.destroy', $gasto) }}" class="inline" onsubmit="return confirm('Eliminar {{ $gasto->servicio }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            {{-- Edit row --}}
                            <tr id="edit-{{ $gasto->id }}" class="hidden">
                                <td colspan="5" class="px-5 py-4 bg-slate-50/50">
                                    <form method="POST" action="{{ route('gastos.update', $gasto) }}" class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="w-full sm:flex-1">
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Servicio</label>
                                            <input type="text" name="servicio" value="{{ $gasto->servicio }}" required class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                                        </div>
                                        <div class="w-full sm:w-24">
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Dia</label>
                                            <input type="number" name="dia_pago" value="{{ $gasto->dia_pago }}" required min="1" max="31" class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                                        </div>
                                        <div class="w-full sm:w-28">
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Monto</label>
                                            <input type="number" name="monto" value="{{ $gasto->monto }}" step="0.01" min="0" class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                                        </div>
                                        <div class="w-full sm:w-24">
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Moneda</label>
                                            <select name="moneda" class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                                                <option value="USD" {{ ($gasto->moneda ?? 'USD') === 'USD' ? 'selected' : '' }}>$ USD</option>
                                                <option value="VES" {{ ($gasto->moneda ?? 'USD') === 'VES' ? 'selected' : '' }}>Bs</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-xl hover:bg-emerald-600 transition-colors">
                                                Guardar
                                            </button>
                                            <button type="button"
                                                onclick="document.getElementById('edit-{{ $gasto->id }}').classList.add('hidden'); document.getElementById('row-{{ $gasto->id }}').classList.remove('hidden')"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors"
                                            >
                                                Cancelar
                                            </button>
                                        </div>
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
