<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Iuran;
use App\Models\JadwalRonda;
use App\Models\KartuKeluarga;
use App\Models\Kas;
use App\Models\Kegiatan;
use App\Models\Notifikasi;
use App\Models\PanicReport;
use App\Models\Pembayaran;
use App\Models\PengajuanSurat;
use App\Models\Pengeluaran;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmergencyLoginAndDashboardTest extends TestCase
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

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('role_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->string('nama_lengkap')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kartu_keluargas')) {
            Schema::create('kartu_keluargas', function (Blueprint $table) {
                $table->id();
                $table->string('no_kk')->unique();
                $table->foreignId('rt_id');
                $table->foreignId('kepala_keluarga_id')->nullable();
                $table->string('alamat')->nullable();
                $table->string('status')->default('aktif');
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
                $table->string('no_transaksi')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('iurans')) {
            Schema::create('iurans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->string('nama_iuran');
                $table->string('tipe')->default('bulanan');
                $table->decimal('nominal', 12, 2)->default(0);
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->string('judul');
                $table->text('deskripsi')->nullable();
                $table->string('kategori')->default('lainnya');
                $table->string('status')->default('dibuka');
                $table->string('cakupan')->default('rt');
                $table->dateTime('tanggal_mulai')->nullable();
                $table->dateTime('tanggal_selesai')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('aspirasis')) {
            Schema::create('aspirasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('judul');
                $table->text('deskripsi')->nullable();
                $table->string('status')->default('menunggu');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluarans')) {
            Schema::create('pengeluarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->string('kategori')->default('operasional');
                $table->string('keterangan');
                $table->decimal('nominal', 12, 2)->default(0);
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengajuan_surats')) {
            Schema::create('pengajuan_surats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('jenis_surat_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('nomor_pengajuan')->nullable();
                $table->string('nomor_surat')->nullable();
                $table->text('keperluan')->nullable();
                $table->string('status')->default('menunggu_rt');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kas')) {
            Schema::create('kas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->enum('jenis', ['masuk', 'keluar'])->default('masuk');
                $table->decimal('nominal', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('panic_reports')) {
            Schema::create('panic_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('judul')->default('Panic');
                $table->text('deskripsi')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_rondas')) {
            Schema::create('jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal');
                $table->string('shift')->default('malam');
                $table->string('lokasi_pos')->nullable();
                $table->string('status')->default('aktif');
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petugas_rondas')) {
            Schema::create('petugas_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id');
                $table->string('peran')->default('anggota');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('absensi_rondas')) {
            Schema::create('absensi_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id');
                $table->string('status')->default('hadir');
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

        if (!Schema::hasTable('rt_audits')) {
            Schema::create('rt_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id');
                $table->integer('tahun');
                $table->integer('bulan');
                $table->string('status')->default('belum_diperiksa');
                $table->timestamps();
            });
        }
    }

    public function test_admin_rw_can_login_and_access_rw_dashboard_without_500(): void
    {
        $roleRw = Role::create(['nama_role' => 'admin']);
        $userRw = User::create([
            'name' => 'Admin RW Test',
            'email' => 'admin_rw_test@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $roleRw->id,
        ]);
        UserProfile::create(['user_id' => $userRw->id, 'role_id' => $roleRw->id, 'nama_lengkap' => $userRw->name, 'status' => 'aktif']);

        $rw = Rw::create(['kode_rw' => '01', 'nama_rw' => 'RW 01', 'ketua_user_id' => $userRw->id]);
        $rt01 = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01', 'nama_rt' => 'RT 01']);

        // POST login
        $loginResponse = $this->post(route('login'), [
            'email' => 'admin_rw_test@test.local',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        // GET /dashboard as Admin RW
        $this->actingAs($userRw);
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Pengurus RW');
        $dashboardResponse->assertSee('Pusat kendali dan monitoring terpadu');
    }

    public function test_ketua_rt_can_login_and_access_rt_dashboard_without_500(): void
    {
        $roleRt = Role::create(['nama_role' => 'rt_01']);
        $rw = Rw::create(['kode_rw' => '01', 'nama_rw' => 'RW 01']);
        $rt = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01', 'nama_rt' => 'RT 01']);

        $ketuaRt = User::create([
            'name' => 'Ketua RT 01 Test',
            'email' => 'ketua_rt01_test@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $roleRt->id,
            'rt_id' => $rt->id,
        ]);
        $rt->update(['ketua_user_id' => $ketuaRt->id]);
        UserProfile::create(['user_id' => $ketuaRt->id, 'role_id' => $roleRt->id, 'rt_id' => $rt->id, 'nama_lengkap' => $ketuaRt->name, 'status' => 'aktif']);

        // POST login
        $loginResponse = $this->post(route('login'), [
            'email' => 'ketua_rt01_test@test.local',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        // GET /dashboard as Ketua RT
        $this->actingAs($ketuaRt);
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Ketua RT 01');
        $dashboardResponse->assertSee('Selamat Datang, Ketua RT 01');
        $dashboardResponse->assertSee('Monitoring Keamanan & Siskamling RT 01', false);
        // Verify CCTV route does not crash
        $dashboardResponse->assertSee(route('layanan.ronda.cctv.live'));
    }

    public function test_warga_can_login_and_access_warga_dashboard_without_500(): void
    {
        $roleWarga = Role::create(['nama_role' => 'warga']);
        $rw = Rw::create(['kode_rw' => '01', 'nama_rw' => 'RW 01']);
        $rt = Rt::create(['rw_id' => $rw->id, 'kode_rt' => '01', 'nama_rt' => 'RT 01']);

        $warga = User::create([
            'name' => 'Warga Test',
            'email' => 'warga_test@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $roleWarga->id,
            'rt_id' => $rt->id,
        ]);
        UserProfile::create(['user_id' => $warga->id, 'role_id' => $roleWarga->id, 'rt_id' => $rt->id, 'nama_lengkap' => $warga->name, 'status' => 'aktif']);

        // POST login
        $loginResponse = $this->post(route('login'), [
            'email' => 'warga_test@test.local',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        // GET /dashboard as Warga
        $this->actingAs($warga);
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Portal Warga');
        $dashboardResponse->assertSee('Layanan Mandiri Warga');
    }

    public function test_user_can_logout_successfully(): void
    {
        $roleWarga = Role::create(['nama_role' => 'warga']);
        $warga = User::create([
            'name' => 'Warga Logout',
            'email' => 'warga_logout@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $roleWarga->id,
        ]);

        $this->actingAs($warga);

        $response = $this->post(route('logout'));
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
