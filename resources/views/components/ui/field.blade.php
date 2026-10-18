@props(['label', 'for' => null, 'error' => null, 'hint' => null])

<div {{ $attributes }}>
    <label @if ($for) for="{{ $for }}" @endif class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</label>
    {{ $slot }}
    @if ($error)
        <p class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">{{ $hint }}</p>
    @endif
</div>
