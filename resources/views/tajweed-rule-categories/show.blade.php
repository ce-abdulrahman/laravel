{{-- resources/views/tajweed-rule-categories/show.blade.php --}}
@extends('layouts.app')

@section('title', __('tajweed_categories.titles.show') . ' - ' . ($tajweedRuleCategory->name_ku ?: $tajweedRuleCategory->name))
@section('page-title', __('tajweed_categories.titles.show'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-rule-categories.index') }}">{{ __('tajweed_categories.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $tajweedRuleCategory->name_ku ?: $tajweedRuleCategory->name }}</li>
@endsection

@section('content')
<div class="quran-dashboard">

    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ $tajweedRuleCategory->name_ku ?: $tajweedRuleCategory->name }}</h1>
            @if($tajweedRuleCategory->name_ar)
            <div class="arabic-text text-muted" dir="rtl">{{ $tajweedRuleCategory->name_ar }}</div>
            @endif
            <small class="text-muted">{{ $tajweedRuleCategory->name }}</small>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('tajweed-rule-categories.edit', $tajweedRuleCategory) }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> {{ __('tajweed_categories.actions.edit') }}
            </a>
            @endif
            <a href="{{ route('tajweed-rule-categories.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> {{ __('tajweed_categories.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Info Card --}}
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-body">
                    <div class="text-center mb-4">
                        <div class="quran-plan-icon mx-auto mb-3" style="width:72px;height:72px;font-size:2rem;">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <h5>{{ $tajweedRuleCategory->name_ku ?: $tajweedRuleCategory->name }}</h5>
                        <span class="quran-table-badge {{ $tajweedRuleCategory->is_active ? 'success' : 'danger' }}">
                            {{ $tajweedRuleCategory->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ __('tajweed_categories.table.rules_count') }}</span>
                        <span class="fw-bold">{{ $rules->total() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ __('tajweed_categories.fields.order') }}</span>
                        <span class="fw-bold">#{{ $tajweedRuleCategory->order }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">{{ __('tajweed_categories.fields.slug') }}</span>
                        <code class="small">{{ $tajweedRuleCategory->slug }}</code>
                    </div>

                    @if($tajweedRuleCategory->description_ku ?: $tajweedRuleCategory->description)
                    <div class="mt-3">
                        <div class="text-muted small mb-1">{{ __('tajweed_categories.fields.description_ku') }}</div>
                        <p class="small" style="font-family:'Cairo';">
                            {{ $tajweedRuleCategory->description_ku ?: $tajweedRuleCategory->description }}
                        </p>
                    </div>
                    @endif

                    @if($tajweedRuleCategory->description_ar)
                    <div class="mt-2">
                        <div class="text-muted small mb-1">{{ __('tajweed_categories.fields.description_ar') }}</div>
                        <p class="small arabic-text" dir="rtl">{{ $tajweedRuleCategory->description_ar }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Rules in This Category --}}
        <div class="col-lg-8">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-palette me-2"></i>
                        {{ __('tajweed_categories.actions.view_rules') }}
                        <span class="quran-table-badge info ms-2">{{ $rules->total() }}</span>
                    </h6>
                    @if(auth()->user()?->role === 'admin')
                    <a href="{{ route('tajweed-rules.create') }}" class="quran-btn quran-btn-outline-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('tajweed_rules.actions.create') }}
                    </a>
                    @endif
                </div>
                <div class="quran-card-body p-0">
                    @forelse($rules as $rule)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                        @if($rule->color_code)
                        <div style="width:36px;height:36px;border-radius:8px;background:{{ $rule->color_code }};
                                    border:2px solid var(--quran-border-light);flex-shrink:0;"></div>
                        @else
                        <div class="quran-plan-icon" style="width:36px;height:36px;font-size:1rem;flex-shrink:0;">
                            <i class="bi bi-palette"></i>
                        </div>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">{{ $rule->name_ku ?: $rule->name }}</div>
                            @if($rule->name_ar)
                            <small class="text-success arabic-text" dir="rtl">{{ $rule->name_ar }}</small>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="quran-table-badge {{ $rule->is_active ? 'success' : 'danger' }}">
                                {{ $rule->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                            <small class="text-muted">{{ $rule->ayah_tajweed_segments_count }} segs</small>
                            <a href="{{ route('tajweed-rules.show', $rule) }}" class="quran-table-action-btn view">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="quran-table-empty">
                        <i class="bi bi-palette"></i>
                        <p>{{ __('tajweed_categories.no_rules') }}</p>
                    </div>
                    @endforelse
                </div>
                @if($rules->hasPages())
                <div class="quran-card-body">
                    {{ $rules->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
