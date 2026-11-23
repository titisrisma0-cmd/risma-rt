<?php

namespace Tests\Feature;

use App\Models\Aspirasi;
use App\Models\AuditLog;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RtMobileBottomNavTest extends TestCase
{
    use DatabaseTransactions;

    protected User $rtUser;
    protected User $superAdmin;
    protected Rw $rw01;
    protected Rt $rt01;

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
                $table->string('jenis_kelamin')->nullable();
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

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->string('nama_kegiatan');
                $table->text('deskripsi')->nullable();
                $table->string('cakupan')->default('rt');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('iuran_id')->nullable();
                $table->decimal('jumlah_bayar', 15, 2)->default(0);
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kas')) {
            Schema::create('kas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->string('jenis')->default('masuk');
                $table->decimal('nominal', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluarans')) {
            Schema::create('pengeluarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
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

        if (!Schema::hasTable('rt_audits')) {
            Schema::create('rt_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id');
                $table->integer('tahun')->default(2026);
                $table->integer('bulan')->default(11);
                $table->string('status')->default('belum_diperiksa');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_rondas')) {
            Schema::create('jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('panic_reports')) {
            Schema::create('panic_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        $rtRole = Role::firstOrCreate(['nama_role' => 'rt']);
        $roleSuperAdmin = Role::firstOrCreate(['nama_role' => 'super_admin']);

        $this->rw01 = Rw::firstOrCreate(
            ['kode_rw' => '01'],
            ['nama_rw' => 'RW 01 Merdeka']
        );

        $this->rt01 = Rt::firstOrCreate(
            ['kode_rt' => '01'],
            ['rw_id' => $this->rw01->id, 'nama_rt' => 'RT 01 Sentosa']
        );

        $this->rtUser = User::create([
            'name' => 'Pak RT 01',
            'email' => 'ketua_rt01_' . uniqid() . '@smartrw.id',
            'password' => Hash::make('password123'),
            'role_id' => $rtRole->id,
            'rt_id' => $this->rt01->id,
            'is_super_admin' => false,
        ]);

        UserProfile::create([
            'user_id' => $this->rtUser->id,
            'role_id' => $rtRole->id,
            'rt_id' => $this->rt01->id,
            'rw_id' => $this->rw01->id,
            'nama_lengkap' => 'Bapak Ketua RT 01',
            'status' => 'aktif',
        ]);

        $this->rt01->update(['ketua_user_id' => $this->rtUser->id]);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin_rt_' . uniqid() . '@smartrw.id',
            'password' => Hash::make('password123'),
            'role_id' => $roleSuperAdmin->id,
            'is_super_admin' => true,
        ]);

        UserProfile::create([
            'user_id' => $this->superAdmin->id,
            'role_id' => $roleSuperAdmin->id,
            'nama_lengkap' => $this->superAdmin->name,
            'status' => 'aktif',
        ]);
    }

    public function test_rt_mobile_header_brand_and_sidebar_toggle(): void
    {
        $response = $this->actingAs($this->rtUser)->get(route('dashboard'));

        $response->assertStatus(200);

        // Header Mobile Brand Icon ke dashboard RT
        $response->assertSee('class="mobile-header-brand md:hidden', false);
        $response->assertSee(route('dashboard'), false);
        $response->assertSee('#DCF8C6', false);
        $response->assertSee('#00A884', false);

        // Sidebar dan hamburger toggle disembunyikan di layar HP
        $response->assertSee('sidebar hidden md:flex d-none d-md-flex', false);
        $response->assertSee('toggle-sidebar-btn flex-shrink-0 hidden md:flex d-none d-md-flex', false);

        // Judul halaman desktop disembunyikan di HP
        $response->assertSee('hidden md:block text-truncate', false);
    }

    public function test_rt_mobile_bottom_nav_bar_and_drawers(): void
    {
        $response = $this->actingAs($this->rtUser)->get(route('dashboard'));

        $response->assertStatus(200);

        // 1. Bottom Navigation Bar Ketua RT Container
        $response->assertSee('class="rt-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40', false);
        $response->assertSee('aria-label="Navigasi Bawah Ketua RT"', false);

        // 2. Tab 1: Beranda
        $response->assertSee('Beranda', false);
        $response->assertSee(route('dashboard'), false);

        // 3. Tab 2: Warga (Drawer #bottomSheetRtWarga)
        $response->assertSee('data-bs-target="#bottomSheetRtWarga"', false);
        $response->assertSee('id="bottomSheetRtWarga"', false);
        $response->assertSee('Administrasi Warga', false);
        $response->assertSee(route('warga.index'), false);
        $response->assertSee(route('kartu-keluarga.index'), false);
        $response->assertSee(route('warga.verifikasi.index'), false);
        $response->assertSee(route('warga.mutasi'), false);

        // 4. Tab 3: Layanan (Raised FAB Center Button #bottomSheetRtLayanan)
        $response->assertSee('bottom-nav-fab -mt-4 shadow-md ring-4 ring-white', false);
        $response->assertSee('data-bs-target="#bottomSheetRtLayanan"', false);
        $response->assertSee('id="bottomSheetRtLayanan"', false);
        $response->assertSee('Pelayanan Masyarakat', false);
        $response->assertSee(route('rt.verifikasi.permohonan'), false);
        $response->assertSee(route('bank-sampah.index'), false);
        $response->assertSee(route('layanan.umkm.index'), false);
        $response->assertSee(route('layanan.posyandu.index'), false);
        $response->assertSee(route('layanan.ronda.index'), false);
        $response->assertSee(route('kegiatan.index'), false);
        $response->assertSee(route('aspirasi.index'), false);

        // 5. Tab 4: Keuangan (Drawer #bottomSheetRtKeuangan)
        $response->assertSee('data-bs-target="#bottomSheetRtKeuangan"', false);
        $response->assertSee('id="bottomSheetRtKeuangan"', false);
        $response->assertSee('Keuangan RT', false);
        $response->assertSee(route('pembayaran.index'), false);
        $response->assertSee(route('pengeluaran.index'), false);
        $response->assertSee(route('kas.index'), false);
        $response->assertSee(route('laporan.index'), false);
        $response->assertSee('Informasi', false);
        $response->assertSee(route('rt.informasi.pengumuman'), false);

        // 6. Tab 5: Akun (Drawer #bottomSheetRtAkun)
        $response->assertSee('data-bs-target="#bottomSheetRtAkun"', false);
        $response->assertSee('id="bottomSheetRtAkun"', false);
        $response->assertSee(route('profile.index'), false);
        $response->assertSee(route('logout'), false);

        // Verifikasi Drawer Design Pattern (max-h-[75vh], rounded-t-3xl, pb-24, z-50, drag handle)
        $response->assertSee('max-h-[75vh] overflow-y-auto rounded-t-3xl pb-24', false);
        $response->assertSee('bottom-sheet-modal z-50', false);
        $response->assertSee('bottom-sheet-drag-handle', false);

        // Verifikasi Avatar Drawer Akun 1:1 Aspect-Square dan tidak gepeng
        $response->assertSee('w-11 h-11 min-w-[44px] min-h-[44px] aspect-square rounded-full object-cover shrink-0', false);
        $response->assertSee('min-w-0 flex-1', false);
    }

    public function test_rt_dashboard_quick_grid_and_stat_cards(): void
    {
        $response = $this->actingAs($this->rtUser)->get(route('dashboard'));

        $response->assertStatus(200);

        // 1. Grid Akses Cepat Ketua RT di Bagian Atas
        $response->assertSee('Akses Cepat Ketua RT', false);
        $response->assertSee('warga-services-grid grid-cols-4 lg:grid-cols-8 gap-2.5 sm:gap-4', false);

        // 8 Pintasan Utama
        $response->assertSee('Permohonan Surat', false);
        $response->assertSee(route('rt.verifikasi.permohonan'), false);

        $response->assertSee('Pembayaran Iuran', false);
        $response->assertSee(route('pembayaran.index'), false);

        $response->assertSee('Verifikasi Warga', false);
        $response->assertSee(route('warga.verifikasi.index'), false);

        $response->assertSee('Data Warga', false);
        $response->assertSee(route('warga.index'), false);

        $response->assertSee('Kas RT', false);
        $response->assertSee(route('kas.index'), false);

        $response->assertSee('Bank Sampah', false);
        $response->assertSee(route('bank-sampah.index'), false);

        $response->assertSee('Ronda & RFID', false);
        $response->assertSee(route('layanan.ronda.index'), false);

        $response->assertSee('Posyandu', false);
        $response->assertSee(route('layanan.posyandu.index'), false);

        // 2. 8 Kartu Statistik Responsive Grid 2x4 Desktop / 2x2 Mobile
        $response->assertSee('warga-stat-grid grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full mb-4', false);
        $response->assertSee('Kepala Keluarga', false);
        $response->assertSee('Total Penduduk', false);
        $response->assertSee('Laki-laki', false);
        $response->assertSee('Perempuan', false);
        $response->assertSee('Iuran Terkumpul', false);
        $response->assertSee('Tunggakan / Pending', false);
        $response->assertSee('Saldo Kas RT', false);
        $response->assertSee('Pengumuman RW', false);
        $response->assertSee('whitespace-nowrap', false);

        // 3. Main container mobile bottom padding
        $response->assertSee('has-rt-bottom-nav pb-24 md:pb-8', false);
    }

    public function test_simulation_mode_rt_renders_rt_mobile_bottom_nav_with_back_button(): void
    {
        // Super Admin simulasi peran Ketua RT 01
        $response = $this->actingAs($this->superAdmin)
            ->withSession([
                'super_admin_context' => [
                    'mode' => 'rt',
                    'rt_id' => $this->rt01->id,
                    'label' => 'Ketua RT 01 Sentosa',
                ],
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Harus render bottom nav Ketua RT
        $response->assertSee('class="rt-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40', false);
        $response->assertSee('id="bottomSheetRtAkun"', false);

        // Harus terdapat tombol Kembali ke Super Admin
        $response->assertSee('Kembali ke Super Admin', false);
        $response->assertSee(route('superadmin.switch-role'), false);
        $response->assertSee('name="mode" value="super_admin"', false);
    }
}
