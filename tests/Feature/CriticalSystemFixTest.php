<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\JadwalRonda;
use App\Models\KartuKeluarga;
use App\Models\Notifikasi;
use App\Models\PetugasRonda;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CriticalSystemFixTest extends TestCase
{
    use DatabaseTransactions;

    protected $roleRw;
    protected $roleRt;
    protected $roleWarga;
    protected $rw;
    protected $rt1;
    protected $rt2;
    protected $adminRw;
    protected $ketuaRt1;
    protected $ketuaRt2;

    protected function setUp(): void
    {
        parent::setUp();

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
                $table->rememberToken();
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
                $table->foreignId('rw_id');
                $table->string('kode_rt');
                $table->string('nama_rt')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kartu_keluargas')) {
            Schema::create('kartu_keluargas', function (Blueprint $table) {
                $table->id();
                $table->string('no_kk')->unique();
                $table->foreignId('rt_id');
                $table->foreignId('kepala_keluarga_user_id')->nullable();
                $table->text('alamat')->nullable();
                $table->string('status_rumah')->default('tetap');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('kartu_keluarga_id')->nullable();
                $table->string('no_kk', 20)->nullable();
                $table->string('nik', 20)->nullable();
                $table->string('nama_lengkap');
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
                $table->string('agama')->nullable();
                $table->text('alamat')->nullable();
                $table->string('nomor_rumah')->nullable();
                $table->string('rt', 5)->nullable();
                $table->string('rw', 5)->nullable();
                $table->string('kelurahan')->nullable();
                $table->string('kecamatan')->nullable();
                $table->string('kota')->nullable();
                $table->string('no_hp', 25)->nullable();
                $table->string('pekerjaan')->nullable();
                $table->string('status_perkawinan')->nullable();
                $table->string('status_hubungan_keluarga')->nullable();
                $table->string('pendidikan')->nullable();
                $table->string('nama_ayah')->nullable();
                $table->string('nama_ibu')->nullable();
                $table->string('foto')->nullable();
                $table->string('foto_base64')->nullable();
                $table->string('status')->default('aktif');
                $table->boolean('siap_ronda')->default(true)->nullable();
                $table->string('alasan_pengecualian_ronda')->nullable();
                $table->text('catatan_verifikasi')->nullable();
                $table->foreignId('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_rondas')) {
            Schema::create('jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal');
                $table->string('shift', 50)->default('malam');
                $table->string('jam_mulai', 10)->nullable();
                $table->string('jam_selesai', 10)->nullable();
                $table->string('lokasi_pos')->nullable();
                $table->string('keterangan')->nullable();
                $table->string('status', 20)->default('aktif');
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petugas_rondas')) {
            Schema::create('petugas_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_profile_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->string('status_kehadiran')->default('belum_hadir');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('absensi_rondas')) {
            Schema::create('absensi_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_profile_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->dateTime('waktu_absen')->nullable();
                $table->string('status')->default('hadir');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tukar_jadwal_rondas')) {
            Schema::create('tukar_jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('user_id');
                $table->foreignId('target_user_id')->nullable();
                $table->foreignId('jadwal_ronda_asal_id');
                $table->foreignId('target_jadwal_ronda_id')->nullable();
                $table->string('status')->default('menunggu');
                $table->text('alasan')->nullable();
                $table->text('catatan_rt')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('panic_reports')) {
            Schema::create('panic_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('rt_id')->nullable();
                $table->string('tipe_kejadian')->default('keamanan');
                $table->string('status')->default('aktif');
                $table->text('deskripsi')->nullable();
                $table->foreignId('handler_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kontak_darurats')) {
            Schema::create('kontak_darurats', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('kategori')->default('umum');
                $table->string('nomor_telepon');
                $table->text('alamat')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('from_user_id')->nullable();
                $table->string('tipe')->default('info');
                $table->string('judul');
                $table->text('pesan');
                $table->boolean('dibaca')->default(false);
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
                $table->decimal('nominal', 12, 2)->nullable();
                $table->string('bukti')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('iuran_id')->nullable();
                $table->decimal('jumlah_bayar', 12, 2)->default(0);
                $table->string('status')->default('pending');
                $table->string('bukti_transfer')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kas')) {
            Schema::create('kas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('pembayaran_id')->nullable();
                $table->string('jenis')->default('masuk');
                $table->decimal('nominal', 12, 2)->default(0);
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('cakupan')->default('rt');
                $table->string('judul')->default('Kegiatan');
                $table->text('deskripsi')->nullable();
                $table->string('lokasi')->default('Balai');
                $table->dateTime('tanggal_mulai')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('aspirasis')) {
            Schema::create('aspirasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('judul')->nullable();
                $table->text('isi')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengajuans')) {
            Schema::create('pengajuans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengajuan_surats')) {
            Schema::create('pengajuan_surats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('jenis_surat_id')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        $this->roleRw = Role::firstOrCreate(['nama_role' => 'rw']);
        $this->roleRt = Role::firstOrCreate(['nama_role' => 'rt']);
        $this->roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);

        $this->rw = Rw::firstOrCreate(['kode_rw' => '05'], ['nama_rw' => 'RW 05 SMART']);

        $this->rt1 = Rt::firstOrCreate(
            ['kode_rt' => '01', 'rw_id' => $this->rw->id],
            ['nama_rt' => 'RT 01']
        );

        $this->rt2 = Rt::firstOrCreate(
            ['kode_rt' => '02', 'rw_id' => $this->rw->id],
            ['nama_rt' => 'RT 02']
        );

        $this->adminRw = User::create([
            'name' => 'Admin RW 05',
            'email' => 'admin_rw_' . uniqid() . '@rw-smart.test',
            'password' => Hash::make('password123'),
            'role_id' => $this->roleRw->id,
        ]);

        $this->ketuaRt1 = User::create([
            'name' => 'Ketua RT 01',
            'email' => 'rt01_' . uniqid() . '@rw-smart.test',
            'password' => Hash::make('password123'),
            'role_id' => $this->roleRt->id,
            'rt_id' => $this->rt1->id,
        ]);
        $this->rt1->update(['ketua_user_id' => $this->ketuaRt1->id]);

        UserProfile::create([
            'user_id' => $this->ketuaRt1->id,
            'role_id' => $this->roleRt->id,
            'rt_id' => $this->rt1->id,
            'nama_lengkap' => $this->ketuaRt1->name,
            'status' => 'aktif',
        ]);

        $this->ketuaRt2 = User::create([
            'name' => 'Ketua RT 02',
            'email' => 'rt02_' . uniqid() . '@rw-smart.test',
            'password' => Hash::make('password123'),
            'role_id' => $this->roleRt->id,
            'rt_id' => $this->rt2->id,
        ]);
        $this->rt2->update(['ketua_user_id' => $this->ketuaRt2->id]);

        UserProfile::create([
            'user_id' => $this->ketuaRt2->id,
            'role_id' => $this->roleRt->id,
            'rt_id' => $this->rt2->id,
            'nama_lengkap' => $this->ketuaRt2->name,
            'status' => 'aktif',
        ]);
    }

    /**
     * a. RT menambahkan warga tanpa akun -> profil tersimpan dengan status aktif, user_id null.
     */
    public function test_a_rt_adds_citizen_without_account_saves_active_profile_with_null_user_id()
    {
        $nik = '320101' . rand(1000000000, 9999999999);
        $noKk = '320101' . rand(1000000000, 9999999999);

        $response = $this->actingAs($this->ketuaRt1)->post(route('warga.store'), [
            'nama_lengkap' => 'Warga Tanpa Akun',
            'email' => null,
            'nik' => $nik,
            'no_kk' => $noKk,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-05-15',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Mawar No. 10 RT 01',
            'no_hp' => '081234567890',
            'pekerjaan' => 'Wiraswasta',
            'siap_ronda' => 1,
        ]);

        $response->assertRedirect(route('warga.index'));

        $profile = UserProfile::where('nik', $nik)->first();
        $this->assertNotNull($profile, 'UserProfile harus berhasil dibuat');
        $this->assertNull($profile->user_id, 'user_id harus null karena warga belum memiliki akun');
        $this->assertEquals('aktif', $profile->status, 'Status kependudukan harus aktif');
        $this->assertEquals($this->rt1->id, $profile->rt_id);
        $this->assertEquals('Warga Tanpa Akun', $profile->nama_lengkap);
    }

    /**
     * b. Warga registrasi dengan NIK yang sudah didaftarkan RT -> akun terhubung ke profil yang ada setelah verifikasi.
     */
    public function test_b_citizen_registers_with_existing_nik_links_account_to_existing_profile_after_verification()
    {
        $nik = '320102' . rand(1000000000, 9999999999);
        $noKk = '320102' . rand(1000000000, 9999999999);

        $existingProfile = UserProfile::create([
            'user_id' => null,
            'rt_id' => $this->rt1->id,
            'no_kk' => $noKk,
            'nik' => $nik,
            'nama_lengkap' => 'Warga Terdaftar RT',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1992-08-20',
            'alamat' => 'Blok C No. 5',
            'status' => 'aktif',
            'siap_ronda' => true,
        ]);

        $email = 'warga_link_' . uniqid() . '@rw-smart.test';

        $regResponse = $this->post(route('register'), [
            'name' => 'Warga Terdaftar RT',
            'nik' => $nik,
            'rt_id' => $this->rt1->id,
            'no_kk' => $noKk,
            'no_hp' => '081299998888',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $regResponse->assertRedirect('/login');

        $newUser = User::where('email', $email)->first();
        $this->assertNotNull($newUser);

        $existingProfile->refresh();
        $this->assertEquals($newUser->id, $existingProfile->user_id, 'Akun harus ditautkan ke profil kependudukan yang sudah ada');
        $this->assertEquals('pending', $existingProfile->status, 'Status akun menunggu verifikasi Ketua RT');

        $verifyResponse = $this->actingAs($this->ketuaRt1)->post(route('warga.verifikasi.approve', $existingProfile->id));
        $verifyResponse->assertRedirect();

        $existingProfile->refresh();
        $this->assertEquals('aktif', $existingProfile->status, 'Setelah disetujui RT, status menjadi aktif');
    }

    /**
     * c. Warga registrasi dengan NIK baru -> akun dan profil baru dibuat dengan status pending.
     */
    public function test_c_citizen_registers_with_new_nik_creates_user_and_profile_with_pending_status()
    {
        $nik = '320103' . rand(1000000000, 9999999999);
        $noKk = '320103' . rand(1000000000, 9999999999);
        $email = 'warga_baru_' . uniqid() . '@rw-smart.test';

        $response = $this->post(route('register'), [
            'name' => 'Warga Pendatang Baru',
            'nik' => $nik,
            'rt_id' => $this->rt1->id,
            'no_kk' => $noKk,
            'no_hp' => '081377776666',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/login');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);

        $profile = UserProfile::where('nik', $nik)->first();
        $this->assertNotNull($profile);
        $this->assertEquals($user->id, $profile->user_id);
        $this->assertEquals('pending', $profile->status);
    }

    /**
     * d. Registrasi dengan NIK yang sudah berakun -> ditolak dengan pesan yang sesuai.
     */
    public function test_d_registration_with_already_registered_nik_is_rejected()
    {
        $nik = '320104' . rand(1000000000, 9999999999);
        $noKk = '320104' . rand(1000000000, 9999999999);

        $existingUser = User::create([
            'name' => 'Warga Berakun Lama',
            'email' => 'lama_' . uniqid() . '@rw-smart.test',
            'password' => Hash::make('password123'),
            'role_id' => $this->roleWarga->id,
        ]);

        UserProfile::create([
            'user_id' => $existingUser->id,
            'rt_id' => $this->rt1->id,
            'no_kk' => $noKk,
            'nik' => $nik,
            'nama_lengkap' => $existingUser->name,
            'status' => 'aktif',
        ]);

        $response = $this->from('/login#register')->post(route('register'), [
            'name' => 'Penyerobot Akun',
            'nik' => $nik,
            'rt_id' => $this->rt1->id,
            'no_kk' => $noKk,
            'no_hp' => '081999999999',
            'jenis_kelamin' => 'L',
            'email' => 'hacker_' . uniqid() . '@rw-smart.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('nik');
    }

    /**
     * e. Warga pending mencoba login -> ditolak dengan pesan "Menunggu verifikasi Ketua RT".
     */
    public function test_e_pending_citizen_login_is_rejected_with_proper_status_message()
    {
        auth()->logout();

        $email = 'pending_' . uniqid() . '@rw-smart.test';
        $user = User::create([
            'name' => 'Warga Masih Pending',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role_id' => $this->roleWarga->id,
        ]);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rt_id' => $this->rt1->id,
                'nama_lengkap' => $user->name,
                'status' => 'pending',
            ]
        );

        $response = $this->post(route('login'), [
            'email' => $email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('Ketua RT', $errorMessage);
    }

    /**
     * f. Ketua RT menyetujui pendaftaran -> akun aktif, warga bisa login.
     */
    public function test_f_ketua_rt_approves_registration_citizen_becomes_active_and_can_login()
    {
        $email = 'approve_test_' . uniqid() . '@rw-smart.test';
        $user = User::create([
            'name' => 'Warga Siap Disetujui',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role_id' => $this->roleWarga->id,
        ]);

        $profile = UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rt_id' => $this->rt1->id,
                'nama_lengkap' => $user->name,
                'status' => 'pending',
            ]
        );

        $responseApprove = $this->actingAs($this->ketuaRt1)->post(route('warga.verifikasi.approve', $profile->id));
        $responseApprove->assertSessionHas('success');

        $profile->refresh();
        $this->assertEquals('aktif', $profile->status);
        $this->assertEquals($this->ketuaRt1->id, $profile->verified_by);
        $this->assertNotNull($profile->verified_at);

        auth()->logout();

        $responseLogin = $this->post(route('login'), [
            'email' => $email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * g. Ketua RT menolak pendaftaran -> akun ditolak dengan alasan, warga tidak bisa login.
     */
    public function test_g_ketua_rt_rejects_registration_citizen_is_rejected_with_reason_and_cannot_login()
    {
        $email = 'reject_test_' . uniqid() . '@rw-smart.test';
        $user = User::create([
            'name' => 'Warga Akan Ditolak',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role_id' => $this->roleWarga->id,
        ]);

        $profile = UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rt_id' => $this->rt1->id,
                'nama_lengkap' => $user->name,
                'status' => 'pending',
            ]
        );

        $alasan = 'Bukan warga domisili RT 01';
        $responseReject = $this->actingAs($this->ketuaRt1)->post(route('warga.verifikasi.reject', $profile->id), [
            'alasan' => $alasan,
        ]);
        $responseReject->assertSessionHas('success');

        $profile->refresh();
        $this->assertEquals('ditolak', $profile->status);
        $this->assertEquals($alasan, $profile->catatan_verifikasi);

        auth()->logout();

        $responseLogin = $this->post(route('login'), [
            'email' => $email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $responseLogin->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('ditolak', strtolower($errorMessage));
        $this->assertStringContainsString($alasan, $errorMessage);
    }

    /**
     * h. Data warga di dashboard RW menampilkan seluruh warga (berakun + belum berakun).
     */
    public function test_h_warga_index_rw_displays_all_citizens_with_or_without_accounts()
    {
        $nikWithAccount = '320108' . rand(1000000000, 9999999999);
        $nikWithoutAccount = '320109' . rand(1000000000, 9999999999);

        $userWithAccount = User::create([
            'name' => 'Warga Punya Akun ' . uniqid(),
            'email' => 'akun_' . uniqid() . '@rw-smart.test',
            'password' => Hash::make('password123'),
            'role_id' => $this->roleWarga->id,
        ]);

        UserProfile::create([
            'user_id' => $userWithAccount->id,
            'rt_id' => $this->rt1->id,
            'nik' => $nikWithAccount,
            'nama_lengkap' => $userWithAccount->name,
            'status' => 'aktif',
        ]);

        UserProfile::create([
            'user_id' => null,
            'rt_id' => $this->rt1->id,
            'nik' => $nikWithoutAccount,
            'nama_lengkap' => 'Warga Belum Ada Akun ' . uniqid(),
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->adminRw)->get(route('warga.index'));
        $response->assertOk();
        $response->assertSee($nikWithAccount);
        $response->assertSee($nikWithoutAccount);
        $response->assertSee('Belum Ada Akun');
    }

    /**
     * i. Filter RT pada data warga RW menampilkan data yang benar.
     */
    public function test_i_filter_rt_on_warga_index_rw_displays_correct_scoped_citizens()
    {
        $nikRt1 = '320111' . rand(1000000000, 9999999999);
        $nikRt2 = '320122' . rand(1000000000, 9999999999);

        UserProfile::create([
            'rt_id' => $this->rt1->id,
            'nik' => $nikRt1,
            'nama_lengkap' => 'Warga Wilayah RT Satu',
            'status' => 'aktif',
        ]);

        UserProfile::create([
            'rt_id' => $this->rt2->id,
            'nik' => $nikRt2,
            'nama_lengkap' => 'Warga Wilayah RT Dua',
            'status' => 'aktif',
        ]);

        $responseRt1 = $this->actingAs($this->adminRw)->get(route('warga.index', ['rt_id' => $this->rt1->id]));
        $responseRt1->assertOk();
        $responseRt1->assertSee($nikRt1);
        $responseRt1->assertDontSee($nikRt2);

        $responseRt2 = $this->actingAs($this->adminRw)->get(route('warga.index', ['rt_id' => $this->rt2->id]));
        $responseRt2->assertOk();
        $responseRt2->assertSee($nikRt2);
        $responseRt2->assertDontSee($nikRt1);
    }

    /**
     * j. Generator ronda otomatis menghasilkan jadwal 1 bulan dengan distribusi merata.
     */
    public function test_j_automatic_ronda_generator_produces_monthly_schedule_with_fair_distribution()
    {
        for ($i = 1; $i <= 10; $i++) {
            UserProfile::create([
                'rt_id' => $this->rt1->id,
                'nik' => '320113' . rand(1000000000, 9999999999),
                'nama_lengkap' => "Warga Ronda {$i}",
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1990-01-01',
                'status' => 'aktif',
                'siap_ronda' => true,
                'status_hubungan_keluarga' => ($i <= 5) ? 'Kepala Keluarga' : 'Anggota',
            ]);
        }

        $response = $this->actingAs($this->ketuaRt1)->postJson(route('ronda.generate-preview'), [
            'bulan' => 11,
            'tahun' => 2026,
            'jumlah_petugas' => 3,
            'jumlah_shift' => 1,
            'jam_mulai_1' => '22:00',
            'jam_selesai_1' => '02:00',
            'lokasi_pos' => 'Pos Ronda RT 01',
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(30, count($data['jadwals']), 'Bulan November harus memiliki 30 hari jadwal');

        foreach ($data['jadwals'] as $j) {
            $this->assertCount(3, $j['petugas']);
        }

        $responseSave = $this->actingAs($this->ketuaRt1)->postJson(route('ronda.simpan-otomatis'), [
            'jadwals' => $data['jadwals'],
        ]);

        $responseSave->assertOk();
        $this->assertTrue($responseSave->json('success'));

        $this->assertCount(30, JadwalRonda::where('rt_id', $this->rt1->id)->get());
        $this->assertNotNull(JadwalRonda::where('rt_id', $this->rt1->id)->whereDate('tanggal', '2026-11-01')->first());
    }

    /**
     * k. Generator ronda otomatis HANYA memasukkan warga laki-laki dewasa yang eligible.
     */
    public function test_k_automatic_ronda_generator_only_includes_eligible_adult_males()
    {
        $nikPerempuan = '320114' . rand(1000000000, 9999999999);
        $nikAnak = '320115' . rand(1000000000, 9999999999);
        $nikPengecualian = '320116' . rand(1000000000, 9999999999);
        $nikEligible = '320117' . rand(1000000000, 9999999999);

        UserProfile::create([
            'rt_id' => $this->rt1->id,
            'nik' => $nikPerempuan,
            'nama_lengkap' => 'Warga Perempuan Tidak Ronda',
            'jenis_kelamin' => 'P',
            'tanggal_lahir' => '1992-01-01',
            'status' => 'aktif',
        ]);

        UserProfile::create([
            'rt_id' => $this->rt1->id,
            'nik' => $nikAnak,
            'nama_lengkap' => 'Anak Kecil Tidak Ronda',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => Carbon::now()->subYears(12)->format('Y-m-d'),
            'status' => 'aktif',
        ]);

        UserProfile::create([
            'rt_id' => $this->rt1->id,
            'nik' => $nikPengecualian,
            'nama_lengkap' => 'Warga Bebas Ronda Medis',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1985-01-01',
            'status' => 'aktif',
            'siap_ronda' => false,
            'alasan_pengecualian_ronda' => 'Riwayat penyakit asma',
        ]);

        UserProfile::create([
            'rt_id' => $this->rt1->id,
            'nik' => $nikEligible,
            'nama_lengkap' => 'Warga Pria Dewasa Siap',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1995-01-01',
            'status' => 'aktif',
            'siap_ronda' => true,
        ]);

        $response = $this->actingAs($this->ketuaRt1)->postJson(route('ronda.generate-preview'), [
            'bulan' => 11,
            'tahun' => 2026,
            'jumlah_petugas' => 1,
            'jumlah_shift' => 1,
            'jam_mulai_1' => '22:00',
            'jam_selesai_1' => '02:00',
            'lokasi_pos' => 'Pos Ronda RT 01',
        ]);

        $response->assertOk();
        $data = $response->json();

        $poolNiks = collect($data['jadwals'])->pluck('petugas')->flatten(1)->pluck('nik')->unique()->toArray();
        $this->assertContains($nikEligible, $poolNiks);
        $this->assertNotContains($nikPerempuan, $poolNiks, 'Perempuan tidak boleh dimasukkan otomatis');
        $this->assertNotContains($nikAnak, $poolNiks, 'Anak di bawah umur tidak boleh dimasukkan');
        $this->assertNotContains($nikPengecualian, $poolNiks, 'Warga yang siap_ronda = false tidak boleh dimasukkan');
    }

    /**
     * l. Generator ronda otomatis tidak menjadwalkan orang yang sama 2 kali di malam yang sama.
     */
    public function test_l_automatic_ronda_generator_does_not_duplicate_officer_in_same_night()
    {
        for ($i = 1; $i <= 8; $i++) {
            UserProfile::create([
                'rt_id' => $this->rt1->id,
                'nik' => '320118' . rand(1000000000, 9999999999),
                'nama_lengkap' => "Warga NoDupe {$i}",
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1992-01-01',
                'status' => 'aktif',
                'siap_ronda' => true,
            ]);
        }

        $response = $this->actingAs($this->ketuaRt1)->postJson(route('ronda.generate-preview'), [
            'bulan' => 11,
            'tahun' => 2026,
            'jumlah_petugas' => 2,
            'jumlah_shift' => 2,
            'jam_mulai_1' => '22:00',
            'jam_selesai_1' => '02:00',
            'jam_mulai_2' => '02:00',
            'jam_selesai_2' => '05:00',
            'lokasi_pos' => 'Pos Ronda RT 01',
        ]);

        $response->assertOk();
        $jadwals = $response->json('jadwals');

        $groupedByDate = collect($jadwals)->groupBy('tanggal');
        foreach ($groupedByDate as $date => $shifts) {
            $officersTonight = [];
            foreach ($shifts as $s) {
                foreach ($s['petugas'] as $p) {
                    $officersTonight[] = $p['user_profile_id'];
                }
            }
            $this->assertEquals(count($officersTonight), count(array_unique($officersTonight)), "Tidak boleh ada petugas ganda pada tanggal {$date}");
        }
    }

    /**
     * m. Jadwal ronda tampil di dashboard warga yang bersangkutan.
     */
    public function test_m_assigned_ronda_schedule_appears_on_warga_dashboard()
    {
        $email = 'warga_dashboard_' . uniqid() . '@rw-smart.test';
        $wargaUser = User::create([
            'name' => 'Warga Cek Dashboard',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role_id' => $this->roleWarga->id,
        ]);

        $profile = UserProfile::create([
            'user_id' => $wargaUser->id,
            'rt_id' => $this->rt1->id,
            'nama_lengkap' => $wargaUser->name,
            'status' => 'aktif',
        ]);

        $jadwal = JadwalRonda::create([
            'rt_id' => $this->rt1->id,
            'tanggal' => Carbon::today()->addDay(),
            'shift' => 'Malam Regu 1',
            'jam_mulai' => '22:00',
            'jam_selesai' => '02:00',
            'lokasi_pos' => 'Pos Induk RT 01',
            'created_by' => $this->ketuaRt1->id,
            'status' => 'aktif',
        ]);

        PetugasRonda::create([
            'jadwal_ronda_id' => $jadwal->id,
            'user_profile_id' => $profile->id,
            'user_id' => $wargaUser->id,
        ]);

        $response = $this->actingAs($wargaUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Jadwal Tugas Ronda Malam Anda');
        $response->assertSee('Malam Regu 1');
        $response->assertSee('Pos Induk RT 01');
    }

    /**
     * n. Warga yang dijadwalkan sebelum punya akun, jadwalnya muncul setelah akun aktif.
     */
    public function test_n_citizen_scheduled_before_having_account_sees_ronda_schedule_after_account_is_active()
    {
        $nik = '320120' . rand(1000000000, 9999999999);
        $noKk = '320120' . rand(1000000000, 9999999999);

        $citizenProfile = UserProfile::create([
            'user_id' => null,
            'rt_id' => $this->rt1->id,
            'nik' => $nik,
            'no_kk' => $noKk,
            'nama_lengkap' => 'Warga Jadwal Dulu Akun Nanti',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'siap_ronda' => true,
        ]);

        $jadwal = JadwalRonda::create([
            'rt_id' => $this->rt1->id,
            'tanggal' => Carbon::today()->addDays(2),
            'shift' => 'Shift Malam A',
            'jam_mulai' => '22:00',
            'jam_selesai' => '02:00',
            'lokasi_pos' => 'Pos Pantau RT 01',
            'created_by' => $this->ketuaRt1->id,
            'status' => 'aktif',
        ]);

        PetugasRonda::create([
            'jadwal_ronda_id' => $jadwal->id,
            'user_profile_id' => $citizenProfile->id,
            'user_id' => null,
        ]);

        $email = 'daftar_belakangan_' . uniqid() . '@rw-smart.test';
        $this->post(route('register'), [
            'name' => 'Warga Jadwal Dulu Akun Nanti',
            'nik' => $nik,
            'rt_id' => $this->rt1->id,
            'no_kk' => $noKk,
            'no_hp' => '081255554444',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $newUser = User::where('email', $email)->first();
        $this->assertNotNull($newUser);

        $this->actingAs($this->ketuaRt1)->post(route('warga.verifikasi.approve', $citizenProfile->id));

        $response = $this->actingAs($newUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Jadwal Tugas Ronda Malam Anda');
        $response->assertSee('Shift Malam A');
        $response->assertSee('Pos Pantau RT 01');
    }

    /**
     * o. Halaman /layanan/ronda dapat dibuka Ketua RT tanpa Error 500,
     *    dan accessor age serta tanggal lahir diproses secara aman.
     */
    public function test_o_ronda_index_page_loads_without_500_for_ketua_rt_and_citizens_with_or_without_accounts()
    {
        // Buat warga dengan tanggal_lahir sebagai date/string
        $p1 = UserProfile::create([
            'rt_id' => $this->rt1->id,
            'user_id' => null,
            'nik' => '320199' . rand(1000000000, 9999999999),
            'nama_lengkap' => 'Warga Tanpa Akun Usia 25',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => now()->subYears(25)->format('Y-m-d'),
            'status' => 'aktif',
            'siap_ronda' => true,
        ]);

        $p2 = UserProfile::create([
            'rt_id' => $this->rt1->id,
            'user_id' => null,
            'nik' => '320198' . rand(1000000000, 9999999999),
            'nama_lengkap' => 'Warga Tanpa TTL',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => null,
            'status' => 'aktif',
            'siap_ronda' => true,
        ]);

        $this->assertEquals(25, $p1->age);
        $this->assertNull($p2->age);
        $this->assertEquals('Warga Tanpa Akun Usia 25', $p1->name);

        $response = $this->actingAs($this->ketuaRt1)->get(route('layanan.ronda.index'));
        $response->assertOk();
        $response->assertSee('Warga Tanpa Akun Usia 25');
        $response->assertSee('Warga Tanpa TTL');
        $response->assertSee('Usia: 25 thn');
        $response->assertSee('modalTambahJadwal');
        $response->assertSee('officerSearchInput');
    }
}
