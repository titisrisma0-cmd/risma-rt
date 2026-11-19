<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    */

    use SendsPasswordResetEmails;

    /**
     * Mengirimkan kode OTP verifikasi untuk pengaturan ulang kata sandi
     * Endpoint: POST /password/email
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Harap masukkan alamat email Anda.',
            'email.email' => 'Format alamat email tidak valid.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $ip = $request->ip();

        // 1. Cek pembatasan laju (rate limiting: max 3 kirim per 10 menit, max 5 salah input)
        if (OtpService::isRateLimited($email, $ip)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => OtpService::getRateLimitMessage($email, $ip),
            ]);
        }

        // 2. Generate dan kirim kode OTP langsung
        $sent = OtpService::generateAndSendOtp($email, $ip);

        if ($sent === false) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Gagal mengirimkan email verifikasi. Silakan periksa koneksi atau hubungi pengurus RT.',
            ]);
        }

        // 3. Simpan email di session (TIDAK menyertakan email di query string URL)
        session(['reset_email' => $email]);

        // 4. Respons identik baik email terdaftar maupun tidak (mencegah enumerasi akun)
        return redirect()->route('password.otp')
            ->with('status', 'Kode verifikasi telah dikirimkan ke email terdaftar Anda jika akun tersedia.');
    }
}
