@if (session('warning'))
    <div {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20']) }} role="alert">
        <x-icon name="alerta" class="h-5 w-5 shrink-0 text-amber-500" />
        {{ session('warning') }}
    </div>
@endif
