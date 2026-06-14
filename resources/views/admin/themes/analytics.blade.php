@extends('layouts.app')

@section('title', 'Theme Analytics')
@section('page-title', 'Theme Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.themes.dashboard') }}">Themes Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Theme Analytics</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Theme Analytics</h1>
            <div class="text-muted">In-depth telemetry of user theme updates, popularity, and events.</div>
        </div>
        <a href="{{ route('admin.themes.dashboard') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    {{-- Adoption Rates Card --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="card-title mb-4"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Current Theme Adoption Rate</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Theme Key</th>
                                <th class="text-end">Active Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adoptionRates as $rate)
                                <tr>
                                    <td class="fw-semibold">{{ ucwords(str_replace('_', ' ', $rate->theme_key)) }}</td>
                                    <td class="text-end fw-bold text-primary">{{ $rate->active_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">No active user data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="card-title mb-4"><i class="bi bi-activity text-success me-2"></i> Theme Telemetry Events</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Event Type</th>
                                <th class="text-end">Log Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($eventDistribution as $dist)
                                <tr>
                                    <td class="fw-semibold text-capitalize">{{ $dist->event_type }}</td>
                                    <td class="text-end fw-bold text-success">{{ $dist->count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">No event log records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Downloads History timeline --}}
    <div class="card border-0 shadow-sm p-4">
        <h5 class="card-title mb-4"><i class="bi bi-calendar-range text-info me-2"></i> Downloads (Last 30 Days)</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Downloads Logged</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($downloadsTimeline as $timeline)
                        <tr>
                            <td>{{ $timeline->date }}</td>
                            <td class="text-end fw-bold text-info">{{ $timeline->count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">No downloads logged in the last 30 days.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
