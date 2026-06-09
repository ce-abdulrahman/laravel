@extends('layouts.app')

@section('title', 'Translation Environment Synchronization')
@section('page-title', 'Translation Environment Synchronization')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">Translation Manager</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sync Center</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Environment Sync Center</h1>
            <div class="text-muted">Push or pull translations between different environments (e.g. Local, Staging, Production).</div>
        </div>
        <div>
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Manager
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert"
         style="background: linear-gradient(135deg, rgba(27,115,64,0.12) 0%, rgba(16,185,129,0.08) 100%); border-left: 4px solid #1B7340 !important;">
        <i class="bi bi-check-circle-fill me-2 text-success"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        {{-- Pull Card --}}
        <div class="col-12 col-md-6">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-2">
                    <i class="bi bi-cloud-arrow-down-fill text-primary me-2"></i>
                    Pull Translations (Import)
                </h5>
                <p class="text-muted small mb-4">Pull all translations from a remote environment's sync endpoint and resolve conflicts dynamically.</p>
                
                <form action="{{ route('translations-manager.sync-pull') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="remote_url_pull" class="form-label fw-semibold text-muted small text-uppercase">Remote Synchronization Endpoint</label>
                        <input type="url" 
                               id="remote_url_pull" 
                               name="remote_url" 
                               class="form-control" 
                               placeholder="https://staging.example.com/api/translations/sync" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase d-block">Conflict Resolution Strategy</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="strategy" id="strategy_latest" value="latest_wins" checked>
                            <label class="form-check-label small" for="strategy_latest">
                                <strong>Latest Wins (Recommended):</strong> Compares updated timestamps, newest state overrides.
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="strategy" id="strategy_remote" value="remote_wins">
                            <label class="form-check-label small" for="strategy_remote">
                                <strong>Remote Wins:</strong> The remote environment values will overwrite all local ones.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="strategy" id="strategy_local" value="local_wins">
                            <label class="form-check-label small" for="strategy_local">
                                <strong>Local Wins:</strong> Preserve local translations; only import new missing keys.
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="quran-btn quran-btn-primary">
                            <i class="bi bi-arrow-down-circle me-1"></i> Pull and Merge Translations
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Push Card --}}
        <div class="col-12 col-md-6">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-2">
                    <i class="bi bi-cloud-arrow-up-fill text-success me-2"></i>
                    Push Translations (Export)
                </h5>
                <p class="text-muted small mb-4">Export and push your local translation database directly into a remote staging or production environment.</p>

                <form action="{{ route('translations-manager.sync-push') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="remote_url_push" class="form-label fw-semibold text-muted small text-uppercase">Remote Synchronization Endpoint</label>
                        <input type="url" 
                               id="remote_url_push" 
                               name="remote_url" 
                               class="form-control" 
                               placeholder="https://staging.example.com/api/translations/sync" 
                               required>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="quran-btn quran-btn-success text-white">
                            <i class="bi bi-arrow-up-circle me-1"></i> Push Local Data to Remote
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Credentials Info Card --}}
        <div class="col-12">
            <div class="quran-card p-4 bg-light">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-key-fill text-warning me-1"></i> Synchronization Authorization credentials</h6>
                <p class="text-muted small mb-3">Both pushing and pulling require authorization via a sync token. Place the sync token below inside the target environment's <code>.env</code> file:</p>
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent text-muted small font-monospace">TRANSLATIONS_SYNC_TOKEN</span>
                            <input type="text" class="form-control font-monospace bg-white" readonly value="{{ config('translations.sync_token') }}">
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText('{{ config('translations.sync_token') }}'); alert('Token copied!');">
                            <i class="bi bi-clipboard"></i> Copy Token
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
