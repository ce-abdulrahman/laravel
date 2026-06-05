{{-- resources/views/ayahs/_form.blade.php --}}
<div class="row g-4">
    <!-- Main Information Section -->
    <div class="col-lg-8">
        <div class="quran-card h-100 shadow-sm border-0">
            <div class="quran-card-header border-0 bg-transparent pb-0">
                <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                    <i class="bi bi-journal-text me-2"></i>
                    {{ __('ayahs.main_information') }}
                </h6>
            </div>
            <div class="quran-card-body pt-3">
                <!-- Surah Selection -->
                <div class="mb-4">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="surah_id">
                        {{ __('ayahs.surah') }} <span class="text-danger">*</span>
                    </label>
                    <select name="surah_id" id="surah_id"
                            class="quran-form-select @error('surah_id') is-invalid @enderror" required>
                        <option value="">{{ __('ayahs.select_surah') }}</option>
                        @foreach($surahs as $surah)
                        <option value="{{ $surah->id }}" {{ old('surah_id', isset($ayah) ? $ayah->surah_id : '') == $surah->id ? 'selected' : '' }}>
                            {{ $surah->id }}. {{ $surah->name_ar }} ({{ $surah->name_en }})
                        </option>
                        @endforeach
                    </select>
                    @error('surah_id')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ayah Number -->
                <div class="mb-4">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="ayah_number">
                        {{ __('ayahs.ayah_number') }} <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="ayah_number" id="ayah_number"
                           class="quran-form-control @error('ayah_number') is-invalid @enderror"
                           value="{{ old('ayah_number', isset($ayah) ? $ayah->ayah_number : '') }}" min="1" required>
                    @error('ayah_number')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ayah Text (Uthmani) -->
                <div class="mb-4">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="text_uthmani">
                        {{ __('ayahs.text_uthmani') }} <span class="text-danger">*</span>
                    </label>
                    <textarea name="text_uthmani" id="text_uthmani" rows="4"
                              class="quran-form-control arabic-text @error('text_uthmani') is-invalid @enderror"
                              style="font-family: 'CustomArFont', 'Amiri', serif !important; font-size: 22px; line-height: 1.8;"
                              dir="rtl" required>{{ old('text_uthmani', isset($ayah) ? $ayah->text_uthmani : '') }}</textarea>
                    @error('text_uthmani')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ayah Text (Simple) -->
                <div class="mb-4">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="text_simple">
                        {{ __('ayahs.text_simple') }}
                    </label>
                    <textarea name="text_simple" id="text_simple" rows="3"
                              class="quran-form-control arabic-text @error('text_simple') is-invalid @enderror"
                              style="font-family: 'CustomArFont', 'Amiri', serif !important; font-size: 20px; line-height: 1.8;"
                              dir="rtl">{{ old('text_simple', isset($ayah) ? $ayah->text_simple : '') }}</textarea>
                    @error('text_simple')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar / Additional Settings Section -->
    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- Additional Metadata Card -->
        <div class="quran-card shadow-sm border-0">
            <div class="quran-card-header border-0 bg-transparent pb-0">
                <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('ayahs.additional_information') }}
                </h6>
            </div>
            <div class="quran-card-body pt-3">
                <!-- Page Number -->
                <div class="mb-3">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="page_number">
                        {{ __('ayahs.page_number') }}
                    </label>
                    <input type="number" name="page_number" id="page_number"
                           class="quran-form-control @error('page_number') is-invalid @enderror"
                           value="{{ old('page_number', isset($ayah) ? $ayah->page_number : '') }}" min="1" max="604">
                    @error('page_number')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Juz Number -->
                <div class="mb-3">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="juz_number">
                        {{ __('ayahs.juz_number') }}
                    </label>
                    <select name="juz_number" id="juz_number"
                            class="quran-form-select @error('juz_number') is-invalid @enderror">
                        <option value="">{{ __('ayahs.select_juz') }}</option>
                        @foreach($juzNumbers as $juz)
                        <option value="{{ $juz }}" {{ old('juz_number', isset($ayah) ? $ayah->juz_number : '') == $juz ? 'selected' : '' }}>
                            {{ __('ayahs.juz') }} {{ $juz }}
                        </option>
                        @endforeach
                    </select>
                    @error('juz_number')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Hizb Number -->
                <div class="mb-3">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="hizb_number">
                        {{ __('ayahs.hizb_number') }}
                    </label>
                    <input type="number" name="hizb_number" id="hizb_number"
                           class="quran-form-control @error('hizb_number') is-invalid @enderror"
                           value="{{ old('hizb_number', isset($ayah) ? $ayah->hizb_number : '') }}" min="1" max="60">
                    @error('hizb_number')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Rub Number -->
                <div class="mb-3">
                    <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold" for="rub_number">
                        {{ __('ayahs.rub_number') }}
                    </label>
                    <input type="number" name="rub_number" id="rub_number"
                           class="quran-form-control @error('rub_number') is-invalid @enderror"
                           value="{{ old('rub_number', isset($ayah) ? $ayah->rub_number : '') }}" min="1" max="240">
                    @error('rub_number')
                    <div class="quran-invalid-feedback text-danger mt-1 text-xs">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="quran-card shadow-sm border-0">
            <div class="quran-card-header border-0 bg-transparent pb-0">
                <h6 class="quran-card-title text-emerald-800 dark:text-emerald-400 font-bold fs-5">
                    <i class="bi bi-gear me-2"></i>
                    {{ __('ayahs.settings') }}
                </h6>
            </div>
            <div class="quran-card-body pt-3">
                <!-- Sajda Flag -->
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" name="sajda_flag" id="sajda_flag" value="1"
                           class="form-check-input @error('sajda_flag') is-invalid @enderror"
                           {{ old('sajda_flag', isset($ayah) ? $ayah->sajda_flag : false) ? 'checked' : '' }}>
                    <label class="form-check-label text-zinc-700 dark:text-zinc-300 font-semibold" for="sajda_flag">
                        <i class="bi bi-star-fill text-warning me-1"></i>
                        {{ __('ayahs.sajda_flag') }}
                    </label>
                </div>
                <small class="text-zinc-500 dark:text-zinc-400 d-block mb-4 leading-normal">
                    {{ __('ayahs.sajda_flag_help') }}
                </small>

                <!-- Active Status -->
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="form-check-input @error('is_active') is-invalid @enderror"
                           {{ old('is_active', isset($ayah) ? $ayah->is_active : true) ? 'checked' : '' }}>
                    <label class="form-check-label text-zinc-700 dark:text-zinc-300 font-semibold" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        {{ __('ayahs.is_active') }}
                    </label>
                </div>
                <small class="text-zinc-500 dark:text-zinc-400 d-block leading-normal">
                    {{ __('ayahs.is_active_help') }}
                </small>
            </div>
        </div>
    </div>
</div>
