{{-- resources/views/auth/reset-password.blade.php --}}
@extends('layouts.guest')

@section('title', __('auth.reset_password'))

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h3 class="auth-title">{{ __('auth.reset_password') }}</h3>
            <p class="auth-subtitle">{{ __('auth.reset_password_hint') }}</p>
        </div>

        <!-- Reset Password Form -->
        <form method="POST" action="{{ route('password.store') }}" class="auth-form">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email -->
            <div class="form-group mb-4">
                <label class="quran-form-label" for="email">
                    <i class="bi bi-envelope me-1"></i>
                    {{ __('auth.email') }}
                </label>
                <input type="email" name="email" id="email" 
                       class="quran-form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $request->email) }}" 
                       placeholder="{{ __('auth.email_placeholder') }}"
                       required readonly>
                @error('email')
                <div class="quran-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group mb-4">
                <label class="quran-form-label" for="password">
                    <i class="bi bi-lock me-1"></i>
                    {{ __('auth.new_password') }}
                </label>
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="password" 
                           class="quran-form-control @error('password') is-invalid @enderror"
                           placeholder="{{ __('auth.new_password_placeholder') }}"
                           required autofocus>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                <div class="quran-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group mb-4">
                <label class="quran-form-label" for="password_confirmation">
                    <i class="bi bi-lock-fill me-1"></i>
                    {{ __('auth.confirm_password') }}
                </label>
                <div class="password-input-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="quran-form-control @error('password_confirmation') is-invalid @enderror"
                           placeholder="{{ __('auth.confirm_password_placeholder') }}"
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password_confirmation')
                <div class="quran-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="quran-btn quran-btn-primary w-100 mb-4">
                <i class="bi bi-check-lg me-2"></i>
                {{ __('auth.reset_password') }}
            </button>
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