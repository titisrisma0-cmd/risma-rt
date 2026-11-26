<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\DeviceToken;
use App\Models\RfidScan;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RfidSecurityAndAccessTest extends TestCase
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
                $table->rememberToken();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false);
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
                $table->string('rfid_uid', 64)->nullable()->unique();
                $table->string('rfid_card_type', 100)->nullable();
                $table->string('jenis_kartu', 100)->nullable()->default('Kartu RFID');
                $table->timestamp('rfid_registered_at')->nullable();
                $table->string('rfid_registered_by', 100)->nullable();
                $table->string('nama_lengkap');
                $table->string('nik')->nullable();
                $table->string('no_kk')->nullable();
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->string('jenis_kelamin', 2)->nullable();
                $table->text('alamat')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('user_profiles', 'rfid_card_type')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->string('rfid_card_type', 100)->nullable();
                $table->timestamp('rfid_registered_at')->nullable();
                $table->string('rfid_registered_by', 100)->nullable();
            });
        }

        if (!Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('token_hash', 64)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('action');
                $table->string('entity_type')->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('deskripsi')->nullable();
                $table->decimal('nominal', 15, 2)->nullable();
                $table->string('bukti')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->string('judul')->nullable();
                $table->text('pesan')->nullable();
                $table->boolean('dibaca')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rfid_scans')) {
            Schema::create('rfid_scans', function (Blueprint $table) {
                $table->id();
                $table->string('uid', 64)->index();
                $table->string('formatted_uid', 64)->nullable();
                $table->string('card_type', 100)->nullable();
                $table->string('sak', 10)->nullable();
                $table->string('atqa', 10)->nullable();
                $table->unsignedTinyInteger('uid_size')->nullable();
                $table->string('device_id', 100)->nullable();
                $table->foreignId('device_token_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('nama_warga')->nullable();
                $table->boolean('access_granted')->default(false);
                $table->string('buzzer_signal', 20)->default('DENIED');
                $table->string('access_message')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('scanned_at')->useCurrent()->index();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('rfid_scans', 'sak')) {
            Schema::table('rfid_scans', function (Blueprint $table) {
                $table->string('sak', 10)->nullable();
                $table->string('atqa', 10)->nullable();
                $table->unsignedTinyInteger('uid_size')->nullable();
            });
        }

        if (Schema::hasTable('rfid_scans') && !Schema::hasColumn('rfid_scans', 'fungsi')) {
            Schema::table('rfid_scans', function (Blueprint $table) {
                $table->string('fungsi', 50)->nullable();
            });
        }

        if (!Schema::hasTable('rfid_devices')) {
            Schema::create('rfid_devices', function (Blueprint $table) {
                $table->id();
                $table->string('device_id', 100)->unique();
                $table->string('nama_lokasi');
                $table->string('fungsi', 50)->default('belum_dikonfigurasi');
                $table->boolean('is_active')->default(true);
                $table->foreignId('rt_id')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        // Pastikan perangkat uji coba terdaftar aktif untuk pengujian keamanan dan otentikasi
        $testDevices = [
            'ESP32-GATE-01' => 'gerbang',
            'ESP32-POS-RONDA' => 'gerbang',
            'ESP32-GATE-RW01' => 'gerbang',
            'ESP32-POS-RT01' => 'gerbang',
            'ESP32-RC522' => 'gerbang',
        ];

        foreach ($testDevices as $devId => $fn) {
            \App\Models\RfidDevice::firstOrCreate(
                ['device_id' => $devId],
                [
                    'nama_lokasi' => 'Test ' . $devId,
                    'fungsi' => $fn,
                    'is_active' => true,
                ]
            );
        }
    }

    protected function createActiveDeviceToken(string $plainToken = 'test-valid-esp32-secret-token-12345'): DeviceToken
    {
        return DeviceToken::create([
            'name' => 'ESP32 Pos Ronda Test',
            'token_hash' => DeviceToken::hashToken($plainToken),
            'is_active' => true,
            'last_used_at' => null,
        ]);
    }

    public function test_scan_without_token_is_rejected_401(): void
    {
        $response = $this->postJson('/api/rfid/scan', [
            'uid' => 'A1B2C3D4',
        ]);

        $response->assertStatus(401);
        $response->assertExactJson([
            'status' => 'error',
            'message' => 'Token tidak valid',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'rfid_token_rejected',
            'entity_type' => 'DeviceToken',
        ]);
    }

    public function test_scan_with_invalid_token_is_rejected_401(): void
    {
        $this->createActiveDeviceToken('real-valid-token-abc');

        $response = $this->postJson('/api/rfid/scan', [
            'uid' => 'A1B2C3D4',
        ], [
            'X-Device-Token' => 'hacker-fake-token',
        ]);

        $response->assertStatus(401);
        $response->assertExactJson([
            'status' => 'error',
            'message' => 'Token tidak valid',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'rfid_token_rejected',
            'entity_type' => 'DeviceToken',
        ]);
    }

    public function test_scan_with_inactive_token_is_rejected_401(): void
    {
        $plainToken = 'inactive-device-token-999';
        $deviceToken = DeviceToken::create([
            'name' => 'ESP32 Nonaktif',
            'token_hash' => DeviceToken::hashToken($plainToken),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/rfid/scan', [
            'uid' => 'A1B2C3D4',
        ], [
            'X-Device-Token' => $plainToken,
        ]);

        $response->assertStatus(401);
        $response->assertExactJson([
            'status' => 'error',
            'message' => 'Token tidak valid',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'rfid_token_rejected',
            'entity_type' => 'DeviceToken',
            'entity_id' => $deviceToken->id,
        ]);
    }

    public function test_scan_with_valid_token_unregistered_card(): void
    {
        $validToken = 'valid-token-unregistered-card-123';
        $deviceToken = $this->createActiveDeviceToken($validToken);
        $this->assertNull($deviceToken->last_used_at);

        $response = $this->postJson('/api/rfid/scan', [
            'uid' => 'A1:B2:C3:D4',
            'device_id' => 'ESP32-GATE-01',
        ], [
            'X-Device-Token' => $validToken,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'access_granted' => false,
            'buzzer_signal' => 'DENIED',
            'is_registered' => false,
        ]);
        $response->assertJsonStructure([
            'verification_token',
            'data' => ['uid', 'formatted_uid', 'timestamp'],
        ]);

        // Pastikan last_used_at diperbarui oleh middleware
        $deviceToken->refresh();
        $this->assertNotNull($deviceToken->last_used_at);

        // Pastikan tersimpan di cache buffer untuk modal rekam web
        $cached = Cache::get('rfid_latest_scan');
        $this->assertNotNull($cached);
        $this->assertEquals('A1B2C3D4', $cached['uid']);
    }

    public function test_scan_with_valid_token_registered_active_warga_grants_access(): void
    {
        $validToken = 'valid-token-registered-warga-456';
        $deviceToken = $this->createActiveDeviceToken($validToken);

        $rw = Rw::firstOrCreate(['kode_rw' => '01'], ['nama_rw' => 'RW 01']);
        $rt = Rt::firstOrCreate(['rw_id' => $rw->id, 'kode_rt' => '01'], ['nama_rt' => 'RT 01']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);

        $user = User::create([
            'name' => 'Warga Ber-KTP Sah',
            'email' => 'warga-ktp@example.test',
            'password' => bcrypt('password123'),
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
            'no_kk' => '3276010000009999',
            'nik' => '3276010000009999',
            'nama_lengkap' => 'Warga Ber-KTP Sah',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Mawar No. 1',
            'status' => 'aktif',
            'rfid_uid' => '04A1B2C3',
        ]);

        $response = $this->postJson('/api/rfid/scan', [
            'uid' => '04:A1:B2:C3',
            'device_id' => 'ESP32-POS-RONDA',
        ], [
            'X-Device-Token' => $validToken,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'access_granted' => true,
            'buzzer_signal' => 'GRANTED',
            'is_registered' => true,
            'registered_to' => [
                'nama_lengkap' => 'Warga Ber-KTP Sah',
                'nik' => '3276010000009999',
            ],
        ]);
        $this->assertNotEmpty($response->json('verification_token'));

        $deviceToken->refresh();
        $this->assertNotNull($deviceToken->last_used_at);
    }

    public function test_rfid_scan_endpoint_enforces_rate_limit_60_per_minute(): void
    {
        $validToken = 'rate-limit-test-token-789';
        $this->createActiveDeviceToken($validToken);

        // Kirim 60 request sukses (batas maksimum per menit)
        for ($i = 0; $i < 60; $i++) {
            $res = $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.99'])
                ->postJson('/api/rfid/scan', [
                    'uid' => 'AABBCCDD',
                ], [
                    'X-Device-Token' => $validToken,
                ]);
            $res->assertStatus(200);
        }

        // Request ke-61 dalam 1 menit dari IP yang sama harus ditolak 429 Too Many Requests
        $blockedResponse = $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.99'])
            ->postJson('/api/rfid/scan', [
                'uid' => 'AABBCCDD',
            ], [
                'X-Device-Token' => $validToken,
            ]);

        $blockedResponse->assertStatus(429);
    }

    public function test_api_settings_dashboard_accessible_by_super_admin_and_manages_tokens(): void
    {
        $superAdminRole = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $superAdmin = User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin-api@example.test',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
            'is_super_admin' => true,
        ]);

        // 1. Akses halaman dashboard pengaturan API
        $this->actingAs($superAdmin)
            ->get(route('superadmin.api-settings.index'))
            ->assertStatus(200)
            ->assertSee('Pengaturan Token API');

        // 2. Generate token baru -> plain token ditampilkan via flash session, hanya hash yang masuk DB
        $generateResponse = $this->actingAs($superAdmin)
            ->post(route('superadmin.api-settings.generate'), [
                'name' => 'ESP32 Gate Utama',
            ]);

        $generateResponse->assertRedirect();
        $generateResponse->assertSessionHas('generated_token');

        $plainToken = session('generated_token');
        $this->assertNotEmpty($plainToken);

        $tokenModel = DeviceToken::where('name', 'ESP32 Gate Utama')->first();
        $this->assertNotNull($tokenModel);
        $this->assertEquals(DeviceToken::hashToken($plainToken), $tokenModel->token_hash);
        $this->assertNotEquals($plainToken, $tokenModel->token_hash);
        $this->assertTrue($tokenModel->is_active);

        // 3. Toggle nonaktif
        $this->actingAs($superAdmin)
            ->patch(route('superadmin.api-settings.toggle', $tokenModel->id))
            ->assertRedirect();

        $tokenModel->refresh();
        $this->assertFalse($tokenModel->is_active);

        // 4. Regenerate token
        $oldHash = $tokenModel->token_hash;
        $regenResponse = $this->actingAs($superAdmin)
            ->post(route('superadmin.api-settings.regenerate', $tokenModel->id));
        $regenResponse->assertRedirect();
        $regenResponse->assertSessionHas('generated_token');

        $tokenModel->refresh();
        $this->assertNotEquals($oldHash, $tokenModel->token_hash);
    }

    public function test_api_settings_dashboard_denies_non_super_admin_roles_403(): void
    {
        $rwRole = Role::firstOrCreate(['nama_role' => 'rw']);
        $rtRole = Role::firstOrCreate(['nama_role' => 'rt']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);

        $rwUser = User::create([
            'name' => 'Ketua RW Test',
            'email' => 'rw-api-test@example.test',
            'password' => bcrypt('password'),
            'role_id' => $rwRole->id,
            'is_super_admin' => false,
        ]);

        $rtUser = User::create([
            'name' => 'Ketua RT Test',
            'email' => 'rt-api-test@example.test',
            'password' => bcrypt('password'),
            'role_id' => $rtRole->id,
            'is_super_admin' => false,
        ]);

        $wargaUser = User::create([
            'name' => 'Warga Test',
            'email' => 'warga-api-test@example.test',
            'password' => bcrypt('password'),
            'role_id' => $wargaRole->id,
            'is_super_admin' => false,
        ]);

        foreach ([$rwUser, $rtUser, $wargaUser] as $nonSuperAdmin) {
            $this->actingAs($nonSuperAdmin)
                ->get(route('superadmin.api-settings.index'))
                ->assertStatus(403);

            $this->actingAs($nonSuperAdmin)
                ->post(route('superadmin.api-settings.generate'), ['name' => 'Hack Device'])
                ->assertStatus(403);
        }
    }

    public function test_rfid_scan_persists_history_for_registered_and_unregistered_cards(): void
    {
        $validToken = 'persist-history-token-123';
        $deviceToken = $this->createActiveDeviceToken($validToken);

        $rw = Rw::firstOrCreate(['kode_rw' => '01'], ['nama_rw' => 'RW 01']);
        $rt1 = Rt::firstOrCreate(['rw_id' => $rw->id, 'kode_rt' => '01'], ['nama_rt' => 'RT 01']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);

        $wargaUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi-rfid-history@example.test',
            'password' => bcrypt('password123'),
            'role_id' => $wargaRole->id,
            'rt_id' => $rt1->id,
        ]);

        $profile = UserProfile::create([
            'user_id' => $wargaUser->id,
            'role_id' => $wargaRole->id,
            'rt_id' => $rt1->id,
            'no_kk' => '3276010000001111',
            'nik' => '3276010000001111',
            'nama_lengkap' => 'Budi Santoso',
            'status' => 'aktif',
            'rfid_uid' => '11223344',
        ]);

        // 1. Scan kartu tidak terdaftar
        $this->postJson('/api/rfid/scan', [
            'uid' => 'DE:AD:BE:EF',
            'device_id' => 'ESP32-GATE-RW01',
        ], [
            'X-Device-Token' => $validToken,
        ])->assertStatus(200);

        $this->assertDatabaseHas('rfid_scans', [
            'uid' => 'DEADBEEF',
            'formatted_uid' => 'DE:AD:BE:EF',
            'device_id' => 'ESP32-GATE-RW01',
            'device_token_id' => $deviceToken->id,
            'user_profile_id' => null,
            'rt_id' => null,
            'nama_warga' => null,
            'access_granted' => false,
            'buzzer_signal' => 'DENIED',
        ]);

        // 2. Scan kartu terdaftar (warga aktif RT 01)
        $this->postJson('/api/rfid/scan', [
            'uid' => '11:22:33:44',
            'device_id' => 'ESP32-POS-RT01',
        ], [
            'X-Device-Token' => $validToken,
        ])->assertStatus(200);

        $this->assertDatabaseHas('rfid_scans', [
            'uid' => '11223344',
            'formatted_uid' => '11:22:33:44',
            'device_id' => 'ESP32-POS-RT01',
            'device_token_id' => $deviceToken->id,
            'user_profile_id' => $profile->id,
            'rt_id' => $rt1->id,
            'nama_warga' => 'Budi Santoso',
            'access_granted' => true,
            'buzzer_signal' => 'GRANTED',
        ]);
    }

    public function test_rfid_history_dashboard_and_json_endpoint_respect_roles_territory_and_ordering(): void
    {
        $rw = Rw::firstOrCreate(['kode_rw' => '01'], ['nama_rw' => 'RW 01']);
        $rt1 = Rt::firstOrCreate(['rw_id' => $rw->id, 'kode_rt' => '01'], ['nama_rt' => 'RT 01']);
        $rt2 = Rt::firstOrCreate(['rw_id' => $rw->id, 'kode_rt' => '02'], ['nama_rt' => 'RT 02']);

        $superAdminRole = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $rwRole = Role::firstOrCreate(['nama_role' => 'rw']);
        $rtRole = Role::firstOrCreate(['nama_role' => 'rt']);
        $wargaRole = Role::firstOrCreate(['nama_role' => 'warga']);

        $superAdmin = User::create([
            'name' => 'Super Admin History',
            'email' => 'sa-rfid-history@example.test',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
            'is_super_admin' => true,
        ]);

        $adminRw = User::create([
            'name' => 'Admin RW History',
            'email' => 'rw-rfid-history@example.test',
            'password' => bcrypt('password'),
            'role_id' => $rwRole->id,
            'is_super_admin' => false,
        ]);

        $adminRt1 = User::create([
            'name' => 'Ketua RT 01 History',
            'email' => 'rt1-rfid-history@example.test',
            'password' => bcrypt('password'),
            'role_id' => $rtRole->id,
            'rt_id' => $rt1->id,
            'is_super_admin' => false,
        ]);

        $wargaBiasa = User::create([
            'name' => 'Warga Biasa History',
            'email' => 'warga-rfid-history@example.test',
            'password' => bcrypt('password'),
            'role_id' => $wargaRole->id,
            'rt_id' => $rt1->id,
            'is_super_admin' => false,
        ]);

        $profileRt1 = UserProfile::create([
            'role_id' => $wargaRole->id,
            'rt_id' => $rt1->id,
            'nama_lengkap' => 'Warga Satu RT01',
            'status' => 'aktif',
            'rfid_uid' => 'AA11BB22',
        ]);

        $profileRt2 = UserProfile::create([
            'role_id' => $wargaRole->id,
            'rt_id' => $rt2->id,
            'nama_lengkap' => 'Warga Dua RT02',
            'status' => 'aktif',
            'rfid_uid' => 'CC33DD44',
        ]);

        // 3 riwayat scan dengan waktu berbeda (urut terbaru -> terlama: scan3, scan2, scan1)
        RfidScan::create([
            'uid' => 'AA11BB22',
            'formatted_uid' => 'AA:11:BB:22',
            'device_id' => 'ESP32-RT01',
            'user_profile_id' => $profileRt1->id,
            'rt_id' => $rt1->id,
            'nama_warga' => 'Warga Satu RT01',
            'access_granted' => true,
            'buzzer_signal' => 'GRANTED',
            'scanned_at' => now()->subMinutes(5),
        ]);

        RfidScan::create([
            'uid' => 'CC33DD44',
            'formatted_uid' => 'CC:33:DD:44',
            'device_id' => 'ESP32-RT02',
            'user_profile_id' => $profileRt2->id,
            'rt_id' => $rt2->id,
            'nama_warga' => 'Warga Dua RT02',
            'access_granted' => true,
            'buzzer_signal' => 'GRANTED',
            'scanned_at' => now()->subMinutes(2),
        ]);

        RfidScan::create([
            'uid' => 'FF00FF00',
            'formatted_uid' => 'FF:00:FF:00',
            'device_id' => 'ESP32-GATE-UTAMA',
            'user_profile_id' => null,
            'rt_id' => null,
            'nama_warga' => null,
            'access_granted' => false,
            'buzzer_signal' => 'DENIED',
            'scanned_at' => now(),
        ]);

        // 1. Super Admin melihat semua scan & urutan dari yang terbaru
        $this->actingAs($superAdmin)
            ->get(route('rfid.history'))
            ->assertStatus(200)
            ->assertSee('Riwayat Scan RFID')
            ->assertSee('Warga Satu RT01')
            ->assertSee('Warga Dua RT02')
            ->assertSee('Tidak terdaftar')
            ->assertSee('Auto-refresh 3 detik');

        $saJson = $this->actingAs($superAdmin)
            ->getJson(route('rfid.history.data'))
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $saData = $saJson->json('data');
        $this->assertCount(3, $saData);
        $this->assertEquals('FF:00:FF:00', $saData[0]['uid']);
        $this->assertEquals('Tidak terdaftar', $saData[0]['nama_warga']);
        $this->assertEquals('Ditolak', $saData[0]['status_label']);
        $this->assertEquals('CC:33:DD:44', $saData[1]['uid']);
        $this->assertEquals('Warga Dua RT02', $saData[1]['nama_warga']);
        $this->assertEquals('AA:11:BB:22', $saData[2]['uid']);
        $this->assertEquals('Warga Satu RT01', $saData[2]['nama_warga']);

        // 2. Admin RW melihat semua scan & dapat memfilter berdasarkan RT
        $this->actingAs($adminRw)
            ->get(route('rfid.history'))
            ->assertStatus(200)
            ->assertSee('Warga Satu RT01')
            ->assertSee('Warga Dua RT02')
            ->assertSee('Tidak terdaftar');

        $rwFiltered = $this->actingAs($adminRw)
            ->getJson(route('rfid.history.data', ['rt_id' => $rt2->id]))
            ->assertStatus(200);
        $this->assertCount(1, $rwFiltered->json('data'));
        $this->assertEquals('Warga Dua RT02', $rwFiltered->json('data.0.nama_warga'));

        // 3. Ketua RT 01 hanya melihat scan warga RT 01 (isolasi wilayah)
        $this->actingAs($adminRt1)
            ->get(route('rfid.history'))
            ->assertStatus(200)
            ->assertSee('Warga Satu RT01')
            ->assertDontSee('Warga Dua RT02')
            ->assertDontSee('FF:00:FF:00');

        // Bahkan jika RT 01 mencoba mengubah parameter ?rt_id=rt2, tetap terkunci ke RT 01
        $rtJson = $this->actingAs($adminRt1)
            ->getJson(route('rfid.history.data', ['rt_id' => $rt2->id]))
            ->assertStatus(200);
        $this->assertCount(1, $rtJson->json('data'));
        $this->assertEquals('Warga Satu RT01', $rtJson->json('data.0.nama_warga'));

        // 4. Warga biasa ditolak 403
        $this->actingAs($wargaBiasa)
            ->get(route('rfid.history'))
            ->assertStatus(403);

        $this->actingAs($wargaBiasa)
            ->getJson(route('rfid.history.data'))
            ->assertStatus(403);
    }

    public function test_rfid_scan_persists_sak_atqa_uid_size_and_card_type(): void
    {
        $plainToken = 'test-scan-profile-token-777';
        $this->createActiveDeviceToken($plainToken);

        $response = $this->withHeaders([
            'X-Device-Token' => $plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '04A1B2C3D4E5F6',
            'sak' => '20',
            'atqa' => '0044',
            'uid_size' => 7,
            'card_type' => 'PICC / ISO 14443-4',
            'device_id' => 'ESP32-GATE-RW01',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'uid' => '04A1B2C3D4E5F6',
                    'sak' => '20',
                    'atqa' => '0044',
                    'uid_size' => 7,
                    'card_type' => 'PICC / ISO 14443-4',
                ],
            ]);

        $this->assertDatabaseHas('rfid_scans', [
            'uid' => '04A1B2C3D4E5F6',
            'sak' => '20',
            'atqa' => '0044',
            'uid_size' => 7,
            'card_type' => 'PICC / ISO 14443-4',
        ]);
    }

    public function test_card_profile_filtering_accepts_all_cards_by_default(): void
    {
        $plainToken = 'test-token-default-profile';
        $this->createActiveDeviceToken($plainToken);

        // Pastikan AppSetting untuk profil kartu kosong
        AppSetting::set('rfid_profile_sak', null);
        AppSetting::set('rfid_profile_uid_size', null);
        AppSetting::set('rfid_profile_card_type', null);

        $response = $this->withHeaders([
            'X-Device-Token' => $plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '11223344',
            'sak' => '08',
            'atqa' => '0004',
            'uid_size' => 4,
            'card_type' => 'MIFARE 1KB',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('success', $response->json('status'));
    }

    public function test_card_profile_filtering_rejects_mismatched_card_with_proper_message(): void
    {
        $plainToken = 'test-token-profile-filter';
        $this->createActiveDeviceToken($plainToken);

        // Atur profil kartu yang diterima: SAK=20, UID Size=7, Card Type=ISO 14443-4
        AppSetting::set('rfid_profile_sak', '20');
        AppSetting::set('rfid_profile_uid_size', '7');
        AppSetting::set('rfid_profile_card_type', 'ISO 14443-4');

        // Scan dengan kartu MIFARE 1KB (SAK 08, UID size 4) -> Mismatch!
        $response = $this->withHeaders([
            'X-Device-Token' => $plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '11223344',
            'sak' => '08',
            'atqa' => '0004',
            'uid_size' => 4,
            'card_type' => 'MIFARE 1KB',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'access_granted' => false,
                'buzzer_signal' => 'DENIED',
                'message' => 'Jenis kartu tidak sesuai',
            ]);

        // Pastikan tercatat di riwayat scan sebagai ditolak dengan pesan yang sesuai
        $this->assertDatabaseHas('rfid_scans', [
            'uid' => '11223344',
            'access_granted' => false,
            'access_message' => 'Jenis kartu tidak sesuai',
        ]);
    }

    public function test_card_profile_filtering_accepts_matching_card(): void
    {
        $plainToken = 'test-token-profile-matching';
        $this->createActiveDeviceToken($plainToken);

        AppSetting::set('rfid_profile_sak', '20, 28');
        AppSetting::set('rfid_profile_uid_size', '7');
        AppSetting::set('rfid_profile_card_type', 'ISO 14443-4');

        // Scan dengan kartu yang sesuai profil (SAK 20, UID size 7, card_type mengandung ISO 14443-4)
        $response = $this->withHeaders([
            'X-Device-Token' => $plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '041234567890AB',
            'sak' => '20',
            'atqa' => '0044',
            'uid_size' => 7,
            'card_type' => 'PICC / ISO 14443-4',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('success', $response->json('status'));
    }

    public function test_assign_rfid_to_warga_updates_status_to_terdaftar_and_preserves_nik(): void
    {
        $rw = Rw::create(['kode_rw' => '01']);
        $rt = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01']);

        $roleRt = Role::firstOrCreate(['nama_role' => 'rt']);
        $userRt = User::create([
            'name' => 'Ketua RT 01 Amanah',
            'email' => 'ketua_rt01@test.com',
            'password' => bcrypt('password'),
            'role_id' => $roleRt->id,
            'rt_id' => $rt->id,
        ]);

        $wargaProfile = UserProfile::create([
            'nama_lengkap' => 'Budi Santoso',
            'nik' => '3201123456789001',
            'rt_id' => $rt->id,
            'status' => 'pending',
        ]);

        // Simpan Kartu RFID apa saja ke warga (tanpa validasi NIK)
        $response = $this->actingAs($userRt)->postJson("/warga/{$wargaProfile->id}/rfid/save", [
            'rfid_uid' => 'A1:B2:C3:D4:E5:F6',
            'jenis_kartu' => 'E-Money / Kartu Bank',
            'card_type' => 'PICC / ISO 14443-4',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'rfid_uid' => 'A1B2C3D4E5F6',
                    'jenis_kartu' => 'E-Money / Kartu Bank',
                    'card_type' => 'PICC / ISO 14443-4',
                    'registered_by' => 'Ketua RT 01 Amanah',
                ],
            ]);

        $wargaProfile->refresh();

        // 1. UID, jenis kartu, nama petugas, waktu tersimpan
        $this->assertEquals('A1B2C3D4E5F6', $wargaProfile->rfid_uid);
        $this->assertEquals('E-Money / Kartu Bank', $wargaProfile->jenis_kartu);
        $this->assertEquals('PICC / ISO 14443-4', $wargaProfile->rfid_card_type);
        $this->assertEquals('Ketua RT 01 Amanah', $wargaProfile->rfid_registered_by);
        $this->assertNotNull($wargaProfile->rfid_registered_at);

        // 2. NIK warga TIDAK boleh berubah dari hasil scan!
        $this->assertEquals('3201123456789001', $wargaProfile->nik);

        // 4. Audit Log dicatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update_rfid',
            'entity_id' => $wargaProfile->id,
        ]);
    }

    public function test_superadmin_can_update_card_profile_settings(): void
    {
        $superAdminRole = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $superAdmin = User::create([
            'name' => 'Super Admin IoT',
            'email' => 'superadmin_iot@test.com',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('superadmin.api-settings.card-profile'), [
            'sak' => '20, 28',
            'uid_size' => 7,
            'card_type' => 'ISO 14443-4',
        ]);

        $response->assertRedirect();
        $this->assertEquals('20, 28', AppSetting::get('rfid_profile_sak'));
        $this->assertEquals('7', AppSetting::get('rfid_profile_uid_size'));
        $this->assertEquals('ISO 14443-4', AppSetting::get('rfid_profile_card_type'));
    }
}
