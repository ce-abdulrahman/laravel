{{-- resources/views/adhkar-categories/show.blade.php --}}
@extends('layouts.app')

@section('title', __('adhkar_categories.titles.show'))
@section('page-title', __('adhkar_categories.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('adhkar-categories.index') }}">{{ __('adhkar_categories.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('adhkar_categories.titles.show') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('adhkar_categories.titles.show') }}</h1>
            <div class="text-muted">{{ __('adhkar_categories.hints.show') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('adhkar-categories.edit', $category) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('adhkar_categories.actions.edit') }}
            </a>
            <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('adhkar_categories.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Card --}}
        <div class="col-lg-8">
            <!-- Translations Card -->
            <x-translations.show-tabs :model="$category" :active-languages="$activeLanguages" />

            {{-- Adhkars Link Card --}}
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-list-task me-2" style="color: #1B7340;"></i>
                        {{ __('adhkar_categories.table.adhkars_count') }}
                    </h5>
                </div>
                <div class="quran-card-body text-center py-4">
                    <p class="text-muted mb-3">{{ __('adhkar_categories.no_adhkars') }}</p>
                    <a href="{{ route('adhkars.index') }}?category_id={{ $category->id }}"
                       class="quran-btn quran-btn-outline-primary">
                        <i class="bi bi-arrow-right me-1"></i>
                        {{ __('adhkar_categories.actions.view_adhkars') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('common.details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <dl class="row g-2 mb-0">
                        {{-- Status --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('adhkar_categories.table.status') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            @if($category->is_active)
                                <span class="quran-table-badge success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('adhkar_categories.status.active') }}
                                </span>
                            @else
                                <span class="quran-table-badge danger">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('adhkar_categories.status.inactive') }}
                                </span>
                            @endif
                        </dd>

                        {{-- Order --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('adhkar_categories.fields.order') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="surah-number" style="width:36px;height:36px;font-size:0.85rem;display:inline-flex;">
                                {{ $category->order }}
                            </span>
                        </dd>

                        @if($category->icon)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('adhkar_categories.fields.icon') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <code class="badge bg-light text-dark border" style="font-size: 0.85rem;">{{ $category->icon }}</code>
                        </dd>
                        @endif

                        {{-- Created At --}}
                        @if($category->created_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.created_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $category->created_at->format('Y-m-d') }}</dd>
                        @endif

                        {{-- Updated At --}}
                        @if($category->updated_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.updated_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $category->updated_at->diffForHumans() }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="quran-card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('adhkar-categories.edit', $category) }}" class="quran-btn quran-btn-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('adhkar_categories.actions.edit') }}
                        </a>
                        <form method="POST" action="{{ route('adhkar-categories.destroy', $category) }}"
                              onsubmit="return confirm('{{ __('adhkar_categories.messages.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="quran-btn quran-btn-danger w-100">
                                <i class="bi bi-trash me-1"></i>{{ __('adhkar_categories.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
