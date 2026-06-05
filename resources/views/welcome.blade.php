<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'My Quran') }} - {{ __('قورئانی پیرۆز') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <!-- Outfit for clean UI text, Cairo for headers, Amiri for gorgeous classical Arabic calligraphy/text -->
        <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Cairo:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Fallback standard CDN setup for Tailwind CSS v4 in case compile assets are missing -->
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
                            @php
                                $settings = \App\Models\Setting::first();
                            @endphp
                            @if($settings->app_logo)
                                <img src="{{ asset('storage/' . $settings->app_logo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                            @else
                                <i class="bi bi-book quran-logo-icon"></i>
                            @endif
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
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:text-emerald-600 dark:hover:text-emerald-400 px-3 py-2 rounded-lg transition-colors">
                                    {{ __('Log in') }}
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                                        {{ __('Register') }}
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>

            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-grow hero-pattern pb-20">
            
            <!-- Hero Header Section -->
            <div class="max-w-4xl mx-auto px-4 pt-16 pb-12 text-center flex flex-col items-center">
                
                <!-- Glowing Top Badge -->
                <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900 rounded-full px-4 py-1.5 mb-6 text-sm text-emerald-800 dark:text-emerald-300 font-medium">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    {{ __('قورئانی پیرۆز بە وەرگێڕانی کوردی') }}
                </div>

                <!-- Arabic Calligraphy Logo / Title -->
                <h1 class="arabic-text text-5xl md:text-7xl font-bold mb-5 tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500 dark:from-emerald-400 dark:to-teal-300 drop-shadow-sm select-none">
                    القرآن الكريم
                </h1>
                
                <!-- Kurdish Subtitle -->
                <p class="text-lg md:text-xl font-medium text-zinc-600 dark:text-zinc-300 max-w-xl mb-8 leading-relaxed">
                    خوێندنەوە، تەفسیر و بیستنی قورئانی پیرۆز بە شێوازێکی مۆدێرن و ئاسان
                </p>

                <!-- Search Container -->
                <div class="w-full max-w-2xl relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur opacity-25 group-hover:opacity-35 transition duration-1000 group-focus-within:opacity-40"></div>
                    <div class="relative flex items-center bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl overflow-hidden focus-within:border-emerald-500 dark:focus-within:border-emerald-400 transition-all">
                        
                        <!-- Search Icon -->
                        <div class="pl-5 pr-3 text-zinc-400 dark:text-zinc-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        
                        <!-- Search Input -->
                        <input id="search-input" type="text" placeholder="بگەڕێ بۆ سورەت، ژمارە، ناو یان وەرگێڕان... (کلیک لە / بکە)" class="w-full py-4.5 bg-transparent border-0 outline-none text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 font-medium text-sm md:text-base pr-4 text-right" dir="rtl">
                        
                        <!-- Clear Button -->
                        <button id="clear-search-btn" type="button" class="hidden px-3 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors" title="Clear search">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        
                        <!-- Keyboard shortcut badge -->
                        <div class="hidden md:flex items-center px-4 border-l border-zinc-200 dark:border-zinc-800 text-[10px] font-bold text-zinc-400 dark:text-zinc-500 select-none">
                            <kbd class="bg-zinc-100 dark:bg-zinc-800 px-1.5 py-1 rounded">/</kbd>
                        </div>
                    </div>
                </div>

                <!-- Popular Surahs Quick Links -->
                @php
                    $popularSurahNumbers = [1, 18, 36, 56, 67];
                    $popularSurahs = $surahs->whereIn('number', $popularSurahNumbers);
                @endphp
                @if($popularSurahs->count() > 0)
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2 text-xs md:text-sm">
                        @foreach($popularSurahs as $popSurah)
                            <a href="{{ route('read.surah', $popSurah->id) }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-3.5 py-1.5 rounded-full hover:border-emerald-500 dark:hover:border-emerald-500/50 hover:text-emerald-600 dark:hover:text-emerald-400 font-medium shadow-sm transition-all">
                                {{ $popSurah->name_ar }}
                            </a>
                        @endforeach
                        <span class="text-zinc-500 dark:text-zinc-400 font-semibold">{{ __(':پەیجە باوەکان') }}</span>
                    </div>
                @endif

            </div>

            <!-- Content Area Tabs / Lists -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Navigation Tabs Selection -->
                <div class="flex items-center justify-center border-b border-zinc-200 dark:border-zinc-800 mb-10 gap-8">
                    <button id="tab-surah" type="button" class="py-4.5 px-3 border-b-2 border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400 font-bold text-base md:text-lg flex items-center gap-2.5 transition-all outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.58 1.477 4.5 2.5m0-16.247C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        {{ __('سورەتەکان') }} ({{ $surahs->count() }})
                    </button>
                    <button id="tab-juz" type="button" class="py-4.5 px-3 border-b-2 border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 font-bold text-base md:text-lg flex items-center gap-2.5 transition-all outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        {{ __('جوزءەکان') }} (30)
                    </button>
                </div>

                <!-- Surahs Tab Grid Content -->
                <div id="grid-surah" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($surahs as $surah)
                        <a href="{{ route('read.surah', $surah->id) }}" 
                           class="surah-card group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between hover:-translate-y-1 hover:border-emerald-500/50 dark:hover:border-emerald-500/30"
                           data-search-term="{{ $surah->number }} {{ $surah->name_en }} {{ $surah->name_ar }} {{ $surah->name_ku }}">
                            
                            <!-- Left Content: Star Badge & Names -->
                            <div class="flex items-center gap-4">
                                <!-- Islamic Geometric Octagram Star Badge for Number -->
                                <div class="relative flex items-center justify-center w-11 h-11 select-none flex-shrink-0">
                                    <svg class="absolute w-full h-full text-emerald-50 dark:text-emerald-950/40 fill-current stroke-emerald-600/60 dark:stroke-emerald-400/40 stroke-1 group-hover:rotate-45 group-hover:scale-105 transition-all duration-500" viewBox="0 0 24 24">
                                        <path d="M12 2L15 5H19V9L22 12L19 15V19H15L12 22L9 19H5V15L2 12L5 9V5H9L12 2Z" />
                                    </svg>
                                    <span class="relative z-10 text-xs md:text-sm font-extrabold text-emerald-800 dark:text-emerald-300">
                                        {{ $surah->number }}
                                    </span>
                                </div>
                                
                                <!-- English & Kurdish Name / Ayah details -->
                                <div class="flex flex-col">
                                    <h3 class="font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ $surah->name_en }}
                                    </h3>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        {{ ucfirst($surah->revelation_type) }} • {{ $surah->ayah_count }} {{ __('ayahs.ayahs') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right Content: Arabic Name & Kurdish Meaning -->
                            <div class="text-right flex flex-col items-end">
                                <span class="arabic-text text-xl font-bold text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    {{ $surah->name_ar }}
                                </span>
                                @if($surah->name_ku)
                                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400 font-semibold mt-0.5 max-w-[130px] truncate" title="{{ $surah->name_ku }}">
                                        {{ $surah->name_ku }}
                                    </span>
                                @endif
                            </div>

                        </a>
                    @empty
                        <div class="col-span-full py-20 text-center bg-white dark:bg-zinc-900 border border-dashed border-zinc-300 dark:border-zinc-800 rounded-2xl">
                            <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-bold text-zinc-900 dark:text-white">{{ __('هیچ سورەتێک نەدۆزرایەوە') }}</h3>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('تکایە دڵنیابە لە سیودکردنی داتاکان لە داتابەیسدا.') }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Juz Tab Grid Content (Initially Hidden) -->
                <div id="grid-juz" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden">
                    @for ($i = 1; $i <= 30; $i++)
                        <a href="{{ route('read.juz', $i) }}" 
                           class="juz-card group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between hover:-translate-y-1 hover:border-emerald-500/50 dark:hover:border-emerald-500/30">
                            
                            <div class="flex items-center gap-4">
                                <!-- Octagram Badge for Juz number -->
                                <div class="relative flex items-center justify-center w-11 h-11 select-none flex-shrink-0">
                                    <svg class="absolute w-full h-full text-emerald-50 dark:text-emerald-950/40 fill-current stroke-emerald-600/60 dark:stroke-emerald-400/40 stroke-1 group-hover:rotate-45 group-hover:scale-105 transition-all duration-500" viewBox="0 0 24 24">
                                        <path d="M12 2L15 5H19V9L22 12L19 15V19H15L12 22L9 19H5V15L2 12L5 9V5H9L12 2Z" />
                                    </svg>
                                    <span class="relative z-10 text-xs md:text-sm font-extrabold text-emerald-800 dark:text-emerald-300">
                                        {{ $i }}
                                    </span>
                                </div>
                                
                                <div>
                                    <h3 class="font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ __('Juz') }} {{ $i }}
                                    </h3>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        جوزئی {{ $i }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-zinc-400 group-hover:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>

                        </a>
                    @endfor
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

                // Tab Switcher Implementation
                const tabSurah = document.getElementById('tab-surah');
                const tabJuz = document.getElementById('tab-juz');
                const gridSurah = document.getElementById('grid-surah');
                const gridJuz = document.getElementById('grid-juz');

                tabSurah.addEventListener('click', () => {
                    tabSurah.classList.add('border-emerald-600', 'text-emerald-600', 'dark:border-emerald-400', 'dark:text-emerald-400');
                    tabSurah.classList.remove('border-transparent', 'text-zinc-500', 'dark:text-zinc-400');
                    
                    tabJuz.classList.remove('border-emerald-600', 'text-emerald-600', 'dark:border-emerald-400', 'dark:text-emerald-400');
                    tabJuz.classList.add('border-transparent', 'text-zinc-500', 'dark:text-zinc-400');

                    gridSurah.classList.remove('hidden');
                    gridJuz.classList.add('hidden');
                });

                tabJuz.addEventListener('click', () => {
                    tabJuz.classList.add('border-emerald-600', 'text-emerald-600', 'dark:border-emerald-400', 'dark:text-emerald-400');
                    tabJuz.classList.remove('border-transparent', 'text-zinc-500', 'dark:text-zinc-400');

                    tabSurah.classList.remove('border-emerald-600', 'text-emerald-600', 'dark:border-emerald-400', 'dark:text-emerald-400');
                    tabSurah.classList.add('border-transparent', 'text-zinc-500', 'dark:text-zinc-400');

                    gridJuz.classList.remove('hidden');
                    gridSurah.classList.add('hidden');
                });

                // Search Filter Implementation
                const searchInput = document.getElementById('search-input');
                const clearSearchBtn = document.getElementById('clear-search-btn');
                const surahCards = document.querySelectorAll('.surah-card');

                searchInput.addEventListener('input', (e) => {
                    const query = e.target.value.toLowerCase().trim();
                    
                    if (query.length > 0) {
                        clearSearchBtn.classList.remove('hidden');
                    } else {
                        clearSearchBtn.classList.add('hidden');
                    }

                    // Always switch back to Surahs tab automatically when user types to show results
                    if (query.length > 0 && gridSurah.classList.contains('hidden')) {
                        tabSurah.click();
                    }

                    surahCards.forEach(card => {
                        const searchTerm = card.getAttribute('data-search-term').toLowerCase();
                        if (searchTerm.includes(query)) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });

                clearSearchBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    clearSearchBtn.classList.add('hidden');
                    surahCards.forEach(card => {
                        card.style.display = 'flex';
                    });
                    searchInput.focus();
                });

                // '/' key focus shortcut
                document.addEventListener('keydown', (e) => {
                    if (e.key === '/' && document.activeElement !== searchInput) {
                        e.preventDefault();
                        searchInput.focus();
                    }
                });
            });
        </script>
    </body>
</html>
