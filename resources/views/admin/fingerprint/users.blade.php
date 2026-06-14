@extends('layouts.app')
@section('title', __('fingerprint.admin.users.title'))
@section('page-title', __('fingerprint.admin.users.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fingerprint.dashboard') }}">{{ __('fingerprint.admin.title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('fingerprint.admin.tabs.users') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🧑‍💼 {{ __('fingerprint.admin.users.title') }}</h1>
            <div class="text-muted small">{{ __('fingerprint.admin.users.subtitle') }}</div>
        </div>
        <form action="{{ route('admin.fingerprint.users') }}" method="GET" class="d-flex gap-2 col-12 col-lg-4 p-0">
            <div class="input-group">
                <input type="text" name="q" value="{{ $search }}" class="form-control border-0 shadow-sm" placeholder="{{ __('fingerprint.admin.users.search_placeholder') }}">
                <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-search"></i></button>
            </div>
            @if($search)
                <a href="{{ route('admin.fingerprint.users') }}" class="btn btn-outline-secondary d-flex align-items-center"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="fingerprintTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.fingerprint.dashboard') }}">{{ __('fingerprint.admin.tabs.dashboard') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.fingerprint.users') }}">{{ __('fingerprint.admin.tabs.users') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.fingerprint.settings') }}">{{ __('fingerprint.admin.tabs.settings') }}</a>
        </li>
    </ul>

    {{-- Users List --}}
    <div class="quran-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('fingerprint.admin.fields.user') }}</th>
                        <th class="text-end">{{ __('fingerprint.admin.fields.sessions') }}</th>
                        <th class="text-end">{{ __('fingerprint.admin.fields.taps') }}</th>
                        <th class="text-end">{{ __('fingerprint.admin.fields.avg_duration') }}</th>
                        <th class="text-end">{{ __('fingerprint.admin.fields.touch_rate') }}</th>
                        <th class="text-center">{{ __('fingerprint.admin.fields.preferred_mode') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                <span class="text-muted small">{{ $user->email }}</span>
                            </td>
                            <td class="text-end text-muted">{{ number_format($user->total_sessions) }}</td>
                            <td class="text-end text-primary fw-bold">{{ number_format($user->total_counts) }}</td>
                            <td class="text-end text-muted">{{ (int) round($user->avg_duration) }}s</td>
                            <td class="text-end text-muted">{{ number_format($user->avg_touch_rate, 2) }}/min</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark text-capitalize border">
                                    {{ str_replace('_', ' ', $user->preferred_mode ?? 'single_touch') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                {{ __('fingerprint.admin.users.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
