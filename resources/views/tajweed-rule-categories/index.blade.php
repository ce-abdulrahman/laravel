{{-- resources/views/tajweed-rule-categories/index.blade.php --}}
@extends('layouts.app')

@section('title', __('tajweed_categories.titles.index'))
@section('page-title', __('tajweed_categories.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('tajweed_categories.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tajweed_categories.titles.index') }}</h1>
            <div class="text-muted">{{ __('tajweed_categories.hints.index') }}</div>
        </div>
        @if(auth()->user()?->role === 'admin')
        <div class="d-flex gap-2">
            <a href="{{ route('tajweed-rule-categories.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tajweed_categories.actions.create') }}
            </a>
        </div>
        @endif
    </div>

    {{-- Stats --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_categories.stats.total') }}</div>
                        <div class="quran-stat-value">{{ $stats['total'] }}</div>
                    </div>
                    <div class="quran-stat-icon"><i class="bi bi-folder"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_categories.stats.active') }}</div>
                        <div class="quran-stat-value">{{ $stats['active'] }}</div>
                    </div>
                    <div class="quran-stat-icon"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_categories.stats.inactive') }}</div>
                        <div class="quran-stat-value">{{ $stats['inactive'] }}</div>
                    </div>
                    <div class="quran-stat-icon"><i class="bi bi-x-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('tajweed-rule-categories.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('tajweed_categories.fields.is_active') }}</label>
                        <select name="status" class="quran-form-select">
                            <option value="">{{ __('common.all') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('common.active') }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('common.inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="quran-form-label">{{ __('common.search') }}</label>
                        <input type="text" name="search" class="quran-form-control"
                               placeholder="{{ __('tajweed_categories.placeholders.search') }}"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Category Cards --}}
    <div class="row g-4">
        @forelse($categories as $cat)
        <div class="col-md-6 col-xl-4">
            <div class="quran-card h-100">
                <div class="quran-card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="quran-plan-icon" style="width:48px;height:48px;font-size:1.4rem;flex-shrink:0;">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="mb-1 text-truncate">{{ $cat->name_ku ?: $cat->name }}</h6>
                            @if($cat->name_ar)
                            <div class="arabic-text text-muted small" dir="rtl">{{ $cat->name_ar }}</div>
                            @endif
                            <small class="text-muted">{{ $cat->name }}</small>
                        </div>
                        <span class="quran-table-badge {{ $cat->is_active ? 'success' : 'danger' }} flex-shrink-0">
                            {{ $cat->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                    </div>

                    @if($cat->description_ku ?: $cat->description)
                    <p class="text-muted small mb-3" style="font-family:'Cairo';">
                        {{ Str::limit($cat->description_ku ?: $cat->description, 120) }}
                    </p>
                    @endif

                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="quran-table-badge info">
                                <i class="bi bi-palette me-1"></i>
                                {{ $cat->tajweed_rules_count }} {{ __('tajweed_categories.table.rules_count') }}
                            </span>
                            <span class="quran-table-badge secondary ms-1">
                                #{{ $cat->order }}
                            </span>
                        </div>
                        <div class="quran-table-actions">
                            <a href="{{ route('tajweed-rule-categories.show', $cat) }}"
                               class="quran-table-action-btn view"
                               data-bs-toggle="tooltip" title="{{ __('common.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()?->role === 'admin')
                            <a href="{{ route('tajweed-rule-categories.edit', $cat) }}"
                               class="quran-table-action-btn edit"
                               data-bs-toggle="tooltip" title="{{ __('common.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('tajweed-rule-categories.destroy', $cat) }}" method="POST"
                                  onsubmit="return confirm('{{ __('tajweed_categories.messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="quran-table-action-btn delete"
                                        data-bs-toggle="tooltip" title="{{ __('common.delete') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="quran-table-empty">
                <i class="bi bi-folder"></i>
                <h6>{{ __('tajweed_categories.empty.title') }}</h6>
                <p>{{ __('tajweed_categories.empty.message') }}</p>
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('tajweed-rule-categories.create') }}" class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('tajweed_categories.actions.create_first') }}
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($categories->hasPages())
    <div class="mt-4">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
