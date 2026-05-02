{{-- resources/views/memorization-plans/show.blade.php --}}
@extends('layouts.app')

@section('title', $memorizationPlan->title)
@section('page-title', $memorizationPlan->title)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('memorization-plans.index') }}">{{ __('memorization_plans.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ $memorizationPlan->title }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="h4 mb-0">{{ $memorizationPlan->title }}</h1>
                <span class="quran-plan-badge {{ $memorizationPlan->status }}">
                    {{ __('memorization_plans.statuses.' . $memorizationPlan->status) }}
                </span>
            </div>
            <div class="text-muted">
                {{ __('memorization_plans.started') }}: {{ $memorizationPlan->start_date->format('Y-m-d') }}
                @if($memorizationPlan->target_end_date)
                • {{ __('memorization_plans.target_end') }}: {{ $memorizationPlan->target_end_date->format('Y-m-d') }}
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('memorization-reviews.create') }}" class="quran-btn quran-btn-success">
                <i class="bi bi-check-circle me-1"></i>
                {{ __('memorization_reviews.actions.add_review') }}
            </a>
            <a href="{{ route('memorization-plans.edit', $memorizationPlan) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            <a href="{{ route('memorization-plans.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('memorization_plans.actions.back') }}
            </a>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.overall_progress') }}</div>
                        <div class="quran-stat-value">{{ $progress }}%</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-trophy"></i>
                    </div>
                </div>
                <div class="quran-stat-progress">
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.completed_days') }}</div>
                        <div class="quran-stat-value">{{ $stats['completed_days'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.pending_days') }}</div>
                        <div class="quran-stat-value">{{ $stats['pending_days'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.total_ayahs') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_ayahs']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Task -->
    @if($todayItem)
    <div class="quran-card mb-4 border-primary">
        <div class="quran-card-header bg-primary bg-opacity-10">
            <h5 class="quran-card-title text-primary">
                <i class="bi bi-calendar-check me-2"></i>
                {{ __('memorization_plans.today_task') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="d-flex align-items-center gap-4">
                <div>
                    <span class="quran-surah-number">{{ $todayItem->day_number }}</span>
                </div>
                <div class="flex-grow-1">
                    <h6>
                        {{ $todayItem->fromAyah->surah->name_ar }}
                        ({{ $todayItem->fromAyah->ayah_number }} - {{ $todayItem->toAyah->ayah_number }})
                    </h6>
                    <p class="text-muted mb-0">
                        {{ $todayItem->fromAyah->ayah_number }} {{ __('memorization_plans.to') }} {{ $todayItem->toAyah->ayah_number }}
                    </p>
                </div>
                <div>
                    @if($todayItem->status === 'pending')
                    <button class="quran-btn quran-btn-success mark-complete" data-item-id="{{ $todayItem->id }}">
                        <i class="bi bi-check-lg me-1"></i>
                        {{ __('memorization_plans.actions.mark_complete') }}
                    </button>
                    @else
                    <span class="quran-table-badge success">{{ __('memorization_plans.completed') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Plan Items Table -->
    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-list-check me-2"></i>
                {{ __('memorization_plans.plan_schedule') }}
            </h5>
        </div>
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>{{ __('memorization_plans.fields.day') }}</th>
                        <th>{{ __('memorization_plans.fields.surah_ayah') }}</th>
                        <th>{{ __('memorization_plans.fields.target_date') }}</th>
                        <th>{{ __('memorization_plans.fields.status') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr class="{{ $item->target_date->isToday() ? 'table-primary' : '' }}">
                        <td>
                            <span class="quran-surah-number" style="width: 36px; height: 36px; font-size: 14px;">
                                {{ $item->day_number }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('ayahs.show', $item->fromAyah) }}" class="text-decoration-none">
                                {{ $item->fromAyah->surah->name_ar }}
                                ({{ $item->fromAyah->ayah_number }} - {{ $item->toAyah->ayah_number }})
                            </a>
                        </td>
                        <td>
                            {{ $item->target_date->format('Y-m-d') }}
                            @if($item->target_date->isPast() && $item->status === 'pending')
                            <span class="quran-table-badge danger ms-2">{{ __('memorization_plans.overdue') }}</span>
                            @endif
                        </td>
                        <td>
                            <select class="form-select form-select-sm status-select" 
                                    data-item-id="{{ $item->id }}"
                                    style="width: 120px;">
                                <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>
                                    {{ __('memorization_plans.statuses.pending') }}
                                </option>
                                <option value="completed" {{ $item->status === 'completed' ? 'selected' : '' }}>
                                    {{ __('memorization_plans.statuses.completed') }}
                                </option>
                                <option value="skipped" {{ $item->status === 'skipped' ? 'selected' : '' }}>
                                    {{ __('memorization_plans.statuses.skipped') }}
                                </option>
                            </select>
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('ayahs.show', $item->fromAyah) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($memorizationPlan->notes)
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-pencil me-2"></i>
                {{ __('memorization_plans.fields.notes') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="quran-description">{{ $memorizationPlan->notes }}</div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">{{ __('common.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('memorization_plans.messages.confirm_delete') }}</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <form id="deleteForm" method="POST" action="{{ route('memorization-plans.destroy', $memorizationPlan) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update item status
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            const status = this.value;
            
            fetch(`/memorization-plans/{{ $memorizationPlan->id }}/items/${itemId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.plan_completed) {
                        window.location.reload();
                    }
                }
            });
        });
    });

    // Mark today's task as complete
    document.querySelector('.mark-complete')?.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        
        fetch(`/memorization-plans/{{ $memorizationPlan->id }}/items/${itemId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'completed' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });
});
</script>
@endpush