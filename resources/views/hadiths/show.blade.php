{{-- resources/views/hadiths/show.blade.php --}}
@extends('layouts.app')

@section('title', __('hadiths.titles.show'))
@section('page-title', __('hadiths.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hadiths.index') }}">{{ __('hadiths.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('hadiths.titles.show') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('hadiths.titles.show') }}</h1>
            <div class="text-muted">{{ __('hadiths.hints.show') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hadiths.edit', $hadith) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('hadiths.actions.edit') }}
            </a>
            <a href="{{ route('hadiths.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('hadiths.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Arabic text card -->
            <div class="quran-card mb-4" style="
                background: linear-gradient(135deg, rgba(27,115,64,0.04) 0%, rgba(212,175,55,0.02) 100%);
                border: 1px solid rgba(27,115,64,0.12);
                position: relative; overflow: hidden;
            ">
                <div style="position: absolute; top: -30px; right: -30px; width: 150px; height: 150px;
                            background: radial-gradient(circle, rgba(212,175,55,0.06) 0%, transparent 70%);
                            border-radius: 50%; pointer-events: none;"></div>
                
                <div class="quran-card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                        <i class="bi bi-chat-quote me-1"></i>
                        {{ $hadith->category->{'name_' . app()->getLocale()} ?? $hadith->category->name_ku }}
                    </span>
                    @if($hadith->narrator)
                        <span class="text-muted small">
                            <i class="bi bi-person-badge me-1"></i>
                            {{ $hadith->narrator }}
                        </span>
                    @endif
                </div>

                <div class="quran-card-body pt-2 pb-4 text-center">
                    <div class="surah-name-arabic my-3 px-3" style="
                        font-family: 'Scheherazade New', 'Amiri', 'Traditional Arabic', serif;
                        font-size: 1.85rem;
                        line-height: 2.2;
                        color: #1a4f32;
                        direction: rtl;
                        text-shadow: 0 1px 1px rgba(0,0,0,0.02);
                    ">
                        {{ $hadith->arabic_text }}
                    </div>

                    @if($hadith->source)
                        <div class="mt-3">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1">
                                <i class="bi bi-journal-bookmark me-1"></i>
                                {{ $hadith->source }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Translations Card -->
            <x-translations.show-tabs :model="$hadith" :active-languages="$activeLanguages" />
        </div>

        <!-- Sidebar -->
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
                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('hadiths.table.status') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            @if($hadith->is_active)
                                <span class="quran-table-badge success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('hadiths.status.active') }}
                                </span>
                            @else
                                <span class="quran-table-badge danger">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('hadiths.status.inactive') }}
                                </span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted small fw-normal">{{ __('hadiths.fields.order') }}</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="surah-number" style="width: 36px; height: 36px; font-size: 0.85rem; display: inline-flex;">
                                {{ $hadith->order }}
                            </span>
                        </dd>

                        @if($hadith->created_at)
                            <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.created_at') }}</dt>
                            <dd class="col-sm-7 mb-0 small text-muted">{{ $hadith->created_at->format('Y-m-d') }}</dd>
                        @endif

                        @if($hadith->updated_at)
                            <dt class="col-sm-5 text-muted small fw-normal">{{ __('common.updated_at') }}</dt>
                            <dd class="col-sm-7 mb-0 small text-muted">{{ $hadith->updated_at->diffForHumans() }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="quran-card-footer border-top">
                    <div class="d-grid gap-2">
                        <a href="{{ route('hadiths.edit', $hadith) }}" class="quran-btn quran-btn-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('hadiths.actions.edit') }}
                        </a>
                        <form method="POST" action="{{ route('hadiths.destroy', $hadith) }}"
                              onsubmit="return confirmDelete(event)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="quran-btn quran-btn-danger w-100">
                                <i class="bi bi-trash me-1"></i>{{ __('hadiths.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(event) {
        event.preventDefault();
        const form = event.target;

        if (confirm('{{ __('hadiths.messages.confirm_delete') }}')) {
            form.submit();
        }
        return false;
    }
</script>
@endpush
