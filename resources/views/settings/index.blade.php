{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.app')

@section('title', __('settings.titles.index'))
@section('page-title', __('settings.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('settings.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('settings.titles.index') }}</h1>
            <div class="text-muted">{{ __('settings.hints.manage') }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.update', $settings) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- General Settings -->
            <div class="col-lg-6">
                <div class="quran-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-gear me-2"></i>
                            {{ __('settings.sections.general') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <div class="mb-3">
                            <label class="quran-form-label" for="app_name">
                                {{ __('settings.fields.app_name') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="app_name" id="app_name" 
                                   class="quran-form-control @error('app_name') is-invalid @enderror"
                                   value="{{ old('app_name', $settings->app_name) }}" required>
                            @error('app_name')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="app_logo">
                                {{ __('settings.fields.app_logo') }}
                            </label>
                            @if($settings->app_logo)
                            <div class="mb-2">
                                <img src="{{ Storage::url($settings->app_logo) }}" 
                                     alt="Logo" style="max-height: 60px;">
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="remove_logo" id="remove_logo" 
                                           class="form-check-input" value="1">
                                    <label class="form-check-label" for="remove_logo">
                                        {{ __('settings.actions.remove_logo') }}
                                    </label>
                                </div>
                            </div>
                            @endif
                            <input type="file" name="app_logo" id="app_logo" 
                                   class="quran-form-control @error('app_logo') is-invalid @enderror"
                                   accept="image/*">
                            @error('app_logo')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="default_language">
                                {{ __('settings.fields.default_language') }}
                            </label>
                            <select name="default_language" id="default_language" 
                                    class="quran-form-select @error('default_language') is-invalid @enderror">
                                <option value="">{{ __('settings.select_language') }}</option>
                                @foreach($languages as $code => $name)
                                <option value="{{ $code }}" {{ old('default_language', $settings->default_language) == $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                                @endforeach
                            </select>
                            @error('default_language')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="contact_email">
                                {{ __('settings.fields.contact_email') }}
                            </label>
                            <input type="email" name="contact_email" id="contact_email" 
                                   class="quran-form-control @error('contact_email') is-invalid @enderror"
                                   value="{{ old('contact_email', $settings->contact_email) }}">
                            @error('contact_email')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="about_text">
                                {{ __('settings.fields.about_text') }}
                            </label>
                            <textarea name="about_text" id="about_text" rows="4"
                                      class="quran-form-control @error('about_text') is-invalid @enderror">{{ old('about_text', $settings->about_text) }}</textarea>
                            @error('about_text')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Default Settings -->
            <div class="col-lg-6">
                <div class="quran-card">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-star me-2"></i>
                            {{ __('settings.sections.defaults') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <div class="mb-3">
                            <label class="quran-form-label" for="default_tafsir_book_id">
                                {{ __('settings.fields.default_tafsir') }}
                            </label>
                            <select name="default_tafsir_book_id" id="default_tafsir_book_id" 
                                    class="quran-form-select @error('default_tafsir_book_id') is-invalid @enderror">
                                <option value="">{{ __('settings.select_tafsir') }}</option>
                                @foreach($tafsirBooks as $book)
                                <option value="{{ $book->id }}" {{ old('default_tafsir_book_id', $settings->default_tafsir_book_id) == $book->id ? 'selected' : '' }}>
                                    {{ $book->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('default_tafsir_book_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="default_reciter_id">
                                {{ __('settings.fields.default_reciter') }}
                            </label>
                            <select name="default_reciter_id" id="default_reciter_id" 
                                    class="quran-form-select @error('default_reciter_id') is-invalid @enderror">
                                <option value="">{{ __('settings.select_reciter') }}</option>
                                @foreach($reciters as $reciter)
                                <option value="{{ $reciter->id }}" {{ old('default_reciter_id', $settings->default_reciter_id) == $reciter->id ? 'selected' : '' }}>
                                    {{ $reciter->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('default_reciter_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="default_qiraah_id">
                                {{ __('settings.fields.default_qiraat') }}
                            </label>
                            <select name="default_qiraah_id" id="default_qiraah_id" 
                                    class="quran-form-select @error('default_qiraah_id') is-invalid @enderror">
                                <option value="">{{ __('settings.select_qiraat') }}</option>
                                @foreach($qiraats as $qiraat)
                                <option value="{{ $qiraat->id }}" {{ old('default_qiraah_id', $settings->default_qiraah_id) == $qiraat->id ? 'selected' : '' }}>
                                    {{ $qiraat->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('default_qiraah_id')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Font Settings Card -->
                <div class="quran-card mt-4">
                    <div class="quran-card-header">
                        <h5 class="quran-card-title">
                            <i class="bi bi-fonts me-2"></i>
                            {{ __('settings.sections.fonts') }}
                        </h5>
                    </div>
                    <div class="quran-card-body">
                        <div class="mb-3">
                            <label class="quran-form-label" for="font_ar">
                                {{ __('settings.fields.font_ar') }}
                            </label>
                            <select name="font_ar" id="font_ar" class="quran-form-select @error('font_ar') is-invalid @enderror">
                                @foreach($availableArFonts as $font)
                                    <option value="{{ $font }}" {{ old('font_ar', $settings->font_ar) == $font ? 'selected' : '' }}>
                                        {{ $font }}
                                    </option>
                                @endforeach
                            </select>
                            @error('font_ar')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="font_ku">
                                {{ __('settings.fields.font_ku') }}
                            </label>
                            <select name="font_ku" id="font_ku" class="quran-form-select @error('font_ku') is-invalid @enderror">
                                @foreach($availableKuFonts as $font)
                                    <option value="{{ $font }}" {{ old('font_ku', $settings->font_ku) == $font ? 'selected' : '' }}>
                                        {{ $font }}
                                    </option>
                                @endforeach
                            </select>
                            @error('font_ku')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="font_en">
                                {{ __('settings.fields.font_en') }}
                            </label>
                            <select name="font_en" id="font_en" class="quran-form-select @error('font_en') is-invalid @enderror">
                                @foreach($availableEnFonts as $font)
                                    <option value="{{ $font }}" {{ old('font_en', $settings->font_en) == $font ? 'selected' : '' }}>
                                        {{ $font }}
                                    </option>
                                @endforeach
                            </select>
                            @error('font_en')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quran-form-actions mt-4">
            <button type="submit" class="quran-btn quran-btn-primary">
                <i class="bi bi-save me-1"></i>
                {{ __('common.save') }}
            </button>
        </div>
    </form>
</div>
@endsection