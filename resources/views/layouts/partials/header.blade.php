<!-- Main Header -->
<header class="quran-header">
    <div class="quran-header-container">
        <!-- Left Section -->
        <div class="quran-header-left">
            <!-- Mobile Menu Toggle -->
            <button class="quran-mobile-menu-btn d-lg-none" id="mobileMenuBtn">
                <i class="bi bi-list"></i>
            </button>

            <!-- Page Title -->
            <div class="quran-page-title">
                <h4 class="mb-0">@yield('page-title', __('dashboard.title'))</h4>
                @hasSection('page-subtitle')
                    <small class="quran-page-subtitle">@yield('page-subtitle')</small>
                @endif
            </div>
        </div>

        <!-- Center Section - Search -->
        <div class="quran-header-center">
            <div class="quran-global-search">
                <div class="quran-search-wrapper">
                    <i class="bi bi-search quran-search-icon"></i>
                    <input type="text"
                           class="quran-search-input"
                           placeholder="{{ __('header.search_quran_placeholder') }}"
                           id="globalSearchInput">
                    <div class="quran-search-shortcut">
                        <kbd>Ctrl</kbd> + <kbd>K</kbd>
                    </div>
                </div>
                <!-- Search Results Dropdown -->
                <div class="quran-search-results" id="searchResults">
                    <div class="quran-search-categories">
                        <button class="quran-search-category active" data-category="surah">
                            {{ __('sidebar.surahs') }}
                        </button>
                        <button class="quran-search-category" data-category="ayah">
                            {{ __('sidebar.ayahs') }}
                        </button>
                        <button class="quran-search-category" data-category="tafsir">
                            {{ __('sidebar.tafsir') }}
                        </button>
                    </div>
                    <div class="quran-search-results-list">
                        <!-- Dynamic Search Results -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="quran-header-right">
            <!-- Continue Reading -->
            @if($lastRead = auth()->user()->readingHistories()->latest()->first())
                <a href="#" class="quran-continue-reading-btn">
                    <i class="bi bi-book-half"></i>
                    <div class="d-none d-md-block">
                        <small>{{ __('header.continue_reading') }}</small>
                        <strong>{{ $lastRead->surah->name }} {{ $lastRead->ayah->number }}</strong>
                    </div>
                </a>
            @endif

            <!-- Audio Player Toggle -->
            <button class="quran-header-icon-btn" id="audioPlayerToggle">
                <i class="bi bi-music-note"></i>
                <span class="quran-audio-indicator" style="display: none;"></span>
            </button>

            <!-- Theme Toggle Button -->
            <button class="quran-header-icon-btn" id="themeToggleHeaderBtn" data-bs-toggle="tooltip" title="{{ __('sidebar.dark_mode') }}">
                <i class="bi bi-moon-stars" id="themeHeaderIcon"></i>
            </button>

            <!-- Language Switcher -->
            <div class="quran-language-switcher">
                <button class="quran-header-icon-btn" id="languageDropdownBtn">
                    @php
                        $locale = app()->getLocale();
                        $flags = ['en' => '🇬🇧', 'ku' => '🇮🇶', 'ar' => '🇸🇦'];
                        $names = ['en' => 'EN', 'ku' => 'KU', 'ar' => 'AR'];
                    @endphp
                    <span class="quran-current-lang">{{ $names[$locale] }}</span>
                </button>
                <div class="quran-language-dropdown">
                    <a href="{{ route('language.switch', 'en') }}"
                       class="quran-language-option {{ $locale == 'en' ? 'active' : '' }}">
                        <span class="quran-lang-flag">🇬🇧</span>
                        <span class="quran-lang-name">English</span>
                    </a>
                    <a href="{{ route('language.switch', 'ku') }}"
                       class="quran-language-option {{ $locale == 'ku' ? 'active' : '' }}">
                        <span class="quran-lang-flag">🇮🇶</span>
                        <span class="quran-lang-name">کوردی</span>
                    </a>
                    <a href="{{ route('language.switch', 'ar') }}"
                       class="quran-language-option {{ $locale == 'ar' ? 'active' : '' }}">
                        <span class="quran-lang-flag">🇸🇦</span>
                        <span class="quran-lang-name">العربية</span>
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <div class="quran-notifications">
                <button class="quran-header-icon-btn" id="notificationsBtn">
                    <i class="bi bi-bell"></i>
                    <span class="quran-notification-badge">3</span>
                </button>
                <div class="quran-notifications-dropdown">
                    <div class="quran-notifications-header">
                        <h6>{{ __('header.notifications') }}</h6>
                        <button class="quran-mark-read">{{ __('header.mark_all_read') }}</button>
                    </div>
                    <div class="quran-notifications-list">
                        <a href="#" class="quran-notification-item unread">
                            <div class="quran-notification-icon">
                                <i class="bi bi-bookmark-star"></i>
                            </div>
                            <div class="quran-notification-content">
                                <p>{{ __('header.memorization_reminder') }}</p>
                                <small>2 hours ago</small>
                            </div>
                        </a>
                        <a href="#" class="quran-notification-item">
                            <div class="quran-notification-icon">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <div class="quran-notification-content">
                                <p>{{ __('header.achievement_unlocked') }}</p>
                                <small>Yesterday</small>
                            </div>
                        </a>
                    </div>
                    <div class="quran-notifications-footer">
                        <a href="#">{{ __('header.view_all') }}</a>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="quran-user-menu">
                <button class="quran-user-menu-btn" id="userMenuBtn">
                    <img src="{{ auth()->user()->avatar ?? asset('images/default-avatar.png') }}"
                         alt="User"
                         class="d-none quran-user-avatar">
                    <span class="d-none d-md-inline">{{ substr(auth()->user()->name, 0, 4) ?? 'User' }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="quran-user-dropdown">
                    <div class="quran-user-dropdown-header">
                        <div class="quran-user-stats">
                            <div class="quran-stat-item">
                                <span class="quran-stat-value">{{ auth()->user()->memorizationPlans()->count() ?? 0 }}</span>
                                <span class="quran-stat-label">{{ __('sidebar.memorization_plans') }}</span>
                            </div>
                            <div class="quran-stat-item">
                                <span class="quran-stat-value">{{ auth()->user()->bookmarks()->count() ?? 0 }}</span>
                                <span class="quran-stat-label">{{ __('sidebar.bookmarks') }}</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="quran-user-dropdown-item">
                        <i class="bi bi-person"></i>
                        <span>{{ __('header.my_profile') }}</span>
                    </a>
                    <a href="{{ route('user-ayah-progress.index') }}" class="quran-user-dropdown-item">
                        <i class="bi bi-graph-up"></i>
                        <span>{{ __('header.progress') }}</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="quran-user-dropdown-item">
                        <i class="bi bi-gear"></i>
                        <span>{{ __('header.settings') }}</span>
                    </a>
                    <div class="quran-dropdown-divider"></div>
                    <a href="{{ route('logout') }}"
                       class="quran-user-dropdown-item text-danger"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>{{ __('common.logout') }}</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio Player Bar (Initially Hidden) -->
    <div class="quran-audio-player" id="audioPlayer" style="display: none;">
        <div class="quran-audio-player-container">
            <div class="quran-audio-info">
                <img src="{{ asset('images/quran-audio-thumb.jpg') }}"
                     alt="Current Surah"
                     class="quran-audio-thumb">
                <div class="quran-audio-details">
                    <h6 class="quran-audio-title">سورة البقرة</h6>
                    <p class="quran-audio-reciter">الشيخ ماهر المعيقلي</p>
                </div>
            </div>
            <div class="quran-audio-controls">
                <button class="quran-audio-btn"><i class="bi bi-skip-backward-fill"></i></button>
                <button class="quran-audio-btn quran-audio-play"><i class="bi bi-play-fill"></i></button>
                <button class="quran-audio-btn"><i class="bi bi-skip-forward-fill"></i></button>
                <div class="quran-audio-progress">
                    <span class="quran-audio-time">00:00</span>
                    <div class="quran-audio-progress-bar">
                        <div class="quran-audio-progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="quran-audio-time">03:45</span>
                </div>
            </div>
            <div class="quran-audio-actions">
                <button class="quran-audio-btn"><i class="bi bi-volume-up"></i></button>
                <button class="quran-audio-btn"><i class="bi bi-heart"></i></button>
                <button class="quran-audio-btn"><i class="bi bi-download"></i></button>
                <button class="quran-audio-btn" id="closeAudioPlayer">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    </div>
</header>
