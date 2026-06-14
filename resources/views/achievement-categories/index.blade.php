@extends('layouts.app')
@section('title', __('achievements.titles.categories'))
@section('page-title', __('achievements.titles.categories'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">{{ __('achievements.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('achievements.titles.categories') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📂 {{ __('achievements.titles.categories') }}</h1>
            <div class="text-muted small">{{ __('achievements.hints.categories') }}</div>
        </div>
        <a href="{{ route('achievement-categories.create') }}" class="quran-btn quran-btn-primary">
            <i class="bi bi-plus-lg me-1"></i> {{ __('achievements.titles.category_create') }}
        </a>
    </div>

    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>{{ __('achievements.fields.category') }}</th>
                        <th class="text-center" style="width:100px;">{{ __('achievements.fields.sort_order') }}</th>
                        <th class="text-center" style="width:100px;">{{ __('achievements.table.status') }}</th>
                        <th class="text-end" style="width:130px;">{{ __('achievements.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="text-muted small">{{ $category->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size:1.75rem;">{{ $category->icon }}</span>
                                <span class="fw-semibold">{{ $category->name ?: '(وەرگێڕان نییە)' }}</span>
                            </div>
                        </td>
                        <td class="text-center text-muted">{{ $category->sort_order }}</td>
                        <td class="text-center">
                            @if($category->is_active)
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
                                <a href="{{ route('achievement-categories.edit', $category) }}"
                                   class="quran-table-action-btn edit" title="{{ __('achievements.actions.edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('achievement-categories.destroy', $category) }}" method="POST"
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
                        <td colspan="5">
                            <div class="quran-table-empty">
                                <i class="bi bi-folder2 d-block mb-2"></i>
                                <h6>{{ __('achievements.empty.categories') }}</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.showing') }}
                    <strong>{{ $categories->firstItem() }}</strong>
                    {{ __('common.to') }}
                    <strong>{{ $categories->lastItem() }}</strong>
                    {{ __('common.of') }}
                    <strong>{{ $categories->total() }}</strong>
                    {{ __('common.results') }}
                </div>
                <div class="quran-pagination">
                    {{ $categories->links() }}
                </div>
            </div>
        @elseif($categories->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.total') }}: <strong>{{ $categories->count() }}</strong>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
