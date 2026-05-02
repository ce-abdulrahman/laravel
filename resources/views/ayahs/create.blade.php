{{-- resources/views/ayahs/create.blade.php --}}
@extends('layouts.app')

@section('title', __('ayahs.create_ayah'))

@section('content')
<div class="quran-content-container">
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">{{ __('ayahs.create_ayah') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('sidebar.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ayahs.index') }}">{{ __('ayahs.ayahs') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('ayahs.create') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('common.back_to_list') }}</span>
        </a>
    </div>

    {{-- Create Form --}}
    <form method="POST" action="{{ route('ayahs.store') }}" class="quran-form">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Main Information --}}
                <div class="quran-card">
                    <div class="quran-card-header">
                        <h6 class="quran-card-title">
                            <i class="bi bi-journal-text me-2"></i>
                            {{ __('ayahs.main_information') }}
                        </h6>
                    </div>
                    <div class="quran-card-body">
                        {{-- Surah Selection --}}
                        <div class="mb-4">
                            <label class="quran-form-label" for="surah_id">
                                {{ __('ayahs.surah') }} <span class="text-danger">*</span>
                            </label>
                            <select name="surah_id" id="surah_id"
                                    class="quran-form-select @error('surah_id') is-invalid @enderror" required>
                                <option value="">{{ __('ayahs.select_surah') }}</option>
                                @foreach($surahs as $surah)
                                <option value="{{ $surah->id }}" {{ old('surah_id') == $surah->id ? 'selected' : '' }}>
                                    {{ $surah->id }}. {{ $surah->name_ar }} ({{ $surah->name_en }})
                                </option> 
                                @endforeach
                            </select>
                            @error('surah_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ayah Number --}}
                        <div class="mb-4">
                            <label class="quran-form-label" for="ayah_number">
                                {{ __('ayahs.ayah_number') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="ayah_number" id="ayah_number"
                                   class="quran-form-control @error('ayah_number') is-invalid @enderror"
                                   value="{{ old('ayah_number') }}" min="1" required>
                            @error('ayah_number')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ayah Text (Uthmani) --}}
                        <div class="mb-4">
                            <label class="quran-form-label" for="text_uthmani">
                                {{ __('ayahs.text_uthmani') }} <span class="text-danger">*</span>
                            </label>
                            <textarea name="text_uthmani" id="text_uthmani" rows="4"
                                      class="quran-form-control arabic-text @error('text_uthmani') is-invalid @enderror"
                                      dir="rtl" required>{{ old('text_uthmani') }}</textarea>
                            @error('text_uthmani')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ayah Text (Simple) --}}
                        <div class="mb-4">
                            <label class="quran-form-label" for="text_simple">
                                {{ __('ayahs.text_simple') }}
                            </label>
                            <textarea name="text_simple" id="text_simple" rows="3"
                                      class="quran-form-control arabic-text @error('text_simple') is-invalid @enderror"
                                      dir="rtl">{{ old('text_simple') }}</textarea>
                            @error('text_simple')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Additional Information --}}
                <div class="quran-card mb-4">
                    <div class="quran-card-header">
                        <h6 class="quran-card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('ayahs.additional_information') }}
                        </h6>
                    </div>
                    <div class="quran-card-body">
                        {{-- Page Number --}}
                        <div class="mb-3">
                            <label class="quran-form-label" for="page_number">
                                {{ __('ayahs.page_number') }}
                            </label>
                            <input type="number" name="page_number" id="page_number"
                                   class="quran-form-control @error('page_number') is-invalid @enderror"
                                   value="{{ old('page_number') }}" min="1" max="604">
                            @error('page_number')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Juz Number --}}
                        <div class="mb-3">
                            <label class="quran-form-label" for="juz_number">
                                {{ __('ayahs.juz_number') }}
                            </label>
                            <select name="juz_number" id="juz_number"
                                    class="quran-form-select @error('juz_number') is-invalid @enderror">
                                <option value="">{{ __('ayahs.select_juz') }}</option>
                                @foreach($juzNumbers as $juz)
                                <option value="{{ $juz }}" {{ old('juz_number') == $juz ? 'selected' : '' }}>
                                    {{ __('ayahs.juz') }} {{ $juz }}
                                </option>
                                @endforeach
                            </select>
                            @error('juz_number')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Hizb Number --}}
                        <div class="mb-3">
                            <label class="quran-form-label" for="hizb_number">
                                {{ __('ayahs.hizb_number') }}
                            </label>
                            <input type="number" name="hizb_number" id="hizb_number"
                                   class="quran-form-control @error('hizb_number') is-invalid @enderror"
                                   value="{{ old('hizb_number') }}" min="1" max="60">
                            @error('hizb_number')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Rub Number --}}
                        <div class="mb-3">
                            <label class="quran-form-label" for="rub_number">
                                {{ __('ayahs.rub_number') }}
                            </label>
                            <input type="number" name="rub_number" id="rub_number"
                                   class="quran-form-control @error('rub_number') is-invalid @enderror"
                                   value="{{ old('rub_number') }}" min="1" max="240">
                            @error('rub_number')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Settings --}}
                <div class="quran-card">
                    <div class="quran-card-header">
                        <h6 class="quran-card-title">
                            <i class="bi bi-gear me-2"></i>
                            {{ __('ayahs.settings') }}
                        </h6>
                    </div>
                    <div class="quran-card-body">
                        {{-- Sajda Flag --}}
                        <div class="quran-form-check mb-3">
                            <input type="checkbox" name="sajda_flag" id="sajda_flag" value="1"
                                   class="quran-form-check-input" {{ old('sajda_flag') ? 'checked' : '' }}>
                            <label class="quran-form-check-label" for="sajda_flag">
                                <i class="bi bi-star-fill text-warning me-2"></i>
                                {{ __('ayahs.sajda_flag') }}
                            </label>
                        </div>
                        <small class="text-muted d-block mb-3">{{ __('ayahs.sajda_flag_help') }}</small>

                        {{-- Active Status --}}
                        <div class="quran-form-check">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   class="quran-form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="quran-form-check-label" for="is_active">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                {{ __('ayahs.is_active') }}
                            </label>
                        </div>
                        <small class="text-muted d-block">{{ __('ayahs.is_active_help') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="quran-form-actions mt-4">
            <button type="submit" class="quran-btn quran-btn-primary">
                <i class="bi bi-save"></i>
                <span>{{ __('common.save') }}</span>
            </button>
            <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-x-lg"></i>
                <span>{{ __('common.cancel') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection
