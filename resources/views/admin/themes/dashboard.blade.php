@extends('layouts.app')

@section('title', 'Themes Dashboard')
@section('page-title', 'Themes Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Themes Dashboard</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Themes Dashboard</h1>
            <div class="text-muted">Overview of Tasbih themes, usage, and downloads telemetry.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.themes.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-list me-1"></i> Manage Themes
            </a>
            <a href="{{ route('admin.themes.categories') }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-grid me-1"></i> Manage Categories
            </a>
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="quran-stat-card card border-0 shadow-sm p-4 text-center">
                <div class="quran-stat-icon bg-primary-subtle text-primary rounded-circle mx-auto mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="bi bi-palette"></i>
                </div>
                <div class="quran-stat-label text-muted small text-uppercase">Total Themes</div>
                <div class="quran-stat-value h2 font-weight-bold my-1 text-primary">{{ $totalThemes }}</div>
                <div class="small text-success">{{ $activeThemes }} Active Themes</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="quran-stat-card card border-0 shadow-sm p-4 text-center">
                <div class="quran-stat-icon bg-success-subtle text-success rounded-circle mx-auto mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="bi bi-download"></i>
                </div>
                <div class="quran-stat-label text-muted small text-uppercase">Downloads</div>
                <div class="quran-stat-value h2 font-weight-bold my-1 text-success">{{ $totalDownloads }}</div>
                <div class="small text-muted">Theme asset files retrieved</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="quran-stat-card card border-0 shadow-sm p-4 text-center">
                <div class="quran-stat-icon bg-danger-subtle text-danger rounded-circle mx-auto mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="bi bi-heart-fill"></i>
                </div>
                <div class="quran-stat-label text-muted small text-uppercase">Favorites</div>
                <div class="quran-stat-value h2 font-weight-bold my-1 text-danger">{{ $totalFavorites }}</div>
                <div class="small text-muted">User favorite bookmarks</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="quran-stat-card card border-0 shadow-sm p-4 text-center">
                <div class="quran-stat-icon bg-warning-subtle text-warning rounded-circle mx-auto mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div class="quran-stat-label text-muted small text-uppercase">Most Active Theme</div>
                <div class="quran-stat-value h4 font-weight-bold my-2 text-warning text-truncate px-2" title="{{ $mostUsedTheme ? $mostUsedTheme->theme_key : 'None' }}">
                    {{ $mostUsedTheme ? ucwords(str_replace('_', ' ', $mostUsedTheme->theme_key)) : 'None' }}
                </div>
                <div class="small text-muted">{{ $mostUsedTheme ? $mostUsedTheme->count : 0 }} users currently active</div>
            </div>
        </div>
    </div>

    {{-- Details Row --}}
    <div class="row g-4">
        {{-- Top Downloads --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-download text-primary me-2"></i> Top Downloaded Themes
                </h5>
                <ul class="list-group list-group-flush">
                    @forelse($topDownloads as $download)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-light py-3">
                            <span class="fw-semibold text-dark">{{ ucwords(str_replace('_', ' ', $download->theme_key)) }}</span>
                            <span class="badge bg-primary rounded-pill px-3">{{ $download->count }} Downloads</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted px-0 bg-transparent py-4">No download history found.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Top Favorites --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-heart-fill text-danger me-2"></i> Top Favorited Themes
                </h5>
                <ul class="list-group list-group-flush">
                    @forelse($topFavorites as $fav)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-light py-3">
                            <span class="fw-semibold text-dark">{{ ucwords(str_replace('_', ' ', $fav->theme_key)) }}</span>
                            <span class="badge bg-danger rounded-pill px-3">{{ $fav->count }} Favorites</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted px-0 bg-transparent py-4">No favorites history found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
