@extends('layouts.app')
@section('title', 'Registered Users List')
@section('page-title', 'Registered Users')
@section('page-subtitle', 'Search, filter, and audit user profiles')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.dashboard') }}">User Administration</a></li>
    <li class="breadcrumb-item active" aria-current="page">Registered Users</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📋 Registered Users</h1>
            <div class="text-muted small">Manage status, inspect details, and query user demographic segments</div>
        </div>
        <div>
            <a href="{{ route('admin.users.dashboard') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="userAdminTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.users.dashboard') }}">📊 Overview & Charts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.users.index') }}">📋 Registered Users</a>
        </li>
    </ul>

    {{-- Filter Card --}}
    <div class="quran-card p-4 mb-4 shadow-sm border-0">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Search Name/Email/Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Type search..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Pending Deletion</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ request('gender') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Country</label>
                <select name="country_id" class="form-select">
                    <option value="">All Countries</option>
                    @foreach($countries as $c)
                        @php
                            $enName = $c->translations->where('language_id', 1)->first()->value ?? 'Unknown';
                        @endphp
                        <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>{{ $enName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>

    {{-- Users List Table --}}
    <div class="quran-card p-0 shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Country</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    @if($u->avatar)
                                        <img src="{{ asset($u->avatar) }}" class="rounded-circle" width="36" height="36" alt="Avatar">
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $u->name }}</div>
                                        <span class="text-muted small">Age: {{ $u->age ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $u->username }}</code></td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @if($u->country)
                                    @php
                                        $cntName = $u->country->translations->where('language_id', 1)->first()->value ?? 'Unknown';
                                    @endphp
                                    {{ $cntName }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill">Admin</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1 rounded-pill">User</span>
                                @endif
                            </td>
                            <td>
                                @if($u->trashed())
                                    <span class="badge bg-danger text-white px-3 py-1 rounded-pill" data-bs-toggle="tooltip" title="Soft Deleted - Expires {{ $u->deleted_at->addDays(30)->format('Y-m-d') }}">Pending Deletion</span>
                                @elseif(!$u->status)
                                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">Suspended</span>
                                @else
                                    <span class="badge bg-success text-white px-3 py-1 rounded-pill">Active</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $u->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $u->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.users.show', $u->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View details, statistics, and security logs">
                                    <i class="bi bi-eye"></i> View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-3"></i>
                                No users matched your search criteria
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-top">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
