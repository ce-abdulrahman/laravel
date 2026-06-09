{{-- resources/views/languages/confirm_delete.blade.php --}}
@extends('layouts.app')

@section('title', 'Confirm Deletion')
@section('page-title', 'Confirm Deletion')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('languages.index') }}">Languages</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Confirm Deletion</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="card border-danger mx-auto mt-5" style="max-width: 600px;">
        <div class="card-header bg-danger text-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Warning: Translation Data Loss
            </h5>
        </div>
        <div class="card-body">
            <p class="card-text">
                The language <strong>{{ $language->name }} ({{ $language->code }})</strong> has active translations registered in the database.
            </p>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle me-2"></i>
                Deleting this language will permanently erase all associated Surah, Ayah, Tajweed, Adhkar, and Hadith translations matching this locale. This action is irreversible.
            </div>
            <p>Are you absolutely sure you want to proceed with deleting this language and all its translations?</p>

            <form method="POST" action="{{ route('languages.destroy', $language) }}" class="mt-4 d-flex justify-content-between">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm_delete" value="1">

                <button type="submit" class="quran-btn quran-btn-danger">
                    <i class="bi bi-trash-fill me-1"></i>
                    Yes, Delete Everything
                </button>

                <a href="{{ route('languages.index') }}" class="quran-btn quran-btn-outline-secondary">
                    No, Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
