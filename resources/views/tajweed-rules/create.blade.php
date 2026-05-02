{{-- resources/views/tajweed-rules/create.blade.php --}}
@extends('layouts.app')

@section('title', __('tajweed_rules.titles.create'))
@section('page-title', __('tajweed_rules.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-rules.index') }}">{{ __('tajweed_rules.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('tajweed_rules.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tajweed_rules.titles.create') }}</h1>
            <div class="text-muted">{{ __('tajweed_rules.hints.create_new') }}</div>
        </div>
        <a href="{{ route('tajweed-rules.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('tajweed_rules.actions.back') }}
        </a>
    </div>

    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-plus-circle me-2"></i>
                {{ __('tajweed_rules.titles.form_create') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('tajweed-rules.store') }}">
                @csrf
                @include('tajweed-rules._form', ['tajweedRule' => new \App\Models\TajweedRule()])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.save') }}
                    </button>
                    <a href="{{ route('tajweed-rules.index') }}" class="quran-btn quran-btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection