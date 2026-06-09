{{-- resources/views/adhkars/_form.blade.php --}}
@php
    /** @var \App\Models\Adhkar $adhkar */
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\AdhkarCategory[] $categories */
@endphp

<div class="quran-form">
    <div class="row g-4">
        {{-- Content Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-chat-left-text me-2"></i>
                    {{ __('adhkars.sections.content') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="category_id">
                            {{ __('adhkars.fields.category') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="category_id"
                                id="category_id"
                                class="quran-form-select @error('category_id') is-invalid @enderror"
                                required>
                            <option value="">{{ __('adhkars.placeholders.category') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $adhkar->category_id) == $cat->id)>
                                    {{ $cat->name_ku }} ({{ $cat->name_ar }})
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="count">
                            {{ __('adhkars.fields.count') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="count"
                            id="count"
                            class="quran-form-control @error('count') is-invalid @enderror"
                            value="{{ old('count', $adhkar->count) }}"
                            min="1"
                            required
                        >
                        @error('count')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="arabic_text">
                            {{ __('adhkars.fields.arabic_text') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="arabic_text"
                            id="arabic_text"
                            rows="4"
                            class="quran-form-control arabic-text @error('arabic_text') is-invalid @enderror"
                            required
                            dir="rtl"
                            style="font-family: var(--quran-font, 'Amiri Quran', serif); font-size: 1.1rem; line-height: 2;"
                            placeholder="{{ __('adhkars.placeholders.arabic_text') }}"
                        >{{ old('arabic_text', $adhkar->arabic_text) }}</textarea>
                        @error('arabic_text')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @foreach(\App\Models\Language::activeList() as $lang)
                        <div class="col-md-6">
                            <label class="quran-form-label" for="translations_{{ $lang->code }}_translation">
                                @if(Lang::has('adhkars.fields.translation_' . $lang->code))
                                    {{ __('adhkars.fields.translation_' . $lang->code) }}
                                @else
                                    Translation ({{ $lang->name }})
                                @endif
                            </label>
                            <textarea
                                name="translations[{{ $lang->code }}][translation]"
                                id="translations_{{ $lang->code }}_translation"
                                rows="3"
                                class="quran-form-control @error('translations.' . $lang->code . '.translation') is-invalid @enderror"
                                @if($lang->isRtl()) dir="rtl" @endif
                                placeholder="{{ __('adhkars.placeholders.translation_' . $lang->code) ?? ('Enter ' . $lang->name . ' translation') }}"
                            >{{ old('translations.' . $lang->code . '.translation', $adhkar->getTranslation('translation', $lang->code)) }}</textarea>
                            @error('translations.' . $lang->code . '.translation')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Meta Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('adhkars.sections.meta') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="source">
                            {{ __('adhkars.fields.source') }}
                        </label>
                        <input
                            type="text"
                            name="source"
                            id="source"
                            class="quran-form-control @error('source') is-invalid @enderror"
                            value="{{ old('source', $adhkar->source) }}"
                            placeholder="{{ __('adhkars.placeholders.source') }}"
                        >
                        @error('source')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="order">
                            {{ __('adhkars.fields.order') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="order"
                            id="order"
                            class="quran-form-control @error('order') is-invalid @enderror"
                            value="{{ old('order', $adhkar->order) }}"
                            required
                        >
                        @error('order')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="description">
                            {{ __('adhkars.fields.description') }}
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="2"
                            class="quran-form-control @error('description') is-invalid @enderror"
                            placeholder="{{ __('adhkars.placeholders.description') }}"
                        >{{ old('description', $adhkar->description) }}</textarea>
                        @error('description')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
