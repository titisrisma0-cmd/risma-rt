@extends('layouts.auth')

@section('title', 'Verifikasi Kode OTP')

@section('content')
<div class="auth-card">
    <div class="auth-card-icon">
        <i class="fas fa-shield-halved"></i>
    </div>
    
    <h1 class="auth-card-title">Verifikasi Kode OTP</h1>
    <p class="auth-card-desc mb-2">
        Masukkan 6 digit kode verifikasi yang telah dikirimkan ke alamat email terdaftar Anda.
    </p>

    {{-- Display Email Target (Disamarkan) --}}
    @if (!empty($maskedEmail ?? $email))
        <div class="text-center mb-3">
            <span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill small fw-semibold" style="background: var(--surface-2); border: 1px solid var(--border); color: var(--ink);">
                <i class="fas fa-envelope text-success"></i> {{ $maskedEmail ?? \App\Services\OtpService::maskEmail($email) }}
            </span>
            <div class="mt-1">
                <a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none" style="color: var(--primary); font-size: 0.8rem;">
                    Bukan email Anda? Ganti Email
                </a>
            </div>
        </div>
    @endif

    {{-- Alert Status --}}
    @if (session('status'))
        <div class="alert-custom alert-custom-success" role="alert">
            <i class="fas fa-check-circle mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Informasi OTP</strong><br>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    {{-- Alert Error --}}
    @if ($errors->any())
        <div class="alert-custom alert-custom-danger" role="alert">
            <i class="fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0"></i>
            <div>
                <strong>Verifikasi Gagal</strong><br>
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    {{-- Form Verifikasi OTP --}}
    <form method="POST" action="{{ route('password.otp.verify') }}" id="formOtpVerification">
        @csrf
        <input type="hidden" name="otp" id="hiddenOtpInput" value="{{ old('otp') }}">

        <div class="text-center mb-2">
            <label class="form-label-custom mb-1">
                Kode Verifikasi 6 Digit <span class="text-danger">*</span>
            </label>
            <span class="text-secondary small" style="font-size: 0.8rem;">
                Ketik digit satu per satu atau tempel (paste) kode langsung.
            </span>
        </div>

        {{-- 6 Digit Input Boxes --}}
        <div class="otp-box-grid" id="otpBoxGrid">
            <input type="text" class="otp-digit-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-index="0" autocomplete="off" autofocus aria-label="Digit 1">
            <input type="text" class="otp-digit-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-index="1" autocomplete="off" aria-label="Digit 2">
            <input type="text" class="otp-digit-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-index="2" autocomplete="off" aria-label="Digit 3">
            <input type="text" class="otp-digit-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-index="3" autocomplete="off" aria-label="Digit 4">
            <input type="text" class="otp-digit-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-index="4" autocomplete="off" aria-label="Digit 5">
            <input type="text" class="otp-digit-input" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-index="5" autocomplete="off" aria-label="Digit 6">
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex flex-column gap-2.5 mt-4">
            <button type="submit" class="btn-primary-custom" id="btnVerifyOtp">
                <i class="fas fa-check-circle me-1"></i> Verifikasi & Lanjutkan
            </button>
        </div>
    </form>

    {{-- Form Kirim Ulang OTP Terpisah --}}
    <form method="POST" action="{{ route('password.otp.resend') }}" id="formResendOtp" class="mt-2.5">
        @csrf
        <button type="submit" class="btn-secondary-custom" id="btnResendOtp">
            <i class="fas fa-rotate-right me-1" id="resendIcon"></i> <span id="resendText">Kirim Ulang Kode OTP</span>
        </button>
    </form>

    {{-- Petunjuk Bantuan & Folder Spam (Requirement 6) --}}
    <div class="mt-3 p-2.5 rounded-3 text-center small" style="background: var(--surface-2); border: 1px solid var(--border); color: var(--ink-soft); font-size: 0.82rem; line-height: 1.5;">
        <i class="fas fa-circle-info text-primary me-1"></i>
        Tidak menerima email? Cek folder Spam. Anda juga bisa menghubungi pengurus RT untuk reset kata sandi.
    </div>

    <div class="text-center mt-3 pt-3 border-top" style="border-color: var(--border) !important;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('password.request') }}" class="text-decoration-none fw-semibold small" style="color: var(--ink-soft);">
                <i class="fas fa-arrow-left me-1"></i> Ubah Email
            </a>
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold small" style="color: var(--primary);">
                Halaman Masuk &rarr;
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.otp-digit-input');
    const hiddenInput = document.getElementById('hiddenOtpInput');
    const form = document.getElementById('formOtpVerification');
    const btnResend = document.getElementById('btnResendOtp');
    const resendText = document.getElementById('resendText');

    function syncHiddenInput() {
        let combined = '';
        inputs.forEach(input => {
            combined += input.value.trim();
        });
        if (hiddenInput) {
            hiddenInput.value = combined;
        }
        return combined;
    }

    // Auto-focus logic & input restrictions
    inputs.forEach((input, index) => {
        // Prevent non-numeric entry
        input.addEventListener('input', function (e) {
            const val = this.value.replace(/[^0-9]/g, '');
            this.value = val ? val.charAt(val.length - 1) : '';

            if (this.value) {
                this.classList.add('filled');
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            } else {
                this.classList.remove('filled');
            }

            const code = syncHiddenInput();
            if (code.length === 6) {
                // All 6 digits filled
                document.getElementById('btnVerifyOtp').focus();
            }
        });

        // Handle Backspace and Navigation
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                if (!this.value && index > 0) {
                    inputs[index - 1].value = '';
                    inputs[index - 1].classList.remove('filled');
                    inputs[index - 1].focus();
                    syncHiddenInput();
                    e.preventDefault();
                } else {
                    this.value = '';
                    this.classList.remove('filled');
                    syncHiddenInput();
                }
            } else if (e.key === 'ArrowLeft' && index > 0) {
                inputs[index - 1].focus();
                e.preventDefault();
            } else if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                inputs[index + 1].focus();
                e.preventDefault();
            }
        });

        // Paste support for the entire 6-digit code
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text');
            const cleanDigits = pasteData.replace(/[^0-9]/g, '').slice(0, 6);

            if (cleanDigits.length > 0) {
                cleanDigits.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        inputs[i].classList.add('filled');
                    }
                });
                syncHiddenInput();
                const nextFocus = Math.min(cleanDigits.length, inputs.length - 1);
                inputs[nextFocus].focus();
                if (cleanDigits.length === 6) {
                    document.getElementById('btnVerifyOtp').focus();
                }
            }
        });
    });

    // Form submit validation
    form.addEventListener('submit', function (e) {
        const code = syncHiddenInput();
        if (code.length !== 6) {
            e.preventDefault();
            alert('Harap masukkan lengkap 6 digit kode OTP verifikasi.');
            const firstEmpty = Array.from(inputs).find(input => !input.value);
            if (firstEmpty) firstEmpty.focus();
        }
    });

    // Countdown Timer for Resend OTP (60s)
    let countdown = 60;
    let timer = null;

    function startResendCountdown() {
        btnResend.disabled = true;
        btnResend.style.opacity = '0.7';
        btnResend.style.cursor = 'not-allowed';

        timer = setInterval(function () {
            countdown--;
            resendText.textContent = `Kirim Ulang Kode (${countdown}s)`;

            if (countdown <= 0) {
                clearInterval(timer);
                btnResend.disabled = false;
                btnResend.style.opacity = '1';
                btnResend.style.cursor = 'pointer';
                resendText.textContent = 'Kirim Ulang Kode OTP';
                countdown = 60;
            }
        }, 1000);
    }

    startResendCountdown();
});
</script>
@endpush
