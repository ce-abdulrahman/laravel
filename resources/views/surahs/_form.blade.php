@php
    /** @var \App\Models\Surah $surah */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Basic Information Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('surah.sections.basic_info') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="quran-form-label" for="number">
                            {{ __('surah.fields.number') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="number"
                            id="number"
                            class="quran-form-control @error('number') is-invalid @enderror"
                            value="{{ old('number', $surah->number) }}"
                            min="1"
                            max="114"
                            required
                        >
                        @error('number')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @foreach(\App\Models\Language::activeList() as $lang)
                        @php
                            $isAr = $lang->code === 'ar';
                        @endphp
                        <div class="col-md-5">
                            <label class="quran-form-label" for="translations_{{ $lang->code }}_name">
                                @if(Lang::has('surah.fields.name_' . $lang->code))
                                    {{ __('surah.fields.name_' . $lang->code) }}
                                @else
                                    Name ({{ $lang->name }})
                                @endif
                                @if($isAr) <span class="text-danger">*</span> @endif
                            </label>
                            <input
                                type="text"
                                name="translations[{{ $lang->code }}][name]"
                                id="translations_{{ $lang->code }}_name"
                                class="quran-form-control @if($isAr) arabic-text @endif @error('translations.' . $lang->code . '.name') is-invalid @enderror"
                                value="{{ old('translations.' . $lang->code . '.name', $surah->getTranslation('name', $lang->code)) }}"
                                @if($lang->isRtl()) dir="rtl" @endif
                                @if($isAr) required @endif
                            >
                            @error('translations.' . $lang->code . '.name')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Classification Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-tags me-2"></i>
                    {{ __('surah.sections.classification') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="quran-form-label" for="revelation_type">
                            {{ __('surah.fields.revelation_type') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="revelation_type"
                                id="revelation_type"
                                class="quran-form-select @error('revelation_type') is-invalid @enderror"
                                required>
                            @php
                                $value = old('revelation_type', $surah->revelation_type);
                                $value = strtolower((string) $value);
                            @endphp
                            <option value="meccan" @selected($value === 'meccan')>
                                {{ __('surah.revelation_types.meccan') }}
                            </option>
                            <option value="medinan" @selected($value === 'medinan')>
                                {{ __('surah.revelation_types.medinan') }}
                            </option>
                        </select>
                        @error('revelation_type')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="quran-form-label" for="ayah_count">
                            {{ __('surah.fields.ayah_count') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="ayah_count"
                            id="ayah_count"
                            class="quran-form-control @error('ayah_count') is-invalid @enderror"
                            value="{{ old('ayah_count', $surah->ayah_count) }}"
                            min="1"
                            required
                        >
                        @error('ayah_count')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Position Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-pin-map me-2"></i>
                    {{ __('surah.sections.position') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="quran-form-label" for="page_start">
                            {{ __('surah.fields.page_start') }}
                        </label>
                        <input
                            type="number"
                            name="page_start"
                            id="page_start"
                            class="quran-form-control @error('page_start') is-invalid @enderror"
                            value="{{ old('page_start', $surah->page_start) }}"
                            min="1"
                            max="604"
                        >
                        @error('page_start')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="quran-form-label" for="page_end">
                            {{ __('surah.fields.page_end') }}
                        </label>
                        <input
                            type="number"
                            name="page_end"
                            id="page_end"
                            class="quran-form-control @error('page_end') is-invalid @enderror"
                            value="{{ old('page_end', $surah->page_end) }}"
                            min="1"
                            max="604"
                        >
                        @error('page_end')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="quran-form-label" for="juz_start">
                            {{ __('surah.fields.juz_start') }}
                        </label>
                        <input
                            type="number"
                            name="juz_start"
                            id="juz_start"
                            class="quran-form-control @error('juz_start') is-invalid @enderror"
                            value="{{ old('juz_start', $surah->juz_start) }}"
                            min="1"
                            max="30"
                        >
                        @error('juz_start')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="quran-form-label" for="juz_end">
                            {{ __('surah.fields.juz_end') }}
                        </label>
                        <input
                            type="number"
                            name="juz_end"
                            id="juz_end"
                            class="quran-form-control @error('juz_end') is-invalid @enderror"
                            value="{{ old('juz_end', $surah->juz_end) }}"
                            min="1"
                            max="30"
                        >
                        @error('juz_end')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-card-text me-2"></i>
                    {{ __('surah.fields.description') }}
                </h6>

                <div class="row g-3">
                    <div class="col-12">
                        <textarea
                            name="description"
                            id="description"
                            rows="4"
                            class="quran-form-control @error('description') is-invalid @enderror"
                            placeholder="{{ __('surah.placeholders.description') }}"
                        >{{ old('description', $surah->description) }}</textarea>
                        @error('description')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Section -->
        <div class="col-12">
            <div class="quran-form-section">
                <div class="quran-form-check">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        class="quran-form-check-input"
                        value="1"
                        @checked(old('is_active', $surah->is_active))
                    >
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('surah.fields.is_active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
