@extends('layouts.app')

@section('title', 'Prayer Settings')
@section('page-title', 'Prayer Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Prayer Settings</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Prayer Settings</h1>
            <div class="text-muted">Manage calculation methods, normalized cities list, adhan triggers, and refresh cache settings.</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Settings & Cache Control -->
        <div class="col-lg-6">
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-gear me-2"></i>
                        General Settings
                    </h5>
                </div>
                <div class="quran-card-body">
                    <form method="POST" action="{{ route('admin.prayer-settings.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="quran-form-label" for="calculation_method">Calculation Method</label>
                            <select name="calculation_method" id="calculation_method" class="quran-form-select">
                                <option value="kurdistan" {{ $settings->calculation_method == 'kurdistan' ? 'selected' : '' }}>Kurdistan Region (Ministry of Awqaf)</option>
                                <option value="muslim_world_league" {{ $settings->calculation_method == 'muslim_world_league' ? 'selected' : '' }}>Muslim World League (MWL)</option>
                                <option value="egyptian" {{ $settings->calculation_method == 'egyptian' ? 'selected' : '' }}>Egyptian General Authority of Survey</option>
                                <option value="karachi" {{ $settings->calculation_method == 'karachi' ? 'selected' : '' }}>University of Islamic Sciences, Karachi</option>
                                <option value="umm_al_qura" {{ $settings->calculation_method == 'umm_al_qura' ? 'selected' : '' }}>Umm al-Qura University, Makkah</option>
                                <option value="gulf" {{ $settings->calculation_method == 'gulf' ? 'selected' : '' }}>Gulf Region</option>
                                <option value="moonsighting_committee" {{ $settings->calculation_method == 'moonsighting_committee' ? 'selected' : '' }}>Moonsighting Committee</option>
                                <option value="north_america" {{ $settings->calculation_method == 'north_america' ? 'selected' : '' }}>ISNA (North America)</option>
                                <option value="turkey" {{ $settings->calculation_method == 'turkey' ? 'selected' : '' }}>Diyanet (Turkey)</option>
                                <option value="singapore" {{ $settings->calculation_method == 'singapore' ? 'selected' : '' }}>MUIS (Singapore)</option>
                                <option value="tehran" {{ $settings->calculation_method == 'tehran' ? 'selected' : '' }}>University of Tehran</option>
                                <option value="shia" {{ $settings->calculation_method == 'shia' ? 'selected' : '' }}>Shia Ithna-Ashari</option>
                            </select>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="global_notifications_enabled" id="global_notifications_enabled" value="1" {{ $settings->global_notifications_enabled ? 'checked' : '' }}>
                            <label class="form-check-label quran-form-label mb-0 ms-2" for="global_notifications_enabled">
                                Enable Global Prayer Notifications
                            </label>
                        </div>

                        <h6 class="mt-4 mb-3 border-bottom pb-2">Adhan Configuration (JSON)</h6>
                        <div class="mb-3">
                            <label class="quran-form-label" for="adhan_settings_json">Adhan Settings (JSON Format)</label>
                            <textarea name="adhan_settings_raw" id="adhan_settings_json" rows="5" class="form-control font-monospace" style="background-color: #f8f9fa; font-size: 0.85rem;" placeholder='{"fajr": "adhan_fajr.mp3", "dhuhr": "adhan_std.mp3"}' disabled>{{ json_encode($settings->adhan_settings ?? ['fajr' => 'default_fajr.mp3', 'dhuhr' => 'default.mp3', 'asr' => 'default.mp3', 'maghrib' => 'default.mp3', 'isha' => 'default.mp3'], JSON_PRETTY_PRINT) }}</textarea>
                            <small class="text-muted d-block mt-1">Configured globally for calculations.</small>
                        </div>

                        <div class="quran-form-actions mt-4">
                            <button type="submit" class="quran-btn quran-btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cache Control Card -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-cpu me-2"></i>
                        Cache Refresh Control
                    </h5>
                </div>
                <div class="quran-card-body">
                    <p class="text-muted">Cached prayer data optimizes DB queries and mobile sync operations. Click below to clear all cached prayer snapshot data.</p>
                    <form method="POST" action="{{ route('admin.prayer-settings.clear-cache') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-trash me-1"></i>
                            Clear Cached Prayer Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- City Management -->
        <div class="col-lg-6">
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-geo-alt me-2"></i>
                        City Coordinates Management
                    </h5>
                </div>
                <div class="quran-card-body">
                    <!-- City List -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>City Name</th>
                                    <th>Lat</th>
                                    <th>Lng</th>
                                    <th>Timezone</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cities as $city)
                                <tr>
                                    <td><strong>{{ $city->name }}</strong></td>
                                    <td>{{ $city->lat }}</td>
                                    <td>{{ $city->lng }}</td>
                                    <td><span class="badge bg-secondary">{{ $city->timezone }}</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCityModal{{ $city->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.prayer-settings.destroy-city', $city) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this city?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit City Modal -->
                                <div class="modal fade" id="editCityModal{{ $city->id }}" tabindex="-1" aria-labelledby="editCityModalLabel{{ $city->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('admin.prayer-settings.update-city', $city) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editCityModalLabel{{ $city->id }}">Edit City: {{ $city->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="name{{ $city->id }}">City Name</label>
                                                        <input type="text" name="name" id="name{{ $city->id }}" class="form-control" value="{{ $city->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="lat{{ $city->id }}">Latitude</label>
                                                        <input type="number" step="any" name="lat" id="lat{{ $city->id }}" class="form-control" value="{{ $city->lat }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="lng{{ $city->id }}">Longitude</label>
                                                        <input type="number" step="any" name="lng" id="lng{{ $city->id }}" class="form-control" value="{{ $city->lng }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="timezone{{ $city->id }}">Timezone</label>
                                                        <input type="text" name="timezone" id="timezone{{ $city->id }}" class="form-control" value="{{ $city->timezone }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Add City Form -->
                    <h6 class="mt-4 mb-3 border-bottom pb-2">Add New City</h6>
                    <form method="POST" action="{{ route('admin.prayer-settings.store-city') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="quran-form-label" for="new_name">City Name</label>
                                <input type="text" name="name" id="new_name" class="quran-form-control" placeholder="E.g. Erbil" required>
                            </div>
                            <div class="col-md-6">
                                <label class="quran-form-label" for="new_timezone">Timezone</label>
                                <input type="text" name="timezone" id="new_timezone" class="quran-form-control" value="Asia/Baghdad" required>
                            </div>
                            <div class="col-md-6">
                                <label class="quran-form-label" for="new_lat">Latitude</label>
                                <input type="number" step="any" name="lat" id="new_lat" class="quran-form-control" placeholder="36.1912" required>
                            </div>
                            <div class="col-md-6">
                                <label class="quran-form-label" for="new_lng">Longitude</label>
                                <input type="number" step="any" name="lng" id="new_lng" class="quran-form-control" placeholder="44.0091" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-lg me-1"></i>
                                Add City
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
