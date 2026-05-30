{{-- resources/views/audio-files/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('audio_files.titles.edit'))
@section('page-title', __('audio_files.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('audio-files.index') }}">{{ __('audio_files.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('audio-files.show', $audioFile) }}">{{ __('audio_files.titles.show') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('audio_files.titles.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('audio_files.titles.edit') }}</h1>
            <div class="text-muted">{{ __('audio_files.hints.edit') }}</div>
        </div>
        <a href="{{ route('audio-files.show', $audioFile) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('audio_files.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('audio-files.update', $audioFile) }}" id="audioForm">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <!-- Basic Info Card -->
            <div class="col-lg-6">
                <div class="quran-card audio-form-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('audio_files.sections.basic_info') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <!-- Reciter Selection -->
                        <div class="mb-4">
                            <label class="quran-form-label" for="reciter_id">
                                <i class="bi bi-mic me-1"></i>
                                {{ __('audio_files.fields.reciter') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select name="reciter_id" id="reciter_id" 
                                    class="quran-form-select select2 @error('reciter_id') is-invalid @enderror" 
                                    required>
                                @foreach($reciters as $reciter)
                                <option value="{{ $reciter->id }}" 
                                    {{ old('reciter_id', $audioFile->reciter_id) == $reciter->id ? 'selected' : '' }}>
                                    {{ $reciter->name }} {{ $reciter->riwayah ? '(' . $reciter->riwayah . ')' : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('reciter_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Surah Selection -->
                        <div class="mb-4">
                            <label class="quran-form-label" for="surah_id">
                                <i class="bi bi-book me-1"></i>
                                {{ __('audio_files.fields.surah') }}
                                <span class="text-muted">({{ __('common.optional') }})</span>
                            </label>
                            <select name="surah_id" id="surah_id" 
                                    class="quran-form-select select2 @error('surah_id') is-invalid @enderror">
                                <option value="">{{ __('audio_files.select_surah') }}</option>
                                @foreach($surahs as $surah)
                                <option value="{{ $surah->id }}" 
                                    {{ old('surah_id', $audioFile->surah_id) == $surah->id ? 'selected' : '' }}>
                                    {{ $surah->number }}. {{ $surah->name_ar }}
                                </option>
                                @endforeach
                            </select>
                            @error('surah_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ayah Selection -->
                        <div class="mb-4">
                            <label class="quran-form-label" for="ayah_id">
                                <i class="bi bi-journal-text me-1"></i>
                                {{ __('audio_files.fields.ayah') }}
                                <span class="text-muted">({{ __('common.optional') }})</span>
                            </label>
                            <select name="ayah_id" id="ayah_id" 
                                    class="quran-form-select @error('ayah_id') is-invalid @enderror"
                                    {{ $audioFile->surah_id ? '' : 'disabled' }}>
                                <option value="">{{ __('audio_files.select_ayah') }}</option>
                                @foreach($ayahs as $ayah)
                                <option value="{{ $ayah->id }}" 
                                    {{ old('ayah_id', $audioFile->ayah_id) == $ayah->id ? 'selected' : '' }}>
                                    {{ $ayah->ayah_number }}
                                </option>
                                @endforeach
                            </select>
                            <div id="ayahLoading" class="text-muted small mt-1 d-none">
                                <i class="bi bi-arrow-repeat spin me-1"></i>
                                {{ __('audio_files.loading_ayahs') }}
                            </div>
                            @error('ayah_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audio Settings Card -->
            <div class="col-lg-6">
                <div class="quran-card audio-form-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-sliders me-2"></i>
                            {{ __('audio_files.sections.audio_settings') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <!-- Duration -->
                        <div class="mb-4">
                            <label class="quran-form-label" for="duration_seconds">
                                <i class="bi bi-clock me-1"></i>
                                {{ __('audio_files.fields.duration') }}
                                <span class="text-muted">({{ __('common.optional') }})</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="duration_seconds" id="duration_seconds" 
                                       class="quran-form-control @error('duration_seconds') is-invalid @enderror"
                                       value="{{ old('duration_seconds', $audioFile->duration_seconds) }}" min="1" step="1"
                                       placeholder="{{ __('audio_files.placeholders.duration') }}">
                                <span class="input-group-text">{{ __('audio_files.seconds') }}</span>
                            </div>
                            <div id="durationDisplay" class="text-muted small mt-1"></div>
                            @error('duration_seconds')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quality -->
                        <div class="mb-4">
                            <label class="quran-form-label" for="quality">
                                <i class="bi bi-soundwave me-1"></i>
                                {{ __('audio_files.fields.quality') }}
                            </label>
                            <select name="quality" id="quality" 
                                    class="quran-form-select @error('quality') is-invalid @enderror">
                                <option value="">{{ __('audio_files.select_quality') }}</option>
                                @foreach($qualities as $value => $label)
                                <option value="{{ $value }}" {{ old('quality', $audioFile->quality) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('quality')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="form-check form-switch mb-4">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   class="form-check-input" 
                                   {{ old('is_active', $audioFile->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                {{ __('audio_files.fields.is_active') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="quran-form-actions mt-4">
            <button type="submit" class="quran-btn quran-btn-primary" id="submitBtn">
                <i class="bi bi-save me-1"></i>
                <span id="submitText">{{ __('common.save') }}</span>
                <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
            <a href="{{ route('audio-files.show', $audioFile) }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>
                {{ __('common.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.audio-form-card {
    background: var(--quran-bg-card);
    border-radius: 24px;
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.05),
        -8px -8px 16px rgba(255, 255, 255, 0.8);
    border: none;
    transition: all 0.3s ease;
}

[data-theme="dark"] .audio-form-card {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.3),
        -8px -8px 16px rgba(255, 255, 255, 0.02);
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Select2 Custom Styling */
.select2-container--default .select2-selection--single {
    height: 44px;
    border: 1.5px solid var(--quran-border-light);
    border-radius: 12px;
    background: var(--quran-bg-card);
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 42px;
    color: var(--quran-text-primary);
    padding-left: 16px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px;
}

.select2-dropdown {
    border: 1.5px solid var(--quran-border-light);
    border-radius: 12px;
    background: var(--quran-bg-card);
    box-shadow: var(--shadow-lg);
}

.select2-results__option {
    padding: 10px 16px;
    color: var(--quran-text-primary);
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: var(--quran-primary);
    color: white;
}

.form-switch .form-check-input {
    width: 48px;
    height: 24px;
    cursor: pointer;
}

.form-switch .form-check-input:checked {
    background-color: var(--quran-primary);
    border-color: var(--quran-primary);
}
</style>
@endpush

@push('scripts')
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: '{{ __("common.search") }}...',
        allowClear: true,
        dir: document.documentElement.dir || 'ltr'
    });

    const surahSelect = document.getElementById('surah_id');
    const ayahSelect = document.getElementById('ayah_id');
    const ayahLoading = document.getElementById('ayahLoading');
    const durationInput = document.getElementById('duration_seconds');
    const durationDisplay = document.getElementById('durationDisplay');
    const form = document.getElementById('audioForm');

    // Load Ayahs when Surah changes
    if (surahSelect) {
        surahSelect.addEventListener('change', function() {
            const surahId = this.value;
            
            ayahSelect.innerHTML = '<option value="">{{ __("audio_files.select_ayah") }}</option>';
            
            if (surahId) {
                ayahSelect.disabled = true;
                ayahLoading.classList.remove('d-none');
                
                fetch(`/api/surah/${surahId}/ayahs`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(ayah => {
                            const option = document.createElement('option');
                            option.value = ayah.id;
                            option.textContent = `${ayah.ayah_number}`;
                            if ('{{ $audioFile->ayah_id }}' == ayah.id) {
                                option.selected = true;
                            }
                            ayahSelect.appendChild(option);
                        });
                        ayahSelect.disabled = false;
                        ayahLoading.classList.add('d-none');
                    })
                    .catch(error => {
                        console.error('Error loading ayahs:', error);
                        ayahSelect.disabled = false;
                        ayahLoading.classList.add('d-none');
                    });
            } else {
                ayahSelect.disabled = true;
            }
        });
    }

    // Format duration display
    if (durationInput) {
        durationInput.addEventListener('input', function() {
            const seconds = parseInt(this.value);
            if (seconds > 0) {
                const minutes = Math.floor(seconds / 60);
                const secs = seconds % 60;
                durationDisplay.textContent = `(${minutes}:${secs.toString().padStart(2, '0')})`;
            } else {
                durationDisplay.textContent = '';
            }
        });
        // Trigger initial formatting
        durationInput.dispatchEvent(new Event('input'));
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        
        submitBtn.disabled = true;
        submitText.textContent = '{{ __("common.saving") }}...';
        submitSpinner.classList.remove('d-none');
    });
});
</script>
@endpush
