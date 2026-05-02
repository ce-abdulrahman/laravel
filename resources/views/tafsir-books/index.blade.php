{{-- resources/views/tafsir-books/index.blade.php --}}
@extends('layouts.app')

@section('title', __('tafsir_books.titles.index'))
@section('page-title', __('tafsir_books.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('tafsir_books.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tafsir_books.titles.index') }}</h1>
            <div class="text-muted">{{ __('tafsir_books.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('tafsir-books.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tafsir_books.actions.create') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tafsir_books.total_books') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_books'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-bookshelf"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tafsir_books.total_tafsirs') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_tafsirs']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tafsir_books.active_books') }}</div>
                        <div class="quran-stat-value">{{ $stats['active_books'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('tafsir-books.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tafsir_books.filter_by_language') }}</label>
                        <select name="language_code" class="quran-form-select">
                            <option value="">{{ __('tafsir_books.all_languages') }}</option>
                            @foreach($languages as $code => $name)
                            <option value="{{ $code }}" {{ request('language_code') == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tafsir_books.filter_by_status') }}</label>
                        <select name="status" class="quran-form-select">
                            <option value="">{{ __('tafsir_books.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                {{ __('common.active') }}
                            </option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                {{ __('common.inactive') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('tafsir_books.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('tafsir_books.search_placeholder') }}" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="row g-4">
        @forelse($tafsirBooks as $book)
        <div class="col-md-6 col-lg-4">
            <div class="quran-card h-100">
                <div class="quran-card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="quran-plan-icon" style="width: 56px; height: 56px;">
                            <i class="bi bi-book" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $book->name }}</h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-person me-1"></i>
                                {{ $book->author ?: __('tafsir_books.unknown_author') }}
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                @if($book->language_code)
                                <span class="quran-table-badge info">
                                    {{ $languages[$book->language_code] ?? $book->language_code }}
                                </span>
                                @endif
                                <span class="quran-table-badge {{ $book->is_active ? 'success' : 'danger' }}">
                                    {{ $book->is_active ? __('common.active') : __('common.inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($book->short_description)
                    <p class="text-muted small mb-3">{{ Str::limit($book->short_description, 100) }}</p>
                    @endif

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small text-muted">
                            <i class="bi bi-journal-text me-1"></i>
                            {{ $book->tafsirs_count }} {{ __('tafsir_books.tafsirs') }}
                        </span>
                        <div class="quran-table-actions">
                            <a href="{{ route('tafsir-books.show', $book) }}" 
                               class="quran-table-action-btn view" 
                               data-bs-toggle="tooltip" 
                               title="{{ __('common.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()?->role === 'admin')
                            <a href="{{ route('tafsir-books.edit', $book) }}" 
                               class="quran-table-action-btn edit" 
                               data-bs-toggle="tooltip" 
                               title="{{ __('common.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="quran-table-empty">
                <i class="bi bi-bookshelf"></i>
                <h6>{{ __('tafsir_books.no_books_found') }}</h6>
                <p>{{ __('tafsir_books.no_books_message') }}</p>
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('tafsir-books.create') }}" class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('tafsir_books.actions.create_first') }}
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($tafsirBooks->hasPages())
    <div class="mt-4">
        <div class="quran-pagination">
            {{ $tafsirBooks->links() }}
        </div>
    </div>
    @endif
</div>
@endsection