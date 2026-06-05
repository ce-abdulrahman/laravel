<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} - {{ config('app.name', 'My Quran') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Cairo:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Fallback CDN setup for Tailwind CSS v4 in case compile assets are missing -->
            <script src="https://cdn.tailwindcss.com"></script>
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
        </style>
    </head>
    <body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white transition-colors duration-300">

        <!-- Header Navigation -->
        <header class="sticky top-0 z-50 w-full border-b border-zinc-200/80 dark:border-zinc-900/80 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                
                <!-- Back to Home -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-600 dark:text-zinc-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>{{ __('سەرەکی') }}</span>
                    </a>
                </div>

                <!-- Surah / Juz Selector or Title Info -->
                <div class="flex flex-col items-center text-center">
                    <span class="text-sm font-bold text-zinc-900 dark:text-white">
                        {{ $title }}
                    </span>
                    @if($subtitle)
                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400 -mt-0.5 font-bold">
                            {{ $subtitle }}
                        </span>
                    @endif
                </div>

                <!-- Right Settings -->
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
                        <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464a1 1 0 10-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Profile link if logged in -->
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-bold shadow hover:bg-emerald-500 transition-all">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </a>
                    @endauth
                </div>

            </div>
        </header>

        <!-- Main Content Workspace -->
        <main class="flex-grow max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">

            <!-- Mode: Surah Header Card -->
            @if($mode === 'surah')
                <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 text-center shadow-sm mb-10 overflow-hidden">
                    <div class="absolute -right-16 -top-16 w-36 h-36 bg-emerald-500/5 rounded-full blur-2xl"></div>
                    <div class="absolute -left-16 -bottom-16 w-36 h-36 bg-teal-500/5 rounded-full blur-2xl"></div>
                    
                    <h2 class="arabic-text text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500 dark:from-emerald-400 dark:to-teal-300 mb-2 select-none">
                        {{ $surah->name_ar }}
                    </h2>
                    <h3 class="text-xl font-extrabold text-zinc-800 dark:text-zinc-100">
                        {{ $surah->name_en }}
                    </h3>
                    @if($surah->name_ku)
                        <p class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ $surah->name_ku }}
                        </p>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs md:text-sm font-semibold text-zinc-500 dark:text-zinc-400 border-t border-zinc-100 dark:border-zinc-800/60 pt-6">
                        <span>{{ ucfirst($surah->revelation_type) }}</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                        <span>{{ $surah->ayah_count }} {{ __('ayahs.ayahs') }}</span>
                        @if($surah->page_start)
                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                            <span>{{ __('پەڕەی') }} {{ $surah->page_start }} - {{ $surah->page_end }}</span>
                        @endif
                    </div>
                </div>

                <!-- Bismillah display (except Surah 9 (Tawbah) and Surah 1) -->
                @if($surah->number !== 9 && $surah->number !== 1)
                    <div class="arabic-text text-center text-2xl md:text-3xl my-10 text-zinc-900 dark:text-white select-none tracking-wide">
                        بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                    </div>
                @endif
            @endif

            <!-- Mode: Juz Header Card -->
            @if($mode === 'juz')
                <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 text-center shadow-sm mb-10 overflow-hidden">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-2">
                        Juz {{ $juz_number }}
                    </h2>
                    <p class="text-lg font-bold text-zinc-600 dark:text-zinc-300">
                        جوزئی {{ $juz_number }}
                    </p>
                </div>
            @endif

            <!-- Mode: Page Header Card -->
            @if($mode === 'page')
                <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 text-center shadow-sm mb-10 overflow-hidden">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-2">
                        Page {{ $page_number }}
                    </h2>
                    <p class="text-lg font-bold text-zinc-600 dark:text-zinc-300">
                        لاپەڕەی {{ $page_number }}
                    </p>
                </div>
            @endif

            <!-- Ayahs List rendering -->
            <div class="space-y-6">
                @php
                    $currentSurahId = null;
                @endphp
                @forelse($ayahs as $ayah)
                    
                    <!-- For Juz/Page Mode: Display Surah Banner whenever Surah changes -->
                    @if(($mode === 'juz' || $mode === 'page') && $currentSurahId !== $ayah->surah_id)
                        @php
                            $currentSurahId = $ayah->surah_id;
                        @endphp
                        <div class="bg-gradient-to-r from-emerald-600/5 to-teal-600/5 dark:from-emerald-950/20 dark:to-teal-950/20 border border-emerald-500/10 rounded-2xl p-5 text-center mt-12 mb-6 shadow-sm">
                            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 tracking-widest uppercase">Surah</span>
                            <h4 class="arabic-text text-2xl font-bold text-zinc-900 dark:text-white mt-1">
                                {{ $ayah->surah->name_ar }} ({{ $ayah->surah->name_en }})
                            </h4>
                        </div>
                        @if($ayah->surah->number !== 9 && $ayah->surah->number !== 1 && $ayah->ayah_number == 1)
                            <div class="arabic-text text-center text-2xl my-8 text-zinc-900 dark:text-white select-none">
                                بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                            </div>
                        @endif
                    @endif

                    <!-- Individual Ayah Card -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 rounded-2xl p-6 md:p-8 shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col gap-6">
                        
                        <!-- Top Metadata / Reference & Actions -->
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800/60 pb-4">
                            <div class="flex items-center gap-2">
                                <!-- Islamic Star Badge for Ayah Number -->
                                <div class="relative flex items-center justify-center w-9 h-9 select-none flex-shrink-0">
                                    <svg class="absolute w-full h-full text-emerald-50 dark:text-emerald-950/40 fill-current stroke-emerald-600/50 dark:stroke-emerald-400/40 stroke-1" viewBox="0 0 24 24">
                                        <path d="M12 2L15 5H19V9L22 12L19 15V19H15L12 22L9 19H5V15L2 12L5 9V5H9L12 2Z" />
                                    </svg>
                                    <span class="relative z-10 text-[10px] md:text-xs font-extrabold text-emerald-800 dark:text-emerald-400">
                                        {{ $ayah->ayah_number }}
                                    </span>
                                </div>
                                <span class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400 font-bold">
                                    {{ $ayah->surah->name_en }}:{{ $ayah->ayah_number }}
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3">
                                <button type="button" class="copy-ayah-btn p-2 text-zinc-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors" data-text="{{ $ayah->text_uthmani }}" title="Copy Arabic Text">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Ayah Arabic Text -->
                        <div class="arabic-text text-3xl md:text-4xl text-right text-zinc-900 dark:text-white leading-[2.5] tracking-wide select-none">
                            {{ $ayah->text_uthmani }}
                        </div>

                        <!-- Ayah Translations -->
                        <div class="space-y-4 border-t border-zinc-100 dark:border-zinc-800/60 pt-4">
                            @forelse($ayah->translations as $translation)
                                <div class="flex flex-col gap-1 {{ $translation->language_code === 'ku' ? 'text-right' : 'text-left' }}" dir="{{ $translation->language_code === 'ku' ? 'rtl' : 'ltr' }}">
                                    <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500">
                                        {{ $translation->language_code === 'ku' ? 'وەرگێڕان (کوردی)' : 'Translation (English)' }} - {{ $translation->translator_name }}
                                    </span>
                                    <p class="text-sm md:text-base text-zinc-700 dark:text-zinc-300 font-medium leading-relaxed">
                                        {{ $translation->content }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 italic">
                                    {{ __('ھیچ وەرگێڕانێک نییە بۆ ئەم ئایەتە') }} (No translations available)
                                </p>
                            @endforelse
                        </div>

                    </div>
                @empty
                    <div class="text-center py-20 bg-white dark:bg-zinc-900 border border-dashed border-zinc-300 dark:border-zinc-800 rounded-2xl">
                        <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-bold text-zinc-900 dark:text-white">{{ __('هیچ ئایەتێک نەدۆزرایەوە') }}</h3>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('ئایەتەکان هێشتا هاوردە نەکراون.') }}</p>
                    </div>
                @endforelse
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
            document.addEventListener('DOMContentLoaded', () => {
                // Theme Toggle Implementation
                const themeToggleBtn = document.getElementById('theme-toggle');
                const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
                const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

                // Set icon based on initial theme state
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

                // Copy Ayah text to clipboard
                const copyButtons = document.querySelectorAll('.copy-ayah-btn');
                copyButtons.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const text = btn.getAttribute('data-text');
                        navigator.clipboard.writeText(text).then(() => {
                            // Temporary Success Feedback (change icon briefly)
                            const originalSvg = btn.innerHTML;
                            btn.innerHTML = `
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            `;
                            setTimeout(() => {
                                btn.innerHTML = originalSvg;
                            }, 2000);
                        }).catch(err => {
                            console.error('Could not copy text: ', err);
                        });
                    });
                });
            });
        </script>
    </body>
</html>
