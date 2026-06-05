{{-- resources/views/tasbihs/show.blade.php --}}
@extends('layouts.app')

@section('title', __('tasbihs.titles.show'))
@section('page-title', __('tasbihs.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tasbihs.index') }}">{{ __('tasbihs.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('tasbihs.titles.show') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tasbihs.titles.show') }}</h1>
            <div class="text-muted">{{ __('tasbihs.hints.show') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tasbihs.edit', $tasbih) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('tasbihs.actions.edit') }}
            </a>
            <a href="{{ route('tasbihs.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('tasbihs.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        {{-- Main Display Card --}}
        <div class="col-lg-7">
            <div class="quran-card" style="
                background: linear-gradient(135deg, rgba(27,115,64,0.05) 0%, rgba(212,175,55,0.04) 100%);
                border: 1px solid rgba(27,115,64,0.14);
                overflow: hidden; position: relative;">

                {{-- Decorative Circles --}}
                <div style="position:absolute;top:-40px;right:-40px;width:160px;height:160px;
                            background:radial-gradient(circle,rgba(212,175,55,0.1) 0%,transparent 70%);
                            border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-30px;left:-30px;width:110px;height:110px;
                            background:radial-gradient(circle,rgba(27,115,64,0.08) 0%,transparent 70%);
                            border-radius:50%;pointer-events:none;"></div>

                <div class="quran-card-body text-center py-5 px-4" style="position:relative;z-index:1;">
                    {{-- Arabic Text --}}
                    <div style="
                        font-family: var(--quran-font, 'Amiri Quran', 'KFGQPC Uthmanic Script HAFS', serif);
                        font-size: clamp(1.6rem, 4vw, 2.4rem);
                        line-height: 2;
                        color: var(--quran-text-primary, #1a1a1a);
                        direction: rtl;
                        margin-bottom: 2rem;">
                        {{ $tasbih->name }}
                    </div>

                    {{-- Target Count Ring --}}
                    <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                        <div style="
                            width: 90px; height: 90px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05));
                            border: 2px solid rgba(212,175,55,0.4);
                            display: flex; flex-direction: column;
                            align-items: center; justify-content: center;
                            box-shadow: 0 4px 20px rgba(212,175,55,0.15);">
                            <span style="font-size: 1.6rem; font-weight: 800; color: #a08000; line-height: 1;">
                                {{ $tasbih->target }}
                            </span>
                            <span style="font-size: 0.65rem; color: #b09020; font-weight: 600; letter-spacing: 0.05em; margin-top: 2px;">
                                × {{ __('tasbihs.pagination.entries') }}
                            </span>
                        </div>
                        <div class="text-start">
                            <div class="text-muted small">{{ __('tasbihs.fields.target') }}</div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: var(--quran-text-primary);">
                                {{ $tasbih->target }}×
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('common.details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <dl class="row g-3 mb-0">
                        {{-- Status --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('tasbihs.table.status') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            @if($tasbih->is_active)
                                <span class="quran-table-badge success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('tasbihs.status.active') }}
                                </span>
                            @else
                                <span class="quran-table-badge danger">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('tasbihs.status.inactive') }}
                                </span>
                            @endif
                        </dd>

                        {{-- Target --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('tasbihs.table.target') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="badge px-2 py-1"
                                  style="background:rgba(212,175,55,0.15);color:#a08000;border:1px solid rgba(212,175,55,0.3);font-weight:700;font-size:0.9rem;">
                                {{ $tasbih->target }}×
                            </span>
                        </dd>

                        {{-- Dates --}}
                        @if($tasbih->created_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.created_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $tasbih->created_at->format('Y-m-d') }}</dd>
                        @endif

                        @if($tasbih->updated_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.updated_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $tasbih->updated_at->diffForHumans() }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="quran-card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('tasbihs.edit', $tasbih) }}" class="quran-btn quran-btn-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('tasbihs.actions.edit') }}
                        </a>
                        <form method="POST" action="{{ route('tasbihs.destroy', $tasbih) }}"
                              onsubmit="return confirm('{{ __('tasbihs.messages.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="quran-btn quran-btn-danger w-100">
                                <i class="bi bi-trash me-1"></i>{{ __('tasbihs.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
