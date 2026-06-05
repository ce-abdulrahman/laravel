{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ku']) ? 'rtl' : 'ltr' }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('auth.login') }} - {{ config('app.name', 'My Quran') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Cairo:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Fallback standard CDN setup for Tailwind CSS -->
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    darkMode: 'class',
                }
            </script>
        @endif

        <!-- Inline scripts for instant dark mode handling to prevent flash of light theme -->
        <script>
            const savedTheme = localStorage.getItem('theme') || localStorage.getItem('quran-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        </script>

        @php
            $settings = \Illuminate\Support\Facades\Cache::remember('app_settings', 3600, function () {
                return \App\Models\Setting::firstOrCreate([]);
            });
            
            $fontAr = $settings->font_ar ?? 'Wafeq-Regular.otf';
            $fontKu = $settings->font_ku ?? '3_NRT-Bd.ttf';
            $fontEn = $settings->font_en ?? 'PatuaOne-Regular.ttf';

            $fontArExt = pathinfo($fontAr, PATHINFO_EXTENSION);
            $fontKuExt = pathinfo($fontKu, PATHINFO_EXTENSION);
            $fontEnExt = pathinfo($fontEn, PATHINFO_EXTENSION);
            
            $formatAr = strtolower($fontArExt) === 'otf' ? 'opentype' : 'truetype';
            $formatKu = strtolower($fontKuExt) === 'otf' ? 'opentype' : 'truetype';
            $formatEn = strtolower($fontEnExt) === 'otf' ? 'opentype' : 'truetype';
        @endphp

        <style>
            /* Define Custom Webfonts */
            @font-face {
                font-family: 'CustomArFont';
                src: url('{{ asset("fonts/ar/" . $fontAr) }}') format('{{ $formatAr }}');
                font-display: swap;
            }
            @font-face {
                font-family: 'CustomKuFont';
                src: url('{{ asset("fonts/ku/" . $fontKu) }}') format('{{ $formatKu }}');
                font-display: swap;
            }
            @font-face {
                font-family: 'CustomEnFont';
                src: url('{{ asset("fonts/en/" . $fontEn) }}') format('{{ $formatEn }}');
                font-display: swap;
            }

            body {
                font-family: {!! app()->getLocale() == 'ku' ? '"CustomKuFont", "Noto Naskh Arabic", sans-serif' : '"CustomEnFont", "Outfit", sans-serif' !!};
            }
            .arabic-text {
                font-family: 'CustomArFont', 'Amiri', serif !important;
            }
            .hero-pattern {
                background-image: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.08) 0%, transparent 60%);
            }
            .dark .hero-pattern {
                background-image: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 60%);
            }
        </style>
    </head>
    <body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white transition-colors duration-300">
        
        <!-- Premium Navbar -->
        <header class="sticky top-0 z-50 w-full border-b border-zinc-200/80 dark:border-zinc-900/80 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                
                <!-- Logo & Branding -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 dark:bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-300">
                            <!-- Islamic Crescent / Star SVG Logo -->
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.58 1.477 4.5 2.5m0-16.247C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-extrabold tracking-tight text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                My Quran
                            </span>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 -mt-1 font-semibold tracking-wider uppercase">
                                قورئانی پیرۆز
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Navigation & Settings Actions -->
                <div class="flex items-center gap-3">
                    
                    <!-- Language Switcher Dropdown -->
                    <div class="relative">
                        <button id="lang-switch-btn" type="button" class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 focus:outline-none rounded-xl px-2.5 py-2 text-xs font-bold transition-all hover:scale-105">
                            <i class="bi bi-globe text-sm"></i>
                            <span>{{ strtoupper(app()->getLocale()) }}</span>
                            <i class="bi bi-chevron-down text-[9px] text-zinc-400"></i>
                        </button>
                        <div id="lang-dropdown-menu" class="hidden absolute top-full mt-1.5 {{ in_array(app()->getLocale(), ['ar', 'ku']) ? 'left-0' : 'right-0' }} w-32 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl py-1 z-50">
                            <a class="flex items-center justify-between py-2.5 px-4 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ app()->getLocale() == 'ku' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/10' : '' }}" href="{{ route('language.switch', 'ku') }}">
                                <span>کوردی</span>
                                @if(app()->getLocale() == 'ku') <i class="bi bi-check text-base"></i> @endif
                            </a>
                            <a class="flex items-center justify-between py-2.5 px-4 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ app()->getLocale() == 'ar' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/10' : '' }}" href="{{ route('language.switch', 'ar') }}">
                                <span>العربية</span>
                                @if(app()->getLocale() == 'ar') <i class="bi bi-check text-base"></i> @endif
                            </a>
                            <a class="flex items-center justify-between py-2.5 px-4 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ app()->getLocale() == 'en' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/10' : '' }}" href="{{ route('language.switch', 'en') }}">
                                <span>English</span>
                                @if(app()->getLocale() == 'en') <i class="bi bi-check text-base"></i> @endif
                            </a>
                        </div>
                    </div>

                    <!-- Theme Toggle Switch -->
                    <button id="theme-toggle" type="button" class="text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 focus:outline-none rounded-xl p-2 text-sm transition-all hover:scale-105" aria-label="Toggle dark mode">
                        <!-- Dark Icon -->
                        <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <!-- Light Icon -->
                        <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464a1 1 0 10-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Auth State Buttons -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                                    {{ __('Dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-3.5 py-1.5 rounded-xl transition-colors">
                                    {{ __('Log in') }}
                                </a>
                            @endauth
                        </div>
                    @endif
                </div>

            </div>
        </header>

        <!-- Main centered layout -->
        <main class="flex-grow hero-pattern flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-xl relative overflow-hidden">
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 dark:bg-emerald-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/20 text-white">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.58 1.477 4.5 2.5m0-16.247C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-zinc-900 dark:text-white mb-1">
                        {{ __('auth.welcome_back') }}
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold uppercase tracking-wider">
                        {{ __('auth.login_to_continue') }}
                    </p>
                </div>

                <!-- Session Status -->
                @if(session('status'))
                    <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 rounded-xl p-4 mb-6 text-sm text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300" for="email">
                            {{ __('auth.email') }}
                        </label>
                        <div class="relative rounded-2xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </div>
                            <input type="email" name="email" id="email" 
                                   class="block w-full pl-11 pr-4 py-3 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-800 rounded-2xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:ring-emerald-400/20 dark:focus:border-emerald-400 transition-all outline-none @error('email') border-red-500 @enderror"
                                   value="{{ old('email') }}" 
                                   placeholder="{{ __('auth.email_placeholder') }}"
                                   required autofocus>
                        </div>
                        @error('email')
                            <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300" for="password">
                                {{ __('auth.password') }}
                            </label>
                        </div>
                        <div class="relative rounded-2xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" 
                                   class="block w-full pl-11 pr-12 py-3 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-800 rounded-2xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:ring-emerald-400/20 dark:focus:border-emerald-400 transition-all outline-none @error('password') border-red-500 @enderror"
                                   placeholder="{{ __('auth.password_placeholder') }}"
                                   required>
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" onclick="togglePassword('password')">
                                <!-- Eye Icon -->
                                <svg id="eye-icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3.5 text-sm font-bold rounded-2xl text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 active:scale-95 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        {{ __('auth.login') }}
                    </button>
                </form>

                <!-- Back to Main Page Link -->
                <div class="mt-6 text-center border-t border-zinc-100 dark:border-zinc-800/60 pt-4">
                    <a href="{{ url('/') }}" class="text-xs font-semibold text-zinc-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>گەڕانەوە بۆ لاپەڕەی سەرەکی</span>
                    </a>
                </div>
            </div>
        </main>

        <!-- Modern Premium Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-900 bg-white dark:bg-zinc-950 transition-colors duration-300 py-10 text-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-col items-center md:items-start">
                    <span class="font-bold text-sm text-zinc-700 dark:text-zinc-300">
                        My Quran App &copy; {{ date('Y') }}
                    </span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                        خزمەتگوزارییەکی بێبەرامبەرە بۆ بڵاوکردنەوەی پەیامی قورئانی پیرۆز
                    </span>
                </div>
                <div class="flex items-center gap-6 text-sm font-semibold text-zinc-500 dark:text-zinc-400">
                    <a href="{{ url('/') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">سەرەکی</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">داشبۆرد</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">چوونەژوورەوە</a>
                    @endauth
                    <a href="https://quran.com" target="_blank" rel="noopener" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Quran.com</a>
                </div>
            </div>
        </footer>

        <!-- Javascript Functions -->
        <script>
            // Toggle Password Visibility
            function togglePassword(fieldId) {
                const field = document.getElementById(fieldId);
                const btn = field.parentElement.querySelector('button');
                
                if (field.type === 'password') {
                    field.type = 'text';
                    btn.innerHTML = `
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    `;
                } else {
                    field.type = 'password';
                    btn.innerHTML = `
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    `;
            }
        }

        // Theme Toggle logic for Login Page
            document.addEventListener('DOMContentLoaded', () => {
                const themeToggleBtn = document.getElementById('theme-toggle');
                const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
                const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

                // Initial setup
                const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                    themeToggleLightIcon.classList.remove('hidden');
                    themeToggleDarkIcon.classList.add('hidden');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-theme', 'light');
                    themeToggleDarkIcon.classList.remove('hidden');
                    themeToggleLightIcon.classList.add('hidden');
                }

                themeToggleBtn.addEventListener('click', () => {
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.setAttribute('data-theme', 'light');
                        localStorage.setItem('theme', 'light');
                        localStorage.setItem('quran-theme', 'light');
                        themeToggleDarkIcon.classList.remove('hidden');
                        themeToggleLightIcon.classList.add('hidden');
                    } else {
                        document.documentElement.classList.add('dark');
                        document.documentElement.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        localStorage.setItem('quran-theme', 'dark');
                        themeToggleLightIcon.classList.remove('hidden');
                        themeToggleDarkIcon.classList.add('hidden');
                    }
                });

                // Toggle Language Dropdown
                const langBtn = document.getElementById('lang-switch-btn');
                const langMenu = document.getElementById('lang-dropdown-menu');
                if (langBtn && langMenu) {
                    langBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        langMenu.classList.toggle('hidden');
                    });
                    document.addEventListener('click', () => {
                        langMenu.classList.add('hidden');
                    });
                }
            });
        </script>
    </body>
</html>