{{-- resources/views/bookmarks/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('bookmarks.titles.edit'))
@section('page-title', __('bookmarks.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('bookmarks.index') }}">{{ __('bookmarks.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('common.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('bookmarks.titles.edit') }}</h1>
            <div class="text-muted">
                {{ $bookmark->ayah->surah->name_ar }} - {{ __('bookmarks.ayah') }} {{ $bookmark->ayah->ayah_number }}
            </div>
        </div>
        <a href="{{ route('bookmarks.show', $bookmark) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('bookmarks.actions.back') }}
        </a>
    </div>

    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-pencil-square me-2"></i>
                {{ __('bookmarks.titles.edit_note') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <!-- Ayah Preview -->
            <div class="mb-4 p-3 bg-light rounded-3">
                <label class="quran-detail-label">{{ __('bookmarks.ayah_text') }}</label>
                <div class="arabic-text" style="font-size: 18px;">
                    {{ $bookmark->ayah->text_uthmani }}
                </div>
            </div>

            <form method="POST" action="{{ route('bookmarks.update', $bookmark) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="quran-form-label" for="note">
                        <i class="bi bi-pencil me-1"></i>
                        {{ __('bookmarks.fields.note') }}
                    </label>
                    <textarea name="note" id="note" rows="4"
                              class="quran-form-control @error('note') is-invalid @enderror"
                              placeholder="{{ __('bookmarks.placeholders.note') }}">{{ old('note', $bookmark->note) }}</textarea>
                    @error('note')
                    <div class="quran-invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">{{ __('bookmarks.hints.note_optional') }}</small>
                </div>

                <div class="quran-form-actions">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.update') }}
                    </button>
                    <a href="{{ route('bookmarks.show', $bookmark) }}" class="quran-btn quran-btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection