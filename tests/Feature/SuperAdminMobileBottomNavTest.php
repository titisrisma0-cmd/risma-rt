<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KartuKeluarga;
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

class SuperAdminMobileBottomNavTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $wargaUser;
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

        if (!Schema::hasTable('aspirasis')) {
            Schema::create('aspirasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->string('judul')->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('status')->default('menunggu');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('umkms')) {
            Schema::create('umkms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('rt_id')->nullable();
                $table->string('nama_usaha')->nullable();
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
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengeluarans')) {
            Schema::create('pengeluarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('iurans')) {
            Schema::create('iurans', function (Blueprint $table) {
                $table->id();
                $table->string('nama_iuran')->default('Iuran');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->decimal('jumlah_bayar', 12, 2)->default(0);
                $table->string('status')->default('pending');
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

        if (!Schema::hasTable('jadwal_rondas')) {
            Schema::create('jadwal_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->date('tanggal');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petugas_rondas')) {
            Schema::create('petugas_rondas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jadwal_ronda_id');
                $table->foreignId('user_id')->nullable();
                $table->foreignId('user_profile_id')->nullable();
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
                $table->text('deskripsi')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        $roleSuperAdmin = Role::firstOrCreate(['nama_role' => 'super_admin']);
        $roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);

        $this->rw01 = Rw::firstOrCreate(
            ['kode_rw' => '01'],
            ['nama_rw' => 'RW 01']
        );

        $this->rt01 = Rt::firstOrCreate(
            ['kode_rt' => '01'],
            ['rw_id' => $this->rw01->id, 'nama_rt' => 'RT 01']
        );

        $this->superAdmin = User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin_test_' . uniqid() . '@smartrw.id',
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

        $this->wargaUser = User::create([
            'name' => 'Warga Test',
            'email' => 'warga_test_' . uniqid() . '@smartrw.id',
            'password' => Hash::make('password123'),
            'role_id' => $roleWarga->id,
            'rt_id' => $this->rt01->id,
            'is_super_admin' => false,
        ]);
        UserProfile::create([
            'user_id' => $this->wargaUser->id,
            'role_id' => $roleWarga->id,
            'rt_id' => $this->rt01->id,
            'nama_lengkap' => $this->wargaUser->name,
            'status' => 'aktif',
        ]);
    }

    /**
     * Memastikan Super Admin mobile layout merender Header logo rumah mengarah ke superadmin dashboard
     * dan menyembunyikan sidebar bawaan di HP.
     */
    public function test_superadmin_mobile_header_brand_and_sidebar_toggle(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('superadmin.dashboard'));
        $response->assertStatus(200);

        // Header logo rumah khusus mobile mengarah ke dashboard super admin
        $response->assertSee('href="' . route('superadmin.dashboard') . '"', false);
        $response->assertSee('mobile-header-brand md:hidden d-flex d-md-none w-9 h-9 rounded-xl bg-[#DCF8C6] border border-[#BBF7D0] text-[#00A884]', false);

        // Judul halaman desktop tidak tampil di mobile untuk mencegah kepotong
        $response->assertSee('navbar-page-title topbar-title hidden md:block text-truncate', false);

        // Sidebar bawaan disembunyikan di HP untuk Super Admin
        $response->assertSee('sidebar hidden md:flex d-none d-md-flex', false);
        $response->assertSee('toggle-sidebar-btn flex-shrink-0 hidden md:flex d-none d-md-flex', false);

        // Main content padding container untuk bottom nav bar
        $response->assertSee('has-superadmin-bottom-nav pb-24 md:pb-8', false);
    }

    /**
     * Memastikan Bottom Navigation Bar Khusus Mobile Super Admin (5 Item Navigasi)
     * dan 4 Bottom Sheet Modal Drawers dirender dengan sempurna.
     */
    public function test_superadmin_mobile_bottom_nav_bar_and_drawers(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('superadmin.dashboard'));
        $response->assertStatus(200);

        // 1. Container Bottom Nav Bar
        $response->assertSee('superadmin-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_16px_rgba(0,0,0,0.04)] pb-safe', false);

        // 2. Tab 1: Beranda
        $response->assertSee(route('superadmin.dashboard'), false);
        $response->assertSee('Beranda', false);

        // 3. Tab 2: Wilayah
        $response->assertSee('#bottomSheetSuperAdminWilayah', false);
        $response->assertSee('Wilayah', false);

        // 4. Tab 3: Sistem (Tombol Tengah Menonjol ala E-Wallet)
        $response->assertSee('bottom-nav-fab -mt-4 shadow-md ring-4 ring-white', false);
        $response->assertSee('#bottomSheetSuperAdminSistem', false);
        $response->assertSee('Sistem', false);

        // 5. Tab 4: Monitor
        $response->assertSee('#bottomSheetSuperAdminMonitor', false);
        $response->assertSee('Monitor', false);

        // 6. Tab 5: Akun
        $response->assertSee('#bottomSheetSuperAdminAkun', false);
        $response->assertSee('Akun', false);

        // 7. Drawer Wilayah
        $response->assertSee('id="bottomSheetSuperAdminWilayah"', false);
        $response->assertSee(route('rts.index'), false);
        $response->assertSee(route('warga.index'), false);
        $response->assertSee(route('warga.verifikasi.index'), false);
        $response->assertSee(route('rw.monitoring.warga'), false);
        $response->assertSee(route('rw.audit.index'), false);
        $response->assertSee(route('rw.metode-pembayaran.index'), false);

        // 8. Drawer Sistem
        $response->assertSee('id="bottomSheetSuperAdminSistem"', false);
        $response->assertSee('#modalSwitchRole', false);
        $response->assertSee(route('superadmin.rfid-devices.index'), false);
        $response->assertSee(route('rfid.history'), false);
        $response->assertSee(route('superadmin.api-settings.index'), false);
        $response->assertSee(route('superadmin.audit-logs'), false);
        $response->assertSee(route('assistant.faqs.index'), false);

        // 9. Drawer Monitor
        $response->assertSee('id="bottomSheetSuperAdminMonitor"', false);
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('kegiatan.index', ['cakupan' => 'rw']), false);
        $response->assertSee(route('rw.monitoring.kegiatan'), false);
        $response->assertSee(route('rw.monitoring.layanan'), false);
        $response->assertSee(route('pengumuman.index'), false);
        $response->assertSee(route('rw.pengawasan.persetujuan'), false);
        $response->assertSee(route('rw.audit.riwayat'), false);
        $response->assertSee(route('laporan.index'), false);

        // 10. Drawer Akun
        $response->assertSee('id="bottomSheetSuperAdminAkun"', false);
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
     * Memastikan Dashboard Super Admin memiliki Section Akses Cepat 8 Grid
     * dan 8 Kartu Statistik Sistem Ditata Rapi 2x2 di Layar HP.
     */
    public function test_superadmin_dashboard_quick_grid_and_stat_cards(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('superadmin.dashboard'));
        $response->assertStatus(200);

        // 1. Section Akses Cepat Super Admin & RW (8 Pintasan)
        $response->assertSee('Akses Cepat Super Admin & RW', false);
        $response->assertSee('warga-services-grid grid-cols-4 lg:grid-cols-8 gap-2.5 sm:gap-4', false);
        $response->assertSee('Ganti Role', false);
        $response->assertSee('Perangkat RFID', false);
        $response->assertSee('Scan RFID', false);
        $response->assertSee('Manajemen RT', false);
        $response->assertSee('Data Warga', false);
        $response->assertSee('Verifikasi', false);
        $response->assertSee('Keuangan RT', false);
        $response->assertSee('Persetujuan RW', false);

        // 2. 8 Kartu Statistik dalam format warga-stat-grid
        $response->assertSee('warga-stat-grid grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full mb-4', false);
        $response->assertSee('stat-value fw-bold mb-1 text-base sm:text-2xl tracking-tight whitespace-nowrap', false);
        $response->assertSee('Total RW', false);
        $response->assertSee('Total RT', false);
        $response->assertSee('Total Warga Aktif', false);
        $response->assertSee('Kartu Keluarga (KK)', false);
        $response->assertSee('Akun Terdaftar', false);
        $response->assertSee('Pengurus Wilayah', false);
        $response->assertSee('Aspirasi & Laporan', false);
        $response->assertSee('Kegiatan & Agenda', false);
    }

    /**
     * Memastikan mode simulasi warga pada Super Admin tidak crash dan tetap konsisten.
     */
    public function test_superadmin_in_warga_mode_renders_warga_bottom_nav(): void
    {
        $this->actingAs($this->wargaUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Menampilkan bottom nav warga bukan super admin
        $response->assertSee('warga-bottom-nav', false);
        $response->assertDontSee('superadmin-bottom-nav', false);
    }
}
