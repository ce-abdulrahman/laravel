{{-- resources/views/translations/_form.blade.php --}}
@php
    /** @var \App\Models\Translation $translation */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Ayah Selection Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-journal-text me-2"></i>
                    {{ __('translations.sections.ayah_selection') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_id">
                            {{ __('translations.fields.ayah') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="ayah_id" id="ayah_id" 
                                class="quran-form-select @error('ayah_id') is-invalid @enderror" required>
                            <option value="">{{ __('translations.select_ayah') }}</option>
                            @foreach($ayahs as $ayah)
                            <option value="{{ $ayah->id }}" 
                                {{ old('ayah_id', $translation->ayah_id) == $ayah->id ? 'selected' : '' }}>
                                {{ $ayah->surah->number }}.{{ $ayah->ayah_number }} - 
                                {{ $ayah->surah->name_ar }} - 
                                {{ Str::limit($ayah->text_uthmani, 30) }}
                            </option>
                            @endforeach
                        </select>
                        @error('ayah_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Preview Ayah -->
                <div class="mt-3" id="ayahPreview">
                    @if($selectedAyah ?? false)
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('translations.selected_ayah') }}</label>
                        <div class="quran-detail-value arabic-text">{{ $selectedAyah->text_uthmani }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Translation Details Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-translate me-2"></i>
                    {{ __('translations.sections.translation_details') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="quran-form-label" for="language_code">
                            {{ __('translations.fields.language') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="language_code" id="language_code" 
                                class="quran-form-select @error('language_code') is-invalid @enderror" required>
                            <option value="">{{ __('translations.select_language') }}</option>
                            @foreach($languages as $code => $name)
                            <option value="{{ $code }}" 
                                {{ old('language_code', $translation->language_code) == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                        @error('language_code')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="quran-form-label" for="translator_name">
                            {{ __('translations.fields.translator') }}
                        </label>
                        <input type="text" name="translator_name" id="translator_name" 
                               class="quran-form-control @error('translator_name') is-invalid @enderror"
                               value="{{ old('translator_name', $translation->translator_name) }}"
                               placeholder="{{ __('translations.placeholders.translator') }}">
                        @error('translator_name')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="quran-form-label" for="content">
                            {{ __('translations.fields.content') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="content" id="content" rows="5"
                                  class="quran-form-control @error('content') is-invalid @enderror"
                                  placeholder="{{ __('translations.placeholders.content') }}"
                                  required>{{ old('content', $translation->content) }}</textarea>
                        @error('content')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-gear me-2"></i>
                    {{ __('translations.sections.settings') }}
                </h6>

                <div class="quran-form-check mb-3">
                    <input type="checkbox" name="is_default" id="is_default" value="1"
                           class="quran-form-check-input" 
                           {{ old('is_default', $translation->is_default) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_default">
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        {{ __('translations.fields.set_as_default') }}
                    </label>
                </div>
                <small class="text-muted d-block mb-3">{{ __('translations.hints.default_help') }}</small>

                <div class="quran-form-check">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="quran-form-check-input" 
                           {{ old('is_active', $translation->is_active ?? true) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ __('translations.fields.is_active') }}
                    </label>
                </div>
                <small class="text-muted d-block">{{ __('translations.hints.active_help') }}</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ayahSelect = document.getElementById('ayah_id');
    const previewDiv = document.getElementById('ayahPreview');

    if (ayahSelect) {
        ayahSelect.addEventListener('change', function() {
            // You can add AJAX call to load ayah preview
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                // Show preview (simplified version)
            }
        });
    }
});
</script>
@endpush