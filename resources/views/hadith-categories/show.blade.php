{{-- resources/views/hadith-categories/show.blade.php --}}
@extends('layouts.app')

@section('title', __('hadith_categories.titles.show'))
@section('page-title', __('hadith_categories.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('hadith-categories.index') }}">{{ __('hadith_categories.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('hadith_categories.titles.show') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('hadith_categories.titles.show') }}</h1>
            <div class="text-muted">{{ __('hadith_categories.hints.show') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hadith-categories.edit', $category) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('hadith_categories.actions.edit') }}
            </a>
            <a href="{{ route('hadith-categories.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('hadith_categories.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Card --}}
        <div class="col-lg-8">
            {{-- Names Card --}}
            <div class="quran-card mb-4" style="
                background: linear-gradient(135deg, rgba(27,115,64,0.05) 0%, rgba(212,175,55,0.03) 100%);
                border: 1px solid rgba(27,115,64,0.12);
                overflow: hidden; position: relative;">
                <div style="position:absolute;top:-30px;right:-30px;width:130px;height:130px;
                            background:radial-gradient(circle,rgba(212,175,55,0.07) 0%,transparent 70%);
                            border-radius:50%;pointer-events:none;"></div>

                <div class="quran-card-header border-0" style="background: transparent;">
                    <h5 class="quran-card-title mb-0">
                        <i class="bi bi-tag-fill me-2" style="color: #1B7340;"></i>
                        {{ __('hadith_categories.sections.info') }}
                    </h5>
                </div>
                <div class="quran-card-body pt-0">
                    <div class="row g-4">
                        {{-- Kurdish --}}
                        <div class="col-md-4">
                            <label class="text-muted small d-block mb-1">{{ __('hadith_categories.fields.name_ku') }}</label>
                            <div style="font-size: 1.1rem; font-weight: 600;">{{ $category->name_ku }}</div>
                        </div>
                        {{-- Arabic --}}
                        <div class="col-md-4">
                            <label class="text-muted small d-block mb-1">{{ __('hadith_categories.fields.name_ar') }}</label>
                            <div class="surah-name-arabic" style="font-size: 1.3rem; direction: rtl; text-align: right;">
                                {{ $category->name_ar }}
                            </div>
                        </div>
                        {{-- English --}}
                        <div class="col-md-4">
                            <label class="text-muted small d-block mb-1">{{ __('hadith_categories.fields.name_en') }}</label>
                            <div style="font-size: 1rem; color: var(--quran-text-secondary);">
                                {{ $category->name_en ?? '—' }}
                            </div>
                        </div>
                    </div>

                    @if($category->icon)
                    <hr style="border-color: rgba(27,115,64,0.1); margin: 1.25rem 0;">
                    <div>
                        <label class="text-muted small d-block mb-2">{{ __('hadith_categories.fields.icon') }}</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="surah-number" style="width:52px;height:52px;font-size:1.4rem;display:inline-flex;">
                                <i class="bi bi-book"></i>
                            </div>
                            <div>
                                <code class="badge bg-light text-dark border" style="font-size:0.9rem;padding:0.5em 0.8em;">
                                    {{ $category->icon }}
                                </code>
                                <p class="text-muted small mb-0 mt-1">{{ __('hadith_categories.fields.icon_hint') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Hadiths Link Card --}}
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-list-task me-2" style="color: #1B7340;"></i>
                        {{ __('hadith_categories.table.hadiths_count') }}
                    </h5>
                </div>
                <div class="quran-card-body text-center py-4">
                    <p class="text-muted mb-3">{{ __('hadith_categories.no_hadiths') }}</p>
                    <a href="{{ route('hadiths.index') }}?category_id={{ $category->id }}"
                       class="quran-btn quran-btn-outline-primary">
                        <i class="bi bi-arrow-right me-1"></i>
                        {{ __('hadith_categories.actions.view_hadiths') }}
                    </a>
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
                    <dl class="row g-2 mb-0">
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('hadith_categories.table.status') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            @if($category->is_active)
                                <span class="quran-table-badge success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('hadith_categories.status.active') }}
                                </span>
                            @else
                                <span class="quran-table-badge danger">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('hadith_categories.status.inactive') }}
                                </span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('hadith_categories.fields.order') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="surah-number" style="width:36px;height:36px;font-size:0.85rem;display:inline-flex;">
                                {{ $category->order }}
                            </span>
                        </dd>

                        @if($category->created_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.created_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $category->created_at->format('Y-m-d') }}</dd>
                        @endif

                        @if($category->updated_at)
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.updated_at') }}</dt>
                        <dd class="col-sm-7 mb-0 small">{{ $category->updated_at->diffForHumans() }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="quran-card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('hadith-categories.edit', $category) }}" class="quran-btn quran-btn-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('hadith_categories.actions.edit') }}
                        </a>
                        <form method="POST" action="{{ route('hadith-categories.destroy', $category) }}"
                              onsubmit="return confirm('{{ __('hadith_categories.messages.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="quran-btn quran-btn-danger w-100">
                                <i class="bi bi-trash me-1"></i>{{ __('hadith_categories.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
