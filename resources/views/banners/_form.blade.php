{{-- resources/views/banners/_form.blade.php --}}
@php
    /** @var \App\Models\Banner $banner */
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Surah[] $surahs */
@endphp

<div class="quran-form">
    <div class="row g-4">
        {{-- Content Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-card-text me-2"></i>
                    {{ __('banners.sections.content') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="title_arabic">
                            {{ __('banners.fields.title_arabic') }}
                        </label>
                        <input
                            type="text"
                            name="title_arabic"
                            id="title_arabic"
                            class="quran-form-control arabic-text @error('title_arabic') is-invalid @enderror"
                            value="{{ old('title_arabic', $banner->title_arabic) }}"
                            dir="rtl"
                            placeholder="{{ __('banners.placeholders.title_arabic') }}"
                            style="font-family: var(--quran-font, 'Amiri Quran', serif); font-size: 1.1rem;"
                        >
                        @error('title_arabic')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="source">
                            {{ __('banners.fields.source') }}
                        </label>
                        <input
                            type="text"
                            name="source"
                            id="source"
                            class="quran-form-control @error('source') is-invalid @enderror"
                            value="{{ old('source', $banner->source) }}"
                            placeholder="{{ __('banners.placeholders.source') }}"
                        >
                        @error('source')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="verse">
                            {{ __('banners.fields.verse') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="verse"
                            id="verse"
                            rows="3"
                            class="quran-form-control @error('verse') is-invalid @enderror"
                            required
                            placeholder="{{ __('banners.placeholders.verse') }}"
                        >{{ old('verse', $banner->verse) }}</textarea>
                        @error('verse')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Linking Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-link-45deg me-2"></i>
                    {{ __('banners.sections.linking') }}
                </h6>
                <p class="text-muted small mb-3">
                    {{ __('banners.sections.linking_hint') }}
                </p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="surah_id">
                            {{ __('banners.fields.surah') }}
                        </label>
                        <select name="surah_id"
                                id="surah_id"
                                class="quran-form-select @error('surah_id') is-invalid @enderror">
                            <option value="">{{ __('banners.placeholders.no_surah') }}</option>
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
                            {{ __('banners.fields.ayah_number') }}
                        </label>
                        <input
                            type="number"
                            name="ayah_number"
                            id="ayah_number"
                            class="quran-form-control @error('ayah_number') is-invalid @enderror"
                            value="{{ old('ayah_number', $banner->ayah_number) }}"
                            min="1"
                            placeholder="{{ __('banners.placeholders.ayah_number') }}"
                        >
                        @error('ayah_number')
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
                    {{ __('banners.sections.configuration') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="order">
                            {{ __('banners.fields.order') }}
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
                                {{ __('banners.fields.is_active') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
