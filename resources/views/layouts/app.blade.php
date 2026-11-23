<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#147A45" id="metaThemeColor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart RW - Modern SaaS Dashboard')</title>
    
    <script>
        (function () {
            const savedTheme = localStorage.getItem('risma-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme ? savedTheme : (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme;
            const metaTheme = document.getElementById('metaThemeColor');
            if (metaTheme) metaTheme.setAttribute('content', theme === 'dark' ? '#12201a' : '#147A45');
        })();
    </script>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- SMART RW Modern SaaS Theme (Donezo Style) -->
    <link href="{{ asset('css/theme-dashboard.css') }}?v={{ file_exists(public_path('css/theme-dashboard.css')) ? filemtime(public_path('css/theme-dashboard.css')) : '1.0' }}" rel="stylesheet">
    <!-- SMART RW Motion Graphics Engine -->
    <link href="{{ asset('css/motion.css') }}?v={{ file_exists(public_path('css/motion.css')) ? filemtime(public_path('css/motion.css')) : '1.0' }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <!-- Floating Centered Toast Stack Container (Bagian 3) -->
    <div class="toast-stack" id="toastStack" role="region" aria-label="Notifikasi" data-no-motion="true"></div>

    <!-- Scroll Progress Bar -->
    <div class="m-progress" aria-hidden="true" data-no-motion="true"></div>

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" data-no-motion="true"></div>

    @php
        $authUser = Auth::user();
        $isSuperAdminMode = $authUser && $authUser->isSuperAdmin() && !$authUser->isSimulatingRole();
        $isWargaMode = $authUser && $authUser->isWarga();
        $isRwMode = $authUser && $authUser->isAdminRw() && !$isSuperAdminMode;
        $isRtMode = $authUser && $authUser->isAdminRt() && !$isSuperAdminMode;
    @endphp

    <!-- ==========================================================================
       SIDEBAR NAVIGATION (DONEZO CLEAN WHITE STYLE)
       ========================================================================== -->
    <aside class="sidebar {{ (Auth::check() && ($isWargaMode || $isSuperAdminMode || $isRwMode || $isRtMode)) ? 'hidden md:flex d-none d-md-flex' : '' }}" id="sidebar" data-no-motion="true">
        @php
            $authUser = Auth::user();
            $roleLabel = 'PENGURUS RW';
            $roleSub = 'Pengawas & Koordinator';
            $brandIcon = 'fa-building';
            if ($authUser && $authUser->isSuperAdmin()) {
                if ($authUser->isSimulatingRole()) {
                    $roleLabel = 'SIMULASI: ' . ($authUser->getActiveContext()['label'] ?? 'PERAN');
                    $roleSub = 'Mode Penyamaran';
                    $brandIcon = 'fa-user-astronaut';
                } else {
                    $roleLabel = 'SUPER ADMIN';
                    $roleSub = 'Hak Akses Tertinggi';
                    $brandIcon = 'fa-crown';
                }
            } elseif ($authUser && $authUser->isAdminRt()) {
                $roleLabel = 'RT ' . ($authUser->getRtKode() ?? '01');
                $roleSub = 'Pengurus Wilayah';
                $brandIcon = 'fa-landmark';
            } elseif ($authUser && $authUser->isWarga()) {
                $roleLabel = 'PORTAL WARGA';
                $roleSub = 'RT ' . ($authUser->getRtKode() ?? '01') . ' / RW 01';
                $brandIcon = 'fa-users';
            }
        @endphp

        <!-- Sidebar Header / Brand -->
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-link d-flex align-items-center gap-2">
                <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="SMART RW Logo" width="34" height="34" class="flex-shrink-0">
                <div class="sidebar-brand-text">
                    <span class="sidebar-brand-title">SMART <span>RW</span></span>
                    <span class="sidebar-brand-badge">{{ $roleLabel }}</span>
                </div>
            </a>
            <button class="d-lg-none toggle-sidebar-btn" id="closeSidebarBtn" title="Tutup Menu">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Items -->
        <div class="sidebar-content">
            @if(Auth::check() && Auth::user()->isSuperAdmin())
                {{-- ==================== S. ROLE: SUPER ADMIN ==================== --}}
                <div class="sidebar-group border-bottom pb-2 mb-2">
                    <div class="sidebar-group-title text-warning fw-bold">
                        <i class="fas fa-crown text-warning me-1"></i> SUPER ADMIN
                    </div>
                    <a class="sidebar-nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}">
                        <i class="fas fa-tachometer-alt text-warning"></i>
                        <span>Dashboard Super Admin</span>
                    </a>
                    <a class="sidebar-nav-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                        <i class="fas fa-arrows-rotate text-info"></i>
                        <span>Beralih Peran (Ganti Role)</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('superadmin.audit-logs') ? 'active' : '' }}" href="{{ route('superadmin.audit-logs') }}">
                        <i class="fas fa-shield-halved text-success"></i>
                        <span>Log Audit Sistem</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('superadmin.api-settings.*') ? 'active' : '' }}" href="{{ route('superadmin.api-settings.index') }}">
                        <i class="fas fa-key text-primary"></i>
                        <span>Pengaturan Token API</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('superadmin.rfid-devices.*') ? 'active' : '' }}" href="{{ route('superadmin.rfid-devices.index') }}">
                        <i class="fas fa-microchip text-info"></i>
                        <span>Perangkat RFID</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('rfid.history*') ? 'active' : '' }}" href="{{ route('rfid.history') }}">
                        <i class="fas fa-id-card-clip text-success"></i>
                        <span>Riwayat Scan RFID</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('assistant.faqs.*') ? 'active' : '' }}" href="{{ route('assistant.faqs.index') }}">
                        <i class="fas fa-robot text-warning"></i>
                        <span>Kelola FAQ Asisten</span>
                    </a>
                </div>
            @endif

            @if(Auth::check() && Auth::user()->isAdminRt())
                {{-- ==================== B. ROLE: KETUA RT ==================== --}}
                <div class="sidebar-group">
                    <div class="sidebar-group-title">Utama</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard RT</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Administrasi Warga</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('warga.index', 'warga.show', 'warga.create', 'warga.edit') ? 'active' : '' }}" href="{{ route('warga.index') }}">
                        <i class="fas fa-users"></i>
                        <span>Data Warga</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('kartu-keluarga.*') ? 'active' : '' }}" href="{{ route('kartu-keluarga.index') }}">
                        <i class="fas fa-id-card"></i>
                        <span>Data Keluarga & KK</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('warga.verifikasi.*') ? 'active' : '' }}" href="{{ route('warga.verifikasi.index') }}">
                        <i class="fas fa-user-check"></i>
                        <span>Verifikasi Warga</span>
                        @php
                            $pendingRtCount = \App\Models\UserProfile::where('status', 'pending')
                                ->where('rt_id', Auth::user()->managedRtId())
                                ->count();
                        @endphp
                        @if($pendingRtCount > 0)
                            <span class="badge bg-danger ms-auto rounded-pill">{{ $pendingRtCount }}</span>
                        @endif
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('warga.mutasi*') ? 'active' : '' }}" href="{{ route('warga.mutasi') }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Mutasi Warga</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Keuangan RT</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Pembayaran Iuran</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}" href="{{ route('pengeluaran.index') }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pengeluaran RT</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('kas.*') ? 'active' : '' }}" href="{{ route('kas.index') }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Kas RT</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Laporan Keuangan RT</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Pelayanan Masyarakat</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('bank-sampah.*') ? 'active' : '' }}" href="{{ route('bank-sampah.index') }}">
                        <i class="fas fa-recycle"></i>
                        <span>Bank Sampah</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('layanan.umkm.*') ? 'active' : '' }}" href="{{ route('layanan.umkm.index') }}">
                        <i class="fas fa-store"></i>
                        <span>UMKM RT</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('layanan.posyandu.*') ? 'active' : '' }}" href="{{ route('layanan.posyandu.index') }}">
                        <i class="fas fa-baby"></i>
                        <span>Posyandu</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('layanan.ronda.*') ? 'active' : '' }}" href="{{ route('layanan.ronda.index') }}">
                        <i class="fas fa-shield-halved"></i>
                        <span>Keamanan / Ronda</span>
                    </a>
                    @if(!Auth::user()->isSuperAdmin() || Auth::user()->isSimulatingRole())
                    <a class="sidebar-nav-item {{ request()->routeIs('rfid.history*') ? 'active' : '' }}" href="{{ route('rfid.history') }}">
                        <i class="fas fa-id-card-clip"></i>
                        <span>Riwayat Scan RFID</span>
                    </a>
                    @endif
                    <a class="sidebar-nav-item {{ request()->routeIs('kegiatan.*') && request('tingkat') !== 'rw' ? 'active' : '' }}" href="{{ route('kegiatan.index') }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Manajemen Kegiatan</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('aspirasi.*') ? 'active' : '' }}" href="{{ route('aspirasi.index') }}">
                        <i class="fas fa-comment-dots"></i>
                        <span>Aspirasi Warga</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('rt.verifikasi.*') ? 'active' : '' }}" href="{{ route('rt.verifikasi.permohonan') }}">
                        <i class="fas fa-file-signature"></i>
                        <span>Manajemen Permohonan</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Informasi</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('rt.informasi.pengumuman') ? 'active' : '' }}" href="{{ route('rt.informasi.pengumuman') }}">
                        <i class="fas fa-bullhorn"></i>
                        <span>Pengumuman RW</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('kegiatan.index') && request('tingkat') === 'rw' ? 'active' : '' }}" href="{{ route('kegiatan.index', ['tingkat' => 'rw']) }}">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Agenda RW</span>
                    </a>
                </div>

                <div class="sidebar-group mt-auto pt-2">
                    <div class="sidebar-group-title">Akun</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                        <i class="fas fa-user-circle"></i>
                        <span>Profil Ketua RT</span>
                    </a>
                </div>

            @elseif(Auth::check() && Auth::user()->isWarga())
                {{-- ==================== C. ROLE: WARGA ==================== --}}
                <div class="sidebar-group">
                    <div class="sidebar-group-title">Beranda</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard Warga</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Informasi</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('warga.pengumuman') && request('scope') === 'rt' ? 'active' : '' }}" href="{{ route('warga.pengumuman', ['scope' => 'rt']) }}">
                        <i class="fas fa-bullhorn"></i>
                        <span>Pengumuman RT</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('warga.pengumuman') && request('scope') === 'rw' ? 'active' : '' }}" href="{{ route('warga.pengumuman', ['scope' => 'rw']) }}">
                        <i class="fas fa-newspaper"></i>
                        <span>Pengumuman RW</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" href="{{ route('kegiatan.index') }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Agenda Kegiatan</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Layanan Warga</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('layanan.surat.*') ? 'active' : '' }}" href="{{ route('layanan.surat.index') }}">
                        <i class="fas fa-envelope-open-text"></i>
                        <span>Permohonan Surat</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('bank-sampah.*') ? 'active' : '' }}" href="{{ route('bank-sampah.index') }}">
                        <i class="fas fa-recycle"></i>
                        <span>Bank Sampah</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('layanan.umkm.*') ? 'active' : '' }}" href="{{ route('layanan.umkm.index') }}">
                        <i class="fas fa-store"></i>
                        <span>Info UMKM</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('layanan.posyandu.*') ? 'active' : '' }}" href="{{ route('layanan.posyandu.index') }}">
                        <i class="fas fa-baby"></i>
                        <span>Info Posyandu</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('aspirasi.*') ? 'active' : '' }}" href="{{ route('aspirasi.index') }}">
                        <i class="fas fa-comment-dots"></i>
                        <span>Aspirasi & Pengaduan</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Keuangan</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('pembayaran.index') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                        <i class="fas fa-file-invoice"></i>
                        <span>Tagihan Iuran</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('pembayaran.create') ? 'active' : '' }}" href="{{ route('pembayaran.create') }}">
                        <i class="fas fa-credit-card"></i>
                        <span>Pembayaran Iuran</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Permohonan Saya</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('layanan.surat.index') ? 'active' : '' }}" href="{{ route('layanan.surat.index') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Status & Riwayat</span>
                    </a>
                </div>

                <div class="sidebar-group mt-auto pt-2">
                    <div class="sidebar-group-title">Akun</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('kartu-keluarga.*') ? 'active' : '' }}" href="{{ route('kartu-keluarga.saya') }}">
                        <i class="fas fa-id-card"></i>
                        <span>Data Keluarga Saya</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                        <i class="fas fa-user-circle"></i>
                        <span>Profil Saya</span>
                    </a>
                </div>

            @else
                {{-- ==================== A. ROLE: PENGURUS RW / ADMIN ==================== --}}
                <div class="sidebar-group">
                    <div class="sidebar-group-title">Utama</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard RW</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Wilayah & Kependudukan</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('rts.*') ? 'active' : '' }}" href="{{ route('rts.index') }}">
                        <i class="fas fa-sitemap"></i>
                        <span>Manajemen RT</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('warga.index', 'warga.show', 'warga.edit') ? 'active' : '' }}" href="{{ route('warga.index') }}">
                        <i class="fas fa-users"></i>
                        <span>Data Kependudukan (Warga)</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('warga.verifikasi.*') ? 'active' : '' }}" href="{{ route('warga.verifikasi.index') }}">
                        <i class="fas fa-user-check"></i>
                        <span>Monitoring Verifikasi</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('rw.monitoring.warga') ? 'active' : '' }}" href="{{ route('rw.monitoring.warga') }}">
                        <i class="fas fa-users-viewfinder"></i>
                        <span>Monitoring Wilayah</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Keuangan</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('rw.audit.*') && !request()->routeIs('rw.audit.riwayat') ? 'active' : '' }}" href="{{ route('rw.audit.index') }}">
                        <i class="fas fa-wallet"></i>
                        <span>Keuangan & Audit RT</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('rw.metode-pembayaran.*') ? 'active' : '' }}" href="{{ route('rw.metode-pembayaran.index') }}">
                        <i class="fas fa-qrcode"></i>
                        <span>Metode Pembayaran</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Kegiatan</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('kegiatan.*') && (request('cakupan') === 'rw' || request('tingkat') === 'rw') ? 'active' : '' }}" href="{{ route('kegiatan.index', ['cakupan' => 'rw']) }}">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Kegiatan RW</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('rw.monitoring.kegiatan') ? 'active' : '' }}" href="{{ route('rw.monitoring.kegiatan') }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Monitoring Kegiatan RT</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Layanan</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('rw.monitoring.layanan') ? 'active' : '' }}" href="{{ route('rw.monitoring.layanan') }}">
                        <i class="fas fa-handshake-angle"></i>
                        <span>Monitoring Layanan RT</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Koordinasi & Pengawasan</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}" href="{{ route('pengumuman.index') }}">
                        <i class="fas fa-bullhorn"></i>
                        <span>Pengumuman RW</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('rw.pengawasan.persetujuan') ? 'active' : '' }}" href="{{ route('rw.pengawasan.persetujuan') }}">
                        <i class="fas fa-stamp"></i>
                        <span>Persetujuan RW</span>
                    </a>
                    <a class="sidebar-nav-item {{ request()->routeIs('rw.audit.riwayat') ? 'active' : '' }}" href="{{ route('rw.audit.riwayat') }}">
                        <i class="fas fa-list-check"></i>
                        <span>Log & Audit Trail</span>
                    </a>
                    @if(!Auth::user()->isSuperAdmin() || Auth::user()->isSimulatingRole())
                    <a class="sidebar-nav-item {{ request()->routeIs('rfid.history*') ? 'active' : '' }}" href="{{ route('rfid.history') }}">
                        <i class="fas fa-id-card-clip"></i>
                        <span>Riwayat Scan RFID</span>
                    </a>
                    @endif
                    <a class="sidebar-nav-item {{ request()->routeIs('assistant.faqs.*') ? 'active' : '' }}" href="{{ route('assistant.faqs.index') }}">
                        <i class="fas fa-robot"></i>
                        <span>Kelola FAQ Asisten</span>
                    </a>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Laporan</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                        <i class="fas fa-file-invoice"></i>
                        <span>Laporan RW</span>
                    </a>
                </div>

                <div class="sidebar-group mt-auto pt-2">
                    <div class="sidebar-group-title">Akun</div>
                    <a class="sidebar-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                        <i class="fas fa-user-circle"></i>
                        <span>Profil & Pengaturan</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-primary px-2 py-1">Smart RW 01 v2.5</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-light border-0 p-1 text-danger" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- ==========================================================================
       TOP NAVBAR (MODERN CLEAN SAAS)
       ========================================================================== -->
    <header class="navbar-saas navbar">
        <div class="topbar-left d-flex align-items-center gap-2 gap-md-3 flex-grow-1" style="min-width: 0;">
            <button class="toggle-sidebar-btn flex-shrink-0 {{ (Auth::check() && ($isWargaMode || $isSuperAdminMode || $isRwMode || $isRtMode)) ? 'hidden md:flex d-none d-md-flex' : '' }}" id="toggleSidebarBtn" title="Buka/Tutup Menu" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            {{-- Ikon Logo Rumah Smart RW Khusus Mobile (< 768px) ala WhatsApp Light --}}
            <a href="{{ $isSuperAdminMode ? route('superadmin.dashboard') : route('dashboard') }}" class="mobile-header-brand md:hidden d-flex d-md-none w-9 h-9 rounded-xl bg-[#DCF8C6] border border-[#BBF7D0] text-[#00A884] items-center justify-center shrink-0 text-decoration-none shadow-xs" title="Smart RW 01" aria-label="Beranda Smart RW">
                <i class="fas fa-house" style="font-size: 1.1rem; color: #00A884;"></i>
            </a>

            {{-- Judul Halaman (Hanya Tampil di Layar Desktop >= 768px agar tidak kepotong di HP) --}}
            <div class="navbar-page-indicator hidden md:flex d-none d-md-flex flex-column" style="min-width: 0; flex: 1 1 auto;">
                <span class="navbar-page-title topbar-title hidden md:block text-truncate">@yield('title', 'Sistem Informasi Smart RW')</span>
                <span class="navbar-page-subtitle topbar-subtitle d-none d-sm-inline text-truncate">Wilayah RW 01 &bull; Kelurahan Digital</span>
            </div>
        </div>

        <div class="topbar-right ms-auto d-flex align-items-center gap-1 gap-sm-2 flex-shrink-0">
            @php
                $user = Auth::user();
                $defaultName = $user ? $user->name : 'Pengguna';
                $fotoUrl = 'https://ui-avatars.com/api/?background=147A45&color=fff&rounded=true&size=128&name=' . urlencode($defaultName);
                if ($user && $user->profile && $user->profile->foto_base64) {
                    $fotoUrl = $user->profile->foto_base64;
                }
                $unreadCount = 0;
                $latestNotifs = collect();
                if ($user) {
                    $unreadCount = \App\Models\Notifikasi::where('user_id', $user->id)->where('dibaca', false)->count();
                    $latestNotifs = \App\Models\Notifikasi::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                }
            @endphp

            <!-- Panic Button Emergency Action -->
            <button type="button" class="topbar-panic-btn" data-bs-toggle="modal" data-bs-target="#modalGlobalPanic" title="Tombol Darurat (Panic Button)" aria-label="Tombol Darurat">
                <i class="fas fa-triangle-exclamation fa-beat text-white"></i>
                <span class="d-none d-sm-inline">DARURAT</span>
                <span class="d-inline d-sm-none" style="font-size: 0.72rem; letter-spacing: 0.04em;">SOS</span>
            </button>

            <!-- Theme Mode Toggle -->
            <button type="button" class="topbar-icon-btn" id="themeToggleBtn" title="Ganti mode terang/gelap" aria-label="Toggle Theme">
                <i class="fas fa-sun" id="themeToggleIcon"></i>
            </button>

            <!-- Notifications Dropdown -->
            <div class="dropdown">
                <button type="button" class="topbar-icon-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi" aria-label="Notifikasi">
                    <i class="fas fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="topbar-badge-dot">{{ $unreadCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end p-2 border-0 shadow-lg" style="min-width: 280px; max-width: calc(100vw - 24px); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <span class="fw-bold text-dark small">Notifikasi Baru</span>
                        @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifikasi.read-all') }}" class="m-0">
                                @csrf
                                <button class="btn btn-sm btn-link p-0 text-success text-decoration-none small">Tandai semua</button>
                            </form>
                        @endif
                    </div>
                    <div class="py-2" style="max-height: 280px; overflow-y: auto;">
                        @if($latestNotifs->count())
                            @foreach($latestNotifs as $notif)
                                <a href="{{ route('notifikasi.index') }}" class="dropdown-item p-2.5 rounded-3 mb-1 {{ $notif->dibaca ? '' : 'bg-success bg-opacity-10' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="me-2">
                                            <strong class="d-block small text-dark mb-0.5">{{ $notif->judul }}</strong>
                                            <span class="text-secondary small d-block" style="font-size: 0.78rem;">{{ \Illuminate\Support\Str::limit($notif->pesan, 55) }}</span>
                                        </div>
                                        @if(!$notif->dibaca)
                                            <span class="badge badge-soft-success" style="font-size: 0.65rem;">Baru</span>
                                        @endif
                                    </div>
                                    <span class="text-muted" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</span>
                                </a>
                            @endforeach
                        @else
                            <div class="text-center py-3 text-secondary small">
                                <i class="fas fa-bell-slash mb-1 d-block"></i>
                                Tidak ada notifikasi baru
                            </div>
                        @endif
                    </div>
                    <div class="border-top pt-2 text-center">
                        <a href="{{ route('notifikasi.index') }}" class="small fw-semibold text-success text-decoration-none">
                            Lihat Semua Notifikasi <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <button type="button" class="topbar-profile-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ $fotoUrl }}" alt="Avatar" class="topbar-avatar">
                    <div class="topbar-user-info d-none d-sm-flex">
                        <span class="topbar-user-name">{{ $defaultName }}</span>
                        <span class="topbar-user-role">{{ $roleLabel }}</span>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="min-width: 200px; border-radius: 14px;">
                    <li class="px-3 py-2 border-bottom mb-1">
                        <span class="d-block fw-bold text-dark small">{{ $defaultName }}</span>
                        <span class="badge badge-soft-primary" style="font-size: 0.7rem;">{{ $roleLabel }}</span>
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 py-2" href="{{ route('profile.index') }}">
                            <i class="fas fa-user-circle me-2 text-success"></i> Profil Saya
                        </a>
                    </li>
                    @if(Auth::check() && Auth::user()->isSuperAdmin())
                        <li>
                            <button type="button" class="dropdown-item rounded-2 py-2 text-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                                <i class="fas fa-arrows-rotate me-2 text-warning"></i> Beralih Peran (Ganti Role)
                            </button>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 py-2 text-success fw-semibold" href="{{ route('superadmin.dashboard') }}">
                                <i class="fas fa-crown me-2 text-success"></i> Super Admin Dashboard
                            </a>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item rounded-2 py-2 text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Keluar (Logout)
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- ==========================================================================
       MAIN CONTENT CONTAINER
       ========================================================================== -->
    <main class="main-content {{ $isWargaMode ? 'has-warga-bottom-nav pb-24 md:pb-8' : '' }} {{ $isSuperAdminMode ? 'has-superadmin-bottom-nav pb-24 md:pb-8' : '' }} {{ $isRwMode ? 'has-rw-bottom-nav pb-24 md:pb-8' : '' }} {{ $isRtMode ? 'has-rt-bottom-nav pb-24 md:pb-8' : '' }}" id="mainContent">
        <div class="page-container">
            {{-- Mode Simulasi Super Admin Banner --}}
            @if(session()->has('super_admin_context'))
                <div class="simulation-banner alert border border-warning border-opacity-25 rounded-4 shadow-sm mb-4 p-3" style="background: var(--accent-soft);">
                    <div class="simulation-banner-info">
                        <div class="simulation-banner-badge mb-1 mb-sm-0">
                            <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill fw-bold">
                                <i class="fas fa-user-astronaut me-1"></i> MODE SIMULASI AKTIF
                            </span>
                        </div>
                        <div class="simulation-banner-text fw-semibold" style="color: var(--ink);">
                            {{ session('super_admin_context.label') ?? 'Mode Simulasi' }}
                        </div>
                    </div>
                    <div class="simulation-banner-actions">
                        <button type="button" class="btn btn-sm btn-sim-outline rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                            <i class="fas fa-arrows-rotate me-1"></i> Ganti Role
                        </button>
                        <form method="POST" action="{{ route('superadmin.switch-role') }}" class="m-0 p-0 d-grid">
                            @csrf
                            <input type="hidden" name="mode" value="super_admin">
                            <button type="submit" class="btn btn-sm btn-warning text-dark rounded-pill fw-semibold w-100">
                                <i class="fas fa-shield-alt me-1"></i> <span class="d-none d-sm-inline">Kembali ke Super Admin</span><span class="d-inline d-sm-none">Keluar Simulasi</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Yield Page Content --}}
            @yield('content')
        </div>

        <footer>
            <p class="mb-0">&copy; {{ date('Y') }} <strong>SMART RW</strong> &bull; Sistem Informasi Rukun Warga & Pelayanan Masyarakat Digital</p>
        </footer>
    </main>

    @if(Auth::check() && Auth::user()->isWarga())
        {{-- ==================================================================
           BOTTOM NAVIGATION BAR KHUSUS MOBILE (< 768px) PORTAL WARGA
           ================================================================== --}}
        <nav class="warga-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_16px_rgba(0,0,0,0.04)] pb-safe" aria-label="Navigasi Bawah Portal Warga">
            <div class="bottom-nav-inner">
                {{-- 1. Beranda --}}
                <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="bottom-nav-label">Beranda</span>
                </a>

                {{-- 2. Informasi (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('warga.pengumuman*') || request()->routeIs('kegiatan.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetInformasi">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <span class="bottom-nav-label">Informasi</span>
                </button>

                {{-- 3. Iuran (Tombol Tengah Menonjol ala E-Wallet) --}}
                <a href="{{ route('pembayaran.index') }}" class="bottom-nav-item bottom-nav-item--center {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}" title="Tagihan & Pembayaran Iuran">
                    <div class="bottom-nav-fab -mt-4 shadow-md">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="bottom-nav-label">Iuran</span>
                </a>

                {{-- 4. Riwayat (Permohonan Warga) --}}
                <a href="{{ route('layanan.surat.index') }}" class="bottom-nav-item {{ request()->routeIs('layanan.surat.*') ? 'active' : '' }}">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <span class="bottom-nav-label">Riwayat</span>
                </a>

                {{-- 5. Akun (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('kartu-keluarga.*') || request()->routeIs('profile.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetAkun">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="bottom-nav-label">Akun</span>
                </button>
            </div>
        </nav>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: INFORMASI (Pengumuman RT, RW & Agenda)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetInformasi" tabindex="-1" aria-labelledby="bottomSheetInformasiLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetInformasiLabel">
                                <i class="fas fa-bullhorn text-emerald me-1.5" style="color: #00A884;"></i> Pusat Informasi Lingkungan
                            </h6>
                            <small class="text-secondary">Pengumuman RT, RW, dan agenda kegiatan</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        {{-- Pengumuman RT --}}
                        <a href="{{ route('warga.pengumuman', ['scope' => 'rt']) }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pengumuman RT</strong>
                                <span class="text-secondary small d-block">Informasi khusus warga RT {{ optional(Auth::user())->getRtKode() ?? '01' }}</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        {{-- Pengumuman RW --}}
                        <a href="{{ route('warga.pengumuman', ['scope' => 'rw']) }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pengumuman RW</strong>
                                <span class="text-secondary small d-block">Kabar dan surat edaran tingkat RW 01</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        {{-- Agenda Kegiatan --}}
                        <a href="{{ route('kegiatan.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Agenda Kegiatan</strong>
                                <span class="text-secondary small d-block">Jadwal kerja bakti, pertemuan, & perayaan warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: AKUN (Data Keluarga, Profil & Logout)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetAkun" tabindex="-1" aria-labelledby="bottomSheetAkunLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex align-items-center gap-3 p-2.5 mb-3 rounded-3" style="background: var(--surface-2, #e7fceb); border: 1px solid var(--border);">
                        <img src="{{ $fotoUrl }}" alt="Avatar" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <strong class="d-block text-dark text-truncate small fw-bold">{{ $defaultName }}</strong>
                            <span class="badge badge-soft-primary px-2 py-0.5 rounded-full" style="font-size: 0.72rem;">Warga RT {{ optional(Auth::user())->getRtKode() ?? '01' }} / RW 01</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        {{-- Data Keluarga Saya --}}
                        <a href="{{ route('kartu-keluarga.saya') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #ecfdf5; color: #047857;">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Data Keluarga Saya</strong>
                                <span class="text-secondary small d-block">Informasi Nomor Kartu Keluarga & anggota keluarga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        {{-- Profil Saya --}}
                        <a href="{{ route('profile.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-user-gear"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Profil Saya</strong>
                                <span class="text-secondary small d-block">Ubah biodata, nomor HP / WhatsApp, & kata sandi</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        {{-- Keluar (Logout) --}}
                        <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                            @csrf
                            <button type="submit" class="bottom-sheet-item border-danger border-opacity-25 w-100 text-start" style="background: #fef2f2; color: #dc2626;">
                                <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="d-block small fw-bold text-danger">Keluar (Logout)</strong>
                                    <span class="text-danger small opacity-75 d-block">Akhiri sesi portal warga di perangkat ini</span>
                                </div>
                                <i class="fas fa-chevron-right text-danger opacity-50 small"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @elseif(Auth::check() && Auth::user()->isSuperAdmin() && !Auth::user()->isSimulatingRole())
        {{-- ==================================================================
           BOTTOM NAVIGATION BAR KHUSUS MOBILE (< 768px) SUPER ADMIN
           ================================================================== --}}
        <nav class="superadmin-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_16px_rgba(0,0,0,0.04)] pb-safe" aria-label="Navigasi Bawah Super Admin">
            <div class="bottom-nav-inner">
                {{-- 1. Beranda --}}
                <a href="{{ route('superadmin.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="bottom-nav-label">Beranda</span>
                </a>

                {{-- 2. Wilayah (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('rts.*') || request()->routeIs('warga.*') || request()->routeIs('rw.monitoring.*') || request()->routeIs('rw.audit.*') || request()->routeIs('rw.metode-pembayaran.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetSuperAdminWilayah">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <span class="bottom-nav-label">Wilayah</span>
                </button>

                {{-- 3. Sistem (Tombol Tengah Menonjol ala E-Wallet) --}}
                <button type="button" class="bottom-nav-item bottom-nav-item--center {{ (request()->routeIs('superadmin.rfid-devices.*') || request()->routeIs('rfid.history*') || request()->routeIs('superadmin.api-settings.*') || request()->routeIs('superadmin.audit-logs') || request()->routeIs('assistant.faqs.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetSuperAdminSistem" title="Pusat Kontrol Sistem">
                    <div class="bottom-nav-fab -mt-4 shadow-md ring-4 ring-white">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <span class="bottom-nav-label">Sistem</span>
                </button>

                {{-- 4. Monitor (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('dashboard') || request()->routeIs('kegiatan.*') || request()->routeIs('rw.monitoring.layanan') || request()->routeIs('pengumuman.*') || request()->routeIs('rw.pengawasan.persetujuan') || request()->routeIs('laporan.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetSuperAdminMonitor">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="bottom-nav-label">Monitor</span>
                </button>

                {{-- 5. Akun (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetSuperAdminAkun">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="bottom-nav-label">Akun</span>
                </button>
            </div>
        </nav>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: WILAYAH (Manajemen RT, Warga, Monitoring, Keuangan)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetSuperAdminWilayah" tabindex="-1" aria-labelledby="bottomSheetSuperAdminWilayahLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetSuperAdminWilayahLabel">
                                <i class="fas fa-sitemap text-emerald me-1.5" style="color: #00A884;"></i> Wilayah & Keuangan
                            </h6>
                            <small class="text-secondary">Manajemen RT, data kependudukan & keuangan wilayah</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    {{-- Grup Wilayah & Kependudukan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Wilayah & Kependudukan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('rts.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Manajemen RT</strong>
                                <span class="text-secondary small d-block">Kelola unit RT dinamis & struktur pengurus</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('warga.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Data Kependudukan (Warga)</strong>
                                <span class="text-secondary small d-block">Data warga seluruh RT, mutasi, & KK</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('warga.verifikasi.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Verifikasi</strong>
                                <span class="text-secondary small d-block">Persetujuan & validasi pendaftaran warga baru</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.monitoring.warga') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fas fa-users-viewfinder"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Wilayah</strong>
                                <span class="text-secondary small d-block">Peta sebaran & statistik kependudukan RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Keuangan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Keuangan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('rw.audit.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #ecfdf5; color: #047857;">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Keuangan & Audit RT</strong>
                                <span class="text-secondary small d-block">Laporan kas masuk/keluar & audit bulanan</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.metode-pembayaran.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Metode Pembayaran</strong>
                                <span class="text-secondary small d-block">Kelola QRIS, transfer bank, & e-wallet</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: SISTEM (Beralih Peran, RFID, API, Log Audit, FAQ)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetSuperAdminSistem" tabindex="-1" aria-labelledby="bottomSheetSuperAdminSistemLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetSuperAdminSistemLabel">
                                <i class="fas fa-sliders text-emerald me-1.5" style="color: #00A884;"></i> Pusat Kontrol Sistem
                            </h6>
                            <small class="text-secondary">Simulasi peran, integrasi hardware, & pengaturan API</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="javascript:void(0)" class="bottom-sheet-item" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-arrows-rotate"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Beralih Peran (Ganti Role)</strong>
                                <span class="text-secondary small d-block">Simulasikan peran RW, RT, atau Warga tanpa logout</span>
                            </div>
                            <span class="badge bg-warning text-dark px-2 py-0.5 rounded-full" style="font-size: 0.7rem;">Simulasi</span>
                        </a>

                        <a href="{{ route('superadmin.rfid-devices.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-microchip"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Perangkat RFID</strong>
                                <span class="text-secondary small d-block">Registrasi & status konektivitas scanner portal</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rfid.history') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-id-card-clip"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Riwayat Scan RFID</strong>
                                <span class="text-secondary small d-block">Log akses gerbang, tap kartu e-KTP warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('superadmin.api-settings.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pengaturan Token API</strong>
                                <span class="text-secondary small d-block">Kredensial webhook & token integrasi IoT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('superadmin.audit-logs') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Log Audit Sistem</strong>
                                <span class="text-secondary small d-block">Jejak aktivitas super admin & keamanan data</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('assistant.faqs.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Kelola FAQ Asisten</strong>
                                <span class="text-secondary small d-block">Basis pengetahuan AI Assistant WhatsApp RW</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: MONITOR (Dashboard RW, Kegiatan, Layanan, Koordinasi, Laporan)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetSuperAdminMonitor" tabindex="-1" aria-labelledby="bottomSheetSuperAdminMonitorLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetSuperAdminMonitorLabel">
                                <i class="fas fa-chart-line text-emerald me-1.5" style="color: #00A884;"></i> Monitoring & Pengawasan RW
                            </h6>
                            <small class="text-secondary">Pusat pantauan kegiatan, layanan, & laporan wilayah</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    {{-- Dashboard RW --}}
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('dashboard') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Dashboard RW</strong>
                                <span class="text-secondary small d-block">Tinjauan umum koordinasi rukun warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Kegiatan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Kegiatan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('kegiatan.index', ['cakupan' => 'rw']) }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Kegiatan RW</strong>
                                <span class="text-secondary small d-block">Agenda & perayaan tingkat rukun warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.monitoring.kegiatan') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Kegiatan RT</strong>
                                <span class="text-secondary small d-block">Pantauan acara & gotong royong seluruh RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Layanan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Layanan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('rw.monitoring.layanan') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #ecfdf5; color: #047857;">
                                <i class="fas fa-handshake-angle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Layanan RT</strong>
                                <span class="text-secondary small d-block">Kinerja posyandu, bank sampah, & surat</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Koordinasi & Pengawasan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Koordinasi & Pengawasan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('pengumuman.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pengumuman RW</strong>
                                <span class="text-secondary small d-block">Publikasi siaran kabar untuk seluruh warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.pengawasan.persetujuan') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                <i class="fas fa-stamp"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Persetujuan RW</strong>
                                <span class="text-secondary small d-block">Validasi berkas & persetujuan tindak lanjut</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.audit.riwayat') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Log & Audit Trail</strong>
                                <span class="text-secondary small d-block">Riwayat verifikasi keuangan dan perubahan data</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Laporan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Laporan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('laporan.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Laporan RW</strong>
                                <span class="text-secondary small d-block">Rekapitulasi berkas & ekspor laporan kependudukan</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: AKUN (Profil & Logout)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetSuperAdminAkun" tabindex="-1" aria-labelledby="bottomSheetSuperAdminAkunLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex align-items-center gap-3 p-2.5 mb-3 rounded-3" style="background: var(--surface-2, #e7fceb); border: 1px solid var(--border);">
                        <img src="{{ $fotoUrl }}" alt="Avatar" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <strong class="d-block text-dark text-truncate small fw-bold">{{ $defaultName }}</strong>
                            <span class="badge bg-warning text-dark px-2 py-0.5 rounded-full" style="font-size: 0.72rem;">Super Admin & Developer</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('profile.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-user-gear"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Profil & Pengaturan</strong>
                                <span class="text-secondary small d-block">Ubah informasi akun, email, & kata sandi</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                            @csrf
                            <button type="submit" class="bottom-sheet-item border-danger border-opacity-25 w-100 text-start" style="background: #fef2f2; color: #dc2626;">
                                <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="d-block small fw-bold text-danger">Keluar (Logout)</strong>
                                    <span class="text-danger small opacity-75 d-block">Akhiri sesi Super Admin di perangkat ini</span>
                                </div>
                                <i class="fas fa-chevron-right text-danger opacity-50 small"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @elseif($isRwMode)
        {{-- ==================================================================
           BOTTOM NAVIGATION BAR KHUSUS MOBILE (< 768px) KETUA RW
           ================================================================== --}}
        <nav class="rw-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_16px_rgba(0,0,0,0.04)] pb-safe" aria-label="Navigasi Bawah Ketua RW">
            <div class="bottom-nav-inner">
                {{-- 1. Beranda --}}
                <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="bottom-nav-label">Beranda</span>
                </a>

                {{-- 2. Wilayah (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('rts.*') || request()->routeIs('warga.*') || request()->routeIs('rw.monitoring.warga')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRwWilayah">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <span class="bottom-nav-label">Wilayah</span>
                </button>

                {{-- 3. Pengawasan (Tombol Tengah Menonjol ala E-Wallet) --}}
                <button type="button" class="bottom-nav-item bottom-nav-item--center {{ (request()->routeIs('rw.pengawasan.*') || request()->routeIs('pengumuman.*') || request()->routeIs('rw.audit.riwayat') || request()->routeIs('rfid.history*') || request()->routeIs('assistant.faqs.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRwPengawasan" title="Koordinasi & Pengawasan RW">
                    <div class="bottom-nav-fab -mt-4 shadow-md ring-4 ring-white">
                        <i class="fas fa-stamp"></i>
                    </div>
                    <span class="bottom-nav-label">Pengawasan</span>
                </button>

                {{-- 4. Monitor (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('rw.audit.*') || request()->routeIs('rw.metode-pembayaran.*') || request()->routeIs('kegiatan.*') || request()->routeIs('rw.monitoring.*') || request()->routeIs('laporan.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRwMonitor">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="bottom-nav-label">Monitor</span>
                </button>

                {{-- 5. Akun (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRwAkun">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="bottom-nav-label">Akun</span>
                </button>
            </div>
        </nav>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: WILAYAH (Manajemen RT, Warga, Verifikasi, Monitoring)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRwWilayah" tabindex="-1" aria-labelledby="bottomSheetRwWilayahLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetRwWilayahLabel">
                                <i class="fas fa-sitemap text-emerald me-1.5" style="color: #00A884;"></i> Wilayah & Kependudukan
                            </h6>
                            <small class="text-secondary">Manajemen RT dan pantauan kependudukan RW 01</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('rts.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Manajemen RT</strong>
                                <span class="text-secondary small d-block">Kelola unit RT dinamis & struktur pengurus</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('warga.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Data Kependudukan (Warga)</strong>
                                <span class="text-secondary small d-block">Data warga seluruh RT, mutasi, & KK</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('warga.verifikasi.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Verifikasi</strong>
                                <span class="text-secondary small d-block">Validasi & verifikasi warga baru oleh RT/RW</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.monitoring.warga') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fas fa-users-viewfinder"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Wilayah</strong>
                                <span class="text-secondary small d-block">Peta sebaran & statistik kependudukan RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: PENGAWASAN (Persetujuan, Pengumuman, Audit Trail, RFID, FAQ)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRwPengawasan" tabindex="-1" aria-labelledby="bottomSheetRwPengawasanLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetRwPengawasanLabel">
                                <i class="fas fa-stamp text-emerald me-1.5" style="color: #00A884;"></i> Koordinasi & Pengawasan RW
                            </h6>
                            <small class="text-secondary">Persetujuan surat, publikasi siaran, dan audit trail</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('rw.pengawasan.persetujuan') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                <i class="fas fa-stamp"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Persetujuan RW</strong>
                                <span class="text-secondary small d-block">Tinjau & setujui permohonan surat warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('pengumuman.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pengumuman RW</strong>
                                <span class="text-secondary small d-block">Publikasi siaran kabar untuk seluruh warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.audit.riwayat') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Log & Audit Trail</strong>
                                <span class="text-secondary small d-block">Riwayat verifikasi keuangan dan perubahan data</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rfid.history') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-id-card-clip"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Riwayat Scan RFID</strong>
                                <span class="text-secondary small d-block">Log akses gerbang, tap kartu e-KTP warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('assistant.faqs.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Kelola FAQ Asisten</strong>
                                <span class="text-secondary small d-block">Basis pengetahuan AI Assistant WhatsApp RW</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: MONITOR (Keuangan, Kegiatan, Layanan, Laporan)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRwMonitor" tabindex="-1" aria-labelledby="bottomSheetRwMonitorLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetRwMonitorLabel">
                                <i class="fas fa-chart-line text-emerald me-1.5" style="color: #00A884;"></i> Monitoring Terpadu RW
                            </h6>
                            <small class="text-secondary">Audit kas RT, kegiatan wilayah, dan laporan resmi</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    {{-- Grup Keuangan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Keuangan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('rw.audit.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #ecfdf5; color: #047857;">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Keuangan & Audit RT</strong>
                                <span class="text-secondary small d-block">Laporan kas masuk/keluar & audit bulanan</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.metode-pembayaran.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Metode Pembayaran</strong>
                                <span class="text-secondary small d-block">Kelola QRIS, transfer bank, & e-wallet</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Kegiatan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Kegiatan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('kegiatan.index', ['cakupan' => 'rw']) }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Kegiatan RW</strong>
                                <span class="text-secondary small d-block">Agenda & perayaan tingkat rukun warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('rw.monitoring.kegiatan') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Kegiatan RT</strong>
                                <span class="text-secondary small d-block">Pantauan acara & gotong royong seluruh RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Layanan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Layanan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('rw.monitoring.layanan') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #ecfdf5; color: #047857;">
                                <i class="fas fa-handshake-angle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Monitoring Layanan RT</strong>
                                <span class="text-secondary small d-block">Kinerja posyandu, bank sampah, & surat</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Laporan --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Laporan</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('laporan.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Laporan RW</strong>
                                <span class="text-secondary small d-block">Rekapitulasi berkas & ekspor laporan kependudukan</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: AKUN (Profil, Beralih Simulasi, Logout)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRwAkun" tabindex="-1" aria-labelledby="bottomSheetRwAkunLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex align-items-center gap-3 p-2.5 mb-3 rounded-3" style="background: var(--surface-2, #e7fceb); border: 1px solid var(--border);">
                        <img src="{{ $fotoUrl }}" alt="Avatar" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <strong class="d-block text-dark text-truncate small fw-bold">{{ $defaultName }}</strong>
                            @if(session()->has('super_admin_context'))
                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-full" style="font-size: 0.72rem;">Simulasi: Ketua RW</span>
                            @else
                                <span class="badge badge-soft-primary px-2 py-0.5 rounded-full" style="font-size: 0.72rem;">Pengurus RW 01</span>
                            @endif
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('profile.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-user-gear"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Profil & Pengaturan</strong>
                                <span class="text-secondary small d-block">Ubah profil, kontak WhatsApp, & kata sandi</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        {{-- Tombol Kembali ke Super Admin jika dalam Mode Simulasi --}}
                        @if(session()->has('super_admin_context'))
                            <form method="POST" action="{{ route('superadmin.switch-role') }}" class="m-0 w-100">
                                @csrf
                                <input type="hidden" name="mode" value="super_admin">
                                <button type="submit" class="bottom-sheet-item border-warning border-opacity-50 w-100 text-start" style="background: #fffbeb; color: #b45309;">
                                    <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong class="d-block small fw-bold text-dark">Kembali ke Super Admin</strong>
                                        <span class="text-secondary small d-block">Akhiri simulasi peran Ketua RW</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </button>
                            </form>
                        @endif

                        {{-- Keluar (Logout) --}}
                        <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                            @csrf
                            <button type="submit" class="bottom-sheet-item border-danger border-opacity-25 w-100 text-start" style="background: #fef2f2; color: #dc2626;">
                                <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="d-block small fw-bold text-danger">Keluar (Logout)</strong>
                                    <span class="text-danger small opacity-75 d-block">Akhiri sesi di perangkat ini</span>
                                </div>
                                <i class="fas fa-chevron-right text-danger opacity-50 small"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @elseif($isRtMode)
        {{-- ==================================================================
           BOTTOM NAVIGATION BAR KHUSUS MOBILE (< 768px) KETUA RT
           ================================================================== --}}
        <nav class="rt-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_16px_rgba(0,0,0,0.04)] pb-safe" aria-label="Navigasi Bawah Ketua RT">
            <div class="bottom-nav-inner">
                {{-- 1. Beranda --}}
                <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="bottom-nav-label">Beranda</span>
                </a>

                {{-- 2. Warga (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('warga.*') || request()->routeIs('kartu-keluarga.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRtWarga">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="bottom-nav-label">Warga</span>
                </button>

                {{-- 3. Layanan (Tombol Tengah Menonjol ala E-Wallet) --}}
                <button type="button" class="bottom-nav-item bottom-nav-item--center {{ (request()->routeIs('rt.verifikasi.*') || request()->routeIs('bank-sampah.*') || request()->routeIs('layanan.umkm.*') || request()->routeIs('layanan.posyandu.*') || request()->routeIs('layanan.ronda.*') || request()->routeIs('rfid.history*') || request()->routeIs('kegiatan.*') || request()->routeIs('aspirasi.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRtLayanan" title="Pelayanan Masyarakat RT">
                    <div class="bottom-nav-fab -mt-4 shadow-md ring-4 ring-white">
                        <i class="fas fa-handshake-angle"></i>
                    </div>
                    <span class="bottom-nav-label">Layanan</span>
                </button>

                {{-- 4. Keuangan (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ (request()->routeIs('pembayaran.*') || request()->routeIs('pengeluaran.*') || request()->routeIs('kas.*') || request()->routeIs('laporan.*') || request()->routeIs('rt.informasi.*')) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRtKeuangan">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="bottom-nav-label">Keuangan</span>
                </button>

                {{-- 5. Akun (Modal Bottom Sheet) --}}
                <button type="button" class="bottom-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#bottomSheetRtAkun">
                    <div class="bottom-nav-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="bottom-nav-label">Akun</span>
                </button>
            </div>
        </nav>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: WARGA (Administrasi Warga)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRtWarga" tabindex="-1" aria-labelledby="bottomSheetRtWargaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetRtWargaLabel">
                                <i class="fas fa-users text-emerald me-1.5" style="color: #00A884;"></i> Administrasi Warga
                            </h6>
                            <small class="text-secondary">Data kependudukan, kartu keluarga, & mutasi warga</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('warga.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Data Warga</strong>
                                <span class="text-secondary small d-block">Daftar lengkap warga dan status kependudukan</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('kartu-keluarga.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Data Keluarga & KK</strong>
                                <span class="text-secondary small d-block">Nomor KK, kepala keluarga, & susunan anggota</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('warga.verifikasi.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-1.5">
                                    <strong class="d-block text-dark small fw-bold">Verifikasi Warga</strong>
                                    @php
                                        $pendingRtCount = \App\Models\UserProfile::where('status', 'pending')
                                            ->where('rt_id', $authUser->managedRtId())
                                            ->count();
                                    @endphp
                                    @if($pendingRtCount > 0)
                                        <span class="badge bg-danger rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">{{ $pendingRtCount }}</span>
                                    @endif
                                </div>
                                <span class="text-secondary small d-block">Persetujuan pendaftaran warga baru RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('warga.mutasi') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Mutasi Warga</strong>
                                <span class="text-secondary small d-block">Warga pindah masuk, keluar, & antar-RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: LAYANAN (Pelayanan Masyarakat RT)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRtLayanan" tabindex="-1" aria-labelledby="bottomSheetRtLayananLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetRtLayananLabel">
                                <i class="fas fa-handshake-angle text-emerald me-1.5" style="color: #00A884;"></i> Pelayanan Masyarakat
                            </h6>
                            <small class="text-secondary">Pusat operasional surat, lingkungan, & kegiatan</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('rt.verifikasi.permohonan') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Manajemen Permohonan</strong>
                                <span class="text-secondary small d-block">Tinjau & terbitkan surat pengantar warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('bank-sampah.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-recycle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Bank Sampah</strong>
                                <span class="text-secondary small d-block">Penimbangan, setoran sampah, & saldo warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('layanan.umkm.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">UMKM RT</strong>
                                <span class="text-secondary small d-block">Etalase usaha lokal dan promosi warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('layanan.posyandu.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fce7f3; color: #db2777;">
                                <i class="fas fa-baby"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Posyandu</strong>
                                <span class="text-secondary small d-block">Pemeriksaan balita, lansia, & imunisasi</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('layanan.ronda.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Keamanan / Ronda</strong>
                                <span class="text-secondary small d-block">Jadwal ronda malam, pos kamling, & absensi</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        @if(!Auth::user()->isSuperAdmin() || Auth::user()->isSimulatingRole())
                        <a href="{{ route('rfid.history') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fas fa-id-card-clip"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Riwayat Scan RFID</strong>
                                <span class="text-secondary small d-block">Log absensi pos ronda & gerbang RFID</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                        @endif

                        <a href="{{ route('kegiatan.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Manajemen Kegiatan</strong>
                                <span class="text-secondary small d-block">Jadwal gotong royong, arisan, & musyawarah RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('aspirasi.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef9c3; color: #ca8a04;">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Aspirasi Warga</strong>
                                <span class="text-secondary small d-block">Kotak saran, aduan, & aspirasi warga RT</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: KEUANGAN (Keuangan RT & Informasi)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRtKeuangan" tabindex="-1" aria-labelledby="bottomSheetRtKeuanganLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="bottomSheetRtKeuanganLabel">
                                <i class="fas fa-wallet text-emerald me-1.5" style="color: #00A884;"></i> Keuangan & Informasi
                            </h6>
                            <small class="text-secondary">Kas RT, pembukuan, iuran, serta warta RW</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    {{-- Grup Keuangan RT --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Keuangan RT</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2 mb-3">
                        <a href="{{ route('pembayaran.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e7fceb; color: #00A884;">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pembayaran Iuran</strong>
                                <span class="text-secondary small d-block">Verifikasi setoran & tagihan iuran warga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('pengeluaran.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pengeluaran RT</strong>
                                <span class="text-secondary small d-block">Catat belanja operasional & bukti nota</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('kas.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Kas RT</strong>
                                <span class="text-secondary small d-block">Buku kas umum & riwayat mutasi saldo</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('laporan.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #f3e8ff; color: #9333ea;">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Laporan Keuangan RT</strong>
                                <span class="text-secondary small d-block">Rekapitulasi bulanan & laporan pertanggungjawaban</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>

                    {{-- Grup Informasi --}}
                    <div class="mb-2 px-1">
                        <span class="text-xs uppercase fw-bold tracking-wider text-secondary">Informasi</span>
                    </div>
                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('rt.informasi.pengumuman') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Pengumuman RW</strong>
                                <span class="text-secondary small d-block">Warta dan siaran resmi dari pengurus RW</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        <a href="{{ route('kegiatan.index', ['tingkat' => 'rw']) }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0e7ff; color: #4f46e5;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Agenda RW</strong>
                                <span class="text-secondary small d-block">Kalender kegiatan terkoordinasi tingkat RW</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
           BOTTOM SHEET MODAL: AKUN (Profil Ketua RT, Beralih Simulasi, Logout)
           ================================================================== --}}
        <div class="modal fade bottom-sheet-modal" id="bottomSheetRtAkun" tabindex="-1" aria-labelledby="bottomSheetRtAkunLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content border-0 p-3 p-sm-4 shadow-lg max-h-[75vh] overflow-y-auto rounded-t-3xl">
                    <div class="bottom-sheet-drag-handle"></div>
                    <div class="d-flex align-items-center gap-3 p-2.5 mb-3 rounded-3" style="background: var(--surface-2, #e7fceb); border: 1px solid var(--border);">
                        <img src="{{ $fotoUrl }}" alt="Avatar" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <strong class="d-block text-dark text-truncate small fw-bold">{{ $defaultName }}</strong>
                            @if(session()->has('super_admin_context'))
                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-full" style="font-size: 0.72rem;">Simulasi: Ketua RT {{ $authUser->getRtKode() ?? '' }}</span>
                            @else
                                <span class="badge badge-soft-primary px-2 py-0.5 rounded-full" style="font-size: 0.72rem;">Ketua RT {{ $authUser->getRtKode() ?? '01' }}</span>
                            @endif
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="bottom-sheet-list d-flex flex-column gap-2">
                        <a href="{{ route('profile.index') }}" class="bottom-sheet-item">
                            <div class="bottom-sheet-icon" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-user-gear"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark small fw-bold">Profil & Pengaturan</strong>
                                <span class="text-secondary small d-block">Ubah profil, kontak WhatsApp, & kata sandi</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>

                        {{-- Tombol Kembali ke Super Admin jika dalam Mode Simulasi --}}
                        @if(session()->has('super_admin_context'))
                            <form method="POST" action="{{ route('superadmin.switch-role') }}" class="m-0 w-100">
                                @csrf
                                <input type="hidden" name="mode" value="super_admin">
                                <button type="submit" class="bottom-sheet-item border-warning border-opacity-50 w-100 text-start" style="background: #fffbeb; color: #b45309;">
                                    <div class="bottom-sheet-icon" style="background: #fef3c7; color: #b45309;">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong class="d-block small fw-bold text-dark">Kembali ke Super Admin</strong>
                                        <span class="text-secondary small d-block">Akhiri simulasi peran Ketua RT</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </button>
                            </form>
                        @endif

                        {{-- Keluar (Logout) --}}
                        <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                            @csrf
                            <button type="submit" class="bottom-sheet-item border-danger border-opacity-25 w-100 text-start" style="background: #fef2f2; color: #dc2626;">
                                <div class="bottom-sheet-icon" style="background: #fee2e2; color: #dc2626;">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="d-block small fw-bold text-danger">Keluar (Logout)</strong>
                                    <span class="text-danger small opacity-75 d-block">Akhiri sesi di perangkat ini</span>
                                </div>
                                <i class="fas fa-chevron-right text-danger opacity-50 small"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ==========================================================================
       GLOBAL PANIC BUTTON EMERGENCY MODAL
       ========================================================================== -->
    <div class="modal fade" id="modalGlobalPanic" tabindex="-1" aria-labelledby="modalGlobalPanicLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form method="POST" action="{{ route('layanan.ronda.panic.store') }}">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold text-white" id="modalGlobalPanicLabel">
                            <i class="fas fa-triangle-exclamation fa-beat me-2"></i>PANGGILAN DARURAT (PANIC BUTTON)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4 px-4">
                        <div class="mb-3">
                            <div class="d-inline-flex p-3 rounded-circle bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-tower-broadcast fa-3x animate-pulse"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-danger mb-2">Peringatan Keadaan Darurat!</h5>
                        <p class="small text-secondary mb-3">
                            Menekan tombol ini akan mengirimkan sinyal bahaya darurat beserta identitas RT Anda ke seluruh pengurus RT, petugas ronda malam yang bertugas, dan warga sekitar.
                        </p>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-semibold">Deskripsi Kejadian <small class="text-muted">(Opsional / Cepat)</small></label>
                            <input type="text" name="deskripsi" class="form-control" placeholder="Contoh: Kebakaran, pencurian, kecelakaan, butuh ambulans">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4">
                            <i class="fas fa-bell me-1"></i>KIRIM SINYAL DARURAT
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init({ duration: 600, once: true });

        $(document).ready(function () {
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    },
                    responsive: true
                });
            }
        });

        // Mobile-First Sidebar Drawer Controller
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        let lastFocusedElement = null;

        function openSidebar() {
            if (!sidebar) return;
            lastFocusedElement = document.activeElement;
            sidebar.classList.add('show', 'is-open');
            if (backdrop) backdrop.classList.add('show', 'is-open');
            document.body.classList.add('sidebar-open');
            document.body.style.overflow = 'hidden';
            document.body.style.touchAction = 'none';
            const main = document.getElementById('mainContent');
            if (main) main.setAttribute('aria-hidden', 'true');
            // Focus on close button or first nav link for keyboard accessibility
            const firstFocusable = sidebar.querySelector('#closeSidebarBtn, .sidebar-nav-item');
            if (firstFocusable) firstFocusable.focus();
        }

        function closeSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('show', 'is-open');
            if (backdrop) backdrop.classList.remove('show', 'is-open');
            document.body.classList.remove('sidebar-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('touch-action');
            const main = document.getElementById('mainContent');
            if (main) main.removeAttribute('aria-hidden');
            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            } else if (toggleBtn) {
                toggleBtn.focus();
            }
        }

        function toggleSidebar() {
            if (!sidebar) return;
            if (sidebar.classList.contains('show') || sidebar.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);

        // Auto-close on menu selection in mobile drawer mode (< 1024px)
        if (sidebar) {
            sidebar.querySelectorAll('.sidebar-nav-item').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });

            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && (sidebar.classList.contains('show') || sidebar.classList.contains('is-open'))) {
                    closeSidebar();
                }
            });

            // Focus trap inside sidebar drawer when open
            sidebar.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab' || (!sidebar.classList.contains('show') && !sidebar.classList.contains('is-open')) || window.innerWidth >= 1024) return;
                const focusables = sidebar.querySelectorAll('a[href], button:not([disabled]), input, [tabindex]:not([tabindex="-1"])');
                if (!focusables.length) return;
                const first = focusables[0];
                const last = focusables[focusables.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            });
        }

        // Lightweight count-up animation for stats (0.8s - 1.2s, preserving exact server numbers)
        document.addEventListener('DOMContentLoaded', function () {
            const counters = document.querySelectorAll('.count-up');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target') || counter.innerText.replace(/\D/g, ''), 10);
                if (isNaN(target) || target <= 0) return;
                
                const duration = 900;
                const startTime = performance.now();
                
                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const val = Math.floor(easeOut * target);
                    counter.innerText = val.toLocaleString('id-ID');
                    
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                }
                requestAnimationFrame(update);
            });
        });

        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');

        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme;
            localStorage.setItem('risma-theme', theme);
            const metaTheme = document.getElementById('metaThemeColor');
            if (metaTheme) metaTheme.setAttribute('content', theme === 'dark' ? '#12201a' : '#147A45');
            if (themeToggleIcon) {
                themeToggleIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            }
            window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
        }

        window.addEventListener('load', function () {
            document.documentElement.classList.add('theme-ready');
        });

        setTheme(localStorage.getItem('risma-theme') || 'light');

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });
        }

        // =========================================================================
        // BAGIAN 3: FLOATING CENTERED TOAST NOTIFICATION STACK (PILL DESIGN)
        // =========================================================================
        function escapeHtml(str) {
            if (typeof str !== 'string') return str ?? '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function dismissToast(pill) {
            if (!pill || pill.dataset.dismissing) return;
            pill.dataset.dismissing = 'true';
            pill.style.animation = 'toastSlideOut 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            setTimeout(() => {
                if (pill.parentElement) {
                    pill.parentElement.removeChild(pill);
                }
            }, 260);
        }

        window.showToast = function(message, type = 'success', customTitle = null) {
            const stack = document.getElementById('toastStack');
            if (!stack) return;

            let title = customTitle;
            if (!title) {
                switch (type) {
                    case 'error':
                    case 'danger':
                        title = 'Gagal';
                        break;
                    case 'warning':
                        title = 'Peringatan';
                        break;
                    case 'info':
                        title = 'Informasi';
                        break;
                    default:
                        title = 'Berhasil';
                        break;
                }
            }

            const isError = (type === 'error' || type === 'danger');
            const duration = isError ? 5000 : 3500;
            const roleAttr = isError ? 'alert' : 'status';
            const ariaLive = isError ? 'assertive' : 'polite';

            let iconClass = 'fa-check';
            if (isError) iconClass = 'fa-times';
            else if (type === 'warning') iconClass = 'fa-exclamation';
            else if (type === 'info') iconClass = 'fa-info';

            const pill = document.createElement('div');
            pill.className = `toast-pill toast-pill--${type}`;
            pill.setAttribute('role', roleAttr);
            pill.setAttribute('aria-live', ariaLive);
            pill.setAttribute('data-no-motion', 'true');

            pill.innerHTML = `
                <div class="toast-pill__icon" aria-hidden="true">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="toast-pill__content">
                    <div class="toast-pill__title">${escapeHtml(title)}</div>
                    <div class="toast-pill__desc">${escapeHtml(message)}</div>
                </div>
                <button type="button" class="toast-pill__close" aria-label="Tutup notifikasi">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <div class="toast-pill__progress" aria-hidden="true">
                    <div class="toast-pill__progress-bar" style="animation-duration: ${duration}ms;"></div>
                </div>
            `;

            // Max 3 visible toasts, dismiss older
            while (stack.children.length >= 3) {
                dismissToast(stack.firstElementChild);
            }

            stack.appendChild(pill);

            let remainingTime = duration;
            let startTime = Date.now();
            let timerId = null;
            let isPaused = false;

            function startTimer() {
                startTime = Date.now();
                timerId = setTimeout(() => {
                    dismissToast(pill);
                }, remainingTime);
            }

            function pauseTimer() {
                if (isPaused) return;
                isPaused = true;
                clearTimeout(timerId);
                remainingTime -= (Date.now() - startTime);
                if (remainingTime < 300) remainingTime = 300;
                pill.classList.add('is-paused');
            }

            function resumeTimer() {
                if (!isPaused) return;
                isPaused = false;
                pill.classList.remove('is-paused');
                startTimer();
            }

            pill.addEventListener('mouseenter', pauseTimer);
            pill.addEventListener('mouseleave', resumeTimer);
            pill.addEventListener('touchstart', pauseTimer, { passive: true });
            pill.addEventListener('touchend', resumeTimer, { passive: true });

            const closeBtn = pill.querySelector('.toast-pill__close');
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dismissToast(pill);
                });
            }

            startTimer();
        };

        // Automatic flash message toasts from Laravel Session
        @if(session('success'))
            window.showToast(@json(session('success')), 'success');
        @endif
        @if(session('error'))
            window.showToast(@json(session('error')), 'error');
        @endif
        @if(session('warning'))
            window.showToast(@json(session('warning')), 'warning');
        @endif
        @if(session('info'))
            window.showToast(@json(session('info')), 'info');
        @endif

        // Safe graceful override of native browser window.alert
        window.alert = function (message) {
            Swal.fire({
                title: 'Informasi',
                text: message,
                icon: 'info',
                confirmButtonColor: '#147A45',
                confirmButtonText: 'Mengerti',
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0',
                    confirmButton: 'btn btn-emerald text-white rounded-pill px-4 py-2 font-weight-semibold'
                }
            });
        };

        // Universal data-confirm handler for buttons and forms
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-confirm]');
            if (!trigger) return;

            e.preventDefault();
            const message = trigger.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const title = trigger.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const btnText = trigger.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
            const isDanger = trigger.classList.contains('btn-danger') || trigger.classList.contains('btn-outline-danger') || trigger.classList.contains('text-danger') || /hapus|keluarkan|batalkan|tolak/i.test(message);
            const confirmColor = isDanger ? '#E65353' : '#147A45';
            const icon = isDanger ? 'warning' : 'question';

            Swal.fire({
                title: title,
                text: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#758078',
                confirmButtonText: btnText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0',
                    confirmButton: 'btn rounded-pill px-4 py-2 font-weight-semibold shadow-xs',
                    cancelButton: 'btn btn-light rounded-pill px-4 py-2 font-weight-semibold shadow-xs'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = trigger.closest('form');
                    if (form) {
                        form.submit();
                    } else if (trigger.tagName === 'A' && trigger.href) {
                        window.location.href = trigger.href;
                    }
                }
            });
        }, true);

        // Intercept forms with data-confirm
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form.hasAttribute('data-confirm') && !form.dataset.confirmed) {
                e.preventDefault();
                const message = form.getAttribute('data-confirm');
                const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
                const btnText = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
                const isDanger = /hapus|keluarkan|batalkan|tolak/i.test(message);

                Swal.fire({
                    title: title,
                    text: message,
                    icon: isDanger ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: isDanger ? '#E65353' : '#147A45',
                    cancelButtonColor: '#758078',
                    confirmButtonText: btnText,
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'btn rounded-pill px-4 py-2 font-weight-semibold shadow-xs',
                        cancelButton: 'btn btn-light rounded-pill px-4 py-2 font-weight-semibold shadow-xs'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            }
        });

        // Generic SweetAlert Delete confirmation helper
        function confirmDelete(url, name) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Data ' + name + ' akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E65353',
                cancelButtonColor: '#758078',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.style.display = 'none';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';

                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        // Modal Teleport & Stacking Context Safeguard (prevents black backdrop trapping)
        document.addEventListener('show.bs.modal', function (event) {
            const modal = event.target;
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            // Clean up any orphan backdrops before showing
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(bd => bd.remove());
        });

        document.addEventListener('shown.bs.modal', function (event) {
            const modal = event.target;
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach((bd, idx) => {
                if (idx > 0) {
                    bd.remove();
                } else {
                    bd.style.zIndex = 'var(--z-backdrop, 70)';
                }
            });
            if (modal) {
                modal.style.zIndex = 'var(--z-modal, 80)';
            }
        });

        document.addEventListener('hide.bs.modal', function () {
            // Safety timer in case Bootstrap transitionend is cancelled or interrupted
            setTimeout(function () {
                if (!document.querySelector('.modal.show')) {
                    document.querySelectorAll('.modal-backdrop').forEach(bd => bd.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');
                }
            }, 300);
        });

        document.addEventListener('hidden.bs.modal', function () {
            // Remove zombie backdrops and reset body state completely
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(bd => bd.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        });

        // Inisialisasi strategi Popper "fixed" dan boundary "viewport" untuk dropdown di dalam tabel
        function initTableDropdowns() {
            document.querySelectorAll('.table .dropdown-toggle, .table-responsive .dropdown-toggle, [data-bs-boundary="viewport"]').forEach(function (btn) {
                if (!btn.hasAttribute('data-bs-boundary')) {
                    btn.setAttribute('data-bs-boundary', 'viewport');
                }
                if (!btn.hasAttribute('data-bs-popper-config')) {
                    btn.setAttribute('data-bs-popper-config', '{"strategy":"fixed"}');
                }
            });
        }
        initTableDropdowns();
        document.addEventListener('show.bs.dropdown', function (e) {
            const toggle = e.target;
            if (toggle.closest('.table-responsive, table, .table, .card-table') || toggle.getAttribute('data-bs-boundary') === 'viewport') {
                toggle.setAttribute('data-bs-boundary', 'viewport');
                if (!toggle.getAttribute('data-bs-popper-config')) {
                    toggle.setAttribute('data-bs-popper-config', '{"strategy":"fixed"}');
                }
            }
        });
    </script>

    @if(Auth::check() && Auth::user()->isSuperAdmin())
        @php
            $modalRts = \App\Models\Rt::with('rw')->orderBy('kode_rt')->get();
            $modalRws = \App\Models\Rw::orderBy('kode_rw')->get();
        @endphp
        <!-- Modal Beralih Peran (Role Switcher) -->
        <div class="modal fade" id="modalSwitchRole" tabindex="-1" aria-labelledby="modalSwitchRoleLabel" aria-hidden="true" data-no-motion="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" data-no-motion="true">
                <div class="modal-content border-0 shadow-lg rounded-4" data-no-motion="true">
                    <div class="modal-header border-bottom py-3 px-4 rounded-top-4" data-no-motion="true">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 rounded-3 bg-warning text-dark flex-shrink-0">
                                <i class="fas fa-arrows-rotate"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalSwitchRoleLabel">Beralih Peran Sistem (Role Switching)</h5>
                                <small class="text-secondary">Simulasikan peran pengurus atau warga secara instan tanpa perlu logout</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4" data-no-motion="true">
                        <div class="alert alert-info border-0 rounded-3 small mb-4 d-flex align-items-center gap-2">
                            <i class="fas fa-circle-info fa-lg flex-shrink-0"></i>
                            <div>
                                <strong>Fitur Eksklusif Super Admin:</strong> Anda dapat menguji seluruh hak akses dan alur sistem dari sudut pandang Pengurus RW, Ketua RT di wilayah mana pun, atau Warga biasa.
                            </div>
                        </div>

                        <div class="row g-3">
                            {{-- Pilihan 1: Super Admin --}}
                            <div class="col-12 col-md-6">
                                <form method="POST" action="{{ route('superadmin.switch-role') }}" class="h-100">
                                    @csrf
                                    <input type="hidden" name="mode" value="super_admin">
                                    <div class="card h-100 border rounded-4 p-3 hover-lift d-flex flex-column justify-content-between {{ !Auth::user()->isSimulatingRole() ? 'border-2 border-warning bg-warning bg-opacity-10' : '' }}" data-no-motion="true">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill">ROLE DEFAULT</span>
                                                <i class="fas fa-crown text-warning fs-5"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Super Admin Asli</h6>
                                            <p class="small mb-3">Hak akses tak terbatas ke seluruh unit RT, master database, dan konfigurasi sistem.</p>
                                        </div>
                                        <button type="submit" class="btn btn-warning text-dark fw-bold w-100 rounded-3">
                                            <i class="fas fa-check-circle me-1"></i> Aktifkan Super Admin
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Pilihan 2: Ketua RW --}}
                            <div class="col-12 col-md-6">
                                <form method="POST" action="{{ route('superadmin.switch-role') }}" class="h-100">
                                    @csrf
                                    <input type="hidden" name="mode" value="rw">
                                    @if($modalRws->count() > 0)
                                        <input type="hidden" name="rw_id" value="{{ $modalRws->first()->id }}">
                                    @endif
                                    <div class="card h-100 border rounded-4 p-3 hover-lift d-flex flex-column justify-content-between {{ (session('super_admin_context.mode') ?? '') === 'rw' ? 'border-2 border-success bg-success bg-opacity-10' : '' }}" data-no-motion="true">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-success text-white fw-semibold px-2 py-1 rounded-pill">TINGKAT RW</span>
                                                <i class="fas fa-building text-success fs-5"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Ketua / Pengurus RW</h6>
                                            <p class="small mb-3">Memvalidasi persetujuan surat RW, audit keuangan antar-RT, dan monitoring pengumuman RW.</p>
                                        </div>
                                        <button type="submit" class="btn btn-success fw-bold w-100 rounded-3">
                                            <i class="fas fa-play me-1"></i> Simulasikan Pengurus RW
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Pilihan 3: Ketua RT (Dinamis RT 001 - RT 012...) --}}
                            <div class="col-12 col-md-6">
                                <form method="POST" action="{{ route('superadmin.switch-role') }}" class="h-100">
                                    @csrf
                                    <input type="hidden" name="mode" value="rt">
                                    <div class="card h-100 border rounded-4 p-3 hover-lift d-flex flex-column justify-content-between {{ (session('super_admin_context.mode') ?? '') === 'rt' ? 'border-2 border-primary bg-primary bg-opacity-10' : '' }}" data-no-motion="true">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-primary text-white fw-semibold px-2 py-1 rounded-pill">TINGKAT RT</span>
                                                <i class="fas fa-landmark text-primary fs-5"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Ketua RT (Pilih Unit RT)</h6>
                                            <p class="small mb-2">Pilih unit RT yang ingin Anda pimpin untuk verifikasi warga, kas RT, dan jadwal ronda:</p>
                                            <div class="mb-3">
                                                <select name="rt_id" class="form-select form-select-sm" required>
                                                    @foreach($modalRts as $mRt)
                                                        <option value="{{ $mRt->id }}" {{ (session('super_admin_context.rt_id') == $mRt->id) ? 'selected' : '' }}>
                                                             RT {{ $mRt->kode_rt }} - {{ $mRt->nama_rt }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary fw-bold w-100 rounded-3">
                                            <i class="fas fa-play me-1"></i> Simulasikan Ketua RT
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Pilihan 4: Warga (Dinamis Domisili RT) --}}
                            <div class="col-12 col-md-6">
                                <form method="POST" action="{{ route('superadmin.switch-role') }}" class="h-100">
                                    @csrf
                                    <input type="hidden" name="mode" value="warga">
                                    <div class="card h-100 border rounded-4 p-3 hover-lift d-flex flex-column justify-content-between {{ (session('super_admin_context.mode') ?? '') === 'warga' ? 'border-2 border-info bg-info bg-opacity-10' : '' }}" data-no-motion="true">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-info text-white fw-semibold px-2 py-1 rounded-pill">PORTAL WARGA</span>
                                                <i class="fas fa-users text-info fs-5"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Warga Biasa</h6>
                                            <p class="small mb-2">Simulasikan pengalaman warga mengajukan surat, bayar iuran, dan kirim aspirasi di RT:</p>
                                            <div class="mb-3">
                                                <select name="rt_id" class="form-select form-select-sm" required>
                                                    @foreach($modalRts as $mRt)
                                                        <option value="{{ $mRt->id }}" {{ (session('super_admin_context.rt_id') == $mRt->id) ? 'selected' : '' }}>
                                                            Warga di RT {{ $mRt->kode_rt }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-info text-white fw-bold w-100 rounded-3">
                                            <i class="fas fa-play me-1"></i> Simulasikan Warga
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4 rounded-bottom-4" data-no-motion="true">
                        <button type="button" class="btn btn-secondary px-4 rounded-3 w-100" data-bs-dismiss="modal" style="min-height: 48px; font-weight: 700;">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Stack for Modals outside main content to prevent z-index stacking context trapping --}}
    @stack('modals')

    @stack('scripts')
    <!-- SMART RW Motion Graphics Engine -->
    <script src="{{ asset('js/motion.js') }}?v={{ file_exists(public_path('js/motion.js')) ? filemtime(public_path('js/motion.js')) : '1.0' }}"></script>
    @include('partials.whatsapp-help')
</body>
</html>