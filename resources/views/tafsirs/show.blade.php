{{-- resources/views/tafsirs/show.blade.php --}}
@extends('layouts.app')

@section('title', $tafsir->tafsirBook->name . ' - ' . $tafsir->ayah->surah->name_ar)
@section('page-title', $tafsir->tafsirBook->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tafsirs.index') }}">{{ __('tafsirs.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('tafsir-books.show', $tafsir->tafsirBook) }}">{{ $tafsir->tafsirBook->name }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        {{ $tafsir->ayah->surah->name_ar }} {{ $tafsir->ayah->ayah_number }}
    </li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="quran-surah-number">{{ $tafsir->ayah->surah->number }}</span>
                <h1 class="h4 mb-0">{{ $tafsir->ayah->surah->name_ar }}</h1>
            </div>
            <div class="text-muted">
                {{ __('tafsirs.ayah') }} {{ $tafsir->ayah->ayah_number }} - 
                <a href="{{ route('tafsir-books.show', $tafsir->tafsirBook) }}" class="text-decoration-none">
                    {{ $tafsir->tafsirBook->name }}
                </a>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('tafsirs.edit', $tafsir) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('tafsir-books.show', $tafsir->tafsirBook) }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('tafsirs.actions.back') }}
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
                        {{ __('tafsirs.original_ayah') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-verse-arabic-text p-4 bg-light rounded-3" 
                         style="font-family: var(--font-arabic); font-size: 24px; line-height: 2;">
                        {{ $tafsir->ayah->text_uthmani }}
                        <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($tafsir->ayah->ayah_number) }}</span>
                    </div>
                </div>
            </div>

            <!-- Tafsir Card -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-book me-2"></i>
                        {{ __('tafsirs.tafsir_content') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="quran-table-badge {{ $tafsir->is_active ? 'success' : 'danger' }}">
                            {{ $tafsir->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                    </div>
                </div>
                <div class="quran-card-body">
                    <div class="quran-description" style="font-size: 16px; line-height: 1.9;">
                        {!! nl2br(e($tafsir->content)) !!}
                    </div>

                    @if($tafsir->source_reference)
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            <i class="bi bi-link-45deg me-1"></i>
                            {{ __('tafsirs.fields.source') }}: {{ $tafsir->source_reference }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Book Info -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('tafsirs.book_info') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="quran-plan-icon" style="width: 48px; height: 48px;">
                            <i class="bi bi-book"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $tafsir->tafsirBook->name }}</h6>
                            <p class="text-muted small mb-0">
                                {{ $tafsir->tafsirBook->author ?: __('tafsir_books.unknown_author') }}
                            </p>
                        </div>
                    </div>

                    @if($tafsir->tafsirBook->short_description)
                    <p class="small text-muted">{{ $tafsir->tafsirBook->short_description }}</p>
                    @endif

                    <a href="{{ route('tafsir-books.show', $tafsir->tafsirBook) }}" class="btn btn-link btn-sm p-0">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        {{ __('tafsirs.view_all_tafsirs') }}
                    </a>
                </div>
            </div>

            <!-- Other Tafsirs -->
            @if($otherTafsirs->count() > 0)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-bookshelf me-2"></i>
                        {{ __('tafsirs.other_tafsirs') }}
                    </h6>
                </div>
                <div class="quran-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($otherTafsirs as $other)
                        <a href="{{ route('tafsirs.show', $other) }}" 
                           class="list-group-item list-group-item-action bg-transparent">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <strong>{{ $other->tafsirBook->name }}</strong>
                                    <small class="d-block text-muted">
                                        {{ Str::limit($other->short_content ?: $other->content, 50) }}
                                    </small>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection