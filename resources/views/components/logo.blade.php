@props(['size' => 'md'])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <div @class([
        'flex items-center justify-center bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-500/30',
        'h-9 w-9 rounded-xl' => $size === 'md',
        'h-11 w-11 rounded-2xl' => $size === 'lg',
    ])>
        <x-icon name="billetera" @class(['h-5 w-5' => $size === 'md', 'h-6 w-6' => $size === 'lg']) />
    </div>
    <div class="leading-tight">
        <p @class(['font-bold tracking-tight text-slate-900 dark:text-white', 'text-base' => $size === 'md', 'text-xl' => $size === 'lg'])>Gastos</p>
        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Control mensual</p>
    </div>
</div>
