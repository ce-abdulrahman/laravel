@extends('layouts.app')

@section('title', __('ayahs.edit_ayah'))

@section('content')
<div class="quran-dashboard">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1 text-zinc-900 dark:text-white font-bold">
                {{ __('ayahs.edit_ayah') }}: {{ $ayah->surah->name_simple }} {{ $ayah->ayah_number }}
            </h1>
            <div class="text-zinc-500 dark:text-zinc-400 text-sm font-semibold">{{ __('common.edit') }} - {{ __('ayahs.ayah') }} {{ $ayah->ayah_number }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ayahs.show', $ayah) }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-eye"></i>
                <span>{{ __('common.view') }}</span>
            </a>
            <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left"></i>
                <span>{{ __('common.back') }}</span>
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('ayahs.update', $ayah) }}">
        @csrf
        @method('PUT')

        @include('ayahs._form')

        <div class="row g-4 mt-2">
            <div class="col-lg-8">
                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('ayahs.show', $ayah) }}" class="quran-btn quran-btn-outline-primary">
                        <i class="bi bi-x-lg me-1"></i>
                        <span>{{ __('common.cancel') }}</span>
                    </a>
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        <span>{{ __('common.save') }}</span>
                    </button>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Danger Zone -->
                @can('delete', $ayah)
                <div class="quran-card border border-danger/30 bg-danger/5 dark:bg-danger/5 rounded-2xl shadow-sm overflow-hidden">
                    <div class="quran-card-header border-0 bg-transparent pb-0">
                        <h6 class="quran-card-title text-danger font-bold fs-6">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ __('common.danger_zone') }}
                        </h6>
                    </div>
                    <div class="quran-card-body pt-2 pb-3">
                        <p class="text-zinc-500 dark:text-zinc-400 text-xs mb-3 leading-normal">{{ __('ayahs.delete_warning') }}</p>
                        <button type="button" class="quran-btn quran-btn-outline-danger w-100 justify-content-center"
                                onclick="confirmDelete()">
                            <i class="bi bi-trash me-1"></i>
                            <span>{{ __('common.delete') }}</span>
                        </button>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </form>
</div>

{{-- Delete Form --}}
@can('delete', $ayah)
<form id="deleteForm" method="POST" action="{{ route('ayahs.destroy', $ayah) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endcan

@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('{{ __("ayahs.delete_confirm_message") }}')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
