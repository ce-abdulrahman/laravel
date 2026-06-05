{{-- resources/views/banners/show.blade.php --}}
@extends('layouts.app')

@section('title', __('banners.titles.show'))
@section('page-title', __('banners.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('banners.index') }}">{{ __('banners.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('banners.titles.show') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('banners.titles.show') }}</h1>
            <div class="text-muted">{{ __('banners.hints.show') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('banners.edit', $banner) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('banners.actions.edit') }}
            </a>
            <a href="{{ route('banners.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('banners.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Banner Display --}}
        <div class="col-lg-8">
            {{-- Arabic Verse Card --}}
            <div class="quran-card mb-4" style="
                background: linear-gradient(135deg, rgba(27,115,64,0.06) 0%, rgba(212,175,55,0.04) 100%);
                border: 1px solid rgba(27,115,64,0.15);
                overflow: hidden;
                position: relative;">
                {{-- Decorative Geometric Background --}}
                <div style="position: absolute; top: -30px; right: -30px; width: 150px; height: 150px;
                            background: radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 70%);
                            border-radius: 50%; pointer-events: none;"></div>
                <div style="position: absolute; bottom: -20px; left: -20px; width: 100px; height: 100px;
                            background: radial-gradient(circle, rgba(27,115,64,0.08) 0%, transparent 70%);
                            border-radius: 50%; pointer-events: none;"></div>

                <div class="quran-card-body py-5 text-center" style="position: relative; z-index: 1;">
                    @if($banner->title_arabic)
                    <div class="mb-4" style="
                        font-family: var(--quran-font, 'Amiri Quran', 'KFGQPC Uthmanic Script HAFS', serif);
                        font-size: clamp(1.4rem, 3vw, 2rem);
                        line-height: 2.2;
                        color: var(--quran-text-primary, #1a1a1a);
                        direction: rtl;
                        text-align: right;
                        padding: 0 1rem;">
                        {{ $banner->title_arabic }}
                        <span style="color: #1B7340; font-size: 1.2em;">﴾</span>
                    </div>
                    <hr style="border-color: rgba(212,175,55,0.3); margin: 1.5rem 2rem;">
                    @endif

                    <div style="font-size: 1rem; line-height: 1.8; color: var(--quran-text-muted, #555);">
                        {{ $banner->verse }}
                    </div>

                    @if($banner->source)
                    <div class="mt-3">
                        <span class="badge px-3 py-2" style="background: rgba(212,175,55,0.15); color: #a08000; border: 1px solid rgba(212,175,55,0.3); font-size: 0.85rem;">
                            <i class="bi bi-book me-1"></i>{{ $banner->source }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Surah Link Card --}}
            @if($banner->surah)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-link-45deg me-2 text-primary"></i>
                        {{ __('banners.fields.linked_to') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(13,110,253,0.05);">
                        <div class="surah-number" style="width: 50px; height: 50px; font-size: 1rem;">
                            {{ $banner->surah->number }}
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 1.1rem; direction: rtl;">
                                {{ $banner->surah->name_ar }}
                            </div>
                            <div class="text-muted small">
                                {{ $banner->surah->name_ku ?? $banner->surah->name_en }}
                                — {{ __('banners.fields.ayah_number') }}: {{ $banner->ayah_number ?? 1 }}
                            </div>
                        </div>
                        <div class="ms-auto">
                            <span class="quran-table-badge info">
                                <i class="bi bi-book-open me-1"></i>
                                {{ __('banners.surah_link', ['name' => $banner->surah->name_ar, 'number' => $banner->ayah_number ?? 1]) }}
                            </span>
                        </div>
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
                        {{-- Status --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('banners.table.status') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            @if($banner->is_active)
                                <span class="quran-table-badge success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('banners.status.active') }}
                                </span>
                            @else
                                <span class="quran-table-badge danger">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('banners.status.inactive') }}
                                </span>
                            @endif
                        </dd>

                        {{-- Order --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('banners.fields.order') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="surah-number" style="width: 36px; height: 36px; font-size: 0.85rem; display: inline-flex;">
                                {{ $banner->order }}
                            </span>
                        </dd>

                        {{-- Source --}}
                        @if($banner->source)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('banners.fields.source') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="badge bg-light text-dark border">{{ $banner->source }}</span>
                        </dd>
                        @endif

                        {{-- Linked Surah --}}
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('banners.fields.linked_to') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            @if($banner->surah)
                                <span class="quran-table-badge info small">{{ $banner->surah->name_ar }}</span>
                            @else
                                <span class="text-muted small">{{ __('banners.fields.not_linked') }}</span>
                            @endif
                        </dd>

                        {{-- Dates --}}
                        @if($banner->created_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.created_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $banner->created_at->format('Y-m-d') }}</dd>
                        @endif

                        @if($banner->updated_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.updated_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $banner->updated_at->diffForHumans() }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="quran-card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('banners.edit', $banner) }}" class="quran-btn quran-btn-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('banners.actions.edit') }}
                        </a>
                        <form method="POST" action="{{ route('banners.destroy', $banner) }}"
                              onsubmit="return confirm('{{ __('banners.messages.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="quran-btn quran-btn-danger w-100">
                                <i class="bi bi-trash me-1"></i>{{ __('banners.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
