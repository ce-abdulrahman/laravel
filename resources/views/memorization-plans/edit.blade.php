{{-- resources/views/memorization-plans/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_plans.titles.edit') ?? 'Edit Plan')
@section('page-title', __('memorization_plans.titles.edit') ?? 'Edit Plan')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('memorization-plans.index') }}">{{ __('memorization_plans.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('memorization-plans.show', $memorizationPlan) }}">{{ $memorizationPlan->title }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('memorization_plans.titles.edit') ?? 'Edit' }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('memorization_plans.titles.edit') ?? 'Edit Plan' }}</h1>
            <div class="text-muted">{{ __('memorization_plans.hints.edit_existing') ?? 'Update your memorization plan details' }}</div>
        </div>
        <a href="{{ route('memorization-plans.show', $memorizationPlan) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('memorization_plans.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('memorization-plans.update', $memorizationPlan) }}" id="planForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Edit Settings -->
            <div class="col-lg-6">
                <div class="quran-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-gear me-2"></i>
                            {{ __('memorization_plans.sections.basic_settings') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <div class="mb-3">
                            <label class="quran-form-label" for="title">
                                {{ __('memorization_plans.fields.title') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" id="title" 
                                   class="quran-form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $memorizationPlan->title) }}" required>
                            @error('title')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="status">
                                {{ __('memorization_plans.fields.status') ?? 'Status' }}
                                <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" 
                                    class="quran-form-select @error('status') is-invalid @enderror" required>
                                @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $memorizationPlan->status) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('status')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="quran-form-label" for="start_date">
                                    {{ __('memorization_plans.fields.start_date') }}
                                </label>
                                <input type="date" id="start_date" 
                                       class="quran-form-control"
                                       value="{{ $memorizationPlan->start_date?->format('Y-m-d') }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="quran-form-label" for="target_end_date">
                                    {{ __('memorization_plans.fields.target_end_date') }}
                                </label>
                                <input type="date" name="target_end_date" id="target_end_date" 
                                       class="quran-form-control @error('target_end_date') is-invalid @enderror"
                                       value="{{ old('target_end_date', $memorizationPlan->target_end_date?->format('Y-m-d')) }}">
                                @error('target_end_date')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="quran-form-label">{{ __('memorization_plans.fields.daily_target') }}</label>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <select name="daily_target_type" id="daily_target_type" 
                                            class="quran-form-select @error('daily_target_type') is-invalid @enderror" required>
                                        @foreach($dailyTargetTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('daily_target_type', $memorizationPlan->daily_target_type) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="daily_target_value" id="daily_target_value" 
                                           class="quran-form-control @error('daily_target_value') is-invalid @enderror"
                                           value="{{ old('daily_target_value', $memorizationPlan->daily_target_value) }}" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <span class="form-control-plaintext" id="targetUnit">
                                        {{ __('memorization_plans.per_day') }}
                                    </span>
                                </div>
                            </div>
                            @error('daily_target_value')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <label class="quran-form-label" for="notes">
                                {{ __('memorization_plans.fields.notes') }}
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="quran-form-control @error('notes') is-invalid @enderror"
                                      placeholder="{{ __('memorization_plans.placeholders.notes') }}">{{ old('notes', $memorizationPlan->notes) }}</textarea>
                            @error('notes')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Summary & Info -->
            <div class="col-lg-6">
                <div class="quran-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('memorization_plans.sections.plan_content') ?? 'Plan Information' }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <div class="mb-4">
                            <div class="text-muted small mb-1">{{ __('memorization_plans.fields.plan_type') }}</div>
                            <div class="h6 mb-0 text-capitalize">
                                <span class="badge bg-primary fs-7">
                                    {{ $planTypes[$memorizationPlan->plan_type] ?? $memorizationPlan->plan_type }}
                                </span>
                            </div>
                        </div>

                        @if($memorizationPlan->surah_id)
                        <div class="mb-4">
                            <div class="text-muted small mb-1">Target Surah</div>
                            <div class="h6 mb-0">{{ $memorizationPlan->surah_id }}</div>
                        </div>
                        @endif

                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Editing the plan type or start date of an active plan is not supported. Please create a new plan if you wish to change these details.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quran-form-actions mt-4">
            <button type="submit" class="quran-btn quran-btn-primary">
                <i class="bi bi-save me-1"></i>
                {{ __('common.save') ?? 'Save' }}
            </button>
            <a href="{{ route('memorization-plans.show', $memorizationPlan) }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>
                {{ __('common.cancel') ?? 'Cancel' }}
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update target unit text
    const targetTypeSelect = document.getElementById('daily_target_type');
    const targetUnit = document.getElementById('targetUnit');

    function updateTargetUnit() {
        const selected = targetTypeSelect.options[targetTypeSelect.selectedIndex]?.text || '{{ __("memorization_plans.per_day") }}';
        targetUnit.textContent = '{{ __("memorization_plans.per_day") }} (' + selected + ')';
    }

    targetTypeSelect.addEventListener('change', updateTargetUnit);
    updateTargetUnit();
});
</script>
@endpush
