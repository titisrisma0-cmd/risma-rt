@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
<div class="auth-card">
    <div class="auth-card-icon">
        <i class="fas fa-key"></i>
    </div>
    
    <h1 class="auth-card-title">Lupa Kata Sandi?</h1>
    <p class="auth-card-desc">
        Masukkan alamat email terdaftar akun Anda. Sistem akan mengirimkan kode OTP atau tautan verifikasi untuk mengatur ulang kata sandi.
    </p>

    {{-- Status Alert (Bebas dari raw key 'passwords.sent') --}}
    @if (session('status'))
        @php
            $statusText = session('status');
            if ($statusText === 'passwords.sent' || str_contains(strtolower($statusText), 'link')) {
                $statusText = 'Kode OTP atau tautan pemulihan kata sandi telah dikirim ke email Anda.';
            } elseif ($statusText === 'passwords.throttled') {
                $statusText = 'Harap tunggu beberapa saat sebelum meminta tautan kembali.';
            } elseif ($statusText === 'passwords.user') {
                $statusText = 'Kami tidak dapat menemukan akun warga dengan alamat email tersebut.';
            } else {
                $statusText = __($statusText);
            }
        @endphp
        <div class="alert-custom alert-custom-success" role="alert">
            <i class="fas fa-check-circle mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Berhasil Terkirim!</strong><br>
                <span>{{ $statusText }}</span>
                <div class="mt-2">
                    <a href="{{ route('password.otp') }}" class="fw-bold text-decoration-underline" style="color: inherit;">
                        Sudah menerima kode? Verifikasi 6 Digit OTP di sini &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="alert-custom alert-custom-danger" role="alert">
            <i class="fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Permintaan Belum Berhasil</strong><br>
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" id="formForgotPassword">
        @csrf

        {{-- Input Email disusun Vertikal (Label di atas, Input w-full di bawah) --}}
        <div class="mb-4">
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
                       value="{{ old('email', request('email')) }}" 
                       required 
                       autocomplete="email" 
                       autofocus 
                       placeholder="contoh: warga@kelurahan.id"
                       class="w-100">
            </div>
            <small class="text-secondary d-block mt-1.5" style="font-size: 0.8rem;">
                Gunakan email yang telah Anda daftarkan di sistem SMART RW.
            </small>
        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex flex-column gap-2.5">
            <button type="submit" class="btn-primary-custom" id="btnSubmitReset">
                <i class="fas fa-paper-plane me-1"></i> Kirim Kode OTP / Tautan Reset
            </button>

            <a href="{{ route('password.otp') }}" class="btn-secondary-custom">
                <i class="fas fa-shield-halved me-1"></i> Masukkan 6 Digit Kode OTP
            </a>

            <div class="text-center mt-3 pt-3 border-top" style="border-color: var(--border) !important;">
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold small" style="color: var(--ink-soft); transition: color 0.2s ease;">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
