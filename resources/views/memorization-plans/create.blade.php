{{-- resources/views/memorization-plans/create.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_plans.titles.create'))
@section('page-title', __('memorization_plans.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('memorization-plans.index') }}">{{ __('memorization_plans.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('memorization_plans.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('memorization_plans.titles.create') }}</h1>
            <div class="text-muted">{{ __('memorization_plans.hints.create_new') }}</div>
        </div>
        <a href="{{ route('memorization-plans.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('memorization_plans.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('memorization-plans.store') }}" id="planForm">
        @csrf

        <div class="row g-4">
            <!-- Basic Settings -->
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
                                   value="{{ old('title') }}" required>
                            @error('title')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="plan_type">
                                {{ __('memorization_plans.fields.plan_type') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select name="plan_type" id="plan_type" 
                                    class="quran-form-select @error('plan_type') is-invalid @enderror" required>
                                <option value="">{{ __('memorization_plans.select_type') }}</option>
                                @foreach($planTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('plan_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('plan_type')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="quran-form-label" for="start_date">
                                    {{ __('memorization_plans.fields.start_date') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="quran-form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="quran-form-label" for="target_end_date">
                                    {{ __('memorization_plans.fields.target_end_date') }}
                                </label>
                                <input type="date" name="target_end_date" id="target_end_date" 
                                       class="quran-form-control @error('target_end_date') is-invalid @enderror"
                                       value="{{ old('target_end_date') }}">
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
                                        <option value="{{ $key }}" {{ old('daily_target_type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="daily_target_value" id="daily_target_value" 
                                           class="quran-form-control @error('daily_target_value') is-invalid @enderror"
                                           value="{{ old('daily_target_value', 1) }}" min="1" required>
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
                                      placeholder="{{ __('memorization_plans.placeholders.notes') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Content -->
            <div class="col-lg-6">
                <div class="quran-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-list-check me-2"></i>
                            {{ __('memorization_plans.sections.plan_content') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <!-- Surah Selection -->
                        <div id="surahSelection" class="d-none">
                            <label class="quran-form-label">{{ __('memorization_plans.select_surah') }}</label>
                            <select name="surah_id" id="surah_id" class="quran-form-select">
                                <option value="">{{ __('memorization_plans.choose_surah') }}</option>
                                @foreach($surahs as $surah)
                                <option value="{{ $surah->id }}">{{ $surah->number }}. {{ $surah->name_ar }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Juz Selection -->
                        <div id="juzSelection" class="d-none">
                            <label class="quran-form-label">{{ __('memorization_plans.select_juz') }}</label>
                            <select name="juz_number" id="juz_number" class="quran-form-select">
                                <option value="">{{ __('memorization_plans.choose_juz') }}</option>
                                @for($i = 1; $i <= 30; $i++)
                                <option value="{{ $i }}">{{ __('memorization_plans.juz') }} {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Custom Selection Info -->
                        <div id="customInfo" class="d-none">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ __('memorization_plans.hints.custom_plan_info') }}
                            </div>
                            <p class="text-muted">{{ __('memorization_plans.hints.custom_after_create') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quran-form-actions mt-4">
            <button type="submit" class="quran-btn quran-btn-primary">
                <i class="bi bi-save me-1"></i>
                {{ __('common.save') }}
            </button>
            <a href="{{ route('memorization-plans.index') }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>
                {{ __('common.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const planTypeSelect = document.getElementById('plan_type');
    const surahSelection = document.getElementById('surahSelection');
    const juzSelection = document.getElementById('juzSelection');
    const customInfo = document.getElementById('customInfo');

    function updatePlanTypeFields() {
        const selectedType = planTypeSelect.value;
        
        surahSelection.classList.add('d-none');
        juzSelection.classList.add('d-none');
        customInfo.classList.add('d-none');

        if (selectedType === 'surah') {
            surahSelection.classList.remove('d-none');
        } else if (selectedType === 'juz') {
            juzSelection.classList.remove('d-none');
        } else if (selectedType === 'custom') {
            customInfo.classList.remove('d-none');
        }
    }

    planTypeSelect.addEventListener('change', updatePlanTypeFields);
    updatePlanTypeFields();

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