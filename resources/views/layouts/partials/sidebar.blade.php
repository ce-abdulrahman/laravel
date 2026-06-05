<!-- Sidebar Toggle Button (Mobile) -->
<button class="quran-sidebar-toggle d-lg-none" id="sidebarToggleBtn">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar Overlay (Mobile) -->
<div class="quran-sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Sidebar -->
<aside class="quran-sidebar" id="mainSidebar">
    <!-- Sidebar Header -->
    <div class="quran-sidebar-header">
        <div class="d-flex align-items-center">
            <div class="quran-logo">
                <i class="bi bi-book quran-logo-icon"></i>
            </div>
            <div class="quran-brand">
                <h5 class="quran-brand-name">{{ __('common.app_name') }}</h5>
                <small class="quran-brand-subtitle">{{ __('sidebar.quran_academy') }}</small>
            </div>
        </div>
        <button class="quran-sidebar-collapse-btn d-none d-lg-block" id="collapseSidebarBtn">
            <i class="bi bi-chevron-double-left"></i>
        </button>
    </div>

    <!-- User Profile Section -->
    <div class="d-none quran-user-profile">
        <div class="quran-avatar">
            <img src="{{ auth()->user()->avatar ?? asset('images/default-avatar.png') }}"
                 alt="User Avatar"
                 class="quran-avatar-img">
            <span class="quran-avatar-status online"></span>
        </div>
        <div class="quran-user-info">
            <h6 class="quran-user-name">{{ auth()->user()->name ?? 'Guest User' }}</h6>
            <span class="quran-user-plan">{{ auth()->user()->plan ?? __('sidebar.free_plan') }}</span>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="quran-sidebar-nav">
        <div class="quran-nav-section">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="quran-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="quran-nav-icon">
                    <i class="bi bi-grid"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.dashboard') }}</span>
                @if(request()->routeIs('dashboard'))
                    <span class="quran-nav-indicator"></span>
                @endif
            </a>

            <!-- Quran Reading Section -->
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.quran_reading') }}</span>
            </div>

            <!-- Surah Navigation -->
            <div class="quran-nav-group">
                <a href="#surahSubmenu"
                   class="quran-nav-item has-submenu"
                   data-bs-toggle="collapse"
                   aria-expanded="false">
                    <div class="quran-nav-icon">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.surahs') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse" id="surahSubmenu">
                    <div class="quran-submenu-inner">
                        <!-- Dynamic Surah List (Populated via JavaScript) -->
                        <div class="quran-surah-list">
                            <div class="quran-surah-search">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       placeholder="{{ __('sidebar.search_surah') }}"
                                       id="surahSearchInput">
                            </div>
                            <div class="quran-surah-items" id="surahListContainer">
                                <!-- Will be populated dynamically -->
                                <a href="#" class="quran-surah-item">
                                    <span class="surah-number">1</span>
                                    <span class="surah-name">الفاتحة</span>
                                    <span class="surah-trans">Al-Fatiha</span>
                                    <span class="surah-info">7 Ayahs</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $surahs = App\Models\Surah::all();
                $ayahs = App\Models\Ayah::all();
            @endphp
            <!-- Surah Navigation -->
            <a href="{{ route('surahs.index') }}" class="quran-nav-item">
                <div class="quran-nav-icon">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.surahs') }}</span>
                <span class="quran-nav-badge">{{ count($surahs) }}</span>
            </a>

            <!-- Juz Navigation -->
            <a href="{{ route('ayahs.index') }}" class="quran-nav-item">
                <div class="quran-nav-icon">
                    <i class="bi bi-layers"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.ayahs') }}</span>
                <span class="quran-nav-badge">{{ count($ayahs) }}</span>
            </a>

            <!-- Juz Navigation -->
            <a href="#" class="quran-nav-item">
                <div class="quran-nav-icon">
                    <i class="bi bi-layers"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.juz') }}</span>
                <span class="quran-nav-badge">30</span>
            </a>

            <!-- Page Navigation -->
            <a href="#" class="quran-nav-item">
                <div class="quran-nav-icon">
                    <i class="bi bi-file-text"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.page') }}</span>
                <span class="quran-nav-badge">604</span>
            </a>

            
        </div>

        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.tajweed') }}</span>
            </div>
            
            <a href="{{ route('tajweed-rules.index') }}" class="quran-nav-item {{ request()->routeIs('tajweed-rules.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-palette"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.tajweed_rules') }}</span>
            </a>
            
            <a href="{{ route('tajweed-segments.index') }}" class="quran-nav-item {{ request()->routeIs('tajweed-segments.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-puzzle"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.tajweed_segments') }}</span>
            </a>
        </div>

        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.recitation') }}</span>
            </div>

            <!-- Reciters -->
            <a href="{{ route('reciters.index') }}" class="quran-nav-item {{ request()->routeIs('reciters.*') ? 'active' : '' }}">
                <div class="quran-nav-icon">
                    <i class="bi bi-mic"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.reciters') }}</span>
            </a>

            <!-- Audio Files -->
            <a href="{{ route('audio-files.index') }}" class="quran-nav-item {{ request()->routeIs('audio-files.*') ? 'active' : '' }}">
                <div class="quran-nav-icon">
                    <i class="bi bi-headphones"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.audio_library') }}</span>
            </a>
        </div>

        <!-- Qiraat Section -->
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.qiraat') }}</span>
            </div>
            
            <a href="{{ route('qiraats.index') }}" class="quran-nav-item {{ request()->routeIs('qiraats.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-book-half"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.qiraats') }}</span>
            </a>
            
            <a href="{{ route('qiraat-texts.index') }}" class="quran-nav-item {{ request()->routeIs('qiraat-texts.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-journal-text"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.qiraat_texts') }}</span>
            </a>
        </div>

        

        <div class="quran-nav-section">
            <div class="quran-nav-section">
                <div class="quran-nav-divider">
                    <span>{{ __('sidebar.tafsir') }}</span>
                </div>
                
                <a href="{{ route('tafsir-books.index') }}" class="quran-nav-item {{ request()->routeIs('tafsir-books.*') ? 'active' : '' }}">
                    <span class="quran-nav-icon">
                        <i class="bi bi-bookshelf"></i>
                    </span>
                    <span class="quran-nav-label">{{ __('sidebar.tafsir_books') }}</span>
                </a>
                
                <a href="{{ route('tafsirs.index') }}" class="quran-nav-item {{ request()->routeIs('tafsirs.*') ? 'active' : '' }}">
                    <span class="quran-nav-icon">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </span>
                    <span class="quran-nav-label">{{ __('sidebar.tafsirs') }}</span>
                </a>
            </div>

            <div class="quran-nav-divider">
                <span>{{ __('sidebar.study_tools') }}</span>
            </div>

            <!-- Translations -->
            <a href="{{ route('translations.index') }}" class="quran-nav-item {{ request()->routeIs('translations.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-translate"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.translations') }}</span>
            </a>

            <!-- Memorization -->
            <div class="quran-nav-group">
                <a href="#memorizationSubmenu"
                   class="quran-nav-item has-submenu"
                   data-bs-toggle="collapse">
                    <div class="quran-nav-icon">
                        <i class="bi bi-brain"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.memorization') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse" id="memorizationSubmenu">
                    <a href="#" class="quran-submenu-item">
                        <i class="bi bi-calendar-check"></i>
                        <span>{{ __('sidebar.memorization_plan') }}</span>
                    </a>
                    <a href="#" class="quran-submenu-item">
                        <i class="bi bi-check2-circle"></i>
                        <span>{{ __('sidebar.daily_review') }}</span>
                    </a>
                    <a href="#" class="quran-submenu-item">
                        <i class="bi bi-graph-up"></i>
                        <span>{{ __('sidebar.progress') }}</span>
                    </a>
                </div>
            </div>

            {{-- User Section --}}
            <a href="{{ route('bookmarks.index') }}" class="quran-nav-item {{ request()->routeIs('bookmarks.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-bookmark"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.bookmarks') }}</span>
            </a>
            
            <a href="{{ route('favorites.index') }}" class="quran-nav-item {{ request()->routeIs('favorites.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-heart"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.favorites') }}</span>
            </a>
            
            <a href="{{ route('reading-history.index') }}" class="quran-nav-item {{ request()->routeIs('reading-history.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-clock-history"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.reading_history') }}</span>
            </a>
            
            <a href="{{ route('leaderboard.index') }}" class="quran-nav-item {{ request()->routeIs('leaderboard.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-trophy"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.leaderboard') }}</span>
            </a>
        </div>

        {{-- resources/views/layouts/partials/sidebar.blade.php --}}

        {{-- Memorization Section --}}
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.memorization') }}</span>
            </div>
            
            <a href="{{ route('memorization-plans.index') }}" class="quran-nav-item {{ request()->routeIs('memorization-plans.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-calendar-range"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.memorization_plans') }}</span>
            </a>
            
            <a href="{{ route('memorization-reviews.index') }}" class="quran-nav-item {{ request()->routeIs('memorization-reviews.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-check2-all"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.memorization_reviews') }}</span>
            </a>
        </div>

        {{-- Progress Section --}}
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.progress') }}</span>
            </div>
            
            <a href="{{ route('user-ayah-progress.index') }}" class="quran-nav-item {{ request()->routeIs('user-ayah-progress.*') && !request()->routeIs('user-ayah-progress.dashboard') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-bar-chart-steps"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.my_progress') }}</span>
            </a>
            
            <a href="{{ route('user-ayah-progress.dashboard') }}" class="quran-nav-item {{ request()->routeIs('user-ayah-progress.dashboard') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-speedometer2"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.progress_dashboard') }}</span>
            </a>
        </div>

        {{-- Admin Section - تەنها بۆ ئەدمین --}}
        @if(auth()->user()?->role === 'admin')
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.admin') }}</span>
            </div>
            
            <a href="{{ route('settings.index') }}" class="quran-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-gear"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.settings') }}</span>
            </a>
            
            <a href="{{ route('banners.index') }}" class="quran-nav-item {{ request()->routeIs('banners.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-image"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.banners') }}</span>
            </a>
            
            <a href="{{ route('adhkar-categories.index') }}" class="quran-nav-item {{ request()->routeIs('adhkar-categories.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-tags"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.adhkar_categories') }}</span>
            </a>
            
            <a href="{{ route('adhkars.index') }}" class="quran-nav-item {{ request()->routeIs('adhkars.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-chat-square-text"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.adhkars') }}</span>
            </a>

            <a href="{{ route('tasbihs.index') }}" class="quran-nav-item {{ request()->routeIs('tasbihs.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-heptagon"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.tasbihs') }}</span>
            </a>

            <a href="{{ route('hadith-categories.index') }}" class="quran-nav-item {{ request()->routeIs('hadith-categories.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-folder2"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.hadith_categories') }}</span>
            </a>

            <a href="{{ route('hadiths.index') }}" class="quran-nav-item {{ request()->routeIs('hadiths.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-book"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.hadiths') }}</span>
            </a>
        </div>
        @endif

            <!-- Theme Toggle -->
            <button class="quran-nav-item w-100" id="themeToggleBtn">
                <div class="quran-nav-icon">
                    <i class="bi bi-moon-stars"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.dark_mode') }}</span>
                <div class="form-check form-switch ms-auto">
                    <input class="form-check-input" type="checkbox" id="themeSwitch">
                </div>
            </button>
    </nav>

    <!-- Sidebar Footer -->
    <div class="quran-sidebar-footer">
        <div class="quran-version-info">
            <small>{{ __('footer.version') }} 2.0.0</small>
        </div>
    </div>
</aside>
