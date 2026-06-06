{{-- resources/views/tajweed-rules/show.blade.php --}}
@extends('layouts.app')

@section('title', $tajweedRule->name)
@section('page-title', $tajweedRule->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-rules.index') }}">{{ __('tajweed_rules.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ $tajweedRule->name }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                @if($tajweedRule->color_code)
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: {{ $tajweedRule->color_code }}; 
                            border: 2px solid var(--quran-border-light);"></div>
                @endif
                <div>
                    <h1 class="h4 mb-1">{{ $tajweedRule->name_ku }} ({{ $tajweedRule->name }})</h1>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @if($tajweedRule->name_ar)
                        <span class="fs-6 arabic-text text-success font-bold" dir="rtl">{{ $tajweedRule->name_ar }}</span>
                        @endif
                        @if($tajweedRule->category)
                        <a href="{{ route('tajweed-rule-categories.show', $tajweedRule->category) }}"
                           class="quran-table-badge info text-decoration-none">
                            {{ $tajweedRule->category->name_ku ?: $tajweedRule->category->name }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('tajweed-segments.create', ['tajweed_rule_id' => $tajweedRule->id]) }}" 
               class="quran-btn quran-btn-success">
                 <i class="bi bi-plus-lg me-1"></i>
                 {{ __('tajweed_segments.actions.add_segment') }}
            </a>
            <a href="{{ route('tajweed-rules.edit', $tajweedRule) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('tajweed-rules.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('tajweed_rules.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Rule Details -->
        <div class="col-lg-5">
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('tajweed_rules.details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.status') }}</label>
                        <div class="quran-detail-value">
                            <span class="quran-table-badge {{ $tajweedRule->is_active ? 'success' : 'danger' }}">
                                {{ $tajweedRule->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </div>
                    </div>

                    @if($tajweedRule->category)
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.category') }}</label>
                        <div class="quran-detail-value">
                            <a href="{{ route('tajweed-rule-categories.show', $tajweedRule->category) }}"
                               class="quran-table-badge info text-decoration-none">
                                <i class="bi bi-folder2-open me-1"></i>
                                {{ $tajweedRule->category->name_ku ?: $tajweedRule->category->name }}
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.color_code') }}</label>
                        <div class="quran-detail-value">
                            @if($tajweedRule->color_code)
                            <span>{{ $tajweedRule->color_code }}</span>
                            <span class="ms-2 d-inline-block" 
                                  style="width: 20px; height: 20px; border-radius: 4px; background-color: {{ $tajweedRule->color_code }};"></span>
                            @else
                            —
                            @endif
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.priority') }}</label>
                        <div class="quran-detail-value">{{ $tajweedRule->priority }}</div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.total_segments') }}</label>
                        <div class="quran-detail-value">
                            <strong>{{ $tajweedRule->ayah_tajweed_segments_count }}</strong> 
                            {{ __('tajweed_rules.segments') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-card-text me-2"></i>
                        {{ __('tajweed_rules.fields.description') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="mb-3">
                        <label class="quran-detail-label d-block mb-1">شیکردنەوە بە کوردی</label>
                        <div class="quran-description text-primary font-medium" style="font-family: 'Cairo'; line-height: 1.6; font-size: 14px;">
                            {{ $tajweedRule->description_ku }}
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="quran-detail-label d-block mb-1">Explanation (English)</label>
                        <div class="quran-description">{{ $tajweedRule->description }}</div>
                    </div>

                    @if($tajweedRule->example_text)
                    <div class="mt-4">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.example_text') }}</label>
                        <div class="bg-light p-3 rounded-3">
                            <div class="arabic-text" style="font-size: 20px;">{{ $tajweedRule->example_text }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Segments List -->
        <div class="col-lg-7">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-puzzle me-2"></i>
                        {{ __('tajweed_rules.segments_list') }}
                    </h5>
                </div>
                <div class="quran-table-container">
                    <table class="quran-table quran-table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('tajweed_segments.fields.surah_ayah') }}</th>
                                <th>{{ __('tajweed_segments.fields.text_segment') }}</th>
                                <th class="text-end">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($segments as $segment)
                            <tr>
                                <td>
                                    <a href="{{ route('ayahs.show', $segment->ayah) }}" class="text-decoration-none">
                                        {{ $segment->ayah->surah->name_ar }} 
                                        ({{ $segment->ayah->ayah_number }})
                                    </a>
                                    @if($segment->start_index !== null)
                                    <small class="text-muted d-block">
                                        {{ $segment->start_index }}-{{ $segment->end_index }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="arabic-text" style="font-size: 18px;">
                                        <span style="background-color: {{ $tajweedRule->color_code }}20; 
                                                     padding: 2px 8px; border-radius: 6px;">
                                            {{ $segment->text_segment }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="quran-table-actions justify-content-end">
                                        <a href="{{ route('tajweed-segments.show', $segment) }}" 
                                           class="quran-table-action-btn view">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('tajweed-segments.edit', $segment) }}" 
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
                                        <i class="bi bi-puzzle"></i>
                                        <h6>{{ __('tajweed_rules.no_segments_yet') }}</h6>
                                        @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('tajweed-segments.create', ['tajweed_rule_id' => $tajweedRule->id]) }}" 
                                           class="quran-btn quran-btn-primary mt-3">
                                            <i class="bi bi-plus-lg me-1"></i>
                                            {{ __('tajweed_segments.actions.add_first') }}
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($segments->hasPages())
                <div class="card-footer">
                    {{ $segments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection