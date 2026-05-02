{{-- resources/views/tajweed-segments/_form.blade.php --}}
@php
    /** @var \App\Models\AyahTajweedSegment $tajweedSegment */
    $selectedRule = $selectedRule ?? $tajweedSegment->tajweedRule ?? null;
    $selectedAyah = $selectedAyah ?? $tajweedSegment->ayah ?? null;
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Selection -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-journal-text me-2"></i>
                    {{ __('tajweed_segments.sections.selection') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="tajweed_rule_id">
                            {{ __('tajweed_segments.fields.tajweed_rule') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="tajweed_rule_id" id="tajweed_rule_id" 
                                class="quran-form-select @error('tajweed_rule_id') is-invalid @enderror" required>
                            <option value="">{{ __('tajweed_segments.select_rule') }}</option>
                            @foreach($tajweedRules as $rule)
                            <option value="{{ $rule->id }}" 
                                {{ old('tajweed_rule_id', $tajweedSegment->tajweed_rule_id) == $rule->id ? 'selected' : '' }}
                                data-color="{{ $rule->color_code }}">
                                {{ $rule->name }} ({{ $rule->category }})
                            </option>
                            @endforeach
                        </select>
                        @error('tajweed_rule_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_id">
                            {{ __('tajweed_segments.fields.ayah') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="ayah_id" id="ayah_id" 
                                class="quran-form-select @error('ayah_id') is-invalid @enderror" required>
                            <option value="">{{ __('tajweed_segments.select_ayah') }}</option>
                            @foreach($ayahs as $ayah)
                            <option value="{{ $ayah->id }}" 
                                {{ old('ayah_id', $tajweedSegment->ayah_id) == $ayah->id ? 'selected' : '' }}>
                                {{ $ayah->surah->number }}:{{ $ayah->ayah_number }} - 
                                {{ $ayah->surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                        @error('ayah_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if($selectedAyah)
                <div class="mt-3 p-3 bg-light rounded-3">
                    <label class="quran-detail-label">{{ __('tajweed_segments.selected_ayah') }}</label>
                    <div class="arabic-text" style="font-size: 20px;">{{ $selectedAyah->text_uthmani }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Segment Details -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-puzzle me-2"></i>
                    {{ __('tajweed_segments.sections.segment_details') }}
                </h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="quran-form-label" for="text_segment">
                            {{ __('tajweed_segments.fields.text_segment') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="text_segment" id="text_segment" rows="2"
                                  class="quran-form-control arabic-text @error('text_segment') is-invalid @enderror"
                                  dir="rtl"
                                  placeholder="{{ __('tajweed_segments.placeholders.text_segment') }}"
                                  required>{{ old('text_segment', $tajweedSegment->text_segment) }}</textarea>
                        @error('text_segment')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="start_index">
                            {{ __('tajweed_segments.fields.start_index') }}
                        </label>
                        <input type="number" name="start_index" id="start_index" 
                               class="quran-form-control @error('start_index') is-invalid @enderror"
                               value="{{ old('start_index', $tajweedSegment->start_index) }}" min="0">
                        @error('start_index')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="end_index">
                            {{ __('tajweed_segments.fields.end_index') }}
                        </label>
                        <input type="number" name="end_index" id="end_index" 
                               class="quran-form-control @error('end_index') is-invalid @enderror"
                               value="{{ old('end_index', $tajweedSegment->end_index) }}" min="0">
                        @error('end_index')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="note">
                            {{ __('tajweed_segments.fields.note') }}
                        </label>
                        <textarea name="note" id="note" rows="2"
                                  class="quran-form-control @error('note') is-invalid @enderror"
                                  placeholder="{{ __('tajweed_segments.placeholders.note') }}">{{ old('note', $tajweedSegment->note) }}</textarea>
                        @error('note')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>