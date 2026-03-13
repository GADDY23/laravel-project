<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-white">Login</h2>
        <p class="mt-2 text-sm text-slate-200/90">Enter your credentials to access your dashboard.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="text-slate-200" />
            <x-text-input id="email" class="block mt-1 w-full text-[#1f2f4d]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5 space-y-2">
            <x-input-label for="password" :value="__('Password')" class="text-slate-200" />

            <x-text-input id="password" class="block mt-1 w-full text-[#1f2f4d]"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-5 flex items-center justify-between text-slate-200">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-blue-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-sky-200 hover:text-white" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-2.5 text-base bg-sky-200 text-[#1f2f4d] hover:bg-sky-100 border border-sky-100 shadow-md hover:shadow-lg uppercase tracking-[0.22em]">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
