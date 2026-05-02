{{-- resources/views/bookmarks/show.blade.php --}}
@extends('layouts.app')

@section('title', __('bookmarks.titles.show'))
@section('page-title', $bookmark->ayah->surah->name_ar)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('bookmarks.index') }}">{{ __('bookmarks.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">
        {{ $bookmark->ayah->surah->name_ar }} {{ $bookmark->ayah->ayah_number }}
    </li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="quran-surah-number">{{ $bookmark->ayah->surah->number }}</span>
                <h1 class="h4 mb-0">{{ $bookmark->ayah->surah->name_ar }}</h1>
            </div>
            <div class="text-muted">
                {{ __('bookmarks.ayah') }} {{ $bookmark->ayah->ayah_number }}
                <span class="mx-2">•</span>
                <i class="bi bi-bookmark-fill text-primary me-1"></i>
                {{ __('bookmarks.bookmarked') }} {{ $bookmark->created_at->diffForHumans() }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('bookmarks.edit', $bookmark) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            <a href="{{ route('bookmarks.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('bookmarks.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ayah Card -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('bookmarks.ayah_text') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-verse-arabic-text p-4 bg-light rounded-3" 
                         style="font-family: var(--font-arabic); font-size: 28px; line-height: 2.2;">
                        {{ $bookmark->ayah->text_uthmani }}
                        <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($bookmark->ayah->ayah_number) }}</span>
                    </div>
                </div>
            </div>

            <!-- Note Card -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-pencil me-2"></i>
                        {{ __('bookmarks.fields.note') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    @if($bookmark->note)
                    <div class="quran-description">{{ $bookmark->note }}</div>
                    @else
                    <p class="text-muted">{{ __('bookmarks.no_note') }}</p>
                    <a href="{{ route('bookmarks.edit', $bookmark) }}" class="quran-btn quran-btn-outline-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('bookmarks.actions.add_note') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Navigation -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-arrow-left-right me-2"></i>
                        {{ __('bookmarks.navigation') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="d-flex justify-content-between">
                        @if($prevAyah)
                        <a href="{{ route('ayahs.show', $prevAyah) }}" class="quran-btn quran-btn-outline-primary">
                            <i class="bi bi-chevron-right"></i>
                            {{ __('bookmarks.previous_ayah') }}
                        </a>
                        @else
                        <span></span>
                        @endif

                        <a href="{{ route('ayahs.show', $bookmark->ayah) }}" class="quran-btn quran-btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            {{ __('bookmarks.view_ayah') }}
                        </a>

                        @if($nextAyah)
                        <a href="{{ route('ayahs.show', $nextAyah) }}" class="quran-btn quran-btn-outline-primary">
                            {{ __('bookmarks.next_ayah') }}
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        @else
                        <span></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-gear me-2"></i>
                        {{ __('common.actions') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="quran-btn quran-btn-outline-primary" 
                                onclick="copyAyah()">
                            <i class="bi bi-clipboard me-1"></i>
                            {{ __('bookmarks.actions.copy_ayah') }}
                        </button>
                        <button type="button" class="quran-btn quran-btn-outline-primary toggle-favorite"
                                data-ayah-id="{{ $bookmark->ayah_id }}"
                                data-favorited="{{ $isFavorite ? 'true' : 'false' }}">
                            <i class="bi bi-heart{{ $isFavorite ? '-fill' : '' }} me-1"></i>
                            <span>{{ $isFavorite ? __('favorites.actions.remove') : __('favorites.actions.add') }}</span>
                        </button>
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
    const text = `{{ str_replace("'", "\'", $bookmark->ayah->text_uthmani) }}`;
    navigator.clipboard.writeText(text).then(() => {
        alert('{{ __("bookmarks.messages.copied") }}');
    });
}

// Toggle favorite
document.querySelector('.toggle-favorite')?.addEventListener('click', function() {
    const btn = this;
    const ayahId = btn.dataset.ayahId;
    
    fetch('{{ route("favorites.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ayah_id: ayahId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const icon = btn.querySelector('i');
            const span = btn.querySelector('span');
            if (data.favorited) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                span.textContent = '{{ __("favorites.actions.remove") }}';
            } else {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                span.textContent = '{{ __("favorites.actions.add") }}';
            }
        }
    });
});
</script>
@endpush