<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpVerificationController extends Controller
{
    /**
     * Tampilkan formulir verifikasi 6 digit kode OTP
     * Endpoint: GET /password/otp
     */
    public function show(Request $request)
    {
        $email = session('reset_email');

        // Jika tidak ada sesi email aktif, arahkan kembali ke form permintaan lupa kata sandi
        if (empty($email)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Silakan masukkan alamat email Anda terlebih dahulu.']);
        }

        $maskedEmail = OtpService::maskEmail($email);

        return view('auth.passwords.otp', compact('maskedEmail'));
    }

    /**
     * Verifikasi kode OTP 6 digit
     * Endpoint: POST /password/otp/verify
     */
    public function verify(Request $request)
    {
        $email = session('reset_email') ?: strtolower(trim((string) $request->input('email', '')));

        if (empty($email)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi pemulihan telah berakhir. Silakan masukkan email Anda kembali.']);
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Harap masukkan 6 digit kode OTP.',
            'otp.size' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $ip = $request->ip();
        $inputOtp = (string) $request->input('otp');

        $result = OtpService::verifyOtp($email, $inputOtp, $ip);

        if (! $result['success']) {
            return back()->withInput()->withErrors([
                'otp' => $result['message'],
            ]);
        }

        // Simpan email yang sudah terverifikasi ke session untuk form reset password
        session([
            'reset_verified_email' => $email,
            'reset_email' => $email,
        ]);

        return redirect()->route('password.reset', [
            'token' => $result['token'],
            'email' => $email,
        ])->with('status', 'Kode OTP berhasil diverifikasi. Silakan buat kata sandi baru Anda.');
    }

    /**
     * Kirim ulang kode OTP verifikasi
     * Endpoint: POST /password/otp/resend
     */
    public function resend(Request $request)
    {
        $email = session('reset_email') ?: strtolower(trim((string) $request->input('email', '')));

        if (empty($email)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi pemulihan telah berakhir. Silakan masukkan email Anda kembali.']);
        }

        $ip = $request->ip();

        // 1. Cek pembatasan laju (rate limiting)
        if (OtpService::isRateLimited($email, $ip)) {
            return back()->withErrors([
                'otp' => OtpService::getRateLimitMessage($email, $ip),
            ]);
        }

        // 2. Kirim ulang OTP langsung
        $sent = OtpService::generateAndSendOtp($email, $ip);

        if ($sent === false) {
            return back()->withErrors([
                'otp' => 'Gagal mengirimkan email verifikasi baru. Silakan periksa koneksi atau hubungi pengurus RT.',
            ]);
        }

        return back()->with('status', 'Kode verifikasi baru telah dikirimkan ke email terdaftar Anda.');
    }
}
