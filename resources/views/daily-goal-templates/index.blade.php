@extends('layouts.app')

@section('title', __('daily_goals.templates_title'))
@section('page-title', __('daily_goals.templates_title'))

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ __('sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('daily_goals.templates_title') }}</li>
        </ol>
    </nav>

    <!-- Page Title & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-1 text-primary fw-bold">{{ __('daily_goals.templates_title') }}</h1>
            <p class="text-muted mb-0">{{ __('daily_goals.templates_subtitle') }}</p>
        </div>
        <div>
            <a href="{{ route('daily-goal-templates.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg"></i>
                {{ __('daily_goals.create_template') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Templates Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light border-0">
                        <tr>
                            <th scope="col" class="border-0 rounded-start">{{ __('daily_goals.template_value') }}</th>
                            <th scope="col" class="border-0">Translations</th>
                            <th scope="col" class="border-0">{{ __('daily_goals.is_active') }}</th>
                            <th scope="col" class="border-0 text-end rounded-end">{{ __('daily_goals.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>
                                    <span class="fs-5 fw-bold text-primary">{{ $template->value }}</span>
                                    <span class="small text-muted d-block">dhikr/day</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($activeLanguages as $lang)
                                            <div class="d-flex align-items-start gap-2">
                                                <span class="badge bg-light text-dark border small" style="min-width: 32px; text-align: center;">
                                                    {{ strtoupper($lang->code) }}
                                                </span>
                                                <div>
                                                    <strong class="small text-dark">{{ $template->getTranslation('title', $lang->code) ?? 'N/A' }}</strong>
                                                    <span class="small text-muted d-block" style="font-size: 11px;">
                                                        {{ $template->getTranslation('description', $lang->code) ?? 'No description.' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded">
                                            {{ __('daily_goals.active') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded">
                                            {{ __('daily_goals.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('daily-goal-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form method="POST" action="{{ route('daily-goal-templates.destroy', $template) }}" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this template?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No templates found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
