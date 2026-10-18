@php
    $mensajesDeEstado = [
        'profile-updated' => 'Perfil actualizado correctamente.',
        'password-updated' => 'Contraseña actualizada correctamente.',
    ];
    $exito = session('success') ?? $mensajesDeEstado[session('status')] ?? null;
@endphp

<div class="pointer-events-none fixed bottom-24 right-4 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2 lg:bottom-6 lg:right-6" aria-live="polite">
    @if ($exito)
        <x-toast tipo="success" autocerrar="4000">{{ $exito }}</x-toast>
    @endif

    @if (session('warning'))
        <x-toast tipo="warning" autocerrar="8000">{{ session('warning') }}</x-toast>
    @endif

    @if ($errors->any())
        <x-toast tipo="error">
            <ul class="space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-toast>
    @endif
</div>
