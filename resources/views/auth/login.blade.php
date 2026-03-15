<x-guest-layout>
    <div class="w-full max-w-xl">
        <div class="rounded-3xl border border-white/70 bg-white/95 p-8 shadow-2xl shadow-blue-950/30 sm:p-10">
            <div class="text-center">
                <img src="{{ asset('aclclogo.png') }}" alt="ACLCC logo" class="mx-auto h-20 w-20">
                <h1 class="mt-5 text-2xl font-semibold text-slate-900 sm:text-3xl">Schedule Portal</h1>
                <p class="mt-2 text-sm text-slate-500">Log in to manage academic schedules.</p>
            </div>

            <x-auth-session-status class="mt-6" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                @csrf

                <div class="space-y-2">
                    <x-input-label for="email" :value="__('Email')" class="text-slate-700" />
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M4 6h16v12H4z"/>
                                <path d="M22 6l-10 7L2 6"/>
                            </svg>
                        </span>
                        <x-text-input id="email" class="block w-full rounded-xl border border-slate-200 bg-white pl-11 text-slate-900 shadow-sm placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500/40" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Enter your email" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="password" :value="__('Password')" class="text-slate-700" />
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <rect x="4" y="10" width="16" height="10" rx="2"/>
                                <path d="M8 10V8a4 4 0 0 1 8 0v2"/>
                            </svg>
                        </span>
                        <x-text-input id="password" class="block w-full rounded-xl border border-slate-200 bg-white pl-11 pr-12 text-slate-900 shadow-sm placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500/40"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password" placeholder="Enter your password" />
                        <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700" aria-label="Toggle password visibility">
                            <svg id="eye-open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3.5"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label for="remember_me" class="inline-flex items-center text-slate-600">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                        <span class="ms-2">{{ __('Remember me') }}</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="font-medium text-blue-600 hover:text-blue-800" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <x-primary-button class="group flex w-full items-center justify-center gap-3 rounded-xl bg-blue-700 py-3 text-base font-semibold text-white shadow-lg shadow-blue-700/30 transition hover:-translate-y-0.5 hover:bg-blue-800">
                    <span>{{ __('Log in') }}</span>
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-white/80 transition group-hover:scale-110"></span>
                </x-primary-button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-blue-100/80">
            ACLCC College of Taytay • Academic Scheduling System
        </p>
    </div>

    <style>
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('toggle-password');

        if (passwordInput && togglePassword) {
            togglePassword.addEventListener('click', () => {
                passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            });
        }
    </script>
</x-guest-layout>
