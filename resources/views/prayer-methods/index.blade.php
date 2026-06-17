@extends('layouts.app')

@section('title', 'Calculation Methods')
@section('page-title', 'Calculation Methods')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.prayer-settings.index') }}">Prayer Settings</a></li>
    <li class="breadcrumb-item active">Calculation Methods</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Prayer Calculation Methods</h1>
            <div class="text-muted">Configure angles, rules, order, soft status, and the global default fallback method. All text is translated dynamically.</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Default Fallback Config -->
        <div class="col-lg-12">
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title mb-0">
                        <i class="bi bi-bookmark-star me-2 text-warning"></i>
                        Active Global Fallback Default
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted">The system fallback calculation method is currently set to:</span>
                            <h4 class="mt-2 text-primary font-monospace">{{ $settings->calculation_method }}</h4>
                        </div>
                        <div>
                            <span class="badge bg-success p-2">Global Default Fallback</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Methods Configuration List -->
        <div class="col-lg-12">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title mb-0">
                        <i class="bi bi-list-ol me-2"></i>
                        Available Methods
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Sort Order</th>
                                    <th>Key</th>
                                    <th>Config Angles / Rules</th>
                                    <th>Status</th>
                                    <th>Global Fallback</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($methods as $method)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark px-3 py-2 border"># {{ $method->sort_order }}</span>
                                    </td>
                                    <td>
                                        <div><strong>{{ t($method->translation_key_name) }}</strong></div>
                                        <span class="text-muted font-monospace" style="font-size: 0.8rem;">{{ $method->key }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            @if(isset($method->config['fajr_angle']))
                                                <span class="me-2">Fajr: <strong>{{ $method->config['fajr_angle'] }}°</strong></span>
                                            @endif
                                            @if(isset($method->config['isha_angle']))
                                                <span class="me-2">Isha: <strong>{{ $method->config['isha_angle'] }}°</strong></span>
                                            @endif
                                            @if(isset($method->config['rules']))
                                                @foreach((array)$method->config['rules'] as $ruleKey => $ruleVal)
                                                    <span class="badge bg-info text-dark me-1" style="font-size: 0.75rem;">
                                                        {{ $ruleKey }}: {{ is_bool($ruleVal) ? ($ruleVal ? 'true' : 'false') : $ruleVal }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($method->is_enabled)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($method->key === $settings->calculation_method)
                                            <span class="badge bg-primary">Global Default</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <!-- Edit config modal button -->
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMethodModal{{ $method->id }}">
                                            <i class="bi bi-pencil-fill me-1"></i> Edit
                                        </button>

                                        <!-- Toggle Status (Soft Disable instead of Delete) -->
                                        <form method="POST" action="{{ route('admin.prayer-methods.toggle-active', $method->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $method->is_enabled ? 'btn-outline-danger' : 'btn-outline-success' }} me-1" {{ $method->key === $settings->calculation_method ? 'disabled' : '' }}>
                                                @if($method->is_enabled)
                                                    <i class="bi bi-slash-circle me-1"></i> Disable
                                                @else
                                                    <i class="bi bi-check-circle me-1"></i> Enable
                                                @endif
                                            </button>
                                        </form>

                                        <!-- Set Default Fallback -->
                                        <form method="POST" action="{{ route('admin.prayer-methods.set-default', $method->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" {{ !$method->is_enabled || $method->key === $settings->calculation_method ? 'disabled' : '' }}>
                                                <i class="bi bi-bookmark-star me-1"></i> Set Fallback
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Config Modal -->
                                <div class="modal fade" id="editMethodModal{{ $method->id }}" tabindex="-1" aria-labelledby="editMethodModalLabel{{ $method->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('admin.prayer-methods.update', $method->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editMethodModalLabel{{ $method->id }}">Configure Method: {{ t($method->translation_key_name) }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Key ID</label>
                                                        <input type="text" class="form-control" value="{{ $method->key }}" disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="fajr_angle{{ $method->id }}">Fajr Angle</label>
                                                        <input type="number" step="0.1" name="fajr_angle" id="fajr_angle{{ $method->id }}" class="form-control" value="{{ $method->config['fajr_angle'] ?? '' }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="isha_angle{{ $method->id }}">Isha Angle</label>
                                                        <input type="number" step="0.1" name="isha_angle" id="isha_angle{{ $method->id }}" class="form-control" value="{{ $method->config['isha_angle'] ?? '' }}">
                                                    </div>

                                                    <!-- Conditional rules depending on keys -->
                                                    @if($method->key === 'umm_al_qura')
                                                    <div class="mb-3">
                                                        <label class="form-label" for="isha_delay_minutes{{ $method->id }}">Isha Delay Minutes</label>
                                                        <input type="number" name="isha_delay_minutes" id="isha_delay_minutes{{ $method->id }}" class="form-control" value="{{ $method->config['rules']['isha_delay_minutes'] ?? 90 }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="isha_delay_ramadan_minutes{{ $method->id }}">Isha Delay Ramadan Minutes</label>
                                                        <input type="number" name="isha_delay_ramadan_minutes" id="isha_delay_ramadan_minutes{{ $method->id }}" class="form-control" value="{{ $method->config['rules']['isha_delay_ramadan_minutes'] ?? 120 }}">
                                                    </div>
                                                    @endif

                                                    @if($method->key === 'turkey')
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" name="use_diyanet_offsets" id="use_diyanet_offsets{{ $method->id }}" value="1" {{ !empty($method->config['rules']['use_diyanet_offsets']) ? 'checked' : '' }}>
                                                        <label class="form-check-label ms-2" for="use_diyanet_offsets{{ $method->id }}">Use Diyanet Offsets</label>
                                                    </div>
                                                    @endif

                                                    @if($method->key === 'kurdistan')
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" name="local_offsets_enabled" id="local_offsets_enabled{{ $method->id }}" value="1" {{ !empty($method->config['rules']['local_offsets_enabled']) ? 'checked' : '' }}>
                                                        <label class="form-check-label ms-2" for="local_offsets_enabled{{ $method->id }}">Enable Local Offsets Adjustments</label>
                                                    </div>
                                                    @endif

                                                    <div class="mb-3">
                                                        <label class="form-label" for="sort_order{{ $method->id }}">Sort Order</label>
                                                        <input type="number" name="sort_order" id="sort_order{{ $method->id }}" class="form-control" value="{{ $method->sort_order }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save Configuration</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
