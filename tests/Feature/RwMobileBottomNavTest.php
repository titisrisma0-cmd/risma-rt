<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KartuKeluarga;
use App\Models\Kegiatan;
use App\Models\Role;
use App\Models\Rt;
use App\Models\RtAudit;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RwMobileBottomNavTest extends TestCase
{
    use DatabaseTransactions;

    protected User $rwUser;
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
                $table->foreignId('rw_id')->nullable();
                $table->string('judul')->nullable();
                $table->string('cakupan')->default('rw');
                $table->date('tanggal_mulai')->nullable();
                $table->softDeletes();
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

        if (!Schema::hasTable('pengajuan_surats')) {
            Schema::create('pengajuan_surats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('status')->default('menunggu');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifikasis')) {
            Schema::create('notifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->boolean('dibaca')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('aksi');
                $table->timestamps();
            });
        }

        $roleRw = Role::firstOrCreate(['nama_role' => 'admin']);
        $roleSuperAdmin = Role::firstOrCreate(['nama_role' => 'super_admin']);

        $this->rw01 = Rw::firstOrCreate(
            ['kode_rw' => '01'],
            ['nama_rw' => 'RW 01']
        );

        $this->rt01 = Rt::firstOrCreate(
            ['kode_rt' => '01'],
            ['rw_id' => $this->rw01->id, 'nama_rt' => 'RT 01']
        );

        $this->rwUser = User::create([
            'name' => 'Ketua RW Test',
            'email' => 'rw_test_' . uniqid() . '@smartrw.id',
            'password' => Hash::make('password123'),
            'role_id' => $roleRw->id,
            'is_super_admin' => false,
        ]);
        UserProfile::create([
            'user_id' => $this->rwUser->id,
            'role_id' => $roleRw->id,
            'nama_lengkap' => $this->rwUser->name,
            'status' => 'aktif',
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin Simulator',
            'email' => 'superadmin_sim_' . uniqid() . '@smartrw.id',
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

    /**
     * Memastikan Ketua RW mobile layout merender Header logo rumah mengarah ke Dashboard RW
     * dan menyembunyikan sidebar bawaan di HP.
     */
    public function test_rw_mobile_header_brand_and_sidebar_toggle(): void
    {
        $this->actingAs($this->rwUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Header logo rumah khusus mobile mengarah ke dashboard RW
        $response->assertSee('href="' . route('dashboard') . '"', false);
        $response->assertSee('mobile-header-brand md:hidden d-flex d-md-none w-9 h-9 rounded-xl bg-[#DCF8C6] border border-[#BBF7D0] text-[#00A884]', false);

        // Judul halaman desktop tidak tampil di mobile untuk mencegah kepotong
        $response->assertSee('navbar-page-title topbar-title hidden md:block text-truncate', false);

        // Sidebar bawaan disembunyikan di HP untuk Ketua RW
        $response->assertSee('sidebar hidden md:flex d-none d-md-flex', false);
        $response->assertSee('toggle-sidebar-btn flex-shrink-0 hidden md:flex d-none d-md-flex', false);

        // Main content padding container untuk bottom nav bar
        $response->assertSee('has-rw-bottom-nav pb-24 md:pb-8', false);
    }

    /**
     * Memastikan Bottom Navigation Bar Khusus Mobile Ketua RW (5 Item Navigasi)
     * dan 4 Bottom Sheet Modal Drawers dirender dengan sempurna.
     */
    public function test_rw_mobile_bottom_nav_bar_and_drawers(): void
    {
        $this->actingAs($this->rwUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // 1. Container Bottom Nav Bar Ketua RW
        $response->assertSee('rw-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_16px_rgba(0,0,0,0.04)] pb-safe', false);

        // 2. Tab 1: Beranda
        $response->assertSee(route('dashboard'), false);
        $response->assertSee('Beranda', false);

        // 3. Tab 2: Wilayah
        $response->assertSee('#bottomSheetRwWilayah', false);
        $response->assertSee('Wilayah', false);

        // 4. Tab 3: Pengawasan (Tombol Tengah Menonjol ala E-Wallet)
        $response->assertSee('bottom-nav-fab -mt-4 shadow-md ring-4 ring-white', false);
        $response->assertSee('#bottomSheetRwPengawasan', false);
        $response->assertSee('Pengawasan', false);

        // 5. Tab 4: Monitor
        $response->assertSee('#bottomSheetRwMonitor', false);
        $response->assertSee('Monitor', false);

        // 6. Tab 5: Akun
        $response->assertSee('#bottomSheetRwAkun', false);
        $response->assertSee('Akun', false);

        // 7. Drawer Wilayah
        $response->assertSee('id="bottomSheetRwWilayah"', false);
        $response->assertSee(route('rts.index'), false);
        $response->assertSee(route('warga.index'), false);
        $response->assertSee(route('warga.verifikasi.index'), false);
        $response->assertSee(route('rw.monitoring.warga'), false);

        // 8. Drawer Pengawasan
        $response->assertSee('id="bottomSheetRwPengawasan"', false);
        $response->assertSee(route('rw.pengawasan.persetujuan'), false);
        $response->assertSee(route('pengumuman.index'), false);
        $response->assertSee(route('rw.audit.riwayat'), false);
        $response->assertSee(route('rfid.history'), false);
        $response->assertSee(route('assistant.faqs.index'), false);

        // 9. Drawer Monitor
        $response->assertSee('id="bottomSheetRwMonitor"', false);
        $response->assertSee(route('rw.audit.index'), false);
        $response->assertSee(route('rw.metode-pembayaran.index'), false);
        $response->assertSee(route('kegiatan.index', ['cakupan' => 'rw']), false);
        $response->assertSee(route('rw.monitoring.kegiatan'), false);
        $response->assertSee(route('rw.monitoring.layanan'), false);
        $response->assertSee(route('laporan.index'), false);

        // 10. Drawer Akun
        $response->assertSee('id="bottomSheetRwAkun"', false);
        $response->assertSee(route('profile.index'), false);
        $response->assertSee(route('logout'), false);

        // 11. Modal styling: max-h-[75vh] overflow-y-auto rounded-t-3xl pb-24, z-50, dan drag handle
        $response->assertSee('max-h-[75vh] overflow-y-auto rounded-t-3xl pb-24', false);
        $response->assertSee('bottom-sheet-modal z-50', false);
        $response->assertSee('bottom-sheet-drag-handle', false);

        // Verifikasi Avatar Drawer Akun 1:1 Aspect-Square dan tidak gepeng
        $response->assertSee('w-11 h-11 min-w-[44px] min-h-[44px] aspect-square rounded-full object-cover shrink-0', false);
        $response->assertSee('min-w-0 flex-1', false);
    }

    /**
     * Memastikan Dashboard RW memiliki Section Akses Cepat 8 Grid
     * dan 4 Kartu Statistik Ditata Rapi 2x2 di Layar HP.
     */
    public function test_rw_dashboard_quick_grid_and_stat_cards(): void
    {
        $this->actingAs($this->rwUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // 1. Section Akses Cepat Ketua RW (8 Pintasan)
        $response->assertSee('Akses Cepat Ketua RW', false);
        $response->assertSee('warga-services-grid grid-cols-4 lg:grid-cols-8 gap-2.5 sm:gap-4', false);
        $response->assertSee('Persetujuan RW', false);
        $response->assertSee('Manajemen RT', false);
        $response->assertSee('Data Warga', false);
        $response->assertSee('Verifikasi', false);
        $response->assertSee('Keuangan RT', false);
        $response->assertSee('Pengumuman RW', false);
        $response->assertSee('Scan RFID', false);
        $response->assertSee('Laporan RW', false);

        // 2. 4 Kartu Statistik Utama Wilayah RW dalam format warga-stat-grid
        $response->assertSee('warga-stat-grid grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full mb-4', false);
        $response->assertSee('stat-value text-dark text-base sm:text-xl font-bold tracking-tight whitespace-nowrap', false);
        $response->assertSee('Wilayah RT Aktif', false);
        $response->assertSee('Total Kepala Keluarga', false);
        $response->assertSee('Total Warga Terdaftar', false);
        $response->assertSee('Saldo Kas Gabungan', false);
    }

    /**
     * Memastikan mode Simulasi Ketua RW oleh Super Admin juga merender navigasi mobile Ketua RW
     * dan menyediakan tombol kembali ke Super Admin.
     */
    public function test_simulation_mode_rw_renders_rw_mobile_bottom_nav_with_back_button(): void
    {
        $this->actingAs($this->superAdmin)
            ->withSession([
                'super_admin_context' => [
                    'mode' => 'rw',
                    'label' => 'Ketua RW 01',
                    'rw_id' => $this->rw01->id,
                ],
            ]);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Mode Simulasi RW merender bottom nav Ketua RW
        $response->assertSee('rw-bottom-nav', false);
        $response->assertSee('Simulasi: Ketua RW', false);

        // Tombol kembali ke super admin di drawer akun
        $response->assertSee(route('superadmin.switch-role'), false);
        $response->assertSee('Kembali ke Super Admin', false);
    }
}
