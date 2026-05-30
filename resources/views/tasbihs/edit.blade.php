@extends('layouts.app')

@section('title', 'دەستکاریکردنی تەسبیح')
@section('page-title', 'دەستکاریکردنی تەسبیح')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasbihs.index') }}">بەڕێوەبردنی تەسبیحەکان</a></li>
    <li class="breadcrumb-item active" aria-current="page">دەستکاریکردنی تەسبیح</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">دەستکاریکردنی تەسبیح</h1>
            <div class="text-muted">دەستکاری زانیارییەکانی تەسبیحی "{{ $tasbih->name }}" بکە.</div>
        </div>
        <a href="{{ route('tasbihs.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            گەڕانەوە بۆ لیست
        </a>
    </div>

    <!-- Form Container -->
    <div class="quran-form-container">
        <form method="POST" action="{{ route('tasbihs.update', $tasbih) }}">
            @csrf
            @method('PUT')
            
            @include('tasbihs._form')

            <!-- Form Actions -->
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    تازەکردنەوە
                </button>
                <a href="{{ route('tasbihs.index') }}" class="quran-btn quran-btn-outline-secondary">
                    پاشگەزبوونەوە
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
