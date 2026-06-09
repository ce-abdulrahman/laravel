{{-- resources/views/languages/_form.blade.php --}}
@php
    /** @var \App\Models\Language $language */
@endphp

<div class="quran-form">
    <div class="row g-4">
        {{-- Language Info Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-translate me-2"></i>
                    Language Information
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="code">
                            Language Code (ISO 639-1) <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="code"
                            id="code"
                            class="quran-form-control @error('code') is-invalid @enderror"
                            value="{{ old('code', $language->code) }}"
                            required
                            maxlength="10"
                            placeholder="e.g. en, ku, ar, tr"
                        >
                        @error('code')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="name">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="quran-form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $language->name) }}"
                            required
                            maxlength="100"
                            placeholder="e.g. English, Turkish"
                        >
                        @error('name')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="native_name">
                            Native Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="native_name"
                            id="native_name"
                            class="quran-form-control @error('native_name') is-invalid @enderror"
                            value="{{ old('native_name', $language->native_name) }}"
                            required
                            maxlength="100"
                            placeholder="e.g. English, Türkçe"
                        >
                        @error('native_name')
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
                    Configuration & Display
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="quran-form-label" for="direction">
                            Text Direction <span class="text-danger">*</span>
                        </label>
                        <select
                            name="direction"
                            id="direction"
                            class="quran-form-control @error('direction') is-invalid @enderror"
                            required
                        >
                            <option value="ltr" @selected(old('direction', $language->direction) === 'ltr')>Left-to-Right (LTR)</option>
                            <option value="rtl" @selected(old('direction', $language->direction) === 'rtl')>Right-to-Left (RTL)</option>
                        </select>
                        @error('direction')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="flag">
                            Flag (Emoji or Code)
                        </label>
                        <input
                            type="text"
                            name="flag"
                            id="flag"
                            class="quran-form-control @error('flag') is-invalid @enderror"
                            value="{{ old('flag', $language->flag) }}"
                            maxlength="10"
                            placeholder="e.g. 🇬🇧, 🇹🇷, 🌙"
                        >
                        @error('flag')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="order">
                            Display Order <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="order"
                            id="order"
                            class="quran-form-control @error('order') is-invalid @enderror"
                            value="{{ old('order', $language->order) }}"
                            required
                            min="0"
                        >
                        @error('order')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 d-flex align-items-end mt-4">
                        <div class="quran-form-check mb-2 me-4">
                            <input
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                class="quran-form-check-input"
                                value="1"
                                @checked(old('is_active', $language->is_active))
                            >
                            <label class="quran-form-check-label" for="is_active">
                                <i class="bi bi-check-circle me-1"></i>
                                Is Active (Available for use)
                            </label>
                        </div>

                        <div class="quran-form-check mb-2">
                            <input
                                type="checkbox"
                                name="is_default"
                                id="is_default"
                                class="quran-form-check-input"
                                value="1"
                                @checked(old('is_default', $language->is_default))
                            >
                            <label class="quran-form-check-label" for="is_default">
                                <i class="bi bi-star me-1"></i>
                                Is Default Language
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
