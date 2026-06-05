{{-- resources/views/tajweed-rules/index.blade.php --}}
@extends('layouts.app')

@section('title', __('tajweed_rules.titles.index'))
@section('page-title', __('tajweed_rules.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('tajweed_rules.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tajweed_rules.titles.index') }}</h1>
            <div class="text-muted">{{ __('tajweed_rules.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i>
                Import JSON
            </button>
            <button type="button" class="quran-btn quran-btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <i class="bi bi-code-slash me-1"></i>
                Example JSON
            </button>
            <a href="{{ route('tajweed-segments.create') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tajweed_segments.actions.create') }}
            </a>
            <a href="{{ route('tajweed-rules.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tajweed_rules.actions.create') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_rules.total_rules') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_rules'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-palette"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_rules.active_rules') }}</div>
                        <div class="quran-stat-value">{{ $stats['active_rules'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_rules.categories_count') }}</div>
                        <div class="quran-stat-value">{{ $stats['categories_count'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-folder"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_rules.total_segments') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_segments']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-puzzle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('tajweed-rules.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tajweed_rules.filter_by_category') }}</label>
                        <select name="category" class="quran-form-select">
                            <option value="">{{ __('tajweed_rules.all_categories') }}</option>
                            @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tajweed_rules.filter_by_status') }}</label>
                        <select name="status" class="quran-form-select">
                            <option value="">{{ __('tajweed_rules.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                {{ __('common.active') }}
                            </option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                {{ __('common.inactive') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('tajweed_rules.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('tajweed_rules.search_placeholder') }}" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rules Grid -->
    <div class="row g-4">
        @forelse($tajweedRules as $rule)
        <div class="col-md-6 col-lg-4">
            <div class="quran-card h-100">
                <div class="quran-card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        @if($rule->color_code)
                        <div style="width: 40px; height: 40px; border-radius: 10px; background-color: {{ $rule->color_code }}; 
                                    border: 2px solid var(--quran-border-light);"></div>
                        @else
                        <div class="quran-plan-icon" style="width: 40px; height: 40px;">
                            <i class="bi bi-palette"></i>
                        </div>
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $rule->name_ku ?? $rule->name }}</h6>
                            <div class="d-flex align-items-center gap-2">
                                @if($rule->name_ar)
                                <small class="text-success arabic-text" dir="rtl">{{ $rule->name_ar }}</small>
                                @endif
                                @if($rule->category)
                                <span class="quran-table-badge info">{{ $rule->category }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mb-3" style="font-family: 'Cairo';">{{ Str::limit($rule->description_ku ?? $rule->description, 100) }}</p>

                    @if($rule->example_text)
                    <div class="bg-light p-3 rounded-3 mb-3">
                        <small class="text-muted d-block mb-1">{{ __('tajweed_rules.example') }}:</small>
                        <div class="arabic-text" style="font-size: 18px;">{{ $rule->example_text }}</div>
                    </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="quran-table-badge {{ $rule->is_active ? 'success' : 'danger' }}">
                                {{ $rule->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                            <small class="text-muted ms-2">
                                {{ $rule->ayah_tajweed_segments_count }} {{ __('tajweed_rules.segments') }}
                            </small>
                        </div>
                        <div class="quran-table-actions">
                            <a href="{{ route('tajweed-rules.show', $rule) }}" 
                               class="quran-table-action-btn view" 
                               data-bs-toggle="tooltip" title="{{ __('common.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()?->role === 'admin')
                            <a href="{{ route('tajweed-rules.edit', $rule) }}" 
                               class="quran-table-action-btn edit" 
                               data-bs-toggle="tooltip" title="{{ __('common.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="quran-table-empty">
                <i class="bi bi-palette"></i>
                <h6>{{ __('tajweed_rules.no_rules_found') }}</h6>
                <p>{{ __('tajweed_rules.no_rules_message') }}</p>
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('tajweed-rules.create') }}" class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('tajweed_rules.actions.create_first') }}
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($tajweedRules->hasPages())
    <div class="mt-4">
        {{ $tajweedRules->links() }}
    </div>
    @endif
</div>
{{-- Import JSON Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="importModalLabel">Import JSON File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tajweed-rules.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="json_file" class="form-label">Select .json file to import</label>
                        <input type="file" class="form-control" id="json_file" name="file" accept=".json" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="quran-btn quran-btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Example JSON Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="exampleModalLabel">Example JSON Format</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>The JSON file must be an array of objects structured as shown below:</p>
                <div class="bg-dark text-light p-3 rounded-3" style="max-height: 400px; overflow-y: auto;">
                    <pre><code class="text-info">[
  {
    "name": "Ikhfa",
    "name_ku": "ئیخفا",
    "name_ar": "إخفاء",
    "category": "noon_sakinah",
    "color_code": "#FF5733",
    "description": "To hide the sound of Noon",
    "description_ku": "شاردنەوەی دەنگی نوون لە کاتی خوێندنەوەدا",
    "example_text": "مِنْ قَبْلُ",
    "priority": 1,
    "is_active": true
  }
]</code></pre>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection