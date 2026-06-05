{{-- resources/views/ayahs/show.blade.php --}}
@extends('layouts.app')

@section('title', __('ayahs.ayah_details'))

@section('content')
<div class="quran-dashboard">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1 text-zinc-900 dark:text-white font-bold">
                {{ $ayah->surah->name_simple }} - {{ __('ayahs.ayah') }} {{ $ayah->ayah_number }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-emerald-600 hover:text-emerald-700 text-decoration-none">{{ __('sidebar.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ayahs.index') }}" class="text-emerald-600 hover:text-emerald-700 text-decoration-none">{{ __('ayahs.ayahs') }}</a></li>
                    <li class="breadcrumb-item active text-zinc-500">{{ $ayah->surah->name_simple }} {{ $ayah->ayah_number }}</li>
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
                <span>{{ __('common.all') ?? 'All' }}</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Ayah Display -->
        <div class="col-lg-8">
            <!-- Ayah Card -->
            <div class="quran-card mb-4 border-0 shadow-sm overflow-hidden position-relative">
                <div class="absolute -right-16 -top-16 w-36 h-36 bg-emerald-500/5 rounded-full blur-2xl"></div>
                <div class="absolute -left-16 -bottom-16 w-36 h-36 bg-teal-500/5 rounded-full blur-2xl"></div>
                
                <div class="quran-card-body p-4 p-md-5">
                    <!-- Surah Info Header -->
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-zinc-100 dark:border-zinc-800">
                        <div class="relative d-inline-flex align-items-center justify-content-center w-12 h-12 select-none flex-shrink-0">
                            <svg class="absolute w-full h-full text-emerald-50 dark:text-emerald-950/40 fill-current stroke-emerald-600/50 dark:stroke-emerald-400/40 stroke-1" viewBox="0 0 24 24">
                                <path d="M12 2L15 5H19V9L22 12L19 15V19H15L12 22L9 19H5V15L2 12L5 9V5H9L12 2Z" />
                            </svg>
                            <span class="relative z-10 text-xs font-extrabold text-emerald-800 dark:text-emerald-400">
                                {{ $ayah->surah->id }}
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1 text-zinc-900 dark:text-white font-bold">{{ $ayah->surah->name_simple }}</h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="arabic-text text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $ayah->surah->name_arabic }}
                                </span>
                                <span class="text-zinc-300 dark:text-zinc-700 text-xs">•</span>
                                <span class="text-zinc-500 dark:text-zinc-400 text-xs font-semibold uppercase">
                                    {{ $ayah->surah->revelation_type }}
                                </span>
                            </div>
                        </div>
                        @if($ayah->sajda_flag)
                        <div class="ms-auto">
                            <span class="quran-table-badge warning py-1.5 px-3">
                                <i class="bi bi-star-fill text-warning me-1"></i> {{ __('ayahs.sajda_ayah') }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Ayah Arabic Text -->
                    <div class="bg-zinc-50 dark:bg-zinc-950/40 rounded-3xl p-5 md:p-8 mb-4 border border-zinc-100/50 dark:border-zinc-900/50 text-center relative">
                        <div class="arabic-text text-3xl md:text-4xl text-zinc-900 dark:text-white leading-[2.5] tracking-wide select-none" dir="rtl">
                            {{ $ayah->text_uthmani }}
                            <span class="arabic-text text-2xl md:text-3xl text-emerald-600 dark:text-emerald-400 mx-2 select-none">
                                ﴿{{ App\Helpers\QuranHelper::getArabicNumber($ayah->ayah_number) }}﴾
                            </span>
                        </div>
                    </div>

                    <!-- Ayah Controls -->
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-2 mt-4">
                        <button class="quran-btn-icon" onclick="copyAyah()" title="Copy Arabic Text">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <button class="quran-btn-icon" onclick="toggleBookmark()" title="Toggle Bookmark">
                            <i class="bi bi-bookmark{{ $userBookmark ? '-fill text-emerald-600' : '' }}" id="bookmarkIcon"></i>
                        </button>
                        <button class="quran-btn-icon" onclick="shareAyah()" title="Share Ayah">
                            <i class="bi bi-share"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Translations Section -->
            <div class="quran-card mb-4 border-0 shadow-sm">
                <div class="quran-card-header border-0 bg-transparent pb-0">
                    <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                        <i class="bi bi-translate me-2"></i>
                        {{ __('ayahs.translations') }}
                    </h6>
                </div>
                <div class="quran-card-body pt-3">
                    @forelse($ayah->translations as $translation)
                    <div class="mb-4 pb-3 border-bottom border-zinc-100 dark:border-zinc-800 last-border-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-500/10 font-bold px-2.5 py-1.5 text-[11px]">
                                {{ $translation->language->name }}
                            </span>
                            <span class="text-zinc-300 dark:text-zinc-700 text-xs">•</span>
                            <span class="text-zinc-500 dark:text-zinc-400 text-xs font-semibold">{{ $translation->translator_name }}</span>
                        </div>
                        <p class="text-zinc-700 dark:text-zinc-300 font-medium mb-0 leading-relaxed text-sm md:text-base {{ in_array($translation->language->code, ['ku', 'ar']) ? 'text-right' : 'text-left' }}" dir="{{ in_array($translation->language->code, ['ku', 'ar']) ? 'rtl' : 'ltr' }}">
                            {{ $translation->text }}
                        </p>
                    </div>
                    @empty
                    <div class="text-center py-4 text-zinc-500 dark:text-zinc-400 italic">
                        No translations available for this verse.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Tafsir Section -->
            <div class="quran-card border-0 shadow-sm">
                <div class="quran-card-header border-0 bg-transparent pb-0">
                    <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                        <i class="bi bi-book me-2"></i>
                        {{ __('ayahs.tafsir') }}
                    </h6>
                </div>
                <div class="quran-card-body pt-3">
                    @forelse($ayah->tafsirs as $tafsir)
                    <div class="mb-4 pb-3 border-bottom border-zinc-100 dark:border-zinc-800 last-border-0">
                        <h6 class="text-zinc-800 dark:text-zinc-200 font-bold mb-2 fs-6">{{ $tafsir->tafsirBook->name }}</h6>
                        <div class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed arabic-text p-3 bg-zinc-50 dark:bg-zinc-950/20 rounded-2xl border border-zinc-100/50 dark:border-zinc-900/50" dir="rtl" style="font-size: 17px; line-height: 1.8;">
                            {{ $tafsir->text }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-zinc-500 dark:text-zinc-400 italic">
                        No tafsir records available for this verse.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4 d-flex flex-column gap-4">
            <!-- Ayah Metadata Card -->
            <div class="quran-card border-0 shadow-sm">
                <div class="quran-card-header border-0 bg-transparent pb-0">
                    <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('ayahs.ayah_information') }}
                    </h6>
                </div>
                <div class="quran-card-body pt-3">
                    <div class="d-flex flex-col gap-3">
                        <div class="d-flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-500 dark:text-zinc-400 text-xs font-bold">
                                <i class="bi bi-book me-1.5 text-emerald-600"></i>{{ __('ayahs.surah') }}
                            </span>
                            <span class="text-zinc-800 dark:text-zinc-200 text-xs font-bold">
                                <a href="{{ route('surahs.show', $ayah->surah) }}" class="text-emerald-600 hover:text-emerald-700 text-decoration-none">
                                    {{ $ayah->surah->name_simple }} ({{ $ayah->surah->name_arabic }})
                                </a>
                            </span>
                        </div>

                        <div class="d-flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-500 dark:text-zinc-400 text-xs font-bold">
                                <i class="bi bi-hash me-1.5 text-emerald-600"></i>{{ __('ayahs.ayah_number') }}
                            </span>
                            <span class="text-zinc-800 dark:text-zinc-200 text-xs font-bold">{{ $ayah->ayah_number }}</span>
                        </div>

                        @if($ayah->page_number)
                        <div class="d-flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-500 dark:text-zinc-400 text-xs font-bold">
                                <i class="bi bi-file-text me-1.5 text-emerald-600"></i>{{ __('ayahs.page_number') }}
                            </span>
                            <span class="text-zinc-800 dark:text-zinc-200 text-xs font-bold">{{ $ayah->page_number }}</span>
                        </div>
                        @endif

                        @if($ayah->juz_number)
                        <div class="d-flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-500 dark:text-zinc-400 text-xs font-bold">
                                <i class="bi bi-grid-3x3 me-1.5 text-emerald-600"></i>{{ __('ayahs.juz') }}
                            </span>
                            <span class="text-zinc-800 dark:text-zinc-200 text-xs font-bold">{{ $ayah->juz_number }}</span>
                        </div>
                        @endif

                        @if($ayah->hizb_number)
                        <div class="d-flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800/60">
                            <span class="text-zinc-500 dark:text-zinc-400 text-xs font-bold">
                                <i class="bi bi-grid me-1.5 text-emerald-600"></i>{{ __('ayahs.hizb') }}
                            </span>
                            <span class="text-zinc-800 dark:text-zinc-200 text-xs font-bold">{{ $ayah->hizb_number }}</span>
                        </div>
                        @endif

                        <div class="d-flex items-center justify-between py-2">
                            <span class="text-zinc-500 dark:text-zinc-400 text-xs font-bold">
                                <i class="bi bi-check-circle me-1.5 text-emerald-600"></i>{{ __('ayahs.status') }}
                            </span>
                            <span>
                                @if($ayah->is_active)
                                <span class="text-success text-xs font-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('common.active') }}
                                </span>
                                @else
                                <span class="text-danger text-xs font-bold">
                                    <i class="bi bi-x-circle-fill me-1"></i>{{ __('common.inactive') }}
                                </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audio Files Card -->
            @if($ayah->audioFiles->count() > 0)
            <div class="quran-card border-0 shadow-sm">
                <div class="quran-card-header border-0 bg-transparent pb-0">
                    <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                        <i class="bi bi-headphones me-2"></i>
                        {{ __('ayahs.audio_recitations') }}
                    </h6>
                </div>
                <div class="quran-card-body pt-3">
                    <div class="d-flex flex-column gap-3">
                        @foreach($ayah->audioFiles as $audio)
                        <div class="d-flex align-items-center justify-content-between p-2.5 rounded-2xl bg-zinc-50 dark:bg-zinc-950/20 border border-zinc-100/50 dark:border-zinc-900/50">
                            <div>
                                <div class="text-zinc-800 dark:text-zinc-200 text-xs font-bold">{{ $audio->reciter->name }}</div>
                                <small class="text-zinc-500 dark:text-zinc-400 text-[10px] font-semibold">{{ $audio->reciter->style }}</small>
                            </div>
                            <button class="quran-btn-icon" onclick="playReciterAudio('{{ asset($audio->url) }}', this)" title="Play Recitation">
                                <i class="bi bi-play-fill text-lg"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Stats Card -->
            <div class="quran-card border-0 shadow-sm">
                <div class="quran-card-header border-0 bg-transparent pb-0">
                    <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                        <i class="bi bi-bar-chart me-2"></i>
                        {{ __('ayahs.quick_stats') }}
                    </h6>
                </div>
                <div class="quran-card-body pt-3">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-2.5 bg-zinc-50 dark:bg-zinc-950/20 rounded-2xl border border-zinc-100/50 dark:border-zinc-900/50">
                                <div class="text-zinc-800 dark:text-zinc-200 font-extrabold fs-5 mb-0.5">{{ $ayah->translations->count() }}</div>
                                <small class="text-zinc-500 dark:text-zinc-400 text-[10px] font-bold">{{ __('ayahs.translations') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2.5 bg-zinc-50 dark:bg-zinc-950/20 rounded-2xl border border-zinc-100/50 dark:border-zinc-900/50">
                                <div class="text-zinc-800 dark:text-zinc-200 font-extrabold fs-5 mb-0.5">{{ $ayah->tafsirs->count() }}</div>
                                <small class="text-zinc-500 dark:text-zinc-400 text-[10px] font-bold">{{ __('ayahs.tafsirs') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2.5 bg-zinc-50 dark:bg-zinc-950/20 rounded-2xl border border-zinc-100/50 dark:border-zinc-900/50">
                                <div class="text-zinc-800 dark:text-zinc-200 font-extrabold fs-5 mb-0.5">{{ $ayah->audioFiles->count() }}</div>
                                <small class="text-zinc-500 dark:text-zinc-400 text-[10px] font-bold">{{ __('ayahs.audio_files') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2.5 bg-zinc-50 dark:bg-zinc-950/20 rounded-2xl border border-zinc-100/50 dark:border-zinc-900/50">
                                <div class="text-zinc-800 dark:text-zinc-200 font-extrabold fs-5 mb-0.5">{{ $ayah->bookmarks->count() }}</div>
                                <small class="text-zinc-500 dark:text-zinc-400 text-[10px] font-bold">{{ __('ayahs.bookmarks') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Audio Player Context --}}
<audio id="globalAudioPlayer" style="display: none;"></audio>

@endsection

@push('scripts')
<script>
let currentPlayingBtn = null;
const audioPlayer = document.getElementById('globalAudioPlayer');

function copyAyah() {
    const text = `{{ $ayah->text_uthmani }}`;
    navigator.clipboard.writeText(text).then(() => {
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
            icon.classList.add('text-emerald-600');
        } else {
            icon.classList.remove('bi-bookmark-fill');
            icon.classList.remove('text-emerald-600');
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

function playReciterAudio(url, btn) {
    if (currentPlayingBtn === btn && !audioPlayer.paused) {
        audioPlayer.pause();
        btn.querySelector('i').className = 'bi bi-play-fill text-lg';
        return;
    }

    if (currentPlayingBtn) {
        currentPlayingBtn.querySelector('i').className = 'bi bi-play-fill text-lg';
    }

    audioPlayer.src = url;
    audioPlayer.play();
    btn.querySelector('i').className = 'bi bi-pause-fill text-lg';
    currentPlayingBtn = btn;

    audioPlayer.onended = () => {
        btn.querySelector('i').className = 'bi bi-play-fill text-lg';
        currentPlayingBtn = null;
    };
}
</script>
@endpush
