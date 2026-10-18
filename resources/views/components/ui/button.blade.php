@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'icon' => null])

@php
    $variantes = [
        'primary' => 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-500 hover:to-violet-500',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700',
        'danger' => 'bg-rose-600 text-white shadow-lg shadow-rose-500/20 hover:bg-rose-500',
        'ghost' => 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
    ];
    $tamanos = [
        'sm' => 'gap-1.5 px-3 py-2 text-xs',
        'md' => 'gap-2 px-4 py-2.5 text-sm',
    ];
    $clases = 'inline-flex items-center justify-center rounded-xl font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:ring-offset-slate-950 '
        .$variantes[$variant].' '.$tamanos[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $clases]) }}>
        @if ($icon)<x-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $clases]) }}>
        @if ($icon)<x-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </button>
@endif
