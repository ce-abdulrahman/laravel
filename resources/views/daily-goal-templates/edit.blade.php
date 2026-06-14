@extends('layouts.app')

@section('title', __('daily_goals.edit_template'))
@section('page-title', __('daily_goals.edit_template'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('daily-goal-templates.index') }}">{{ __('daily_goals.templates_title') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('daily_goals.edit_template') }}</li>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-primary fw-bold">{{ __('daily_goals.edit_template') }}</h1>
            <div class="text-muted">Edit daily dhikr count target and translations.</div>
        </div>
        <div>
            <a href="{{ route('daily-goal-templates.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                {{ __('daily_goals.back_to_list') }}
            </a>
        </div>
    </div>

    {{-- Form Container --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('daily-goal-templates.update', $template) }}">
                @csrf
                @method('PUT')

                @include('daily-goal-templates._form')

                {{-- Actions --}}
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i>
                        {{ __('daily_goals.save_changes') }}
                    </button>
                    <a href="{{ route('daily-goal-templates.index') }}" class="btn btn-outline-secondary">
                        {{ __('daily_goals.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="card border-danger border-opacity-25 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-danger bg-opacity-10 text-danger border-0 py-3 rounded-top-4">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Danger Zone
            </h5>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-3">Deleting this template will remove it from the default recommendations. This action cannot be undone.</p>
            <form method="POST" action="{{ route('daily-goal-templates.destroy', $template) }}"
                  onsubmit="return confirm('Are you sure you want to delete this daily goal template?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i>
                    Delete Template
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
