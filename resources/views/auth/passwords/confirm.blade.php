@extends('layouts.app')

@section('title', 'Konfirmasi Kata Sandi')

@section('content')
<div class="content-container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="width: 100%; max-width: 500px;">
        <div class="saas-card p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="stat-icon-wrapper bg-warning-soft text-warning mx-auto mb-3" style="width: 56px; height: 56px; display: grid; place-items: center; border-radius: 16px;">
                    <i class="fas fa-shield-halved fs-4"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Konfirmasi Kata Sandi</h4>
                <p class="text-secondary small mb-0">Harap masukkan kembali kata sandi Anda sebelum melanjutkan tindakan ini.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-3">
                    <label for="password" class="form-label-saas">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control-saas @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback d-block small mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-saas-primary">
                        <i class="fas fa-check me-1"></i> Konfirmasi Kata Sandi
                    </button>
                    @if (Route::has('password.request'))
                        <a class="btn btn-outline-saas text-center" href="{{ route('password.request') }}">
                            Lupa Kata Sandi?
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
