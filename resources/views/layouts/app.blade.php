<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RW SMART - Modern SaaS Dashboard')</title>
    
    <script>
        (function () {
            const savedTheme = localStorage.getItem('risma-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            document.documentElement.setAttribute('data-theme', savedTheme);
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

    <!-- RW SMART Modern SaaS Theme (Donezo Style) -->
    <link href="{{ asset('css/theme-dashboard.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ==========================================================================
       SIDEBAR NAVIGATION (DONEZO CLEAN WHITE STYLE)
       ========================================================================== -->
    <aside class="sidebar" id="sidebar">
        @php
            $authUser = Auth::user();
            $roleLabel = 'PENGURUS RW';
            $roleSub = 'Pengawas & Koordinator';
            $brandIcon = 'fa-building';
            if ($authUser && $authUser->isAdminRt()) {
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
                <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="RW SMART Logo" width="34" height="34" class="flex-shrink-0">
                <div class="sidebar-brand-text">
                    <span class="sidebar-brand-title">RW SMART</span>
                    <span class="sidebar-brand-badge">{{ $roleLabel }}</span>
                </div>
            </a>
            <button class="d-lg-none toggle-sidebar-btn" id="closeSidebarBtn" title="Tutup Menu">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Items -->
        <div class="sidebar-content">
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
                    <a class="sidebar-nav-item {{ request()->routeIs('kartu-keluarga.*', 'warga.index', 'warga.show', 'warga.create', 'warga.edit') ? 'active' : '' }}" href="{{ route('kartu-keluarga.index') }}">
                        <i class="fas fa-id-card"></i>
                        <span>Data Keluarga & Warga</span>
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
                <span class="badge badge-soft-primary px-2 py-1">RW 01 Smart v2.5</span>
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
        <div class="d-flex align-items-center gap-3">
            <button class="toggle-sidebar-btn" id="toggleSidebarBtn" title="Buka/Tutup Menu" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="navbar-page-indicator d-none d-sm-flex">
                <span class="navbar-page-title">@yield('title', 'Sistem Informasi RW SMART')</span>
                <span class="navbar-page-subtitle">Wilayah RW 01 &bull; Kelurahan Digital</span>
            </div>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
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
                <span class="d-none d-md-inline">DARURAT</span>
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
                <div class="dropdown-menu dropdown-menu-end p-2 border-0 shadow-lg" style="min-width: 320px; border-radius: 16px;">
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
                    <div class="topbar-user-info d-none d-md-flex">
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
    <main class="main-content" id="mainContent">
        <div class="page-container">
            {{-- Flash Session Alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between mb-4 shadow-xs" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-lg me-2 text-success"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center justify-content-between mb-4 shadow-xs" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle fa-lg me-2 text-danger"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Yield Page Content --}}
            @yield('content')
        </div>

        <footer>
            <p class="mb-0">&copy; {{ date('Y') }} <strong>RW SMART</strong> &bull; Sistem Informasi Rukun Warga & Pelayanan Masyarakat Digital</p>
        </footer>
    </main>

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

        // Sidebar Drawer toggle for mobile & desktop
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            if (backdrop) backdrop.classList.toggle('show');
        }

        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if (backdrop) backdrop.addEventListener('click', toggleSidebar);

        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');

        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('risma-theme', theme);
            if (themeToggleIcon) {
                themeToggleIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            }
            window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
        }

        setTheme(localStorage.getItem('risma-theme') || 'light');

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });
        }

        // =========================================================================
        // MODERN TOAST & CONFIRMATION MODALS (REPLACING BROWSER ALERTS/CONFIRMS)
        // =========================================================================
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        window.showToast = function(message, type = 'success') {
            Toast.fire({
                icon: type,
                title: message
            });
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
    </script>

    @stack('scripts')
    @include('partials.whatsapp-help')
</body>
</html>