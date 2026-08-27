<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DOCTA') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden bg-gradient-to-br from-teal-50 via-white to-blue-50">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-teal-200/40 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-200/40 rounded-full blur-3xl"></div>

            <div class="relative w-full px-4 sm:px-6">
                <div class="flex justify-center mb-6">
                    <a href="/" class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-teal-600 text-white shadow-lg shadow-teal-600/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        <span class="font-extrabold text-2xl tracking-tight text-gray-900">DOCTA</span>
                    </a>
                </div>

                <div class="w-full sm:max-w-lg mx-auto mt-2 px-6 py-8 bg-white/90 backdrop-blur shadow-xl shadow-gray-200/60 ring-1 ring-gray-100 overflow-hidden sm:rounded-2xl">
                    {{ $slot }}
                </div>

                <p class="relative text-center mt-6 text-xs text-gray-400">
                    &copy; {{ date('Y') }} DOCTA — ERP Médical
                </p>
            </div>
        </div>
    </body>
</html>
