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
                    @foreach(\App\Models\Language::activeList() as $lang)
                        @php
                            $isRequired = in_array($lang->code, ['ku', 'ar']);
                            $isAr = $lang->code === 'ar';
                        @endphp
                        <div class="col-md-4">
                            <label class="quran-form-label" for="translations_{{ $lang->code }}_name">
                                @if(Lang::has('adhkar_categories.fields.name_' . $lang->code))
                                    {{ __('adhkar_categories.fields.name_' . $lang->code) }}
                                @else
                                    Name ({{ $lang->name }})
                                @endif
                                @if($isRequired) <span class="text-danger">*</span> @endif
                            </label>
                            <input
                                type="text"
                                name="translations[{{ $lang->code }}][name]"
                                id="translations_{{ $lang->code }}_name"
                                class="quran-form-control @if($isAr) arabic-text @endif @error('translations.' . $lang->code . '.name') is-invalid @enderror"
                                value="{{ old('translations.' . $lang->code . '.name', $category->getTranslation('name', $lang->code)) }}"
                                @if($isRequired) required @endif
                                @if($lang->isRtl()) dir="rtl" @endif
                                placeholder="{{ __('adhkar_categories.placeholders.name_' . $lang->code) ?? ('Enter ' . $lang->name . ' name') }}"
                            >
                            @error('translations.' . $lang->code . '.name')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
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
