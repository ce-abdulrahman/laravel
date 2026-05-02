{{-- resources/views/audio-files/index.blade.php --}}
@extends('layouts.app')

@section('title', __('audio_files.titles.index'))
@section('page-title', __('audio_files.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('audio_files.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('audio_files.titles.index') }}</h1>
            <div class="text-muted">{{ __('audio_files.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('reciters.create') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-person-plus me-1"></i>
                {{ __('reciters.actions.create') }}
            </a>
            <a href="{{ route('audio-files.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-cloud-upload me-1"></i>
                {{ __('audio_files.actions.upload') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('audio_files.total_files') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_files']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-file-music"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('audio_files.total_duration') }}</div>
                        <div class="quran-stat-value">{{ floor($stats['total_duration'] / 3600) }}h</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('audio_files.reciters_with_audio') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_reciters_with_audio'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-mic"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('audio_files.full_surahs') }}</div>
                        <div class="quran-stat-value">{{ $stats['full_surahs'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('audio-files.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('audio_files.filter_by_reciter') }}</label>
                        <select name="reciter_id" class="quran-form-select">
                            <option value="">{{ __('audio_files.all_reciters') }}</option>
                            @foreach($reciters as $reciter)
                            <option value="{{ $reciter->id }}" {{ request('reciter_id') == $reciter->id ? 'selected' : '' }}>
                                {{ $reciter->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('audio_files.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('audio_files.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('audio_files.filter_by_type') }}</label>
                        <select name="type" class="quran-form-select">
                            <option value="">{{ __('audio_files.all_types') }}</option>
                            <option value="full" {{ request('type') == 'full' ? 'selected' : '' }}>
                                {{ __('audio_files.full_surah') }}
                            </option>
                            <option value="ayah" {{ request('type') == 'ayah' ? 'selected' : '' }}>
                                {{ __('audio_files.single_ayah') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>{{ __('audio_files.fields.reciter') }}</th>
                        <th>{{ __('audio_files.fields.surah_ayah') }}</th>
                        <th>{{ __('audio_files.fields.duration') }}</th>
                        <th>{{ __('audio_files.fields.quality') }}</th>
                        <th>{{ __('audio_files.fields.status') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audioFiles as $audio)
                    <tr>
                        <td>
                            <a href="{{ route('reciters.show', $audio->reciter) }}" class="text-decoration-none">
                                {{ $audio->reciter->name }}
                            </a>
                        </td>
                        <td>
                            @if($audio->surah)
                            {{ $audio->surah->name_ar }}
                            @if($audio->ayah)
                            <span class="text-muted">({{ $audio->ayah->ayah_number }})</span>
                            @endif
                            @else
                            —
                            @endif
                        </td>
                        <td>
                            @if($audio->duration_seconds)
                            {{ gmdate('i:s', $audio->duration_seconds) }}
                            @else
                            —
                            @endif
                        </td>
                        <td>{{ $audio->quality ?: '—' }}</td>
                        <td>
                            <span class="quran-table-badge {{ $audio->is_active ? 'success' : 'danger' }}">
                                {{ $audio->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <button type="button" class="quran-table-action-btn play" 
                                        onclick="playAudio('{{ route('audio-files.stream', $audio) }}')">
                                    <i class="bi bi-play-fill"></i>
                                </button>
                                <a href="{{ route('audio-files.show', $audio) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('audio-files.edit', $audio) }}" 
                                   class="quran-table-action-btn edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="quran-table-action-btn delete" 
                                        onclick="confirmDelete({{ $audio->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="quran-table-empty">
                                <i class="bi bi-file-music"></i>
                                <h6>{{ __('audio_files.no_files_found') }}</h6>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('audio-files.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-cloud-upload me-1"></i>
                                    {{ __('audio_files.actions.upload_first') }}
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audioFiles->hasPages())
        <div class="card-footer">
            {{ $audioFiles->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
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
                <form id="deleteForm" method="POST">
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
let currentAudio = null;

function playAudio(url) {
    if (currentAudio) {
        currentAudio.pause();
    }
    currentAudio = new Audio(url);
    currentAudio.play();
}

function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('audio-files.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush