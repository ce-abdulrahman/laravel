{{-- resources/views/auth/verify-email.blade.php --}}
@extends('layouts.guest')

@section('title', __('auth.verify_email'))

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-envelope-check"></i>
            </div>
            <h3 class="auth-title">{{ __('auth.verify_email') }}</h3>
            <p class="auth-subtitle">{{ __('auth.verify_email_hint') }}</p>
        </div>

        <!-- Status Message -->
        @if(session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">
            <i class="bi bi-check-circle me-2"></i>
            {{ __('auth.verification_link_sent') }}
        </div>
        @endif

        <div class="auth-content text-center">
            <p class="mb-4">{{ __('auth.verify_email_message') }}</p>

            <div class="d-grid gap-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="quran-btn quran-btn-primary w-100">
                        <i class="bi bi-envelope me-2"></i>
                        {{ __('auth.resend_verification') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="quran-btn quran-btn-outline-primary w-100">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        {{ __('auth.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection