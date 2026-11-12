<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Balita;
use App\Models\Iuran;
use App\Models\JadwalRonda;
use App\Models\KartuKeluarga;
use App\Models\MetodePembayaran;
use App\Models\MutasiWarga;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\PetugasRonda;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\TukarJadwalRonda;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RevisiBesarRwSmartTest extends TestCase
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
                $table->string('status')->default('aktif');
                $table->boolean('siap_ronda')->default(true);
                $table->string('alasan_pengecualian_ronda')->nullable();
                $table->text('catatan_verifikasi')->nullable();
                $table->foreignId('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
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

        if (!Schema::hasTable('aspirasis')) {
            Schema::create('aspirasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('kartu_keluarga_id')->nullable();
                $table->string('judul')->nullable();
                $table->text('isi')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('metode_pembayarans')) {
            Schema::create('metode_pembayarans', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique();
                $table->string('nama', 100);
                $table->string('kategori', 50)->default('ewallet');
                $table->string('nama_penerima')->nullable();
                $table->string('nomor_rekening')->nullable();
                $table->string('qr_image')->nullable();
                $table->text('instruksi')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('iurans')) {
            Schema::create('iurans', function (Blueprint $table) {
                $table->id();
                $table->string('nama_iuran');
                $table->decimal('nominal', 12, 2);
                $table->string('periode')->default('bulanan');
                $table->text('deskripsi')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->string('no_transaksi')->nullable();
                $table->foreignId('user_id');
                $table->foreignId('iuran_id');
                $table->date('tanggal_bayar');
                $table->decimal('jumlah_bayar', 15, 2);
                $table->decimal('denda', 15, 2)->default(0);
                $table->string('metode_bayar')->default('tunai');
                $table->string('status')->default('pending');
                $table->text('bukti_bayar')->nullable();
                $table->text('bukti_transfer')->nullable();
                $table->string('bulan_tahun')->nullable();
                $table->text('keterangan')->nullable();
                $table->foreignId('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mutasi_wargas')) {
            Schema::create('mutasi_wargas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
                $table->foreignId('kartu_keluarga_id')->nullable();
                $table->foreignId('rt_id_tujuan')->nullable();
                $table->string('nama_warga')->nullable();
                $table->string('nama_kepala_keluarga')->nullable();
                $table->string('nik', 30)->nullable();
                $table->string('no_kk', 30)->nullable();
                $table->string('jenis_mutasi');
                $table->date('tanggal_mutasi');
                $table->text('alasan')->nullable();
                $table->text('keterangan')->nullable();
                $table->text('alamat_asal')->nullable();
                $table->text('alamat_tujuan')->nullable();
                $table->json('anggota_keluarga')->nullable();
                $table->string('dokumen_pendukung')->nullable();
                $table->string('status_verifikasi')->default('menunggu');
                $table->foreignId('created_by')->nullable();
                $table->foreignId('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->text('catatan_verifikasi')->nullable();
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
                $table->string('status')->default('aktif');
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

        if (!Schema::hasTable('tukar_jadwal_rondas')) {
            Schema::create('tukar_jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('jadwal_ronda_id')->nullable();
                $table->foreignId('jadwal_ronda_asal_id')->nullable();
                $table->foreignId('target_jadwal_ronda_id')->nullable();
                $table->foreignId('user_id');
                $table->foreignId('target_user_id')->nullable();
                $table->string('jenis_pengajuan')->default('tukar_rekan');
                $table->string('status')->default('menunggu');
                $table->text('alasan')->nullable();
                $table->text('catatan_rt')->nullable();
                $table->foreignId('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->dateTime('target_user_approved_at')->nullable();
                $table->dateTime('responded_at')->nullable();
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
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('from_user_id')->nullable();
                $table->foreignId('iuran_id')->nullable();
                $table->foreignId('pembayaran_id')->nullable();
                $table->string('tipe')->default('info');
                $table->string('judul')->nullable();
                $table->text('pesan')->nullable();
                $table->string('title')->nullable();
                $table->text('message')->nullable();
                $table->json('data')->nullable();
                $table->boolean('dibaca')->default(false);
                $table->boolean('is_read')->default(false);
                $table->string('link')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('action');
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('deskripsi')->nullable();
                $table->decimal('nominal', 15, 2)->nullable();
                $table->string('bukti')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kontak_darurats')) {
            Schema::create('kontak_darurats', function (Blueprint $table) {
                $table->id();
                $table->string('nama_instansi');
                $table->string('nomor_telepon');
                $table->string('kategori')->default('umum');
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('panic_reports')) {
            Schema::create('panic_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('rt_id')->nullable();
                $table->string('lokasi')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('status')->default('aktif');
                $table->foreignId('handled_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kas')) {
            Schema::create('kas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal');
                $table->string('jenis', 20);
                $table->string('kategori', 100)->nullable();
                $table->decimal('nominal', 15, 2);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('laporans')) {
            Schema::create('laporans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_laporan')->unique();
                $table->string('jenis_laporan', 50);
                $table->date('periode_awal');
                $table->date('periode_akhir');
                $table->decimal('total_pemasukan', 15, 2)->default(0);
                $table->decimal('total_pengeluaran', 15, 2)->default(0);
                $table->decimal('saldo_akhir', 15, 2)->default(0);
                $table->string('file_path')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createRoles(): array
    {
        $roleRw = Role::firstOrCreate(['nama_role' => 'rw']);
        $roleRt = Role::firstOrCreate(['nama_role' => 'rt']);
        $roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);

        return [$roleRw, $roleRt, $roleWarga];
    }

    private function createRtAndUsers(): array
    {
        [$roleRw, $roleRt, $roleWarga] = $this->createRoles();

        $rw = Rw::firstOrCreate(['kode_rw' => '05'], ['nama_rw' => 'RW 05']);

        $rt1 = Rt::firstOrCreate(['kode_rt' => '001'], ['rw_id' => $rw->id, 'nama_rt' => 'RT 01']);
        $rt2 = Rt::firstOrCreate(['kode_rt' => '002'], ['rw_id' => $rw->id, 'nama_rt' => 'RT 02']);

        $adminRw = User::create([
            'name' => 'Admin RW 05',
            'email' => 'adminrw@example.com',
            'password' => Hash::make('password'),
            'role_id' => $roleRw->id,
        ]);

        $ketuaRt1 = User::create([
            'name' => 'Ketua RT 01',
            'email' => 'ketuart1@example.com',
            'password' => Hash::make('password'),
            'role_id' => $roleRt->id,
            'rt_id' => $rt1->id,
        ]);
        $rt1->update(['ketua_user_id' => $ketuaRt1->id]);

        // Berikan UserProfile untuk Ketua RT 1
        UserProfile::create([
            'user_id' => $ketuaRt1->id,
            'role_id' => $roleRt->id,
            'nama_lengkap' => $ketuaRt1->name,
            'nik' => '3201010101019999',
            'rt_id' => $rt1->id,
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'no_hp' => '081234567890',
        ]);

        $warga1 = User::create([
            'name' => 'Warga A RT 01',
            'email' => 'wargaa@example.com',
            'password' => Hash::make('password'),
            'role_id' => $roleWarga->id,
            'rt_id' => $rt1->id,
        ]);

        $profileWarga1 = UserProfile::create([
            'user_id' => $warga1->id,
            'role_id' => $roleWarga->id,
            'nama_lengkap' => $warga1->name,
            'nik' => '3201010101010001',
            'rt_id' => $rt1->id,
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'no_hp' => '081234567891',
        ]);

        $warga2 = User::create([
            'name' => 'Warga B RT 01',
            'email' => 'wargab@example.com',
            'password' => Hash::make('password'),
            'role_id' => $roleWarga->id,
            'rt_id' => $rt1->id,
        ]);

        $profileWarga2 = UserProfile::create([
            'user_id' => $warga2->id,
            'role_id' => $roleWarga->id,
            'nama_lengkap' => $warga2->name,
            'nik' => '3201010101010002',
            'rt_id' => $rt1->id,
            'status' => 'aktif',
            'jenis_kelamin' => 'P',
            'no_hp' => '081234567892',
        ]);

        $ketuaRt2 = User::create([
            'name' => 'Ketua RT 02',
            'email' => 'ketuart2@example.com',
            'password' => Hash::make('password'),
            'role_id' => $roleRt->id,
            'rt_id' => $rt2->id,
        ]);
        $rt2->update(['ketua_user_id' => $ketuaRt2->id]);

        UserProfile::create([
            'user_id' => $ketuaRt2->id,
            'role_id' => $roleRt->id,
            'nama_lengkap' => $ketuaRt2->name,
            'nik' => '3201010101019998',
            'rt_id' => $rt2->id,
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'no_hp' => '081234567899',
        ]);

        return compact('rw', 'rt1', 'rt2', 'adminRw', 'ketuaRt1', 'ketuaRt2', 'warga1', 'warga2', 'profileWarga1', 'profileWarga2');
    }

    public function test_kk_single_source_of_truth_and_demographics_calculation(): void
    {
        $data = $this->createRtAndUsers();
        $rt1 = $data['rt1'];
        $warga1 = $data['warga1'];
        $warga2 = $data['warga2'];
        $profileWarga1 = $data['profileWarga1'];
        $profileWarga2 = $data['profileWarga2'];

        $kk = KartuKeluarga::create([
            'no_kk' => '3201012345670001',
            'rt_id' => $rt1->id,
            'kepala_keluarga_user_id' => $warga1->id,
            'alamat' => 'Jl. Mawar No. 12',
        ]);

        // Hubungkan profile ke KK
        $profileWarga1->update(['kartu_keluarga_id' => $kk->id, 'no_kk' => $kk->no_kk]);
        $profileWarga2->update(['kartu_keluarga_id' => $kk->id, 'no_kk' => $kk->no_kk]);

        $kk->refresh();

        // Verifikasi perhitungan Single Source of Truth
        $this->assertEquals(2, $kk->jumlah_jiwa);
        $this->assertEquals(1, $kk->jumlah_laki_laki);
        $this->assertEquals(1, $kk->jumlah_perempuan);
        $this->assertCount(2, $kk->anggotaAktif);
    }

    public function test_hapus_kk_kosong_vs_kk_beranggota(): void
    {
        $data = $this->createRtAndUsers();
        $rt1 = $data['rt1'];
        $ketuaRt1 = $data['ketuaRt1'];
        $profileWarga1 = $data['profileWarga1'];

        // 1. KK dengan anggota aktif
        $kkBerisi = KartuKeluarga::create([
            'no_kk' => '3201012345670002',
            'rt_id' => $rt1->id,
            'alamat' => 'Jl. Melati No. 5',
        ]);
        $profileWarga1->update(['kartu_keluarga_id' => $kkBerisi->id]);

        $responseBerisi = $this->actingAs($ketuaRt1)->delete(route('kartu-keluarga.destroy', $kkBerisi));
        $responseBerisi->assertSessionHas('error');
        $this->assertDatabaseHas('kartu_keluargas', ['id' => $kkBerisi->id]);

        // 2. KK benar-benar kosong
        $kkKosong = KartuKeluarga::create([
            'no_kk' => '3201012345670003',
            'rt_id' => $rt1->id,
            'alamat' => 'Jl. Kenanga No. 9',
        ]);

        $responseKosong = $this->actingAs($ketuaRt1)->delete(route('kartu-keluarga.destroy', $kkKosong));
        $responseKosong->assertSessionHas('success');
        $this->assertDatabaseMissing('kartu_keluargas', ['id' => $kkKosong->id]);
    }

    public function test_deaktivasi_anggota_keluarga_tanpa_kehilangan_data(): void
    {
        $data = $this->createRtAndUsers();
        $rt1 = $data['rt1'];
        $ketuaRt1 = $data['ketuaRt1'];
        $profileWarga1 = $data['profileWarga1'];

        $kk = KartuKeluarga::create([
            'no_kk' => '3201012345670004',
            'rt_id' => $rt1->id,
            'alamat' => 'Jl. Flamboyan No. 1',
        ]);
        $profileWarga1->update(['kartu_keluarga_id' => $kk->id]);

        // Lakukan pelepasan/deaktivasi anggota keluarga
        $response = $this->actingAs($ketuaRt1)->delete(route('kartu-keluarga.anggota.destroy', [$kk, $profileWarga1]), [
            'mode' => 'keluar',
        ]);

        $response->assertSessionHas('success');
        // Pastikan data record warga tetap utuh di database (bukan hard delete)
        $this->assertDatabaseHas('user_profiles', ['id' => $profileWarga1->id]);
        $profileWarga1->refresh();
        $this->assertNull($profileWarga1->kartu_keluarga_id);
    }

    public function test_metode_pembayaran_crud_and_visibility(): void
    {
        $data = $this->createRtAndUsers();
        $adminRw = $data['adminRw'];

        // Tambah metode pembayaran QRIS & Bank
        $metodeQris = MetodePembayaran::create([
            'kode' => 'qris-rw05',
            'nama' => 'QRIS RW 05',
            'kategori' => 'qris',
            'nama_penerima' => 'Bendahara RW 05',
            'is_active' => true,
        ]);

        $metodeOvo = MetodePembayaran::create([
            'kode' => 'ovo-bendahara',
            'nama' => 'OVO Bendahara',
            'kategori' => 'ewallet',
            'nama_penerima' => 'Bendahara RW 05',
            'nomor_rekening' => '081299998888',
            'is_active' => false, // Dinonaktifkan
        ]);

        $this->assertCount(1, MetodePembayaran::active()->get());

        // RW dapat toggle status aktif
        $this->actingAs($adminRw)->patch(route('rw.metode-pembayaran.toggle', $metodeOvo));
        $metodeOvo->refresh();
        $this->assertTrue((bool)$metodeOvo->is_active);
    }

    public function test_pembayaran_cegah_duplikasi_periode_dan_iuran(): void
    {
        $data = $this->createRtAndUsers();
        $rt1 = $data['rt1'];
        $warga1 = $data['warga1'];

        $iuran = Iuran::create([
            'nama_iuran' => 'Iuran Sampah RT 01',
            'nominal' => 25000,
            'rt_id' => $rt1->id,
        ]);

        $metode = MetodePembayaran::create([
            'kode' => 'bca-rt01',
            'nama' => 'Transfer BCA',
            'kategori' => 'bank',
            'nama_penerima' => 'Kas RT 01',
            'nomor_rekening' => '1234567890',
            'is_active' => true,
        ]);

        // Pembayaran pertama berhasil
        $response1 = $this->actingAs($warga1)->post(route('pembayaran.store'), [
            'user_id' => $warga1->id,
            'iuran_id' => $iuran->id,
            'jumlah_bayar' => 25000,
            'metode_bayar' => 'tunai',
            'bulan_tahun' => '2026-11',
        ]);
        $response1->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'user_id' => $warga1->id,
            'iuran_id' => $iuran->id,
            'bulan_tahun' => '2026-11',
        ]);

        // Pembayaran kedua untuk periode dan iuran yang sama harus DITOLAK
        $response2 = $this->actingAs($warga1)->post(route('pembayaran.store'), [
            'user_id' => $warga1->id,
            'iuran_id' => $iuran->id,
            'jumlah_bayar' => 25000,
            'metode_bayar' => 'tunai',
            'bulan_tahun' => '2026-11',
        ]);
        $response2->assertSessionHas('error');
    }

    public function test_mutasi_warga_4_tab_flows(): void
    {
        $data = $this->createRtAndUsers();
        $rt1 = $data['rt1'];
        $rt2 = $data['rt2'];
        $ketuaRt1 = $data['ketuaRt1'];
        $ketuaRt2 = $data['ketuaRt2'];
        $profileWarga1 = $data['profileWarga1'];

        // Tab 1: Mutasi Masuk
        $responseMasuk = $this->actingAs($ketuaRt1)->post(route('warga.mutasi.masuk.store'), [
            'nama_kepala_keluarga' => 'Budi Santoso',
            'nik' => '3201010101010099',
            'no_kk' => '3201012345670099',
            'alamat_asal' => 'Kelurahan Suka Maju',
            'alamat_tujuan' => 'RT 01 RW 05 Blok C4',
            'tanggal_mutasi' => now()->toDateString(),
            'alasan' => 'Pindah kerja ke kota ini',
        ]);
        $responseMasuk->assertSessionHas('success');
        $this->assertDatabaseHas('mutasi_wargas', [
            'jenis_mutasi' => 'masuk',
            'nik' => '3201010101010099',
            'status_verifikasi' => 'menunggu',
        ]);

        // Tab 2: Mutasi Keluar
        $responseKeluar = $this->actingAs($ketuaRt1)->post(route('warga.mutasi.keluar.store'), [
            'tipe_keluar' => 'individu',
            'user_profile_id' => $profileWarga1->id,
            'alamat_tujuan' => 'Kota Surabaya',
            'tanggal_mutasi' => now()->toDateString(),
            'keterangan' => 'Pindah domisili permanen',
        ]);
        $responseKeluar->assertSessionHas('success');
        $profileWarga1->refresh();
        $this->assertEquals('pindah', $profileWarga1->status);

        // Kembalikan ke aktif untuk tes antar RT
        $profileWarga1->update(['status' => 'aktif']);

        // Tab 3: Mutasi Antar-RT (Pengajuan RT Asal -> Persetujuan RT Tujuan)
        $responseAntarRt = $this->actingAs($ketuaRt1)->post(route('warga.mutasi.antar-rt.store'), [
            'user_profile_id' => $profileWarga1->id,
            'rt_id_tujuan' => $rt2->id,
            'tanggal_mutasi' => now()->toDateString(),
            'keterangan' => 'Tukar unit rumah',
        ]);
        $responseAntarRt->assertSessionHas('success');

        $mutasiAntarRt = MutasiWarga::where('jenis_mutasi', 'pindah_rt')->latest()->first();
        $this->assertNotNull($mutasiAntarRt);
        $this->assertEquals('menunggu_tujuan', $mutasiAntarRt->status_verifikasi);

        // Ketua RT 2 (Tujuan) menyetujui mutasi masuk
        $responseApprove = $this->actingAs($ketuaRt2)->post(route('warga.mutasi.approve', $mutasiAntarRt));
        $responseApprove->assertSessionHas('success');

        $profileWarga1->refresh();
        $this->assertEquals($rt2->id, $profileWarga1->rt_id);
    }

    public function test_pendaftaran_warga_baru_dengan_rt_selection_and_verification(): void
    {
        $data = $this->createRtAndUsers();
        $rt1 = $data['rt1'];
        $ketuaRt1 = $data['ketuaRt1'];

        // Register warga baru
        $responseRegister = $this->post(route('register'), [
            'name' => 'Warga Calon',
            'email' => 'calon@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3201010101010088',
            'no_kk' => '3201010101010088',
            'jenis_kelamin' => 'L',
            'rt_id' => $rt1->id,
            'no_hp' => '081299991111',
            'alamat' => 'Blok B1 No. 4',
        ]);
        $responseRegister->assertRedirect();

        $calonUser = User::where('email', 'calon@example.com')->first();
        $this->assertNotNull($calonUser);
        $this->assertEquals('pending', $calonUser->profile->status);

        // Notifikasi harus terkirim ke Ketua RT
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ketuaRt1->id,
        ]);

        // Verifikasi oleh RT
        $responseVerify = $this->actingAs($ketuaRt1)->post(route('warga.verify', $calonUser->id), [
            'status' => 'aktif',
            'role_id' => Role::where('nama_role', 'warga')->first()->id,
        ]);
        $responseVerify->assertSessionHas('success');
        $calonUser->profile->refresh();
        $this->assertEquals('aktif', $calonUser->profile->status);
    }

    public function test_keamanan_ronda_kalender_and_alur_tukar_shift(): void
    {
        $data = $this->createRtAndUsers();
        $rt1 = $data['rt1'];
        $ketuaRt1 = $data['ketuaRt1'];
        $warga1 = $data['warga1'];
        $warga2 = $data['warga2'];

        // Buat jadwal ronda untuk warga1
        $jadwal1 = JadwalRonda::create([
            'rt_id' => $rt1->id,
            'tanggal' => now('Asia/Jakarta')->addDays(2)->toDateString(),
            'shift' => 'malam',
            'lokasi_pos' => 'Pos Ronda Barat',
            'status' => 'aktif',
        ]);
        PetugasRonda::create([
            'jadwal_ronda_id' => $jadwal1->id,
            'user_id' => $warga1->id,
        ]);

        // Buat jadwal ronda untuk warga2
        $jadwal2 = JadwalRonda::create([
            'rt_id' => $rt1->id,
            'tanggal' => now('Asia/Jakarta')->addDays(3)->toDateString(),
            'shift' => 'malam',
            'lokasi_pos' => 'Pos Ronda Barat',
            'status' => 'aktif',
        ]);
        PetugasRonda::create([
            'jadwal_ronda_id' => $jadwal2->id,
            'user_id' => $warga2->id,
        ]);

        // ================= ALUR 1: Tukar dengan Rekan =================
        $responseTukar1 = $this->actingAs($warga1)->post(route('ronda.tukar.store'), [
            'jenis_pengajuan' => 'tukar_rekan',
            'jadwal_ronda_asal_id' => $jadwal1->id,
            'target_user_id' => $warga2->id,
            'target_jadwal_ronda_id' => $jadwal2->id,
            'alasan' => 'Ada acara hajatan keluarga',
        ]);
        $responseTukar1->assertSessionHas('success');

        $tukar1 = TukarJadwalRonda::where('user_id', $warga1->id)->latest()->first();
        $this->assertNotNull($tukar1);
        $this->assertEquals('menunggu_rekan', $tukar1->status);

        // Rekan (Warga2) merespon setuju
        $responseRekan = $this->actingAs($warga2)->patch(route('ronda.tukar.respon', $tukar1), [
            'status' => 'disetujui',
        ]);
        $responseRekan->assertSessionHas('success');
        $tukar1->refresh();
        $this->assertEquals('menunggu_rt', $tukar1->status);

        // Ketua RT memberikan persetujuan final -> jadwal ditukar secara atomik
        $responseRt = $this->actingAs($ketuaRt1)->patch(route('ronda.tukar.respon', $tukar1), [
            'status' => 'disetujui',
        ]);
        $responseRt->assertSessionHas('success');
        $tukar1->refresh();
        $this->assertEquals('disetujui', $tukar1->status);

        // Verifikasi jadwal ditukar secara atomik
        $this->assertDatabaseHas('petugas_rondas', [
            'jadwal_ronda_id' => $jadwal1->id,
            'user_id' => $warga2->id,
        ]);
        $this->assertDatabaseHas('petugas_rondas', [
            'jadwal_ronda_id' => $jadwal2->id,
            'user_id' => $warga1->id,
        ]);

        // ================= ALUR 2: Minta Pengganti Melalui RT =================
        $jadwal3 = JadwalRonda::create([
            'rt_id' => $rt1->id,
            'tanggal' => now('Asia/Jakarta')->addDays(5)->toDateString(),
            'shift' => 'malam',
            'lokasi_pos' => 'Pos Ronda Utama',
            'status' => 'aktif',
        ]);
        PetugasRonda::create([
            'jadwal_ronda_id' => $jadwal3->id,
            'user_id' => $warga1->id,
        ]);

        $responseTukar2 = $this->actingAs($warga1)->post(route('ronda.tukar.store'), [
            'jenis_pengajuan' => 'minta_pengganti',
            'jadwal_ronda_asal_id' => $jadwal3->id,
            'alasan' => 'Dinas luar pulau dadakan',
        ]);
        $responseTukar2->assertSessionHas('success');

        $tukar2 = TukarJadwalRonda::where('jadwal_ronda_asal_id', $jadwal3->id)->latest()->first();
        $this->assertEquals('menunggu_rt', $tukar2->status);

        // RT menyetujui dan menentukan warga pengganti (warga2)
        $responseRt2 = $this->actingAs($ketuaRt1)->patch(route('ronda.tukar.respon', $tukar2), [
            'status' => 'disetujui',
            'pengganti_user_id' => $warga2->id,
        ]);
        $responseRt2->assertSessionHas('success');
        $tukar2->refresh();
        $this->assertEquals('disetujui', $tukar2->status);

        // Petugas di jadwal3 sekarang harus menjadi warga2
        $this->assertDatabaseHas('petugas_rondas', [
            'jadwal_ronda_id' => $jadwal3->id,
            'user_id' => $warga2->id,
        ]);
        $this->assertDatabaseMissing('petugas_rondas', [
            'jadwal_ronda_id' => $jadwal3->id,
            'user_id' => $warga1->id,
        ]);
    }

    public function test_laporan_generate_and_layanan_tukar_ronda_status(): void
    {
        $data = $this->createRtAndUsers();
        $adminRw = $data['adminRw'];
        $warga1 = $data['warga1'];
        $warga2 = $data['warga2'];
        $rt1 = $data['rt1'];

        // 1. Test generate laporan keuangan agregat RW
        $responseLaporan = $this->actingAs($adminRw)->post(route('laporan.generate'), [
            'jenis_laporan' => 'keuangan_gabungan',
            'periode_awal' => now()->startOfMonth()->toDateString(),
            'periode_akhir' => now()->toDateString(),
        ]);
        $responseLaporan->assertRedirect();
        $this->assertDatabaseHas('laporans', [
            'jenis_laporan' => 'keuangan_gabungan',
            'created_by' => $adminRw->id,
        ]);

        // 2. Test tukar ronda status flow melalui LayananMasyarakatController
        $jadwalA = JadwalRonda::create([
            'rt_id' => $rt1->id,
            'tanggal' => now('Asia/Jakarta')->addDays(2)->toDateString(),
            'shift' => 'malam',
            'lokasi_pos' => 'Pos Layanan Ronda',
            'status' => 'aktif',
        ]);
        $jadwalB = JadwalRonda::create([
            'rt_id' => $rt1->id,
            'tanggal' => now('Asia/Jakarta')->addDays(4)->toDateString(),
            'shift' => 'malam',
            'lokasi_pos' => 'Pos Layanan Ronda',
            'status' => 'aktif',
        ]);

        $responseTukar = $this->actingAs($warga1)->post(route('ronda.tukar.store'), [
            'jenis_pengajuan' => 'tukar_rekan',
            'jadwal_ronda_asal_id' => $jadwalA->id,
            'target_user_id' => $warga2->id,
            'target_jadwal_ronda_id' => $jadwalB->id,
            'alasan' => 'Tukar shift dinas malam',
        ]);
        $responseTukar->assertRedirect();
        $this->assertDatabaseHas('tukar_jadwal_rondas', [
            'user_id' => $warga1->id,
            'target_user_id' => $warga2->id,
            'status' => 'menunggu_rekan',
        ]);
    }
}
