<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordOtpMail;
use App\Models\Role;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasswordOtpEmailFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $wargaUser;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('nama_role')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->boolean('is_super_admin')->default(false);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('nama_lengkap')->nullable();
                $table->string('nik')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);

        $this->wargaUser = User::firstOrCreate(
            ['email' => 'teguh.purnomo@gmail.com'],
            [
                'name' => 'Teguh Purnomo',
                'password' => bcrypt('password123'),
                'role_id' => $wargaRole->id,
            ]
        );
    }

    public function test_email_masking_helper_masks_correctly(): void
    {
        $this->assertEquals('t*****@gmail.com', OtpService::maskEmail('teguh.purnomo@gmail.com'));
        $this->assertEquals('w*****@smartrw.id', OtpService::maskEmail('warga@smartrw.id'));
        $this->assertEquals('a*****@example.org', OtpService::maskEmail('a@example.org'));
        $this->assertEquals('', OtpService::maskEmail(''));
        $this->assertEquals('', OtpService::maskEmail('invalid-email'));
    }

    public function test_forgot_password_response_is_identical_for_registered_and_unregistered_email(): void
    {
        Mail::fake();

        // 1. Email terdaftar
        $resRegistered = $this->post(route('password.email'), [
            'email' => 'teguh.purnomo@gmail.com',
        ]);

        $resRegistered->assertRedirect(route('password.otp'));
        $resRegistered->assertSessionHas('status', 'Kode verifikasi telah dikirimkan ke email terdaftar Anda jika akun tersedia.');
        $resRegistered->assertSessionHas('reset_email', 'teguh.purnomo@gmail.com');
        Mail::assertSent(ResetPasswordOtpMail::class, function ($mail) {
            return $mail->hasTo('teguh.purnomo@gmail.com');
        });

        // 2. Email TIDAK terdaftar
        Mail::fake(); // reset fake
        Cache::flush();
        session()->flush();

        $resUnregistered = $this->post(route('password.email'), [
            'email' => 'tidak.terdaftar.999@gmail.com',
        ]);

        // Respons IDENTIK: redirect ke route yang sama dengan pesan status yang sama
        $resUnregistered->assertRedirect(route('password.otp'));
        $resUnregistered->assertSessionHas('status', 'Kode verifikasi telah dikirimkan ke email terdaftar Anda jika akun tersedia.');
        $resUnregistered->assertSessionHas('reset_email', 'tidak.terdaftar.999@gmail.com');
        // Tidak ada email yang dikirim untuk akun yang tidak ada
        Mail::assertNothingSent();
    }

    public function test_otp_page_requires_active_session_and_masks_email(): void
    {
        // 1. Akses tanpa sesi email -> diarahkan kembali ke password.request
        $resNoSession = $this->get(route('password.otp'));
        $resNoSession->assertRedirect(route('password.request'));
        $resNoSession->assertSessionHasErrors('email');

        // 2. Akses dengan sesi email -> tampilkan email tersamar (contoh: t*****@gmail.com)
        $resWithSession = $this->withSession(['reset_email' => 'teguh.purnomo@gmail.com'])
            ->get(route('password.otp'));

        $resWithSession->assertStatus(200);
        $resWithSession->assertSee('t*****@gmail.com');
        $resWithSession->assertDontSee('teguh.purnomo@gmail.com'); // Jangan tampilkan email lengkap
        // Menampilkan petunjuk folder Spam dan pengurus RT (Requirement 6)
        $resWithSession->assertSee('Tidak menerima email? Cek folder Spam. Anda juga bisa menghubungi pengurus RT untuk reset kata sandi.');
    }

    public function test_otp_is_stored_as_hash_and_single_use(): void
    {
        Mail::fake();

        $email = 'teguh.purnomo@gmail.com';
        $ip = '127.0.0.1';

        OtpService::generateAndSendOtp($email, $ip);

        // Ambil hash yang tersimpan
        $hashKey = 'pwd_otp_hash_' . hash('sha256', $email);
        $this->assertTrue(Cache::has($hashKey));

        $storedHash = Cache::get($hashKey);
        // Pastikan bukan 6 digit plaintext
        $this->assertEquals(64, strlen($storedHash)); // sha256 64 hex chars

        // Dapatkan OTP yang dikirim lewat Mail::fake
        $sentOtp = null;
        Mail::assertSent(ResetPasswordOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return strlen($sentOtp) === 6 && ctype_digit($sentOtp);
        });

        $this->assertNotNull($sentOtp);

        // 1. Verifikasi dengan OTP salah -> gagal
        $wrongResult = OtpService::verifyOtp($email, '000000', $ip);
        $this->assertFalse($wrongResult['success']);
        $this->assertNull($wrongResult['token']);

        // 2. Verifikasi dengan OTP benar -> sukses
        $correctResult = OtpService::verifyOtp($email, $sentOtp, $ip);
        $this->assertTrue($correctResult['success']);
        $this->assertNotNull($correctResult['token']);

        // 3. Sekali pakai: verifikasi ulang dengan OTP yang sama langsung gagal
        $reuseResult = OtpService::verifyOtp($email, $sentOtp, $ip);
        $this->assertFalse($reuseResult['success']);
        $this->assertNull($reuseResult['token']);
    }

    public function test_rate_limiting_max_3_resend_and_max_5_failed_attempts(): void
    {
        Mail::fake();
        $email = 'teguh.purnomo@gmail.com';
        $ip = '192.168.1.100';

        // 1. Maksimal 3 kali kirim / kirim ulang
        $this->assertTrue(OtpService::generateAndSendOtp($email, $ip)); // 1
        $this->assertTrue(OtpService::generateAndSendOtp($email, $ip)); // 2
        $this->assertTrue(OtpService::generateAndSendOtp($email, $ip)); // 3
        $this->assertTrue(OtpService::isRateLimited($email, $ip));

        $msg = OtpService::getRateLimitMessage($email, $ip);
        $this->assertStringContainsString('maksimal 3 kali per 10 menit', $msg);

        // Reset rate limit untuk tes salah input
        Cache::flush();

        OtpService::generateAndSendOtp($email, $ip);

        // 2. Maksimal 5 kali salah input
        for ($i = 0; $i < 5; $i++) {
            $res = OtpService::verifyOtp($email, '111111', $ip);
            $this->assertFalse($res['success']);
        }

        $this->assertTrue(OtpService::isRateLimited($email, $ip));
        $limitMsg = OtpService::getRateLimitMessage($email, $ip);
        $this->assertStringContainsString('Terlalu banyak percobaan verifikasi yang salah', $limitMsg);
    }

    public function test_otp_verification_via_http_redirects_to_password_reset(): void
    {
        Mail::fake();

        // Request OTP
        $this->post(route('password.email'), [
            'email' => 'teguh.purnomo@gmail.com',
        ]);

        $sentOtp = null;
        Mail::assertSent(ResetPasswordOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        // Submit form verifikasi OTP
        $verifyRes = $this->withSession(['reset_email' => 'teguh.purnomo@gmail.com'])
            ->post(route('password.otp.verify'), [
                'otp' => $sentOtp,
            ]);

        $verifyRes->assertRedirect();
        $targetUrl = $verifyRes->headers->get('Location');
        $this->assertStringContainsString('/password/reset/', $targetUrl);
        $this->assertStringContainsString('email=teguh.purnomo%40gmail.com', $targetUrl);
    }

    public function test_mailable_content_and_subject(): void
    {
        $mailable = new ResetPasswordOtpMail('739201');

        $this->assertEquals('Kode verifikasi Smart RW', $mailable->subject);

        // Render HTML dan Plain text
        $renderedHtml = $mailable->render();
        $this->assertStringContainsString('739201', $renderedHtml);
        $this->assertStringContainsString('10 menit', $renderedHtml);
        $this->assertStringContainsString('satu kali', $renderedHtml);
        $this->assertStringNotContainsString('<img', $renderedHtml); // Tanpa gambar

        $viewData = $mailable->build();
        $this->assertEquals('emails.otp_html', $viewData->view);
        $this->assertEquals('emails.otp_text', $viewData->textView);
    }

    public function test_artisan_smartrw_test_mail_command(): void
    {
        Mail::fake();

        // 1. Email format tidak valid
        $this->artisan('smartrw:test-mail', ['email' => 'bukan-email'])
            ->expectsOutputToContain('Format alamat email tidak valid')
            ->assertExitCode(1);

        // 2. Email format valid
        $this->artisan('smartrw:test-mail', ['email' => 'uji.coba@smartrw.id'])
            ->expectsOutputToContain('Menguji pengiriman email ke: uji.coba@smartrw.id')
            ->expectsOutputToContain('Sukses! Email percobaan berhasil dikirim')
            ->assertExitCode(0);
    }

    public function test_artisan_smartrw_test_mail_sanitizes_errors_without_exposing_passwords(): void
    {
        // Set fake password di config
        config(['mail.mailers.smtp.password' => 'SuperSecretSmtpPass123!']);

        // Mock Mail agar throw exception yang mengandung password
        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new \Exception('SMTP Connection failed with authentication SuperSecretSmtpPass123! at port 587'));

        $this->artisan('smartrw:test-mail', ['email' => 'error.test@smartrw.id'])
            ->expectsOutputToContain('Gagal mengirim email percobaan')
            ->doesntExpectOutputToContain('SuperSecretSmtpPass123!') // Password harus disensor
            ->assertExitCode(1);
    }

    public function test_env_is_ignored_and_env_example_has_empty_mail_variables(): void
    {
        $gitignore = file_get_contents(base_path('.gitignore'));
        $this->assertMatchesRegularExpression('/^\.env$/m', $gitignore);

        $envExample = file_get_contents(base_path('.env.example'));
        $this->assertStringContainsString("MAIL_MAILER=\n", $envExample);
        $this->assertStringContainsString("MAIL_HOST=\n", $envExample);
        $this->assertStringContainsString("MAIL_PORT=\n", $envExample);
        $this->assertStringContainsString("MAIL_USERNAME=\n", $envExample);
        $this->assertStringContainsString("MAIL_PASSWORD=\n", $envExample);
    }
}
