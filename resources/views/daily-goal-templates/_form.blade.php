{{-- resources/views/daily-goal-templates/_form.blade.php --}}
@php
    /** @var \App\Models\DailyGoalTemplate $template */
    /** @var \Illuminate\Support\Collection|\App\Models\Language[] $activeLanguages */
@endphp

<div class="quran-form">
    <div class="row g-4">
        {{-- Config Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-gear me-2"></i>
                    Configuration
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="value">
                            {{ __('daily_goals.template_value') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="value"
                            id="value"
                            class="quran-form-control @error('value') is-invalid @enderror"
                            value="{{ old('value', $template->value) }}"
                            min="1"
                            required
                        >
                        @error('value')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                value="1"
                                @checked(old('is_active', $template->is_active))
                            >
                            <label class="form-check-label fw-semibold" for="is_active">
                                {{ __('daily_goals.is_active') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Translations Section --}}
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-translate me-2"></i>
                    Translations
                </h6>

                <div class="row g-3">
                    @foreach($activeLanguages as $lang)
                        <div class="col-12 p-3 mb-3 bg-light bg-opacity-50 rounded-3 border">
                            <h6 class="fw-bold mb-3 text-secondary d-flex align-items-center gap-2">
                                <span class="badge bg-primary">{{ strtoupper($lang->code) }}</span>
                                {{ $lang->name }}
                            </h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="quran-form-label" for="translations_{{ $lang->code }}_title">
                                        {{ __('daily_goals.field_title') }} ({{ $lang->name }})
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="translations[{{ $lang->code }}][title]"
                                        id="translations_{{ $lang->code }}_title"
                                        class="quran-form-control @error('translations.' . $lang->code . '.title') is-invalid @enderror"
                                        value="{{ old('translations.' . $lang->code . '.title', $template->getTranslation('title', $lang->code)) }}"
                                        @if($lang->isRtl()) dir="rtl" @endif
                                        required
                                    >
                                    @error('translations.' . $lang->code . '.title')
                                        <div class="quran-invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="quran-form-label" for="translations_{{ $lang->code }}_description">
                                        {{ __('daily_goals.field_description') }} ({{ $lang->name }})
                                    </label>
                                    <input
                                        type="text"
                                        name="translations[{{ $lang->code }}][description]"
                                        id="translations_{{ $lang->code }}_description"
                                        class="quran-form-control @error('translations.' . $lang->code . '.description') is-invalid @enderror"
                                        value="{{ old('translations.' . $lang->code . '.description', $template->getTranslation('description', $lang->code)) }}"
                                        @if($lang->isRtl()) dir="rtl" @endif
                                    >
                                    @error('translations.' . $lang->code . '.description')
                                        <div class="quran-invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
