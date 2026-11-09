<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\JadwalRonda;
use App\Models\KartuKeluarga;
use App\Models\Kegiatan;
use App\Models\PemeriksaanBalita;
use App\Models\PetugasRonda;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PosyanduKkAndAgendaTest extends TestCase
{
    use DatabaseTransactions;

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
                $table->string('no_hp', 15)->nullable();
                $table->string('pekerjaan')->nullable();
                $table->string('status_perkawinan')->nullable();
                $table->string('status_hubungan_keluarga')->nullable();
                $table->string('pendidikan')->nullable();
                $table->string('nama_ayah')->nullable();
                $table->string('nama_ibu')->nullable();
                $table->string('foto')->nullable();
                $table->enum('status', ['pending', 'aktif', 'nonaktif'])->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_posyandus')) {
            Schema::create('jadwal_posyandus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->string('judul');
                $table->dateTime('tanggal_mulai');
                $table->dateTime('tanggal_selesai')->nullable();
                $table->string('lokasi');
                $table->string('nama_bidan')->nullable();
                $table->string('kontak_bidan')->nullable();
                $table->string('kader_info')->nullable();
                $table->string('status')->default('terjadwal');
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('balitas')) {
            Schema::create('balitas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kartu_keluarga_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
                $table->foreignId('orang_tua_user_id')->nullable();
                $table->string('nama');
                $table->string('nik', 20)->nullable();
                $table->date('tanggal_lahir');
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->string('nama_orang_tua', 255)->nullable();
                $table->string('no_hp_orang_tua', 50)->nullable();
                $table->text('catatan_kesehatan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pemeriksaan_balitas')) {
            Schema::create('pemeriksaan_balitas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('balita_id');
                $table->foreignId('jadwal_posyandu_id')->nullable();
                $table->date('tanggal_periksa');
                $table->decimal('berat_kg', 5, 2)->nullable();
                $table->decimal('tinggi_cm', 5, 2)->nullable();
                $table->decimal('lingkar_kepala_cm', 5, 2)->nullable();
                $table->json('imunisasi_sudah')->nullable();
                $table->json('imunisasi_belum')->nullable();
                $table->text('catatan')->nullable();
                $table->foreignId('petugas_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_rondas')) {
            Schema::create('jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal');
                $table->string('shift', 20)->default('malam');
                $table->string('lokasi_pos')->nullable();
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petugas_rondas')) {
            Schema::create('petugas_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id');
                $table->string('status_kehadiran')->default('belum_hadir');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('absensi_rondas')) {
            Schema::create('absensi_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id');
                $table->dateTime('waktu_absen');
                $table->string('status')->default('hadir');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tukar_jadwal_rondas')) {
            Schema::create('tukar_jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id');
                $table->foreignId('target_user_id')->nullable();
                $table->string('status')->default('menunggu');
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

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('cakupan')->default('rt');
                $table->string('judul');
                $table->text('deskripsi')->nullable();
                $table->string('kategori')->default('sosial');
                $table->string('lokasi');
                $table->dateTime('tanggal_mulai');
                $table->dateTime('tanggal_selesai')->nullable();
                $table->integer('kuota_peserta')->nullable();
                $table->string('status')->default('dibuka');
                $table->foreignId('created_by')->nullable();
                $table->string('creator_role')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('kegiatan_pesertas')) {
            Schema::create('kegiatan_pesertas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kegiatan_id');
                $table->foreignId('user_id');
                $table->string('status')->default('terdaftar');
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
                $table->timestamps();
            });
        }
    }

    private function createRole(string $name): Role
    {
        return Role::firstOrCreate(['nama_role' => $name]);
    }

    private function createRt(string $kodeRt = '01'): array
    {
        $rwRole = $this->createRole('admin_rw');
        $rtRole = $this->createRole('rt');

        $rw = Rw::firstOrCreate(['kode_rw' => '05'], ['nama_rw' => 'RW 05 Sejahtera']);
        $rt = Rt::firstOrCreate(['kode_rt' => $kodeRt, 'rw_id' => $rw->id], ['nama_rt' => 'RT ' . $kodeRt]);

        $ketuaRt = User::create([
            'name' => 'Ketua RT ' . $kodeRt,
            'email' => 'rt' . $kodeRt . '_' . uniqid() . '@rw-smart.test',
            'password' => bcrypt('password123'),
            'role_id' => $rtRole->id,
            'rt_id' => $rt->id,
        ]);
        $rt->update(['ketua_user_id' => $ketuaRt->id]);

        UserProfile::create([
            'user_id' => $ketuaRt->id,
            'role_id' => $rtRole->id,
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'nama_lengkap' => $ketuaRt->name,
            'status' => 'aktif',
        ]);

        return [$rw, $rt, $ketuaRt];
    }

    public function test_ronda_view_renders_cleanly_without_parse_error()
    {
        [$rw, $rt, $ketuaRt] = $this->createRt('01');

        JadwalRonda::create([
            'rt_id' => $rt->id,
            'tanggal' => now()->toDateString(),
            'shift' => 'malam',
            'lokasi_pos' => 'Pos Ronda RT 01',
            'keterangan' => 'Ronda Rutin RT',
        ]);

        $response = $this->actingAs($ketuaRt)->get(route('layanan.ronda.index'));
        $response->assertStatus(200);
        $response->assertSee('Keamanan &amp; Ronda', false);
        $response->assertSee('Pos Ronda RT 01');
    }

    public function test_posyandu_page_loads_and_lists_kk_and_eligible_children()
    {
        [$rw, $rt, $ketuaRt] = $this->createRt('01');
        $wargaRole = $this->createRole('warga');

        $kk = KartuKeluarga::create([
            'no_kk' => '3201012345670001',
            'rt_id' => $rt->id,
            'alamat' => 'Jl. Kenanga No. 12',
        ]);

        $kepala = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi_' . uniqid() . '@warga.test',
            'password' => bcrypt('password123'),
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
        ]);
        $kk->update(['kepala_keluarga_user_id' => $kepala->id]);

        $childProfile = UserProfile::create([
            'user_id' => null,
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'kartu_keluarga_id' => $kk->id,
            'nik' => '3201012345670003',
            'nama_lengkap' => 'Ananda Budi',
            'tanggal_lahir' => '2024-05-10',
            'jenis_kelamin' => 'L',
            'status_hubungan_keluarga' => 'Anak',
            'nama_ayah' => 'Budi Santoso',
            'nama_ibu' => 'Siti Aminah',
            'no_hp' => '081234567890',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($ketuaRt)->get(route('layanan.posyandu.index'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Data Anak dari Kartu Keluarga');
        $response->assertSee('3201012345670001');
        $response->assertSee('Budi Santoso');
    }

    public function test_rt_can_register_child_from_kk_data_successfully()
    {
        [$rw, $rt, $ketuaRt] = $this->createRt('01');
        $wargaRole = $this->createRole('warga');

        $kk = KartuKeluarga::create([
            'no_kk' => '3201012345670002',
            'rt_id' => $rt->id,
            'alamat' => 'Jl. Mawar No. 5',
        ]);

        $childProfile = UserProfile::create([
            'user_id' => null,
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'kartu_keluarga_id' => $kk->id,
            'nik' => '3201019988770001',
            'nama_lengkap' => 'Rizky Pratama',
            'tanggal_lahir' => '2024-02-15',
            'jenis_kelamin' => 'L',
            'status_hubungan_keluarga' => 'Anak',
            'nama_ayah' => 'Ahmad Pratama',
            'nama_ibu' => 'Rina Marlina',
            'no_hp' => '081987654321',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($ketuaRt)->post(route('layanan.posyandu.balita.store'), [
            'kartu_keluarga_id' => $kk->id,
            'user_profile_id' => $childProfile->id,
            'nama' => 'Rizky Pratama',
            'nik' => '3201019988770001',
            'tanggal_lahir' => '2024-02-15',
            'jenis_kelamin' => 'L',
            'nama_orang_tua' => 'Ahmad Pratama',
            'no_hp_orang_tua' => '081987654321',
            'catatan_kesehatan' => 'Alergi susu sapi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('balitas', [
            'nama' => 'Rizky Pratama',
            'nik' => '3201019988770001',
            'kartu_keluarga_id' => $kk->id,
            'user_profile_id' => $childProfile->id,
            'nama_orang_tua' => 'Ahmad Pratama',
            'no_hp_orang_tua' => '081987654321',
            'catatan_kesehatan' => 'Alergi susu sapi',
        ]);
    }

    public function test_duplicate_child_registration_is_prevented()
    {
        [$rw, $rt, $ketuaRt] = $this->createRt('01');
        $wargaRole = $this->createRole('warga');

        $kk = KartuKeluarga::create([
            'no_kk' => '3201012345670003',
            'rt_id' => $rt->id,
            'alamat' => 'Jl. Melati No. 8',
        ]);

        $childProfile = UserProfile::create([
            'user_id' => null,
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'kartu_keluarga_id' => $kk->id,
            'nik' => '3201019988770002',
            'nama_lengkap' => 'Alya Putri',
            'tanggal_lahir' => '2024-03-20',
            'jenis_kelamin' => 'P',
            'status_hubungan_keluarga' => 'Anak',
            'nama_ayah' => 'Doni Saputra',
            'nama_ibu' => 'Lestari',
            'no_hp' => '081234567899',
            'status' => 'aktif',
        ]);

        // Register first time
        Balita::create([
            'rt_id' => $rt->id,
            'kartu_keluarga_id' => $kk->id,
            'user_profile_id' => $childProfile->id,
            'nama' => 'Alya Putri',
            'nik' => '3201019988770002',
            'tanggal_lahir' => '2024-03-20',
            'jenis_kelamin' => 'P',
            'nama_orang_tua' => 'Doni Saputra',
        ]);

        // Attempt duplicate registration with same user_profile_id
        $response = $this->actingAs($ketuaRt)->post(route('layanan.posyandu.balita.store'), [
            'kartu_keluarga_id' => $kk->id,
            'user_profile_id' => $childProfile->id,
            'nama' => 'Alya Putri',
            'nik' => '3201019988770002',
            'tanggal_lahir' => '2024-03-20',
            'jenis_kelamin' => 'P',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Balita::where('nik', '3201019988770002')->count());
    }

    public function test_child_from_another_rt_is_rejected_due_to_rt_isolation()
    {
        [$rw, $rt1, $ketuaRt1] = $this->createRt('01');
        [$rwB, $rt2, $ketuaRt2] = $this->createRt('02');
        $wargaRole = $this->createRole('warga');

        $kkRt2 = KartuKeluarga::create([
            'no_kk' => '3201012345670004',
            'rt_id' => $rt2->id,
            'alamat' => 'Jl. Cempaka No. 20',
        ]);

        $childRt2 = UserProfile::create([
            'user_id' => null,
            'role_id' => $wargaRole->id,
            'rt_id' => $rt2->id,
            'rw_id' => $rw->id,
            'kartu_keluarga_id' => $kkRt2->id,
            'nik' => '3201019988770004',
            'nama_lengkap' => 'Anak RT Dua',
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'status_hubungan_keluarga' => 'Anak',
            'status' => 'aktif',
        ]);

        // Ketua RT 1 tries to register child from RT 2 -> must be 403 Forbidden
        $response = $this->actingAs($ketuaRt1)->post(route('layanan.posyandu.balita.store'), [
            'kartu_keluarga_id' => $kkRt2->id,
            'user_profile_id' => $childRt2->id,
            'nama' => 'Anak RT Dua',
            'nik' => '3201019988770004',
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
        ]);

        $response->assertStatus(403);
    }

    public function test_warga_parent_can_view_their_registered_child_in_posyandu()
    {
        [$rw, $rt, $ketuaRt] = $this->createRt('01');
        $wargaRole = $this->createRole('warga');

        $kk = KartuKeluarga::create([
            'no_kk' => '3201012345670005',
            'rt_id' => $rt->id,
            'alamat' => 'Jl. Flamboyan No. 3',
        ]);

        $wargaParent = User::create([
            'name' => 'Hendro Wijaya',
            'email' => 'hendro_' . uniqid() . '@warga.test',
            'password' => bcrypt('password123'),
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
        ]);
        $kk->update(['kepala_keluarga_user_id' => $wargaParent->id]);

        $parentProfile = UserProfile::create([
            'user_id' => $wargaParent->id,
            'role_id' => $wargaRole->id,
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'kartu_keluarga_id' => $kk->id,
            'nama_lengkap' => 'Hendro Wijaya',
            'status' => 'aktif',
        ]);

        $balita = Balita::create([
            'rt_id' => $rt->id,
            'kartu_keluarga_id' => $kk->id,
            'orang_tua_user_id' => $wargaParent->id,
            'nama' => 'Bintang Hendro',
            'nik' => '3201019988770005',
            'tanggal_lahir' => '2024-04-10',
            'jenis_kelamin' => 'L',
            'nama_orang_tua' => 'Hendro Wijaya',
        ]);

        $response = $this->actingAs($wargaParent)->get(route('layanan.posyandu.index'));
        $response->assertStatus(200);
        $response->assertSee('Bintang Hendro');
    }

    public function test_kegiatan_rw_agenda_page_loads_with_filters_and_grouping()
    {
        [$rw, $rt, $ketuaRt] = $this->createRt('01');

        Kegiatan::create([
            'rw_id' => $rw->id,
            'rt_id' => null,
            'cakupan' => 'rw',
            'judul' => 'Senam Sehat RW 05',
            'deskripsi' => 'Senam bersama warga RW 05 di balai warga.',
            'kategori' => 'olahraga',
            'lokasi' => 'Balai RW 05',
            'tanggal_mulai' => now()->startOfMonth()->addDays(5)->setTime(7, 0),
            'tanggal_selesai' => now()->startOfMonth()->addDays(5)->setTime(9, 0),
            'kuota_peserta' => 100,
            'status' => 'dibuka',
            'created_by' => $ketuaRt->id,
        ]);

        $response = $this->actingAs($ketuaRt)->get(route('kegiatan.index', ['tingkat' => 'rw']));
        $response->assertStatus(200);
        $response->assertSee('Senam Sehat RW 05');
        $response->assertSee('Balai RW 05');
        $response->assertSee('Kegiatan Bulan Ini');
        $response->assertSee('Bulan Mendatang');
        $response->assertSee('Arsip Kegiatan');
        $response->assertSee('Terapkan Filter', false);
    }

    public function test_kegiatan_search_and_status_filtering()
    {
        [$rw, $rt, $ketuaRt] = $this->createRt('01');

        Kegiatan::create([
            'rw_id' => $rw->id,
            'cakupan' => 'rw',
            'judul' => 'Bakti Sosial Donor Darah RW',
            'deskripsi' => 'Donor darah PMI bersama PMI Kota.',
            'kategori' => 'sosial',
            'lokasi' => 'Puskesmas Pembantu',
            'tanggal_mulai' => now()->setTime(8, 0),
            'status' => 'dibuka',
            'created_by' => $ketuaRt->id,
        ]);

        Kegiatan::create([
            'rw_id' => $rw->id,
            'cakupan' => 'rw',
            'judul' => 'Pengajian Rutin Bulanan',
            'deskripsi' => 'Kajian akbar warga di masjid.',
            'kategori' => 'keagamaan',
            'lokasi' => 'Masjid Al-Ikhlas',
            'tanggal_mulai' => now()->setTime(19, 30),
            'status' => 'selesai',
            'created_by' => $ketuaRt->id,
        ]);

        // Filter by keyword 'Donor'
        $response = $this->actingAs($ketuaRt)->get(route('kegiatan.index', ['q' => 'Donor']));
        $response->assertStatus(200);
        $response->assertSee('Bakti Sosial Donor Darah RW');
        $response->assertDontSee('Pengajian Rutin Bulanan');

        // Filter by status 'selesai'
        $responseStatus = $this->actingAs($ketuaRt)->get(route('kegiatan.index', ['status' => 'selesai']));
        $responseStatus->assertStatus(200);
        $responseStatus->assertSee('Pengajian Rutin Bulanan');
        $responseStatus->assertDontSee('Bakti Sosial Donor Darah RW');
    }
}
