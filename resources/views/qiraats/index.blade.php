{{-- resources/views/qiraats/index.blade.php --}}
@extends('layouts.app')

@section('title', __('qiraats.titles.index'))
@section('page-title', __('qiraats.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('qiraats.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('qiraats.titles.index') }}</h1>
            <div class="text-muted">{{ __('qiraats.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('qiraat-texts.create') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('qiraat_texts.actions.create') }}
            </a>
            <a href="{{ route('qiraats.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('qiraats.actions.create') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('qiraats.total_qiraats') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_qiraats'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('qiraats.active_qiraats') }}</div>
                        <div class="quran-stat-value">{{ $stats['active_qiraats'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('qiraats.total_texts') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_texts']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('qiraats.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('qiraats.filter_by_riwayah') }}</label>
                        <select name="riwayah" class="quran-form-select">
                            <option value="">{{ __('qiraats.all_riwayahs') }}</option>
                            @foreach($riwayahs as $riwayah)
                            <option value="{{ $riwayah }}" {{ request('riwayah') == $riwayah ? 'selected' : '' }}>
                                {{ $riwayah }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('qiraats.filter_by_status') }}</label>
                        <select name="status" class="quran-form-select">
                            <option value="">{{ __('qiraats.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                {{ __('common.active') }}
                            </option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                {{ __('common.inactive') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('qiraats.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('qiraats.search_placeholder') }}" 
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

    <!-- Qiraats Grid -->
    <div class="row g-4">
        @forelse($qiraats as $qiraat)
        <div class="col-md-6 col-lg-4">
            <div class="quran-card h-100">
                <div class="quran-card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="quran-plan-icon" style="width: 48px; height: 48px;">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $qiraat->name }}</h6>
                            @if($qiraat->riwayah)
                            <span class="quran-table-badge info">{{ $qiraat->riwayah }}</span>
                            @endif
                        </div>
                    </div>

                    @if($qiraat->description)
                    <p class="text-muted small mb-3">{{ Str::limit($qiraat->description, 100) }}</p>
                    @endif

                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="quran-table-badge {{ $qiraat->is_active ? 'success' : 'danger' }}">
                                {{ $qiraat->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                            <small class="text-muted ms-2">
                                <i class="bi bi-journal-text me-1"></i>
                                {{ $qiraat->texts_count }} {{ __('qiraats.texts') }}
                            </small>
                        </div>
                        <div class="quran-table-actions">
                            <a href="{{ route('qiraats.show', $qiraat) }}" 
                               class="quran-table-action-btn view" 
                               data-bs-toggle="tooltip" title="{{ __('common.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()?->role === 'admin')
                            <a href="{{ route('qiraats.edit', $qiraat) }}" 
                               class="quran-table-action-btn edit" 
                               data-bs-toggle="tooltip" title="{{ __('common.edit') }}">
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
                <i class="bi bi-book"></i>
                <h6>{{ __('qiraats.no_qiraats_found') }}</h6>
                <p>{{ __('qiraats.no_qiraats_message') }}</p>
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('qiraats.create') }}" class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('qiraats.actions.create_first') }}
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($qiraats->hasPages())
    <div class="mt-4">
        {{ $qiraats->links() }}
    </div>
    @endif
</div>
@endsection