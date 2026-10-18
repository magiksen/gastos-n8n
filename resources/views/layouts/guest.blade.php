<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['titulo' => config('app.name', 'Gastos').' · Acceso'])
    </head>
    <body class="bg-white font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="flex min-h-screen">
            <div class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 lg:flex lg:w-1/2">
                <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 right-0 h-[28rem] w-[28rem] rounded-full bg-fuchsia-400/30 blur-3xl"></div>

                <div class="relative z-10 flex flex-col justify-center px-16 text-white">
                    <div class="mb-10 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                            <x-icon name="billetera" class="h-6 w-6" />
                        </div>
                        <span class="text-2xl font-bold tracking-tight">Gastos</span>
                    </div>
                    <h1 class="text-4xl font-extrabold leading-tight tracking-tight">Tus pagos del mes,<br>siempre bajo control.</h1>
                    <p class="mt-4 max-w-md text-lg text-white/75">Sabe cuánto te falta, qué vence pronto y cuánto es en bolívares a la tasa BCV del día.</p>

                    <div class="mt-12 w-80 rotate-[-4deg] rounded-3xl bg-white/15 p-5 shadow-2xl ring-1 ring-white/20 backdrop-blur-md">
                        <p class="text-xs font-medium text-white/70">Pendiente por pagar</p>
                        <p class="mt-1 text-3xl font-extrabold">$128.50</p>
                        <div class="mt-4 h-1.5 rounded-full bg-white/20"><div class="h-full w-3/4 rounded-full bg-white"></div></div>
                        <p class="mt-2 text-[11px] text-white/70">9 de 12 pagados · 75%</p>
                    </div>
                </div>
            </div>

            <div class="relative flex flex-1 flex-col items-center justify-center px-6 py-12">
                <x-theme-toggle class="absolute right-4 top-4" />

                <x-logo size="lg" class="mb-10 lg:hidden" />

                <div class="w-full max-w-sm">
                    <x-flash-warning class="mb-6" />
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
