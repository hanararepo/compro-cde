<x-layouts.auth title="Sign In">
    @php
        $siteName    = $settings['site_name']    ?? config('app.name', 'CMS');
        $siteLogo    = $settings['site_logo']    ?? '';
        $siteTagline = trim($settings['site_tagline'] ?? '');
    @endphp

    <div class="w-full max-w-md">
        {{-- Logo & Header --}}
        <div class="text-center mb-8">
            @if($siteLogo)
                <a href="{{ route('home') }}" class="inline-block mb-4">
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-14 mx-auto object-contain">
                </a>
            @else
                <div class="w-14 h-14 rounded-2xl bg-brand-600 flex items-center justify-center text-white font-bold mx-auto shadow-lg shadow-brand-600/30 mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            @endif
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $siteName }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $siteTagline ?: 'Sign in to your administration panel' }}
            </p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-xl shadow-slate-200/60 relative overflow-hidden">
            {{-- Top Indeterminate Progress Bar on Submit --}}
            <div x-show="submitting" x-cloak class="absolute top-0 left-0 right-0 h-1 bg-brand-100/60 overflow-hidden">
                <div class="h-full bg-brand-600 w-1/3 rounded-full animate-progress-bar"></div>
            </div>

            {{-- Flash Errors --}}
            <x-alert />

            <form method="POST" action="{{ route('login') }}" class="space-y-5" id="login-form"
                  x-data="{ submitting: false }"
                  @submit="if ($el.checkValidity()) { submitting = true; }">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                        Email Address
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="admin@example.com"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                        Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all"
                    >
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                               class="rounded border-slate-300 bg-white text-brand-600 focus:ring-brand-500">
                        <span class="text-xs text-slate-600 font-medium">Remember me</span>
                    </label>
                </div>

                {{-- reCAPTCHA v3 Token --}}
                @if(config('recaptcha.site_key'))
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                    @error('recaptcha_token')
                        <p class="text-xs text-rose-500 text-center mt-1">{{ $message }}</p>
                    @enderror
                @endif

                {{-- Submit --}}
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-85 cursor-wait bg-brand-700' : 'hover:bg-brand-700 active:scale-[0.99]'"
                        class="w-full py-3 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-sm shadow-brand-600/30 transition-all flex items-center justify-center gap-2">
                    <svg x-show="submitting" x-cloak class="animate-spin w-4 h-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!submitting">Sign In to Dashboard</span>
                    <span x-show="submitting" x-cloak>Signing In to Dashboard...</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-brand-600 transition-colors">
                    ← Back to public website
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes progressBarAnim {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(120%); }
            100% { transform: translateX(350%); }
        }
        .animate-progress-bar {
            animation: progressBarAnim 1.2s ease-in-out infinite;
        }
    </style>

    {{-- reCAPTCHA v3 Script --}}
    @if(config('recaptcha.site_key'))
        @push('scripts')
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}" defer></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('login-form');
                    const siteKey = '{{ config('recaptcha.site_key') }}';
                    if (!siteKey || !form) { return; }
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        grecaptcha.ready(function () {
                            grecaptcha.execute(siteKey, { action: 'login' }).then(function (token) {
                                document.getElementById('recaptcha_token').value = token;
                                form.submit();
                            }).catch(function (err) {
                                console.error('reCAPTCHA error:', err);
                                if (window.Alpine) {
                                    const alpineData = Alpine.$data(form);
                                    if (alpineData) { alpineData.submitting = false; }
                                }
                            });
                        });
                    });
                });
            </script>
        @endpush
    @endif
</x-layouts.auth>
