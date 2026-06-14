@extends('layouts.app')
@section('title', __('reminders.titles.edit'))
@section('page-title', __('reminders.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reminders.index') }}">{{ __('reminders.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('reminders.titles.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🔔 {{ __('reminders.titles.edit') }}</h1>
            <div class="text-muted small">{{ __('reminders.hints.edit') }}</div>
        </div>
        <a href="{{ route('reminders.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> {{ __('reminders.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('reminders.update', $reminder) }}" class="quran-form">
        @csrf
        @method('PUT')
        <div class="row g-4">
            {{-- Basic Settings --}}
            <div class="col-lg-8">
                <div class="quran-card p-4">
                    <h5 class="fw-semibold mb-4 text-primary d-flex align-items-center gap-2 border-bottom pb-3">
                        <i class="bi bi-info-circle"></i>
                        {{ __('reminders.sections.basic') }}
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="quran-form-label" for="key">
                                {{ __('reminders.fields.key') }}
                            </label>
                            <input type="text" id="key"
                                   class="quran-form-control bg-light"
                                   value="{{ $reminder->key }}"
                                   disabled>
                            <span class="text-muted small mt-1 d-block"><i class="bi bi-info-circle-fill me-1"></i>Template keys cannot be renamed.</span>
                        </div>

                        <div class="col-md-6">
                            <label class="quran-form-label" for="type">
                                {{ __('reminders.fields.type') }} <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="type" class="quran-form-control @error('type') is-invalid @enderror" required>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ old('type', $reminder->type) === $type ? 'selected' : '' }}>
                                        {{ __('reminders.types.' . $type) ?? $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="quran-form-label" for="icon">
                                {{ __('reminders.fields.icon') }}
                            </label>
                            <input type="text" name="icon" id="icon"
                                   class="quran-form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', $reminder->icon) }}">
                            @error('icon')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="quran-form-label" for="priority">
                                {{ __('reminders.fields.priority') }}
                            </label>
                            <select name="priority" id="priority" class="quran-form-control @error('priority') is-invalid @enderror">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('priority', $reminder->priority) == $i ? 'selected' : '' }}>
                                        {{ $i }} {{ $i == 10 ? '(Highest)' : ($i == 1 ? '(Lowest)' : '') }}
                                    </option>
                                @endfor
                            </select>
                            @error('priority')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="quran-form-label" for="sort_order">
                                {{ __('reminders.fields.sort_order') }}
                            </label>
                            <input type="number" name="sort_order" id="sort_order"
                                   class="quran-form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $reminder->sort_order) }}" min="0">
                            @error('sort_order')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Translations Section --}}
                <div class="quran-card p-4 mt-4">
                    <h5 class="fw-semibold mb-4 text-primary d-flex align-items-center gap-2 border-bottom pb-3">
                        <i class="bi bi-translate"></i>
                        {{ __('reminders.sections.translations') }}
                    </h5>

                    <div class="row g-4">
                        @foreach($languages as $lang)
                            <div class="col-12 p-3 bg-light bg-opacity-50 rounded-4 border">
                                <h6 class="fw-bold mb-3 text-secondary d-flex align-items-center gap-2">
                                    <span class="badge bg-primary px-2.5 py-1.5">{{ strtoupper($lang->code) }}</span>
                                    <span>{{ $lang->name }}</span>
                                    <span class="text-muted small">({{ $lang->native_name }})</span>
                                </h6>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="quran-form-label" for="title_{{ $lang->code }}">
                                            {{ __('reminders.fields.title') }} ({{ $lang->name }})
                                        </label>
                                        <input type="text" name="translations[{{ $lang->code }}][title]"
                                               id="title_{{ $lang->code }}"
                                               class="quran-form-control @error('translations.' . $lang->code . '.title') is-invalid @enderror"
                                               value="{{ old('translations.' . $lang->code . '.title', $reminder->getTranslation('title', $lang->code)) }}"
                                               @if($lang->isRtl()) dir="rtl" @endif>
                                        @error('translations.' . $lang->code . '.title')
                                            <div class="quran-invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="quran-form-label" for="body_{{ $lang->code }}">
                                            {{ __('reminders.fields.body') }} ({{ $lang->name }})
                                        </label>
                                        <textarea name="translations[{{ $lang->code }}][body]"
                                                  id="body_{{ $lang->code }}"
                                                  class="quran-form-control @error('translations.' . $lang->code . '.body') is-invalid @enderror"
                                                  rows="3"
                                                  @if($lang->isRtl()) dir="rtl" @endif>{{ old('translations.' . $lang->code . '.body', $reminder->getTranslation('body', $lang->code)) }}</textarea>
                                        @error('translations.' . $lang->code . '.body')
                                            <div class="quran-invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Options & Action Section --}}
            <div class="col-lg-4">
                <div class="quran-card p-4">
                    <h5 class="fw-semibold mb-4 text-primary d-flex align-items-center gap-2 border-bottom pb-3">
                        <i class="bi bi-sliders"></i>
                        {{ __('reminders.sections.options') }}
                    </h5>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $reminder->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">
                            {{ __('reminders.fields.is_active') }}
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="quran-form-label d-block text-muted mb-1">{{ __('reminders.fields.version') }}</label>
                        <span class="badge bg-dark-subtle text-dark border px-3 py-1.5 fs-7">
                            v{{ $reminder->version }}
                        </span>
                        <small class="text-muted d-block mt-2">Updating the type or priority will automatically bump this template's version to force clients to resynchronize.</small>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-warning w-100 py-2.5 rounded-3 mb-2 fw-semibold text-white">
                        <i class="bi bi-save me-1"></i> {{ __('reminders.actions.update') }}
                    </button>
                    <a href="{{ route('reminders.index') }}" class="btn btn-outline-secondary w-100 py-2.5 rounded-3 fw-semibold">
                        {{ __('reminders.actions.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
