@extends('layouts.app')
@section('title', __('achievements.titles.index'))
@section('page-title', __('achievements.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('achievements.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">

    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🏆 {{ __('achievements.titles.index') }}</h1>
            <div class="text-muted small">{{ __('achievements.hints.index') }}</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('user-achievements.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-people"></i>
                <span>{{ __('achievements.actions.view_users') }}</span>
            </a>
            <a href="{{ route('achievement-categories.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-folder2"></i>
                <span>{{ __('achievements.actions.view_cats') }}</span>
            </a>
            <a href="{{ route('achievements.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('achievements.actions.create') }}
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#4f46e5,#6366f1);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.total') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-trophy fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ $achievements->total() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#059669,#10b981);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.active') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-check-circle fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ $achievements->where('is_active', true)->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#7c3aed,#8b5cf6);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.hidden') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-eye-slash fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ $achievements->where('is_hidden', true)->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#ca8a04,#eab308);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.categories') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-folder fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ $categories->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Table --}}
    <div class="quran-card">
        {{-- Toolbar --}}
        <div class="quran-table-toolbar">
            <form method="GET" action="{{ route('achievements.index') }}" class="d-flex gap-2 flex-wrap">
                <div class="quran-table-search">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="{{ __('achievements.placeholders.search') }}">
                </div>
                <select name="category" class="quran-form-control" style="width:auto;">
                    <option value="">{{ __('common.filter') }}: {{ __('achievements.fields.category') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->icon }} {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="quran-form-control" style="width:auto;">
                    <option value="">{{ __('common.status') }}</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('achievements.status.active') }}</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('achievements.status.inactive') }}</option>
                </select>
                <button type="submit" class="quran-table-filter-btn">
                    <i class="bi bi-funnel"></i> {{ __('common.filter') }}
                </button>
                <a href="{{ route('achievements.index') }}" class="quran-table-filter-btn" title="{{ __('common.refresh') }}">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </form>
        </div>

        {{-- Table --}}
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th style="width:60px;">{{ __('achievements.table.number') }}</th>
                        <th>{{ __('achievements.table.achievement') }}</th>
                        <th>{{ __('achievements.table.category') }}</th>
                        <th class="text-center">{{ __('achievements.table.condition') }}</th>
                        <th class="text-center">{{ __('achievements.table.target') }}</th>
                        <th class="text-center">{{ __('achievements.table.reward') }}</th>
                        <th class="text-center">{{ __('achievements.table.status') }}</th>
                        <th class="text-end" style="width:130px;">{{ __('achievements.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($achievements as $achievement)
                    <tr>
                        <td class="text-muted small">{{ $achievement->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size:1.5rem;">{{ $achievement->icon }}</span>
                                <div>
                                    <div class="fw-semibold">{{ $achievement->name ?: '('.$achievement->key.')' }}</div>
                                    <div class="text-muted small font-monospace">{{ $achievement->key }}</div>
                                    @if($achievement->is_hidden)
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">
                                            <i class="bi bi-eye-slash me-1"></i>{{ __('achievements.status.hidden') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($achievement->category)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $achievement->category->icon }} {{ $achievement->category->name }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace small"
                                  title="{{ $conditionTypes[$achievement->condition_type] ?? $achievement->condition_type }}">
                                {{ $achievement->condition_type }}
                            </span>
                        </td>
                        <td class="text-center fw-semibold">{{ number_format($achievement->condition_value) }}</td>
                        <td class="text-center">
                            <span class="text-warning fw-bold">
                                <i class="bi bi-star-fill me-1"></i>{{ number_format($achievement->reward_points) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($achievement->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('achievements.status.active') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('achievements.status.inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('achievements.edit', $achievement) }}"
                                   class="quran-table-action-btn edit" title="{{ __('achievements.actions.edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('achievements.destroy', $achievement) }}" method="POST"
                                      onsubmit="return confirm('{{ __('achievements.messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="quran-table-action-btn delete" title="{{ __('achievements.actions.delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="quran-table-empty">
                                <i class="bi bi-trophy d-block mb-2"></i>
                                <h6>{{ __('achievements.empty.achievements') }}</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($achievements->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.showing') }}
                    <strong>{{ $achievements->firstItem() }}</strong>
                    {{ __('common.to') }}
                    <strong>{{ $achievements->lastItem() }}</strong>
                    {{ __('common.of') }}
                    <strong>{{ $achievements->total() }}</strong>
                    {{ __('common.results') }}
                </div>
                <div class="quran-pagination">
                    {{ $achievements->withQueryString()->links() }}
                </div>
            </div>
        @elseif($achievements->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.total') }}: <strong>{{ $achievements->count() }}</strong>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
