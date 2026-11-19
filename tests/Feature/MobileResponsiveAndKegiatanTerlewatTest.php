<?php

namespace Tests\Feature;

use App\Models\KartuKeluarga;
use App\Models\Kegiatan;
use App\Models\KegiatanPeserta;
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

class MobileResponsiveAndKegiatanTerlewatTest extends TestCase
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
                $table->string('kode_rt')->unique();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('ketua_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kartu_keluargas')) {
            Schema::create('kartu_keluargas', function (Blueprint $table) {
                $table->id();
                $table->string('no_kk')->unique();
                $table->string('nama_kepala_keluarga');
                $table->foreignId('kepala_keluarga_user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->text('alamat')->nullable();
                $table->string('status_rumah')->default('milik_sendiri');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->string('nik', 16)->nullable()->unique();
                $table->string('nama_lengkap');
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('kartu_keluarga_id')->nullable();
                $table->enum('status_hubungan_keluarga', ['Kepala Keluarga', 'Istri', 'Anak', 'Famili Lain'])->default('Kepala Keluarga');
                $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
                $table->date('tanggal_lahir')->nullable();
                $table->string('pekerjaan')->nullable();
                $table->string('no_hp')->nullable();
                $table->string('rfid_uid')->nullable();
                $table->enum('status', ['aktif', 'pindah', 'meninggal', 'pending', 'ditolak'])->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->string('cakupan')->default('rw');
                $table->string('judul');
                $table->string('kategori');
                $table->dateTime('tanggal_mulai');
                $table->dateTime('tanggal_selesai')->nullable();
                $table->string('lokasi');
                $table->integer('kuota_peserta')->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('status')->default('dibuka');
                $table->foreignId('created_by')->nullable();
                $table->string('creator_role')->nullable();
                $table->foreignId('deleted_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
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
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('from_user_id')->nullable();
                $table->string('judul');
                $table->text('pesan');
                $table->string('tipe')->default('info');
                $table->string('tautan')->nullable();
                $table->boolean('dibaca')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengumumans')) {
            Schema::create('pengumumans', function (Blueprint $table) {
                $table->id();
                $table->string('judul');
                $table->text('isi');
                $table->string('kategori')->default('Umum');
                $table->string('target_audience')->default('semua_rt');
                $table->foreignId('rt_id')->nullable();
                $table->dateTime('tanggal_publikasi')->nullable();
                $table->string('status')->default('published');
                $table->string('lampiran')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createWargaUser(string $name, string $nik): User
    {
        $roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);
        $rw = Rw::firstOrCreate(['kode_rw' => '01'], ['nama_rw' => 'RW 01']);
        $rt = Rt::firstOrCreate(['kode_rt' => '02'], ['rw_id' => $rw->id]);

        $user = User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '_' . substr($nik, -4) . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleWarga->id,
            'rt_id' => $rt->id,
        ]);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nik' => $nik,
                'nama_lengkap' => $name,
                'role_id' => $roleWarga->id,
                'rt_id' => $rt->id,
                'rw_id' => $rw->id,
                'status' => 'aktif',
            ]
        );

        return $user;
    }

    public function test_kegiatan_terlewat_tidak_bisa_didaftar_di_backend(): void
    {
        $warga = $this->createWargaUser('Budi Santoso', '3201010101010001');

        // Kegiatan yang sudah selesai di masa lalu
        $kegiatanLewat = Kegiatan::create([
            'judul' => 'Kerja Bakti Bersih Selokan Lampau',
            'kategori' => 'kerja_bakti',
            'cakupan' => 'rw',
            'tanggal_mulai' => Carbon::now()->subDays(6)->setTime(8, 0),
            'tanggal_selesai' => Carbon::now()->subDays(6)->setTime(11, 0),
            'lokasi' => 'Balai Warga',
            'status' => 'dibuka', // status di DB masih 'dibuka', tapi waktu sudah lewat
        ]);

        $this->assertTrue($kegiatanLewat->isPast());

        // Coba daftarkan via API/Endpoint POST /kegiatan/{kegiatan}/daftar
        $response = $this->actingAs($warga)->postJson(route('kegiatan.daftar', $kegiatanLewat));

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Pendaftaran ditutup karena kegiatan telah selesai.',
        ]);

        // Pastikan tidak ada data yang tersimpan di kegiatan_pesertas
        $this->assertDatabaseMissing('kegiatan_pesertas', [
            'kegiatan_id' => $kegiatanLewat->id,
            'user_id' => $warga->id,
        ]);
    }

    public function test_kegiatan_mendatang_dapat_didaftar(): void
    {
        $warga = $this->createWargaUser('Siti Aminah', '3201010101010002');

        // Kegiatan di masa depan
        $kegiatanMendatang = Kegiatan::create([
            'judul' => 'Senam Sehat Masa Depan',
            'kategori' => 'olahraga',
            'cakupan' => 'rw',
            'tanggal_mulai' => Carbon::now()->addDays(5)->setTime(6, 30),
            'tanggal_selesai' => Carbon::now()->addDays(5)->setTime(8, 30),
            'lokasi' => 'Lapangan Utama',
            'status' => 'dibuka',
        ]);

        $this->assertFalse($kegiatanMendatang->isPast());

        $response = $this->actingAs($warga)->postJson(route('kegiatan.daftar', $kegiatanMendatang));

        $response->assertStatus(201);
        $this->assertDatabaseHas('kegiatan_pesertas', [
            'kegiatan_id' => $kegiatanMendatang->id,
            'user_id' => $warga->id,
            'status' => 'terdaftar',
        ]);
    }

    public function test_tampilan_kegiatan_terlewat_menampilkan_status_selesai_dan_tombol_pendaftaran_ditutup(): void
    {
        $warga = $this->createWargaUser('Ahmad Dahlan', '3201010101010003');

        $kegiatanLewat = Kegiatan::create([
            'judul' => 'Rapat Tahunan Warga Lampau',
            'kategori' => 'rapat',
            'cakupan' => 'rw',
            'tanggal_mulai' => Carbon::now()->subDays(10)->setTime(19, 0),
            'tanggal_selesai' => Carbon::now()->subDays(10)->setTime(21, 0),
            'lokasi' => 'Ruang Serbaguna',
            'status' => 'dibuka', // sengaja berstatus dibuka di DB
        ]);

        $response = $this->actingAs($warga)->get(route('kegiatan.index', [
            'bulan' => Carbon::now()->subDays(10)->month,
            'tahun' => Carbon::now()->subDays(10)->year,
        ]));

        $response->assertStatus(200);

        // Status badge harus menampilkan "Selesai" (bukan "Akan Datang")
        $response->assertSee('Selesai');
        // Tombol harus "Pendaftaran Ditutup"
        $response->assertSee('Pendaftaran Ditutup');
    }

    public function test_pengumuman_header_badge_rt_menggunakan_flex_dan_tidak_menumpuk(): void
    {
        $warga = $this->createWargaUser('Dewi Sartika', '3201010101010004');

        $response = $this->actingAs($warga)->get(route('warga.pengumuman'));

        $response->assertStatus(200);
        $response->assertSee('badge-rt-pill');
        $response->assertSee('position: static !important; display: inline-flex !important;', false);
    }

    public function test_dropdown_kategori_pengumuman_memiliki_styling_halus_dan_jarak_label(): void
    {
        $warga = $this->createWargaUser('Dewi Sartika 2', '3201010101010005');

        $response = $this->actingAs($warga)->get(route('warga.pengumuman'));

        $response->assertStatus(200);
        $response->assertSee('Kategori Pengumuman');
        $response->assertSee('Semua Kategori');
        $response->assertSee('name="kategori"', false);
        $response->assertSee('form-select-saas');
        $response->assertSee('rounded-xl');
        $response->assertSee('border-slate-200');
        $response->assertSee('px-3.5');
        $response->assertSee('py-2');
        $response->assertSee('appearance-none');
        $response->assertSee('gap-2.5');
    }
}
