@extends('layouts.app')

@section('title', 'Translation Intelligence Dashboard')
@section('page-title', 'Translation Intelligence Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">Translation Manager</a></li>
    <li class="breadcrumb-item active" aria-current="page">Intelligence Dashboard</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🧠 Translation Intelligence Dashboard</h1>
            <div class="text-muted">Semantic search, naming diagnostics, consistency analysis, and smart AI suggestion helpers.</div>
        </div>
        <div>
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Manager
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="quran-card p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle bg-primary-subtle text-primary">
                        <i class="bi bi-key-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-1">TOTAL SYSTEM KEYS</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalKeys }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="quran-card p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle bg-warning-subtle text-warning">
                        <i class="bi bi-folder-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-1">SYSTEM GROUPS</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ count($groupsSummary) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="quran-card p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle bg-info-subtle text-info">
                        <i class="bi bi-cpu-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-1">CONTEXT ENGINE STATUS</h6>
                        <h3 class="fw-bold mb-0 text-success">ONLINE</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills gap-2 mb-4" id="intelligenceTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-semibold" id="search-tab" data-bs-toggle="pill" data-bs-target="#searchPanel" type="button" role="tab">
                <i class="bi bi-search me-1"></i> Semantic Search
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="suggest-tab" data-bs-toggle="pill" data-bs-target="#suggestPanel" type="button" role="tab">
                <i class="bi bi-lightbulb me-1"></i> Smart Key Suggestions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="consistency-tab" data-bs-toggle="pill" data-bs-target="#consistencyPanel" type="button" role="tab" onclick="runDiagnostics()">
                <i class="bi bi-activity text-danger me-1"></i> Consistency Diagnostics
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-semibold" id="grouping-tab" data-bs-toggle="pill" data-bs-target="#groupingPanel" type="button" role="tab">
                <i class="bi bi-diagram-3 me-1"></i> Auto-Grouping &amp; Rebuild
            </button>
        </li>
    </ul>

    {{-- Panels --}}
    <div class="tab-content" id="intelligenceTabContent">
        {{-- Search Panel --}}
        <div class="tab-pane fade show active" id="searchPanel" role="tabpanel">
            <div class="quran-card p-4">
                <h5 class="fw-bold text-dark mb-1">Semantic &amp; Context Search</h5>
                <p class="text-muted small mb-4">Search keys and values by intent and meaning rather than exact text strings. (Matches context structure and segment synonyms)</p>
                
                <div class="row g-2 mb-4">
                    <div class="col-10">
                        <input type="text" id="semanticQuery" class="form-control" placeholder="E.g. authentication button, logout settings...">
                    </div>
                    <div class="col-2 d-grid">
                        <button type="button" class="quran-btn quran-btn-primary" onclick="runSemanticSearch()">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </div>

                <div id="search-loading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-muted">Running semantic similarity queries...</span>
                </div>

                <div id="search-results" style="display: none;">
                    <h6 class="fw-bold text-dark mb-3">Matching Results (Ranked by confidence score)</h6>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Translation Key</th>
                                    <th>Group</th>
                                    <th>Context Description</th>
                                    <th class="text-end">Semantic Score</th>
                                </tr>
                            </thead>
                            <tbody id="search-results-body" class="small">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Suggestions Panel --}}
        <div class="tab-pane fade" id="suggestPanel" role="tabpanel">
            <div class="quran-card p-4">
                <h5 class="fw-bold text-dark mb-1">Smart Key Naming Suggestions</h5>
                <p class="text-muted small mb-4">Enter a label or description in human phrase and let the engine output suggested dot-notated key naming conventions.</p>

                <div class="row g-2 mb-4">
                    <div class="col-10">
                        <input type="text" id="suggestText" class="form-control" placeholder="E.g. Submit login page, Confirm password update form...">
                    </div>
                    <div class="col-2 d-grid">
                        <button type="button" class="quran-btn quran-btn-primary" onclick="runSuggestions()">
                            <i class="bi bi-lightbulb-fill me-1"></i> Suggest
                        </button>
                    </div>
                </div>

                <div id="suggest-loading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-muted">Analyzing syntax parameters...</span>
                </div>

                <div id="suggest-results" style="display: none;">
                    <h6 class="fw-bold text-dark mb-3">Naming Suggestions</h6>
                    <div class="list-group" id="suggest-results-list">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>

        {{-- Consistency Diagnostics Panel --}}
        <div class="tab-pane fade" id="consistencyPanel" role="tabpanel">
            <div class="row g-4">
                {{-- Naming standard issues --}}
                <div class="col-12 col-md-6">
                    <div class="quran-card p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-bug-fill text-danger me-1"></i> Bad Casings / Inconsistent Structure</h5>
                        <p class="text-muted small mb-3">Keys violating alphanumeric and lowercase dot-notation standards (e.g. camelCase).</p>
                        
                        <div id="diag-inconsistent" class="overflow-auto" style="max-height: 300px;">
                            <!-- Dynamically loaded -->
                        </div>
                    </div>
                </div>

                {{-- Duplicates values --}}
                <div class="col-12 col-md-6">
                    <div class="quran-card p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-files text-warning me-1"></i> Redundant Identical Values</h5>
                        <p class="text-muted small mb-3">Different keys holding identical translations (candidates to merge/re-use).</p>
                        
                        <div id="diag-duplicates" class="overflow-auto" style="max-height: 300px;">
                            <!-- Dynamically loaded -->
                        </div>
                    </div>
                </div>

                {{-- Unused keys --}}
                <div class="col-12 col-md-6">
                    <div class="quran-card p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-trash-fill text-muted me-1"></i> Unused Keys in Code</h5>
                        <p class="text-muted small mb-3">Keys defined in database but never scanned inside app PHP or Blade files.</p>
                        
                        <div id="diag-unused" class="overflow-auto" style="max-height: 300px;">
                            <!-- Dynamically loaded -->
                        </div>
                    </div>
                </div>

                {{-- Missing groups --}}
                <div class="col-12 col-md-6">
                    <div class="quran-card p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-question-circle text-info me-1"></i> Missing / General Groups</h5>
                        <p class="text-muted small mb-3">Keys assigned to default general groups or missing categorizations.</p>
                        
                        <div id="diag-missing-groups" class="overflow-auto" style="max-height: 300px;">
                            <!-- Dynamically loaded -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grouping Rebuilder Panel --}}
        <div class="tab-pane fade" id="groupingPanel" role="tabpanel">
            <div class="quran-card p-4">
                <h5 class="fw-bold text-dark mb-1">Group Restructuring Engine</h5>
                <p class="text-muted small mb-4">Automatically audits database groups and reorganizes keys into matching namespace prefixes (e.g. `auth.*` keys are grouped into `auth`).</p>

                <div class="alert alert-warning border-0 rounded-3 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Caution:</strong> Running the rebuild engine will update the database group classifications. Restructuring changes are logged and will refresh all active translation caches.
                </div>

                <div class="d-grid mb-4">
                    <button type="button" class="quran-btn quran-btn-primary" onclick="rebuildGroups()">
                        <i class="bi bi-arrow-repeat me-1"></i> Start Bulk Group Restructuring
                    </button>
                </div>

                <div id="rebuild-loading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-muted">Analyzing namespace structures and updating database tables...</span>
                </div>

                <div id="rebuild-results" style="display: none;">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-1"></i> Rebuild Completed Successfully!</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light small">
                                <tr>
                                    <th>Translation Key</th>
                                    <th>Old Group</th>
                                    <th>New Restructured Group</th>
                                </tr>
                            </thead>
                            <tbody id="rebuild-results-body" class="small font-monospace">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function runSemanticSearch() {
        const query = document.getElementById('semanticQuery').value;
        const loading = document.getElementById('search-loading');
        const results = document.getElementById('search-results');
        const body = document.getElementById('search-results-body');

        if (!query) return;

        loading.style.display = 'block';
        results.style.display = 'none';
        body.innerHTML = '';

        axios.post("{{ route('translations-manager.intelligence.search') }}", { query: query })
            .then(response => {
                loading.style.display = 'none';
                results.style.display = 'block';

                const data = response.data.results;
                if (data.length === 0) {
                    body.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No semantic matches found. Try other terms.</td></tr>';
                    return;
                }

                data.forEach(item => {
                    let scoreClass = 'bg-danger';
                    if (item.score >= 3.0) scoreClass = 'bg-success';
                    else if (item.score >= 1.5) scoreClass = 'bg-primary';
                    else if (item.score >= 0.8) scoreClass = 'bg-warning text-dark';

                    body.innerHTML += `
                        <tr>
                            <td class="font-monospace fw-bold text-dark">${item.key}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border">${item.group}</span></td>
                            <td class="text-muted">${item.description || 'No description'}</td>
                            <td class="text-end"><span class="badge ${scoreClass} font-monospace fs-6 px-3 py-2">${item.score}</span></td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                loading.style.display = 'none';
                alert('Semantic Search failed.');
                console.error(error);
            });
    }

    function runSuggestions() {
        const text = document.getElementById('suggestText').value;
        const loading = document.getElementById('suggest-loading');
        const results = document.getElementById('suggest-results');
        const list = document.getElementById('suggest-results-list');

        if (!text) return;

        loading.style.display = 'block';
        results.style.display = 'none';
        list.innerHTML = '';

        axios.post("{{ route('translations-manager.intelligence.suggest') }}", { text: text })
            .then(response => {
                loading.style.display = 'none';
                results.style.display = 'block';

                const suggestions = response.data.suggestions;
                if (suggestions.length === 0) {
                    list.innerHTML = '<div class="text-muted py-2">Could not identify naming keywords. Try typing more action words.</div>';
                    return;
                }

                suggestions.forEach(item => {
                    const badge = item.exists 
                        ? '<span class="badge bg-danger">Already Exists in DB</span>' 
                        : '<span class="badge bg-success">Available / Recommended</span>';

                    list.innerHTML += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <code class="fs-6 text-dark font-monospace">${item.key}</code>
                            ${badge}
                        </div>
                    `;
                });
            })
            .catch(error => {
                loading.style.display = 'none';
                alert('Suggestions lookup failed.');
                console.error(error);
            });
    }

    function runDiagnostics() {
        const divInc = document.getElementById('diag-inconsistent');
        const divDups = document.getElementById('diag-duplicates');
        const divUnused = document.getElementById('diag-unused');
        const divMissingG = document.getElementById('diag-missing-groups');

        divInc.innerHTML = '<div class="text-center py-4 small text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Running checks...</div>';
        divDups.innerHTML = divInc.innerHTML;
        divUnused.innerHTML = divInc.innerHTML;
        divMissingG.innerHTML = divInc.innerHTML;

        axios.get("{{ route('translations-manager.intelligence.consistency') }}")
            .then(response => {
                const diag = response.data.diagnostics;

                // 1. Inconsistent keys
                divInc.innerHTML = '';
                if (diag.inconsistent_keys.length === 0) {
                    divInc.innerHTML = '<div class="alert alert-success border-0 rounded-3 small">All keys match casing guidelines!</div>';
                } else {
                    diag.inconsistent_keys.forEach(k => {
                        divInc.innerHTML += `
                            <div class="border-bottom py-2 small">
                                <code class="text-danger-emphasis">${k.key}</code><br>
                                <span class="text-muted small">${k.reason}</span>
                            </div>
                        `;
                    });
                }

                // 2. Duplicate values
                divDups.innerHTML = '';
                const locales = Object.keys(diag.duplicates);
                if (locales.length === 0) {
                    divDups.innerHTML = '<div class="alert alert-success border-0 rounded-3 small">No duplicates detected in any language.</div>';
                } else {
                    locales.forEach(loc => {
                        diag.duplicates[loc].forEach(item => {
                            let keysList = item.keys.map(k => `<code class="d-block text-secondary mb-1">${k}</code>`).join('');
                            divDups.innerHTML += `
                                <div class="border-bottom py-2 small">
                                    <div class="fw-bold mb-1">Value: "${item.value}" (${loc.toUpperCase()})</div>
                                    <div class="ps-2 border-start border-3">${keysList}</div>
                                </div>
                            `;
                        });
                    });
                }

                // 3. Unused keys
                divUnused.innerHTML = '';
                if (diag.unused_keys.length === 0) {
                    divUnused.innerHTML = '<div class="alert alert-success border-0 rounded-3 small">No unused keys found. All keys are referenced in code!</div>';
                } else {
                    diag.unused_keys.forEach(key => {
                        divUnused.innerHTML += `<div class="py-1"><code class="text-muted">${key}</code></div>`;
                    });
                }

                // 4. Missing groups
                divMissingG.innerHTML = '';
                if (diag.missing_groups.length === 0) {
                    divMissingG.innerHTML = '<div class="alert alert-success border-0 rounded-3 small">All keys are categorized outside the general group!</div>';
                } else {
                    diag.missing_groups.forEach(key => {
                        divMissingG.innerHTML += `<div class="py-1"><code class="text-info-emphasis">${key}</code></div>`;
                    });
                }
            })
            .catch(error => {
                const errMsg = '<div class="text-danger small py-3 text-center">Scanner failed to load diagnostics.</div>';
                divInc.innerHTML = errMsg;
                divDups.innerHTML = errMsg;
                divUnused.innerHTML = errMsg;
                divMissingG.innerHTML = errMsg;
                console.error(error);
            });
    }

    function rebuildGroups() {
        if (!confirm('Are you sure you want to trigger bulk grouping restructuring? This will update the "group" field of keys matching naming namespace conventions.')) {
            return;
        }

        const loading = document.getElementById('rebuild-loading');
        const results = document.getElementById('rebuild-results');
        const body = document.getElementById('rebuild-results-body');

        loading.style.display = 'block';
        results.style.display = 'none';
        body.innerHTML = '';

        axios.post("{{ route('translations-manager.intelligence.rebuild-groups') }}")
            .then(response => {
                loading.style.display = 'none';
                results.style.display = 'block';

                const logs = response.data.rebuild_logs;
                if (logs.length === 0) {
                    body.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">All database key classifications are already perfectly aligned. No keys modified.</td></tr>';
                    return;
                }

                logs.forEach(log => {
                    body.innerHTML += `
                        <tr>
                            <td>${log.key}</td>
                            <td class="text-danger-emphasis">${log.old_group}</td>
                            <td class="text-success-emphasis fw-bold">${log.new_group}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                loading.style.display = 'none';
                alert('Restructuring rebuild failed.');
                console.error(error);
            });
    }
</script>
@endpush
