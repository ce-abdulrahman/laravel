{{-- resources/views/reciters/index.blade.php --}}
@extends('layouts.app')

@section('title', __('reciters.titles.index'))
@section('page-title', __('reciters.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('reciters.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('reciters.titles.index') }}</h1>
            <div class="text-muted">{{ __('reciters.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('audio-files.create') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-headphones me-1"></i>
                {{ __('audio_files.actions.upload') }}
            </a>
            <a href="{{ route('reciters.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-person-plus me-1"></i>
                {{ __('reciters.actions.create') }}
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
                        <div class="quran-stat-label">{{ __('reciters.total_reciters') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_reciters'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-mic"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reciters.active_reciters') }}</div>
                        <div class="quran-stat-value">{{ $stats['active_reciters'] }}</div>
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
                        <div class="quran-stat-label">{{ __('reciters.total_audio_files') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_audio_files']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-file-music"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('reciters.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('reciters.filter_by_language') }}</label>
                        <select name="language" class="quran-form-select">
                            <option value="">{{ __('reciters.all_languages') }}</option>
                            @foreach($languages as $lang)
                            <option value="{{ $lang }}" {{ request('language') == $lang ? 'selected' : '' }}>
                                {{ $lang }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('reciters.filter_by_riwayah') }}</label>
                        <select name="riwayah" class="quran-form-select">
                            <option value="">{{ __('reciters.all_riwayahs') }}</option>
                            @foreach($riwayahs as $riwayah)
                            <option value="{{ $riwayah }}" {{ request('riwayah') == $riwayah ? 'selected' : '' }}>
                                {{ $riwayah }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('reciters.filter_by_status') }}</label>
                        <select name="status" class="quran-form-select">
                            <option value="">{{ __('reciters.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                {{ __('common.active') }}
                            </option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                {{ __('common.inactive') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reciters Grid -->
    <div class="row g-4">
        @forelse($reciters as $reciter)
        <div class="col-md-6 col-lg-4">
            <div class="quran-card h-100">
                <div class="quran-card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="quran-avatar">
                            @if($reciter->image)
                            <img src="{{ Storage::url($reciter->image) }}" alt="{{ $reciter->name }}" 
                                 class="quran-avatar-img">
                            @else
                            <div class="quran-avatar-img bg-primary d-flex align-items-center justify-content-center text-white">
                                {{ Str::substr($reciter->name, 0, 1) }}
                            </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $reciter->name }}</h6>
                            @if($reciter->riwayah)
                            <span class="quran-table-badge info">{{ $reciter->riwayah }}</span>
                            @endif
                            @if($reciter->language)
                            <span class="quran-table-badge secondary ms-1">{{ $reciter->language }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div>
                            <span class="quran-table-badge {{ $reciter->is_active ? 'success' : 'danger' }}">
                                {{ $reciter->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                            <small class="text-muted ms-2">
                                <i class="bi bi-file-music me-1"></i>
                                {{ $reciter->audio_files_count }} {{ __('reciters.files') }}
                            </small>
                        </div>
                        <div class="quran-table-actions">
                            <a href="{{ route('reciters.show', $reciter) }}" 
                               class="quran-table-action-btn view" 
                               data-bs-toggle="tooltip" title="{{ __('common.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()?->role === 'admin')
                            <a href="{{ route('reciters.edit', $reciter) }}" 
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
                <i class="bi bi-mic-mute"></i>
                <h6>{{ __('reciters.no_reciters_found') }}</h6>
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('reciters.create') }}" class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-person-plus me-1"></i>
                    {{ __('reciters.actions.create_first') }}
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($reciters->hasPages())
    <div class="mt-4">
        {{ $reciters->links() }}
    </div>
    @endif
</div>
@endsection