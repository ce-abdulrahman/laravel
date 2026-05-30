@php
    /** @var \App\Models\Adhkar $adhkar */
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\AdhkarCategory[] $categories */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Content Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-chat-left-text me-2"></i>
                    ناوەڕۆکی زیکر
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="category_id">
                            هاوپۆل (کاتی خوێندنەوە)
                            <span class="text-danger">*</span>
                        </label>
                        <select name="category_id"
                                id="category_id"
                                class="quran-form-select @error('category_id') is-invalid @enderror"
                                required>
                            <option value="">-- هاوپۆلێک هەڵبژێرە --</option>
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
                            ژمارەی دووبارەکردنەوە (جار)
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
                            دەقی زیکر (عەرەبی)
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="arabic_text"
                            id="arabic_text"
                            rows="4"
                            class="quran-form-control arabic-text @error('arabic_text') is-invalid @enderror"
                            required
                            dir="rtl"
                            placeholder="زیکرەکە بە عەرەبی لێرە بنووسە..."
                        >{{ old('arabic_text', $adhkar->arabic_text) }}</textarea>
                        @error('arabic_text')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="translation_ku">
                            وەرگێڕانی کوردی
                        </label>
                        <textarea
                            name="translation_ku"
                            id="translation_ku"
                            rows="3"
                            class="quran-form-control @error('translation_ku') is-invalid @enderror"
                            placeholder="مانای زیکرەکە بە کوردی لێرە بنووسە..."
                        >{{ old('translation_ku', $adhkar->translation_ku) }}</textarea>
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
                            placeholder="مانای زیکرەکە بە ئینگلیزی لێرە بنووسە..."
                        >{{ old('translation_en', $adhkar->translation_en) }}</textarea>
                        @error('translation_en')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Meta Information Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    زانیاری فەزڵ و سەرچاوە
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="source">
                            سەرچاوەی فەرموودە / زیکر
                        </label>
                        <input
                            type="text"
                            name="source"
                            id="source"
                            class="quran-form-control @error('source') is-invalid @enderror"
                            value="{{ old('source', $adhkar->source) }}"
                            placeholder="بۆ نموونە: رواه البخاری، حصن المسلم..."
                        >
                        @error('source')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="order">
                            ڕیزبەندی نیشاندان لەم هاوپۆلەدا
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
                            فەزڵ و پاداشتی زیکر (کوردی)
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="2"
                            class="quran-form-control @error('description') is-invalid @enderror"
                            placeholder="پاداشت و سودەکانی ئەم زیکرە بنووسە بۆ بەکارهێنەر..."
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
