<button
    type="button"
    x-data
    @click="$store.tema.alternar()"
    title="Cambiar tema"
    aria-label="Cambiar tema"
    data-theme-toggle
    {{ $attributes->merge(['class' => 'inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white']) }}
>
    <x-icon name="luna" class="h-5 w-5 dark:hidden" />
    <x-icon name="sol" class="hidden h-5 w-5 dark:block" />
</button>
