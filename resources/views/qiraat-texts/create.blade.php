{{-- resources/views/qiraat-texts/create.blade.php --}}
@extends('layouts.app')

@section('title', __('qiraat_texts.titles.create'))
@section('page-title', __('qiraat_texts.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('qiraat-texts.index') }}">{{ __('qiraat_texts.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('qiraat_texts.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('qiraat_texts.titles.create') }}</h1>
            <div class="text-muted">{{ __('qiraat_texts.hints.create_new') }}</div>
        </div>
        <a href="{{ route('qiraat-texts.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('qiraat_texts.actions.back') }}
        </a>
    </div>

    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-plus-circle me-2"></i>
                {{ __('qiraat_texts.titles.form_create') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('qiraat-texts.store') }}">
                @csrf
                @include('qiraat-texts._form', ['qiraatText' => new \App\Models\QiraatText()])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.save') }}
                    </button>
                    <a href="{{ route('qiraat-texts.index') }}" class="quran-btn quran-btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection