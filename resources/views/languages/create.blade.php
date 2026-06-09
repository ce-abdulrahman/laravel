{{-- resources/views/languages/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Language')
@section('page-title', 'Add Language')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('languages.index') }}">Languages</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Add Language</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Add Language</h1>
            <div class="text-muted">Register a new system language to support dynamic translations.</div>
        </div>
        <a href="{{ route('languages.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        @if (session('error'))
            <div class="alert alert-danger mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('languages.store') }}">
            @csrf

            @include('languages._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Save
                </button>
                <a href="{{ route('languages.index') }}" class="quran-btn quran-btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
