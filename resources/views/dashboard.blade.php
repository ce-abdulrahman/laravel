@extends('layouts.app')

@section('title', __('dashboard.title'))
@section('page-title', __('dashboard.welcome_back') . ', ' . auth()->user()->name)
@section('page-subtitle', __('dashboard.quran_journey'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('dashboard.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Quick Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Users Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <h6 class="quran-stat-label">{{ __('dashboard.users') }}</h6>
                        <h3 class="quran-stat-value">{{ $stats['users'] ?? '0' }}</h3>
                        <span class="quran-stat-sub">{{ __('dashboard.users_subtitle') }}</span>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hadiths Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <a href="{{ route('hadiths.index') }}" class="text-decoration-none">
                <div class="quran-stat-card quran-stat-success">
                    <div class="quran-stat-content">
                        <div class="quran-stat-info">
                            <h6 class="quran-stat-label">{{ __('sidebar.hadiths') }}</h6>
                            <h3 class="quran-stat-value">{{ $stats['hadiths'] ?? '0' }}</h3>
                            <span class="quran-stat-sub">{{ __('dashboard.hadiths_subtitle', ['count' => $stats['hadith_categories'] ?? '0']) }}</span>
                        </div>
                        <div class="quran-stat-icon">
                            <i class="bi bi-chat-quote-fill"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Adhkars Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <a href="{{ route('adhkars.index') }}" class="text-decoration-none">
                <div class="quran-stat-card quran-stat-warning">
                    <div class="quran-stat-content">
                        <div class="quran-stat-info">
                            <h6 class="quran-stat-label">{{ __('sidebar.adhkars') }}</h6>
                            <h3 class="quran-stat-value">{{ $stats['adhkars'] ?? '0' }}</h3>
                            <span class="quran-stat-sub">{{ __('dashboard.adhkars_subtitle', ['count' => $stats['adhkar_categories'] ?? '0']) }}</span>
                        </div>
                        <div class="quran-stat-icon">
                            <i class="bi bi-sun-fill"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tasbihs Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <a href="{{ route('tasbihs.index') }}" class="text-decoration-none">
                <div class="quran-stat-card quran-stat-info">
                    <div class="quran-stat-content">
                        <div class="quran-stat-info">
                            <h6 class="quran-stat-label">{{ __('sidebar.tasbihs') }}</h6>
                            <h3 class="quran-stat-value">{{ $stats['tasbihs'] ?? '0' }}</h3>
                            <span class="quran-stat-sub">{{ __('dashboard.tasbihs_subtitle') }}</span>
                        </div>
                        <div class="quran-stat-icon">
                            <i class="bi bi-fingerprint"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Surahs Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <a href="{{ route('surahs.index') }}" class="text-decoration-none">
                <div class="quran-stat-card quran-stat-success">
                    <div class="quran-stat-content">
                        <div class="quran-stat-info">
                            <h6 class="quran-stat-label">{{ __('sidebar.surahs') }}</h6>
                            <h3 class="quran-stat-value">{{ $stats['surahs'] ?? '0' }}</h3>
                            <span class="quran-stat-sub">{{ __('dashboard.surahs_subtitle') }}</span>
                        </div>
                        <div class="quran-stat-icon">
                            <i class="bi bi-book-half"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ayahs Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <a href="{{ route('ayahs.index') }}" class="text-decoration-none">
                <div class="quran-stat-card quran-stat-primary">
                    <div class="quran-stat-content">
                        <div class="quran-stat-info">
                            <h6 class="quran-stat-label">{{ __('sidebar.ayahs') }}</h6>
                            <h3 class="quran-stat-value">{{ $stats['ayahs'] ?? '0' }}</h3>
                            <span class="quran-stat-sub">{{ __('dashboard.ayahs_subtitle') }}</span>
                        </div>
                        <div class="quran-stat-icon">
                            <i class="bi bi-file-text-fill"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Reciters Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <a href="{{ route('reciters.index') }}" class="text-decoration-none">
                <div class="quran-stat-card quran-stat-info">
                    <div class="quran-stat-content">
                        <div class="quran-stat-info">
                            <h6 class="quran-stat-label">{{ __('sidebar.reciters') }}</h6>
                            <h3 class="quran-stat-value">{{ $stats['reciters'] ?? '0' }}</h3>
                            <span class="quran-stat-sub">{{ __('dashboard.reciters_subtitle', ['count' => $stats['audio_files'] ?? '0']) }}</span>
                        </div>
                        <div class="quran-stat-icon">
                            <i class="bi bi-headphones"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Banners Card -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
            <a href="{{ route('banners.index') }}" class="text-decoration-none">
                <div class="quran-stat-card quran-stat-warning">
                    <div class="quran-stat-content">
                        <div class="quran-stat-info">
                            <h6 class="quran-stat-label">{{ __('sidebar.banners') }}</h6>
                            <h3 class="quran-stat-value">{{ $stats['banners'] ?? '0' }}</h3>
                            <span class="quran-stat-sub">{{ __('dashboard.banners_subtitle') }}</span>
                        </div>
                        <div class="quran-stat-icon">
                            <i class="bi bi-flag-fill"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="row g-4">
        <!-- Left Column - Continue Reading & Recent Activity -->
        <div class="col-xl-8">
            @if(auth()->user()?->role === 'admin')
                <!-- Translation Dashboard Widget -->
                <div class="quran-card mb-4 border-start border-primary border-4">
                    <div class="quran-card-header bg-transparent border-0 pb-0">
                        <h5 class="quran-card-title text-primary">
                            <i class="bi bi-translate me-2"></i>
                            {{ __('dashboard.translation_status') ?? 'Translation Coverage & Status' }}
                        </h5>
                        <a href="{{ route('translations-manager.index') }}" class="quran-card-link text-primary fw-semibold">
                            {{ __('dashboard.manage_translations') ?? 'Manage Translations' }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="quran-card-body pt-2">
                        <div class="row align-items-center">
                            <!-- Coverage Circular Visualizer -->
                            <div class="col-md-4 text-center my-3 my-md-0">
                                <div class="d-inline-block position-relative">
                                    <div class="translation-coverage-radial" style="
                                        width: 130px; 
                                        height: 130px; 
                                        border-radius: 50%; 
                                        background: radial-gradient(closest-side, var(--quran-bg-card, #ffffff) 78%, transparent 80% 100%), conic-gradient(var(--quran-primary, #1B7340) {{ $stats['translation_coverage'] ?? 0 }}%, var(--quran-border-light, #e9ecef) 0);
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        margin: 0 auto;
                                        box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
                                    ">
                                        <div class="text-center">
                                            <span class="fs-3 fw-extrabold text-primary d-block">{{ number_format($stats['translation_coverage'] ?? 0, 1) }}%</span>
                                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.5px;">{{ __('dashboard.coverage') ?? 'Coverage' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Metrics Cards Grid -->
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <!-- Metric 1: Total Languages -->
                                    <div class="col-6">
                                        <div class="p-3 rounded border border-light" style="background: rgba(27, 115, 64, 0.03); border-left: 3px solid var(--quran-primary, #1B7340) !important;">
                                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">{{ __('dashboard.total_languages') ?? 'Total Languages' }}</small>
                                            <span class="fs-4 fw-bold text-dark">{{ $stats['total_languages'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <!-- Metric 2: Total Translation Records -->
                                    <div class="col-6">
                                        <div class="p-3 rounded border border-light" style="background: rgba(16, 185, 129, 0.03); border-left: 3px solid #10B981 !important;">
                                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">{{ __('dashboard.total_records') ?? 'Translation Records' }}</small>
                                            <span class="fs-4 fw-bold text-dark">{{ number_format($stats['total_translation_records'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                    <!-- Metric 3: Missing Translations -->
                                    <div class="col-6">
                                        <div class="p-3 rounded border border-light" style="background: @if(($stats['missing_translations'] ?? 0) > 0) rgba(239, 68, 68, 0.03) @else rgba(16, 185, 129, 0.03) @endif; border-left: 3px solid @if(($stats['missing_translations'] ?? 0) > 0) #EF4444 @else #10B981 @endif !important;">
                                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">{{ __('dashboard.missing_translations') ?? 'Missing Count' }}</small>
                                            <span class="fs-4 fw-bold @if(($stats['missing_translations'] ?? 0) > 0) text-danger @else text-success @endif">
                                                {{ number_format($stats['missing_translations'] ?? 0) }}
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Metric 4: Active Locales -->
                                    <div class="col-6">
                                        <div class="p-3 rounded border border-light" style="background: rgba(245, 158, 11, 0.03); border-left: 3px solid #F59E0B !important;">
                                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">{{ __('dashboard.active_locales') ?? 'Active Locales' }}</small>
                                            <span class="fs-6 fw-bold text-dark text-truncate d-block" title="{{ implode(', ', $stats['active_locales'] ?? []) }}">
                                                {{ implode(', ', array_map('strtoupper', $stats['active_locales'] ?? [])) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Continue Reading Section -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-book-half me-2"></i>
                        {{ __('dashboard.continue_reading') }}
                    </h5>
                    <a href="{{ route('reading-history.index') }}" class="quran-card-link">
                        {{ __('dashboard.view_all') }} <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="quran-card-body">
                    @if(isset($lastRead) && $lastRead)
                        <div class="quran-continue-reading-widget">
                            <div class="quran-surah-display">
                                <div class="quran-surah-info">
                                    <span class="quran-surah-number">{{ $lastRead->surah->id }}</span>
                                    <div class="quran-surah-details">
                                        <h4 class="quran-surah-arabic">{{ $lastRead->surah->name_arabic }}</h4>
                                        <p class="quran-surah-translation">{{ $lastRead->surah->name_translation }}</p>
                                        <p class="quran-ayah-info">
                                            {{ __('dashboard.ayah') }} {{ $lastRead->ayah->number }}
                                            {{ __('dashboard.of') }} {{ $lastRead->surah->total_ayahs }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('quran.read', ['surah' => $lastRead->surah->id, 'ayah' => $lastRead->ayah->number]) }}"
                                   class="quran-btn quran-btn-primary">
                                    <i class="bi bi-play-fill"></i>
                                    {{ __('dashboard.continue') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="quran-empty-state">
                            <i class="bi bi-book quran-empty-icon"></i>
                            <h6>{{ __('dashboard.no_reading_history') }}</h6>
                            <p>{{ __('dashboard.start_reading_quran') }}</p>
                            <a href="#" class="quran-btn quran-btn-primary">
                                {{ __('dashboard.browse_quran') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity & History -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-clock-history me-2"></i>
                        {{ __('dashboard.recent_activity') }}
                    </h5>
                    <div class="quran-card-actions">
                        <button class="quran-btn-icon" data-bs-toggle="tooltip" title="{{ __('dashboard.refresh') }}">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="quran-card-body p-0">
                    <div class="quran-activity-timeline">
                        @php
                            $recentActivities = $recentActivities ?? [];
                        @endphp
                        @forelse($recentActivities as $activity)
                            <div class="quran-activity-item">
                                <div class="quran-activity-icon {{ $activity->type }}">
                                    @if($activity->type == 'reading')
                                        <i class="bi bi-book"></i>
                                    @elseif($activity->type == 'memorization')
                                        <i class="bi bi-brain"></i>
                                    @elseif($activity->type == 'bookmark')
                                        <i class="bi bi-bookmark-plus"></i>
                                    @elseif($activity->type == 'audio')
                                        <i class="bi bi-headphones"></i>
                                    @else
                                        <i class="bi bi-activity"></i>
                                    @endif
                                </div>
                                <div class="quran-activity-content">
                                    <p class="quran-activity-text">{{ $activity->description }}</p>
                                    <div class="quran-activity-meta">
                                        <span class="quran-activity-time">
                                            <i class="bi bi-clock"></i>
                                            {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                        @if(isset($activity->surah))
                                            <span class="quran-activity-surah">
                                                <i class="bi bi-journal-bookmark-fill"></i>
                                                {{ $activity->surah->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="quran-empty-state quran-empty-small">
                                <i class="bi bi-calendar-x"></i>
                                <p>{{ __('dashboard.no_recent_activity') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Daily Goals & Recommendations -->
        <div class="col-xl-4">
            <!-- Daily Goals Card -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-target me-2"></i>
                        {{ __('dashboard.daily_goals') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-daily-goals">
                        @php
                            $dailyReadingPages = $dailyReadingPages ?? 0;
                            $dailyReadingGoal = $dailyReadingGoal ?? 5;
                            $dailyMemorizedAyahs = $dailyMemorizedAyahs ?? 0;
                            $dailyMemorizationGoal = $dailyMemorizationGoal ?? 3;
                            $dailyReviewedAyahs = $dailyReviewedAyahs ?? 0;
                            $dailyReviewGoal = $dailyReviewGoal ?? 10;

                            $readingPercentage = $dailyReadingGoal > 0 ? ($dailyReadingPages / $dailyReadingGoal) * 100 : 0;
                            $memorizationPercentage = $dailyMemorizationGoal > 0 ? ($dailyMemorizedAyahs / $dailyMemorizationGoal) * 100 : 0;
                            $reviewPercentage = $dailyReviewGoal > 0 ? ($dailyReviewedAyahs / $dailyReviewGoal) * 100 : 0;
                        @endphp

                        <!-- Reading Goal -->
                        <div class="quran-goal-item">
                            <div class="quran-goal-info">
                                <div class="quran-goal-icon reading">
                                    <i class="bi bi-book"></i>
                                </div>
                                <div class="quran-goal-details">
                                    <h6>{{ __('dashboard.read_pages') }}</h6>
                                    <div class="quran-goal-progress-text">
                                        {{ $dailyReadingPages }}/{{ $dailyReadingGoal }} {{ __('dashboard.pages') }}
                                    </div>
                                </div>
                            </div>
                            <div class="quran-goal-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-primary"
                                         style="width: {{ $readingPercentage }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Memorization Goal -->
                        <div class="quran-goal-item">
                            <div class="quran-goal-info">
                                <div class="quran-goal-icon memorization">
                                    <i class="bi bi-brain"></i>
                                </div>
                                <div class="quran-goal-details">
                                    <h6>{{ __('dashboard.memorize_ayahs') }}</h6>
                                    <div class="quran-goal-progress-text">
                                        {{ $dailyMemorizedAyahs }}/{{ $dailyMemorizationGoal }} {{ __('dashboard.ayahs') }}
                                    </div>
                                </div>
                            </div>
                            <div class="quran-goal-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ $memorizationPercentage }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Review Goal -->
                        <div class="quran-goal-item">
                            <div class="quran-goal-info">
                                <div class="quran-goal-icon review">
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                                <div class="quran-goal-details">
                                    <h6>{{ __('dashboard.review_previous') }}</h6>
                                    <div class="quran-goal-progress-text">
                                        {{ $dailyReviewedAyahs }}/{{ $dailyReviewGoal }} {{ __('dashboard.ayahs') }}
                                    </div>
                                </div>
                            </div>
                            <div class="quran-goal-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-warning"
                                         style="width: {{ $reviewPercentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Progress Chart -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-graph-up me-2"></i>
                        {{ __('dashboard.weekly_progress') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <canvas id="weeklyProgressChart" height="200"></canvas>
                </div>
            </div>

            <!-- Recommended Surahs -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-lightbulb me-2"></i>
                        {{ __('dashboard.recommended_for_you') }}
                    </h5>
                </div>
                <div class="quran-card-body p-0">
                    <div class="quran-recommended-list">
                        @php
                            $recommendedSurahs = $recommendedSurahs ?? [];
                        @endphp
                        @forelse($recommendedSurahs as $surah)
                            <a href="{{ route('quran.surah', $surah->id) }}" class="quran-recommended-item">
                                <div class="quran-recommended-number">{{ $surah->id }}</div>
                                <div class="quran-recommended-info">
                                    <h6 class="quran-recommended-name">{{ $surah->name }}</h6>
                                    <p class="quran-recommended-meta">
                                        {{ $surah->ayahs_count }} {{ __('dashboard.ayahs') }} •
                                        {{ $surah->revelation_type == 'Meccan' ? __('dashboard.meccan') : __('dashboard.medinan') }}
                                    </p>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @empty
                            <div class="quran-empty-state quran-empty-small">
                                <i class="bi bi-compass"></i>
                                <p>{{ __('dashboard.explore_quran') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-lightning-charge me-2"></i>
                        {{ __('dashboard.quick_actions') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-quick-actions">
                        <a href="{{ route('surahs.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-book"></i>
                            <span>{{ __('sidebar.surahs') }}</span>
                        </a>
                        <a href="{{ route('ayahs.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-file-text-fill"></i>
                            <span>{{ __('sidebar.ayahs') }}</span>
                        </a>
                        <a href="{{ route('hadiths.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-chat-quote-fill"></i>
                            <span>{{ __('sidebar.hadiths') }}</span>
                        </a>
                        <a href="{{ route('hadith-categories.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-tags-fill"></i>
                            <span>{{ __('sidebar.hadith_categories') }}</span>
                        </a>
                        <a href="{{ route('adhkars.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-sun-fill"></i>
                            <span>{{ __('sidebar.adhkars') }}</span>
                        </a>
                        <a href="{{ route('adhkar-categories.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-tags"></i>
                            <span>{{ __('sidebar.adhkar_categories') }}</span>
                        </a>
                        <a href="{{ route('tasbihs.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-fingerprint"></i>
                            <span>{{ __('sidebar.tasbihs') }}</span>
                        </a>
                        <a href="{{ route('audio-files.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-music-note-beamed"></i>
                            <span>{{ __('sidebar.audio_library') }}</span>
                        </a>
                        <a href="{{ route('reciters.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-headphones"></i>
                            <span>{{ __('sidebar.reciters') }}</span>
                        </a>
                        <a href="{{ route('banners.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-flag-fill"></i>
                            <span>{{ __('sidebar.banners') }}</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-gear-fill"></i>
                            <span>{{ __('sidebar.settings') }}</span>
                        </a>
                        <a href="{{ route('memorization-plans.index') }}" class="quran-quick-action-btn">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>{{ __('sidebar.memorization_plans') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Memorization Plans Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-calendar-check me-2"></i>
                        {{ __('dashboard.active_memorization_plans') }}
                    </h5>
                    <a href="#" class="quran-btn quran-btn-sm quran-btn-outline-primary">
                        {{ __('dashboard.view_all_plans') }}
                    </a>
                </div>
                <div class="quran-card-body">
                    <div class="row g-4">
                        @php
                            $activePlans = $activePlans ?? [];
                        @endphp
                        @forelse($activePlans as $plan)
                            <div class="col-lg-4 col-md-6">
                                <div class="quran-plan-card">
                                    <div class="quran-plan-header">
                                        <div class="quran-plan-icon">
                                            <i class="bi bi-calendar3"></i>
                                        </div>
                                        <div class="quran-plan-info">
                                            <h6>{{ $plan->name }}</h6>
                                            <span class="quran-plan-badge {{ $plan->status == 'active' ? 'active' : 'paused' }}">
                                                {{ $plan->status == 'active' ? __('dashboard.active') : __('dashboard.paused') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="quran-plan-progress">
                                        <div class="quran-plan-stats">
                                            <span>{{ $plan->completed_ayahs }}/{{ $plan->total_ayahs }} {{ __('dashboard.ayahs') }}</span>
                                            @php
                                                $planProgress = $plan->total_ayahs > 0 ? round(($plan->completed_ayahs / $plan->total_ayahs) * 100) : 0;
                                            @endphp
                                            <span>{{ $planProgress }}%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar"
                                                 style="width: {{ $planProgress }}%"></div>
                                        </div>
                                    </div>
                                    <div class="quran-plan-footer">
                                        <div class="quran-plan-next">
                                            <small>{{ __('dashboard.next_review') }}</small>
                                            <strong>{{ $plan->next_review_date ?? __('dashboard.today') }}</strong>
                                        </div>
                                        <a href="{{ route('memorization.plan', $plan->id) }}" class="quran-btn quran-btn-sm quran-btn-primary">
                                            {{ __('dashboard.continue_plan') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="quran-empty-state">
                                    <i class="bi bi-journal-text quran-empty-icon"></i>
                                    <h6>{{ __('dashboard.no_active_plans') }}</h6>
                                    <p>{{ __('dashboard.start_memorization_journey') }}</p>
                                    <a href="#" class="quran-btn quran-btn-primary">
                                        {{ __('dashboard.create_plan') }}
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Verse Widget -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="quran-verse-widget">
                <div class="quran-verse-content">
                    <div class="quran-verse-icon">
                        <i class="bi bi-quote"></i>
                    </div>
                    @php
                        $dailyVerse = $dailyVerse ?? null;
                    @endphp
                    <p class="quran-verse-arabic-text">
                        {{ $dailyVerse->arabic_text ?? 'وَذَكِّرْ فَإِنَّ الذِّكْرَىٰ تَنفَعُ الْمُؤْمِنِينَ' }}
                    </p>
                    <p class="quran-verse-translation-text">
                        {{ $dailyVerse->translation ?? 'And remind, for indeed, the reminder benefits the believers.' }}
                    </p>
                    <div class="quran-verse-reference">
                        <span>{{ $dailyVerse->surah_name ?? 'Adh-Dhariyat' }} ({{ $dailyVerse->ayah_number ?? '51:55' }})</span>
                        <button class="quran-btn-icon" onclick="copyVerse()" data-bs-toggle="tooltip" title="{{ __('dashboard.copy_verse') }}">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <button class="quran-btn-icon" onclick="shareVerse()" data-bs-toggle="tooltip" title="{{ __('dashboard.share_verse') }}">
                            <i class="bi bi-share"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Dashboard Specific CSS -->
<link rel="stylesheet" href="{{ asset('css/components/dashboard.css') }}">
<style>
    .quran-quick-actions {
        grid-template-columns: repeat(4, 1fr) !important;
    }
    @media (max-width: 1199.98px) {
        .quran-quick-actions {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }
    @media (max-width: 767.98px) {
        .quran-quick-actions {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Weekly Progress Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('weeklyProgressChart').getContext('2d');

        // Get data from PHP - FIXED SYNTAX
        @php
            $defaultWeeklyData = [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'reading' => [2, 3, 1, 4, 2, 5, 3],
                'memorization' => [1, 2, 0, 3, 1, 2, 1],
                'review' => [3, 2, 4, 3, 5, 4, 3]
            ];
            $weeklyData = isset($weeklyProgress) ? $weeklyProgress : $defaultWeeklyData;
        @endphp

        const weeklyData = @json($weeklyData);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: weeklyData.labels,
                datasets: [
                    {
                        label: '{{ __("dashboard.reading") }}',
                        data: weeklyData.reading,
                        borderColor: '#1B7340',
                        backgroundColor: 'rgba(27, 115, 64, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '{{ __("dashboard.memorization") }}',
                        data: weeklyData.memorization,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '{{ __("dashboard.review") }}',
                        data: weeklyData.review,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });

    // Copy Verse Function
    function copyVerse() {
        const verseText = document.querySelector('.quran-verse-arabic-text').innerText;
        const translationText = document.querySelector('.quran-verse-translation-text').innerText;
        const fullText = verseText + '\n\n' + translationText;

        navigator.clipboard.writeText(fullText).then(function() {
            window.showToast('{{ __("dashboard.verse_copied") }}', 'success');
        });
    }

    // Share Verse Function
    function shareVerse() {
        if (navigator.share) {
            navigator.share({
                title: '{{ __("dashboard.verse_of_the_day") }}',
                text: document.querySelector('.quran-verse-translation-text').innerText
            });
        } else {
            window.showToast('{{ __("dashboard.share_not_supported") }}', 'info');
        }
    }

    // Refresh Activity
    var refreshBtn = document.querySelector('[data-bs-toggle="tooltip"][title="{{ __("dashboard.refresh") }}"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.showLoading();
            setTimeout(function() {
                window.hideLoading();
                window.showToast('{{ __("dashboard.activity_refreshed") }}', 'success');
            }, 1000);
        });
    }

    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush
