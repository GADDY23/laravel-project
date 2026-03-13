<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-900" style="font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;">
        <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-white via-[#eaf2ff] to-[#cfe2ff]">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#0b2458]/8 via-[#2c4f9d]/6 to-[#0b2458]/8"></div>
            <div class="pointer-events-none absolute -top-40 -right-40 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-blue-400/14 via-sky-300/14 to-cyan-200/14 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-40 h-[520px] w-[520px] rounded-full bg-gradient-to-tr from-indigo-300/14 via-blue-200/14 to-teal-200/14 blur-3xl"></div>

            <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-10">
                @php
                    $loginCardClass = request()->routeIs('login')
                        ? 'bg-[#1f2f4d]/95 text-slate-100 border-blue-900/40'
                        : 'bg-white/85 text-slate-900 border-slate-200';
                @endphp
                <div class="w-full max-w-xl overflow-hidden rounded-3xl border shadow-2xl backdrop-blur {{ $loginCardClass }}">
                    <div class="p-8 md:p-10">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
