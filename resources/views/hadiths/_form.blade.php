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
                    ناوەڕۆکی فەرموودە
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="category_id">
                            هاوپۆلی فەرموودە
                            <span class="text-danger">*</span>
                        </label>
                        <select name="category_id"
                                id="category_id"
                                class="quran-form-select @error('category_id') is-invalid @enderror"
                                required>
                            <option value="">-- هاوپۆلێک هەڵبژێرە --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $hadith->category_id) == $cat->id)>
                                    {{ $cat->name_ku }} ({{ $cat->name_ar }})
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="narrator">
                            ڕاوی (عەرەبی یان کوردی)
                        </label>
                        <input
                            type="text"
                            name="narrator"
                            id="narrator"
                            class="quran-form-control @error('narrator') is-invalid @enderror"
                            value="{{ old('narrator', $hadith->narrator) }}"
                            placeholder="نموونە: عن أبي هريرة رضي الله عنه..."
                        >
                        @error('narrator')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="arabic_text">
                            دەقی فەرموودە (عەرەبی)
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="arabic_text"
                            id="arabic_text"
                            rows="4"
                            class="quran-form-control arabic-text @error('arabic_text') is-invalid @enderror"
                            required
                            dir="rtl"
                            placeholder="دەقی فەرموودەکە بە عەرەبی لێرە بنووسە..."
                        >{{ old('arabic_text', $hadith->arabic_text) }}</textarea>
                        @error('arabic_text')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="translation_ku">
                            وەرگێڕانی کوردی
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="translation_ku"
                            id="translation_ku"
                            rows="3"
                            class="quran-form-control @error('translation_ku') is-invalid @enderror"
                            required
                            placeholder="وەرگێڕانی فەرموودەکە بە کوردی لێرە بنووسە..."
                        >{{ old('translation_ku', $hadith->translation_ku) }}</textarea>
                        @error('translation_ku')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="translation_en">
                            وەرگێڕانی ئینگلیزی
                        </label>
                        <textarea
                            name="translation_en"
                            id="translation_en"
                            rows="3"
                            class="quran-form-control @error('translation_en') is-invalid @enderror"
                            placeholder="وەرگێڕانی فەرموودەکە بە ئینگلیزی لێرە بنووسە..."
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
                    شیکردنەوە و سەرچاوە
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="source">
                            سەرچاوەی فەرموودە
                        </label>
                        <input
                            type="text"
                            name="source"
                            id="source"
                            class="quran-form-control @error('source') is-invalid @enderror"
                            value="{{ old('source', $hadith->source) }}"
                            placeholder="بۆ نموونە: رواه البخاري ومسلم..."
                        >
                        @error('source')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="order">
                            ڕیزبەندی نیشاندان
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
                                چالاک بێت (لەسەر مۆبایل پیشان بدرێت)
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="explanation_ku">
                            شیکردنەوەی فەرموودە (کوردی)
                        </label>
                        <textarea
                            name="explanation_ku"
                            id="explanation_ku"
                            rows="4"
                            class="quran-form-control @error('explanation_ku') is-invalid @enderror"
                            placeholder="شیکردنەوەیەکی کورت بە کوردی..."
                        >{{ old('explanation_ku', $hadith->explanation_ku) }}</textarea>
                        @error('explanation_ku')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="explanation_en">
                            شیکردنەوەی فەرموودە (ئینگلیزی)
                        </label>
                        <textarea
                            name="explanation_en"
                            id="explanation_en"
                            rows="4"
                            class="quran-form-control @error('explanation_en') is-invalid @enderror"
                            placeholder="شیکردنەوەیەکی کورت بە ئینگلیزی..."
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
