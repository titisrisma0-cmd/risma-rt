<?php

namespace Tests\Feature;

use App\Models\AbsensiRonda;
use App\Models\AuditLog;
use App\Models\DeviceToken;
use App\Models\JadwalRonda;
use App\Models\JenisSampah;
use App\Models\RfidDevice;
use App\Models\RfidScan;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SaldoSampah;
use App\Models\SetoranSampah;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RfidMultiFunctionAndDeviceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $adminRw;
    protected User $adminRt;
    protected User $wargaUser;
    protected UserProfile $wargaProfile;
    protected Rt $rt01;
    protected Rw $rw01;
    protected string $plainToken;
    protected DeviceToken $deviceToken;

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
                $table->string('status')->default('aktif');
                $table->timestamps();
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
                $table->string('fungsi', 50)->nullable();
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

        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jenis_sampahs')) {
            Schema::create('jenis_sampahs', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('kategori')->nullable();
                $table->decimal('harga_per_kg', 12, 2);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('saldo_sampahs')) {
            Schema::create('saldo_sampahs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique();
                $table->decimal('saldo', 15, 2)->default(0);
                $table->decimal('total_berat_kg', 10, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('setoran_sampahs')) {
            Schema::create('setoran_sampahs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('jenis_sampah_id');
                $table->decimal('berat_kg', 10, 2);
                $table->decimal('harga_per_kg', 12, 2);
                $table->decimal('total_saldo', 15, 2);
                $table->date('tanggal_setor')->nullable();
                $table->enum('status', ['pending', 'valid', 'ditolak'])->default('valid');
                $table->foreignId('created_by')->nullable();
                $table->foreignId('validated_by')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_rondas')) {
            Schema::create('jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal');
                $table->unsignedTinyInteger('shift')->default(1);
                $table->string('jam_mulai', 10)->nullable();
                $table->string('jam_selesai', 10)->nullable();
                $table->string('lokasi_pos')->nullable();
                $table->string('status', 30)->default('berlangsung');
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('absensi_rondas')) {
            Schema::create('absensi_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
                $table->string('status', 20)->default('hadir');
                $table->timestamp('waktu_absen')->nullable();
                $table->string('catatan')->nullable();
                $table->foreignId('recorded_by')->nullable();
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

        $this->plainToken = 'test-multi-device-secret-token-999';
        $this->deviceToken = DeviceToken::create([
            'name' => 'Gateway Multi Fungsi IoT',
            'token_hash' => DeviceToken::hashToken($this->plainToken),
            'is_active' => true,
        ]);

        $this->rw01 = Rw::firstOrCreate(['kode_rw' => '01'], ['nama_rw' => 'RW 01 Smart']);
        $this->rt01 = Rt::firstOrCreate(['rw_id' => $this->rw01->id, 'kode_rt' => '01'], ['nama_rt' => 'RT 01 Smart']);

        $roleSuper = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $roleRw = Role::firstOrCreate(['nama_role' => 'rw']);
        $roleRt = Role::firstOrCreate(['nama_role' => 'rt']);
        $roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin_multi_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role_id' => $roleSuper->id,
            'is_super_admin' => true,
        ]);

        $this->adminRw = User::create([
            'name' => 'Ketua RW 01',
            'email' => 'rw_multi_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role_id' => $roleRw->id,
        ]);

        $this->adminRt = User::create([
            'name' => 'Ketua RT 01',
            'email' => 'rt_multi_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role_id' => $roleRt->id,
            'rt_id' => $this->rt01->id,
        ]);

        $this->wargaUser = User::create([
            'name' => 'Bambang Sudarsono',
            'email' => 'warga_multi_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role_id' => $roleWarga->id,
            'rt_id' => $this->rt01->id,
        ]);

        $this->wargaProfile = UserProfile::create([
            'user_id' => $this->wargaUser->id,
            'nama_lengkap' => 'Bambang Sudarsono',
            'nik' => '3201998877660001',
            'rt_id' => $this->rt01->id,
            'rfid_uid' => '8899AABB',
            'status' => 'aktif',
        ]);
    }

    // ==================== TAHAP A: PERANGKAT & FUNGSI ====================

    public function test_unregistered_device_is_automatically_recorded_as_belum_dikonfigurasi_and_executes_no_action(): void
    {
        $newDeviceId = 'ESP32-NEW-' . uniqid();

        $response = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '8899AABB',
            'device_id' => $newDeviceId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'unconfigured',
                'fungsi' => 'belum_dikonfigurasi',
                'access_granted' => false,
                'gate_open' => false,
                'buzzer_signal' => 'DENIED',
                'message' => 'Perangkat RFID belum dikonfigurasi',
            ]);

        // Pastikan tercatat di tabel rfid_devices dengan fungsi 'belum_dikonfigurasi' dan is_active false
        $device = RfidDevice::where('device_id', $newDeviceId)->first();
        $this->assertNotNull($device);
        $this->assertEquals('belum_dikonfigurasi', $device->fungsi);
        $this->assertFalse($device->is_active);

        // Pastikan tap tetap tercatat di rfid_scans dengan kolom fungsi 'belum_dikonfigurasi'
        $this->assertDatabaseHas('rfid_scans', [
            'uid' => '8899AABB',
            'device_id' => $newDeviceId,
            'fungsi' => 'belum_dikonfigurasi',
            'access_granted' => false,
        ]);
    }

    public function test_inactive_device_executes_no_action_and_denies_access(): void
    {
        $deviceId = 'ESP32-INACTIVE-' . uniqid();
        RfidDevice::create([
            'device_id' => $deviceId,
            'nama_lokasi' => 'Portal Belakang Nonaktif',
            'fungsi' => 'gerbang',
            'is_active' => false,
        ]);

        $response = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '8899AABB',
            'device_id' => $deviceId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'unconfigured',
                'fungsi' => 'gerbang',
                'access_granted' => false,
                'gate_open' => false,
                'message' => 'Perangkat RFID sedang dinonaktifkan',
            ]);
    }

    public function test_superadmin_can_manage_rfid_devices_crud_and_toggle(): void
    {
        // 1. Index
        $response = $this->actingAs($this->superAdmin)->get(route('superadmin.rfid-devices.index'));
        $response->assertStatus(200)
            ->assertSee('Perangkat RFID');

        // 2. Store
        $newDevId = 'ESP32-GERBANG-BARU-' . uniqid();
        $storeRes = $this->actingAs($this->superAdmin)->post(route('superadmin.rfid-devices.store'), [
            'device_id' => $newDevId,
            'nama_lokasi' => 'Gerbang Sektor Barat',
            'fungsi' => 'gerbang',
            'rt_id' => $this->rt01->id,
            'is_active' => 1,
            'keterangan' => 'Dekat pos satpam',
        ]);
        $storeRes->assertRedirect(route('superadmin.rfid-devices.index'));

        $device = RfidDevice::where('device_id', $newDevId)->first();
        $this->assertNotNull($device);
        $this->assertEquals('gerbang', $device->fungsi);
        $this->assertTrue($device->is_active);

        // 3. Update
        $updateRes = $this->actingAs($this->superAdmin)->put(route('superadmin.rfid-devices.update', $device), [
            'device_id' => $newDevId,
            'nama_lokasi' => 'Gerbang Sektor Barat Diperbarui',
            'fungsi' => 'bank_sampah',
            'is_active' => 1,
        ]);
        $updateRes->assertRedirect(route('superadmin.rfid-devices.index'));
        $this->assertEquals('bank_sampah', $device->fresh()->fungsi);

        // 4. Toggle
        $toggleRes = $this->actingAs($this->superAdmin)->patch(route('superadmin.rfid-devices.toggle', $device));
        $toggleRes->assertStatus(302);
        $this->assertFalse($device->fresh()->is_active);

        // 5. Destroy
        $destroyRes = $this->actingAs($this->superAdmin)->delete(route('superadmin.rfid-devices.destroy', $device));
        $destroyRes->assertRedirect(route('superadmin.rfid-devices.index'));
        $this->assertDatabaseMissing('rfid_devices', ['id' => $device->id]);
    }

    public function test_non_superadmin_cannot_access_rfid_devices_management(): void
    {
        $response = $this->actingAs($this->adminRt)->get(route('superadmin.rfid-devices.index'));
        $response->assertStatus(403);
    }

    public function test_gerbang_function_returns_gate_open_true_only_for_active_registered_warga(): void
    {
        $gateDeviceId = 'ESP32-GATE-MAIN-' . uniqid();
        RfidDevice::create([
            'device_id' => $gateDeviceId,
            'nama_lokasi' => 'Portal Gerbang Utama',
            'fungsi' => 'gerbang',
            'is_active' => true,
        ]);

        // A. Kartu tidak terdaftar -> gate_open: false
        $resUnreg = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '12345678',
            'device_id' => $gateDeviceId,
        ]);

        $resUnreg->assertStatus(200)
            ->assertJson([
                'fungsi' => 'gerbang',
                'gate_open' => false,
                'access_granted' => false,
                'buzzer_signal' => 'DENIED',
            ]);

        // B. Kartu warga aktif terdaftar -> gate_open: true
        $resReg = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '8899AABB',
            'device_id' => $gateDeviceId,
        ]);

        $resReg->assertStatus(200)
            ->assertJson([
                'fungsi' => 'gerbang',
                'gate_open' => true,
                'access_granted' => true,
                'buzzer_signal' => 'GRANTED',
            ]);

        // C. Kartu warga pending / nonaktif -> gate_open: false
        $this->wargaProfile->update(['status' => 'pending']);
        $resPending = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '8899AABB',
            'device_id' => $gateDeviceId,
        ]);

        $resPending->assertStatus(200)
            ->assertJson([
                'fungsi' => 'gerbang',
                'gate_open' => false,
                'access_granted' => false,
                'buzzer_signal' => 'DENIED',
            ]);
    }

    public function test_bank_sampah_function_caches_warga_for_60_seconds(): void
    {
        $bsDeviceId = 'ESP32-BS-UNIT-' . uniqid();
        RfidDevice::create([
            'device_id' => $bsDeviceId,
            'nama_lokasi' => 'Pos Timbangan Bank Sampah',
            'fungsi' => 'bank_sampah',
            'is_active' => true,
        ]);

        SaldoSampah::create([
            'user_id' => $this->wargaUser->id,
            'saldo' => 75000,
            'total_berat_kg' => 25.5,
        ]);

        $response = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '8899AABB',
            'device_id' => $bsDeviceId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'fungsi' => 'bank_sampah',
                'access_granted' => true,
                'buzzer_signal' => 'GRANTED',
            ]);

        // Cek bahwa cache 'rfid_bank_sampah_latest_scan' terisi selama 60 detik
        $cached = Cache::get('rfid_bank_sampah_latest_scan');
        $this->assertNotNull($cached);
        $this->assertEquals('8899AABB', $cached['uid']);
        $this->assertEquals($this->wargaUser->id, $cached['user_id']);
        $this->assertEquals('Bambang Sudarsono', $cached['nama_warga']);
        $this->assertEquals(75000, $cached['saldo']);
    }

    public function test_presensi_function_records_attendance_and_audit_log(): void
    {
        $presensiDeviceId = 'ESP32-RONDA-' . uniqid();
        RfidDevice::create([
            'device_id' => $presensiDeviceId,
            'nama_lokasi' => 'Pos Kamling RT 01',
            'fungsi' => 'presensi',
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '8899AABB',
            'device_id' => $presensiDeviceId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'fungsi' => 'presensi',
                'access_granted' => true,
                'buzzer_signal' => 'GRANTED',
            ]);

        // Pastikan presensi tercatat di AbsensiRonda jika tabel ada
        if (Schema::hasTable('absensi_rondas')) {
            $absensi = AbsensiRonda::where('user_profile_id', $this->wargaProfile->id)
                ->where('status', 'hadir')
                ->first();
            $this->assertNotNull($absensi);
        }

        // Pastikan tercatat di AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'presensi_rfid',
            'entity_id' => $this->wargaProfile->id,
        ]);
    }

    public function test_perekaman_function_caches_scan_data_for_modal(): void
    {
        $rekamDeviceId = 'ESP32-REKAM-' . uniqid();
        RfidDevice::create([
            'device_id' => $rekamDeviceId,
            'nama_lokasi' => 'Meja Registrasi Sekretariat',
            'fungsi' => 'perekaman',
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => 'CCDDEEFF',
            'device_id' => $rekamDeviceId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'fungsi' => 'perekaman',
            ]);

        $cached = Cache::get('rfid_latest_scan');
        $this->assertNotNull($cached);
        $this->assertEquals('CCDDEEFF', $cached['uid']);
    }

    public function test_all_taps_persist_history_with_fungsi_column(): void
    {
        $deviceId = 'ESP32-TEST-FN-' . uniqid();
        RfidDevice::create([
            'device_id' => $deviceId,
            'nama_lokasi' => 'Pintu Bank Sampah',
            'fungsi' => 'bank_sampah',
            'is_active' => true,
        ]);

        $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => '8899AABB',
            'device_id' => $deviceId,
        ]);

        $this->assertDatabaseHas('rfid_scans', [
            'uid' => '8899AABB',
            'device_id' => $deviceId,
            'fungsi' => 'bank_sampah',
        ]);
    }

    // ==================== TAHAP B: BANK SAMPAH ====================

    public function test_bank_sampah_setoran_rfid_view_accessible_by_rw_and_rt_with_proper_scopes(): void
    {
        // Admin RT dapat mengakses
        $resRt = $this->actingAs($this->adminRt)->get(route('bank-sampah.setoran-rfid'));
        $resRt->assertStatus(200)
            ->assertSee('Setoran Bank Sampah')
            ->assertSee('Wilayah RT 01');

        // Admin RW dapat mengakses
        $resRw = $this->actingAs($this->adminRw)->get(route('bank-sampah.setoran-rfid'));
        $resRw->assertStatus(200)
            ->assertSee('Semua Wilayah RW');

        // Warga biasa tidak boleh mengakses
        $resWarga = $this->actingAs($this->wargaUser)->get(route('bank-sampah.setoran-rfid'));
        $resWarga->assertStatus(403);
    }

    public function test_bank_sampah_rfid_latest_polling_endpoint(): void
    {
        // Saat cache kosong
        $resWait = $this->actingAs($this->adminRt)->getJson(route('bank-sampah.rfid.latest'));
        $resWait->assertStatus(200)
            ->assertJson([
                'status' => 'waiting',
            ]);

        // Saat ada kartu warga tap
        Cache::put('rfid_bank_sampah_latest_scan', [
            'uid' => '8899AABB',
            'nama_warga' => 'Bambang Sudarsono',
            'user_id' => $this->wargaUser->id,
            'saldo' => 50000,
            'timestamp' => now()->timestamp,
        ], now()->addSeconds(60));

        $resDetected = $this->actingAs($this->adminRt)->getJson(route('bank-sampah.rfid.latest'));
        $resDetected->assertStatus(200)
            ->assertJson([
                'status' => 'detected',
                'data' => [
                    'uid' => '8899AABB',
                    'nama_warga' => 'Bambang Sudarsono',
                ],
            ]);
    }

    public function test_store_setoran_rfid_saves_setoran_and_increments_saldo_and_logs_audit(): void
    {
        $jenisSampah = JenisSampah::create([
            'nama' => 'Kardus Bersih Tebal',
            'kategori' => 'Anorganik',
            'harga_per_kg' => 2000,
            'status' => true,
        ]);

        $initialSaldo = SaldoSampah::create([
            'user_id' => $this->wargaUser->id,
            'saldo' => 10000,
            'total_berat_kg' => 5.0,
        ]);

        // Simpan setoran: 10 kg * Rp 2.000 = Rp 20.000
        $response = $this->actingAs($this->adminRt)->postJson(route('bank-sampah.setoran-rfid.store'), [
            'user_id' => $this->wargaUser->id,
            'jenis_sampah_id' => $jenisSampah->id,
            'berat_kg' => 10.0,
            'tanggal_setor' => now()->toDateString(),
            'catatan' => 'Kardus bersih packing rapi',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Cek data di setoran_sampahs
        $this->assertDatabaseHas('setoran_sampahs', [
            'user_id' => $this->wargaUser->id,
            'jenis_sampah_id' => $jenisSampah->id,
            'berat_kg' => 10.0,
            'total_saldo' => 20000,
            'status' => 'valid',
        ]);

        // Cek bahwa Saldo bertambah 10.000 + 20.000 = 30.000 dan berat bertambah 5 + 10 = 15 kg
        $initialSaldo->refresh();
        $this->assertEquals(30000, (float) $initialSaldo->saldo);
        $this->assertEquals(15.0, (float) $initialSaldo->total_berat_kg);

        // Cek AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'setoran_bank_sampah_rfid',
            'nominal' => 20000,
        ]);
    }

    // ==================== TAHAP C: PEREKAMAN KARTU ====================

    public function test_assign_rfid_rejects_duplicate_uid_with_exact_message(): void
    {
        // $this->wargaProfile sudah memiliki UID: '8899AABB' (nama: 'Bambang Sudarsono')
        // Buat warga kedua
        $wargaProfile2 = UserProfile::create([
            'nama_lengkap' => 'Siti Nurhaliza',
            'nik' => '3201554433220002',
            'rt_id' => $this->rt01->id,
            'status' => 'aktif',
        ]);

        // Coba rekam kartu dengan UID yang sama ke warga kedua
        $response = $this->actingAs($this->adminRt)->postJson("/warga/{$wargaProfile2->id}/rfid/save", [
            'rfid_uid' => '88:99:AA:BB',
            'konfirmasi_nik' => '3201554433220002',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Kartu ini sudah terdaftar atas nama Bambang Sudarsono',
            ]);
    }

    public function test_assign_rfid_saves_without_nik_validation_and_stores_jenis_kartu(): void
    {
        $wargaBaru = UserProfile::create([
            'nama_lengkap' => 'Hendra Pratama',
            'nik' => '3201112233445566',
            'rt_id' => $this->rt01->id,
            'status' => 'aktif',
        ]);

        // Simpan kartu RFID (misalnya e-Money / Kartu Akses) tanpa konfirmasi_nik
        $resSave = $this->actingAs($this->adminRt)->postJson("/warga/{$wargaBaru->id}/rfid/save", [
            'rfid_uid' => '11:22:33:44',
            'jenis_kartu' => 'Kartu Akses / Gantungan RFID',
        ]);
        $resSave->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'rfid_uid' => '11223344',
                    'jenis_kartu' => 'Kartu Akses / Gantungan RFID',
                ],
            ]);

        $wargaBaru->refresh();
        $this->assertEquals('11223344', $wargaBaru->rfid_uid);
        $this->assertEquals('Kartu Akses / Gantungan RFID', $wargaBaru->jenis_kartu);
        $this->assertEquals('aktif', $wargaBaru->status);
        $this->assertEquals('3201112233445566', $wargaBaru->nik);

        // Cek dicatat ke AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update_rfid',
            'entity_id' => $wargaBaru->id,
        ]);
    }

    public function test_active_recording_session_can_be_started_refreshed_and_stopped(): void
    {
        $this->assertFalse(\App\Http\Controllers\RfidController::hasActiveRecordingSession());

        // 1. Mulai sesi
        $startRes = $this->actingAs($this->adminRt)->postJson('/warga/rfid/session/start');
        $startRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sesi perekaman aktif',
            ]);

        $this->assertTrue(\App\Http\Controllers\RfidController::hasActiveRecordingSession());
        $this->assertTrue(Cache::has("rfid_recording_session_user_{$this->adminRt->id}"));

        // 2. Polling latest memperbarui sesi
        $pollRes = $this->actingAs($this->adminRt)->getJson('/warga/rfid/latest');
        $pollRes->assertStatus(200);
        $this->assertTrue(\App\Http\Controllers\RfidController::hasActiveRecordingSession());

        // 3. Hentikan sesi
        $stopRes = $this->actingAs($this->adminRt)->postJson('/warga/rfid/session/stop');
        $stopRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sesi perekaman dihentikan',
            ]);

        $this->assertFalse(\App\Http\Controllers\RfidController::hasActiveRecordingSession());
    }

    public function test_active_recording_session_intercepts_tap_from_any_active_device_and_does_not_trigger_other_actions(): void
    {
        // 1. Buat perangkat gerbang aktif
        $deviceGerbang = RfidDevice::create([
            'device_id' => 'ESP32-GATE-TEST',
            'nama_lokasi' => 'Portal Gerbang Utama',
            'fungsi' => RfidDevice::FUNGSI_GERBANG,
            'is_active' => true,
        ]);

        // Aktifkan sesi perekaman oleh admin RT
        \App\Http\Controllers\RfidController::refreshRecordingSession($this->adminRt->id);
        $this->assertTrue(\App\Http\Controllers\RfidController::hasActiveRecordingSession());

        // Tap kartu warga aktif di gerbang saat modal rekam terbuka
        $response = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => $this->wargaProfile->rfid_uid,
            'device_id' => 'ESP32-GATE-TEST',
            'card_type' => 'ISO 14443-4',
            'sak' => '0x20',
            'atqa' => '0x0004',
            'uid_size' => 7,
        ]);

        // Gerbang TIDAK boleh terbuka!
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'gate_open' => false,
                'access_message' => 'dipakai untuk perekaman',
            ]);

        // Ditangkap oleh modal rekam di cache
        $latestScan = Cache::get('rfid_latest_scan');
        $this->assertNotNull($latestScan);
        $this->assertEquals($this->wargaProfile->rfid_uid, $latestScan['uid']);
        $this->assertFalse($latestScan['gate_open']);
        $this->assertEquals('dipakai untuk perekaman', $latestScan['access_message']);

        // Riwayat scan tetap mencatat dengan keterangan "dipakai untuk perekaman"
        $this->assertDatabaseHas('rfid_scans', [
            'uid' => $this->wargaProfile->rfid_uid,
            'device_id' => 'ESP32-GATE-TEST',
            'access_message' => 'dipakai untuk perekaman',
        ]);

        // 2. Cek perangkat bank sampah saat sesi perekaman aktif: TIDAK memicu aksi lain
        $deviceBankSampah = RfidDevice::create([
            'device_id' => 'ESP32-TRASH-TEST',
            'nama_lokasi' => 'Pos Bank Sampah RW',
            'fungsi' => RfidDevice::FUNGSI_BANK_SAMPAH,
            'is_active' => true,
        ]);

        Cache::forget('rfid_bank_sampah_latest_scan');

        $resTrash = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => 'A1B2C3D4',
            'device_id' => 'ESP32-TRASH-TEST',
        ]);

        $resTrash->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'gate_open' => false,
                'access_message' => 'dipakai untuk perekaman',
            ]);

        // Bank sampah data TIDAK boleh tersimpan di cache bank sampah!
        $this->assertFalse(Cache::has('rfid_bank_sampah_latest_scan'));
    }

    public function test_modal_latest_scan_detects_duplicate_card_and_returns_clear_message(): void
    {
        // Simpan scan kartu yang sudah dipakai oleh wargaProfile ke cache
        Cache::put('rfid_latest_scan', [
            'uid' => $this->wargaProfile->rfid_uid,
            'formatted_uid' => '1A:2B:3C:4D',
            'card_type' => 'MIFARE Classic 1K',
            'sak' => '08',
            'atqa' => '0004',
            'uid_size' => 4,
            'device_id' => 'ESP32-TEST',
            'timestamp' => now()->timestamp,
        ], 60);

        $response = $this->actingAs($this->adminRt)->getJson('/warga/rfid/latest');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'detected',
                'is_used' => true,
                'duplicate_message' => "Kartu ini sudah terdaftar atas nama {$this->wargaProfile->nama_lengkap}",
            ]);
    }

    public function test_modal_latest_scan_returns_unconfigured_or_denied_scan_from_rfid_scans_table(): void
    {
        // 1. Scan lama sudah ada sebelum modal dibuka
        $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => 'DE:AD:BE:EF',
            'device_id' => 'ESP32 Gerbang',
        ])->assertStatus(200);

        // 2. Modal dibuka -> init=1 mengambil baselineScanId tanpa menampilkan UID lama
        $initRes = $this->actingAs($this->adminRt)->getJson('/warga/rfid/latest?init=1');
        $initRes->assertStatus(200)
            ->assertJson([
                'status' => 'waiting',
                'found' => false,
                'data' => null,
            ]);
        $baselineId = (int) $initRes->json('latest_id');
        $this->assertGreaterThan(0, $baselineId);

        // 3. Polling dengan after_id sebelum tap baru -> tetap waiting (found: false)
        $beforeTap = $this->actingAs($this->adminRt)->getJson("/warga/rfid/latest?after_id={$baselineId}");
        $beforeTap->assertStatus(200)
            ->assertJson([
                'status' => 'waiting',
                'found' => false,
                'data' => null,
            ]);

        // 4. Tap baru masuk setelah modal terbuka (meski perangkat belum dikonfigurasi)
        $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => 'A9:16:37:07',
            'device_id' => 'ESP32 Gerbang',
        ])->assertStatus(200)
          ->assertJson([
              'status' => 'unconfigured',
              'access_granted' => false,
          ]);

        // 5. Polling dengan after_id menangkap tap baru
        $poll = $this->actingAs($this->adminRt)->getJson("/warga/rfid/latest?after_id={$baselineId}");
        $poll->assertStatus(200)
            ->assertJson([
                'status' => 'detected',
                'found' => true,
                'data' => [
                    'uid' => 'A9163707',
                    'formatted_uid' => 'A9:16:37:07',
                    'device_id' => 'ESP32 Gerbang',
                ],
            ]);
        $this->assertGreaterThan($baselineId, (int) $poll->json('latest_id'));
    }

    public function test_superadmin_can_quick_set_gerbang_and_normalized_uid_opens_gate(): void
    {
        $device = RfidDevice::create([
            'device_id' => 'ESP32 Gerbang',
            'nama_lokasi' => 'Perangkat Baru (ESP32 Gerbang)',
            'fungsi' => RfidDevice::FUNGSI_BELUM_DIKONFIGURASI,
            'is_active' => false,
        ]);

        // Superadmin klik cepat "Set Gerbang"
        $this->actingAs($this->superAdmin)
            ->patch("/superadmin/rfid-devices/{$device->id}/set-gerbang")
            ->assertRedirect();

        $device->refresh();
        $this->assertEquals(RfidDevice::FUNGSI_GERBANG, $device->fungsi);
        $this->assertTrue((bool) $device->is_active);

        // Admin mendaftarkan kartu dengan format huruf kecil + titik dua
        $this->actingAs($this->adminRt)->postJson("/warga/{$this->wargaProfile->id}/rfid/save", [
            'rfid_uid' => 'a9:16:37:07',
            'jenis_kartu' => 'E-Money / Kartu Bank',
        ])->assertStatus(200);

        // ESP32 Gerbang tap kartu dengan format uppercase tanpa titik dua
        $resTap = $this->withHeaders([
            'X-Device-Token' => $this->plainToken,
        ])->postJson('/api/rfid/scan', [
            'uid' => 'A9163707',
            'device_id' => 'ESP32 Gerbang',
        ]);

        $resTap->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'fungsi' => 'gerbang',
                'gate_open' => true,
                'access_granted' => true,
                'buzzer_signal' => 'GRANTED',
            ]);
    }
}
