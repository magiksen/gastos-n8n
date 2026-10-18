@props(['icon', 'label', 'tone' => 'slate', 'href' => null])

@php
    $tonos = [
        'slate' => 'hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200',
        'indigo' => 'hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400',
        'amber' => 'hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-500/10 dark:hover:text-amber-400',
        'emerald' => 'hover:bg-emerald-50 hover:text-emerald-600 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400',
        'rose' => 'hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400',
    ];
    $clases = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-slate-500 '.$tonos[$tone];
@endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $clases]) }}>
        <x-icon :name="$icon" class="h-[18px] w-[18px]" />
    </a>
@else
    <button title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['type' => 'button', 'class' => $clases]) }}>
        <x-icon :name="$icon" class="h-[18px] w-[18px]" />
    </button>
@endif
