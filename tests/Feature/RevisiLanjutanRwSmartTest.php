<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\JenisSurat;
use App\Models\Kegiatan;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RevisiLanjutanRwSmartTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role')->unique();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('jenis_kelamin')->nullable();
            $table->foreignId('role_id')->nullable();
            $table->foreignId('rt_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('rws', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rw')->unique();
            $table->string('nama_rw')->nullable();
            $table->foreignId('ketua_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rw_id');
            $table->string('kode_rt');
            $table->string('nama_rt')->nullable();
            $table->foreignId('ketua_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('role_id')->nullable();
            $table->foreignId('rw_id')->nullable();
            $table->foreignId('rt_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nik')->nullable();
            $table->string('no_kk')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });

        Schema::create('kartu_keluargas', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk')->unique();
            $table->foreignId('rt_id');
            $table->foreignId('kepala_keluarga_id')->nullable();
            $table->string('alamat')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });

        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rw_id')->nullable();
            $table->foreignId('rt_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('kategori')->default('lainnya');
            $table->string('status')->default('dibuka');
            $table->string('cakupan')->default('rt');
            $table->string('lokasi')->nullable();
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->integer('kuota_peserta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('kegiatan_pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id');
            $table->foreignId('user_id');
            $table->string('catatan')->nullable();
            $table->string('status')->default('terdaftar');
            $table->timestamps();
        });

        Schema::create('jenis_surats', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->boolean('perlu_persetujuan_rw')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pengajuan_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('jenis_surat_id');
            $table->foreignId('rt_id')->nullable();
            $table->string('target_level')->default('rt');
            $table->boolean('kebutuhan_rw')->default(false);
            $table->string('nomor_pengajuan')->unique();
            $table->string('nomor_surat')->nullable();
            $table->text('keperluan');
            $table->json('data_form_json')->nullable();
            $table->json('berkas_pendukung')->nullable();
            $table->string('status')->default('menunggu_rw');
            $table->foreignId('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->text('catatan_rt')->nullable();
            $table->text('catatan_rw')->nullable();
            $table->foreignId('rt_approved_by')->nullable();
            $table->dateTime('rt_approved_at')->nullable();
            $table->foreignId('rw_approved_by')->nullable();
            $table->dateTime('rw_approved_at')->nullable();
            $table->string('file_pdf')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('rt_id')->nullable();
            $table->foreignId('rw_id')->nullable();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('nominal', 15, 2)->nullable();
            $table->string('bukti')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

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

    /**
     * Test 1: Sinkronisasi Gender Kependudukan
     * Rumus: Total Jiwa = Laki-laki + Perempuan + Belum Diketahui
     */
    public function test_statistik_kependudukan_rumus_gender_konsisten(): void
    {
        $rw = Rw::create(['kode_rw' => '05', 'nama_rw' => 'RW 05']);
        $rt = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01', 'nama_rt' => 'RT 01']);

        // Warga 1: Laki-laki (dari profile)
        $user1 = User::create(['name' => 'Warga L', 'email' => 'l@test.local', 'password' => 'secret']);
        UserProfile::create(['user_id' => $user1->id, 'rt_id' => $rt->id, 'nama_lengkap' => 'Warga L', 'jenis_kelamin' => 'L', 'status' => 'aktif']);

        // Warga 2: Perempuan (dari users fallback)
        $user2 = User::create(['name' => 'Warga P', 'email' => 'p@test.local', 'password' => 'secret', 'jenis_kelamin' => 'P']);
        UserProfile::create(['user_id' => $user2->id, 'rt_id' => $rt->id, 'nama_lengkap' => 'Warga P', 'jenis_kelamin' => null, 'status' => 'aktif']);

        // Warga 3: Belum Diketahui (keduanya null)
        $user3 = User::create(['name' => 'Warga ?', 'email' => 'u@test.local', 'password' => 'secret', 'jenis_kelamin' => null]);
        UserProfile::create(['user_id' => $user3->id, 'rt_id' => $rt->id, 'nama_lengkap' => 'Warga ?', 'jenis_kelamin' => null, 'status' => 'aktif']);

        // Warga 4: Status pindah (tidak boleh dihitung)
        $user4 = User::create(['name' => 'Warga Pindah', 'email' => 'pindah@test.local', 'password' => 'secret', 'jenis_kelamin' => 'L']);
        UserProfile::create(['user_id' => $user4->id, 'rt_id' => $rt->id, 'nama_lengkap' => 'Warga Pindah', 'jenis_kelamin' => 'L', 'status' => 'pindah']);

        $stats = Rt::getStatistikKependudukanWilayah($rt->id);

        $this->assertEquals(3, $stats['total_jiwa'], 'Total jiwa harus hanya menghitung warga aktif (3 jiwa)');
        $this->assertEquals(1, $stats['total_laki'], 'Laki-laki aktif harus 1');
        $this->assertEquals(1, $stats['total_perempuan'], 'Perempuan aktif harus 1');
        $this->assertEquals(1, $stats['total_belum_diketahui'], 'Belum diketahui harus 1');

        // Pastikan rumus wajib: Total Jiwa = L + P + Belum Diketahui
        $this->assertEquals(
            $stats['total_jiwa'],
            $stats['total_laki'] + $stats['total_perempuan'] + $stats['total_belum_diketahui'],
            'Rumus Total Jiwa = L + P + Belum Diketahui harus konsisten'
        );
    }

    /**
     * Test 2: Filter kegiatan berdasarkan bulan dan tahun
     */
    public function test_kegiatan_filter_bulan_dan_tahun(): void
    {
        $roleRw = Role::create(['nama_role' => 'rw']);
        $adminRw = User::create(['name' => 'Ketua RW', 'email' => 'rw_filter@test.local', 'password' => 'secret', 'role_id' => $roleRw->id]);

        // Kegiatan November 2026
        $kegNov = Kegiatan::create([
            'judul' => 'Rapat Evaluasi November',
            'cakupan' => 'rw',
            'status' => 'dibuka',
            'tanggal_mulai' => Carbon::create(2026, 11, 15, 10, 0),
            'tanggal_selesai' => Carbon::create(2026, 11, 15, 12, 0),
            'created_by' => $adminRw->id,
        ]);

        // Kegiatan Desember 2026
        $kegDes = Kegiatan::create([
            'judul' => 'Bakti Sosial Desember',
            'cakupan' => 'rw',
            'status' => 'dibuka',
            'tanggal_mulai' => Carbon::create(2026, 12, 20, 9, 0),
            'tanggal_selesai' => Carbon::create(2026, 12, 20, 12, 0),
            'created_by' => $adminRw->id,
        ]);

        $this->actingAs($adminRw);

        // Akses filter bulan 11 tahun 2026
        $responseNov = $this->get(route('kegiatan.index', ['bulan' => 11, 'tahun' => 2026]));
        $responseNov->assertStatus(200);
        $responseNov->assertViewHas('kegiatanBulanIni', function ($collection) use ($kegNov, $kegDes) {
            return $collection->contains($kegNov) && !$collection->contains($kegDes);
        });
        $responseNov->assertViewHas('kegiatanBulanMendatang', function ($collection) use ($kegDes) {
            return $collection->contains($kegDes);
        });

        // Akses filter bulan 12 tahun 2026
        $responseDes = $this->get(route('kegiatan.index', ['bulan' => 12, 'tahun' => 2026]));
        $responseDes->assertStatus(200);
        $responseDes->assertViewHas('kegiatanBulanIni', function ($collection) use ($kegNov, $kegDes) {
            return $collection->contains($kegDes) && !$collection->contains($kegNov);
        });
        $responseDes->assertViewHas('kegiatanBulanSebelumnya', function ($collection) use ($kegNov) {
            return $collection->contains($kegNov);
        });
    }

    /**
     * Test 3: Soft delete pada kegiatan berstatus dibatalkan
     */
    public function test_kegiatan_soft_delete_untuk_kegiatan_dibatalkan(): void
    {
        $roleRw = Role::create(['nama_role' => 'rw']);
        $adminRw = User::create(['name' => 'Ketua RW', 'email' => 'rw@test.local', 'password' => 'secret', 'role_id' => $roleRw->id]);

        $kegiatan = Kegiatan::create([
            'judul' => 'Senam Pagi Bersama',
            'deskripsi' => 'Dibatalkan karena hujan deras',
            'status' => 'dibatalkan',
            'cakupan' => 'rw',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addHours(2),
            'created_by' => $adminRw->id,
        ]);

        $this->actingAs($adminRw);

        // Lakukan soft delete
        $response = $this->delete(route('kegiatan.soft-delete', $kegiatan));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan kegiatan sudah ter-soft delete
        $this->assertSoftDeleted('kegiatans', ['id' => $kegiatan->id]);

        $reloaded = Kegiatan::withTrashed()->find($kegiatan->id);
        $this->assertEquals($adminRw->id, $reloaded->deleted_by);

        // Pastikan tidak muncul pada query biasa
        $activeKegiatans = Kegiatan::where('id', $kegiatan->id)->get();
        $this->assertCount(0, $activeKegiatans);

        // Pastikan audit trail tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'soft_delete_kegiatan',
            'entity_type' => 'kegiatan',
            'entity_id' => $kegiatan->id,
        ]);
    }

    /**
     * Test 3: Logika Keputusan RW dan Idempotensi
     */
    public function test_persetujuan_rw_status_dan_penolakan_eksekusi_ulang(): void
    {
        $roleRw = Role::create(['nama_role' => 'rw']);
        $adminRw = User::create(['name' => 'Ketua RW', 'email' => 'rw@test.local', 'password' => 'secret', 'role_id' => $roleRw->id]);

        $rw = Rw::create(['kode_rw' => '05', 'nama_rw' => 'RW 05']);
        $ketuaRt = User::create(['name' => 'Ketua RT 01', 'email' => 'rt01@test.local', 'password' => 'secret']);
        $rt = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01', 'ketua_user_id' => $ketuaRt->id]);

        $wargaUser = User::create(['name' => 'Budi Pemohon', 'email' => 'budi@test.local', 'password' => 'secret']);
        $jenisSurat = JenisSurat::create(['kode' => 'SKU', 'nama' => 'Surat Keterangan Usaha', 'perlu_persetujuan_rw' => true]);

        $pengajuan = PengajuanSurat::create([
            'user_id' => $wargaUser->id,
            'jenis_surat_id' => $jenisSurat->id,
            'rt_id' => $rt->id,
            'nomor_pengajuan' => 'REQ-2026-0001',
            'keperluan' => 'Pengajuan Izin Usaha Kuliner',
            'status' => 'menunggu_rw',
            'kebutuhan_rw' => true,
        ]);

        $this->actingAs($adminRw);

        // Putuskan pengajuan pertama kali (Setujui)
        $response = $this->post(route('rw.pengawasan.approve', $pengajuan), [
            'status' => 'disetujui',
            'catatan_rw' => 'Disetujui untuk perizinan usaha UMKM',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->status);
        $this->assertNotNull($pengajuan->nomor_surat);
        $this->assertEquals($adminRw->id, $pengajuan->rw_approved_by);

        // Verifikasi notifikasi terkirim ke pemohon dan Ketua RT
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $wargaUser->id,
            'tipe' => 'surat',
            'judul' => 'Keputusan Persetujuan RW',
        ]);

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ketuaRt->id,
            'tipe' => 'surat',
            'judul' => 'Hasil Eskalasi RW',
        ]);

        // Verifikasi percobaan eksekusi ulang DITOLAK
        $responseRepeat = $this->post(route('rw.pengawasan.approve', $pengajuan), [
            'status' => 'ditolak',
            'catatan_rw' => 'Mencoba menolak setelah disetujui',
        ]);

        $responseRepeat->assertSessionHas('error', 'Pengajuan ini sudah diputuskan sebelumnya.');
    }

    /**
     * Test 5: Tombol Putuskan HANYA muncul pada status menunggu RW, dan tombol Lihat Detail muncul pada status disetujui
     */
    public function test_persetujuan_rw_tampilan_tombol_putuskan_dan_lihat_detail(): void
    {
        $roleRw = Role::create(['nama_role' => 'rw']);
        $adminRw = User::create(['name' => 'Ketua RW', 'email' => 'rw_view@test.local', 'password' => 'secret', 'role_id' => $roleRw->id]);

        $rw = Rw::create(['kode_rw' => '05', 'nama_rw' => 'RW 05']);
        $rt = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01']);

        $wargaUser = User::create(['name' => 'Pemohon Test', 'email' => 'pemohon@test.local', 'password' => 'secret']);
        $jenisSurat = JenisSurat::create(['kode' => 'SKD', 'nama' => 'Surat Keterangan Domisili', 'perlu_persetujuan_rw' => true]);

        // Pengajuan 1: Menunggu RW
        $pPending = PengajuanSurat::create([
            'user_id' => $wargaUser->id,
            'jenis_surat_id' => $jenisSurat->id,
            'rt_id' => $rt->id,
            'nomor_pengajuan' => 'REQ-PENDING-01',
            'keperluan' => 'Pindah domisili',
            'status' => 'menunggu_rw',
            'kebutuhan_rw' => true,
        ]);

        // Pengajuan 2: Sudah Disetujui
        $pApproved = PengajuanSurat::create([
            'user_id' => $wargaUser->id,
            'jenis_surat_id' => $jenisSurat->id,
            'rt_id' => $rt->id,
            'nomor_pengajuan' => 'REQ-APPROVED-02',
            'keperluan' => 'Izin usaha',
            'status' => 'disetujui',
            'kebutuhan_rw' => true,
            'nomor_surat' => 'RW/SUR/2026/11/0002',
        ]);

        $this->actingAs($adminRw);

        $response = $this->get(route('rw.pengawasan.persetujuan'));
        $response->assertStatus(200);

        // Pengajuan pending harus memiliki tombol Putuskan
        $response->assertSee('Putuskan');
        $response->assertSee('modal-dialog-scrollable');

        // Pengajuan approved harus memiliki tombol Lihat Detail
        $response->assertSee('Lihat Detail');
    }
}
