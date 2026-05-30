@php
    /** @var \App\Models\Banner $banner */
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Surah[] $surahs */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Content Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-card-text me-2"></i>
                    ناوەڕۆکی بانەر
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="title_arabic">
                            دەقی عەرەبی (ئایەت)
                        </label>
                        <input
                            type="text"
                            name="title_arabic"
                            id="title_arabic"
                            class="quran-form-control arabic-text @error('title_arabic') is-invalid @enderror"
                            value="{{ old('title_arabic', $banner->title_arabic) }}"
                            dir="rtl"
                            placeholder="ئایەتەکە بە عەرەبی بنووسە..."
                        >
                        @error('title_arabic')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="source">
                            سەرچاوە (نموونە: — ئیسرا ١٧:٩)
                        </label>
                        <input
                            type="text"
                            name="source"
                            id="source"
                            class="quran-form-control @error('source') is-invalid @enderror"
                            value="{{ old('source', $banner->source) }}"
                            placeholder="ناوی سوورەت و ژمارەی ئایەت بنووسە..."
                        >
                        @error('source')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="verse">
                            دەقی کوردی (مانا یان تەفسیر)
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="verse"
                            id="verse"
                            rows="3"
                            class="quran-form-control @error('verse') is-invalid @enderror"
                            required
                            placeholder="مانای ئایەتەکە بە کوردی بنووسە..."
                        >{{ old('verse', $banner->verse) }}</textarea>
                        @error('verse')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Linking Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-link-45deg me-2"></i>
                    بەستنەوە بە سوورەت و ئایەت (بژاردە)
                </h6>
                <p class="text-muted small mb-3">
                    ئەگەر ئەم بەشە دیاری بکەیت، کاتێک بەکارهێنەر لە مۆبایلەکەیدا کلیک لەسەر بانەرەکە دەکات، ڕاستەوخۆ دەچێتە سەر خوێنەری ئەو ئایەتە.
                </p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="surah_id">
                            سوورەت
                        </label>
                        <select name="surah_id"
                                id="surah_id"
                                class="quran-form-select @error('surah_id') is-invalid @enderror">
                            <option value="">-- هیچ سوورەتێک هەڵمەبژێرە --</option>
                            @foreach($surahs as $surah)
                                <option value="{{ $surah->id }}" @selected(old('surah_id', $banner->surah_id) == $surah->id)>
                                    {{ $surah->number }}. {{ $surah->name_ar }} ({{ $surah->name_ku ?? $surah->name_en }})
                                </option>
                            @endforeach
                        </select>
                        @error('surah_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_number">
                            ژمارەی ئایەت
                        </label>
                        <input
                            type="number"
                            name="ayah_number"
                            id="ayah_number"
                            class="quran-form-control @error('ayah_number') is-invalid @enderror"
                            value="{{ old('ayah_number', $banner->ayah_number) }}"
                            min="1"
                            placeholder="ژمارەی ئایەت بنووسە..."
                        >
                        @error('ayah_number')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-sliders me-2"></i>
                    ڕێکخستن و ڕیزبەندی
                </h6>

                <div class="row g-3">
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
                            value="{{ old('order', $banner->order) }}"
                            required
                        >
                        @error('order')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 d-flex align-items-end">
                        <div class="quran-form-check mb-2">
                            <input
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                class="quran-form-check-input"
                                value="1"
                                @checked(old('is_active', $banner->is_active))
                            >
                            <label class="quran-form-check-label" for="is_active">
                                <i class="bi bi-check-circle me-1"></i>
                                چالاک بێت (لەسەر مۆبایل پیشان بدرێت)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
