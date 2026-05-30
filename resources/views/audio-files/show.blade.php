{{-- resources/views/audio-files/show.blade.php --}}
@extends('layouts.app')

@section('title', __('audio_files.titles.show'))
@section('page-title', __('audio_files.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('audio-files.index') }}">{{ __('audio_files.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('audio_files.titles.show') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('audio_files.titles.show') }}</h1>
            <div class="text-muted">{{ __('audio_files.hints.details') }}</div>
        </div>
        <a href="{{ route('audio-files.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('audio_files.actions.back') }}
        </a>
    </div>

    <div class="row g-4">
        <!-- Audio Player & Main Info Card -->
        <div class="col-lg-8">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-play-circle me-2 text-primary"></i>
                        {{ __('audio_files.sections.playback') }}
                    </h5>
                </div>
                <div class="quran-card-body text-center py-5">
                    <div class="audio-wave-icon mb-4">
                        <i class="bi bi-file-earmark-music-fill text-primary" style="font-size: 72px;"></i>
                    </div>
                    
                    <h4 class="mb-2">
                        @if($audioFile->surah)
                            {{ $audioFile->surah->name_en }} / {{ $audioFile->surah->name_ar }}
                        @else
                            {{ __('audio_files.unknown_surah') }}
                        @endif
                    </h4>
                    
                    @if($audioFile->ayah)
                        <p class="text-muted mb-4">{{ __('audio_files.fields.ayah') }}: {{ $audioFile->ayah->ayah_number }}</p>
                    @else
                        <p class="text-muted mb-4">{{ __('audio_files.full_surah') }}</p>
                    @endif

                    <div class="mx-auto" style="max-width: 500px;">
                        <audio controls class="w-100 mb-3" id="mainPlayer">
                            <source src="{{ route('audio-files.stream', $audioFile) }}" type="audio/mpeg">
                            {{ __('audio_files.browser_unsupported') }}
                        </audio>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        @if(auth()->user()?->role === 'admin')
                        <a href="{{ route('audio-files.edit', $audioFile) }}" class="quran-btn quran-btn-primary">
                            <i class="bi bi-pencil me-1"></i>
                            {{ __('common.edit') }}
                        </a>
                        <button type="button" class="quran-btn quran-btn-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('common.delete') }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Meta Data Card -->
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-square me-2 text-primary"></i>
                        {{ __('audio_files.sections.metadata') }}
                    </h5>
                </div>
                <div class="quran-card-body p-0">
                    <ul class="list-group list-group-flush" style="border-radius: 0 0 20px 20px; overflow: hidden;">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent border-bottom">
                            <span class="text-muted">{{ __('audio_files.fields.reciter') }}</span>
                            <span class="font-weight-bold">{{ $audioFile->reciter->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent border-bottom">
                            <span class="text-muted">{{ __('audio_files.fields.source_type') }}</span>
                            <span class="badge bg-secondary">{{ strtoupper($audioFile->source_type) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent border-bottom">
                            <span class="text-muted">{{ __('audio_files.fields.duration') }}</span>
                            <span>{{ $audioFile->duration_seconds ? gmdate('i:s', $audioFile->duration_seconds) . 's' : '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent border-bottom">
                            <span class="text-muted">{{ __('audio_files.fields.quality') }}</span>
                            <span>{{ $audioFile->quality ?: '—' }} kbps</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent border-bottom">
                            <span class="text-muted">{{ __('audio_files.fields.status') }}</span>
                            <span class="badge {{ $audioFile->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $audioFile->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </li>
                        <li class="list-group-item py-3 bg-transparent border-0">
                            <span class="text-muted d-block mb-1">{{ __('audio_files.fields.file_path') }}</span>
                            <span class="text-break small font-monospace">{{ $audioFile->file_path }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">{{ __('common.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('audio_files.messages.confirm_delete') }}</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <form action="{{ route('audio-files.destroy', $audioFile) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
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
function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
