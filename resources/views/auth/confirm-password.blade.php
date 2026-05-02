{{-- resources/views/auth/confirm-password.blade.php --}}
@extends('layouts.guest')

@section('title', __('auth.confirm_password'))

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <h3 class="auth-title">{{ __('auth.confirm_password') }}</h3>
            <p class="auth-subtitle">{{ __('auth.confirm_password_hint') }}</p>
        </div>

        <!-- Confirm Password Form -->
        <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
            @csrf

            <!-- Password -->
            <div class="form-group mb-4">
                <label class="quran-form-label" for="password">
                    <i class="bi bi-lock me-1"></i>
                    {{ __('auth.password') }}
                </label>
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="password" 
                           class="quran-form-control @error('password') is-invalid @enderror"
                           placeholder="{{ __('auth.password_placeholder') }}"
                           required autofocus>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                <div class="quran-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="quran-btn quran-btn-primary w-100 mb-4">
                <i class="bi bi-check-lg me-2"></i>
                {{ __('auth.confirm') }}
            </button>

            <!-- Back -->
            <div class="auth-footer">
                <a href="{{ url()->previous() }}" class="auth-link">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('auth.back') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.parentElement.querySelector('.password-toggle i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endpush