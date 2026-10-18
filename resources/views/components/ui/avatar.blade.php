@props(['name', 'size' => 'md'])

@php
    $tamanos = [
        'sm' => 'h-8 w-8 rounded-lg text-[11px]',
        'md' => 'h-10 w-10 rounded-xl text-xs',
        'lg' => 'h-12 w-12 rounded-2xl text-sm',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center bg-gradient-to-br font-bold text-white shadow-sm '.\App\Support\Paleta::degradadoPara($name).' '.$tamanos[$size]]) }}>
    {{ \App\Support\Paleta::iniciales($name) }}
</div>
