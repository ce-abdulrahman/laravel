{{-- resources/views/tajweed-segments/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('tajweed_segments.titles.edit'))
@section('page-title', __('tajweed_segments.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-segments.index') }}">{{ __('tajweed_segments.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-segments.show', $tajweedSegment) }}">
            {{ $tajweedSegment->tajweedRule->name }}
        </a>
    </li>
    <li class="breadcrumb-item active">{{ __('common.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tajweed_segments.titles.edit') }}</h1>
            <div class="text-muted">{{ $tajweedSegment->tajweedRule->name }} - {{ $tajweedSegment->ayah->surah->name_ar }}</div>
        </div>
        <a href="{{ route('tajweed-segments.show', $tajweedSegment) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('tajweed_segments.actions.back') }}
        </a>
    </div>

    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-pencil-square me-2"></i>
                {{ __('tajweed_segments.titles.form_edit') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('tajweed-segments.update', $tajweedSegment) }}">
                @csrf
                @method('PUT')
                @include('tajweed-segments._form', ['tajweedSegment' => $tajweedSegment])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.update') }}
                    </button>
                    <a href="{{ route('tajweed-segments.show', $tajweedSegment) }}" class="quran-btn quran-btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="quran-card border-danger mt-4">
        <div class="quran-card-header bg-danger bg-opacity-10">
            <h5 class="quran-card-title text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ __('tajweed_segments.titles.danger_zone') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">{{ __('tajweed_segments.messages.delete_title') }}</h6>
                    <p class="text-muted mb-0">{{ __('tajweed_segments.messages.delete_warning') }}</p>
                </div>
                <form method="POST"
                      action="{{ route('tajweed-segments.destroy', $tajweedSegment) }}"
                      onsubmit="return confirmDelete(event)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(event) {
    event.preventDefault();
    const form = event.target;
    if (confirm('{{ __("tajweed_segments.messages.confirm_delete") }}')) {
        form.submit();
    }
    return false;
}
</script>
@endpush