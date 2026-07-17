<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RW SMART - Sistem Informasi RW</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('risma-theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link href="{{ asset('css/theme-dashboard.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Ornamental Decorations -->
    <div class="decoration lantern-top-right"><i class="fas fa-lantern"></i></div>
    <div class="decoration ketupat-bottom-right"><i class="fas fa-gem"></i></div>
    <div class="decoration stars star-1"><i class="fas fa-star"></i></div>
    <div class="decoration stars star-2"><i class="fas fa-star"></i></div>
    <div class="decoration stars star-3"><i class="fas fa-star"></i></div>
    <div class="eid-light-decor eid-decor-moon"><i class="fas fa-moon"></i></div>
    <div class="eid-light-decor eid-decor-mosque"><i class="fas fa-mosque"></i></div>
    <div class="eid-light-decor eid-decor-ketupat"><i class="fas fa-gem"></i></div>
    <div class="eid-light-decor eid-decor-star"><i class="fas fa-star-and-crescent"></i></div>
    
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="toggle-sidebar" id="toggleSidebarBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i> RW SMART
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    @php
                        $user = Auth::user();
                        $defaultName = $user ? $user->name : 'Guest';
                        $fotoUrl = 'https://ui-avatars.com/api/?background=3b82f6&color=fff&rounded=true&size=128&name=' . urlencode($defaultName);
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

                    <li class="nav-item">
                        <button type="button" class="theme-toggle" id="themeToggleBtn" title="Ganti mode tampilan" aria-label="Ganti mode tampilan">
                            <i class="fas fa-moon" id="themeToggleIcon"></i>
                        </button>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill position-absolute" style="top:0; right:0; transform: translate(40%, -40%); font-size: 0.65rem;">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 300px;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifikasi Terbaru</span>
                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifikasi.read-all') }}" class="m-0">
                                        @csrf
                                        <button class="btn btn-sm btn-link text-decoration-none">Tandai semua</button>
                                    </form>
                                @endif
                            </li>
                            @if($latestNotifs->count())
                                @foreach($latestNotifs as $notif)
                                    <li>
                                        <a href="{{ route('notifikasi.index') }}" class="dropdown-item {{ $notif->dibaca ? '' : 'bg-warning bg-opacity-10' }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="d-block">{{ $notif->judul }}</strong>
                                                    <span class="small text-secondary">{{ \Illuminate\Support\Str::limit($notif->pesan, 60) }}</span>
                                                </div>
                                                @if(!$notif->dibaca)
                                                    <span class="badge bg-danger">Baru</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="{{ route('notifikasi.index') }}">Lihat semua notifikasi</a></li>
                            @else
                                <li><div class="dropdown-item text-center text-secondary">Tidak ada notifikasi baru</div></li>
                            @endif
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="{{ $fotoUrl }}" alt="Foto Profil" class="topbar-profile-photo">
                            <span>{{ $defaultName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="fas fa-id-card me-2"></i> Profil Saya
                                </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-3 d-flex flex-column h-100">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center gap-3">
                    <div class="sidebar-logo">
                        <i class="fas fa-wallet fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="sidebar-title">KAS RW</h5>
                        <p class="sidebar-subtitle">Keuangan RW</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Utama</div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Transaksi</div>
                <nav class="nav flex-column">
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara') || Auth::user()->hasRole('warga')))
                        <a class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                            <i class="fas fa-hand-holding-usd"></i> Pembayaran
                        </a>
                    @endif
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}" href="{{ route('pengeluaran.index') }}">
                            <i class="fas fa-shopping-cart"></i> Pengeluaran
                        </a>
                    @endif
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('kas.*') ? 'active' : '' }}" href="{{ route('kas.index') }}">
                            <i class="fas fa-chart-line"></i> Kas RW
                        </a>
                    @endif
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Layanan Masyarakat</div>
                @php
                    $layananAktif = request()->routeIs(
                        'bank-sampah.*', 'layanan.umkm.*', 'layanan.surat.*', 'layanan.posyandu.*',
                        'layanan.ronda.*', 'kegiatan.*', 'aspirasi.*', 'kas.*', 'peta-wilayah.*'
                    );
                @endphp
                <nav class="nav flex-column">
                    <button class="nav-link service-menu-toggle {{ $layananAktif ? '' : 'collapsed' }} {{ $layananAktif ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#layananSubmenu" aria-expanded="{{ $layananAktif ? 'true' : 'false' }}" aria-controls="layananSubmenu">
                        <i class="fas fa-handshake-angle"></i> Layanan
                        <i class="fas fa-chevron-down service-chevron"></i>
                    </button>
                    <div class="collapse service-submenu {{ $layananAktif ? 'show' : '' }}" id="layananSubmenu">
                        <a class="nav-link {{ request()->routeIs('bank-sampah.*') ? 'active' : '' }}" href="{{ route('bank-sampah.index') }}"><i class="fas fa-recycle"></i> Bank Sampah</a>
                        <a class="nav-link {{ request()->routeIs('layanan.umkm.*') ? 'active' : '' }}" href="{{ route('layanan.umkm.index') }}"><i class="fas fa-store"></i> UMKM</a>
                        <a class="nav-link {{ request()->routeIs('layanan.surat.*') ? 'active' : '' }}" href="{{ route('layanan.surat.index') }}"><i class="fas fa-envelope-open-text"></i> Surat Menyurat</a>
                        <a class="nav-link {{ request()->routeIs('layanan.posyandu.*') ? 'active' : '' }}" href="{{ route('layanan.posyandu.index') }}"><i class="fas fa-baby"></i> Posyandu</a>
                        <a class="nav-link {{ request()->routeIs('layanan.ronda.*') ? 'active' : '' }}" href="{{ route('layanan.ronda.index') }}"><i class="fas fa-shield-halved"></i> Keamanan / Ronda</a>
                        <a class="nav-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" href="{{ route('kegiatan.index') }}"><i class="fas fa-calendar-check"></i> Kegiatan</a>
                        <a class="nav-link {{ request()->routeIs('peta-wilayah.*') ? 'active' : '' }}" href="{{ route('peta-wilayah.index') }}"><i class="fas fa-map-location-dot"></i> Peta Wilayah</a>
                        <a class="nav-link {{ request()->routeIs('aspirasi.*') ? 'active' : '' }}" href="{{ route('aspirasi.index') }}"><i class="fas fa-comment-dots"></i> Aspirasi</a>
                        <a class="nav-link {{ request()->routeIs('kas.*') ? 'active' : '' }}" href="{{ route('kas.index') }}"><i class="fas fa-wallet"></i> Kas</a>
                    </div>
                </nav>
            </div>

            <!-- BAGIAN YANG DI-RECOVERY DARI ERROR -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Master Data</div>
                <nav class="nav flex-column">
                    @if(Auth::check() && (Auth::user()->isAdminRw() || Auth::user()->isAdminRt()))
                        <a class="nav-link {{ request()->routeIs('warga.*') ? 'active' : '' }}" href="{{ route('warga.index') }}">
                            <i class="fas fa-users"></i> Data Warga
                        </a>
                    @endif
                    @if(Auth::check() && Auth::user()->isAdminRw())
                        <a class="nav-link {{ request()->routeIs('rts.*') ? 'active' : '' }}" href="{{ route('rts.index') }}">
                            <i class="fas fa-sitemap"></i> Kelola RT
                        </a>
                    @endif
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('iuran.*') ? 'active' : '' }}" href="{{ route('iuran.index') }}">
                            <i class="fas fa-money-bill-wave"></i> Kelola Iuran
                        </a>
                    @endif
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Laporan</div>
                <nav class="nav flex-column">
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                            <i class="fas fa-file-alt"></i> Laporan
                        </a>
                    @endif
                </nav>
            </div>

            <div class="sidebar-section mt-auto">
                <div class="sidebar-section-title">Akun</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="{{ route('profile.index') }}">
                        <i class="fas fa-user-circle"></i> Profil Saya
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')

        <style>
            html[data-bs-theme="light"] .welcome-header h1,
            html[data-bs-theme="light"] h1,
            html[data-bs-theme="light"] h2,
            html[data-bs-theme="light"] h3,
            html[data-bs-theme="light"] h4,
            html[data-bs-theme="light"] h5,
            html[data-bs-theme="light"] h6 {
                color: #1f2933 !important;
            }

            html[data-bs-theme="light"] .glass-card,
            html[data-bs-theme="light"] .stat-card,
            html[data-bs-theme="light"] .welcome-header,
            html[data-bs-theme="light"] .info-item,
            html[data-bs-theme="light"] .warga-card {
                background: rgba(248, 250, 244, 0.86) !important;
                border-color: rgba(125, 138, 113, 0.22) !important;
                box-shadow: 0 10px 28px rgba(57, 73, 56, 0.08) !important;
            }

            html[data-bs-theme="light"] .card-header-custom {
                background: rgba(232, 238, 224, 0.78) !important;
                color: #2f3a31 !important;
                border-bottom-color: rgba(125, 138, 113, 0.2) !important;
            }

            html[data-bs-theme="light"] .table-dark,
            html[data-bs-theme="light"] .table-dark th,
            html[data-bs-theme="light"] .table-dark td {
                color: #1f2933 !important;
            }

            html[data-bs-theme="light"] .table,
            html[data-bs-theme="light"] .table-dark {
                --bs-table-color: #1f2933;
                --bs-table-bg: transparent;
                --bs-table-border-color: rgba(125, 138, 113, 0.16);
                --bs-table-striped-bg: rgba(226, 233, 218, 0.36);
                --bs-table-striped-color: #1f2933;
                --bs-table-hover-bg: rgba(226, 233, 218, 0.58);
                --bs-table-hover-color: #1f2933;
                background-color: transparent !important;
                color: #1f2933 !important;
            }

            html[data-bs-theme="light"] .table thead th,
            html[data-bs-theme="light"] .table-dark thead th {
                background-color: rgba(226, 233, 218, 0.86) !important;
                color: #384336 !important;
                border-bottom-color: rgba(125, 138, 113, 0.24) !important;
            }

            html[data-bs-theme="light"] .table tbody,
            html[data-bs-theme="light"] .table tr,
            html[data-bs-theme="light"] .table td,
            html[data-bs-theme="light"] .table-dark tbody,
            html[data-bs-theme="light"] .table-dark tr,
            html[data-bs-theme="light"] .table-dark td {
                background-color: transparent !important;
                color: #1f2933 !important;
                border-color: rgba(125, 138, 113, 0.16) !important;
            }

            html[data-bs-theme="light"] .table-hover tbody tr:hover,
            html[data-bs-theme="light"] .table-dark.table-hover tbody tr:hover,
            html[data-bs-theme="light"] .table-hover tbody tr:hover > * {
                background-color: rgba(226, 233, 218, 0.58) !important;
                color: #1f2933 !important;
            }

            html[data-bs-theme="light"] .text-muted,
            html[data-bs-theme="light"] .small.text-muted {
                color: #687466 !important;
            }

            html[data-bs-theme="light"] .badge.bg-warning.text-dark {
                color: #3b2f08 !important;
            }

            html[data-bs-theme="light"] .badge {
                text-shadow: none !important;
            }
        </style>
        
        <footer>
            <p>&copy; {{ date('Y') }} RW SMART - Sistem Informasi RW | Dibangun dengan <i class="fas fa-heart text-danger"></i> untuk warga</p>
        </footer>
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
        AOS.init({
            duration: 800,
            once: true
        });

        $(document).ready(function () {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                responsive: true
            });

        });

        function confirmDelete(url, name) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data " + name + " akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                buttonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#f1f5f9'
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

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Toggle sidebar for mobile
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');

        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('risma-theme', theme);

            if (themeToggleIcon) {
                themeToggleIcon.className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
            }

            window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
        }

        setTheme(localStorage.getItem('risma-theme') || 'dark');

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    @stack('scripts')
    @include('partials.whatsapp-help')
</body>
</html>
