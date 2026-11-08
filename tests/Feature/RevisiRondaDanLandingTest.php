<?php

namespace Tests\Feature;

use App\Models\JadwalRonda;
use App\Models\Notifikasi;
use App\Models\PanicReport;
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

class RevisiRondaDanLandingTest extends TestCase
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

        if (!Schema::hasTable('panic_reports')) {
            Schema::create('panic_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('judul')->default('Panic Button');
                $table->text('deskripsi')->nullable();
                $table->decimal('lokasi_lat', 10, 7)->nullable();
                $table->decimal('lokasi_lng', 10, 7)->nullable();
                $table->string('status')->default('aktif');
                $table->foreignId('handled_by')->nullable();
                $table->timestamp('handled_at')->nullable();
                $table->text('catatan_penanganan')->nullable();
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
    }

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('RW SMART');
        $response->assertSee('Bangun Lingkungan Lebih');
        $response->assertSee('Terhubung');
        $response->assertSee('Fitur Utama');
        $response->assertSee('Layanan Warga');
        $response->assertSee('Keamanan');
    }

    public function test_rt_can_update_panic_report_status_without_error(): void
    {
        $roleRt = Role::create(['nama_role' => 'rt']);
        $roleWarga = Role::create(['nama_role' => 'warga']);

        $rw = Rw::create(['kode_rw' => '01', 'nama_rw' => 'RW 01']);
        $rt = Rt::create(['kode_rt' => '01', 'rw_id' => $rw->id, 'nama_rt' => 'RT 01']);

        $ketuaRt = User::create([
            'name' => 'Ketua RT 01',
            'email' => 'ketuart01_test@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleRt->id,
            'rt_id' => $rt->id,
        ]);
        $rt->update(['ketua_user_id' => $ketuaRt->id]);
        UserProfile::create(['user_id' => $ketuaRt->id, 'role_id' => $roleRt->id, 'rt_id' => $rt->id, 'nama_lengkap' => $ketuaRt->name]);

        $warga = User::create([
            'name' => 'Warga Pelapor',
            'email' => 'pelapor_test@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleWarga->id,
            'rt_id' => $rt->id,
        ]);
        UserProfile::create(['user_id' => $warga->id, 'role_id' => $roleWarga->id, 'rt_id' => $rt->id, 'nama_lengkap' => $warga->name]);

        $panic = PanicReport::create([
            'user_id' => $warga->id,
            'rt_id' => $rt->id,
            'judul' => 'Darurat Gangguan',
            'deskripsi' => 'Ada orang mencurigakan di depan gang',
            'status' => 'aktif',
        ]);

        $this->actingAs($ketuaRt);

        // RT updates status to 'sedang_ditangani'
        $response = $this->patch(route('layanan.ronda.panic.status', $panic), [
            'status' => 'sedang_ditangani',
            'catatan_penanganan' => 'Petugas ronda sedang meluncur ke lokasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('panic_reports', [
            'id' => $panic->id,
            'status' => 'sedang_ditangani',
        ]);

        // Verify that citizen received notification
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $warga->id,
            'tipe' => 'darurat',
        ]);
    }

    public function test_rt_can_send_ronda_reminder_with_throttle(): void
    {
        $roleRt = Role::create(['nama_role' => 'rt']);
        $roleWarga = Role::create(['nama_role' => 'warga']);

        $rw = Rw::create(['kode_rw' => '01', 'nama_rw' => 'RW 01']);
        $rt = Rt::create(['kode_rt' => '01', 'rw_id' => $rw->id, 'nama_rt' => 'RT 01']);

        $ketuaRt = User::create([
            'name' => 'Ketua RT Reminder',
            'email' => 'ketua_rem@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleRt->id,
            'rt_id' => $rt->id,
        ]);
        $rt->update(['ketua_user_id' => $ketuaRt->id]);
        UserProfile::create(['user_id' => $ketuaRt->id, 'role_id' => $roleRt->id, 'rt_id' => $rt->id, 'nama_lengkap' => $ketuaRt->name]);

        $petugas1 = User::create([
            'name' => 'Petugas Satu',
            'email' => 'petugas1@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleWarga->id,
            'rt_id' => $rt->id,
        ]);
        UserProfile::create(['user_id' => $petugas1->id, 'role_id' => $roleWarga->id, 'rt_id' => $rt->id, 'nama_lengkap' => $petugas1->name]);

        $jadwal = JadwalRonda::create([
            'rt_id' => $rt->id,
            'tanggal' => Carbon::today()->toDateString(),
            'shift' => 'malam',
            'lokasi_pos' => 'Pos Ronda RT 01',
            'status' => 'aktif',
        ]);

        PetugasRonda::create([
            'jadwal_ronda_id' => $jadwal->id,
            'user_id' => $petugas1->id,
            'peran' => 'anggota',
        ]);

        $this->actingAs($ketuaRt);

        // 1st dispatch should succeed
        $response1 = $this->post(route('ronda.jadwal.ingatkan', $jadwal));
        $response1->assertSessionHas('success');

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $petugas1->id,
            'tipe' => 'ronda',
        ]);

        // Immediate 2nd dispatch should be throttled
        $response2 = $this->post(route('ronda.jadwal.ingatkan', $jadwal));
        $response2->assertSessionHas('warning');
    }
}
