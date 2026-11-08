@extends('layouts.app')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="content-container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div style="width: 100%; max-width: 520px;">
        <div class="saas-card p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="stat-icon-wrapper bg-success-soft text-success mx-auto mb-3" style="width: 56px; height: 56px; display: grid; place-items: center; border-radius: 16px;">
                    <i class="fas fa-lock fs-4"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Atur Ulang Kata Sandi</h4>
                <p class="text-secondary small mb-0">Buat kata sandi baru untuk mengamankan kembali akun Anda.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label-saas">Alamat Email <span class="text-danger">*</span></label>
                    <input id="email" type="email" class="form-control-saas @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <div class="invalid-feedback d-block small mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label-saas">Kata Sandi Baru <span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control-saas @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback d-block small mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password-confirm" class="form-label-saas">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                    <input id="password-confirm" type="password" class="form-control-saas" name="password_confirmation" required autocomplete="new-password">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-saas-primary">
                        <i class="fas fa-check-circle me-1"></i> Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
