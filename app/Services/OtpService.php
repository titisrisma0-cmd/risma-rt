<?php

namespace App\Services;

use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class OtpService
{
    public const OTP_EXPIRY_SECONDS = 600; // 10 menit
    public const MAX_RESEND_ATTEMPTS = 3;  // Maksimal 3 kirim/kirim ulang per 10 menit
    public const MAX_FAILED_ATTEMPTS = 5;  // Maksimal 5 kali salah input per 10 menit

    /**
     * Samarkan alamat email untuk tampilan UI (contoh: t*****@gmail.com)
     */
    public static function maskEmail(?string $email): string
    {
        if (empty($email) || !str_contains($email, '@')) {
            return '';
        }

        [$user, $domain] = explode('@', $email, 2);
        $firstChar = substr($user, 0, 1);

        return $firstChar . '*****@' . $domain;
    }

    /**
     * Cek apakah permintaan OTP dibatasi (rate limited)
     */
    public static function isRateLimited(string $email, string $ip): bool
    {
        $failedEmail = (int) Cache::get(self::failedAttemptsKeyEmail($email), 0);
        $failedIp = (int) Cache::get(self::failedAttemptsKeyIp($ip), 0);

        if ($failedEmail >= self::MAX_FAILED_ATTEMPTS || $failedIp >= self::MAX_FAILED_ATTEMPTS) {
            return true;
        }

        $resendEmail = (int) Cache::get(self::resendKeyEmail($email), 0);
        $resendIp = (int) Cache::get(self::resendKeyIp($ip), 0);

        return $resendEmail >= self::MAX_RESEND_ATTEMPTS || $resendIp >= self::MAX_RESEND_ATTEMPTS;
    }

    /**
     * Dapatkan pesan kesalahan pembatasan laju (rate limit)
     */
    public static function getRateLimitMessage(string $email, string $ip): string
    {
        $failedEmail = (int) Cache::get(self::failedAttemptsKeyEmail($email), 0);
        $failedIp = (int) Cache::get(self::failedAttemptsKeyIp($ip), 0);

        if ($failedEmail >= self::MAX_FAILED_ATTEMPTS || $failedIp >= self::MAX_FAILED_ATTEMPTS) {
            return 'Terlalu banyak percobaan verifikasi yang salah. Silakan coba lagi setelah 10 menit.';
        }

        return 'Batas pengiriman kode verifikasi tercapai (maksimal 3 kali per 10 menit). Harap tunggu beberapa saat.';
    }

    /**
     * Generate 6-digit OTP, simpan sebagai hash (10 menit), dan kirim email langsung
     */
    public static function generateAndSendOtp(string $email, string $ip): bool
    {
        $normalizedEmail = strtolower(trim($email));

        // Catat hitungan resend
        self::incrementResendCount($normalizedEmail, $ip);

        // Generate 6 digit angka acak
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = hash('sha256', $otp . config('app.key'));

        // Simpan hanya hash-nya ke Cache selama 10 menit
        Cache::put(self::otpHashKey($normalizedEmail), $otpHash, self::OTP_EXPIRY_SECONDS);
        Cache::put(self::otpCreatedKey($normalizedEmail), now()->timestamp, self::OTP_EXPIRY_SECONDS);

        // Cek apakah email terdaftar di database
        $user = User::where('email', $normalizedEmail)->first();

        if ($user) {
            try {
                // Kirim langsung (synchronous) tanpa antrean
                Mail::to($user->email)->send(new ResetPasswordOtpMail($otp));
            } catch (\Throwable $e) {
                $maskedEmail = self::maskEmail($user->email);
                // Catat log error TANPA menyertakan kode OTP
                Log::error("Gagal mengirim email verifikasi OTP ke {$maskedEmail}: " . $e->getMessage());
                return false;
            }
        }

        // Jika user tidak terdaftar, tetap kembalikan true agar respons identik (anti-enumeration)
        return true;
    }

    /**
     * Verifikasi kode OTP 6 digit yang diinputkan pengguna
     *
     * @return array{success: bool, message: string, token: ?string}
     */
    public static function verifyOtp(string $email, string $inputOtp, string $ip): array
    {
        $normalizedEmail = strtolower(trim($email));

        // Cek limit salah input
        $failedEmail = (int) Cache::get(self::failedAttemptsKeyEmail($normalizedEmail), 0);
        $failedIp = (int) Cache::get(self::failedAttemptsKeyIp($ip), 0);

        if ($failedEmail >= self::MAX_FAILED_ATTEMPTS || $failedIp >= self::MAX_FAILED_ATTEMPTS) {
            return [
                'success' => false,
                'message' => 'Terlalu banyak percobaan verifikasi yang salah. Silakan coba lagi setelah 10 menit.',
                'token' => null,
            ];
        }

        $storedHash = Cache::get(self::otpHashKey($normalizedEmail));
        $cleanInputOtp = trim($inputOtp);
        $inputHash = hash('sha256', $cleanInputOtp . config('app.key'));

        // Validasi hash dan panjang 6 digit
        if (empty($storedHash) || strlen($cleanInputOtp) !== 6 || !hash_equals($storedHash, $inputHash)) {
            self::incrementFailedAttempts($normalizedEmail, $ip);

            return [
                'success' => false,
                'message' => 'Kode OTP verifikasi tidak valid atau telah kedaluwarsa.',
                'token' => null,
            ];
        }

        // Berhasil verifikasi: hapus failed attempts counter
        Cache::forget(self::failedAttemptsKeyEmail($normalizedEmail));
        Cache::forget(self::failedAttemptsKeyIp($ip));

        // Sekali pakai: hapus hash OTP dari cache segera
        Cache::forget(self::otpHashKey($normalizedEmail));
        Cache::forget(self::otpCreatedKey($normalizedEmail));

        $user = User::where('email', $normalizedEmail)->first();

        if (! $user) {
            return [
                'success' => false,
                'message' => 'Akun tidak ditemukan. Silakan hubungi pengurus RT.',
                'token' => null,
            ];
        }

        // Generate token reset password resmi Laravel broker
        $resetToken = Password::broker()->createToken($user);

        return [
            'success' => true,
            'message' => 'Kode OTP berhasil diverifikasi. Silakan buat kata sandi baru Anda.',
            'token' => $resetToken,
        ];
    }

    // =========================================================================
    // PRIVATE CACHE KEYS & INCREMENT HELPERS
    // =========================================================================

    private static function otpHashKey(string $email): string
    {
        return 'pwd_otp_hash_' . hash('sha256', $email);
    }

    private static function otpCreatedKey(string $email): string
    {
        return 'pwd_otp_created_' . hash('sha256', $email);
    }

    private static function resendKeyEmail(string $email): string
    {
        return 'pwd_otp_resend_email_' . hash('sha256', $email);
    }

    private static function resendKeyIp(string $ip): string
    {
        return 'pwd_otp_resend_ip_' . hash('sha256', $ip);
    }

    private static function failedAttemptsKeyEmail(string $email): string
    {
        return 'pwd_otp_failed_email_' . hash('sha256', $email);
    }

    private static function failedAttemptsKeyIp(string $ip): string
    {
        return 'pwd_otp_failed_ip_' . hash('sha256', $ip);
    }

    private static function incrementResendCount(string $email, string $ip): void
    {
        $keyEmail = self::resendKeyEmail($email);
        $keyIp = self::resendKeyIp($ip);

        $curEmail = (int) Cache::get($keyEmail, 0) + 1;
        $curIp = (int) Cache::get($keyIp, 0) + 1;

        Cache::put($keyEmail, $curEmail, self::OTP_EXPIRY_SECONDS);
        Cache::put($keyIp, $curIp, self::OTP_EXPIRY_SECONDS);
    }

    private static function incrementFailedAttempts(string $email, string $ip): void
    {
        $keyEmail = self::failedAttemptsKeyEmail($email);
        $keyIp = self::failedAttemptsKeyIp($ip);

        $curEmail = (int) Cache::get($keyEmail, 0) + 1;
        $curIp = (int) Cache::get($keyIp, 0) + 1;

        Cache::put($keyEmail, $curEmail, self::OTP_EXPIRY_SECONDS);
        Cache::put($keyIp, $curIp, self::OTP_EXPIRY_SECONDS);
    }
}
