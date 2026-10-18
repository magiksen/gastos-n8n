<div class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2" aria-live="polite">
    @if(session('success'))
        <x-toast tipo="success" autocerrar="4000">{{ session('success') }}</x-toast>
    @endif

    @if(session('warning'))
        <x-toast tipo="warning" autocerrar="8000">{{ session('warning') }}</x-toast>
    @endif

    @if($errors->any())
        <x-toast tipo="error">
            <ul class="space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-toast>
    @endif
</div>
