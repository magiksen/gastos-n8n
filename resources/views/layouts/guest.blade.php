<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gastos') }} — Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex">
            {{-- Left panel - branding --}}
            <div class="hidden lg:flex lg:w-1/2 bg-slate-900 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/20 via-transparent to-slate-900"></div>
                <div class="absolute top-0 left-0 w-96 h-96 bg-emerald-500/10 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full translate-x-1/2 translate-y-1/2 blur-3xl"></div>

                <div class="relative z-10 flex flex-col justify-center px-16">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-white font-bold text-2xl tracking-tight">Gastos</span>
                    </div>
                    <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                        Control total de<br>tus gastos mensuales
                    </h1>
                    <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                        Gestiona tus pagos, sube comprobantes y recibe notificaciones automaticas de vencimientos.
                    </p>
                </div>
            </div>

            {{-- Right panel - form --}}
            <div class="flex-1 flex flex-col justify-center items-center px-6 py-12 bg-white">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-slate-900 font-bold text-2xl tracking-tight">Gastos</span>
                </div>

                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
