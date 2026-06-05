{{-- resources/views/adhkar-categories/_form.blade.php --}}
@php
    /** @var \App\Models\AdhkarCategory $category */
@endphp

<div class="quran-form">
    <div class="row g-4">
        {{-- Category Info Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-tag me-2"></i>
                    {{ __('adhkar_categories.sections.info') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_ku">
                            {{ __('adhkar_categories.fields.name_ku') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="name_ku"
                            id="name_ku"
                            class="quran-form-control @error('name_ku') is-invalid @enderror"
                            value="{{ old('name_ku', $category->name_ku) }}"
                            required
                            placeholder="{{ __('adhkar_categories.placeholders.name_ku') }}"
                        >
                        @error('name_ku')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_ar">
                            {{ __('adhkar_categories.fields.name_ar') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="name_ar"
                            id="name_ar"
                            class="quran-form-control arabic-text @error('name_ar') is-invalid @enderror"
                            value="{{ old('name_ar', $category->name_ar) }}"
                            required
                            dir="rtl"
                            placeholder="{{ __('adhkar_categories.placeholders.name_ar') }}"
                        >
                        @error('name_ar')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_en">
                            {{ __('adhkar_categories.fields.name_en') }}
                        </label>
                        <input
                            type="text"
                            name="name_en"
                            id="name_en"
                            class="quran-form-control @error('name_en') is-invalid @enderror"
                            value="{{ old('name_en', $category->name_en) }}"
                            placeholder="{{ __('adhkar_categories.placeholders.name_en') }}"
                        >
                        @error('name_en')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Configuration Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-sliders me-2"></i>
                    {{ __('adhkar_categories.sections.configuration') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="icon">
                            {{ __('adhkar_categories.fields.icon') }}
                        </label>
                        <input
                            type="text"
                            name="icon"
                            id="icon"
                            class="quran-form-control @error('icon') is-invalid @enderror"
                            value="{{ old('icon', $category->icon) }}"
                            placeholder="{{ __('adhkar_categories.placeholders.icon') }}"
                        >
                        <p class="text-muted small mt-1">{{ __('adhkar_categories.fields.icon_hint') }}</p>
                        @error('icon')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="order">
                            {{ __('adhkar_categories.fields.order') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="order"
                            id="order"
                            class="quran-form-control @error('order') is-invalid @enderror"
                            value="{{ old('order', $category->order) }}"
                            required
                        >
                        @error('order')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="quran-form-check mb-2">
                            <input
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                class="quran-form-check-input"
                                value="1"
                                @checked(old('is_active', $category->is_active))
                            >
                            <label class="quran-form-check-label" for="is_active">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ __('adhkar_categories.fields.is_active') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
