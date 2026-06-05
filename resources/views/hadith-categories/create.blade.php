@extends('layouts.app')

@section('title', 'دروستکردنی هاوپۆلی فەرموودە')
@section('page-title', 'دروستکردنی هاوپۆلی فەرموودە')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hadith-categories.index') }}">هاوپۆلەکانی فەرموودە</a></li>
    <li class="breadcrumb-item active" aria-current="page">هاوپۆلی نوێ</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">دروستکردنی هاوپۆلی نوێی فەرموودە</h1>
            <div class="text-muted">هاوپۆلی نوێ زیاد بکە بۆ پۆلێنکردنی فەرموودەکان (ڕەوشت، بیروباوەڕ...).</div>
        </div>
        <a href="{{ route('hadith-categories.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            گەڕانەوە بۆ لیست
        </a>
    </div>

    <!-- Form Container -->
    <div class="quran-form-container">
        <form method="POST" action="{{ route('hadith-categories.store') }}">
            @csrf
            
            @include('hadith-categories._form')

            <!-- Form Actions -->
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    پاشەکەوتکردن
                </button>
                <a href="{{ route('hadith-categories.index') }}" class="quran-btn quran-btn-outline-secondary">
                    پاشگەزبوونەوە
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
