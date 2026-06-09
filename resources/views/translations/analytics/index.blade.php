@extends('layouts.app')

@section('title', 'Translation Analytics & Monitoring')
@section('page-title', 'Translation Analytics & Monitoring')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">Translation Manager</a></li>
    <li class="breadcrumb-item active" aria-current="page">Analytics &amp; Monitoring</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📊 Translation Analytics &amp; Monitoring</h1>
            <div class="text-muted">Real-time lookup speed, cache hit efficiency, missing keys prioritize tracking, and AI generation metrics.</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="quran-btn quran-btn-outline-primary" onclick="flushBuffer()">
                <i class="bi bi-arrow-repeat me-1"></i> Flush Buffer
            </button>
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Manager
            </a>
        </div>
    </div>

    {{-- System Status Banner if empty logs --}}
    <div id="analytics-alert" class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4 d-none" role="alert"
         style="background: linear-gradient(135deg, rgba(27,115,64,0.12) 0%, rgba(16,185,129,0.08) 100%); border-left: 4px solid #1B7340 !important;">
        <i class="bi bi-check-circle-fill me-2 text-success"></i>
        <span id="analytics-alert-text">Buffer flushed successfully!</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        {{-- Total Requests --}}
        <div class="col-12 col-md-3">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border-radius: 16px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 text-primary" style="background-color: rgba(13, 110, 253, 0.1);">
                        <i class="bi bi-activity fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-semibold mb-1">Lookups Today</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalHitsToday) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Average Lookup Speed --}}
        <div class="col-12 col-md-3">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border-radius: 16px;">
                <div class="d-flex align-items-center gap-3">
                    @php
                        $speedColor = $performanceStats['avg_lookup_ms'] < 2.0 ? 'text-success' : ($performanceStats['avg_lookup_ms'] < 5.0 ? 'text-warning' : 'text-danger');
                        $speedBg = $performanceStats['avg_lookup_ms'] < 2.0 ? 'rgba(25, 135, 84, 0.1)' : ($performanceStats['avg_lookup_ms'] < 5.0 ? 'rgba(255, 193, 7, 0.1)' : 'rgba(220, 53, 69, 0.1)');
                    @endphp
                    <div class="p-3 rounded-3 {{ $speedColor }}" style="background-color: {{ $speedBg }};">
                        <i class="bi bi-lightning-charge-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-semibold mb-1">Avg Speed</h6>
                        <h3 class="fw-bold mb-0 text-dark font-monospace">{{ $performanceStats['avg_lookup_ms'] }}<span class="fs-6 fw-normal text-muted">ms</span></h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cache Hit Efficiency --}}
        <div class="col-12 col-md-3">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border-radius: 16px;">
                <div class="d-flex align-items-center gap-3">
                    @php
                        $hitColor = $performanceStats['cache_hit_rate'] > 90 ? 'text-success' : ($performanceStats['cache_hit_rate'] > 75 ? 'text-warning' : 'text-danger');
                        $hitBg = $performanceStats['cache_hit_rate'] > 90 ? 'rgba(25, 135, 84, 0.1)' : ($performanceStats['cache_hit_rate'] > 75 ? 'rgba(255, 193, 7, 0.1)' : 'rgba(220, 53, 69, 0.1)');
                    @endphp
                    <div class="p-3 rounded-3 {{ $hitColor }}" style="background-color: {{ $hitBg }};">
                        <i class="bi bi-hdd-network-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-semibold mb-1">Cache Hit Rate</h6>
                        <h3 class="fw-bold mb-0 text-dark font-monospace">{{ $performanceStats['cache_hit_rate'] }}<span class="fs-6 fw-normal text-muted">%</span></h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI Acceptance Rate --}}
        <div class="col-12 col-md-3">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border-radius: 16px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 text-info" style="background-color: rgba(13, 202, 240, 0.1);">
                        <i class="bi bi-robot fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-semibold mb-1">AI Acceptance</h6>
                        <h3 class="fw-bold mb-0 text-dark font-monospace">{{ $aiStats['acceptance_rate'] }}<span class="fs-6 fw-normal text-muted">%</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Analytics Layout --}}
    <div class="row g-4 mb-4">
        {{-- Language Distribution --}}
        <div class="col-12 col-lg-5">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <h5 class="fw-bold text-dark mb-1">🌐 Language Distribution</h5>
                <p class="text-muted small mb-4">Percentage share of requested translations by language locale.</p>

                @if(empty($langDistribution))
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-globe fs-2 d-block mb-2 text-secondary"></i>
                        No language queries recorded yet.
                    </div>
                @else
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-4 mb-4">
                        {{-- SVG Donut Chart --}}
                        <div style="position: relative; width: 140px; height: 140px;">
                            <svg width="140" height="140" viewBox="0 0 42 42" style="transform: rotate(-90deg);">
                                <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#f3f4f6" stroke-width="4.5"></circle>
                                @php
                                    $accumulatedPercentage = 0;
                                    $colors = ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6f42c1', '#d63384'];
                                @endphp
                                @foreach($langDistribution as $index => $item)
                                    @php
                                        $color = $colors[$index % count($colors)];
                                        $pct = $item['percentage'];
                                    @endphp
                                    <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" 
                                            stroke="{{ $color }}" stroke-width="4.5" 
                                            stroke-dasharray="{{ $pct }} {{ 100 - $pct }}" 
                                            stroke-dashoffset="{{ 100 - $accumulatedPercentage + 25 }}"></circle>
                                    @php
                                        $accumulatedPercentage += $pct;
                                    @endphp
                                @endforeach
                            </svg>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                <span class="d-block fw-bold text-dark font-monospace" style="font-size: 1.15rem;">{{ count($langDistribution) }}</span>
                                <span class="text-muted small" style="font-size: 0.7rem;">Locales</span>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="flex-grow-1 w-100">
                            @foreach($langDistribution as $index => $item)
                                @php
                                    $color = $colors[$index % count($colors)];
                                    $langObj = $languages->firstWhere('code', $item['locale']);
                                @endphp
                                <div class="d-flex align-items-center justify-content-between mb-2 small">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle" style="width: 10px; height: 10px; background-color: {{ $color }}; display: inline-block;"></span>
                                        <span class="fw-semibold text-dark">{{ $langObj ? $langObj->name : strtoupper($item['locale']) }}</span>
                                        <span class="text-muted">({{ $item['locale'] }})</span>
                                    </div>
                                    <span class="fw-bold font-monospace text-dark">{{ $item['percentage'] }}%</span>
                                </div>
                                <div class="progress mb-3" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $item['percentage'] }}%; background-color: {{ $color }};" aria-valuenow="{{ $item['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Busiest Modules and Keys --}}
        <div class="col-12 col-lg-7">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <h5 class="fw-bold text-dark mb-1">🔥 Heatmap &amp; Access Patterns</h5>
                <p class="text-muted small mb-4">Top requested UI groups and individual keys by frequency.</p>

                <div class="row g-4">
                    {{-- Top Groups/Modules --}}
                    <div class="col-12 col-sm-6">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-folder-fill text-warning me-1"></i> Busiest Groups</h6>
                        @if(empty($heatmapData['top_modules']))
                            <div class="text-muted py-4 text-center small">No group logs available.</div>
                        @else
                            @php
                                $maxModuleHits = reset($heatmapData['top_modules'])['count'] ?? 1;
                            @endphp
                            <div class="d-flex flex-column gap-3">
                                @foreach($heatmapData['top_modules'] as $mod)
                                    @php
                                        $barWidth = $maxModuleHits > 0 ? ($mod['count'] / $maxModuleHits) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1 small">
                                            <span class="font-monospace fw-bold text-dark">{{ $mod['module'] }}</span>
                                            <span class="text-muted font-monospace fw-semibold">{{ number_format($mod['count']) }} hits</span>
                                        </div>
                                        <div class="progress" style="height: 8px; border-radius: 4px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $barWidth }}%;" aria-valuenow="{{ $barWidth }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Top Keys --}}
                    <div class="col-12 col-sm-6">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-key-fill text-primary me-1"></i> Busiest Keys</h6>
                        @if(empty($heatmapData['top_keys']))
                            <div class="text-muted py-4 text-center small">No key logs available.</div>
                        @else
                            @php
                                $maxKeyHits = reset($heatmapData['top_keys'])->count ?? 1;
                            @endphp
                            <div class="d-flex flex-column gap-3">
                                @foreach($heatmapData['top_keys'] as $k)
                                    @php
                                        $barWidth = $maxKeyHits > 0 ? ($k->count / $maxKeyHits) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1 small">
                                            <span class="font-monospace text-truncate text-dark me-2" style="max-width: 160px;" title="{{ $k->key_name }}">{{ $k->key_name }}</span>
                                            <span class="text-muted font-monospace fw-semibold">{{ number_format($k->count) }} hits</span>
                                        </div>
                                        <div class="progress" style="height: 8px; border-radius: 4px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $barWidth }}%;" aria-valuenow="{{ $barWidth }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ranked Missing Keys & AI Fix --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="quran-card p-4 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Missing Keys Analytics</h5>
                        <p class="text-muted small mb-0">Missing translation lookups ranked by frequency and weighted priority score.</p>
                    </div>
                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill font-monospace fw-bold">
                        {{ count($missingAnalysis) }} Unique Missing
                    </span>
                </div>

                @if(empty($missingAnalysis))
                    <div class="text-center py-5 text-success">
                        <i class="bi bi-shield-fill-check fs-1 d-block mb-2"></i>
                        <h6 class="fw-bold mb-1">Outstanding! No missing translations recorded.</h6>
                        <span class="text-muted small">Your users have had 100% of UI translations resolved correctly.</span>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Translation Key</th>
                                    <th>Page Origin</th>
                                    <th class="text-center">Total Hits</th>
                                    <th class="text-center">Priority Score</th>
                                    <th class="text-end">AI Self-Fix</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @foreach($missingAnalysis as $missing)
                                    <tr id="row-{{ str_replace('.', '-', $missing['key']) }}">
                                        <td class="font-monospace fw-bold text-dark">{{ $missing['key'] }}</td>
                                        <td class="text-muted"><code>/{{ $missing['page'] }}</code></td>
                                        <td class="text-center font-monospace">{{ number_format($missing['hits']) }}</td>
                                        <td class="text-center">
                                            @php
                                                $pScore = $missing['priority_score'];
                                                $badgeClass = $pScore > 100 ? 'bg-danger' : ($pScore > 20 ? 'bg-warning text-dark' : 'bg-secondary');
                                            @endphp
                                            <span class="badge {{ $badgeClass }} font-monospace px-2 py-1">{{ $pScore }}</span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" 
                                                    class="quran-btn quran-btn-sm quran-btn-primary" 
                                                    onclick="fixWithAi('{{ $missing['key'] }}')">
                                                <i class="bi bi-cpu-fill me-1"></i> Fix with AI
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Telemetry performance detailed distribution --}}
    <div class="row g-4">
        {{-- Telemetry Details --}}
        <div class="col-12 col-md-6">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <h5 class="fw-bold text-dark mb-1">⏱ Telemetry Distribution</h5>
                <p class="text-muted small mb-4">Request fallback classification ratios and lookup speeds.</p>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <div class="text-muted small mb-1">Database Fallbacks</div>
                            <h4 class="fw-bold text-dark font-monospace mb-0">{{ $performanceStats['db_fallback_rate'] }}%</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <div class="text-muted small mb-1">AI Fallback Rate</div>
                            <h4 class="fw-bold text-dark font-monospace mb-0">{{ $performanceStats['ai_usage_rate'] }}%</h4>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Cache Load Ratio vs Database Hits</span>
                                <span class="badge bg-success-subtle text-success font-monospace">{{ $performanceStats['cache_hit_rate'] }}% Cached</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $performanceStats['cache_hit_rate'] }}%;" aria-valuenow="{{ $performanceStats['cache_hit_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ 100 - $performanceStats['cache_hit_rate'] }}%;" aria-valuenow="{{ 100 - $performanceStats['cache_hit_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI Acceptance details --}}
        <div class="col-12 col-md-6">
            <div class="quran-card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <h5 class="fw-bold text-dark mb-1">🤖 AI Quality metrics</h5>
                <p class="text-muted small mb-4">Tracking efficacy and manual revisions on AI generated translations.</p>

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <span class="text-muted small"><i class="bi bi-robot me-1 text-info"></i> Total translations generated by AI</span>
                        <span class="fw-bold font-monospace text-dark">{{ $aiStats['ai_generated'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <span class="text-muted small"><i class="bi bi-pencil-square me-1 text-warning"></i> Revisions (Edited after AI)</span>
                        <span class="fw-bold font-monospace text-dark">{{ $aiStats['edited_after_ai'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <span class="text-muted small"><i class="bi bi-check-circle-fill me-1 text-success"></i> Accepted As-Is (No revisions)</span>
                        <span class="fw-bold font-monospace text-dark">{{ $aiStats['accepted_as_is'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><i class="bi bi-star-fill me-1 text-primary"></i> Efficacy Score</span>
                        <span class="badge bg-primary font-monospace px-3 py-1">{{ $aiStats['acceptance_rate'] }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function flushBuffer() {
        axios.post("{{ route('translations-manager.analytics.flush') }}")
            .then(response => {
                const alertDiv = document.getElementById('analytics-alert');
                const alertText = document.getElementById('analytics-alert-text');
                
                alertText.innerText = response.data.message;
                alertDiv.classList.remove('d-none');
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            })
            .catch(error => {
                alert('Failed to flush analytics buffer.');
                console.error(error);
            });
    }

    function fixWithAi(key) {
        const rowId = 'row-' + key.replace(/\./g, '-');
        const rowElement = document.getElementById(rowId);
        const actionCell = rowElement.querySelector('td:last-child');
        
        const originalContent = actionCell.innerHTML;
        actionCell.innerHTML = `
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-1 text-muted text-nowrap">Translating...</span>
        `;

        axios.post("{{ route('translations-manager.analytics.ai-fix') }}", { key: key })
            .then(response => {
                if (response.data.success) {
                    actionCell.innerHTML = `
                        <span class="badge bg-success-subtle text-success py-2 px-3">
                            <i class="bi bi-check-circle-fill me-1"></i> Fixed
                        </span>
                    `;
                    // Fade row slightly or alert
                    rowElement.style.opacity = '0.6';
                    
                    const alertDiv = document.getElementById('analytics-alert');
                    const alertText = document.getElementById('analytics-alert-text');
                    alertText.innerText = `AI successfully generated missing translations for key: "${key}"`;
                    alertDiv.classList.remove('d-none');
                } else {
                    actionCell.innerHTML = originalContent;
                    alert('Failed to fix translation.');
                }
            })
            .catch(error => {
                actionCell.innerHTML = originalContent;
                alert('AI translation engine returned an error.');
                console.error(error);
            });
    }
</script>
@endpush
