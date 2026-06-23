@extends('layouts.app')

@section('title', 'Add Prayer Time Entry')

@section('content')
<div class="container py-4" style="max-width:700px">

  <div class="mb-4">
    <a href="{{ route('prayer-times.index') }}" class="text-decoration-none text-muted small">
      <i class="bi bi-arrow-left me-1"></i>Back to Prayer Times
    </a>
    <h1 class="h3 fw-bold mt-1" style="color:#2563eb;">
      <i class="bi bi-plus-circle-fill me-2"></i>Add Prayer Time Entry
    </h1>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <form method="POST" action="{{ route('prayer-times.store') }}">
        @csrf

        {{-- City --}}
        <div class="mb-3">
          <label for="city_id" class="form-label fw-semibold">City <span class="text-danger">*</span></label>
          <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror" required>
            <option value="">Select city...</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                {{ $city->name }}
              </option>
            @endforeach
          </select>
          @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Date --}}
        <div class="mb-3">
          <label for="date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
          <input type="date" name="date" id="date"
                 class="form-control @error('date') is-invalid @enderror"
                 value="{{ old('date') }}" required>
          @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Prayer Times Grid --}}
        <div class="mb-4">
          <p class="fw-semibold mb-2">Prayer Times <span class="text-muted small">(HH:MM format)</span></p>
          <div class="row g-3">
            @foreach(['fajr' => 'Fajr','sunrise' => 'Sunrise','dhuhr' => 'Dhuhr','asr' => 'Asr','maghrib' => 'Maghrib','isha' => 'Isha'] as $field => $label)
              <div class="col-6 col-md-4">
                <label for="{{ $field }}" class="form-label small fw-semibold">{{ $label }}</label>
                <input type="text" name="{{ $field }}" id="{{ $field }}"
                       class="form-control form-control-sm font-monospace @error($field) is-invalid @enderror"
                       placeholder="05:30"
                       value="{{ old($field) }}"
                       pattern="\d{1,2}:\d{2}" required>
                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            @endforeach
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i>Save Entry
          </button>
          <a href="{{ route('prayer-times.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection
