{{-- resources/views/qiraat-texts/index.blade.php --}}
@extends('layouts.app')

@section('title', __('qiraat_texts.titles.index'))
@section('page-title', __('qiraat_texts.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('qiraat_texts.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('qiraat_texts.titles.index') }}</h1>
            <div class="text-muted">{{ __('qiraat_texts.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('qiraats.create') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('qiraats.actions.create') }}
            </a>
            <a href="{{ route('qiraat-texts.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('qiraat_texts.actions.create') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('qiraat_texts.total_texts') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_texts']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('qiraat_texts.total_qiraats_used') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_qiraats_used'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('qiraat_texts.ayahs_with_qiraat') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_ayahs_with_qiraat']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('qiraat-texts.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('qiraat_texts.filter_by_qiraat') }}</label>
                        <select name="qiraah_id" class="quran-form-select">
                            <option value="">{{ __('qiraat_texts.all_qiraats') }}</option>
                            @foreach($qiraats as $qiraat)
                            <option value="{{ $qiraat->id }}" {{ request('qiraah_id') == $qiraat->id ? 'selected' : '' }}>
                                {{ $qiraat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('qiraat_texts.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('qiraat_texts.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('qiraat_texts.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('qiraat_texts.search_placeholder') }}" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
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
                        <th>{{ __('qiraat_texts.fields.qiraat') }}</th>
                        <th>{{ __('qiraat_texts.fields.surah_ayah') }}</th>
                        <th>{{ __('qiraat_texts.fields.text_variant') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qiraatTexts as $text)
                    <tr>
                        <td>
                            <a href="{{ route('qiraats.show', $text->qiraat) }}" class="text-decoration-none">
                                {{ $text->qiraat->name }}
                            </a>
                            @if($text->qiraat->riwayah)
                            <small class="text-muted d-block">{{ $text->qiraat->riwayah }}</small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('ayahs.show', $text->ayah) }}" class="text-decoration-none">
                                {{ $text->ayah->surah->name_ar }} ({{ $text->ayah->ayah_number }})
                            </a>
                        </td>
                        <td>
                            <div class="arabic-text" style="font-size: 18px;">
                                {{ Str::limit($text->text_variant, 60) }}
                            </div>
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('qiraat-texts.show', $text) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('qiraat-texts.edit', $text) }}" 
                                   class="quran-table-action-btn edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="quran-table-action-btn delete" 
                                        onclick="confirmDelete({{ $text->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="quran-table-empty">
                                <i class="bi bi-journal-x"></i>
                                <h6>{{ __('qiraat_texts.no_texts_found') }}</h6>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('qiraat-texts.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    {{ __('qiraat_texts.actions.create_first') }}
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($qiraatTexts->hasPages())
        <div class="card-footer">
            {{ $qiraatTexts->links() }}
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
                <p>{{ __('qiraat_texts.messages.confirm_delete') }}</p>
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
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('qiraat-texts.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush