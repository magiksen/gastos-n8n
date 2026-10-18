@php
    $erroresPassword = $errors->updatePassword;
    $erroresEliminacion = $errors->userDeletion;
@endphp

<x-app-layout title="Mi perfil">
    <x-ui.page-header title="Mi perfil" subtitle="Tu información, apariencia y seguridad" />

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 p-6 text-white shadow-xl shadow-violet-500/20 dark:shadow-none">
                <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold backdrop-blur">
                        {{ \App\Support\Paleta::iniciales($user->name) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-lg font-bold">{{ $user->name }}</p>
                        <p class="truncate text-sm text-white/75">{{ $user->email }}</p>
                    </div>
                </div>
            </section>

            <x-ui.card x-data data-appearance>
                <h2 class="font-semibold text-slate-900 dark:text-white">Apariencia</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Elige cómo se ve la aplicación en este dispositivo.</p>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach (['sistema' => ['Sistema', 'pantalla'], 'claro' => ['Claro', 'sol'], 'oscuro' => ['Oscuro', 'luna']] as $valor => [$etiqueta, $icono])
                        <button type="button" @click="$store.tema.elegir('{{ $valor }}')" data-theme-option="{{ $valor }}"
                            :class="$store.tema.preferencia === '{{ $valor }}'
                                ? 'bg-indigo-50 text-indigo-700 ring-2 ring-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400'
                                : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100 dark:bg-slate-800/60 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800'"
                            class="flex flex-col items-center gap-1.5 rounded-2xl px-2 py-3 text-xs font-semibold transition">
                            <x-icon :name="$icono" class="h-5 w-5" />
                            {{ $etiqueta }}
                        </button>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card class="lg:hidden">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button variant="secondary" icon="salir" class="w-full">Cerrar sesión</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-5 lg:col-span-2">
            <x-ui.card>
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><x-icon name="usuario" class="h-5 w-5" /></span>
                    <h2 class="font-semibold text-slate-900 dark:text-white">Información personal</h2>
                </div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Nombre" for="name" :error="$errors->first('name')">
                            <x-ui.input type="text" name="name" id="name" required autocomplete="name" :value="old('name', $user->name)" :invalid="$errors->has('name')" />
                        </x-ui.field>
                        <x-ui.field label="Correo electrónico" for="email" :error="$errors->first('email')">
                            <x-ui.input type="email" name="email" id="email" required autocomplete="username" :value="old('email', $user->email)" :invalid="$errors->has('email')" />
                        </x-ui.field>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <x-ui.button icon="check">Guardar perfil</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card>
                <div class="mb-2 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400"><x-icon name="candado" class="h-5 w-5" /></span>
                    <h2 class="font-semibold text-slate-900 dark:text-white">Cambiar contraseña</h2>
                </div>
                <p class="mb-5 text-sm text-slate-500 dark:text-slate-400">Al cambiarla se cerrarán las demás sesiones activas.</p>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-ui.field label="Contraseña actual" for="current_password" :error="$erroresPassword->first('current_password')">
                            <x-ui.input type="password" name="current_password" id="current_password" autocomplete="current-password" :invalid="$erroresPassword->has('current_password')" />
                        </x-ui.field>
                        <x-ui.field label="Nueva contraseña" for="password" :error="$erroresPassword->first('password')">
                            <x-ui.input type="password" name="password" id="password" autocomplete="new-password" :invalid="$erroresPassword->has('password')" />
                        </x-ui.field>
                        <x-ui.field label="Confirmar" for="password_confirmation" :error="$erroresPassword->first('password_confirmation')">
                            <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" />
                        </x-ui.field>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <x-ui.button icon="candado">Actualizar contraseña</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card class="!ring-rose-200 dark:!ring-rose-500/20" x-data="{ confirmando: {{ $erroresEliminacion->isNotEmpty() ? 'true' : 'false' }} }">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-rose-600 dark:text-rose-400">Eliminar cuenta</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Se borrarán todos tus datos de forma permanente.</p>
                    </div>
                    <x-ui.button type="button" variant="secondary" icon="papelera" class="!text-rose-600 dark:!text-rose-400" @click="confirmando = true">Eliminar cuenta</x-ui.button>
                </div>

                <div x-show="confirmando" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/60 p-4 backdrop-blur-sm sm:items-center" @keydown.escape.window="confirmando = false">
                    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 dark:ring-1 dark:ring-slate-800" @click.outside="confirmando = false">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400"><x-icon name="alerta" class="h-5 w-5" /></span>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">¿Eliminar tu cuenta?</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Esta acción es irreversible.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('profile.destroy') }}" class="mt-5">
                            @csrf
                            @method('DELETE')
                            <x-ui.field label="Contraseña" for="delete_password" :error="$erroresEliminacion->first('password')">
                                <x-ui.input type="password" name="password" id="delete_password" autocomplete="current-password" placeholder="Tu contraseña actual" :invalid="$erroresEliminacion->has('password')" />
                            </x-ui.field>
                            <div class="mt-5 flex justify-end gap-2">
                                <x-ui.button type="button" variant="ghost" @click="confirmando = false">Cancelar</x-ui.button>
                                <x-ui.button variant="danger" icon="papelera">Sí, eliminar</x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
