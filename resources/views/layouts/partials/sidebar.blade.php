@php
    $settings = \Illuminate\Support\Facades\Cache::remember('app_settings', 3600, function () {
        return \App\Models\Setting::firstOrCreate([]);
    });
@endphp

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
                @if($settings->app_logo)
                    <img src="{{ asset('storage/' . $settings->app_logo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                @else
                    <i class="bi bi-book quran-logo-icon"></i>
                @endif
            </div>
            <div class="quran-brand">
                <h5 class="quran-brand-name">{{ $settings->app_name ?? __('common.app_name') }}</h5>
                <small class="quran-brand-subtitle">{{ __('sidebar.quran_academy') }}</small>
            </div>
        </div>
        <button class="quran-sidebar-collapse-btn d-none d-lg-block" id="collapseSidebarBtn">
            <i class="bi bi-chevron-double-left"></i>
        </button>
        <button class="quran-sidebar-close-btn d-lg-none" id="closeSidebarBtn" style="background: transparent; border: none; color: white; font-size: 1.25rem; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 4px; border-radius: 8px;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="quran-sidebar-nav">
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
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.quran_reading') }}</span>
            </div>

            @php
                $surahsCount = App\Models\Surah::count();
                $ayahsCount = App\Models\Ayah::count();
            @endphp

            <!-- Surah Navigation Dropdown -->
            <a href="{{ route('surahs.index') }}" class="quran-nav-item {{ request()->routeIs('surahs.*') ? 'active' : '' }}">
                <div class="quran-nav-icon">
                    <i class="bi bi-book"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.surahs') }}</span>
                <span class="quran-nav-badge">{{ $surahsCount }}</span>
            </a>

            <!-- Ayahs List Link -->
            <a href="{{ route('ayahs.index') }}" class="quran-nav-item {{ request()->routeIs('ayahs.*') ? 'active' : '' }}">
                <div class="quran-nav-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.ayahs') }}</span>
                <span class="quran-nav-badge">{{ $ayahsCount }}</span>
            </a>

            <!-- Juz Navigation -->
            <a href="{{ route('juz.index') }}" class="quran-nav-item {{ request()->routeIs('juz.*') ? 'active' : '' }}">
                <div class="quran-nav-icon">
                    <i class="bi bi-layers"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.juz') }}</span>
                <span class="quran-nav-badge">30</span>
            </a>

            <!-- Page Navigation -->
            <a href="{{ route('page.index') }}" class="quran-nav-item {{ request()->routeIs('page.*') ? 'active' : '' }}">
                <div class="quran-nav-icon">
                    <i class="bi bi-file-text"></i>
                </div>
                <span class="quran-nav-label">{{ __('sidebar.page') }}</span>
                <span class="quran-nav-badge">604</span>
            </a>
        </div>

        <!-- Recitation & Tajweed Section -->
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.recitation') }} &amp; {{ __('sidebar.tajweed') }}</span>
            </div>

            <!-- Tajweed Dropdown -->
            <div class="quran-nav-group">
                <a href="#tajweedSubmenu"
                   class="quran-nav-item has-submenu {{ request()->routeIs('tajweed-rules.*') || request()->routeIs('tajweed-segments.*') || request()->routeIs('tajweed-rule-categories.*') ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('tajweed-rules.*') || request()->routeIs('tajweed-segments.*') || request()->routeIs('tajweed-rule-categories.*') ? 'true' : 'false' }}">
                    <div class="quran-nav-icon">
                        <i class="bi bi-palette"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.tajweed') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse {{ request()->routeIs('tajweed-rules.*') || request()->routeIs('tajweed-segments.*') || request()->routeIs('tajweed-rule-categories.*') ? 'show' : '' }}" id="tajweedSubmenu">
                    <a href="{{ route('tajweed-rule-categories.index') }}" class="quran-submenu-item {{ request()->routeIs('tajweed-rule-categories.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.tajweed_categories') }}</span>
                    </a>
                    <a href="{{ route('tajweed-rules.index') }}" class="quran-submenu-item {{ request()->routeIs('tajweed-rules.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.tajweed_rules') }}</span>
                    </a>
                    <a href="{{ route('tajweed-segments.index') }}" class="quran-submenu-item {{ request()->routeIs('tajweed-segments.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.tajweed_segments') }}</span>
                    </a>
                </div>
            </div>

            <!-- Qiraat Dropdown -->
            <div class="quran-nav-group">
                <a href="#qiraatSubmenu"
                   class="quran-nav-item has-submenu {{ request()->routeIs('qiraats.*') || request()->routeIs('qiraat-texts.*') ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('qiraats.*') || request()->routeIs('qiraat-texts.*') ? 'true' : 'false' }}">
                    <div class="quran-nav-icon">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.qiraat') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse {{ request()->routeIs('qiraats.*') || request()->routeIs('qiraat-texts.*') ? 'show' : '' }}" id="qiraatSubmenu">
                    <a href="{{ route('qiraats.index') }}" class="quran-submenu-item {{ request()->routeIs('qiraats.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.qiraats') }}</span>
                    </a>
                    <a href="{{ route('qiraat-texts.index') }}" class="quran-submenu-item {{ request()->routeIs('qiraat-texts.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.qiraat_texts') }}</span>
                    </a>
                </div>
            </div>

            <!-- Recitation Dropdown -->
            <div class="quran-nav-group">
                <a href="#recitationSubmenu"
                   class="quran-nav-item has-submenu {{ request()->routeIs('reciters.*') || request()->routeIs('audio-files.*') ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('reciters.*') || request()->routeIs('audio-files.*') ? 'true' : 'false' }}">
                    <div class="quran-nav-icon">
                        <i class="bi bi-mic"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.recitation') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse {{ request()->routeIs('reciters.*') || request()->routeIs('audio-files.*') ? 'show' : '' }}" id="recitationSubmenu">
                    <a href="{{ route('reciters.index') }}" class="quran-submenu-item {{ request()->routeIs('reciters.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.reciters') }}</span>
                    </a>
                    <a href="{{ route('audio-files.index') }}" class="quran-submenu-item {{ request()->routeIs('audio-files.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.audio_library') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tafsir & Study Section -->
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.tafsir') }} &amp; {{ __('sidebar.study_tools') }}</span>
            </div>

            <!-- Tafsir Dropdown -->
            <div class="quran-nav-group">
                <a href="#tafsirSubmenu"
                   class="quran-nav-item has-submenu {{ request()->routeIs('tafsir-books.*') || request()->routeIs('tafsirs.*') ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('tafsir-books.*') || request()->routeIs('tafsirs.*') ? 'true' : 'false' }}">
                    <div class="quran-nav-icon">
                        <i class="bi bi-bookshelf"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.tafsir') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse {{ request()->routeIs('tafsir-books.*') || request()->routeIs('tafsirs.*') ? 'show' : '' }}" id="tafsirSubmenu">
                    <a href="{{ route('tafsir-books.index') }}" class="quran-submenu-item {{ request()->routeIs('tafsir-books.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.tafsir_books') }}</span>
                    </a>
                    <a href="{{ route('tafsirs.index') }}" class="quran-submenu-item {{ request()->routeIs('tafsirs.*') ? 'active' : '' }}">
                        <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i>
                        <span>{{ __('sidebar.tafsirs') }}</span>
                    </a>
                </div>
            </div>

            <!-- Translations Link -->
            <a href="{{ route('translations.index') }}" class="quran-nav-item {{ request()->routeIs('translations.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-translate"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.translations') }}</span>
            </a>
        </div>

        <!-- Memorization Section -->
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.memorization') }}</span>
            </div>

            <!-- Memorization Submenu -->
            <div class="quran-nav-group">
                <a href="#memorizationSubmenu"
                   class="quran-nav-item has-submenu {{ request()->routeIs('memorization-plans.*') || request()->routeIs('memorization-reviews.*') || request()->routeIs('user-ayah-progress.*') ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('memorization-plans.*') || request()->routeIs('memorization-reviews.*') || request()->routeIs('user-ayah-progress.*') ? 'true' : 'false' }}">
                    <div class="quran-nav-icon">
                        <i class="bi bi-brain"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.memorization') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse {{ request()->routeIs('memorization-plans.*') || request()->routeIs('memorization-reviews.*') || request()->routeIs('user-ayah-progress.*') ? 'show' : '' }}" id="memorizationSubmenu">
                    <a href="{{ route('memorization-plans.index') }}" class="quran-submenu-item {{ request()->routeIs('memorization-plans.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-range me-2"></i>
                        <span>{{ __('sidebar.memorization_plans') }}</span>
                    </a>
                    <a href="{{ route('memorization-reviews.index') }}" class="quran-submenu-item {{ request()->routeIs('memorization-reviews.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-all me-2"></i>
                        <span>{{ __('sidebar.memorization_reviews') }}</span>
                    </a>
                    <a href="{{ route('user-ayah-progress.index') }}" class="quran-submenu-item {{ request()->routeIs('user-ayah-progress.*') && !request()->routeIs('user-ayah-progress.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-steps me-2"></i>
                        <span>{{ __('sidebar.my_progress') }}</span>
                    </a>
                    <a href="{{ route('user-ayah-progress.dashboard') }}" class="quran-submenu-item {{ request()->routeIs('user-ayah-progress.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        <span>{{ __('sidebar.progress_dashboard') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Personal Section -->
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.my_account') }}</span>
            </div>

            <!-- Bookmarks -->
            <a href="{{ route('bookmarks.index') }}" class="quran-nav-item {{ request()->routeIs('bookmarks.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-bookmark"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.bookmarks') }}</span>
            </a>
            
            <!-- Favorites -->
            <a href="{{ route('favorites.index') }}" class="quran-nav-item {{ request()->routeIs('favorites.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-heart"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.favorites') }}</span>
            </a>
            
            <!-- Reading History -->
            <a href="{{ route('reading-history.index') }}" class="quran-nav-item {{ request()->routeIs('reading-history.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-clock-history"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.reading_history') }}</span>
            </a>
            
            <!-- Leaderboard -->
            <a href="{{ route('leaderboard.index') }}" class="quran-nav-item {{ request()->routeIs('leaderboard.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-trophy"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.leaderboard') }}</span>
            </a>
        </div>

        <!-- Admin Section -->
        @if(auth()->user()?->role === 'admin')
        <div class="quran-nav-section">
            <div class="quran-nav-divider">
                <span>{{ __('sidebar.admin') }}</span>
            </div>
            
            <!-- Settings -->
            <a href="{{ route('settings.index') }}" class="quran-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-gear"></i>
                </span>
                <span class="quran-nav-label">{{ __('sidebar.settings') }}</span>
            </a>
            
            <!-- Banners -->
            <a href="{{ route('banners.index') }}" class="quran-nav-item {{ request()->routeIs('banners.*') ? 'active' : '' }}">
                <span class="quran-nav-icon">
                    <i class="bi bi-image"></i>
                </span>
                <span class="quran-nav-label{{ request()->routeIs('banners.*') ? ' active' : '' }}">{{ __('sidebar.banners') }}</span>
            </a>
            
            <!-- Adhkar & Tasbih Dropdown -->
            <div class="quran-nav-group">
                <a href="#adhkarAdminSubmenu"
                   class="quran-nav-item has-submenu {{ request()->routeIs('adhkar-categories.*') || request()->routeIs('adhkars.*') || request()->routeIs('tasbihs.*') ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('adhkar-categories.*') || request()->routeIs('adhkars.*') || request()->routeIs('tasbihs.*') ? 'true' : 'false' }}">
                    <div class="quran-nav-icon">
                        <i class="bi bi-stars"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.adhkars') }} &amp; {{ __('sidebar.tasbihs') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse {{ request()->routeIs('adhkar-categories.*') || request()->routeIs('adhkars.*') || request()->routeIs('tasbihs.*') ? 'show' : '' }}" id="adhkarAdminSubmenu">
                    <a href="{{ route('adhkar-categories.index') }}" class="quran-submenu-item {{ request()->routeIs('adhkar-categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags me-2"></i>
                        <span>{{ __('sidebar.adhkar_categories') }}</span>
                    </a>
                    <a href="{{ route('adhkars.index') }}" class="quran-submenu-item {{ request()->routeIs('adhkars.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-square-text me-2"></i>
                        <span>{{ __('sidebar.adhkars') }}</span>
                    </a>
                    <a href="{{ route('tasbihs.index') }}" class="quran-submenu-item {{ request()->routeIs('tasbihs.*') ? 'active' : '' }}">
                        <i class="bi bi-heptagon me-2"></i>
                        <span>{{ __('sidebar.tasbihs') }}</span>
                    </a>
                </div>
            </div>

            <!-- Hadith Dropdown -->
            <div class="quran-nav-group">
                <a href="#hadithAdminSubmenu"
                   class="quran-nav-item has-submenu {{ request()->routeIs('hadith-categories.*') || request()->routeIs('hadiths.*') ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('hadith-categories.*') || request()->routeIs('hadiths.*') ? 'true' : 'false' }}">
                    <div class="quran-nav-icon">
                        <i class="bi bi-chat-left-quote"></i>
                    </div>
                    <span class="quran-nav-label">{{ __('sidebar.hadiths') }}</span>
                    <i class="bi bi-chevron-down quran-submenu-icon"></i>
                </a>
                <div class="quran-submenu collapse {{ request()->routeIs('hadith-categories.*') || request()->routeIs('hadiths.*') ? 'show' : '' }}" id="hadithAdminSubmenu">
                    <a href="{{ route('hadith-categories.index') }}" class="quran-submenu-item {{ request()->routeIs('hadith-categories.*') ? 'active' : '' }}">
                        <i class="bi bi-folder2 me-2"></i>
                        <span>{{ __('sidebar.hadith_categories') }}</span>
                    </a>
                    <a href="{{ route('hadiths.index') }}" class="quran-submenu-item {{ request()->routeIs('hadiths.*') ? 'active' : '' }}">
                        <i class="bi bi-book me-2"></i>
                        <span>{{ __('sidebar.hadiths') }}</span>
                    </a>
                </div>
            </div>
        </div>
        @endif
        
    </nav>

    <!-- Sidebar Footer -->
    <div class="quran-sidebar-footer">
        <div class="quran-version-info">
            <small>{{ __('footer.version') }} 2.0.0</small>
        </div>
    </div>
</aside>

<style>
    /* Styling for active submenus */
    .quran-submenu-item.active {
        color: white !important;
        background: rgba(255, 255, 255, 0.08);
        font-weight: 600;
    }
</style>
