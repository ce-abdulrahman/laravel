{{-- resources/views/user-ayah-progress/_form.blade.php --}}
@php
    /** @var \App\Models\UserAyahProgress $progress */
    $selectedAyah = $selectedAyah ?? $progress->ayah ?? null;
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Ayah Selection -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-journal-text me-2"></i>
                    {{ __('user_ayah_progress.sections.ayah_selection') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label">{{ __('user_ayah_progress.filter_by_surah') }}</label>
                        <select id="filter_surah" class="quran-form-select">
                            <option value="">{{ __('user_ayah_progress.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}">{{ $surah->number }}. {{ $surah->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_id">
                            {{ __('user_ayah_progress.fields.ayah') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="ayah_id" id="ayah_id" 
                                class="quran-form-select select2 @error('ayah_id') is-invalid @enderror" required>
                            <option value="">{{ __('user_ayah_progress.select_ayah') }}</option>
                            @if($selectedAyah)
                            <option value="{{ $selectedAyah->id }}" selected>
                                {{ $selectedAyah->surah->number }}:{{ $selectedAyah->ayah_number }} - 
                                {{ $selectedAyah->surah->name_ar }}
                            </option>
                            @endif
                        </select>
                        @error('ayah_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if($selectedAyah)
                <div class="mt-3 p-3 bg-light rounded-3">
                    <div class="arabic-text" style="font-size: 18px;">{{ $selectedAyah->text_uthmani }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Progress Details -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-bar-chart me-2"></i>
                    {{ __('user_ayah_progress.sections.progress_details') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="memorize_status">
                            {{ __('user_ayah_progress.fields.status') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="memorize_status" id="memorize_status" 
                                class="quran-form-select @error('memorize_status') is-invalid @enderror" required>
                            @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ old('memorize_status', $progress->memorize_status ?? 'not_started') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('memorize_status')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="strength_score">
                            {{ __('user_ayah_progress.fields.strength_score') }}
                        </label>
                        <input type="range" name="strength_score" id="strength_score" 
                               class="form-range @error('strength_score') is-invalid @enderror"
                               value="{{ old('strength_score', $progress->strength_score ?? 0) }}" 
                               min="0" max="100" step="5">
                        <div class="d-flex justify-content-between">
                            <small>0%</small>
                            <small id="strength_value">{{ old('strength_score', $progress->strength_score ?? 0) }}%</small>
                            <small>100%</small>
                        </div>
                        @error('strength_score')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="mistakes_count">
                            {{ __('user_ayah_progress.fields.mistakes') }}
                        </label>
                        <input type="number" name="mistakes_count" id="mistakes_count" 
                               class="quran-form-control @error('mistakes_count') is-invalid @enderror"
                               value="{{ old('mistakes_count', $progress->mistakes_count ?? 0) }}" min="0">
                        @error('mistakes_count')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="notes">
                            {{ __('user_ayah_progress.fields.notes') }}
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="quran-form-control @error('notes') is-invalid @enderror"
                                  placeholder="{{ __('user_ayah_progress.placeholders.notes') }}">{{ old('notes', $progress->notes ?? '') }}</textarea>
                        @error('notes')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const strengthSlider = document.getElementById('strength_score');
    const strengthValue = document.getElementById('strength_value');
    if (strengthSlider && strengthValue) {
        strengthSlider.addEventListener('input', function() {
            strengthValue.textContent = this.value + '%';
        });
    }
});
</script>
@endpush