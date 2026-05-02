{{-- resources/views/ayahs/show.blade.php --}}
@extends('layouts.app')

@section('title', __('ayahs.ayah_details'))

@section('content')
<div class="quran-content-container">
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">
                {{ $ayah->surah->name_simple }} - {{ __('ayahs.ayah') }} {{ $ayah->ayah_number }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('sidebar.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ayahs.index') }}">{{ __('ayahs.ayahs') }}</a></li>
                    <li class="breadcrumb-item active">{{ $ayah->surah->name_simple }} {{ $ayah->ayah_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @can('update', $ayah)
            <a href="{{ route('ayahs.edit', $ayah) }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-pencil"></i>
                <span>{{ __('common.edit') }}</span>
            </a>
            @endcan
            <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-list"></i>
                <span>{{ __('common.all_ayahs') }}</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Ayah Display --}}
        <div class="col-lg-8">
            {{-- Ayah Card --}}
            <div class="quran-card mb-4">
                <div class="quran-card-body">
                    {{-- Surah Info Header --}}
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="quran-surah-number">
                            {{ $ayah->surah->id }}
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $ayah->surah->name_simple }}</h5>
                            <div class="d-flex gap-3">
                                <span class="arabic-text" style="font-size: 18px;">
                                    {{ $ayah->surah->name_arabic }}
                                </span>
                                <span class="text-muted">|</span>
                                <span class="text-muted">{{ $ayah->surah->revelation_type }}</span>
                            </div>
                        </div>
                        @if($ayah->sajda_flag)
                        <div class="ms-auto">
                            <span class="quran-table-badge warning">
                                <i class="bi bi-star-fill"></i> {{ __('ayahs.sajda_ayah') }}
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- Ayah Arabic Text --}}
                    <div class="quran-verse-arabic-text mb-4 p-4 bg-light rounded-3"
                         style="font-family: var(--font-arabic); font-size: 28px; line-height: 2.2; text-align: center;">
                        {{ $ayah->text_uthmani }}
                        <span class="ayah-end-mark" style="font-size: 24px; margin: 0 8px;">
                            {{ App\Helpers\QuranHelper::getAyahEndMark($ayah->ayah_number) }}
                        </span>
                    </div>

                    {{-- Ayah Controls --}}
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
                        <button class="quran-audio-btn" onclick="playAudio()">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <button class="quran-audio-btn" onclick="copyAyah()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <button class="quran-audio-btn" onclick="toggleBookmark()">
                            <i class="bi bi-bookmark{{ $userBookmark ? '-fill' : '' }}" id="bookmarkIcon"></i>
                        </button>
                        <button class="quran-audio-btn" onclick="shareAyah()">
                            <i class="bi bi-share"></i>
                        </button>
                    </div>

                    {{-- Navigation --}}
                    <div class="d-flex align-items-center justify-content-between">
                        @if($prevAyah)
                        <a href="{{ route('ayahs.show', $prevAyah) }}" class="quran-btn quran-btn-outline-primary">
                            <i class="bi bi-chevron-right"></i>
                            <span>{{ __('ayahs.previous_ayah') }}</span>
                        </a>
                        @else
                        <span></span>
                        @endif

                        <span class="text-muted">
                            {{ $ayah->ayah_number }} / {{ $ayah->surah->total_verses }}
                        </span>

                        @if($nextAyah)
                        <a href="{{ route('ayahs.show', $nextAyah) }}" class="quran-btn quran-btn-outline-primary">
                            <span>{{ __('ayahs.next_ayah') }}</span>
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        @else
                        <span></span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Translations Section --}}
            @if($ayah->translations->count() > 0)
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-translate me-2"></i>
                        {{ __('ayahs.translations') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    @foreach($ayah->translations as $translation)
                    <div class="quran-translation-item mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fw-semibold">{{ $translation->language->name }}</span>
                            <span class="text-muted">-</span>
                            <span class="text-muted">{{ $translation->translator_name }}</span>
                        </div>
                        <p class="quran-translation-text mb-0">{{ $translation->text }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tafsir Section --}}
            @if($ayah->tafsirs->count() > 0)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-book me-2"></i>
                        {{ __('ayahs.tafsir') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    @foreach($ayah->tafsirs as $tafsir)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2">{{ $tafsir->tafsirBook->name }}</h6>
                        <div class="quran-description">
                            {{ Str::limit($tafsir->text, 500) }}
                        </div>
                        @if(strlen($tafsir->text) > 500)
                        <button class="btn btn-link btn-sm p-0 mt-2" onclick="showFullTafsir({{ $tafsir->id }})">
                            {{ __('common.read_more') }}
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar Information --}}
        <div class="col-lg-4">
            {{-- Ayah Details --}}
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('ayahs.ayah_information') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-book me-2"></i>
                            {{ __('ayahs.surah') }}
                        </div>
                        <div class="quran-detail-value">
                            <a href="{{ route('surahs.show', $ayah->surah) }}" class="text-decoration-none">
                                {{ $ayah->surah->name_simple }} ({{ $ayah->surah->name_arabic }})
                            </a>
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-hash me-2"></i>
                            {{ __('ayahs.ayah_number') }}
                        </div>
                        <div class="quran-detail-value">{{ $ayah->ayah_number }}</div>
                    </div>

                    @if($ayah->page_number)
                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-file-text me-2"></i>
                            {{ __('ayahs.page_number') }}
                        </div>
                        <div class="quran-detail-value">{{ $ayah->page_number }}</div>
                    </div>
                    @endif

                    @if($ayah->juz_number)
                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-grid-3x3 me-2"></i>
                            {{ __('ayahs.juz') }}
                        </div>
                        <div class="quran-detail-value">{{ $ayah->juz_number }}</div>
                    </div>
                    @endif

                    @if($ayah->hizb_number)
                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-grid me-2"></i>
                            {{ __('ayahs.hizb') }}
                        </div>
                        <div class="quran-detail-value">{{ $ayah->hizb_number }}</div>
                    </div>
                    @endif

                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ __('ayahs.status') }}
                        </div>
                        <div class="quran-detail-value">
                            @if($ayah->is_active)
                            <span class="text-success">
                                <i class="bi bi-check-circle-fill"></i> {{ __('common.active') }}
                            </span>
                            @else
                            <span class="text-danger">
                                <i class="bi bi-x-circle-fill"></i> {{ __('common.inactive') }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Audio Files --}}
            @if($ayah->audioFiles->count() > 0)
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-headphones me-2"></i>
                        {{ __('ayahs.audio_recitations') }}
                    </h6>
                </div>
                <div class="quran-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($ayah->audioFiles as $audio)
                        <div class="list-group-item bg-transparent d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $audio->reciter->name }}</div>
                                <small class="text-muted">{{ $audio->reciter->style }}</small>
                            </div>
                            <button class="quran-audio-btn" onclick="playReciterAudio({{ $audio->id }})">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick Stats --}}
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-bar-chart me-2"></i>
                        {{ __('ayahs.quick_stats') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <h3 class="mb-1">{{ $ayah->translations->count() }}</h3>
                                <small class="text-muted">{{ __('ayahs.translations') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <h3 class="mb-1">{{ $ayah->tafsirs->count() }}</h3>
                                <small class="text-muted">{{ __('ayahs.tafsirs') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <h3 class="mb-1">{{ $ayah->audioFiles->count() }}</h3>
                                <small class="text-muted">{{ __('ayahs.audio_files') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <h3 class="mb-1">{{ $ayah->bookmarks->count() }}</h3>
                                <small class="text-muted">{{ __('ayahs.bookmarks') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function copyAyah() {
    const text = `{{ $ayah->text_uthmani }}`;
    navigator.clipboard.writeText(text).then(() => {
        // Show success toast
        alert('{{ __("ayahs.ayah_copied") }}');
    });
}

function toggleBookmark() {
    @auth
    fetch('{{ route("bookmarks.toggle", $ayah) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const icon = document.getElementById('bookmarkIcon');
        if (data.bookmarked) {
            icon.classList.remove('bi-bookmark');
            icon.classList.add('bi-bookmark-fill');
        } else {
            icon.classList.remove('bi-bookmark-fill');
            icon.classList.add('bi-bookmark');
        }
    });
    @else
    window.location.href = '{{ route("login") }}';
    @endauth
}

function shareAyah() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $ayah->surah->name_simple }} - {{ __("ayahs.ayah") }} {{ $ayah->ayah_number }}',
            text: '{{ Str::limit($ayah->text_uthmani, 100) }}',
            url: window.location.href
        });
    } else {
        copyAyah();
        alert('{{ __("ayahs.link_copied") }}');
    }
}

function playAudio() {
    // Implement audio player functionality
    const audioPlayer = document.querySelector('.quran-audio-player');
    if (audioPlayer) {
        // Update audio player with current ayah
    }
}

function playReciterAudio(audioId) {
    // Implement reciter audio playback
}

function showFullTafsir(tafsirId) {
    // Implement full tafsir modal
}
</script>
@endpush
