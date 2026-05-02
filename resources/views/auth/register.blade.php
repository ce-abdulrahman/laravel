{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.guest')

@section('title', __('auth.register'))

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-person-plus"></i>
            </div>
            <h3 class="auth-title">{{ __('auth.create_account') }}</h3>
            <p class="auth-subtitle">{{ __('auth.register_to_start') }}</p>
        </div>

        <!-- Register Form -->
        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <!-- Name -->
            <div class="form-group mb-4">
                <label class="quran-form-label" for="name">
                    <i class="bi bi-person me-1"></i>
                    {{ __('auth.name') }}
                </label>
                <input type="text" name="name" id="name" 
                       class="quran-form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" 
                       placeholder="{{ __('auth.name_placeholder') }}"
                       required autofocus>
                @error('name')
                <div class="quran-invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

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
                       required>
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

            <!-- Terms and Conditions -->
            <div class="quran-form-check mb-4">
                <input type="checkbox" name="terms" id="terms" 
                       class="quran-form-check-input" required>
                <label class="quran-form-check-label" for="terms">
                    {{ __('auth.i_agree_to') }}
                    <a href="#" class="auth-link">{{ __('auth.terms_of_service') }}</a>
                    {{ __('auth.and') }}
                    <a href="#" class="auth-link">{{ __('auth.privacy_policy') }}</a>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="quran-btn quran-btn-primary w-100 mb-4">
                <i class="bi bi-check-lg me-2"></i>
                {{ __('auth.register') }}
            </button>

            <!-- Login Link -->
            <div class="auth-footer">
                <p class="mb-0">
                    {{ __('auth.already_have_account') }}
                    <a href="{{ route('login') }}" class="auth-link fw-semibold">
                        {{ __('auth.login_now') }}
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
            وَلَقَدْ يَسَّرْنَا الْقُرْآنَ لِلذِّكْرِ فَهَلْ مِن مُّدَّكِرٍ
        </p>
        <p class="auth-verse-translation">
            {{ __('auth.verse_translation_register') }}
        </p>
        <p class="auth-verse-reference">سورة القمر - ١٧</p>
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