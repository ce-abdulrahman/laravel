@extends('layouts.app')

@section('title', 'Manage Themes')
@section('page-title', 'Manage Themes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.themes.dashboard') }}">Themes Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manage Themes</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Manage Themes</h1>
            <div class="text-muted">Create, edit, and configure Tasbih themes and assets.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.themes.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i> New Theme
            </a>
            <a href="{{ route('admin.themes.dashboard') }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-grid-fill me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('admin.themes.index') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label text-muted small fw-bold">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Search by key, name..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="quran-btn quran-btn-primary w-100 py-2">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    {{-- List Table --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">Thumbnail</th>
                        <th class="py-3">Theme Key / Name</th>
                        <th class="py-3">Category</th>
                        <th class="py-3">Version</th>
                        <th class="py-3">Unlock System</th>
                        <th class="py-3 text-center">Featured</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($themes as $theme)
                        <tr>
                            <td class="px-4 py-3">
                                @if($theme->thumbnail)
                                    <img src="{{ asset($theme->thumbnail) }}" alt="Thumbnail" class="rounded" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #eee;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center border" style="width: 50px; height: 50px;">
                                        <i class="bi bi-palette text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="fw-bold text-dark">{{ $theme->name }}</div>
                                <span class="font-monospace text-muted small" style="font-size: 0.75rem;">{{ $theme->theme_key }}</span>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded">
                                    <i class="{{ $theme->category->icon ?? 'bi bi-tag' }} me-1"></i>
                                    {{ $theme->category ? $theme->category->name : 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-info-subtle text-info px-2 py-1 rounded">v{{ $theme->version }}</span>
                            </td>
                            <td class="py-3">
                                @if($theme->unlock_type === 'free')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded">Free</span>
                                @elseif($theme->unlock_type === 'points')
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded">{{ $theme->unlock_value }} Points</span>
                                @elseif($theme->unlock_type === 'streak')
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded">{{ $theme->unlock_value }} Day Streak</span>
                                @elseif($theme->unlock_type === 'achievement')
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded">Achievement</span>
                                @else
                                    <span class="badge bg-dark-subtle text-dark px-2 py-1 rounded">{{ ucfirst($theme->unlock_type) }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                @if($theme->is_featured)
                                    <i class="bi bi-star-fill text-warning fs-5" title="Featured Theme"></i>
                                @else
                                    <i class="bi bi-star text-muted" title="Not Featured"></i>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                @if($theme->is_active)
                                    <span class="badge bg-success px-2 py-1 rounded">Active</span>
                                @else
                                    <span class="badge bg-danger px-2 py-1 rounded">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.themes.edit', $theme->id) }}" class="quran-btn quran-btn-outline-primary py-1 px-2 btn-sm" title="Edit Theme">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.themes.destroy', $theme->id) }}" onsubmit="return confirm('Are you sure you want to delete this theme?')" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="quran-btn quran-btn-outline-danger py-1 px-2 btn-sm" title="Delete Theme">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No themes configured in this filter set.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($themes->hasPages())
            <div class="card-footer bg-transparent border-0 px-4 py-3">
                {{ $themes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
