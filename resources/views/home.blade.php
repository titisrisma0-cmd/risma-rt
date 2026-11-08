@extends('layouts.app')

@section('title', 'Selamat Datang')

@section('content')
<div class="content-container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="width: 100%; max-width: 600px;">
        <div class="saas-card p-4 p-md-5 text-center">
            <div class="stat-icon-wrapper bg-success-soft text-success mx-auto mb-3" style="width: 64px; height: 64px; display: grid; place-items: center; border-radius: 18px;">
                <i class="fas fa-circle-check fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Anda Telah Berhasil Masuk</h4>
            <p class="text-secondary small mb-4">
                Sesi akun aktif. Silakan menuju dashboard utama untuk mengakses menu dan layanan lingkungan RW SMART.
            </p>

            @if (session('status'))
                <div class="alert alert-saas alert-saas-success mb-4 text-start" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                </div>
            @endif

            <a href="{{ route('dashboard') }}" class="btn btn-saas-primary">
                <i class="fas fa-gauge me-1"></i> Masuk ke Dashboard Utama
            </a>
        </div>
    </div>
</div>
@endsection
