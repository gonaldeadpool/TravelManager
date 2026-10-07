<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('logo-europolo.png') }}">
        <link rel="shortcut icon" href="{{ asset('logo-europolo.png') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @if (! request()->routeIs('clienti', 'pratiche.*') && (session('emailSuccess') || session('emailError')))
                    <div class="mx-auto mt-6 max-w-6xl rounded border px-4 py-3 {{ session('emailError') ? 'border-red-300 bg-red-50 text-red-800' : 'border-green-300 bg-green-50 text-green-800' }}">{{ session('emailError') ?? session('emailSuccess') }}</div>
                @endif
                {{ $slot }}
            </main>
            @stack('dialogs')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('dialog[data-open-on-load]').forEach((dialog) => { if (!dialog.open) dialog.showModal(); });
                });
            </script>
        </div>
    </body>
</html>
