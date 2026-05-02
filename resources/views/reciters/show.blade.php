{{-- resources/views/reciters/show.blade.php --}}
@extends('layouts.app')

@section('title', $reciter->name)
@section('page-title', $reciter->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('reciters.index') }}">{{ __('reciters.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ $reciter->name }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="quran-avatar">
                    @if($reciter->image)
                    <img src="{{ Storage::url($reciter->image) }}" alt="{{ $reciter->name }}" 
                         class="quran-avatar-img">
                    @else
                    <div class="quran-avatar-img bg-primary d-flex align-items-center justify-content-center text-white">
                        {{ Str::substr($reciter->name, 0, 1) }}
                    </div>
                    @endif
                </div>
                <div>
                    <h1 class="h4 mb-1">{{ $reciter->name }}</h1>
                    <p class="text-muted mb-0">
                        @if($reciter->riwayah)
                        <span class="me-3">{{ $reciter->riwayah }}</span>
                        @endif
                        @if($reciter->language)
                        <span>{{ $reciter->language }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('audio-files.create', ['reciter_id' => $reciter->id]) }}" 
               class="quran-btn quran-btn-success">
                <i class="bi bi-headphones me-1"></i>
                {{ __('audio_files.actions.upload_for_reciter') }}
            </a>
            <a href="{{ route('reciters.edit', $reciter) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('reciters.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('reciters.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Stats -->
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reciters.total_files') }}</div>
                        <div class="quran-stat-value">{{ $audioStats['total'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-file-music"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reciters.full_surahs') }}</div>
                        <div class="quran-stat-value">{{ $audioStats['full_surahs'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reciters.individual_ayahs') }}</div>
                        <div class="quran-stat-value">{{ $audioStats['individual_ayahs'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio Files List -->
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-headphones me-2"></i>
                {{ __('reciters.audio_files') }}
            </h5>
        </div>
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>{{ __('audio_files.fields.surah_ayah') }}</th>
                        <th>{{ __('audio_files.fields.duration') }}</th>
                        <th>{{ __('audio_files.fields.quality') }}</th>
                        <th>{{ __('audio_files.fields.status') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reciter->audioFiles as $audio)
                    <tr>
                        <td>
                            @if($audio->surah)
                            {{ $audio->surah->name_ar }}
                            @if($audio->ayah)
                            - {{ __('audio_files.ayah') }} {{ $audio->ayah->ayah_number }}
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
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="quran-table-empty">
                                <i class="bi bi-file-music"></i>
                                <h6>{{ __('reciters.no_audio_files') }}</h6>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('audio-files.create', ['reciter_id' => $reciter->id]) }}" 
                                   class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
function playAudio(url) {
    const audio = new Audio(url);
    audio.play();
}
</script>
@endpush