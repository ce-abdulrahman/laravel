@extends('layouts.app')

@section('title', __('translations_manager.audit.title'))
@section('page-title', __('translations_manager.audit.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">{{ __('translations_manager.title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('translations_manager.audit.breadcrumb') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('translations_manager.audit.heading') }}</h1>
            <div class="text-muted">{{ __('translations_manager.audit.subtitle') }}</div>
        </div>
        <div>
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> {{ __('translations_manager.audit.back_to_manager') }}
            </a>
        </div>
    </div>

    {{-- Tabs Header --}}
    <ul class="nav nav-pills gap-2 mb-4" id="auditTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-semibold" id="coverage-tab" data-bs-toggle="pill" data-bs-target="#coverage" type="button" role="tab">
                <i class="bi bi-pie-chart-fill me-1"></i> {{ __('translations_manager.audit.tab_coverage') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="missing-tab" data-bs-toggle="pill" data-bs-target="#missing" type="button" role="tab">
                <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i> {{ __('translations_manager.audit.tab_missing') }} ({{ count($results['missing']) + count($results['empty']) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="duplicates-tab" data-bs-toggle="pill" data-bs-target="#duplicates" type="button" role="tab">
                <i class="bi bi-files text-warning me-1"></i> {{ __('translations_manager.audit.tab_duplicates') }} ({{ count($results['duplicates']) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="codebase-tab" data-bs-toggle="pill" data-bs-target="#codebase" type="button" role="tab">
                <i class="bi bi-code-slash text-info me-1"></i> {{ __('translations_manager.audit.tab_codebase') }}
            </button>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="auditTabContent">
        {{-- Tab 1: Coverage --}}
        <div class="tab-pane fade show active" id="coverage" role="tabpanel">
            <div class="row g-4">
                @foreach($results['coverage'] as $code => $data)
                    <div class="col-12 col-md-4">
                        <div class="quran-card p-4 h-100">
                            <h5 class="fw-bold text-dark mb-1">{{ $data['name'] }}</h5>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace mb-3">{{ strtoupper($code) }}</span>
                            
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <span class="display-6 fw-extrabold text-primary">{{ $data['coverage_percentage'] }}%</span>
                                <span class="text-muted small">{{ __('translations_manager.audit.coverage_label') }}</span>
                            </div>

                            <div class="progress mb-3" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $data['coverage_percentage'] }}%" aria-valuenow="{{ $data['coverage_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="d-flex justify-content-between text-muted small">
                                <span>{{ __('translations_manager.audit.translated_keys_label') }}</span>
                                <span class="fw-bold text-dark">{{ $data['translated'] }} / {{ $data['total_keys'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tab 2: Missing / Empty --}}
        <div class="tab-pane fade" id="missing" role="tabpanel">
            <div class="quran-card p-4">
                <h5 class="fw-bold text-dark mb-3">{{ __('translations_manager.audit.missing_values_title') }}</h5>
                
                @if(empty($results['missing']) && empty($results['empty']))
                    <div class="alert alert-success border-0 rounded-3 mb-0">
                        <i class="bi bi-shield-check me-2"></i> {{ __('translations_manager.audit.no_missing_found') }}
                    </div>
                @else
                    @foreach($languages as $lang)
                        @php
                            $missingList = $results['missing'][$lang->code] ?? [];
                            $emptyList = $results['empty'][$lang->code] ?? [];
                            $totalIssues = count($missingList) + count($emptyList);
                        @endphp
                        
                        @if($totalIssues > 0)
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark d-flex align-items-center mb-2">
                                    <span class="me-2">{{ $lang->flag }}</span>
                                    <span>{{ $lang->name }} ({{ strtoupper($lang->code) }})</span>
                                    <span class="badge bg-danger rounded-pill ms-2">{{ $totalIssues }} {{ __('translations_manager.audit.issues_count') }}</span>
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light small">
                                            <tr>
                                                <th>{{ __('translations_manager.audit.table_key') }}</th>
                                                <th>{{ __('translations_manager.audit.table_status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small font-monospace">
                                            @foreach($missingList as $m)
                                                <tr>
                                                    <td class="text-danger-emphasis">{{ $m }}</td>
                                                    <td><span class="badge bg-danger-subtle text-danger">{{ __('translations_manager.audit.status_missing') }}</span></td>
                                                </tr>
                                            @endforeach
                                            @foreach($emptyList as $e)
                                                <tr>
                                                    <td class="text-warning-emphasis">{{ $e }}</td>
                                                    <td><span class="badge bg-warning-subtle text-warning">{{ __('translations_manager.audit.status_empty') }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Tab 3: Duplicates --}}
        <div class="tab-pane fade" id="duplicates" role="tabpanel">
            <div class="quran-card p-4">
                <h5 class="fw-bold text-dark mb-1">{{ __('translations_manager.audit.duplicate_values_title') }}</h5>
                <p class="text-muted small mb-4">{{ __('translations_manager.audit.duplicate_values_description') }}</p>

                @if(empty($results['duplicates']))
                    <div class="alert alert-success border-0 rounded-3 mb-0">
                        <i class="bi bi-shield-check me-2"></i> {{ __('translations_manager.audit.no_duplicates_found') }}
                    </div>
                @else
                    @foreach($results['duplicates'] as $code => $dups)
                        @php $lang = $languages->where('code', $code)->first(); @endphp
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-2">
                                <span class="me-2">{{ $lang?->flag }}</span>
                                <span>{{ $lang?->name ?? $code }}</span>
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>{{ __('translations_manager.audit.table_value') }}</th>
                                            <th>{{ __('translations_manager.audit.table_shared_keys') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @foreach($dups as $d)
                                            <tr>
                                                <td class="fw-semibold text-dark font-monospace">"{{ $d['value'] }}"</td>
                                                <td>
                                                    @foreach($d['keys'] as $k)
                                                        <code class="d-block text-secondary font-monospace mb-1">{{ $k }}</code>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Tab 4: Codebase Audit --}}
        <div class="tab-pane fade" id="codebase" role="tabpanel">
            <div class="row g-4">
                {{-- Orphans --}}
                <div class="col-12 col-md-6">
                    <div class="quran-card p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1">{{ __('translations_manager.audit.orphan_keys_title') }}</h5>
                        <div class="text-muted small mb-3">{{ __('translations_manager.audit.orphan_keys_description') }}</div>

                        @if(empty($results['orphans']))
                            <div class="alert alert-success border-0 rounded-3 mb-0">
                                <i class="bi bi-shield-check me-2"></i> {{ __('translations_manager.audit.no_orphans_found') }}
                            </div>
                        @else
                            <div class="alert alert-warning border-0 rounded-3 small mb-3">
                                <strong>{{ __('translations_manager.audit.note_label') }}</strong> {{ __('translations_manager.audit.orphan_keys_warning') }}
                            </div>
                            <div style="max-height: 350px; overflow-y: auto;">
                                <ul class="list-group list-group-flush font-monospace small">
                                    @foreach($results['orphans'] as $orphan)
                                        <li class="list-group-item text-secondary py-1 border-0">{{ $orphan }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Missing from DB --}}
                <div class="col-12 col-md-6">
                    <div class="quran-card p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1">{{ __('translations_manager.audit.missing_db_keys_title') }}</h5>
                        <div class="text-muted small mb-3">{{ __('translations_manager.audit.missing_db_keys_description') }}</div>

                        @if(empty($results['missing_from_db']))
                            <div class="alert alert-success border-0 rounded-3 mb-0">
                                <i class="bi bi-shield-check me-2"></i> {{ __('translations_manager.audit.no_missing_db_found') }}
                            </div>
                        @else
                            <div class="alert alert-danger border-0 rounded-3 small mb-3">
                                <strong>{{ __('translations_manager.audit.warning_label') }}</strong> {{ __('translations_manager.audit.missing_db_keys_warning') }}
                            </div>
                            <div style="max-height: 350px; overflow-y: auto;">
                                <ul class="list-group list-group-flush font-monospace small">
                                    @foreach($results['missing_from_db'] as $missingKey)
                                        <li class="list-group-item text-danger py-1 border-0">{{ $missingKey }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
