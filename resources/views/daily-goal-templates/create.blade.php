@extends('layouts.app')

@section('title', __('daily_goals.create_template'))
@section('page-title', __('daily_goals.create_template'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('daily-goal-templates.index') }}">{{ __('daily_goals.templates_title') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('daily_goals.create_template') }}</li>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-primary fw-bold">{{ __('daily_goals.create_template') }}</h1>
            <div class="text-muted">Define a new daily dhikr goal count suggestion for users.</div>
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
            <form method="POST" action="{{ route('daily-goal-templates.store') }}">
                @csrf

                @include('daily-goal-templates._form')

                {{-- Actions --}}
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i>
                        {{ __('daily_goals.save_template') }}
                    </button>
                    <a href="{{ route('daily-goal-templates.index') }}" class="btn btn-outline-secondary">
                        {{ __('daily_goals.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
