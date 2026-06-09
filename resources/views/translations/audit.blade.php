@extends('layouts.app')

@section('title', 'Translation Integrity Audit')
@section('page-title', 'Translation Integrity Audit')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">Translation Manager</a></li>
    <li class="breadcrumb-item active" aria-current="page">Integrity Scan</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Translation Integrity Scan</h1>
            <div class="text-muted">Analyze localization database coverage, discover missing translations, orphans, and duplicate strings.</div>
        </div>
        <div>
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Manager
            </a>
        </div>
    </div>

    {{-- Tabs Header --}}
    <ul class="nav nav-pills gap-2 mb-4" id="auditTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-semibold" id="coverage-tab" data-bs-toggle="pill" data-bs-target="#coverage" type="button" role="tab">
                <i class="bi bi-pie-chart-fill me-1"></i> Coverage &amp; Stats
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="missing-tab" data-bs-toggle="pill" data-bs-target="#missing" type="button" role="tab">
                <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i> Missing / Empty ({{ count($results['missing']) + count($results['empty']) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="duplicates-tab" data-bs-toggle="pill" data-bs-target="#duplicates" type="button" role="tab">
                <i class="bi bi-files text-warning me-1"></i> Duplicate Strings ({{ count($results['duplicates']) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="codebase-tab" data-bs-toggle="pill" data-bs-target="#codebase" type="button" role="tab">
                <i class="bi bi-code-slash text-info me-1"></i> Codebase Audit
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
                                <span class="text-muted small">coverage</span>
                            </div>

                            <div class="progress mb-3" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $data['coverage_percentage'] }}%" aria-valuenow="{{ $data['coverage_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="d-flex justify-content-between text-muted small">
                                <span>Translated Keys</span>
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
                <h5 class="fw-bold text-dark mb-3">Missing Translation Values</h5>
                
                @if(empty($results['missing']) && empty($results['empty']))
                    <div class="alert alert-success border-0 rounded-3 mb-0">
                        <i class="bi bi-shield-check me-2"></i> Perfect! No missing or empty translations found.
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
                                    <span class="badge bg-danger rounded-pill ms-2">{{ $totalIssues }} issues</span>
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light small">
                                            <tr>
                                                <th>Translation Key</th>
                                                <th>Issue Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small font-monospace">
                                            @foreach($missingList as $m)
                                                <tr>
                                                    <td class="text-danger-emphasis">{{ $m }}</td>
                                                    <td><span class="badge bg-danger-subtle text-danger">Missing record</span></td>
                                                </tr>
                                            @endforeach
                                            @foreach($emptyList as $e)
                                                <tr>
                                                    <td class="text-warning-emphasis">{{ $e }}</td>
                                                    <td><span class="badge bg-warning-subtle text-warning">Blank / Empty string</span></td>
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
                <h5 class="fw-bold text-dark mb-1">Duplicate Translation Values</h5>
                <p class="text-muted small mb-4">The following strings have identical values shared across different translation keys. (Ignoring short words less than 5 characters)</p>

                @if(empty($results['duplicates']))
                    <div class="alert alert-success border-0 rounded-3 mb-0">
                        <i class="bi bi-shield-check me-2"></i> Excellent! No duplicate translation values detected.
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
                                            <th>Translation Value</th>
                                            <th>Shared Translation Keys</th>
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
                        <h5 class="fw-bold text-dark mb-1">Orphan Translation Keys</h5>
                        <div class="text-muted small mb-3">Keys defined in the database but not referenced in code files.</div>

                        @if(empty($results['orphans']))
                            <div class="alert alert-success border-0 rounded-3 mb-0">
                                <i class="bi bi-shield-check me-2"></i> No orphan keys found in database.
                            </div>
                        @else
                            <div class="alert alert-warning border-0 rounded-3 small mb-3">
                                <strong>Note:</strong> These keys might be safe to delete if they are not used dynamically (e.g. string concatenation).
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
                        <h5 class="fw-bold text-dark mb-1">Missing Database Keys</h5>
                        <div class="text-muted small mb-3">Keys used in PHP/Blade code (e.g. <code>t(...)</code>) but missing in database.</div>

                        @if(empty($results['missing_from_db']))
                            <div class="alert alert-success border-0 rounded-3 mb-0">
                                <i class="bi bi-shield-check me-2"></i> No missing database keys found. All code calls exist!
                            </div>
                        @else
                            <div class="alert alert-danger border-0 rounded-3 small mb-3">
                                <strong>Warning:</strong> These keys will render as plain text fallback keys if not added to the database.
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
