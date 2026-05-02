{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.guest')

@section('title', __('auth.forgot_password'))

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-key"></i>
            </div>
            <h3 class="auth-title">{{ __('auth.forgot_password') }}</h3>
            <p class="auth-subtitle">{{ __('auth.forgot_password_hint') }}</p>
        </div>

        <!-- Session Status -->
        @if(session('status'))
        <div class="alert alert-success mb-4">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('status') }}
        </div>
        @endif

        <!-- Forgot Password Form -->
        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <!-- Email -->
            <div class="form-group mb-4">
                <label class="quran-form-label" for="email">
                    <i class="bi bi-envelope me-1"></i>
                    {{ __('auth.email') }}
                </label>
                <input type="email" name="email" id="email" 
                       class="quran-form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" 
                       placeholder="{{ __('auth.email_placeholder') }}"
                       required autofocus>
                @error('email')
                <div class="quran-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="quran-btn quran-btn-primary w-100 mb-4">
                <i class="bi bi-envelope-paper me-2"></i>
                {{ __('auth.send_reset_link') }}
            </button>

            <!-- Back to Login -->
            <div class="auth-footer">
                <a href="{{ route('login') }}" class="auth-link">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('auth.back_to_login') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection