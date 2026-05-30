@php
    /** @var \App\Models\AdhkarCategory $category */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Content Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-tag me-2"></i>
                    زانیاری هاوپۆل
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_ku">
                            ناوی هاوپۆل (کوردی)
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="name_ku"
                            id="name_ku"
                            class="quran-form-control @error('name_ku') is-invalid @enderror"
                            value="{{ old('name_ku', $category->name_ku) }}"
                            required
                            placeholder="ناوی هاوپۆل بنووسە بە کوردی..."
                        >
                        @error('name_ku')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_ar">
                            ناوی هاوپۆل (عەرەبی)
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
                            placeholder="ناوی هاوپۆل بنووسە بە عەرەبی..."
                        >
                        @error('name_ar')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="name_en">
                            ناوی هاوپۆل (ئینگلیزی)
                        </label>
                        <input
                            type="text"
                            name="name_en"
                            id="name_en"
                            class="quran-form-control @error('name_en') is-invalid @enderror"
                            value="{{ old('name_en', $category->name_en) }}"
                            placeholder="ناوی هاوپۆل بنووسە بە ئینگلیزی..."
                        >
                        @error('name_en')
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
                    ڕێکخستن و دیزاین
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="icon">
                            ئایکۆنی ویجێت (بۆ مۆبایل)
                        </label>
                        <input
                            type="text"
                            name="icon"
                            id="icon"
                            class="quran-form-control @error('icon') is-invalid @enderror"
                            value="{{ old('icon', $category->icon) }}"
                            placeholder="نموونە: wb_sunny_rounded یان dark_mode_outlined..."
                        >
                        <p class="text-muted small mt-1">ئەم ئایکۆنە لە لاپەڕەی ئەزکاری ئەپەکە بەکاردێت.</p>
                        @error('icon')
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
                                چالاک بێت (لەسەر مۆبایل پیشان بدرێت)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
