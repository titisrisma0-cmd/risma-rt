<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordOtpMail;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\OtpService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InteractiveAuthUiAndOtpStepsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $rwUser;
    protected User $rtUser;
    protected User $wargaUser;
    protected Rt $rt1;

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

        if (!Schema::hasTable('rws')) {
            Schema::create('rws', function (Blueprint $table) {
                $table->id();
                $table->string('kode_rw')->unique();
                $table->string('nama_rw')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rts')) {
            Schema::create('rts', function (Blueprint $table) {
                $table->id();
                $table->string('kode_rt')->unique();
                $table->string('nomor_rt')->nullable();
                $table->string('nama_ketua_rt')->nullable();
                $table->foreignId('rw_id')->nullable();
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
                $table->string('no_kk')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        $superAdminRole = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $rwRole = Role::firstOrCreate(['nama_role' => 'rw']);
        $rtRole = Role::firstOrCreate(['nama_role' => 'rt']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);

        $this->rt1 = Rt::firstOrCreate(
            ['kode_rt' => '01'],
            ['nomor_rt' => '01', 'nama_ketua_rt' => 'Ketua RT 01']
        );

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin.test@smartrw.id'],
            [
                'name' => 'Super Admin Test',
                'password' => bcrypt('password123'),
                'role_id' => $superAdminRole->id,
                'is_super_admin' => true,
            ]
        );

        $this->rwUser = User::firstOrCreate(
            ['email' => 'rw.test@smartrw.id'],
            [
                'name' => 'Ketua RW Test',
                'password' => bcrypt('password123'),
                'role_id' => $rwRole->id,
                'is_super_admin' => false,
            ]
        );

        $this->rtUser = User::firstOrCreate(
            ['email' => 'rt.test@smartrw.id'],
            [
                'name' => 'Ketua RT Test',
                'password' => bcrypt('password123'),
                'role_id' => $rtRole->id,
                'rt_id' => $this->rt1->id,
                'is_super_admin' => false,
            ]
        );

        $this->wargaUser = User::firstOrCreate(
            ['email' => 'warga.test@smartrw.id'],
            [
                'name' => 'Warga Test',
                'password' => bcrypt('password123'),
                'role_id' => $wargaRole->id,
                'rt_id' => $this->rt1->id,
                'is_super_admin' => false,
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $this->wargaUser->id],
            [
                'role_id' => $wargaRole->id,
                'rt_id' => $this->rt1->id,
                'nama_lengkap' => 'Warga Test',
                'nik' => '3201010101010001',
                'no_kk' => '3201010101010002',
                'status' => 'aktif',
            ]
        );
    }

    /**
     * Pastikan halaman login memuat elemen visual ala WhatsApp & Motion Graphic Hub
     */
    public function test_login_page_renders_whatsapp_theme_and_hub_motion_graphic(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('SMART RW 01');
        $response->assertSee('Masuk ke Akun');
        $response->assertSee('Pendaftaran Warga');
        $response->assertSee('Lupa Kata Sandi');

        // Hub Motion Graphic Elements
        $response->assertSee('Terhubung Real-Time');
        $response->assertSee('RW 01 Digital');
        $response->assertSee('Surat Pengantar');
        $response->assertSee('Disetujui Ketua RT');
        $response->assertSee('Kas & Iuran RT', false);
        $response->assertSee('Ronda & CCTV', false);
        $response->assertSee('Notifikasi Warga');

        // 3-Step Forgot Password Elements
        $response->assertSee('Kirim Kode OTP');
        $response->assertSee('Verifikasi OTP');
        $response->assertSee('Simpan Kata Sandi Baru');
    }

    /**
     * Alur Lupa Kata Sandi 3 Tahap Berurutan via AJAX/JSON:
     * Tahap 1: Kirim OTP ke email
     * Tahap 2: Verifikasi 6 digit OTP (Wajib Valid)
     * Tahap 3: Update kata sandi baru
     */
    public function test_forgot_password_three_step_sequential_flow_via_json(): void
    {
        Mail::fake();

        // ===== TAHAP 1: User memasukkan email terdaftar =====
        $resStep1 = $this->postJson(route('password.email'), [
            'email' => 'warga.test@smartrw.id',
        ]);

        $resStep1->assertStatus(200);
        $resStep1->assertJson([
            'success' => true,
            'step' => 2,
            'masked_email' => 'w*****@smartrw.id',
        ]);

        $otpSent = null;
        Mail::assertSent(ResetPasswordOtpMail::class, function ($mail) use (&$otpSent) {
            $otpSent = $mail->otp;
            return $mail->hasTo('warga.test@smartrw.id');
        });

        $this->assertNotNull($otpSent);
        $this->assertEquals(6, strlen($otpSent));

        // ===== TAHAP 2: Verifikasi OTP Salah -> Gagal (TIDAK BOLEH LANJUT) =====
        $resWrongOtp = $this->postJson(route('password.otp.verify'), [
            'email' => 'warga.test@smartrw.id',
            'otp' => '000000',
        ]);

        $resWrongOtp->assertStatus(422);
        $resWrongOtp->assertJsonStructure(['errors' => ['otp']]);

        // ===== TAHAP 2: Verifikasi OTP Benar -> Sukses & Berikan Token Reset =====
        $resCorrectOtp = $this->postJson(route('password.otp.verify'), [
            'email' => 'warga.test@smartrw.id',
            'otp' => $otpSent,
        ]);

        $resCorrectOtp->assertStatus(200);
        $resCorrectOtp->assertJson([
            'success' => true,
            'step' => 3,
        ]);

        $resetToken = $resCorrectOtp->json('token');
        $this->assertNotEmpty($resetToken);

        // ===== TAHAP 3: Update Kata Sandi Baru dengan Token Valid =====
        $resStep3 = $this->postJson(route('password.update'), [
            'token' => $resetToken,
            'email' => 'warga.test@smartrw.id',
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ]);

        $resStep3->assertStatus(200);
        $resStep3->assertJson([
            'success' => true,
        ]);

        // Verifikasi kata sandi di database benar-benar berubah
        $this->wargaUser->refresh();
        $this->assertTrue(Hash::check('newPassword123!', $this->wargaUser->password));
    }

    /**
     * Memastikan Tahap 3 (Update Password) ditolak jika token tidak valid / belum verifikasi OTP
     */
    public function test_step_three_rejects_without_valid_otp_verification(): void
    {
        $res = $this->postJson(route('password.update'), [
            'token' => 'invalid-token-without-otp',
            'email' => 'warga.test@smartrw.id',
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ]);

        $res->assertStatus(422);
        $this->wargaUser->refresh();
        $this->assertTrue(Hash::check('password123', $this->wargaUser->password));
    }

    /**
     * Pastikan redirect role (Super Admin, RW, RT, Warga) tetap 100% terjaga
     */
    public function test_login_role_redirects_are_preserved(): void
    {
        // 1. Super Admin -> /superadmin/dashboard
        $resSuperAdmin = $this->post(route('login'), [
            'email' => 'superadmin.test@smartrw.id',
            'password' => 'password123',
        ]);
        $resSuperAdmin->assertRedirect(route('superadmin.dashboard'));
        auth()->logout();

        // 2. RW -> /dashboard
        $resRw = $this->post(route('login'), [
            'email' => 'rw.test@smartrw.id',
            'password' => 'password123',
        ]);
        $resRw->assertRedirect(route('dashboard'));
        auth()->logout();

        // 3. RT -> /dashboard
        $resRt = $this->post(route('login'), [
            'email' => 'rt.test@smartrw.id',
            'password' => 'password123',
        ]);
        $resRt->assertRedirect(route('dashboard'));
        auth()->logout();

        // 4. Warga Aktif -> /dashboard
        $resWarga = $this->post(route('login'), [
            'email' => 'warga.test@smartrw.id',
            'password' => 'password123',
        ]);
        $resWarga->assertRedirect(route('dashboard'));
        auth()->logout();
    }
}
