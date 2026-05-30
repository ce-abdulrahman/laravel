@php
    /** @var \App\Models\Tasbih $tasbih */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Content Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-card-text me-2"></i>
                    ناوەڕۆکی تەسبیح
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="name">
                            ناوی زیکر
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="quran-form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $tasbih->name) }}"
                            required
                            placeholder="ناوی زیکرەکە لێرە بنووسە (بۆ نموونە: سُبْحَانَ اللهِ)..."
                        >
                        @error('name')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="target">
                            ئامانج (ژمارەی دووبارەکردنەوە)
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="target"
                            id="target"
                            class="quran-form-control @error('target') is-invalid @enderror"
                            value="{{ old('target', $tasbih->target) }}"
                            min="1"
                            required
                            placeholder="بۆ نموونە: ٣٣"
                        >
                        @error('target')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex align-items-end">
                        <div class="quran-form-check mb-2">
                            <input
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                class="quran-form-check-input"
                                value="1"
                                @checked(old('is_active', $tasbih->is_active))
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
