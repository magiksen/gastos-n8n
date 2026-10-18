<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Métodos de pago</h1>
            <p class="text-sm text-slate-500 mt-1">Cuentas o medios con los que pagas tus servicios</p>
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

        <div class="bg-white rounded-2xl border border-slate-200/60 p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Agregar método de pago</h3>
            </div>
            <form method="POST" action="{{ route('metodos-pago.store') }}" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                @csrf
                <div class="w-full sm:w-64">
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                    <input type="text" name="nombre" id="nombre" required maxlength="100" placeholder="Ej: Zelle"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5" value="{{ old('nombre') }}">
                </div>
                <div class="w-full sm:flex-1">
                    <label for="detalle" class="block text-sm font-medium text-slate-700 mb-1.5">Detalle <span class="text-slate-400 font-normal">(opcional)</span></label>
                    <input type="text" name="detalle" id="detalle" maxlength="255" placeholder="Ej: Banesco terminada en 1234"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5" value="{{ old('detalle') }}">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 text-white text-sm font-semibold rounded-xl hover:bg-emerald-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Agregar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Tus métodos de pago</h3>
                    <span class="text-sm text-slate-500">{{ $metodosPago->count() }} métodos</span>
                </div>
            </div>

            @if($metodosPago->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-slate-500">Todavía no tienes métodos de pago. Agrega el primero arriba.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Detalle</th>
                                <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Servicios</th>
                                <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($metodosPago as $metodo)
                                <tr id="metodo-row-{{ $metodo->id }}" class="group hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <span class="text-sm font-medium text-slate-900">{{ $metodo->nombre }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="text-sm text-slate-500">{{ $metodo->detalle ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-lg bg-slate-100 text-xs font-semibold text-slate-600">{{ $metodo->gastos_count }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if($metodo->activo)
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
                                                onclick="document.getElementById('metodo-edit-{{ $metodo->id }}').classList.remove('hidden'); document.getElementById('metodo-row-{{ $metodo->id }}').classList.add('hidden')"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('metodos-pago.toggle-activo', $metodo) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                @if($metodo->activo)
                                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Desactivar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                    </button>
                                                @else
                                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Activar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </button>
                                                @endif
                                            </form>
                                            <form method="POST" action="{{ route('metodos-pago.destroy', $metodo) }}" class="inline" onsubmit="return confirm('¿Eliminar {{ $metodo->nombre }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="metodo-edit-{{ $metodo->id }}" class="hidden">
                                    <td colspan="5" class="px-5 py-4 bg-slate-50/50">
                                        <form method="POST" action="{{ route('metodos-pago.update', $metodo) }}" class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                                            @csrf
                                            @method('PUT')
                                            <div class="w-full sm:w-64">
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Nombre</label>
                                                <input type="text" name="nombre" value="{{ $metodo->nombre }}" required maxlength="100" class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                                            </div>
                                            <div class="w-full sm:flex-1">
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Detalle</label>
                                                <input type="text" name="detalle" value="{{ $metodo->detalle }}" maxlength="255" class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-emerald-500/20 focus:border-emerald-500 py-2">
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-xl hover:bg-emerald-600 transition-colors">
                                                    Guardar
                                                </button>
                                                <button type="button"
                                                    onclick="document.getElementById('metodo-edit-{{ $metodo->id }}').classList.add('hidden'); document.getElementById('metodo-row-{{ $metodo->id }}').classList.remove('hidden')"
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
            @endif
        </div>

    </div>
</x-app-layout>
