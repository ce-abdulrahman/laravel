{{-- resources/views/tasbihs/_form.blade.php --}}
@php
    /** @var \App\Models\Tasbih $tasbih */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-card-text me-2"></i>
                    {{ __('tasbihs.sections.content') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="quran-form-label" for="name">
                            {{ __('tasbihs.fields.name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="quran-form-control arabic-text @error('name') is-invalid @enderror"
                            value="{{ old('name', $tasbih->name) }}"
                            required
                            dir="rtl"
                            style="font-family: var(--quran-font, 'Amiri Quran', serif); font-size: 1.1rem;"
                            placeholder="{{ __('tasbihs.placeholders.name') }}"
                        >
                        @error('name')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="target">
                            {{ __('tasbihs.fields.target') }}
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
                            placeholder="{{ __('tasbihs.placeholders.target') }}"
                        >
                        @error('target')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="quran-form-check">
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
                                {{ __('tasbihs.fields.is_active') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
