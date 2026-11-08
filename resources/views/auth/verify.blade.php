@extends('layouts.app')

@section('title', 'Verifikasi Alamat Email')

@section('content')
<div class="content-container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="width: 100%; max-width: 520px;">
        <div class="saas-card p-4 p-md-5 text-center">
            <div class="stat-icon-wrapper bg-info-soft text-info mx-auto mb-3" style="width: 56px; height: 56px; display: grid; place-items: center; border-radius: 16px;">
                <i class="fas fa-envelope-open-text fs-4"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Verifikasi Alamat Email</h4>
            <p class="text-secondary small mb-4">
                Sebelum melanjutkan, periksa kotak masuk email Anda untuk mengeklik tautan verifikasi akun.
            </p>

            @if (session('resent'))
                <div class="alert alert-saas alert-saas-success mb-4 text-start" role="alert">
                    <i class="fas fa-check-circle me-1"></i> Tautan verifikasi baru telah berhasil dikirimkan ke alamat email Anda.
                </div>
            @endif

            <p class="text-muted small mb-4">
                Jika Anda belum menerima email verifikasi dari sistem:
            </p>

            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-saas-primary">
                    <i class="fas fa-paper-plane me-1"></i> Kirim Ulang Email Verifikasi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
