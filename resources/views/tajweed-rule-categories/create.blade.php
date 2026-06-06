{{-- resources/views/tajweed-rule-categories/create.blade.php --}}
@extends('layouts.app')

@section('title', __('tajweed_categories.titles.create'))
@section('page-title', __('tajweed_categories.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-rule-categories.index') }}">{{ __('tajweed_categories.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('tajweed_categories.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-folder-plus me-2"></i>
                        {{ __('tajweed_categories.titles.create') }}
                    </h5>
                    <a href="{{ route('tajweed-rule-categories.index') }}" class="quran-btn quran-btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('tajweed_categories.actions.back') }}
                    </a>
                </div>
                <div class="quran-card-body">
                    <form action="{{ route('tajweed-rule-categories.store') }}" method="POST">
                        @csrf
                        @include('tajweed-rule-categories._form')
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('tajweed-rule-categories.index') }}" class="quran-btn quran-btn-outline-primary">
                                {{ __('tajweed_categories.actions.cancel') }}
                            </a>
                            <button type="submit" class="quran-btn quran-btn-primary">
                                <i class="bi bi-floppy me-1"></i>
                                {{ __('tajweed_categories.actions.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
