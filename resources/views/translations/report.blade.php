{{-- resources/views/translations/report.blade.php --}}
@extends('layouts.app')

@section('title', __('translations_manager.report.title'))
@section('page-title', __('translations_manager.report.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">{{ __('translations_manager.title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('translations_manager.report.breadcrumb') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('translations_manager.report.heading') }}</h1>
            <div class="text-muted">{{ __('translations_manager.report.subtitle') }}</div>
        </div>
        <div>
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> {{ __('translations_manager.report.back_to_manager') }}
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-card p-3 border-start border-4 border-primary">
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">{{ __('translations_manager.report.overall_coverage') }}</small>
                <h3 class="fw-extrabold text-primary my-1">{{ number_format($data['stats']['coverage_percentage'] ?? 100.0, 2) }}%</h3>
                <span class="text-muted small">{{ number_format($data['stats']['completed_units']) }} / {{ number_format($data['stats']['expected_units']) }} {{ __('translations_manager.report.units') }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-card p-3 border-start border-4 border-warning">
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">{{ __('translations_manager.report.missing_translations') }}</small>
                <h3 class="fw-extrabold text-warning my-1">{{ number_format($data['stats']['missing_translations']) }}</h3>
                <span class="text-muted small">{{ __('translations_manager.report.across_active_languages') }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-card p-3 border-start border-4 border-info">
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">{{ __('translations_manager.report.unused_keys') }}</small>
                <h3 class="fw-extrabold text-info my-1">{{ count($data['unused_keys']) }}</h3>
                <span class="text-muted small">{{ __('translations_manager.report.found_in_db_not_code') }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-card p-3 border-start border-4 border-danger">
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">{{ __('translations_manager.report.orphan_rows') }}</small>
                <h3 class="fw-extrabold text-danger my-1">{{ count($data['orphan_translations']) }}</h3>
                <span class="text-muted small">{{ __('translations_manager.report.no_parent_records') }}</span>
            </div>
        </div>
    </div>

    <!-- Main Diagnostic Sections -->
    <div class="row g-4 mb-4">
        <!-- Languages & Modules -->
        <div class="col-lg-6">
            <!-- Language Coverage -->
            <div class="quran-card p-4 mb-4">
                <h5 class="fw-bold mb-3 text-dark">
                    <i class="bi bi-globe me-2 text-primary"></i> {{ __('translations_manager.report.coverage_per_language') }}
                </h5>
                <div class="d-flex flex-column gap-3">
                    @foreach($data['languages'] as $lang)
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">{{ $lang['name'] }} ({{ strtoupper($lang['code']) }})</span>
                                <span class="badge bg-light text-primary font-monospace">{{ $lang['percentage'] }}%</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $lang['percentage'] }}%; border-radius: 5px;"></div>
                            </div>
                            <small class="text-muted">{{ number_format($lang['completed']) }} / {{ number_format($lang['total']) }} {{ __('translations_manager.report.keys_translated') }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Database Module Coverage -->
            <div class="quran-card p-4">
                <h5 class="fw-bold mb-3 text-dark">
                    <i class="bi bi-database me-2 text-success"></i> {{ __('translations_manager.report.coverage_per_module') }}
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('translations_manager.report.module') }}</th>
                                <th>{{ __('translations_manager.report.items') }}</th>
                                <th class="text-end">{{ __('translations_manager.report.coverage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['modules'] as $module)
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $module['name'] }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ number_format($module['items_count']) }} {{ __('translations_manager.report.items_unit') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge @if($module['percentage'] >= 90) bg-success-subtle text-success @elseif($module['percentage'] >= 50) bg-warning-subtle text-warning @else bg-danger-subtle text-danger @endif font-monospace" style="font-size: 11px;">
                                            {{ $module['percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Groups Coverage -->
        <div class="col-lg-6">
            <div class="quran-card p-4 h-100" style="max-height: 700px; overflow-y: auto;">
                <h5 class="fw-bold mb-3 text-dark">
                    <i class="bi bi-folder-fill me-2 text-warning"></i> {{ __('translations_manager.report.coverage_per_group') }}
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('translations_manager.report.group') }}</th>
                                <th>{{ __('translations_manager.report.keys') }}</th>
                                <th class="text-end">{{ __('translations_manager.report.coverage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['groups'] as $group)
                                <tr>
                                    <td>
                                        <code class="text-secondary font-monospace">{{ $group['group'] }}</code>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ number_format($group['keys_count']) }} {{ __('translations_manager.report.keys_unit') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge @if($group['percentage'] >= 90) bg-success-subtle text-success @elseif($group['percentage'] >= 50) bg-warning-subtle text-warning @else bg-danger-subtle text-danger @endif font-monospace" style="font-size: 11px;">
                                            {{ $group['percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagnostic Details Section -->
    <div class="row g-4">
        <!-- Missing Keys -->
        <div class="col-md-6">
            <div class="quran-card p-4" style="max-height: 400px; overflow-y: auto;">
                <h6 class="fw-bold text-danger mb-3">
                    <i class="bi bi-exclamation-octagon me-2"></i> {{ __('translations_manager.report.keys_missing_translations') }} ({{ count($data['missing_keys']) }})
                </h6>
                @if(count($data['missing_keys']) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['missing_keys'] as $mk)
                            <div class="list-group-item px-0 py-2 border-0 border-bottom">
                                <code class="text-dark font-monospace text-break small">{{ $mk['key'] }}</code>
                                <div class="text-muted small">{{ __('translations_manager.report.group') }}: {{ $mk['group'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                        {{ __('translations_manager.report.no_missing_translations') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Unused Keys -->
        <div class="col-md-6">
            <div class="quran-card p-4" style="max-height: 400px; overflow-y: auto;">
                <h6 class="fw-bold text-warning mb-3">
                    <i class="bi bi-trash3 me-2"></i> {{ __('translations_manager.report.unused_keys_in_code') }} ({{ count($data['unused_keys']) }})
                </h6>
                @if(count($data['unused_keys']) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($data['unused_keys'] as $uk)
                            <div class="list-group-item px-0 py-2 border-0 border-bottom">
                                <code class="text-dark font-monospace text-break small">{{ $uk['key'] }}</code>
                                <div class="text-muted small">{{ __('translations_manager.report.group') }}: {{ $uk['group'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                        {{ __('translations_manager.report.no_unused_keys') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Dynamic Key Warnings -->
    <div class="row g-4 mt-2 mb-4">
        <div class="col-12">
            <div class="quran-card p-4 border-start border-4" style="border-left-color: hsl(35, 90%, 55%) !important;">
                <div class="d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#dynamicWarningsCollapse" role="button" aria-expanded="false" aria-controls="dynamicWarningsCollapse" style="cursor: pointer;">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="color: hsl(35, 95%, 50%);"></i>
                        <span>{{ __('translations_manager.report.dynamic_warnings_title') }} ({{ count($data['dynamic_warnings'] ?? []) }})</span>
                    </h5>
                    <span class="text-muted"><i class="bi bi-chevron-down"></i></span>
                </div>
                
                <div class="collapse mt-3" id="dynamicWarningsCollapse">
                    <div class="text-muted small mb-3">
                        {{ __('translations_manager.report.dynamic_warnings_description') }}
                    </div>
                    
                    @if(count($data['dynamic_warnings'] ?? []) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr style="background-color: hsl(35, 100%, 98%); border-bottom: 2px solid hsl(35, 80%, 90%);">
                                        <th class="py-2 px-3" style="width: 45%;">{{ __('translations_manager.report.file_path') }}</th>
                                        <th class="py-2 px-3" style="width: 10%;">{{ __('translations_manager.report.line') }}</th>
                                        <th class="py-2 px-3" style="width: 45%;">{{ __('translations_manager.report.expression') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['dynamic_warnings'] as $warning)
                                        <tr style="border-bottom: 1px solid hsl(35, 80%, 94%);">
                                            <td class="px-3">
                                                <code class="text-dark small text-break">{{ str_replace(base_path(), '', $warning->file_path) }}</code>
                                            </td>
                                            <td class="px-3">
                                                <span class="badge bg-warning-subtle text-warning-emphasis font-monospace">{{ $warning->line_number }}</span>
                                            </td>
                                            <td class="px-3">
                                                <code class="text-danger small text-break">{{ $warning->expression }}</code>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted small" style="background-color: hsl(120, 40%, 98%); border-radius: 8px; border: 1px dashed hsl(120, 40%, 90%);">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                            {{ __('translations_manager.report.no_dynamic_warnings') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
