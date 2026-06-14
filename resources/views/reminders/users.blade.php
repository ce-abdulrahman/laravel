@extends('layouts.app')
@section('title', __('reminders.titles.users'))
@section('page-title', __('reminders.titles.users'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reminders.index') }}">{{ __('reminders.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('reminders.titles.users') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">👥 {{ __('reminders.titles.users') }}</h1>
            <div class="text-muted small">{{ __('reminders.hints.users') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reminders.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-card-list"></i> {{ __('reminders.actions.back') }}
            </a>
            <a href="{{ route('reminders.analytics') }}" class="btn btn-outline-info d-flex align-items-center gap-2">
                <i class="bi bi-graph-up"></i> {{ __('reminders.actions.analytics') }}
            </a>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="quran-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reminders.users') }}" class="d-flex gap-2 flex-wrap">
                <div class="quran-table-search flex-grow-1" style="min-width: 250px;">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control border-0 bg-transparent py-2 shadow-none"
                           placeholder="{{ __('reminders.placeholders.search_user') }}">
                </div>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4">
                    <i class="bi bi-funnel"></i> {{ __('reminders.actions.filter') }}
                </button>
                <a href="{{ route('reminders.users') }}" class="btn btn-secondary d-flex align-items-center px-3" title="Reset">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped align-middle">
                <thead>
                    <tr>
                        <th>{{ __('reminders.table.user') }}</th>
                        <th>{{ __('reminders.table.reminders') }}</th>
                        <th class="text-center">{{ __('reminders.table.sent') }}</th>
                        <th class="text-center">{{ __('reminders.table.last_sent') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center border"
                                     style="width:40px;height:40px;min-width:40px;font-size:1rem;">
                                    {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $user->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($user->reminders as $reminder)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5"
                                          title="Scheduled at: {{ $reminder->scheduled_time }} ({{ $reminder->timezone }}) - {{ $reminder->frequency }}">
                                        {{ __('reminders.types.' . $reminder->reminder_type) ?? $reminder->reminder_type }}
                                        <span class="text-muted small">({{ $reminder->scheduled_time }})</span>
                                    </span>
                                @empty
                                    <span class="text-muted small">No active reminders</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary border px-3">
                                {{ number_format($user->logs_count) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $lastReminder = $user->reminders->sortByDesc('last_sent_at')->first();
                            @endphp
                            @if($lastReminder && $lastReminder->last_sent_at)
                                <div class="small fw-medium">{{ $lastReminder->last_sent_at->diffForHumans() }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $lastReminder->last_sent_at->toDateTimeString() }}
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="quran-table-empty py-5 text-center">
                                <i class="bi bi-people d-block fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted fw-semibold">{{ __('reminders.empty.users') }}</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.showing') ?? 'Showing' }}
                    <strong>{{ $users->firstItem() }}</strong>
                    {{ __('common.to') ?? 'to' }}
                    <strong>{{ $users->lastItem() }}</strong>
                    {{ __('common.of') ?? 'of' }}
                    <strong>{{ $users->total() }}</strong>
                    {{ __('common.results') ?? 'results' }}
                </div>
                <div class="quran-pagination">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        @elseif($users->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.total') ?? 'Total' }}: <strong>{{ $users->count() }}</strong>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
