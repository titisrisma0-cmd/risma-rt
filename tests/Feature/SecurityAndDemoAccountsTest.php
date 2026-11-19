<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordOtpMail;
use App\Models\Role;
use App\Models\Rt;
use App\Models\User;
use App\Models\UserProfile;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityAndDemoAccountsTest extends TestCase
{
    use DatabaseTransactions;

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
                $table->string('jenis_kelamin', 2)->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false);
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('rts')) {
            Schema::create('rts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rw_id')->nullable();
                $table->string('kode_rt')->nullable();
                $table->string('nama_rt')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('no_kk')->nullable();
                $table->string('nik')->nullable();
                $table->string('nama_lengkap')->nullable();
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->string('jenis_kelamin', 2)->nullable();
                $table->text('alamat')->nullable();
                $table->string('no_hp')->nullable();
                $table->string('pekerjaan')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }
    }

    /**
     * Requirement 1: Setelah reset kata sandi berhasil, arahkan ke halaman login
     * dengan pesan sukses "Kata sandi berhasil diubah, silakan masuk". Jangan ke /home.
     */
    public function test_reset_password_redirects_to_login_with_success_message(): void
    {
        $user = User::create([
            'name' => 'Warga Reset Test',
            'email' => 'warga.reset.' . uniqid() . '@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Harus diarahkan ke login, BUKAN ke /home
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Kata sandi berhasil diubah, silakan masuk');

        // Pastikan password di database terupdate
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        // Pengguna tidak otomatis login (tetap sebagai tamu)
        $this->assertFalse(auth()->check());
    }

    /**
     * Requirement 1: Periksa penyebab 404 di /home:
     * Pengguna login diarahkan ke dashboard sesuai peran, dan tamu ke login.
     */
    public function test_home_route_redirects_guest_to_login(): void
    {
        $response = $this->get('/home');
        $response->assertRedirect(route('login'));
    }

    public function test_home_route_redirects_authenticated_user_to_dashboard(): void
    {
        $user = User::create([
            'name' => 'User Auth Test',
            'email' => 'user.auth.' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'is_super_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/home');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_home_route_redirects_superadmin_to_superadmin_dashboard(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin.' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($superadmin)->get('/home');
        $response->assertRedirect(route('superadmin.dashboard'));
    }

    /**
     * Requirement 2: Template email OTP responsif di HP, tabel, font 28-32px, no fixed px width
     */
    public function test_email_otp_template_table_layout_and_responsive(): void
    {
        $mailable = new ResetPasswordOtpMail('542891');
        $html = $mailable->render();

        // 1. Menggunakan tata letak berbasis tabel
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('role="presentation"', $html);

        // 2. Kode OTP 6 digit ditampilkan
        $this->assertStringContainsString('542891', $html);

        // 3. Font size 28-32px dan letter-spacing sedang
        $this->assertMatchesRegularExpression('/font-size:\s*(2[8-9]|3[0-2])px/', $html);
        $this->assertMatchesRegularExpression('/letter-spacing:\s*[4-8]px/', $html);

        // 4. Kotak memakai width 100%, text-align center, tanpa lebar piksel tetap
        $this->assertStringContainsString('width: 100%', $html);
        $this->assertStringContainsString('text-align: center', $html);
        $this->assertStringNotContainsString('width: 500px', $html);
        $this->assertStringNotContainsString('width: 400px', $html);

        // 5. Dukungan mode gelap (color-scheme & dark media query)
        $this->assertStringContainsString('color-scheme: light dark', $html);
        $this->assertStringContainsString('prefers-color-scheme: dark', $html);

        // 6. Masa berlaku 10 menit dan sekali pakai
        $this->assertStringContainsString('10 menit', $html);
        $this->assertStringContainsString('satu kali', $html);
    }

    /**
     * Requirement 3: Halaman login tidak memiliki bagian akun pengujian cepat dan kredensial
     */
    public function test_login_page_does_not_contain_demo_account_chips_or_credentials(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);

        // Tidak boleh ada teks toolbar demo
        $response->assertDontSee('Akun Pengujian Cepat');
        $response->assertDontSee('titisrisma0@gmail.com');
        $response->assertDontSee('admin@rw-smart.test');
        $response->assertDontSee('rt01@rw-smart.test');
        $response->assertDontSee('warga@rw-smart.test');
        $response->assertDontSee('password123');
        $response->assertDontSee('fillLogin');

        // Pastikan alert status reset kata sandi bisa tampil di login
        $resWithStatus = $this->withSession(['status' => 'Kata sandi berhasil diubah, silakan masuk'])
            ->get(route('login'));
        $resWithStatus->assertSee('Kata sandi berhasil diubah, silakan masuk');
    }

    /**
     * Requirement 4: Perintah php artisan smartrw:demo-accounts (dry-run dan --disable)
     */
    public function test_artisan_demo_accounts_dry_run_does_not_modify_accounts(): void
    {
        $demoUser = User::create([
            'name' => 'Demo User Test',
            'email' => 'test_dryrun_' . uniqid() . '@rw-smart.test',
            'password' => Hash::make('originalpassword'),
        ]);

        UserProfile::create([
            'user_id' => $demoUser->id,
            'nama_lengkap' => 'Demo User Test',
            'status' => 'aktif',
        ]);

        $this->artisan('smartrw:demo-accounts')
            ->expectsOutputToContain('Mode Simulasi (Dry-run)')
            ->expectsOutputToContain($demoUser->email)
            ->assertExitCode(0);

        // Pastikan password dan status tetap utuh
        $demoUser->refresh();
        $this->assertTrue(Hash::check('originalpassword', $demoUser->password));
        $this->assertEquals('aktif', $demoUser->profile->status);
    }

    public function test_artisan_demo_accounts_disable_scrambles_password_and_disables_profile(): void
    {
        $demoUser = User::create([
            'name' => 'Demo User Disable',
            'email' => 'test_disable_' . uniqid() . '@rw-smart.test',
            'password' => Hash::make('originalpassword'),
        ]);

        $profile = UserProfile::create([
            'user_id' => $demoUser->id,
            'nama_lengkap' => 'Demo User Disable',
            'status' => 'aktif',
        ]);

        $realUser = User::create([
            'name' => 'Real Resident',
            'email' => 'real.resident.' . uniqid() . '@gmail.com',
            'password' => Hash::make('realpassword'),
        ]);

        $realProfile = UserProfile::create([
            'user_id' => $realUser->id,
            'nama_lengkap' => 'Real Resident',
            'status' => 'aktif',
        ]);

        $this->artisan('smartrw:demo-accounts', ['--disable' => true])
            ->expectsOutputToContain('berhasil dinonaktifkan dan kata sandinya telah diacak')
            ->assertExitCode(0);

        // Akun demo diacak kata sandinya dan statusnya nonaktif
        $demoUser->refresh();
        $this->assertFalse(Hash::check('originalpassword', $demoUser->password));
        $this->assertEquals('nonaktif', $demoUser->profile->status);

        // Akun nyata di luar @rw-smart.test TIDAK tersentuh sama sekali
        $realUser->refresh();
        $this->assertTrue(Hash::check('realpassword', $realUser->password));
        $this->assertEquals('aktif', $realUser->profile->status);
    }

    /**
     * Requirement 4: Seeder tidak boleh membuat akun demo jika APP_ENV=production
     */
    public function test_seeders_do_not_create_demo_accounts_in_production(): void
    {
        $originalEnv = app()->environment();
        app()['env'] = 'production';

        try {
            // Jalankan UserSeeder pada mode production
            $seeder = new UserSeeder();
            $seeder->run();

            // Pastikan tidak ada akun baru yang berakhiran @rw-smart.test dibuat
            $demoCount = User::where('email', 'like', '%@rw-smart.test')->count();
            $this->assertEquals(0, $demoCount);

            // Jalankan DemoDataSeeder pada mode production
            $demoSeeder = new DemoDataSeeder();
            $demoSeeder->run();

            $demoCountAfter = User::where('email', 'like', '%@rw-smart.test')->count();
            $this->assertEquals(0, $demoCountAfter);
        } finally {
            app()['env'] = $originalEnv;
        }
    }
}
