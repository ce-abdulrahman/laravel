@extends('layouts.app')
@section('title', __('reminders.titles.index'))
@section('page-title', __('reminders.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('reminders.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🔔 {{ __('reminders.titles.index') }}</h1>
            <div class="text-muted small">{{ __('reminders.hints.index') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reminders.analytics') }}" class="btn btn-outline-info d-flex align-items-center gap-2">
                <i class="bi bi-graph-up"></i> {{ __('reminders.actions.analytics') }}
            </a>
            <a href="{{ route('reminders.users') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="bi bi-people"></i> {{ __('reminders.actions.users') }}
            </a>
            <a href="{{ route('reminders.create') }}" class="btn btn-success d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> {{ __('reminders.actions.create') }}
            </a>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="quran-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reminders.index') }}" class="d-flex gap-2 flex-wrap">
                <div class="quran-table-search flex-grow-1" style="min-width: 250px;">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control border-0 bg-transparent py-2 shadow-none"
                           placeholder="{{ __('reminders.placeholders.search') }}">
                </div>
                <select name="type" class="quran-form-control" style="width:auto; min-width: 150px;">
                    <option value="">{{ __('reminders.fields.type') }}</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>
                            {{ __('reminders.types.' . $t) ?? $t }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4">
                    <i class="bi bi-funnel"></i> {{ __('reminders.actions.filter') }}
                </button>
                <a href="{{ route('reminders.index') }}" class="btn btn-secondary d-flex align-items-center px-3" title="Reset">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </form>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped align-middle">
                <thead>
                    <tr>
                        <th>{{ __('reminders.table.key') }}</th>
                        <th>{{ __('reminders.table.type') }}</th>
                        <th>{{ __('reminders.table.title') }}</th>
                        <th class="text-center">{{ __('reminders.table.priority') }}</th>
                        <th class="text-center">{{ __('reminders.table.version') }}</th>
                        <th class="text-center">{{ __('reminders.table.status') }}</th>
                        <th class="text-end" style="min-width: 220px;">{{ __('reminders.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td>
                            <div class="fw-semibold text-primary d-flex align-items-center gap-2">
                                <span class="fs-5">{{ $template->icon }}</span>
                                <span>{{ $template->key }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1.5">
                                {{ __('reminders.types.' . $template->type) ?? $template->type }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-medium">
                                {{ $template->getTranslation('title', app()->getLocale()) ?? '—' }}
                            </div>
                            <div class="text-muted small text-truncate" style="max-width: 300px;">
                                {{ $template->getTranslation('body', app()->getLocale()) ?? '—' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info rounded-pill px-3">
                                {{ $template->priority }} / 10
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-dark-subtle text-dark border px-2">
                                v{{ $template->version }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($template->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2">
                                    <i class="bi bi-patch-check me-1"></i>{{ __('reminders.status.active') }}
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2">
                                    <i class="bi bi-slash-circle me-1"></i>{{ __('reminders.status.inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1.5">
                                <button type="button" class="btn btn-sm btn-outline-secondary test-notification-btn"
                                        data-id="{{ $template->id }}"
                                        data-key="{{ $template->key }}"
                                        title="{{ __('reminders.actions.test') }}">
                                    <i class="bi bi-send-fill text-info"></i>
                                </button>
                                <form action="{{ route('reminders.duplicate', $template) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ __('reminders.actions.duplicate') }}">
                                        <i class="bi bi-copy text-primary"></i>
                                    </button>
                                </form>
                                <a href="{{ route('reminders.edit', $template) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('reminders.actions.edit') }}">
                                    <i class="bi bi-pencil-fill text-warning"></i>
                                </a>
                                <form action="{{ route('reminders.destroy', $template) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('{{ __('reminders.messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ __('reminders.actions.delete') }}">
                                        <i class="bi bi-trash3-fill text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="quran-table-empty py-5 text-center">
                                <i class="bi bi-bell-slash d-block fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted fw-semibold">{{ __('reminders.empty.templates') }}</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.showing') ?? 'Showing' }}
                    <strong>{{ $templates->firstItem() }}</strong>
                    {{ __('common.to') ?? 'to' }}
                    <strong>{{ $templates->lastItem() }}</strong>
                    {{ __('common.of') ?? 'of' }}
                    <strong>{{ $templates->total() }}</strong>
                    {{ __('common.results') ?? 'results' }}
                </div>
                <div class="quran-pagination">
                    {{ $templates->withQueryString()->links() }}
                </div>
            </div>
        @elseif($templates->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.total') ?? 'Total' }}: <strong>{{ $templates->count() }}</strong>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="testNotificationModal" tabindex="-1" aria-labelledby="testNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
            <div class="modal-header border-0 bg-info bg-gradient text-white p-4">
                <h5 class="modal-title fw-bold" id="testNotificationModalLabel">
                    <i class="bi bi-send-check me-2"></i>{{ __('reminders.actions.test') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-success border-0 small mb-4">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <span id="testModalMessage"></span>
                </div>
                <div class="card border shadow-none rounded-3 mb-4">
                    <div class="card-header bg-light border-bottom p-3">
                        <span class="fw-semibold text-secondary small">Notification Payload Preview</span>
                    </div>
                    <div class="card-body p-3 bg-dark text-white rounded-bottom-3">
                        <pre class="m-0 text-success small" style="font-family: monospace;" id="testModalPreview"></pre>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" data-bs-dismiss="modal">
                        {{ __('reminders.actions.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const testModalEl = document.getElementById('testNotificationModal');
    const testModal = new bootstrap.Modal(testModalEl);
    const testMessage = document.getElementById('testModalMessage');
    const testPreview = document.getElementById('testModalPreview');

    document.querySelectorAll('.test-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const templateId = this.dataset.id;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span>';
            this.disabled = true;

            fetch(`/reminders/${templateId}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ locale: '{{ app()->getLocale() }}' })
            })
            .then(res => res.json())
            .then(data => {
                this.innerHTML = originalHtml;
                this.disabled = false;
                if (data.success) {
                    testMessage.innerText = data.message;
                    testPreview.innerText = JSON.stringify(data.preview, null, 4);
                    testModal.show();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                this.innerHTML = originalHtml;
                this.disabled = false;
                console.error(err);
                alert('An error occurred while dispatching the test notification.');
            });
        });
    });
});
</script>
@endpush
