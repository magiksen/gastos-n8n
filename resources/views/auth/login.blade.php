<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Bienvenido de vuelta</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Ingresa tus credenciales para continuar</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-ui.field label="Correo electrónico" for="email" :error="$errors->first('email')">
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="tu@email.com" :invalid="$errors->has('email')" />
        </x-ui.field>

        <x-ui.field label="Contraseña" for="password" :error="$errors->first('password')">
            <x-ui.input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" :invalid="$errors->has('password')" />
        </x-ui.field>

        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800">
            <span class="text-sm text-slate-600 dark:text-slate-400">Recordarme</span>
        </label>

        <x-ui.button class="w-full">Entrar</x-ui.button>
    </form>
</x-guest-layout>
