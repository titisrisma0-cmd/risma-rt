@extends('layouts.auth')

@section('title', 'Konfirmasi Kata Sandi')

@section('content')
<div class="auth-card">
    <div class="auth-card-icon" style="background: var(--accent-soft); color: var(--accent); border-color: rgba(244, 166, 42, 0.28);">
        <i class="fas fa-shield-halved"></i>
    </div>
    
    <h1 class="auth-card-title">Konfirmasi Kata Sandi</h1>
    <p class="auth-card-desc">
        Harap masukkan kembali kata sandi Anda sebelum melanjutkan tindakan keamanan ini.
    </p>

    @if ($errors->any())
        <div class="alert-custom alert-custom-danger" role="alert">
            <i class="fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Konfirmasi Gagal</strong><br>
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" id="formConfirmPassword">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label-custom">
                Kata Sandi Saat Ini <span class="text-danger">*</span>
            </label>
            <div class="input-group-custom @error('password') border-danger @enderror">
                <span class="input-icon">
                    <i class="fas fa-lock"></i>
                </span>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password" 
                       placeholder="Masukkan kata sandi Anda"
                       class="w-100">
            </div>
            @error('password')
                <div class="text-danger small mt-1" style="font-size: 0.82rem;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex flex-column gap-2.5">
            <button type="submit" class="btn-primary-custom">
                <i class="fas fa-check-circle me-1"></i> Konfirmasi Kata Sandi
            </button>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="btn-secondary-custom">
                    <i class="fas fa-key me-1"></i> Lupa Kata Sandi?
                </a>
            @endif

            <div class="text-center mt-3 pt-3 border-top" style="border-color: var(--border) !important;">
                <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold small" style="color: var(--ink-soft);">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
