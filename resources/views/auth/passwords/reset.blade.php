@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="auth-card">
    <div class="auth-card-icon">
        <i class="fas fa-lock"></i>
    </div>
    
    <h1 class="auth-card-title">Atur Ulang Kata Sandi</h1>
    <p class="auth-card-desc">
        Buat kata sandi baru untuk mengamankan kembali akses akun warga Anda.
    </p>

    {{-- Status Alert --}}
    @if (session('status'))
        <div class="alert-custom alert-custom-success" role="alert">
            <i class="fas fa-check-circle mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Informasi</strong><br>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="alert-custom alert-custom-danger" role="alert">
            <i class="fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Gagal Menyimpan</strong><br>
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" id="formResetPassword">
        @csrf
        <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

        {{-- Email Address (Vertical Stacking) --}}
        <div class="mb-3.5">
            <label for="email" class="form-label-custom">
                Alamat Email Terdaftar <span class="text-danger">*</span>
            </label>
            <div class="input-group-custom @error('email') border-danger @enderror">
                <span class="input-icon">
                    <i class="fas fa-envelope"></i>
                </span>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ $email ?? old('email') }}" 
                       required 
                       autocomplete="email" 
                       autofocus 
                       placeholder="nama@email.com"
                       class="w-100">
            </div>
            @error('email')
                <div class="text-danger small mt-1" style="font-size: 0.82rem;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- New Password (Vertical Stacking + Eye Toggle) --}}
        <div class="mb-3.5">
            <label for="password" class="form-label-custom">
                Kata Sandi Baru <span class="text-danger">*</span>
            </label>
            <div class="input-group-custom @error('password') border-danger @enderror">
                <span class="input-icon">
                    <i class="fas fa-key"></i>
                </span>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="new-password" 
                       placeholder="Minimal 8 karakter"
                       class="w-100">
                <button type="button" class="btn-toggle-password" id="togglePasswordBtn" title="Tampilkan/Sembunyikan Kata Sandi" aria-label="Toggle password visibility">
                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="text-danger small mt-1" style="font-size: 0.82rem;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Confirm New Password (Vertical Stacking + Eye Toggle) --}}
        <div class="mb-4">
            <label for="password-confirm" class="form-label-custom">
                Ulangi Kata Sandi Baru <span class="text-danger">*</span>
            </label>
            <div class="input-group-custom">
                <span class="input-icon">
                    <i class="fas fa-check-double"></i>
                </span>
                <input id="password-confirm" 
                       type="password" 
                       name="password_confirmation" 
                       required 
                       autocomplete="new-password" 
                       placeholder="Ulangi kata sandi baru Anda"
                       class="w-100">
                <button type="button" class="btn-toggle-password" id="toggleConfirmPasswordBtn" title="Tampilkan/Sembunyikan Kata Sandi" aria-label="Toggle confirm password visibility">
                    <i class="fas fa-eye" id="toggleConfirmPasswordIcon"></i>
                </button>
            </div>
            <small class="text-secondary d-block mt-1.5" style="font-size: 0.8rem;">
                Pastikan kata sandi minimal 8 karakter dan kombinasi yang aman.
            </small>
        </div>

        {{-- Submit Button --}}
        <div class="d-flex flex-column gap-2.5">
            <button type="submit" class="btn-primary-custom" id="btnSubmitNewPassword">
                <i class="fas fa-shield-check me-1"></i> Simpan & Perbarui Kata Sandi
            </button>

            <div class="text-center mt-3 pt-3 border-top" style="border-color: var(--border) !important;">
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold small" style="color: var(--ink-soft); transition: color 0.2s ease;">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password toggle visibility
    function setupToggle(btnId, inputId, iconId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!btn || !input || !icon) return;

        btn.addEventListener('click', function () {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    setupToggle('togglePasswordBtn', 'password', 'togglePasswordIcon');
    setupToggle('toggleConfirmPasswordBtn', 'password-confirm', 'toggleConfirmPasswordIcon');
});
</script>
@endpush
