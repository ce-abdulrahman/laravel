@extends('layouts.app')

@section('title', 'دەستکاریکردنی هاوپۆلی ئەزکار')
@section('page-title', 'دەستکاریکردنی هاوپۆلی ئەزکار')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('adhkar-categories.index') }}">هاوپۆلەکانی ئەزکار</a></li>
    <li class="breadcrumb-item active" aria-current="page">دەستکاریکردن</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">دەستکاریکردنی هاوپۆلی ئەزکار</h1>
            <div class="text-muted">دەستکاری زانیارییەکانی ئەم هاوپۆلە بکە.</div>
        </div>
        <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            گەڕانەوە بۆ لیست
        </a>
    </div>

    <!-- Form Container -->
    <div class="quran-form-container">
        <form method="POST" action="{{ route('adhkar-categories.update', $category) }}">
            @csrf
            @method('PUT')
            
            @include('adhkar-categories._form')

            <!-- Form Actions -->
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    نوێکردنەوە
                </button>
                <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-secondary">
                    پاشگەزبوونەوە
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
