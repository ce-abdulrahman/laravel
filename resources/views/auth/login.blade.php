{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.guest')

@section('title', __('auth.login'))

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-book"></i>
            </div>
            <h3 class="auth-title">{{ __('auth.welcome_back') }}</h3>
            <p class="auth-subtitle">{{ __('auth.login_to_continue') }}</p>
        </div>

        <!-- Session Status -->
        @if(session('status'))
        <div class="alert alert-success mb-4">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('status') }}
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="auth-form">
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
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                <div class="quran-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="quran-form-check">
                    <input type="checkbox" name="remember" id="remember_me" 
                           class="quran-form-check-input">
                    <label class="quran-form-check-label" for="remember_me">
                        {{ __('auth.remember_me') }}
                    </label>
                </div>

                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">
                    {{ __('auth.forgot_password') }}
                </a>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit" class="quran-btn quran-btn-primary w-100 mb-4">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                {{ __('auth.login') }}
            </button>

            <!-- Register Link -->
            <div class="auth-footer">
                <p class="mb-0">
                    {{ __('auth.no_account') }}
                    <a href="{{ route('register') }}" class="auth-link fw-semibold">
                        {{ __('auth.register_now') }}
                    </a>
                </p>
            </div>
        </form>
    </div>

    <!-- Quran Verse -->
    <div class="auth-verse">
        <div class="auth-verse-icon">
            <i class="bi bi-quote"></i>
        </div>
        <p class="auth-verse-text arabic-text">
            إِنَّ هَٰذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ
        </p>
        <p class="auth-verse-translation">
            {{ __('auth.verse_translation') }}
        </p>
        <p class="auth-verse-reference">سورة الإسراء - ٩</p>
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