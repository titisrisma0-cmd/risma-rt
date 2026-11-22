<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalWargaBottomNavAndQuickGridTest extends TestCase
{
    use DatabaseTransactions;

    protected User $wargaUser;
    protected User $rwUser;
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
                $table->string('kode_iuran')->unique();
                $table->string('nama_iuran');
                $table->enum('jenis_iuran', ['wajib', 'sukarela'])->default('wajib');
                $table->decimal('nominal', 12, 2)->default(0);
                $table->enum('periode', ['bulanan', 'tahunan', 'sekali'])->default('bulanan');
                $table->text('deskripsi')->nullable();
                $table->string('status')->default('aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pembayarans')) {
            Schema::create('pembayarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('iuran_id')->nullable();
                $table->string('no_transaksi')->unique();
                $table->decimal('jumlah_bayar', 12, 2);
                $table->dateTime('tanggal_bayar');
                $table->string('metode_bayar')->default('transfer');
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengajuan_surats')) {
            Schema::create('pengajuan_surats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('jenis_surat_id')->nullable();
                $table->string('nomor_pengajuan')->nullable();
                $table->string('nomor_surat')->nullable();
                $table->text('keperluan')->nullable();
                $table->string('status')->default('menunggu');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->foreignId('rw_id')->nullable();
                $table->string('judul')->nullable();
                $table->string('nama_kegiatan')->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('cakupan')->default('rt');
                $table->date('tanggal_mulai')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rt_audits')) {
            Schema::create('rt_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id');
                $table->integer('tahun')->default(2026);
                $table->integer('bulan')->default(11);
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

        if (!Schema::hasTable('jenis_surats')) {
            Schema::create('jenis_surats', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('kode')->nullable();
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

        if (!Schema::hasTable('pengeluarans')) {
            Schema::create('pengeluarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rt_id')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
                $table->date('tanggal')->nullable();
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

        $roleWarga = Role::firstOrCreate(['nama_role' => 'warga']);
        $roleRw = Role::firstOrCreate(['nama_role' => 'admin']);

        $this->rw01 = Rw::firstOrCreate(
            ['kode_rw' => '01'],
            ['nama_rw' => 'RW 01']
        );

        $this->rt01 = Rt::firstOrCreate(
            ['kode_rt' => '01'],
            ['rw_id' => $this->rw01->id, 'nama_rt' => 'RT 01']
        );

        $this->wargaUser = User::create([
            'name' => 'Warga Mobile Test',
            'email' => 'warga_mobile_' . uniqid() . '@smartrw.id',
            'password' => bcrypt('password123'),
            'role_id' => $roleWarga->id,
            'rt_id' => $this->rt01->id,
        ]);
        UserProfile::create([
            'user_id' => $this->wargaUser->id,
            'role_id' => $roleWarga->id,
            'rt_id' => $this->rt01->id,
            'nama_lengkap' => $this->wargaUser->name,
            'status' => 'aktif',
        ]);

        $this->rwUser = User::create([
            'name' => 'RW Mobile Test',
            'email' => 'rw_mobile_' . uniqid() . '@smartrw.id',
            'password' => bcrypt('password123'),
            'role_id' => $roleRw->id,
        ]);
        UserProfile::create([
            'user_id' => $this->rwUser->id,
            'role_id' => $roleRw->id,
            'nama_lengkap' => $this->rwUser->name,
            'status' => 'aktif',
        ]);
    }

    /**
     * Memastikan Bottom Navigation Bar Khusus Mobile (5 Menu) tampil pada portal warga.
     */
    public function test_portal_warga_renders_bottom_navigation_bar_with_five_items(): void
    {
        $this->actingAs($this->wargaUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // Validasi Container Bottom Nav Bar dan styling utility
        $response->assertSee('warga-bottom-nav', false);
        $response->assertSee('md:hidden fixed bottom-0 inset-x-0 z-40', false);
        $response->assertSee('bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_16px_rgba(0,0,0,0.04)] pb-safe', false);

        // Validasi 5 Menu Utama
        // 1. Beranda
        $response->assertSee(route('dashboard'), false);
        $response->assertSee('Beranda', false);

        // 2. Informasi
        $response->assertSee('#bottomSheetInformasi', false);
        $response->assertSee('Informasi', false);

        // 3. Iuran (Tombol Tengah Menonjol ala E-Wallet)
        $response->assertSee('bottom-nav-fab', false);
        $response->assertSee('-mt-4 shadow-md', false);
        $response->assertSee(route('pembayaran.index'), false);
        $response->assertSee('Iuran', false);

        // 4. Riwayat
        $response->assertSee(route('layanan.surat.index'), false);
        $response->assertSee('Riwayat', false);

        // 5. Akun
        $response->assertSee('#bottomSheetAkun', false);
        $response->assertSee('Akun', false);

        // Validasi Bottom Sheet Informasi (Pengumuman RT, RW, Agenda Kegiatan)
        $response->assertSee('id="bottomSheetInformasi"', false);
        $response->assertSee(route('warga.pengumuman', ['scope' => 'rt']), false);
        $response->assertSee(route('warga.pengumuman', ['scope' => 'rw']), false);
        $response->assertSee(route('kegiatan.index'), false);

        // Validasi Bottom Sheet Akun (Data Keluarga, Profil, Logout form @csrf)
        $response->assertSee('id="bottomSheetAkun"', false);
        $response->assertSee(route('kartu-keluarga.saya'), false);
        $response->assertSee(route('profile.index'), false);
        $response->assertSee(route('logout'), false);

        // Validasi main content padding di mobile
        $response->assertSee('has-warga-bottom-nav pb-24 md:pb-8', false);

        // Validasi sidebar bawaan disembunyikan di HP untuk warga
        $response->assertSee('sidebar hidden md:flex d-none d-md-flex', false);

        // Validasi Header Atas Khusus Mobile: Logo Rumah Smart RW ala WhatsApp Light
        $response->assertSee('mobile-header-brand md:hidden d-flex d-md-none w-9 h-9 rounded-xl bg-[#DCF8C6] border border-[#BBF7D0] text-[#00A884]', false);
        $response->assertSee('navbar-page-title topbar-title hidden md:block text-truncate', false);

        // Validasi Widget Chat / Assistant di Mobile diberi jarak aman
        $response->assertSee('whatsapp-help-container bottom-20 right-3 md:bottom-6 md:right-6', false);
    }

    /**
     * Memastikan Grid Layanan Mandiri Warga di Dashboard Tertata Rapi 4 Kolom ala E-Wallet
     * dan Ringkasan 4 Kartu Statistik 2x2 di Layar HP.
     */
    public function test_dashboard_warga_quick_service_grid_and_stat_cards(): void
    {
        $this->actingAs($this->wargaUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // 1. Grid 2x2 pada 4 Kartu Statistik Atas (Hemat Tempat di Layar HP)
        $response->assertSee('warga-stat-grid grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full mb-4', false);
        $response->assertSee('min-w-0', false);
        $response->assertSee('p-3 sm:p-5', false);
        $response->assertSee('text-[10px] sm:text-xs font-semibold tracking-wide truncate', false);
        $response->assertSee('text-xs sm:text-lg font-bold tracking-tight break-all', false);
        $response->assertSee('text-sm sm:text-xl font-bold tracking-tight truncate', false);

        $response->assertSee('Kartu Keluarga', false);
        $response->assertSee('Iuran Menunggu', false);
        $response->assertSee('Total Iuran Telah Lunas', false);
        $response->assertSee('Permohonan Surat Saya', false);

        // 2. Grid 4 Kolom Layanan Mandiri Warga ala Menu Utama E-Wallet
        $response->assertSee('warga-services-grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 sm:gap-4', false);
        $response->assertSee('Layanan Mandiri Warga', false);

        // Validasi ke-7 item layanan, ikon rounded-2xl, dan label teks
        $response->assertSee('rounded-2xl', false);
        $response->assertSee('text-[11px] sm:text-sm font-semibold text-center leading-tight', false);

        // 7 Layanan dan link-nya
        $response->assertSee(route('layanan.surat.index'), false);
        $response->assertSee('Surat Pengantar', false);

        $response->assertSee(route('pembayaran.index'), false);
        $response->assertSee('Iuran Warga', false);

        $response->assertSee(route('layanan.ronda.index'), false);
        $response->assertSee('Ronda Malam', false);

        $response->assertSee(route('bank-sampah.index'), false);
        $response->assertSee('Bank Sampah', false);

        $response->assertSee(route('layanan.umkm.index'), false);
        $response->assertSee('UMKM Warga', false);

        $response->assertSee(route('layanan.posyandu.index'), false);
        $response->assertSee('Posyandu', false);

        $response->assertSee(route('aspirasi.index'), false);
        $response->assertSee('Aspirasi', false);
    }

    /**
     * Memastikan peran non-warga (misal RW) tetap mempertahankan sidebar normal.
     */
    public function test_pengurus_rw_maintains_normal_desktop_sidebar(): void
    {
        $this->actingAs($this->rwUser);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // Non-warga tidak memiliki bottom nav bar portal warga
        $response->assertDontSee('warga-bottom-nav', false);
        $response->assertDontSee('id="bottomSheetInformasi"', false);
        $response->assertDontSee('id="bottomSheetAkun"', false);

        // Sidebar tetap ada dan normal
        $response->assertSee('Dashboard RW', false);
    }
}
