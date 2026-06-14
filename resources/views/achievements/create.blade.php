@extends('layouts.app')
@section('title', __('achievements.titles.create'))
@section('page-title', __('achievements.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">{{ __('achievements.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('achievements.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🏆 {{ __('achievements.titles.create') }}</h1>
            <div class="text-muted small">{{ __('achievements.hints.create') }}</div>
        </div>
        <a href="{{ route('achievements.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('achievements.actions.back') }}
        </a>
    </div>

    <div class="quran-form-container">
        <form method="POST" action="{{ route('achievements.store') }}">
            @csrf

            <div class="quran-form">
                {{-- Basic Info --}}
                <div class="quran-form-section mb-4">
                    <h6 class="quran-form-section-title">
                        <i class="bi bi-info-circle me-2"></i>{{ __('achievements.sections.basic') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="quran-form-label">{{ __('achievements.fields.icon') }}</label>
                            <input type="text" name="icon" value="{{ old('icon', '🏆') }}"
                                   class="quran-form-control text-center @error('icon') is-invalid @enderror"
                                   style="font-size:1.5rem;">
                            @error('icon')<div class="quran-invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="quran-form-label">{{ __('achievements.fields.key') }} <span class="text-danger">*</span></label>
                            <input type="text" name="key" value="{{ old('key') }}"
                                   class="quran-form-control font-monospace @error('key') is-invalid @enderror"
                                   placeholder="{{ __('achievements.placeholders.key') }}" required>
                            @error('key')<div class="quran-invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="quran-form-label">{{ __('achievements.fields.category') }} <span class="text-danger">*</span></label>
                            <select name="category_id" class="quran-form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- {{ __('common.filter') }} --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->icon }} {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="quran-invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="quran-form-label">{{ __('achievements.fields.sort_order') }}</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   class="quran-form-control @error('sort_order') is-invalid @enderror">
                        </div>
                    </div>
                </div>

                {{-- Condition --}}
                <div class="quran-form-section mb-4">
                    <h6 class="quran-form-section-title">
                        <i class="bi bi-gear me-2"></i>{{ __('achievements.sections.condition') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="quran-form-label">{{ __('achievements.fields.condition_type') }} <span class="text-danger">*</span></label>
                            <select name="condition_type" class="quran-form-control @error('condition_type') is-invalid @enderror" required>
                                <option value="">-- {{ __('common.filter') }} --</option>
                                @foreach($conditionTypes as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}" {{ old('condition_type') === $typeKey ? 'selected' : '' }}>
                                        {{ $typeKey }} &mdash; {{ $typeLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('condition_type')<div class="quran-invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="quran-form-label">{{ __('achievements.fields.condition_value') }} <span class="text-danger">*</span></label>
                            <input type="number" name="condition_value" value="{{ old('condition_value', 1) }}" min="1"
                                   class="quran-form-control @error('condition_value') is-invalid @enderror" required>
                            @error('condition_value')<div class="quran-invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="quran-form-label">{{ __('achievements.fields.version') }}</label>
                            <input type="number" name="version" value="{{ old('version', 1) }}" min="1"
                                   class="quran-form-control">
                        </div>
                    </div>
                </div>

                {{-- Reward --}}
                <div class="quran-form-section mb-4">
                    <h6 class="quran-form-section-title">
                        <i class="bi bi-gift me-2"></i>{{ __('achievements.sections.reward') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="quran-form-label">{{ __('achievements.fields.reward_type') }}</label>
                            <select name="reward_type" class="quran-form-control">
                                @foreach($rewardTypes as $rtKey => $rtLabel)
                                    <option value="{{ $rtKey }}" {{ old('reward_type','POINTS') === $rtKey ? 'selected' : '' }}>
                                        {{ $rtKey }} &mdash; {{ $rtLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="quran-form-label">{{ __('achievements.fields.reward_points') }}</label>
                            <input type="number" name="reward_points" value="{{ old('reward_points', 0) }}" min="0"
                                   class="quran-form-control">
                        </div>
                    </div>
                </div>

                {{-- Translations --}}
                <div class="quran-form-section mb-4">
                    <h6 class="quran-form-section-title">
                        <i class="bi bi-translate me-2"></i>{{ __('achievements.sections.translations') }}
                    </h6>

                    {{-- Language Tabs --}}
                    <ul class="nav nav-tabs quran-tabs mb-3" role="tablist">
                        @foreach($languages as $i => $lang)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $i === 0 ? 'active' : '' }}"
                                    id="tab-create-{{ $lang->code }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#pane-create-{{ $lang->code }}"
                                    type="button" role="tab">
                                {{ $lang->flag }} {{ $lang->native_name }}
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $i => $lang)
                        <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
                             id="pane-create-{{ $lang->code }}" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="quran-form-label" for="create_name_{{ $lang->code }}">
                                        {{ __('achievements.fields.name') }} ({{ $lang->native_name }})
                                    </label>
                                    <input type="text"
                                           id="create_name_{{ $lang->code }}"
                                           name="translations[{{ $lang->code }}][name]"
                                           value="{{ old("translations.{$lang->code}.name") }}"
                                           class="quran-form-control"
                                           dir="{{ $lang->direction }}"
                                           placeholder="ناوی دەستکەوتە بە {{ $lang->native_name }}">
                                </div>
                                <div class="col-md-7">
                                    <label class="quran-form-label" for="create_desc_{{ $lang->code }}">
                                        {{ __('achievements.fields.description') }} ({{ $lang->native_name }})
                                    </label>
                                    <input type="text"
                                           id="create_desc_{{ $lang->code }}"
                                           name="translations[{{ $lang->code }}][description]"
                                           value="{{ old("translations.{$lang->code}.description") }}"
                                           class="quran-form-control"
                                           dir="{{ $lang->direction }}"
                                           placeholder="وصفی دەستکەوتە بە {{ $lang->native_name }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Flags --}}
                <div class="quran-form-section mb-4">
                    <h6 class="quran-form-section-title"><i class="bi bi-toggles me-2"></i>{{ __('achievements.sections.options') }}</h6>
                    <div class="d-flex gap-4">
                        <div class="quran-form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" class="quran-form-check-input" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="quran-form-check-label" for="is_active">
                                <i class="bi bi-check-circle me-1"></i> {{ __('achievements.fields.is_active') }}
                            </label>
                        </div>
                        <div class="quran-form-check">
                            <input type="hidden" name="is_hidden" value="0">
                            <input type="checkbox" name="is_hidden" id="is_hidden" class="quran-form-check-input" value="1" {{ old('is_hidden') ? 'checked' : '' }}>
                            <label class="quran-form-check-label" for="is_hidden">
                                <i class="bi bi-eye-slash me-1"></i> {{ __('achievements.fields.is_hidden') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i> {{ __('achievements.actions.save') }}
                </button>
                <a href="{{ route('achievements.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('achievements.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
