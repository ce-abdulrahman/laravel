{{-- resources/views/memorization-plans/index.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_plans.titles.index'))
@section('page-title', __('memorization_plans.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('memorization_plans.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('memorization_plans.titles.index') }}</h1>
            <div class="text-muted">
                @if(auth()->user()->role === 'admin')
                    {{ __('memorization_plans.hints.manage_all_plans') }}
                @else
                    {{ __('memorization_plans.hints.available_plans') }}
                @endif
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
        <div>
            <a href="{{ route('memorization-plans.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('memorization_plans.actions.create') }}
            </a>
        </div>
        @endif
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.total_plans') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_plans'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.active_plans') }}</div>
                        <div class="quran-stat-value">{{ $stats['active_plans'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-play-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.completed_plans') }}</div>
                        <div class="quran-stat-value">{{ $stats['completed_plans'] ?? 0 }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        @if(auth()->user()->role === 'admin')
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.total_users') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_users'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.my_progress') }}</div>
                        <div class="quran-stat-value">
                            {{ auth()->user()->memorizationReviews()->whereDate('created_at', today())->count() }}
                        </div>
                        <div class="quran-stat-sub">{{ __('memorization_plans.today') }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filter - تەنها بۆ ئەدمین -->
    @if(auth()->user()->role === 'admin')
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('memorization-plans.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('memorization_plans.filter_by_status') }}</label>
                        <select name="status" class="quran-form-select">
                            <option value="">{{ __('memorization_plans.all_statuses') }}</option>
                            @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('memorization_plans.filter_by_type') }}</label>
                        <select name="plan_type" class="quran-form-select">
                            <option value="">{{ __('memorization_plans.all_types') }}</option>
                            @foreach($planTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('plan_type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Plans Grid -->
    <div class="row g-4">
        @forelse($plans as $plan)
        <div class="col-md-6 col-lg-4">
            <div class="quran-plan-card">
                <div class="quran-plan-header">
                    <div class="quran-plan-icon">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="quran-plan-info">
                        <h6>{{ $plan->title }}</h6>
                        <span class="quran-plan-badge {{ $plan->status }}">
                            {{ $statuses[$plan->status] ?? $plan->status }}
                        </span>
                        @if(auth()->user()->role === 'admin')
                        <small class="text-muted d-block">{{ $plan->user->name ?? 'Unknown' }}</small>
                        @endif
                    </div>
                </div>

                <div class="quran-plan-progress">
                    @php
                        $totalItems = $plan->items_count;
                        $completedItems = $plan->items->count();
                        $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                    @endphp
                    <div class="quran-plan-stats">
                        <span>{{ $completedItems }}/{{ $totalItems }} {{ __('memorization_plans.days') }}</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <div class="quran-plan-footer">
                    <div class="quran-plan-next">
                        <small>{{ $plan->start_date->format('Y-m-d') }}</small>
                    </div>
                    <div class="quran-table-actions">
                        <a href="{{ route('memorization-plans.show', $plan) }}" 
                           class="quran-table-action-btn view">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('memorization-plans.edit', $plan) }}" 
                           class="quran-table-action-btn edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="quran-table-empty">
                <i class="bi bi-calendar-range"></i>
                <h6>{{ __('memorization_plans.no_plans') }}</h6>
                <p>
                    @if(auth()->user()->role === 'admin')
                        {{ __('memorization_plans.no_plans_message_admin') }}
                    @else
                        {{ __('memorization_plans.no_plans_message_user') }}
                    @endif
                </p>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('memorization-plans.create') }}" class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('memorization_plans.actions.create_first') }}
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($plans->hasPages())
    <div class="mt-4">
        {{ $plans->links() }}
    </div>
    @endif
</div>
@endsection