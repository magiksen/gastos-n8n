<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="turbo-refresh-method" content="morph">
<meta name="turbo-refresh-scroll" content="preserve">
<meta name="theme-color" content="#4f46e5">

<title>{{ $titulo ?? config('app.name', 'Gastos') }}</title>

<script>
    (() => {
        const preferencia = localStorage.getItem('tema');
        const oscuro = preferencia === 'oscuro' || (preferencia !== 'claro' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', oscuro);
    })();
</script>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
