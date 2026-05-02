{{-- resources/views/qiraats/show.blade.php --}}
@extends('layouts.app')

@section('title', $qiraat->name)
@section('page-title', $qiraat->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('qiraats.index') }}">{{ __('qiraats.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ $qiraat->name }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="quran-plan-icon" style="width: 48px; height: 48px;">
                    <i class="bi bi-book-half"></i>
                </div>
                <div>
                    <h1 class="h4 mb-1">{{ $qiraat->name }}</h1>
                    @if($qiraat->riwayah)
                    <span class="quran-table-badge info">{{ $qiraat->riwayah }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('qiraat-texts.create', ['qiraah_id' => $qiraat->id]) }}" 
               class="quran-btn quran-btn-success">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('qiraat_texts.actions.add_text') }}
            </a>
            <a href="{{ route('qiraats.edit', $qiraat) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('qiraats.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('qiraats.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Details -->
        <div class="col-lg-4">
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('qiraats.details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('qiraats.fields.status') }}</label>
                        <div class="quran-detail-value">
                            <span class="quran-table-badge {{ $qiraat->is_active ? 'success' : 'danger' }}">
                                {{ $qiraat->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('qiraats.fields.total_texts') }}</label>
                        <div class="quran-detail-value">
                            <strong>{{ $stats['total_texts'] }}</strong> {{ __('qiraats.texts') }}
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('qiraats.fields.surahs_covered') }}</label>
                        <div class="quran-detail-value">{{ $stats['surahs_covered'] }} {{ __('qiraats.surahs') }}</div>
                    </div>
                </div>
            </div>

            @if($qiraat->description)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-card-text me-2"></i>
                        {{ __('qiraats.fields.description') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-description">{{ $qiraat->description }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Texts List -->
        <div class="col-lg-8">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('qiraats.texts_list') }}
                    </h5>
                </div>
                <div class="quran-table-container">
                    <table class="quran-table quran-table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('qiraat_texts.fields.surah_ayah') }}</th>
                                <th>{{ __('qiraat_texts.fields.text_variant') }}</th>
                                <th class="text-end">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($qiraat->texts as $text)
                            <tr>
                                <td>
                                    <a href="{{ route('ayahs.show', $text->ayah) }}" class="text-decoration-none">
                                        {{ $text->ayah->surah->name_ar }} 
                                        ({{ $text->ayah->ayah_number }})
                                    </a>
                                </td>
                                <td>
                                    <div class="arabic-text" style="font-size: 18px;">
                                        {{ Str::limit($text->text_variant, 60) }}
                                    </div>
                                    @if($text->note)
                                    <small class="text-muted d-block">{{ Str::limit($text->note, 30) }}</small>
                                    @endif
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
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3">
                                    <div class="quran-table-empty">
                                        <i class="bi bi-journal-x"></i>
                                        <h6>{{ __('qiraats.no_texts_yet') }}</h6>
                                        @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('qiraat-texts.create', ['qiraah_id' => $qiraat->id]) }}" 
                                           class="quran-btn quran-btn-primary mt-3">
                                            <i class="bi bi-plus-lg me-1"></i>
                                            {{ __('qiraat_texts.actions.add_first') }}
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
    </div>
</div>
@endsection