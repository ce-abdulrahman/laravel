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
                    @foreach(\App\Models\Language::activeList() as $lang)
                        @php
                            $isFallback = $lang->code === config('app.fallback_locale', 'en');
                        @endphp
                        <div class="col-md-4">
                            <label class="quran-form-label" for="translations_{{ $lang->code }}_name">
                                @if(Lang::has('tajweed_categories.fields.name_' . $lang->code))
                                    {{ __('tajweed_categories.fields.name_' . $lang->code) }}
                                @elseif($lang->code === 'en' && Lang::has('tajweed_categories.fields.name'))
                                    {{ __('tajweed_categories.fields.name') }}
                                @else
                                    Name ({{ $lang->name }})
                                @endif
                                @if($isFallback) <span class="text-danger">*</span> @endif
                            </label>
                            <input type="text" name="translations[{{ $lang->code }}][name]" id="translations_{{ $lang->code }}_name"
                                   class="quran-form-control @error('translations.' . $lang->code . '.name') is-invalid @enderror"
                                   value="{{ old('translations.' . $lang->code . '.name', $category->getTranslation('name', $lang->code)) }}"
                                   @if($isFallback) required @endif
                                   @if($lang->isRtl()) dir="rtl" @endif
                                   placeholder="{{ __('tajweed_categories.placeholders.name_' . $lang->code) ?? ('Enter ' . $lang->name . ' name') }}">
                            @error('translations.' . $lang->code . '.name')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

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
                    @foreach(\App\Models\Language::activeList() as $lang)
                        <div class="col-12">
                            <label class="quran-form-label" for="translations_{{ $lang->code }}_description">
                                @if(Lang::has('tajweed_categories.fields.description_' . $lang->code))
                                    {{ __('tajweed_categories.fields.description_' . $lang->code) }}
                                @elseif($lang->code === 'en' && Lang::has('tajweed_categories.fields.description'))
                                    {{ __('tajweed_categories.fields.description') }}
                                @else
                                    Description ({{ $lang->name }})
                                @endif
                            </label>
                            <textarea name="translations[{{ $lang->code }}][description]" id="translations_{{ $lang->code }}_description" rows="3"
                                      class="quran-form-control @error('translations.' . $lang->code . '.description') is-invalid @enderror"
                                      @if($lang->isRtl()) dir="rtl" @endif
                                      placeholder="{{ __('tajweed_categories.placeholders.description_' . $lang->code) ?? ('Enter ' . $lang->name . ' description') }}">{{ old('translations.' . $lang->code . '.description', $category->getTranslation('description', $lang->code)) }}</textarea>
                            @error('translations.' . $lang->code . '.description')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
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
