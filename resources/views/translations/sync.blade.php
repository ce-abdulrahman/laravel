@extends('layouts.app')

@section('title', __('translations_manager.sync.title'))
@section('page-title', __('translations_manager.sync.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">{{ __('translations_manager.title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('translations_manager.sync.breadcrumb') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('translations_manager.sync.heading') }}</h1>
            <div class="text-muted">{{ __('translations_manager.sync.subtitle') }}</div>
        </div>
        <div>
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> {{ __('translations_manager.sync.back_to_manager') }}
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
                    {{ __('translations_manager.sync.pull_title') }}
                </h5>
                <p class="text-muted small mb-4">{{ __('translations_manager.sync.pull_description') }}</p>
                
                <form action="{{ route('translations-manager.sync-pull') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="remote_url_pull" class="form-label fw-semibold text-muted small text-uppercase">{{ __('translations_manager.sync.remote_endpoint') }}</label>
                        <input type="url" 
                               id="remote_url_pull" 
                               name="remote_url" 
                               class="form-control" 
                               placeholder="https://staging.example.com/api/translations/sync" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase d-block">{{ __('translations_manager.sync.conflict_strategy') }}</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="strategy" id="strategy_latest" value="latest_wins" checked>
                            <label class="form-check-label small" for="strategy_latest">
                                <strong>{{ __('translations_manager.sync.latest_wins_label') }}</strong> {{ __('translations_manager.sync.latest_wins_description') }}
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="strategy" id="strategy_remote" value="remote_wins">
                            <label class="form-check-label small" for="strategy_remote">
                                <strong>{{ __('translations_manager.sync.remote_wins_label') }}</strong> {{ __('translations_manager.sync.remote_wins_description') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="strategy" id="strategy_local" value="local_wins">
                            <label class="form-check-label small" for="strategy_local">
                                <strong>{{ __('translations_manager.sync.local_wins_label') }}</strong> {{ __('translations_manager.sync.local_wins_description') }}
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="quran-btn quran-btn-primary">
                            <i class="bi bi-arrow-down-circle me-1"></i> {{ __('translations_manager.sync.pull_action') }}
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
                    {{ __('translations_manager.sync.push_title') }}
                </h5>
                <p class="text-muted small mb-4">{{ __('translations_manager.sync.push_description') }}</p>

                <form action="{{ route('translations-manager.sync-push') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="remote_url_push" class="form-label fw-semibold text-muted small text-uppercase">{{ __('translations_manager.sync.remote_endpoint') }}</label>
                        <input type="url" 
                               id="remote_url_push" 
                               name="remote_url" 
                               class="form-control" 
                               placeholder="https://staging.example.com/api/translations/sync" 
                               required>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="quran-btn quran-btn-success text-white">
                            <i class="bi bi-arrow-up-circle me-1"></i> {{ __('translations_manager.sync.push_action') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Credentials Info Card --}}
        <div class="col-12">
            <div class="quran-card p-4 bg-light">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-key-fill text-warning me-1"></i> {{ __('translations_manager.sync.credentials_title') }}</h6>
                <p class="text-muted small mb-3">{{ __('translations_manager.sync.credentials_description') }}</p>
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent text-muted small font-monospace">TRANSLATIONS_SYNC_TOKEN</span>
                            <input type="text" class="form-control font-monospace bg-white" readonly value="{{ config('translations.sync_token') }}">
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText('{{ config('translations.sync_token') }}'); alert('{{ __('translations_manager.sync.token_copied') }}');">
                            <i class="bi bi-clipboard"></i> {{ __('translations_manager.sync.copy_token') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
