{{-- resources/views/adhkars/show.blade.php --}}
@extends('layouts.app')

@section('title', __('adhkars.titles.show'))
@section('page-title', __('adhkars.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('adhkars.index') }}">{{ __('adhkars.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('adhkars.titles.show') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('adhkars.titles.show') }}</h1>
            <div class="text-muted">{{ __('adhkars.hints.show') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('adhkars.edit', $adhkar) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('adhkars.actions.edit') }}
            </a>
            <a href="{{ route('adhkars.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('adhkars.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8">

            {{-- Arabic Text Card --}}
            <div class="quran-card mb-4" style="
                background: linear-gradient(135deg, rgba(27,115,64,0.05) 0%, rgba(212,175,55,0.04) 100%);
                border: 1px solid rgba(27,115,64,0.14);
                overflow: hidden; position: relative;">
                {{-- Decorative --}}
                <div style="position:absolute;top:-30px;right:-30px;width:130px;height:130px;
                            background:radial-gradient(circle,rgba(212,175,55,0.09) 0%,transparent 70%);
                            border-radius:50%;pointer-events:none;"></div>

                {{-- Repetition Badge --}}
                <div class="position-absolute top-0 end-0 m-3">
                    <span class="badge px-3 py-2" style="background:rgba(212,175,55,0.15);color:#a08000;
                          border:1px solid rgba(212,175,55,0.3);font-size:1rem;font-weight:700;">
                        {{ $adhkar->count }}×
                    </span>
                </div>

                <div class="quran-card-body text-center py-5" style="position:relative;z-index:1;">
                    <div style="
                        font-family: var(--quran-font, 'Amiri Quran', 'KFGQPC Uthmanic Script HAFS', serif);
                        font-size: clamp(1.3rem, 3vw, 1.9rem);
                        line-height: 2.2;
                        color: var(--quran-text-primary, #1a1a1a);
                        direction: rtl;
                        text-align: right;
                        padding: 0 1.5rem;">
                        {{ $adhkar->arabic_text }}
                    </div>

                    @if($adhkar->source)
                    <div class="mt-4">
                        <span class="badge px-3 py-2"
                              style="background:rgba(27,115,64,0.1);color:#1B7340;border:1px solid rgba(27,115,64,0.2);font-size:0.85rem;">
                            <i class="bi bi-book me-1"></i>{{ $adhkar->source }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Translations Card --}}
            @if($adhkar->translation_ku || $adhkar->translation_en)
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-translate me-2" style="color:#1B7340;"></i>
                        {{ __('adhkars.fields.translation_ku') }} / {{ __('adhkars.fields.translation_en') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    @if($adhkar->translation_ku)
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">{{ __('adhkars.fields.translation_ku') }}</label>
                        <div style="line-height: 1.8; font-size: 0.95rem;">{{ $adhkar->translation_ku }}</div>
                    </div>
                    @endif
                    @if($adhkar->translation_en)
                    <div>
                        <label class="text-muted small d-block mb-1">{{ __('adhkars.fields.translation_en') }}</label>
                        <div style="line-height: 1.8; font-size: 0.95rem;">{{ $adhkar->translation_en }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Virtue / Description Card --}}
            @if($adhkar->description)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-stars me-2" style="color:#D4AF37;"></i>
                        {{ __('adhkars.fields.description') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div style="line-height: 1.8; font-size: 0.95rem; color: var(--quran-text-secondary);">
                        {{ $adhkar->description }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar Metadata --}}
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('common.details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <dl class="row g-2 mb-0">

                        {{-- Category --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('adhkars.table.category') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            @if($adhkar->category)
                                <span class="quran-table-badge info">{{ $adhkar->category->name_ku }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </dd>

                        {{-- Count --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('adhkars.table.count') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="badge px-2 py-1"
                                  style="background:rgba(212,175,55,0.15);color:#a08000;border:1px solid rgba(212,175,55,0.3);font-weight:700;">
                                {{ $adhkar->count }}×
                            </span>
                        </dd>

                        {{-- Order --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('adhkars.fields.order') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="surah-number" style="width:36px;height:36px;font-size:0.85rem;display:inline-flex;">
                                {{ $adhkar->order }}
                            </span>
                        </dd>

                        {{-- Source --}}
                        @if($adhkar->source)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('adhkars.fields.source') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="badge bg-light text-dark border" style="font-size:0.78rem;">{{ $adhkar->source }}</span>
                        </dd>
                        @endif

                        {{-- Dates --}}
                        @if($adhkar->created_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.created_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $adhkar->created_at->format('Y-m-d') }}</dd>
                        @endif

                        @if($adhkar->updated_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.updated_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $adhkar->updated_at->diffForHumans() }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="quran-card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('adhkars.edit', $adhkar) }}" class="quran-btn quran-btn-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('adhkars.actions.edit') }}
                        </a>
                        <form method="POST" action="{{ route('adhkars.destroy', $adhkar) }}"
                              onsubmit="return confirm('{{ __('adhkars.messages.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="quran-btn quran-btn-danger w-100">
                                <i class="bi bi-trash me-1"></i>{{ __('adhkars.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
