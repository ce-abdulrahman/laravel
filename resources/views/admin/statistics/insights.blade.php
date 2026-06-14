@extends('layouts.app')
@section('title', __('statistics.insights'))
@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 fw-bold mb-0">
            <i class="bi bi-lightbulb-fill text-warning me-2"></i>{{ __('statistics.insights') }}
        </h1>
        <a href="{{ route('admin.statistics.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>{{ __('statistics.back') }}
        </a>
    </div>

    {{-- By Type --}}
    <div class="row g-3 mb-4">
        @foreach($insightsByType as $insight)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-4 mb-1">💡</div>
                <div class="fw-bold fs-5">{{ number_format($insight->count) }}</div>
                <div class="text-muted small text-capitalize">{{ str_replace('_', ' ', $insight->insight_type) }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Recent Insights --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h6 class="fw-semibold mb-0">{{ __('statistics.recent_insights') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">{{ __('statistics.user') }}</th>
                            <th>{{ __('statistics.insight_type') }}</th>
                            <th>{{ __('statistics.insight_content') }}</th>
                            <th>{{ __('statistics.generated_at') }}</th>
                            <th>{{ __('statistics.expires_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentInsights as $insight)
                        <tr>
                            <td class="ps-3 small">{{ $insight->user?->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary text-capitalize">{{ $insight->insight_type }}</span></td>
                            <td class="small">{{ $insight->insight_data['fallback'] ?? '—' }}</td>
                            <td class="text-muted small">{{ $insight->generated_at->diffForHumans() }}</td>
                            <td class="text-muted small">{{ $insight->expires_at?->diffForHumans() ?? '∞' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
