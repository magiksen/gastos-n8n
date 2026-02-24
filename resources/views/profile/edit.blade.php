<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Mi Perfil</h1>
            <p class="text-sm text-slate-500 mt-1">Administra tu información personal y seguridad</p>
        </div>
    </x-slot>

    <div class="space-y-6 max-w-2xl">

        {{-- Alerts --}}
        @if(session('status') === 'profile-updated')
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/60 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Perfil actualizado correctamente.
            </div>
        @endif

        @if(session('status') === 'password-updated')
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/60 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Contraseña actualizada correctamente. Las demás sesiones han sido cerradas.
            </div>
        @endif

        {{-- Profile info card --}}
        <div class="bg-white rounded-2xl border border-slate-200/60 p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Información Personal</h3>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                    <input type="text" name="name" id="name" required autofocus autocomplete="name"
                        value="{{ old('name', $user->name) }}"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5 @error('name') border-rose-400 bg-rose-50 @enderror">
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                    <input type="email" name="email" id="email" required autocomplete="username"
                        value="{{ old('email', $user->email) }}"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5 @error('email') border-rose-400 bg-rose-50 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 text-white text-sm font-semibold rounded-xl hover:bg-emerald-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        {{-- Change password card --}}
        <div class="bg-white rounded-2xl border border-slate-200/60 p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Cambiar Contraseña</h3>
            </div>
            <p class="text-sm text-slate-500 mb-5">Usa una contraseña larga y aleatoria para mantener tu cuenta segura. Al cambiarla, todas las demás sesiones activas serán cerradas.</p>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña actual</label>
                    <input type="password" name="current_password" id="current_password" autocomplete="current-password"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5 @error('current_password', 'updatePassword') border-rose-400 bg-rose-50 @enderror">
                    @error('current_password', 'updatePassword')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Nueva contraseña</label>
                    <input type="password" name="password" id="password" autocomplete="new-password"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5 @error('password', 'updatePassword') border-rose-400 bg-rose-50 @enderror">
                    @error('password', 'updatePassword')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-emerald-500/20 focus:border-emerald-500 py-2.5 @error('password_confirmation', 'updatePassword') border-rose-400 bg-rose-50 @enderror">
                    @error('password_confirmation', 'updatePassword')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-xl hover:bg-amber-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Actualizar contraseña
                    </button>
                </div>
            </form>
        </div>

        {{-- Danger zone --}}
        <div class="bg-white rounded-2xl border border-rose-200/60 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-rose-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-rose-700">Zona de Peligro</h3>
            </div>
            <p class="text-sm text-slate-500 mb-5">Una vez eliminada tu cuenta, todos los datos serán borrados permanentemente. Esta acción no se puede deshacer.</p>

            <button
                onclick="document.getElementById('modal-delete').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-rose-300 text-rose-600 text-sm font-semibold rounded-xl hover:bg-rose-50 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Eliminar cuenta
            </button>
        </div>

    </div>

    {{-- Delete confirmation modal --}}
    <div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">¿Eliminar tu cuenta?</h3>
                    <p class="text-sm text-slate-500">Esta acción es irreversible.</p>
                </div>
            </div>

            <p class="text-sm text-slate-600 mb-5">Ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta y todos sus datos.</p>

            <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                    <input type="password" name="password" id="delete_password" autocomplete="current-password" placeholder="Tu contraseña actual"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:ring-rose-500/20 focus:border-rose-500 py-2.5 @error('password', 'userDeletion') border-rose-400 bg-rose-50 @enderror">
                    @error('password', 'userDeletion')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-delete').classList.add('hidden')"
                        class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 text-white text-sm font-semibold rounded-xl hover:bg-rose-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Sí, eliminar cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($errors->userDeletion->isNotEmpty())
    <script>document.getElementById('modal-delete').classList.remove('hidden');</script>
    @endif

</x-app-layout>
