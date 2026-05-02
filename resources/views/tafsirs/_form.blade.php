{{-- resources/views/tafsirs/_form.blade.php --}}
@php
    /** @var \App\Models\Tafsir $tafsir */
    $selectedBook = $selectedBook ?? $tafsir->tafsirBook ?? null;
    $selectedAyah = $selectedAyah ?? $tafsir->ayah ?? null;
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Selection Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-journal-text me-2"></i>
                    {{ __('tafsirs.sections.selection') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="tafsir_book_id">
                            {{ __('tafsirs.fields.tafsir_book') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="tafsir_book_id" id="tafsir_book_id" 
                                class="quran-form-select @error('tafsir_book_id') is-invalid @enderror" required>
                            <option value="">{{ __('tafsirs.select_book') }}</option>
                            @foreach($tafsirBooks as $book)
                            <option value="{{ $book->id }}" 
                                {{ old('tafsir_book_id', $tafsir->tafsir_book_id) == $book->id ? 'selected' : '' }}>
                                {{ $book->name }} ({{ $book->author ?: __('tafsir_books.unknown_author') }})
                            </option>
                            @endforeach
                        </select>
                        @error('tafsir_book_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_id">
                            {{ __('tafsirs.fields.ayah') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="ayah_id" id="ayah_id" 
                                class="quran-form-select @error('ayah_id') is-invalid @enderror" required>
                            <option value="">{{ __('tafsirs.select_ayah') }}</option>
                            @foreach($ayahs as $ayah)
                            <option value="{{ $ayah->id }}" 
                                {{ old('ayah_id', $tafsir->ayah_id) == $ayah->id ? 'selected' : '' }}>
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
                    <label class="quran-detail-label">{{ __('tafsirs.selected_ayah') }}</label>
                    <div class="arabic-text" style="font-size: 18px;">{{ $selectedAyah->text_uthmani }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Content Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    {{ __('tafsirs.sections.content') }}
                </h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="quran-form-label" for="short_content">
                            {{ __('tafsirs.fields.short_content') }}
                        </label>
                        <textarea name="short_content" id="short_content" rows="2"
                                  class="quran-form-control @error('short_content') is-invalid @enderror"
                                  placeholder="{{ __('tafsirs.placeholders.short_content') }}">{{ old('short_content', $tafsir->short_content) }}</textarea>
                        @error('short_content')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="content">
                            {{ __('tafsirs.fields.content') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="content" id="content" rows="8"
                                  class="quran-form-control @error('content') is-invalid @enderror"
                                  placeholder="{{ __('tafsirs.placeholders.content') }}"
                                  required>{{ old('content', $tafsir->content) }}</textarea>
                        @error('content')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="source_reference">
                            {{ __('tafsirs.fields.source_reference') }}
                        </label>
                        <input type="text" name="source_reference" id="source_reference" 
                               class="quran-form-control @error('source_reference') is-invalid @enderror"
                               value="{{ old('source_reference', $tafsir->source_reference) }}"
                               placeholder="{{ __('tafsirs.placeholders.source_reference') }}">
                        @error('source_reference')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="col-12">
            <div class="quran-form-section">
                <div class="quran-form-check">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="quran-form-check-input" 
                           {{ old('is_active', $tafsir->is_active ?? true) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ __('tafsirs.fields.is_active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>