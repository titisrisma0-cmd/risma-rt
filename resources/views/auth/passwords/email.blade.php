@extends('layouts.app')

@section('title', 'Reset Kata Sandi')

@section('content')
<div class="content-container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="width: 100%; max-width: 520px;">
        <div class="saas-card p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="stat-icon-wrapper bg-success-soft text-success mx-auto mb-3" style="width: 56px; height: 56px; display: grid; place-items: center; border-radius: 16px;">
                    <i class="fas fa-key fs-4"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Lupa Kata Sandi?</h4>
                <p class="text-secondary small mb-0">Masukkan alamat email Anda untuk menerima tautan pemulihan kata sandi.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-saas alert-saas-success mb-4" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label-saas">Alamat Email Terdaftar <span class="text-danger">*</span></label>
                    <input id="email" type="email" class="form-control-saas @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@email.com">
                    @error('email')
                        <div class="invalid-feedback d-block small mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-saas-primary">
                        <i class="fas fa-paper-plane me-1"></i> Kirim Tautan Reset
                    </button>
                    <a href="{{ route('login') }}" class="btn btn-outline-saas text-center">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Masuk
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
