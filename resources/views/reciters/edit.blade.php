{{-- resources/views/reciters/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('reciters.titles.edit'))
@section('page-title', __('reciters.titles.edit') . ': ' . $reciter->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('reciters.index') }}">{{ __('reciters.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('reciters.show', $reciter) }}">{{ $reciter->name }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('common.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('reciters.titles.edit') }}: {{ $reciter->name }}</h1>
            <div class="text-muted">{{ __('reciters.hints.edit_existing') }}</div>
        </div>
        <a href="{{ route('reciters.show', $reciter) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('reciters.actions.back') }}
        </a>
    </div>

    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-pencil-square me-2"></i>
                {{ __('reciters.titles.form_edit') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('reciters.update', $reciter) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('reciters._form', ['reciter' => $reciter])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.update') }}
                    </button>
                    <a href="{{ route('reciters.show', $reciter) }}" class="quran-btn quran-btn-outline-secondary">
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
                {{ __('reciters.titles.danger_zone') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">{{ __('reciters.messages.delete_title') }}</h6>
                    <p class="text-muted mb-0">{{ __('reciters.messages.delete_warning') }}</p>
                </div>
                <form method="POST"
                      action="{{ route('reciters.destroy', $reciter) }}"
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
    if (confirm('{{ __("reciters.messages.confirm_delete") }}')) {
        form.submit();
    }
    return false;
}
</script>
@endpush