{{-- resources/views/qiraat-texts/_form.blade.php --}}
@php
    /** @var \App\Models\QiraatText $qiraatText */
    $selectedQiraat = $selectedQiraat ?? $qiraatText->qiraat ?? null;
    $selectedAyah = $selectedAyah ?? $qiraatText->ayah ?? null;
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Selection -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-journal-text me-2"></i>
                    {{ __('qiraat_texts.sections.selection') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="qiraah_id">
                            {{ __('qiraat_texts.fields.qiraat') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="qiraah_id" id="qiraah_id" 
                                class="quran-form-select select2 @error('qiraah_id') is-invalid @enderror" required>
                            <option value="">{{ __('qiraat_texts.select_qiraat') }}</option>
                            @foreach($qiraats as $qiraat)
                            <option value="{{ $qiraat->id }}" 
                                {{ old('qiraah_id', $qiraatText->qiraah_id) == $qiraat->id ? 'selected' : '' }}>
                                {{ $qiraat->name }} {{ $qiraat->riwayah ? '(' . $qiraat->riwayah . ')' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('qiraah_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_id">
                            {{ __('qiraat_texts.fields.ayah') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="ayah_id" id="ayah_id" 
                                class="quran-form-select select2 @error('ayah_id') is-invalid @enderror" required>
                            <option value="">{{ __('qiraat_texts.select_ayah') }}</option>
                            @foreach($ayahs as $ayah)
                            <option value="{{ $ayah->id }}" 
                                {{ old('ayah_id', $qiraatText->ayah_id) == $ayah->id ? 'selected' : '' }}>
                                {{ $ayah->surah->number }}:{{ $ayah->ayah_number }} - 
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

                @if($selectedAyah)
                <div class="mt-3 p-3 bg-light rounded-3">
                    <label class="quran-detail-label">{{ __('qiraat_texts.original_ayah') }}</label>
                    <div class="arabic-text" style="font-size: 20px;">{{ $selectedAyah->text_uthmani }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Text Variant -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    {{ __('qiraat_texts.sections.variant_details') }}
                </h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="quran-form-label" for="text_variant">
                            {{ __('qiraat_texts.fields.text_variant') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="text_variant" id="text_variant" rows="4"
                                  class="quran-form-control arabic-text @error('text_variant') is-invalid @enderror"
                                  dir="rtl"
                                  placeholder="{{ __('qiraat_texts.placeholders.text_variant') }}"
                                  required>{{ old('text_variant', $qiraatText->text_variant) }}</textarea>
                        @error('text_variant')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="note">
                            {{ __('qiraat_texts.fields.note') }}
                        </label>
                        <textarea name="note" id="note" rows="2"
                                  class="quran-form-control @error('note') is-invalid @enderror"
                                  placeholder="{{ __('qiraat_texts.placeholders.note') }}">{{ old('note', $qiraatText->note) }}</textarea>
                        @error('note')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>