@extends('layouts.app')
@section('title', __('achievements.titles.user_achievements'))
@section('page-title', __('achievements.titles.user_achievements'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">{{ __('achievements.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('achievements.titles.user_achievements') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🎖️ {{ __('achievements.titles.user_achievements') }}</h1>
            <div class="text-muted small">{{ __('achievements.hints.user_achievements') }}</div>
        </div>
        <a href="{{ route('user-achievements.analytics') }}" class="btn btn-outline-info d-flex align-items-center gap-2">
            <i class="bi bi-graph-up"></i> {{ __('achievements.actions.view_analytics') }}
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#4f46e5,#6366f1);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.total_unlocks') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-trophy fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalUnlocks) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#059669,#10b981);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.active_users') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-people fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($activeUsers) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#ca8a04,#eab308);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.avg_completion') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-percent fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ $avgCompletion }}%</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#7c3aed,#8b5cf6);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('achievements.stats.today_unlocks') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-calendar-day fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($todayUnlocks) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="quran-card">
        <div class="quran-table-toolbar">
            <form method="GET" action="{{ route('user-achievements.index') }}" class="d-flex gap-2 flex-wrap">
                <div class="quran-table-search">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="{{ __('achievements.placeholders.search_user') }}">
                </div>
                <select name="achievement_id" class="quran-form-control" style="width:auto;">
                    <option value="">{{ __('achievements.titles.achievements') ?? __('achievements.titles.index') }}</option>
                    @foreach($achievements as $ach)
                        <option value="{{ $ach->id }}" {{ request('achievement_id') == $ach->id ? 'selected' : '' }}>
                            {{ $ach->icon }} {{ $ach->name ?: $ach->key }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="quran-table-filter-btn">
                    <i class="bi bi-funnel"></i> {{ __('common.filter') }}
                </button>
                <a href="{{ route('user-achievements.index') }}" class="quran-table-filter-btn">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </form>
        </div>

        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>{{ __('achievements.table.user') }}</th>
                        <th>{{ __('achievements.table.achievement') }}</th>
                        <th class="text-center">{{ __('achievements.table.progress') }}</th>
                        <th class="text-center">{{ __('achievements.table.status') }}</th>
                        <th class="text-center">{{ __('achievements.table.unlocked_at') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('achievements.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userAchievements as $ua)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center"
                                     style="width:36px;height:36px;min-width:36px;font-size:.85rem;">
                                    {{ strtoupper(substr($ua->user?->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $ua->user?->name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">{{ $ua->user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size:1.25rem;">{{ $ua->achievement?->icon }}</span>
                                <div>
                                    <div class="small fw-semibold">{{ $ua->achievement?->name ?: $ua->achievement?->key }}</div>
                                    @if($ua->achievement?->category)
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.7rem;">
                                            {{ $ua->achievement->category->icon }} {{ $ua->achievement->category->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($ua->achievement)
                            @php $pct = min(100, round(($ua->progress_value / max(1,$ua->achievement->condition_value)) * 100)); @endphp
                            <div class="d-flex align-items-center gap-2" style="min-width:100px;">
                                <div class="progress flex-grow-1" style="height:6px;">
                                    <div class="progress-bar {{ $ua->is_completed ? 'bg-success' : 'bg-primary' }}"
                                         style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="small text-muted" style="min-width:30px;">{{ $pct }}%</span>
                            </div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($ua->is_completed)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('achievements.status.completed') }}
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-hourglass me-1"></i>{{ __('achievements.status.in_progress') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center text-muted small">
                            {{ $ua->completed_at ? $ua->completed_at->format('Y-m-d') : '—' }}
                        </td>
                        <td class="text-end">
                            <form action="{{ route('user-achievements.revoke', $ua) }}" method="POST"
                                  onsubmit="return confirm('{{ __('achievements.messages.confirm_revoke') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="quran-table-action-btn delete" title="{{ __('achievements.actions.revoke') }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="quran-table-empty">
                                <i class="bi bi-trophy d-block mb-2"></i>
                                <h6>{{ __('achievements.empty.users') }}</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($userAchievements->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.showing') }}
                    <strong>{{ $userAchievements->firstItem() }}</strong>
                    {{ __('common.to') }}
                    <strong>{{ $userAchievements->lastItem() }}</strong>
                    {{ __('common.of') }}
                    <strong>{{ $userAchievements->total() }}</strong>
                    {{ __('common.results') }}
                </div>
                <div class="quran-pagination">
                    {{ $userAchievements->withQueryString()->links() }}
                </div>
            </div>
        @elseif($userAchievements->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.total') }}: <strong>{{ $userAchievements->count() }}</strong>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
