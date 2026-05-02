{{-- resources/views/tafsir-books/_form.blade.php --}}
@php
    /** @var \App\Models\TafsirBook $tafsirBook */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('tafsir_books.sections.basic_info') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="quran-form-label" for="name">
                            {{ __('tafsir_books.fields.name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               class="quran-form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $tafsirBook->name) }}" required>
                        @error('name')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="language_code">
                            {{ __('tafsir_books.fields.language') }}
                        </label>
                        <select name="language_code" id="language_code" 
                                class="quran-form-select @error('language_code') is-invalid @enderror">
                            <option value="">{{ __('tafsir_books.select_language') }}</option>
                            @foreach($languages as $code => $name)
                            <option value="{{ $code }}" 
                                {{ old('language_code', $tafsirBook->language_code) == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach>
                        </select>
                        @error('language_code')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="author">
                            {{ __('tafsir_books.fields.author') }}
                        </label>
                        <input type="text" name="author" id="author" 
                               class="quran-form-control @error('author') is-invalid @enderror"
                               value="{{ old('author', $tafsirBook->author) }}">
                        @error('author')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="source">
                            {{ __('tafsir_books.fields.source') }}
                        </label>
                        <input type="text" name="source" id="source" 
                               class="quran-form-control @error('source') is-invalid @enderror"
                               value="{{ old('source', $tafsirBook->source) }}"
                               placeholder="{{ __('tafsir_books.placeholders.source') }}">
                        @error('source')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="short_description">
                            {{ __('tafsir_books.fields.short_description') }}
                        </label>
                        <textarea name="short_description" id="short_description" rows="3"
                                  class="quran-form-control @error('short_description') is-invalid @enderror"
                                  placeholder="{{ __('tafsir_books.placeholders.description') }}">{{ old('short_description', $tafsirBook->short_description) }}</textarea>
                        @error('short_description')
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
                           {{ old('is_active', $tafsirBook->is_active ?? true) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ __('tafsir_books.fields.is_active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>