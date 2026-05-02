{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('profile.title'))
@section('page-title', __('profile.title'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('profile.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('profile.title') }}</h1>
            <div class="text-muted">{{ __('profile.manage_account') }}</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Profile Information -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-person me-2"></i>
                        {{ __('profile.profile_information') }}
                    </h5>
                    <p class="text-muted small mb-0">{{ __('profile.profile_information_hint') }}</p>
                </div>
                <div class="quran-card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="quran-form-label" for="name">
                                    <i class="bi bi-person me-1"></i>
                                    {{ __('profile.name') }}
                                </label>
                                <input type="text" name="name" id="name" 
                                       class="quran-form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="quran-form-label" for="email">
                                    <i class="bi bi-envelope me-1"></i>
                                    {{ __('profile.email') }}
                                </label>
                                <input type="email" name="email" id="email" 
                                       class="quran-form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                <div class="quran-invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <p class="text-warning small">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        {{ __('profile.email_unverified') }}
                                    </p>
                                    <button form="send-verification" class="btn btn-link btn-sm p-0">
                                        {{ __('profile.resend_verification') }}
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="quran-btn quran-btn-primary">
                                <i class="bi bi-save me-1"></i>
                                {{ __('common.save') }}
                            </button>

                            @if(session('status') === 'profile-updated')
                            <span class="text-success ms-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ __('profile.saved') }}
                            </span>
                            @endif
                        </div>
                    </form>

                    <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-lock me-2"></i>
                        {{ __('profile.update_password') }}
                    </h5>
                    <p class="text-muted small mb-0">{{ __('profile.update_password_hint') }}</p>
                </div>
                <div class="quran-card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label class="quran-form-label" for="current_password">
                                <i class="bi bi-key me-1"></i>
                                {{ __('profile.current_password') }}
                            </label>
                            <input type="password" name="current_password" id="current_password" 
                                   class="quran-form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password">
                            @error('current_password', 'updatePassword')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="password">
                                <i class="bi bi-lock-fill me-1"></i>
                                {{ __('profile.new_password') }}
                            </label>
                            <input type="password" name="password" id="password" 
                                   class="quran-form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password', 'updatePassword')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="quran-form-label" for="password_confirmation">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ __('profile.confirm_password') }}
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="quran-form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword')
                            <div class="quran-invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="quran-btn quran-btn-primary">
                                <i class="bi bi-save me-1"></i>
                                {{ __('common.save') }}
                            </button>

                            @if(session('status') === 'password-updated')
                            <span class="text-success ms-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ __('profile.saved') }}
                            </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Account Summary -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-person-circle me-2"></i>
                        {{ __('profile.account_summary') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="quran-avatar">
                            <div class="quran-avatar-img bg-primary d-flex align-items-center justify-content-center text-white">
                                {{ Str::substr($user->name, 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('profile.member_since') }}</label>
                        <div class="quran-detail-value">{{ $user->created_at->format('Y-m-d') }}</div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('profile.account_type') }}</label>
                        <div class="quran-detail-value">
                            @if($user->role === 'admin')
                            <span class="quran-table-badge danger">{{ __('profile.admin') }}</span>
                            @else
                            <span class="quran-table-badge success">{{ __('profile.user') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Account -->
            <div class="quran-card border-danger">
                <div class="quran-card-header bg-danger bg-opacity-10">
                    <h6 class="quran-card-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('profile.delete_account') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <p class="text-muted small">{{ __('profile.delete_account_warning') }}</p>

                    <button type="button" class="quran-btn quran-btn-outline-danger" 
                            x-data="" 
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
                        <i class="bi bi-trash me-1"></i>
                        {{ __('profile.delete_account') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="POST" action="{{ route('profile.destroy') }}" class="p-4">
        @csrf
        @method('delete')

        <h5 class="mb-3">{{ __('profile.are_you_sure') }}</h5>

        <p class="text-muted small mb-4">
            {{ __('profile.delete_account_confirmation') }}
        </p>

        <div class="mb-4">
            <label class="quran-form-label" for="delete_password">
                {{ __('profile.password') }}
            </label>
            <input type="password" name="password" id="delete_password" 
                   class="quran-form-control @error('password', 'userDeletion') is-invalid @enderror"
                   placeholder="{{ __('profile.enter_password') }}">
            @error('password', 'userDeletion')
            <div class="quran-invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="quran-btn quran-btn-outline-primary" x-on:click="$dispatch('close')">
                {{ __('common.cancel') }}
            </button>
            <button type="submit" class="quran-btn quran-btn-danger">
                {{ __('profile.delete_account') }}
            </button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush