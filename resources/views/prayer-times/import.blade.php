@extends('layouts.app')

@section('title', 'Import Prayer Times Calendar')

@push('styles')
<style>
  .drop-zone {
    border: 2px dashed #93c5fd;
    border-radius: 12px;
    background: #eff6ff;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
    min-height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
  }
  .drop-zone:hover, .drop-zone.dragover {
    background: #dbeafe;
    border-color: #2563eb;
  }
  .drop-zone input[type="file"] { display: none; }
  #preview-table td, #preview-table th { font-size: 0.78rem; white-space: nowrap; }
  .step-badge {
    width: 28px; height: 28px; line-height: 28px;
    border-radius: 50%; background: #2563eb; color: #fff;
    text-align: center; font-size: 13px; font-weight: 700;
    display: inline-block; flex-shrink: 0;
  }
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:900px">

  {{-- Header --}}
  <div class="mb-4">
    <a href="{{ route('prayer-times.index') }}" class="text-decoration-none text-muted small">
      <i class="bi bi-arrow-left me-1"></i>Back to Prayer Times
    </a>
    <h1 class="h3 fw-bold mt-1" style="color:#2563eb;">
      <i class="bi bi-upload me-2"></i>Import Prayer Times Calendar
    </h1>
    <p class="text-muted small mb-0">
      Upload a CSV file to import prayer times for one or more cities. Existing rows are updated (upsert).
    </p>
  </div>

  {{-- Expected Format Info --}}
  <div class="alert alert-info border-0 shadow-sm mb-4">
    <h6 class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i>Expected CSV Format</h6>
    <code class="small">city,date,fajr,sunrise,dhuhr,asr,maghrib,isha</code><br>
    <small class="text-muted">Date format: <strong>D-Mon</strong> (e.g. <code>1-Jan</code>, <code>15-Dec</code>). Times as H:MM or HH:MM.</small>
  </div>

  {{-- Alerts --}}
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ── Step 1: Upload & Preview ──────────────────────────────────────────── --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
      <div class="d-flex align-items-center gap-2">
        <span class="step-badge">1</span>
        <span class="fw-semibold">Upload &amp; Preview</span>
      </div>
    </div>
    <div class="card-body p-4">
      <div class="row g-3 mb-3">
        {{-- Year Selector --}}
        <div class="col-md-4">
          <label for="import-year" class="form-label fw-semibold">Calendar Year <span class="text-danger">*</span></label>
          <select id="import-year" class="form-select">
            @php $currentYear = date('Y'); @endphp
            @for($y = $currentYear - 1; $y <= $currentYear + 3; $y++)
              <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
          <div class="form-text">Select the year this CSV represents.</div>
        </div>
      </div>

      {{-- Drop Zone --}}
      <div class="drop-zone mb-3" id="drop-zone" onclick="document.getElementById('csv-file').click()">
        <input type="file" id="csv-file" accept=".csv,.txt">
        <i class="bi bi-file-earmark-spreadsheet display-5 text-primary mb-2"></i>
        <p class="mb-0 fw-semibold text-primary">Click to choose CSV or drag &amp; drop here</p>
        <p class="text-muted small mb-0" id="file-name-display">Supported: .csv, .txt</p>
      </div>

      <button type="button" class="btn btn-primary" id="btn-preview" disabled>
        <i class="bi bi-eye me-1"></i>Preview Import
        <span class="spinner-border spinner-border-sm ms-2 d-none" id="preview-spinner"></span>
      </button>
    </div>
  </div>

  {{-- ── Step 2: Review & Commit ───────────────────────────────────────────── --}}
  <div class="card border-0 shadow-sm" id="preview-section" style="display:none!important">
    <div class="card-header bg-white border-bottom py-3">
      <div class="d-flex align-items-center gap-2">
        <span class="step-badge">2</span>
        <span class="fw-semibold">Review &amp; Commit</span>
      </div>
    </div>
    <div class="card-body p-4">

      {{-- Summary --}}
      <div id="preview-summary" class="mb-3"></div>

      {{-- Errors --}}
      <div id="parse-errors" class="mb-3" style="display:none"></div>

      {{-- Preview Table --}}
      <div class="table-responsive mb-4" id="preview-table-wrapper" style="max-height:350px;overflow-y:auto;">
        <table class="table table-sm table-bordered table-hover" id="preview-table">
          <thead class="table-dark sticky-top">
            <tr>
              <th>City</th><th>Date</th><th>Fajr</th><th>Sunrise</th>
              <th>Dhuhr</th><th>Asr</th><th>Maghrib</th><th>Isha</th>
            </tr>
          </thead>
          <tbody id="preview-tbody"></tbody>
        </table>
      </div>

      {{-- Commit Form --}}
      <form method="POST" action="{{ route('prayer-times.import.commit') }}" id="commit-form">
        @csrf
        <div class="d-flex gap-2 align-items-center">
          <button type="submit" class="btn btn-success" id="btn-commit">
            <i class="bi bi-database-check me-1"></i>Commit Import
          </button>
          <span class="text-muted small" id="commit-label"></span>
        </div>
      </form>
    </div>
  </div>

</div>

@push('scripts')
<script>
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('csv-file');
const btnPreview = document.getElementById('btn-preview');
const previewSpinner = document.getElementById('preview-spinner');
const previewSection = document.getElementById('preview-section');
const previewSummary = document.getElementById('preview-summary');
const parseErrors = document.getElementById('parse-errors');
const previewTbody = document.getElementById('preview-tbody');
const commitLabel = document.getElementById('commit-label');

// Drag and drop
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('dragover');
  fileInput.files = e.dataTransfer.files;
  onFileSelected();
});

fileInput.addEventListener('change', onFileSelected);

function onFileSelected() {
  if (fileInput.files.length > 0) {
    document.getElementById('file-name-display').textContent = '📄 ' + fileInput.files[0].name;
    btnPreview.disabled = false;
  }
}

btnPreview.addEventListener('click', async () => {
  btnPreview.disabled = true;
  previewSpinner.classList.remove('d-none');

  const formData = new FormData();
  formData.append('file', fileInput.files[0]);
  formData.append('year', document.getElementById('import-year').value);
  formData.append('_token', '{{ csrf_token() }}');

  try {
    const res = await fetch('{{ route("prayer-times.import.preview") }}', {
      method: 'POST',
      body: formData,
    });
    const json = await res.json();

    if (!json.success) {
      previewSummary.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
      previewSection.style.removeProperty('display');
      return;
    }

    // Summary
    previewSummary.innerHTML = `
      <div class="alert alert-success border-0">
        <strong><i class="bi bi-check-circle-fill me-1"></i>${json.total_rows.toLocaleString()} rows</strong> parsed for year <strong>${json.year}</strong>.
        ${json.parse_errors > 0 ? `<span class="text-danger ms-2">(${json.parse_errors} parse errors)</span>` : ''}
        <br><small class="text-muted">Showing first ${json.preview.length} rows below. Click Commit to save all rows.</small>
      </div>`;

    // Parse errors
    if (json.errors && json.errors.length > 0) {
      parseErrors.innerHTML = `
        <div class="alert alert-warning small">
          <strong>Parse Warnings (${json.errors.length}):</strong><br>
          ${json.errors.slice(0, 5).join('<br>')}
          ${json.errors.length > 5 ? `<br>...and ${json.errors.length - 5} more.` : ''}
        </div>`;
      parseErrors.style.removeProperty('display');
    }

    // Preview rows
    previewTbody.innerHTML = json.preview.map(row => `
      <tr>
        <td>${row.city_id}</td>
        <td class="font-monospace">${row.date}</td>
        <td class="font-monospace text-primary">${row.fajr}</td>
        <td class="font-monospace">${row.sunrise}</td>
        <td class="font-monospace">${row.dhuhr}</td>
        <td class="font-monospace">${row.asr}</td>
        <td class="font-monospace text-danger">${row.maghrib}</td>
        <td class="font-monospace text-info">${row.isha}</td>
      </tr>`).join('');

    commitLabel.textContent = `${json.total_rows.toLocaleString()} rows will be upserted.`;
    previewSection.style.removeProperty('display');

  } catch (e) {
    previewSummary.innerHTML = `<div class="alert alert-danger">Preview failed: ${e.message}</div>`;
    previewSection.style.removeProperty('display');
  } finally {
    btnPreview.disabled = false;
    previewSpinner.classList.add('d-none');
  }
});
</script>
@endpush

@endsection
