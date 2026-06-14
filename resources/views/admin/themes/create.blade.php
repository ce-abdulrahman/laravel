@extends('layouts.app')

@section('title', 'New Theme')
@section('page-title', 'New Theme')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.themes.dashboard') }}">Themes Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.themes.index') }}">Manage Themes</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Theme</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">New Theme</h1>
            <div class="text-muted">Configure visual configurations and metadata values for the new theme.</div>
        </div>
        <a href="{{ route('admin.themes.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Form --}}
    <div class="quran-form-container card border-0 shadow-sm p-4">
        <form method="POST" action="{{ route('admin.themes.store') }}">
            @csrf

            <div class="row g-4">
                {{-- Column 1 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="theme_key">Theme Key <span class="text-danger">*</span></label>
                        <input type="text" name="theme_key" id="theme_key" class="form-control @error('theme_key') is-invalid @enderror" value="{{ old('theme_key') }}" required placeholder="e.g. kaaba_theme">
                        @error('theme_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="category_id">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="unlock_type">Unlock Method <span class="text-danger">*</span></label>
                        <select name="unlock_type" id="unlock_type" class="form-select @error('unlock_type') is-invalid @enderror" required>
                            <option value="free" @selected(old('unlock_type') === 'free')>Free</option>
                            <option value="points" @selected(old('unlock_type') === 'points')>Points Unlock</option>
                            <option value="streak" @selected(old('unlock_type') === 'streak')>Streak Milestone</option>
                            <option value="achievement" @selected(old('unlock_type') === 'achievement')>Achievement Unlocked</option>
                            <option value="event" @selected(old('unlock_type') === 'event')>Event Triggered</option>
                            <option value="premium" @selected(old('unlock_type') === 'premium')>Premium/Paid</option>
                        </select>
                        @error('unlock_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="unlock_value">Unlock Value / Target</label>
                        <input type="text" name="unlock_value" id="unlock_value" class="form-control @error('unlock_value') is-invalid @enderror" value="{{ old('unlock_value') }}" placeholder="e.g. 500 (points), 7 (days), first_tasbih">
                        @error('unlock_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Column 2 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="preview_image">Preview Image Path</label>
                        <input type="text" name="preview_image" id="preview_image" class="form-control @error('preview_image') is-invalid @enderror" value="{{ old('preview_image') }}" placeholder="assets/themes/kaaba/preview.png">
                        @error('preview_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="thumbnail">Thumbnail Path</label>
                        <input type="text" name="thumbnail" id="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" value="{{ old('thumbnail') }}" placeholder="assets/themes/kaaba/thumb.png">
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" for="min_app_version">Min App Version</label>
                            <input type="text" name="min_app_version" id="min_app_version" class="form-control" value="{{ old('min_app_version', '1.0.0') }}" placeholder="1.2.0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" for="sort_order">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                    </div>

                    <div class="d-flex gap-4 mb-3 mt-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1" @checked(old('is_featured'))>
                            <label class="form-check-label fw-semibold" for="is_featured">Featured Theme</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label fw-semibold" for="is_active">Active status</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4 border-light">

                {{-- Dynamic Translations Section --}}
                <div class="col-12">
                    <h5 class="mb-3"><i class="bi bi-translate text-primary me-2"></i> Dynamic Translations</h5>
                    <div class="row g-4">
                        @foreach($languages as $lang)
                            <div class="col-md-4 card border border-light p-3 bg-light-subtle shadow-none">
                                <div class="fw-bold mb-2 text-primary d-flex align-items-center">
                                    <span class="badge bg-primary me-2 text-uppercase">{{ $lang->code }}</span>
                                    {{ $lang->name }}
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Localized Theme Name <span class="text-danger">*</span></label>
                                    <input type="text" name="translations[{{ $lang->code }}][name]" class="form-control form-control-sm" required value="{{ old("translations.{$lang->code}.name") }}">
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold">Description <span class="text-danger">*</span></label>
                                    <textarea name="translations[{{ $lang->code }}][description]" class="form-control form-control-sm" rows="3" required>{{ old("translations.{$lang->code}.description") }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="my-4 border-light">

                {{-- Metadata JSON --}}
                <div class="col-12">
                    <h5 class="mb-2"><i class="bi bi-braces text-primary me-2"></i> Theme Metadata Configuration</h5>
                    <p class="text-muted small">Must be a valid versioned JSON mapping representing customizable background assets, ring metrics, clicks, and animations.</p>
                    <textarea name="theme_metadata" id="theme_metadata" class="form-control font-monospace @error('theme_metadata') is-invalid @enderror" rows="12" style="font-size: 0.85rem;">{{ old('theme_metadata', json_encode([
    'schema_version' => 1,
    'background' => [
        'type' => 'gradient',
        'value' => 'linear-gradient(180deg, #121212 0%, #000000 100%)',
        'animation_speed' => 1.0,
    ],
    'counter' => [
        'design' => 'circular',
        'background_color' => '#1e1e1e',
        'text_color' => '#ffffff',
    ],
    'ring' => [
        'color' => '#3b82f6',
        'width' => 8.0,
        'glow' => true,
        'animation' => 'pulse',
    ],
    'typography' => [
        'font_family' => 'cairo',
        'arabic_font' => 'amiri',
    ],
    'animation' => [
        'type' => 'ripple',
        'intensity' => 'medium',
    ],
    'sound' => [
        'type' => 'soft_click',
        'asset_path' => 'sounds/click.mp3',
    ],
    'haptic' => [
        'profile' => 'medium',
    ]
], JSON_PRETTY_PRINT)) }}</textarea>
                    @error('theme_metadata')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="quran-btn quran-btn-primary px-4 py-2">
                    <i class="bi bi-save me-1"></i> Save Theme
                </button>
                <a href="{{ route('admin.themes.index') }}" class="quran-btn quran-btn-outline-secondary px-4 py-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
