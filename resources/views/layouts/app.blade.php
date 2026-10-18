<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['titulo' => $title ? $title.' · Gastos' : null])
    </head>
    <body class="bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-slate-200/70 bg-white/80 backdrop-blur-xl lg:flex dark:border-slate-800 dark:bg-slate-900/80" data-sidebar>
            <div class="flex h-20 items-center justify-between px-6">
                <a href="{{ route('gastos.index') }}"><x-logo /></a>
                <x-theme-toggle />
            </div>

            <nav class="flex-1 space-y-1 px-4 py-4">
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Menú</p>
                @foreach ($navegacion() as $item)
                    @php($activo = request()->routeIs($item['activa']))
                    <a href="{{ route($item['ruta']) }}" @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
                        'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25' => $activo,
                        'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! $activo,
                    ]) @if ($activo) aria-current="page" @endif>
                        <x-icon :name="$item['icono']" @class(['h-5 w-5', 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' => ! $activo]) />
                        {{ $item['etiqueta'] }}
                    </a>
                @endforeach
            </nav>

            <div class="m-4 rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-200/70 dark:bg-slate-800/50 dark:ring-slate-700/50">
                <div class="flex items-center gap-3">
                    <x-ui.avatar :name="Auth::user()->name" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.icon-button type="submit" icon="salir" label="Cerrar sesión" tone="rose" />
                    </form>
                </div>
            </div>
        </aside>

        <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl lg:hidden dark:border-slate-800 dark:bg-slate-900/80">
            <div class="flex h-16 items-center justify-between px-4">
                <a href="{{ route('gastos.index') }}"><x-logo /></a>
                <x-theme-toggle />
            </div>
        </header>

        <div class="lg:pl-72">
            <main class="mx-auto w-full max-w-6xl px-4 pb-28 pt-6 sm:px-6 lg:px-10 lg:pb-12 lg:pt-10">
                {{ $slot }}
            </main>
        </div>

        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/70 bg-white/90 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl lg:hidden dark:border-slate-800 dark:bg-slate-900/90" data-bottom-nav>
            <div class="grid grid-cols-4">
                @foreach ($navegacion() as $item)
                    @php($activo = request()->routeIs($item['activa']))
                    <a href="{{ route($item['ruta']) }}" @class([
                        'flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold transition',
                        'text-indigo-600 dark:text-indigo-400' => $activo,
                        'text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-200' => ! $activo,
                    ]) @if ($activo) aria-current="page" @endif>
                        <span @class([
                            'flex h-8 w-14 items-center justify-center rounded-full transition',
                            'bg-indigo-100 dark:bg-indigo-500/15' => $activo,
                        ])>
                            <x-icon :name="$item['icono']" class="h-5 w-5" />
                        </span>
                        {{ $item['corta'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        <x-toasts />
    </body>
</html>
