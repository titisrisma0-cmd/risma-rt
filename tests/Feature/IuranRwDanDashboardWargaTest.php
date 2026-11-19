<?php

namespace Tests\Feature;

use App\Models\Iuran;
use App\Models\Pembayaran;
use App\Models\Role;
use App\Models\Rt;
use App\Models\RtAudit;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IuranRwDanDashboardWargaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminRw;
    protected User $warga;
    protected Rt $rt01;
    protected Rw $rw01;

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
                $table->foreignId('rw_id')->nullable();
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
                $table->foreignId('rt_id')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('iurans')) {
            Schema::create('iurans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->string('kode_iuran')->nullable();
                $table->string('nama_iuran');
                $table->string('jenis_iuran')->default('wajib');
                $table->decimal('nominal', 15, 2)->default(0);
                $table->string('periode')->default('bulanan');
                $table->string('kategori')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('status')->default('aktif');
                $table->unsignedTinyInteger('due_day')->default(10);
                $table->decimal('denda_persen', 5, 2)->default(10.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('iuran_id')->nullable();
                $table->string('no_transaksi')->nullable();
                $table->decimal('jumlah_bayar', 15, 2)->default(0);
                $table->date('tanggal_bayar')->nullable();
                $table->string('bulan_tahun')->nullable();
                $table->string('metode_bayar')->default('transfer');
                $table->string('bukti_transfer')->nullable();
                $table->string('status')->default('lunas');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rt_audits')) {
            Schema::create('rt_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id');
                $table->integer('tahun');
                $table->integer('bulan');
                $table->decimal('total_pemasukan', 15, 2)->default(0);
                $table->decimal('total_pengeluaran', 15, 2)->default(0);
                $table->decimal('saldo_kas', 15, 2)->default(0);
                $table->string('status')->default('belum_diperiksa');
                $table->text('catatan_rw')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kas')) {
            Schema::create('kas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->string('jenis')->default('masuk');
                $table->decimal('nominal', 15, 2)->default(0);
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluarans')) {
            Schema::create('pengeluarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rw_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('judul')->nullable();
                $table->string('nama_kegiatan')->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('status')->default('dibuka');
                $table->string('cakupan')->default('rt');
                $table->dateTime('tanggal_mulai')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengajuan_surats')) {
            Schema::create('pengajuan_surats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('jenis_surat_id')->nullable();
                $table->string('nomor_surat')->nullable();
                $table->string('nomor_pengajuan')->nullable();
                $table->string('status')->default('diajukan');
                $table->text('keperluan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('judul')->nullable();
                $table->text('pesan')->nullable();
                $table->boolean('dibaca')->default(false);
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
                $table->string('jam_mulai')->nullable();
                $table->string('jam_selesai')->nullable();
                $table->string('status')->default('aktif');
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petugas_rondas')) {
            Schema::create('petugas_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
                $table->string('peran')->default('anggota');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('absensi_rondas')) {
            Schema::create('absensi_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
                $table->string('status')->default('hadir');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('action')->nullable();
                $table->string('entity_type')->nullable();
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        $roleRw = Role::firstOrCreate(['nama_role' => 'admin']);
        $roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);

        $this->rw01 = Rw::firstOrCreate(
            ['kode_rw' => '01'],
            ['nama_rw' => 'RW 01']
        );

        $this->rt01 = Rt::firstOrCreate(
            ['kode_rt' => '01'],
            ['rw_id' => $this->rw01->id, 'nama_rt' => 'RT 01']
        );

        $this->adminRw = User::create([
            'name' => 'Pengurus RW Test',
            'email' => 'pengurus_rw_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $roleRw->id,
        ]);
        UserProfile::create([
            'user_id' => $this->adminRw->id,
            'role_id' => $roleRw->id,
            'nama_lengkap' => $this->adminRw->name,
            'status' => 'aktif',
        ]);

        $this->warga = User::create([
            'name' => 'Warga Test',
            'email' => 'warga_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $roleWarga->id,
            'rt_id' => $this->rt01->id,
        ]);
        UserProfile::create([
            'user_id' => $this->warga->id,
            'role_id' => $roleWarga->id,
            'rt_id' => $this->rt01->id,
            'nama_lengkap' => $this->warga->name,
            'status' => 'aktif',
        ]);
    }

    public function test_rw_dapat_melihat_pengelolaan_master_iuran_di_audit_keuangan_tab_2(): void
    {
        $this->actingAs($this->adminRw);

        $response = $this->get(route('rw.audit.index'));

        $response->assertStatus(200);
        $response->assertSee('Master Iuran Warga RW 01');
        $response->assertSee('Tambah Iuran Baru');
        $response->assertSee('modalTambahIuranRw');
        $response->assertSee('Riwayat Pembayaran Iuran Lunas Warga');
    }

    public function test_rw_dapat_menambah_iuran_baru(): void
    {
        $this->actingAs($this->adminRw);

        $uniqueName = 'Iuran Siskamling Cipta ' . uniqid();
        $response = $this->post(route('iuran.store'), [
            'nama_iuran' => $uniqueName,
            'nominal' => 35000,
            'jenis_iuran' => 'wajib',
            'periode' => 'bulanan',
            'kategori' => 'Non-Ramadhan',
            'status' => 'aktif',
            'keterangan' => 'Pemeliharaan pos ronda & operasional petugas malam',
            'redirect_to' => route('rw.audit.index') . '#tab-iuran',
        ]);

        $response->assertRedirect(route('rw.audit.index') . '#tab-iuran');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('iurans', [
            'nama_iuran' => $uniqueName,
            'nominal' => 35000,
            'jenis_iuran' => 'wajib',
            'periode' => 'bulanan',
            'status' => 'aktif',
        ]);
    }

    public function test_warga_tidak_dapat_menambah_atau_menghapus_iuran(): void
    {
        $this->actingAs($this->warga);

        $storeResponse = $this->post(route('iuran.store'), [
            'nama_iuran' => 'Iuran Ilegal Warga',
            'nominal' => 10000,
            'jenis_iuran' => 'sukarela',
            'periode' => 'bulanan',
        ]);
        $storeResponse->assertStatus(403);

        $iuran = Iuran::first();
        if ($iuran) {
            $deleteResponse = $this->delete(route('iuran.destroy', $iuran->id));
            $deleteResponse->assertStatus(403);
        }
    }

    public function test_rw_dapat_mengubah_status_iuran(): void
    {
        $this->actingAs($this->adminRw);

        $iuran = Iuran::create([
            'kode_iuran' => 'TEST' . rand(100, 999),
            'nama_iuran' => 'Iuran Uji Coba Toggle ' . uniqid(),
            'jenis_iuran' => 'sukarela',
            'nominal' => 15000,
            'periode' => 'bulanan',
            'status' => 'aktif',
            'kategori' => 'Non-Ramadhan',
        ]);

        $response = $this->patch(route('iuran.toggle-status', $iuran->id));
        $response->assertSessionHas('success');

        $this->assertEquals('nonaktif', $iuran->fresh()->status);

        // Toggle kembali ke aktif
        $this->patch(route('iuran.toggle-status', $iuran->id));
        $this->assertEquals('aktif', $iuran->fresh()->status);
    }

    public function test_penghapusan_iuran_dengan_transaksi_hanya_menonaktifkan_demi_integritas_data(): void
    {
        $this->actingAs($this->adminRw);

        $iuran = Iuran::create([
            'kode_iuran' => 'SAFE' . rand(100, 999),
            'nama_iuran' => 'Iuran Berbayar ' . uniqid(),
            'jenis_iuran' => 'wajib',
            'nominal' => 20000,
            'periode' => 'bulanan',
            'status' => 'aktif',
            'kategori' => 'Non-Ramadhan',
        ]);

        // Buat relasi pembayaran
        Pembayaran::create([
            'user_id' => $this->warga->id,
            'iuran_id' => $iuran->id,
            'no_transaksi' => 'TRX-' . uniqid(),
            'jumlah_bayar' => 20000,
            'tanggal_bayar' => now(),
            'metode_bayar' => 'transfer',
            'status' => 'lunas',
        ]);

        $response = $this->delete(route('iuran.destroy', $iuran->id));
        $response->assertSessionHas('success');

        // Iuran TIDAK terhapus dari tabel iurans, melainkan dinonaktifkan
        $this->assertDatabaseHas('iurans', [
            'id' => $iuran->id,
            'status' => 'nonaktif',
        ]);
    }

    public function test_penghapusan_iuran_tanpa_transaksi_berhasil_dihapus(): void
    {
        $this->actingAs($this->adminRw);

        $iuran = Iuran::create([
            'kode_iuran' => 'DEL' . rand(100, 999),
            'nama_iuran' => 'Iuran Kosong ' . uniqid(),
            'jenis_iuran' => 'sukarela',
            'nominal' => 12000,
            'periode' => 'bulanan',
            'status' => 'aktif',
            'kategori' => 'Non-Ramadhan',
        ]);

        $response = $this->delete(route('iuran.destroy', $iuran->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('iurans', [
            'id' => $iuran->id,
        ]);
    }

    public function test_dashboard_warga_menampilkan_grafik_keuangan_iuran_rt(): void
    {
        $this->actingAs($this->warga);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Grafik Keuangan Iuran RT');
        $response->assertSee('chartIuranRt');
        $response->assertSee('Tren Penerimaan Iuran (6 Bulan Terakhir)');
        $response->assertSee('Total Iuran Terkumpul');
        $response->assertSee('Dalam Antrean Verifikasi');
    }

    public function test_halaman_pembayaran_menampilkan_badge_soft_pastel(): void
    {
        $this->actingAs($this->warga);

        $response = $this->get(route('pembayaran.index'));

        $response->assertStatus(200);
        $response->assertSee('Tagihan Iuran Warga');
        // Badge text & pill classes
        $response->assertSee('rounded-full');
        $response->assertSee('rounded-xl');
        $response->assertSee('badge-soft-');
    }

    public function test_halaman_pembayaran_kartu_tagihan_header_tidak_memotong_judul(): void
    {
        $this->actingAs($this->warga);

        Iuran::create([
            'kode_iuran' => 'TESTHDR',
            'nama_iuran' => 'Iuran Keamanan Masjid RW',
            'jenis_iuran' => 'wajib',
            'nominal' => 25000,
            'periode' => 'bulanan',
            'status' => 'aktif',
            'kategori' => 'Non-Ramadhan',
        ]);

        $response = $this->get(route('pembayaran.index'));

        $response->assertStatus(200);
        $response->assertSee('Iuran Keamanan Masjid RW');
        $response->assertSee('flex items-start justify-between gap-2');
        $response->assertSee('flex-1 min-w-0');
        $response->assertSee('break-words leading-snug');
        $response->assertSee('shrink-0 whitespace-nowrap');
        $response->assertSee('overflow: visible');
    }
}
