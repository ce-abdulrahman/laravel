@extends('layouts.app')

@section('title', 'Theme Categories')
@section('page-title', 'Theme Categories')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.themes.dashboard') }}">Themes Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Theme Categories</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Theme Categories</h1>
            <div class="text-muted">Manage theme categories and their dynamic translation records.</div>
        </div>
        <a href="{{ route('admin.themes.dashboard') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <div class="row g-4">
        {{-- Category List --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3" style="width: 80px;">Icon</th>
                                <th class="py-3">Name</th>
                                <th class="py-3 text-center" style="width: 120px;">Sort Order</th>
                                <th class="py-3 text-center" style="width: 120px;">Status</th>
                                <th class="py-3 text-end px-4" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                            <i class="{{ $cat->icon }}"></i>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-dark">{{ $cat->name }}</div>
                                        <div class="text-muted small">
                                            @foreach($cat->translations as $t)
                                                <span class="badge bg-light text-dark border me-1">{{ $t->language->code }}: {{ $t->value }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3 text-center fw-bold">{{ $cat->sort_order }}</td>
                                    <td class="py-3 text-center">
                                        <form method="POST" action="{{ route('admin.themes.categories.update', $cat->id) }}" class="m-0">
                                            @csrf
                                            <input type="hidden" name="toggle_active" value="1">
                                            <button type="submit" class="border-0 bg-transparent">
                                                @if($cat->is_active)
                                                    <span class="badge bg-success px-2 py-1 rounded cursor-pointer">Active</span>
                                                @else
                                                    <span class="badge bg-danger px-2 py-1 rounded cursor-pointer">Inactive</span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 text-end px-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="quran-btn quran-btn-outline-primary btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $cat->id }}" title="Edit Category">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.themes.categories.destroy', $cat->id) }}" onsubmit="return confirm('Are you sure you want to delete this category? All child themes will be cascade deleted!')" class="d-inline m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="quran-btn quran-btn-outline-danger btn-sm py-1 px-2" title="Delete Category">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title">Edit Category</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.themes.categories.update', $cat->id) }}">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Bootstrap Icon Class</label>
                                                        <input type="text" name="icon" class="form-control" value="{{ $cat->icon }}" required placeholder="bi bi-tag-fill">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Sort Order</label>
                                                        <input type="number" name="sort_order" class="form-control" value="{{ $cat->sort_order }}" required min="0">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Translations</label>
                                                        @foreach($languages as $lang)
                                                            @php
                                                                $t = $cat->translations->where('language_id', $lang->id)->first();
                                                            @endphp
                                                            <div class="input-group mb-2">
                                                                <span class="input-group-text text-uppercase fw-semibold" style="width: 60px;">{{ $lang->code }}</span>
                                                                <input type="text" name="translations[{{ $lang->code }}][name]" class="form-control" required value="{{ $t ? $t->value : '' }}" placeholder="Name in {{ $lang->name }}">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="quran-btn quran-btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="quran-btn quran-btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No theme categories configured yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Add Category Form --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i> New Category
                </h5>
                <form method="POST" action="{{ route('admin.themes.categories') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="new_icon">Bootstrap Icon Class</label>
                        <input type="text" name="icon" id="new_icon" class="form-control" required placeholder="bi bi-moon-stars-fill">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="new_sort">Sort Order</label>
                        <input type="number" name="sort_order" id="new_sort" class="form-control" required min="0" value="0">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Translations</label>
                        @foreach($languages as $lang)
                            <div class="input-group mb-2">
                                <span class="input-group-text text-uppercase fw-semibold" style="width: 60px;">{{ $lang->code }}</span>
                                <input type="text" name="translations[{{ $lang->code }}][name]" class="form-control" required placeholder="Name in {{ $lang->name }}">
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="quran-btn quran-btn-primary w-100 py-2">
                        <i class="bi bi-save me-1"></i> Save Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
