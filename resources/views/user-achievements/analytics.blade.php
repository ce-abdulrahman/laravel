@extends('layouts.app')
@section('title', 'ئەنالیزی دەستکەوتەکان')
@section('page-title', 'ئەنالیزی دەستکەوتەکان')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">دەستکەوتەکان</a></li>
    <li class="breadcrumb-item"><a href="{{ route('user-achievements.index') }}">بەکارهێنەران</a></li>
    <li class="breadcrumb-item active" aria-current="page">ئەنالیز</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📊 ئەنالیزی دەستکەوتەکان</h1>
            <div class="text-muted small">ئامارەکانی گشتی سیستەمی دەستکەوتەکان</div>
        </div>
        <a href="{{ route('user-achievements.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> گەڕانەوە
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#4f46e5,#6366f1);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">کۆی دەستکەوتە</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-trophy fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalAchievements) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#059669,#10b981);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">کۆی ئەنجامدان</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-award fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalUnlocks) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#ca8a04,#eab308);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">ناوەندی تەواوکردن</span>
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
                        <span class="small fw-semibold opacity-75">بەکارهێنەرانی سەرەوە</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-people fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ $topUsers->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Top Achievements --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>دەستکەوتەی زۆرتر ئەنجامدراو</h6>
                </div>
                <div class="card-body p-4">
                    @forelse($topAchievements as $ach)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span style="font-size:1.5rem;min-width:36px;text-align:center;">{{ $ach->icon }}</span>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold">{{ $ach->name ?: $ach->key }}</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ number_format($ach->user_achievements_count) }}
                                </span>
                            </div>
                            @if($topAchievements->first()->user_achievements_count > 0)
                            @php $pct = round(($ach->user_achievements_count / $topAchievements->first()->user_achievements_count) * 100); @endphp
                            <div class="progress" style="height:5px;">
                                <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center small">داتا نییە</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Users --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary me-2"></i>بەکارهێنەرانی سەرەوە</h6>
                </div>
                <div class="card-body p-4">
                    @forelse($topUsers as $i => $topUser)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="fw-bold text-muted" style="min-width:24px;">{{ $i + 1 }}</div>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center"
                             style="width:38px;height:38px;min-width:38px;font-size:.85rem;">
                            {{ strtoupper(substr($topUser->user?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span class="small fw-semibold">{{ $topUser->user?->name ?? '—' }}</span>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-star-fill me-1"></i>{{ $topUser->total }}
                                </span>
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $topUser->user?->email }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center small">داتا نییە</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Per Category --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-folder2 text-success me-2"></i>ئەنجامدان بەپێی کەتێگۆری</h6>
                </div>
                <div class="card-body p-4">
                    <div class="quran-table-container">
                        <table class="quran-table">
                            <thead>
                                <tr>
                                    <th>کەتێگۆری</th>
                                    <th class="text-center">کۆی دەستکەوتە</th>
                                    <th class="text-center">کۆی ئەنجامدان</th>
                                    <th>پێشکەوتن</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryStats as $stat)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $stat->icon }} {{ $stat->name }}</span>
                                    </td>
                                    <td class="text-center">{{ $stat->achievements_count }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            {{ number_format($stat->total_unlocks) }}
                                        </span>
                                    </td>
                                    <td style="min-width:150px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;">
                                                <div class="progress-bar bg-success"
                                                     style="width:{{ $categoryStats->max('total_unlocks') > 0 ? round(($stat->total_unlocks / $categoryStats->max('total_unlocks')) * 100) : 0 }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
