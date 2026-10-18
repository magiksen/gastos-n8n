@props(['invalid' => false])

<select {{ $attributes->class([
    'block w-full rounded-xl border-0 bg-slate-50 py-2.5 pl-3.5 pr-10 text-sm text-slate-900 ring-1 ring-inset transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800 dark:focus:ring-indigo-400',
    'ring-slate-200 dark:ring-slate-700' => ! $invalid,
    'ring-rose-400 dark:ring-rose-500/60' => $invalid,
]) }}>
    {{ $slot }}
</select>
