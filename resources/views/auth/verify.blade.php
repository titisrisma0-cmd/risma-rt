@extends('layouts.auth')

@section('title', 'Verifikasi Alamat Email')

@section('content')
<div class="auth-card text-center">
    <div class="auth-card-icon" style="background: var(--info-soft); color: var(--info); border-color: rgba(42, 111, 219, 0.28);">
        <i class="fas fa-envelope-open-text"></i>
    </div>
    
    <h1 class="auth-card-title">Verifikasi Alamat Email</h1>
    <p class="auth-card-desc">
        Sebelum melanjutkan, periksa kotak masuk email Anda untuk mengklik tautan verifikasi akun.
    </p>

    @if (session('resent'))
        <div class="alert-custom alert-custom-success text-start" role="alert">
            <i class="fas fa-check-circle mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Terkirim!</strong><br>
                <span>Tautan verifikasi baru telah berhasil dikirimkan ke alamat email Anda.</span>
            </div>
        </div>
    @endif

    <p class="text-secondary small mb-4">
        Jika Anda belum menerima email verifikasi dari sistem SMART RW:
    </p>

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <div class="d-flex flex-column gap-2.5">
            <button type="submit" class="btn-primary-custom">
                <i class="fas fa-paper-plane me-1"></i> Kirim Ulang Email Verifikasi
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
        @csrf
        <button type="submit" class="btn-secondary-custom w-100">
            <i class="fas fa-sign-out-alt me-1"></i> Keluar Akun (Logout)
        </button>
    </form>
</div>
@endsection
