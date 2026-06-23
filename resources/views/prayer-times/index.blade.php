@extends('layouts.app')

@section('title', 'Prayer Times Calendar')

@section('content')
<div class="container-fluid py-4">

  {{-- ── Header ──────────────────────────────────────────────────────────── --}}
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h1 class="h3 fw-bold mb-0" style="color:#2563eb;">
        <i class="bi bi-moon-stars-fill me-2"></i>Prayer Times Calendar
      </h1>
      <p class="text-muted small mb-0">Manage official prayer times per city &amp; date</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route('prayer-times.import') }}" class="btn btn-warning">
        <i class="bi bi-upload me-1"></i>Import Calendar
      </a>
      <a href="{{ route('prayer-times.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add Entry
      </a>
    </div>
  </div>

  {{-- ── Stats Cards ──────────────────────────────────────────────────────── --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm text-center py-3">
        <div class="display-6 fw-bold text-primary">{{ number_format($stats['total']) }}</div>
        <div class="text-muted small">Total Entries</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm text-center py-3">
        <div class="display-6 fw-bold text-success">{{ $stats['cities'] }}</div>
        <div class="text-muted small">Cities</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm text-center py-3">
        <div class="display-6 fw-bold text-warning">{{ number_format($stats['imported']) }}</div>
        <div class="text-muted small">Imported</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm text-center py-3">
        <div class="display-6 fw-bold text-info">{{ number_format($stats['manual']) }}</div>
        <div class="text-muted small">Manual</div>
      </div>
    </div>
  </div>

  {{-- ── Alerts ───────────────────────────────────────────────────────────── --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('prayer-times.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">City</label>
          <select name="city_id" class="form-select form-select-sm">
            <option value="">All Cities</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" {{ $cityId == $city->id ? 'selected' : '' }}>
                {{ $city->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Year</label>
          <select name="year" class="form-select form-select-sm">
            <option value="">All Years</option>
            @foreach($years as $y)
              <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">From</label>
          <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">To</label>
          <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
            <i class="bi bi-funnel me-1"></i>Filter
          </button>
          <a href="{{ route('prayer-times.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Export Buttons ───────────────────────────────────────────────────── --}}
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('prayer-times.export.csv', request()->only(['city_id','year','date_from','date_to'])) }}"
       class="btn btn-outline-success btn-sm">
      <i class="bi bi-filetype-csv me-1"></i>Export CSV
    </a>
    <a href="{{ route('prayer-times.export.json', request()->only(['city_id','year','date_from','date_to'])) }}"
       class="btn btn-outline-info btn-sm">
      <i class="bi bi-filetype-json me-1"></i>Export JSON
    </a>
  </div>

  {{-- ── Data Table ───────────────────────────────────────────────────────── --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      @if($prayerTimes->count() === 0)
        <div class="text-center py-5 text-muted">
          <i class="bi bi-moon-stars display-4 d-block mb-3"></i>
          <p class="mb-0">No prayer times found. <a href="{{ route('prayer-times.import') }}">Import a calendar</a> to get started.</p>
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-hover table-sm align-middle mb-0" id="prayer-times-table">
            <thead class="table-dark">
              <tr>
                <th>City</th>
                <th>Date</th>
                <th class="text-center"><i class="bi bi-sunrise me-1"></i>Fajr</th>
                <th class="text-center">Sunrise</th>
                <th class="text-center">Dhuhr</th>
                <th class="text-center">Asr</th>
                <th class="text-center">Maghrib</th>
                <th class="text-center">Isha</th>
                <th class="text-center">Source</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($prayerTimes as $pt)
                <tr>
                  <td class="fw-semibold">{{ $pt->city->name ?? '—' }}</td>
                  <td class="font-monospace small">{{ $pt->date?->format('Y-m-d') }}</td>
                  <td class="text-center font-monospace text-primary fw-semibold">{{ $pt->fajr }}</td>
                  <td class="text-center font-monospace text-warning">{{ $pt->sunrise }}</td>
                  <td class="text-center font-monospace">{{ $pt->dhuhr }}</td>
                  <td class="text-center font-monospace">{{ $pt->asr }}</td>
                  <td class="text-center font-monospace text-danger">{{ $pt->maghrib }}</td>
                  <td class="text-center font-monospace text-info">{{ $pt->isha }}</td>
                  <td class="text-center">
                    <span class="badge bg-{{ $pt->source === 'import' ? 'success' : ($pt->source === 'manual' ? 'warning' : 'secondary') }}">
                      {{ $pt->source }}
                    </span>
                  </td>
                  <td class="text-center">
                    <a href="{{ route('prayer-times.edit', $pt) }}" class="btn btn-xs btn-outline-primary py-0 px-2">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('prayer-times.destroy', $pt) }}"
                          class="d-inline"
                          onsubmit="return confirm('Delete this entry?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between align-items-center px-3 py-3">
          <p class="text-muted small mb-0">
            Showing {{ $prayerTimes->firstItem() }}–{{ $prayerTimes->lastItem() }}
            of {{ number_format($prayerTimes->total()) }} entries
          </p>
          {{ $prayerTimes->links() }}
        </div>
      @endif
    </div>
  </div>

</div>
@endsection
