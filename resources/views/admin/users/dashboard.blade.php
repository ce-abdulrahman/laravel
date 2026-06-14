@extends('layouts.app')
@section('title', 'User Administration Dashboard')
@section('page-title', 'User Administration')
@section('page-subtitle', 'Management, statistics, and auditing of user accounts')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">User Administration</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">👥 User Administration</h1>
            <div class="text-muted small">Analyze registered user activities, suspended profiles, and device analytics</div>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-people"></i> Manage Users List
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="userAdminTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.users.dashboard') }}">📊 Overview & Charts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.users.index') }}">📋 Registered Users</a>
        </li>
    </ul>

    {{-- Stats Cards Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="quran-stat-card quran-stat-primary shadow-sm border-0">
                <div class="quran-stat-content p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Accounts</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalUsers) }}</h3>
                        <span class="text-muted small">Registered to date</span>
                    </div>
                    <div class="fs-1 text-primary"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="quran-stat-card quran-stat-success shadow-sm border-0">
                <div class="quran-stat-content p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Active Accounts</h6>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($activeUsers) }}</h3>
                        <span class="text-muted small">Normal operations</span>
                    </div>
                    <div class="fs-1 text-success"><i class="bi bi-check-circle-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="quran-stat-card quran-stat-warning shadow-sm border-0">
                <div class="quran-stat-content p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Suspended Accounts</h6>
                        <h3 class="fw-bold mb-0 text-warning">{{ number_format($suspendedUsers) }}</h3>
                        <span class="text-muted small">Access blocked</span>
                    </div>
                    <div class="fs-1 text-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="quran-stat-card quran-stat-danger shadow-sm border-0">
                <div class="quran-stat-content p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Pending Deletion</h6>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($deletedUsers) }}</h3>
                        <span class="text-muted small">30-day recovery active</span>
                    </div>
                    <div class="fs-1 text-danger"><i class="bi bi-trash-fill"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail rows --}}
    <div class="row g-4">
        {{-- Demographics & Charts --}}
        <div class="col-lg-6">
            <div class="quran-card p-4 h-100 shadow-sm border-0">
                <h5 class="fw-bold text-dark mb-4"><i class="bi bi-pie-chart-fill text-primary me-2"></i> Gender Distribution</h5>
                <div style="max-height: 250px; position: relative;">
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="mt-4">
                    <ul class="list-group list-group-flush small">
                        @forelse($genderDistribution as $g)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent ps-0 pe-0">
                                <span class="text-capitalize">
                                    <i class="bi @if($g->gender === 'male') bi-gender-male text-info @elseif($g->gender === 'female') bi-gender-female text-danger @else bi-gender-ambiguous text-secondary @endif me-2"></i>
                                    {{ $g->gender }}
                                </span>
                                <span class="fw-bold text-dark">{{ number_format($g->count) }} users</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted bg-transparent ps-0 pe-0">No gender demographic data registered</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Top Countries --}}
        <div class="col-lg-6">
            <div class="quran-card p-4 h-100 shadow-sm border-0">
                <h5 class="fw-bold text-dark mb-4"><i class="bi bi-geo-alt-fill text-success me-2"></i> Top Countries By Users</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Country</th>
                                <th class="text-end">Registered Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCountries as $c)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $c->country_name }}</div>
                                    </td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($c->user_count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">No location data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Users --}}
        <div class="col-12">
            <div class="quran-card p-4 shadow-sm border-0">
                <h5 class="fw-bold text-dark mb-4"><i class="bi bi-clock-history text-info me-2"></i> Recent Registrations</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $ru)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($ru->avatar)
                                                <img src="{{ asset($ru->avatar) }}" class="rounded-circle" width="36" height="36" alt="Avatar">
                                            @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                                    {{ strtoupper(substr($ru->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="fw-bold text-dark">{{ $ru->name }}</div>
                                        </div>
                                    </td>
                                    <td><code>{{ $ru->username }}</code></td>
                                    <td>{{ $ru->email }}</td>
                                    <td>{{ $ru->created_at->diffForHumans() }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.users.show', $ru->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Profile
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No users registered yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('genderChart').getContext('2d');
        const data = @json($genderDistribution);
        
        const labels = data.map(item => item.gender.charAt(0).toUpperCase() + item.gender.slice(1));
        const counts = data.map(item => item.count);
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: ['#3b82f6', '#ec4899', '#9b5de5', '#f15bb5', '#fee440', '#00f5d4', '#00bbf9'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
