{{-- resources/views/tajweed-rule-categories/_form.blade.php --}}
@php
    /** @var \App\Models\TajweedRuleCategory $category */
@endphp

<div class="quran-form">
    <div class="row g-4">

        {{-- Basic Information --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('tajweed_categories.sections.basic_info') }}
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="name">
                            {{ __('tajweed_categories.fields.name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                               class="quran-form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $category->name) }}" required
                               placeholder="{{ __('tajweed_categories.placeholders.name') }}">
                        @error('name')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_ku">
                            {{ __('tajweed_categories.fields.name_ku') }}
                        </label>
                        <input type="text" name="name_ku" id="name_ku"
                               class="quran-form-control @error('name_ku') is-invalid @enderror"
                               value="{{ old('name_ku', $category->name_ku) }}"
                               placeholder="{{ __('tajweed_categories.placeholders.name_ku') }}">
                        @error('name_ku')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_ar">
                            {{ __('tajweed_categories.fields.name_ar') }}
                        </label>
                        <input type="text" name="name_ar" id="name_ar"
                               class="quran-form-control @error('name_ar') is-invalid @enderror"
                               value="{{ old('name_ar', $category->name_ar) }}"
                               dir="rtl"
                               placeholder="{{ __('tajweed_categories.placeholders.name_ar') }}">
                        @error('name_ar')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="order">
                            {{ __('tajweed_categories.fields.order') }}
                        </label>
                        <input type="number" name="order" id="order"
                               class="quran-form-control @error('order') is-invalid @enderror"
                               value="{{ old('order', $category->order ?? 0) }}" min="0">
                        @error('order')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Descriptions --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-card-text me-2"></i>
                    {{ __('tajweed_categories.sections.description') }}
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="quran-form-label" for="description">
                            {{ __('tajweed_categories.fields.description') }}
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="quran-form-control @error('description') is-invalid @enderror"
                                  placeholder="{{ __('tajweed_categories.placeholders.description') }}">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="description_ku">
                            {{ __('tajweed_categories.fields.description_ku') }}
                        </label>
                        <textarea name="description_ku" id="description_ku" rows="3"
                                  class="quran-form-control @error('description_ku') is-invalid @enderror"
                                  placeholder="{{ __('tajweed_categories.placeholders.description_ku') }}">{{ old('description_ku', $category->description_ku) }}</textarea>
                        @error('description_ku')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="description_ar">
                            {{ __('tajweed_categories.fields.description_ar') }}
                        </label>
                        <textarea name="description_ar" id="description_ar" rows="3"
                                  class="quran-form-control @error('description_ar') is-invalid @enderror"
                                  dir="rtl"
                                  placeholder="{{ __('tajweed_categories.placeholders.description_ar') }}">{{ old('description_ar', $category->description_ar) }}</textarea>
                        @error('description_ar')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Settings --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-gear me-2"></i>
                    {{ __('tajweed_categories.sections.settings') }}
                </h6>
                <div class="quran-form-check">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="quran-form-check-input"
                           {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ __('tajweed_categories.fields.is_active') }}
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>
