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
        <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-[#0b1f66] via-[#14328f] to-[#0b1f66]">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.18),transparent_55%)]"></div>
            <div class="pointer-events-none absolute -top-44 -right-44 h-[520px] w-[520px] rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-44 -left-44 h-[520px] w-[520px] rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-12">
                @if (request()->routeIs('login'))
                    {{ $slot }}
                @else
                    @php
                        $loginCardClass = 'bg-white/90 text-slate-900 border-white/70';
                    @endphp
                    <div class="w-full max-w-xl overflow-hidden rounded-3xl border shadow-2xl backdrop-blur {{ $loginCardClass }}">
                        <div class="p-8 md:p-10">
                            {{ $slot }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </body>
</html>
