{{-- resources/views/hadiths/_form.blade.php --}}
@php
    /** @var \App\Models\Hadith $hadith */
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\HadithCategory[] $categories */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Content Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-chat-left-text me-2"></i>
                    {{ __('hadiths.sections.content') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="category_id">
                            {{ __('hadiths.fields.category_id') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="category_id"
                                id="category_id"
                                class="quran-form-select @error('category_id') is-invalid @enderror"
                                required>
                            <option value="">-- {{ __('hadiths.placeholders.category_id') }} --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $hadith->category_id) == $cat->id)>
                                    {{ $cat->{'name_' . app()->getLocale()} ?? $cat->name_ku }}
                                    @if(app()->getLocale() !== 'ar' && $cat->name_ar)
                                        ({{ $cat->name_ar }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="narrator">
                            {{ __('hadiths.fields.narrator') }}
                        </label>
                        <input
                            type="text"
                            name="narrator"
                            id="narrator"
                            class="quran-form-control @error('narrator') is-invalid @enderror"
                            value="{{ old('narrator', $hadith->narrator) }}"
                            placeholder="{{ __('hadiths.placeholders.narrator') }}"
                        >
                        @error('narrator')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="arabic_text">
                            {{ __('hadiths.fields.arabic_text') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="arabic_text"
                            id="arabic_text"
                            rows="4"
                            class="quran-form-control arabic-text @error('arabic_text') is-invalid @enderror"
                            required
                            dir="rtl"
                            placeholder="{{ __('hadiths.placeholders.arabic_text') }}"
                            style="font-family: 'Scheherazade New', 'Amiri', 'Traditional Arabic', serif; font-size: 1.25rem;"
                        >{{ old('arabic_text', $hadith->arabic_text) }}</textarea>
                        @error('arabic_text')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="translation_ku">
                            {{ __('hadiths.fields.translation_ku') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="translation_ku"
                            id="translation_ku"
                            rows="3"
                            class="quran-form-control @error('translation_ku') is-invalid @enderror"
                            required
                            placeholder="{{ __('hadiths.placeholders.translation_ku') }}"
                        >{{ old('translation_ku', $hadith->translation_ku) }}</textarea>
                        @error('translation_ku')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="translation_en">
                            {{ __('hadiths.fields.translation_en') }}
                        </label>
                        <textarea
                            name="translation_en"
                            id="translation_en"
                            rows="3"
                            class="quran-form-control @error('translation_en') is-invalid @enderror"
                            placeholder="{{ __('hadiths.placeholders.translation_en') }}"
                        >{{ old('translation_en', $hadith->translation_en) }}</textarea>
                        @error('translation_en')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Meta & Explanation Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('hadiths.sections.meta') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="source">
                            {{ __('hadiths.fields.source') }}
                        </label>
                        <input
                            type="text"
                            name="source"
                            id="source"
                            class="quran-form-control @error('source') is-invalid @enderror"
                            value="{{ old('source', $hadith->source) }}"
                            placeholder="{{ __('hadiths.placeholders.source') }}"
                        >
                        @error('source')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="order">
                            {{ __('hadiths.fields.order') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="order"
                            id="order"
                            class="quran-form-control @error('order') is-invalid @enderror"
                            value="{{ old('order', $hadith->order) }}"
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
                                @checked(old('is_active', $hadith->is_active))
                            >
                            <label class="quran-form-check-label" for="is_active">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ __('hadiths.fields.is_active') }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="explanation_ku">
                            {{ __('hadiths.fields.explanation_ku') }}
                        </label>
                        <textarea
                            name="explanation_ku"
                            id="explanation_ku"
                            rows="4"
                            class="quran-form-control @error('explanation_ku') is-invalid @enderror"
                            placeholder="{{ __('hadiths.placeholders.explanation_ku') }}"
                        >{{ old('explanation_ku', $hadith->explanation_ku) }}</textarea>
                        @error('explanation_ku')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="explanation_en">
                            {{ __('hadiths.fields.explanation_en') }}
                        </label>
                        <textarea
                            name="explanation_en"
                            id="explanation_en"
                            rows="4"
                            class="quran-form-control @error('explanation_en') is-invalid @enderror"
                            placeholder="{{ __('hadiths.placeholders.explanation_en') }}"
                        >{{ old('explanation_en', $hadith->explanation_en) }}</textarea>
                        @error('explanation_en')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
