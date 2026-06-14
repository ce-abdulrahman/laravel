@extends('layouts.app')
@section('title', 'دەستکاری کەتێگۆری')
@section('page-title', 'دەستکاری کەتێگۆری')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">دەستکەوتەکان</a></li>
    <li class="breadcrumb-item"><a href="{{ route('achievement-categories.index') }}">کەتێگۆریەکان</a></li>
    <li class="breadcrumb-item active" aria-current="page">دەستکاری</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ $category->icon }} دەستکاری: {{ $category->name ?: '(وەرگێڕان نییە)' }}</h1>
        </div>
        <a href="{{ route('achievement-categories.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> گەڕانەوە
        </a>
    </div>

    <div class="quran-form-container">
        <form method="POST" action="{{ route('achievement-categories.update', $category) }}">
            @csrf @method('PUT')
            <div class="quran-form">
                {{-- Basic Info --}}
                <div class="quran-form-section mb-4">
                    <h6 class="quran-form-section-title">
                        <i class="bi bi-info-circle me-2"></i>زانیاری سەرەکی
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="quran-form-label" for="cat_icon">ئایکۆن</label>
                            <input type="text" name="icon" id="cat_icon" value="{{ old('icon', $category->icon) }}"
                                   class="quran-form-control text-center" style="font-size:1.5rem;">
                        </div>
                        <div class="col-md-3">
                            <label class="quran-form-label" for="cat_sort">ڕیزبەندی</label>
                            <input type="number" name="sort_order" id="cat_sort" value="{{ old('sort_order', $category->sort_order) }}" min="0"
                                   class="quran-form-control">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="quran-form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" class="quran-form-check-input" value="1"
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="quran-form-check-label" for="is_active">
                                    <i class="bi bi-check-circle me-1"></i> چالاک
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Translations --}}
                <div class="quran-form-section mb-4">
                    <h6 class="quran-form-section-title">
                        <i class="bi bi-translate me-2"></i>وەرگێڕانەکان
                    </h6>

                    <ul class="nav nav-tabs quran-tabs mb-3" role="tablist">
                        @foreach($languages as $i => $lang)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $i === 0 ? 'active' : '' }}"
                                    id="tab-cat-edit-{{ $lang->code }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#pane-cat-edit-{{ $lang->code }}"
                                    type="button" role="tab">
                                {{ $lang->flag }} {{ $lang->native_name }}
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $i => $lang)
                        @php $t = $category->translations->firstWhere('locale', $lang->code); @endphp
                        <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
                             id="pane-cat-edit-{{ $lang->code }}" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="quran-form-label" for="cat_name_{{ $lang->code }}">
                                        ناوی کەتێگۆری ({{ $lang->native_name }})
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           id="cat_name_{{ $lang->code }}"
                                           name="translations[{{ $lang->code }}][name]"
                                           value="{{ old("translations.{$lang->code}.name", $t?->name) }}"
                                           class="quran-form-control"
                                           dir="{{ $lang->direction }}"
                                           placeholder="ناوی کەتێگۆری بە {{ $lang->native_name }}">
                                </div>
                                <div class="col-md-7">
                                    <label class="quran-form-label" for="cat_desc_{{ $lang->code }}">
                                        وصف ({{ $lang->native_name }})
                                    </label>
                                    <input type="text"
                                           id="cat_desc_{{ $lang->code }}"
                                           name="translations[{{ $lang->code }}][description]"
                                           value="{{ old("translations.{$lang->code}.description", $t?->description) }}"
                                           class="quran-form-control"
                                           dir="{{ $lang->direction }}"
                                           placeholder="وصفی کەتێگۆری بە {{ $lang->native_name }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i> پاشەکەوتکردن
                </button>
                <a href="{{ route('achievement-categories.index') }}" class="quran-btn quran-btn-outline-secondary">
                    پاشگەزبوونەوە
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
