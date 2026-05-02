{{-- resources/views/tafsir-books/show.blade.php --}}
@extends('layouts.app')

@section('title', $tafsirBook->name)
@section('page-title', $tafsirBook->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tafsir-books.index') }}">{{ __('tafsir_books.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $tafsirBook->name }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="quran-plan-icon" style="width: 56px; height: 56px;">
                    <i class="bi bi-book" style="font-size: 24px;"></i>
                </div>
                <div>
                    <h1 class="h4 mb-1">{{ $tafsirBook->name }}</h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-person me-1"></i>
                        {{ $tafsirBook->author ?: __('tafsir_books.unknown_author') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('tafsirs.create', ['tafsir_book_id' => $tafsirBook->id]) }}" class="quran-btn quran-btn-success">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tafsirs.actions.add_tafsir') }}
            </a>
            <a href="{{ route('tafsir-books.edit', $tafsirBook) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('tafsir-books.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('tafsir_books.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Book Details -->
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('tafsir_books.details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tafsir_books.fields.language') }}</label>
                        <div class="quran-detail-value">
                            @if($tafsirBook->language_code)
                            <span class="quran-table-badge info">
                                {{ $languages[$tafsirBook->language_code] ?? $tafsirBook->language_code }}
                            </span>
                            @else
                            —
                            @endif
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tafsir_books.fields.source') }}</label>
                        <div class="quran-detail-value">{{ $tafsirBook->source ?: '—' }}</div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tafsir_books.fields.status') }}</label>
                        <div class="quran-detail-value">
                            <span class="quran-table-badge {{ $tafsirBook->is_active ? 'success' : 'danger' }}">
                                {{ $tafsirBook->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tafsir_books.fields.total_tafsirs') }}</label>
                        <div class="quran-detail-value">
                            <strong>{{ $tafsirBook->tafsirs->count() }}</strong> {{ __('tafsir_books.tafsirs') }}
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tafsir_books.fields.created_at') }}</label>
                        <div class="quran-detail-value">{{ $tafsirBook->created_at->format('Y-m-d') }}</div>
                    </div>
                </div>
            </div>

            @if($tafsirBook->short_description)
            <div class="quran-card mt-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-card-text me-2"></i>
                        {{ __('tafsir_books.fields.description') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-description">{{ $tafsirBook->short_description }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Tafsirs List -->
        <div class="col-lg-8">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('tafsir_books.tafsirs_list') }}
                    </h5>
                </div>
                <div class="quran-table-container">
                    <table class="quran-table quran-table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('tafsirs.fields.surah_ayah') }}</th>
                                <th>{{ __('tafsirs.fields.content') }}</th>
                                <th class="text-end">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tafsirs as $tafsir)
                            <tr>
                                <td>
                                    <a href="{{ route('ayahs.show', $tafsir->ayah) }}" class="text-decoration-none">
                                        {{ $tafsir->ayah->surah->name_ar }} 
                                        ({{ $tafsir->ayah->ayah_number }})
                                    </a>
                                </td>
                                <td>
                                    <div class="tafsir-excerpt">
                                        {{ $tafsir->short_content ?: Str::limit($tafsir->content, 100) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="quran-table-actions justify-content-end">
                                        <a href="{{ route('tafsirs.show', $tafsir) }}" 
                                           class="quran-table-action-btn view">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('tafsirs.edit', $tafsir) }}" 
                                           class="quran-table-action-btn edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3">
                                    <div class="quran-table-empty">
                                        <i class="bi bi-journal-x"></i>
                                        <h6>{{ __('tafsir_books.no_tafsirs_yet') }}</h6>
                                        @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('tafsirs.create', ['tafsir_book_id' => $tafsirBook->id]) }}" 
                                           class="quran-btn quran-btn-primary mt-3">
                                            <i class="bi bi-plus-lg me-1"></i>
                                            {{ __('tafsirs.actions.add_first') }}
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($tafsirs->hasPages())
                <div class="card-footer">
                    {{ $tafsirs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection