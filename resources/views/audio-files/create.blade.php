{{-- resources/views/audio-files/create.blade.php --}}
@extends('layouts.app')

@section('title', __('audio_files.titles.create'))
@section('page-title', __('audio_files.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('audio-files.index') }}">{{ __('audio_files.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('audio_files.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('audio_files.titles.create') }}</h1>
            <div class="text-muted">{{ __('audio_files.hints.upload_new') }}</div>
        </div>
        <a href="{{ route('audio-files.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('audio_files.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('audio-files.store') }}" enctype="multipart/form-data" id="audioForm">
        @csrf
        
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
                                <option value="">{{ __('audio_files.select_reciter') }}</option>
                                @foreach($reciters as $reciter)
                                <option value="{{ $reciter->id }}" 
                                    {{ old('reciter_id', $selectedReciter?->id) == $reciter->id ? 'selected' : '' }}>
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
                                    {{ old('surah_id') == $surah->id ? 'selected' : '' }}>
                                    {{ $surah->number }}. {{ $surah->name_ar }}
                                </option>
                                @endforeach>
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
                                    {{ old('surah_id') ? '' : 'disabled' }}>
                                <option value="">{{ __('audio_files.select_ayah') }}</option>
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
                                       value="{{ old('duration_seconds') }}" min="1" step="1"
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
                                <option value="{{ $value }}" {{ old('quality') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('quality')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   class="form-check-input" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                {{ __('audio_files.fields.is_active') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Source Settings Card -->
            <div class="col-12">
                <div class="quran-card audio-form-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-cloud-upload me-2"></i>
                            {{ __('audio_files.sections.source_settings') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <!-- Source Type -->
                        <div class="mb-4">
                            <label class="quran-form-label">{{ __('audio_files.fields.source_type') }}</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="source_type" id="source_upload" 
                                       value="upload" {{ old('source_type', 'upload') === 'upload' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="source_upload">
                                    <i class="bi bi-cloud-upload me-1"></i>
                                    {{ __('audio_files.source_types.upload') }}
                                </label>
                                
                                <input type="radio" class="btn-check" name="source_type" id="source_url" 
                                       value="url" {{ old('source_type') === 'url' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="source_url">
                                    <i class="bi bi-link-45deg me-1"></i>
                                    {{ __('audio_files.source_types.url') }}
                                </label>
                            </div>
                        </div>

                        <!-- Upload Area -->
                        <div id="uploadArea" class="audio-upload-area {{ old('source_type', 'upload') === 'upload' ? '' : 'd-none' }}">
                            <div class="upload-dropzone" id="dropZone">
                                <input type="file" name="audio_file" id="audio_file" 
                                       class="d-none" accept=".mp3,.wav,.ogg">
                                <div class="upload-content">
                                    <div class="upload-icon">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                    <h6>{{ __('audio_files.drag_drop') }}</h6>
                                    <p class="text-muted mb-3">{{ __('audio_files.or') }}</p>
                                    <button type="button" class="quran-btn quran-btn-outline-primary" 
                                            onclick="document.getElementById('audio_file').click()">
                                        <i class="bi bi-folder2-open me-1"></i>
                                        {{ __('audio_files.browse_files') }}
                                    </button>
                                    <p class="text-muted small mt-3">{{ __('audio_files.supported_formats') }}</p>
                                </div>
                            </div>
                            
                            <!-- Upload Progress -->
                            <div id="uploadProgress" class="mt-3 d-none">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span id="uploadFileName" class="small"></span>
                                    <span id="uploadPercent" class="small">0%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                         style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- Audio Preview -->
                            <div id="audioPreview" class="mt-3 d-none">
                                <label class="form-label small">
                                    <i class="bi bi-play-circle me-1"></i>
                                    {{ __('audio_files.preview') }}
                                </label>
                                <audio id="audioPlayer" controls class="w-100" style="height: 40px;"></audio>
                            </div>

                            @error('audio_file')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL Input -->
                        <div id="urlArea" class="{{ old('source_type') === 'url' ? '' : 'd-none' }}">
                            <label class="quran-form-label" for="file_path">
                                <i class="bi bi-link-45deg me-1"></i>
                                {{ __('audio_files.fields.url') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="url" name="file_path" id="file_path" 
                                   class="quran-form-control @error('file_path') is-invalid @enderror"
                                   value="{{ old('file_path') }}"
                                   placeholder="https://example.com/audio.mp3">
                            @error('file_path')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('audio_files.hints.url_help') }}</small>
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
            <a href="{{ route('audio-files.index') }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>
                {{ __('common.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
/* Neumorphism Audio Form Styles */
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

.audio-upload-area {
    width: 100%;
}

.upload-dropzone {
    border: 2px dashed var(--quran-border-light);
    border-radius: 20px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--quran-bg-pattern);
}

.upload-dropzone:hover {
    border-color: var(--quran-primary);
    background: color-mix(in srgb, var(--quran-primary) 5%, transparent);
}

.upload-dropzone.dragover {
    border-color: var(--quran-primary);
    background: color-mix(in srgb, var(--quran-primary) 10%, transparent);
    transform: scale(1.02);
}

.upload-icon {
    font-size: 48px;
    color: var(--quran-primary);
    margin-bottom: 16px;
}

.upload-content h6 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--quran-text-primary);
}

/* Loading Spinner Animation */
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

/* Form Switch Customization */
.form-switch .form-check-input {
    width: 48px;
    height: 24px;
    cursor: pointer;
}

.form-switch .form-check-input:checked {
    background-color: var(--quran-primary);
    border-color: var(--quran-primary);
}

/* Responsive */
@media (max-width: 767.98px) {
    .upload-dropzone {
        padding: 24px 16px;
    }
    
    .upload-icon {
        font-size: 36px;
    }
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

    // Variables
    const surahSelect = document.getElementById('surah_id');
    const ayahSelect = document.getElementById('ayah_id');
    const ayahLoading = document.getElementById('ayahLoading');
    const sourceTypeRadios = document.querySelectorAll('input[name="source_type"]');
    const uploadArea = document.getElementById('uploadArea');
    const urlArea = document.getElementById('urlArea');
    const dropZone = document.getElementById('dropZone');
    const audioFileInput = document.getElementById('audio_file');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const uploadPercent = document.getElementById('uploadPercent');
    const uploadFileName = document.getElementById('uploadFileName');
    const audioPreview = document.getElementById('audioPreview');
    const audioPlayer = document.getElementById('audioPlayer');
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
                            option.textContent = `${ayah.ayah_number} - ${ayah.text_uthmani.substring(0, 30)}...`;
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

    // Toggle Source Type
    sourceTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'upload') {
                uploadArea.classList.remove('d-none');
                urlArea.classList.add('d-none');
            } else {
                uploadArea.classList.add('d-none');
                urlArea.classList.remove('d-none');
            }
        });
    });

    // Drag & Drop Upload
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
    });

    dropZone.addEventListener('drop', function(e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });

    dropZone.addEventListener('click', () => audioFileInput.click());

    audioFileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileUpload(this.files[0]);
        }
    });

    // Handle File Upload
    function handleFileUpload(file) {
        // Check file type
        const allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', '.mp3', '.wav', '.ogg'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!['mp3', 'wav', 'ogg'].includes(fileExtension)) {
            showToast('{{ __("audio_files.messages.invalid_file_type") }}', 'error');
            return;
        }

        // Check file size (100MB max)
        if (file.size > 100 * 1024 * 1024) {
            showToast('{{ __("audio_files.messages.file_too_large") }}', 'error');
            return;
        }

        // Show progress
        uploadProgress.classList.remove('d-none');
        uploadFileName.textContent = file.name;

        // Create FormData
        const formData = new FormData();
        formData.append('audio_file', file);
        formData.append('reciter_id', document.getElementById('reciter_id').value || '');
        formData.append('_token', '{{ csrf_token() }}');

        // Upload via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("audio-files.upload") }}', true);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                uploadPercent.textContent = percent + '%';
            }
        });

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                
                // Hide progress
                setTimeout(() => {
                    uploadProgress.classList.add('d-none');
                    progressBar.style.width = '0%';
                }, 500);

                // Show preview
                audioPreview.classList.remove('d-none');
                audioPlayer.src = response.url;
                audioPlayer.load();

                // Auto-fill duration
                if (response.duration > 0) {
                    durationInput.value = response.duration;
                    const minutes = Math.floor(response.duration / 60);
                    const seconds = response.duration % 60;
                    durationDisplay.textContent = `(${minutes}:${seconds.toString().padStart(2, '0')})`;
                }

                // Set file path
                document.getElementById('file_path').value = response.file_path;

                showToast('{{ __("audio_files.messages.upload_success") }}', 'success');
            } else {
                showToast('{{ __("audio_files.messages.upload_error") }}', 'error');
                uploadProgress.classList.add('d-none');
            }
        };

        xhr.onerror = function() {
            showToast('{{ __("audio_files.messages.upload_error") }}', 'error');
            uploadProgress.classList.add('d-none');
        };

        xhr.send(formData);
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

    // Toast notification function
    function showToast(message, type = 'success') {
        // Create toast container if not exists
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        toast.style.cssText = 'min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Trigger surah change if pre-selected
    @if(old('surah_id'))
    if (surahSelect.value) {
        surahSelect.dispatchEvent(new Event('change'));
        // Set ayah after load
        setTimeout(() => {
            ayahSelect.value = '{{ old('ayah_id') }}';
        }, 500);
    }
    @endif
});
</script>
@endpush