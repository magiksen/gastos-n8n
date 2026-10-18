@props(['invalid' => false])

<input {{ $attributes->class([
    'block w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 ring-1 ring-inset placeholder:text-slate-400 transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:bg-slate-800/60 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-800 dark:focus:ring-indigo-400',
    'ring-slate-200 dark:ring-slate-700' => ! $invalid,
    'ring-rose-400 dark:ring-rose-500/60' => $invalid,
]) }}>
