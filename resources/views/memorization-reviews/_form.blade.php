{{-- resources/views/memorization-reviews/_form.blade.php --}}
@php
    /** @var \App\Models\MemorizationReview $review */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Ayah Selection -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-journal-text me-2"></i>
                    {{ __('memorization_reviews.sections.ayah_selection') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label">{{ __('memorization_reviews.filter_by_surah') }}</label>
                        <select id="filter_surah" class="quran-form-select">
                            <option value="">{{ __('memorization_reviews.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}">{{ $surah->number }}. {{ $surah->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_id">
                            {{ __('memorization_reviews.fields.ayah') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="ayah_id" id="ayah_id" 
                                class="quran-form-select select2 @error('ayah_id') is-invalid @enderror" required>
                            <option value="">{{ __('memorization_reviews.select_ayah') }}</option>
                            @if(isset($selectedAyah))
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
            </div>
        </div>

        <!-- Review Details -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-check2-circle me-2"></i>
                    {{ __('memorization_reviews.sections.review_details') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="review_date">
                            {{ __('memorization_reviews.fields.review_date') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="review_date" id="review_date" 
                               class="quran-form-control @error('review_date') is-invalid @enderror"
                               value="{{ old('review_date', $review->review_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                        @error('review_date')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="review_level">
                            {{ __('memorization_reviews.fields.level') }}
                        </label>
                        <select name="review_level" id="review_level" 
                                class="quran-form-select @error('review_level') is-invalid @enderror">
                            <option value="">{{ __('memorization_reviews.select_level') }}</option>
                            @foreach($reviewLevels as $key => $label)
                            <option value="{{ $key }}" {{ old('review_level', $review->review_level) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('review_level')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="result">
                            {{ __('memorization_reviews.fields.result') }}
                        </label>
                        <select name="result" id="result" 
                                class="quran-form-select @error('result') is-invalid @enderror">
                            <option value="">{{ __('memorization_reviews.select_result') }}</option>
                            @foreach($results as $key => $label)
                            <option value="{{ $key }}" {{ old('result', $review->result) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('result')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="notes">
                            {{ __('memorization_reviews.fields.notes') }}
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="quran-form-control @error('notes') is-invalid @enderror"
                                  placeholder="{{ __('memorization_reviews.placeholders.notes') }}">{{ old('notes', $review->notes) }}</textarea>
                        @error('notes')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>