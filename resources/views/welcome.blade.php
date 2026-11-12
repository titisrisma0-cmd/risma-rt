<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RW SMART — Modern Community & Neighborhood Platform</title>

    <script>
        (function () {
            const savedTheme = localStorage.getItem('risma-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link href="{{ asset('css/theme-welcome.css') }}" rel="stylesheet">
</head>
<body>

    <!-- ==========================================================================
       1. TOP NAVBAR
       ========================================================================== -->
    <header class="landing-nav">
        <div class="nav-container">
            <a href="{{ url('/') }}" class="brand-link">
                <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="RW SMART Logo" width="38" height="38">
                <span class="brand-title">RW <span>SMART</span></span>
            </a>

            <ul class="nav-menu">
                <li><a href="#fitur">Fitur Utama</a></li>
                <li><a href="#layanan">Layanan Warga</a></li>
                <li><a href="#manfaat">Manfaat Role</a></li>
                <li><a href="#cara-kerja">Cara Kerja</a></li>
                <li><a href="#keamanan">Keamanan</a></li>
            </ul>

            <div class="nav-actions">
                <button type="button" class="theme-toggle-pill" id="themeToggleBtn" title="Ganti mode tampilan" aria-label="Toggle Theme">
                    <i class="fas fa-sun" id="themeToggleIcon" aria-hidden="true"></i>
                </button>

                @if(Auth::check())
                    <a href="{{ route('dashboard') }}" class="btn-saas-primary">
                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                        <span>Buka Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-saas-outline">Masuk</a>
                    <a href="{{ route('login') }}#register" class="btn-saas-primary">
                        <span>Daftar Warga</span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- ==========================================================================
       2. HERO SECTION
       ========================================================================== -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="badge-gold">TERPERCAYA</span>
                    <span>Platform Digital Rukun Warga & Lingkungan Modern</span>
                </div>

                <h1 class="hero-title">
                    Bangun Lingkungan Lebih 
                    <span class="highlight-green">
                        Terhubung
                        <svg class="highlight-underline-svg" viewBox="0 0 200 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M3 9C55 2.5 145 2.5 197 9" />
                        </svg>
                    </span>, Aman, dan Sejahtera.
                </h1>

                <p class="hero-subtitle">
                    RW SMART menghadirkan kemudahan pengelolaan warga, administrasi kependudukan, transparansi keuangan kas, keamanan ronda malam, serta pelayanan publik dalam satu platform digital yang modern dan inklusif.
                </p>

                <div class="hero-cta-group">
                    <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" class="btn-saas-primary">
                        <i class="fas fa-rocket" aria-hidden="true"></i>
                        <span>Mulai Sekarang</span>
                    </a>
                    <a href="#fitur" class="btn-saas-outline">
                        <i class="fas fa-compass" aria-hidden="true"></i>
                        <span>Jelajahi Fitur</span>
                    </a>
                </div>

                <div class="hero-trust-bar">
                    <div class="trust-item">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                        <span>Isolasi Data Aman Antar RT</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
                        <span>100% Kas Terbuka & Tervalidasi</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-bolt" aria-hidden="true"></i>
                        <span>Panic Button Darurat 24/7</span>
                    </div>
                </div>
            </div>

            <!-- Hero Vector Illustration Showcase (Panel Mint + Balai RW 01 + 4 Rumah RT + Flow + Pulse) -->
            <div class="hero-visual-wrapper">
                <div class="hero-svg-frame">
                    <!-- Top Floating Badge (bob jeda 0s) -->
                    <div class="floating-badge top-badge">
                        <div class="floating-badge-icon">
                            <i class="fas fa-file-circle-check" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="floating-badge-title">Kas RT & RW Terbuka</div>
                            <div class="floating-badge-sub">Transparan & diaudit real-time</div>
                        </div>
                    </div>

                    <!-- Modern Vector SVG: Balai RW 01 di tengah, 4 rumah di sudut, garis putus-putus flow, titik pulse -->
                    <svg class="hero-illustration-svg" viewBox="0 0 600 460" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Ilustrasi Komunitas Terhubung Balai RW 01 dan Rukun Tetangga">
                        <defs>
                            <linearGradient id="balaiRoofGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#0e7a4b" />
                                <stop offset="100%" stop-color="#0a5c38" />
                            </linearGradient>
                            <linearGradient id="sunGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#fcd34d" />
                                <stop offset="100%" stop-color="#f4a62a" />
                            </linearGradient>
                            <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                                <feGaussianBlur stdDeviation="8" result="blur" />
                                <feComposite in="SourceGraphic" in2="blur" operator="over" />
                            </filter>
                        </defs>

                        <!-- Matahari Kuning -->
                        <g transform="translate(480, 55)">
                            <circle cx="28" cy="28" r="42" fill="#fdf0d8" opacity="0.6" />
                            <circle cx="28" cy="28" r="28" fill="url(#sunGrad)" filter="url(#softGlow)" />
                        </g>

                        <!-- Awan Bergeser Pelan -->
                        <g class="cloud-drift" opacity="0.88">
                            <path d="M70 75 C70 65 78 57 88 57 C92 57 95 58 98 60 C101 50 110 43 121 43 C134 43 145 53 146 66 C149 65 152 64 156 64 C167 64 176 73 176 84 C176 95 167 104 156 104 L88 104 C78 104 70 96 70 86 Z" fill="#ffffff" />
                            <path d="M360 48 C360 40 366 34 374 34 C377 34 380 35 382 36 C385 28 392 23 400 23 C411 23 419 31 420 41 C422 40 425 40 428 40 C437 40 444 47 444 56 C444 65 437 72 428 72 L374 72 C366 72 360 66 360 58 Z" fill="#ffffff" opacity="0.75" />
                        </g>

                        <!-- Garis Putus-Putus Hijau Mengalir dari tiap Rumah ke Balai RW 01 (class="flow") -->
                        <!-- Garis RT 01 (Kiri Atas) -> Balai -->
                        <path d="M 145 140 Q 210 180 260 215" class="flow-line flow" />
                        <!-- Garis RT 02 (Kanan Atas) -> Balai -->
                        <path d="M 455 140 Q 390 180 340 215" class="flow-line flow" />
                        <!-- Garis RT 03 (Kiri Bawah) -> Balai -->
                        <path d="M 145 340 Q 210 300 260 265" class="flow-line flow" />
                        <!-- Garis RT 04 (Kanan Bawah) -> Balai -->
                        <path d="M 455 340 Q 390 300 340 265" class="flow-line flow" />

                        <!-- ================= BALAI RW 01 (DI TENGAH) ================= -->
                        <g transform="translate(225, 175)">
                            <!-- Bayangan Balai -->
                            <ellipse cx="75" cy="130" rx="90" ry="14" fill="#0f2a1d" opacity="0.08" />

                            <!-- Lantai / Podium Balai -->
                            <rect x="10" y="100" width="130" height="18" rx="6" fill="#ffffff" stroke="#dbe9e0" stroke-width="2" />
                            <rect x="0" y="112" width="150" height="12" rx="4" fill="#e3f4ea" stroke="#dbe9e0" stroke-width="1.5" />

                            <!-- Pilar Balai -->
                            <rect x="24" y="52" width="10" height="50" rx="3" fill="#ffffff" stroke="#dbe9e0" stroke-width="2" />
                            <rect x="52" y="52" width="9" height="50" rx="3" fill="#ffffff" stroke="#dbe9e0" stroke-width="2" />
                            <rect x="89" y="52" width="9" height="50" rx="3" fill="#ffffff" stroke="#dbe9e0" stroke-width="2" />
                            <rect x="116" y="52" width="10" height="50" rx="3" fill="#ffffff" stroke="#dbe9e0" stroke-width="2" />

                            <!-- Pintu Terbuka Balai & Partisi Kaca -->
                            <rect x="63" y="65" width="24" height="37" rx="3" fill="#0f2a1d" opacity="0.12" />

                            <!-- Atap Joglo / Limasan Balai RW 01 -->
                            <!-- Tingkat Bawah Atap -->
                            <polygon points="10,54 75,24 140,54" fill="#0a5c38" />
                            <polygon points="2,56 75,22 148,56 142,60 75,28 8,60" fill="url(#balaiRoofGrad)" />
                            <!-- Tingkat Atas Limasan -->
                            <polygon points="36,26 75,4 114,26" fill="url(#balaiRoofGrad)" />
                            <!-- Puncak Mahkota -->
                            <rect x="72" y="-1" width="6" height="6" rx="2" fill="#f4a62a" />

                            <!-- Tiang Bendera Merah Putih di Kiri Balai -->
                            <line x1="-12" y1="20" x2="-12" y2="120" stroke="#5f7468" stroke-width="2.5" stroke-linecap="round" />
                            <polygon points="-12,24 10,31 -12,38" fill="#ffffff" />
                            <polygon points="-12,24 10,31 -12,31" fill="#e5483a" />

                            <!-- Papan Nama "BALAI RW 01" -->
                            <rect x="36" y="38" width="78" height="18" rx="5" fill="#ffffff" stroke="#0e7a4b" stroke-width="1.5" />
                            <text x="75" y="50" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#0e7a4b" text-anchor="middle" letter-spacing="0.5">BALAI RW 01</text>
                        </g>

                        <!-- ================= EMPAT RUMAH DI SUDUT ================= -->

                        <!-- 1. Rumah RT 01 (Kiri Atas) -->
                        <g transform="translate(85, 80)">
                            <ellipse cx="40" cy="74" rx="42" ry="8" fill="#0f2a1d" opacity="0.06" />
                            <!-- Dinding -->
                            <rect x="15" y="36" width="50" height="34" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1.5" />
                            <!-- Atap Pelana -->
                            <polygon points="8,38 40,14 72,38" fill="#f4a62a" stroke="#d97706" stroke-width="1.5" />
                            <!-- Jendela -->
                            <rect x="22" y="44" width="12" height="12" rx="2" fill="#e3f4ea" stroke="#0e7a4b" stroke-width="1" />
                            <!-- Pintu -->
                            <rect x="42" y="46" width="14" height="24" rx="2" fill="#4b6355" />
                            <!-- Titik Berdenyut di Pintu Rumah (class pulse) -->
                            <circle cx="49" cy="58" r="9" fill="#0e7a4b" class="pulse-circle pulse" />
                            <circle cx="49" cy="58" r="3.5" fill="#0e7a4b" />
                            <!-- Label RT -->
                            <rect x="24" y="74" width="32" height="14" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1" />
                            <text x="40" y="84" font-family="'Plus Jakarta Sans', sans-serif" font-size="8" font-weight="700" fill="#0f2a1d" text-anchor="middle">RT 01</text>
                        </g>

                        <!-- 2. Rumah RT 02 (Kanan Atas) -->
                        <g transform="translate(435, 80)">
                            <ellipse cx="40" cy="74" rx="42" ry="8" fill="#0f2a1d" opacity="0.06" />
                            <!-- Dinding -->
                            <rect x="15" y="36" width="50" height="34" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1.5" />
                            <!-- Atap Pelana -->
                            <polygon points="8,38 40,14 72,38" fill="#0e7a4b" stroke="#0a5c38" stroke-width="1.5" />
                            <!-- Jendela -->
                            <rect x="46" y="44" width="12" height="12" rx="2" fill="#e3f4ea" stroke="#0e7a4b" stroke-width="1" />
                            <!-- Pintu -->
                            <rect x="24" y="46" width="14" height="24" rx="2" fill="#4b6355" />
                            <!-- Titik Berdenyut di Pintu Rumah (class pulse) -->
                            <circle cx="31" cy="58" r="9" fill="#0e7a4b" class="pulse-circle pulse" />
                            <circle cx="31" cy="58" r="3.5" fill="#0e7a4b" />
                            <!-- Label RT -->
                            <rect x="24" y="74" width="32" height="14" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1" />
                            <text x="40" y="84" font-family="'Plus Jakarta Sans', sans-serif" font-size="8" font-weight="700" fill="#0f2a1d" text-anchor="middle">RT 02</text>
                        </g>

                        <!-- 3. Rumah RT 03 (Kiri Bawah) -->
                        <g transform="translate(85, 290)">
                            <ellipse cx="40" cy="74" rx="42" ry="8" fill="#0f2a1d" opacity="0.06" />
                            <!-- Dinding -->
                            <rect x="15" y="36" width="50" height="34" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1.5" />
                            <!-- Atap Pelana -->
                            <polygon points="8,38 40,14 72,38" fill="#2a6fdb" stroke="#1d4ed8" stroke-width="1.5" />
                            <!-- Jendela -->
                            <rect x="22" y="44" width="12" height="12" rx="2" fill="#eaf1fc" stroke="#2a6fdb" stroke-width="1" />
                            <!-- Pintu -->
                            <rect x="42" y="46" width="14" height="24" rx="2" fill="#4b6355" />
                            <!-- Titik Berdenyut di Pintu Rumah (class pulse) -->
                            <circle cx="49" cy="58" r="9" fill="#0e7a4b" class="pulse-circle pulse" />
                            <circle cx="49" cy="58" r="3.5" fill="#0e7a4b" />
                            <!-- Label RT -->
                            <rect x="24" y="74" width="32" height="14" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1" />
                            <text x="40" y="84" font-family="'Plus Jakarta Sans', sans-serif" font-size="8" font-weight="700" fill="#0f2a1d" text-anchor="middle">RT 03</text>
                        </g>

                        <!-- 4. Rumah RT 04 (Kanan Bawah) -->
                        <g transform="translate(435, 290)">
                            <ellipse cx="40" cy="74" rx="42" ry="8" fill="#0f2a1d" opacity="0.06" />
                            <!-- Dinding -->
                            <rect x="15" y="36" width="50" height="34" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1.5" />
                            <!-- Atap Pelana -->
                            <polygon points="8,38 40,14 72,38" fill="#e5483a" stroke="#b91c1c" stroke-width="1.5" />
                            <!-- Jendela -->
                            <rect x="46" y="44" width="12" height="12" rx="2" fill="#fdeeee" stroke="#e5483a" stroke-width="1" />
                            <!-- Pintu -->
                            <rect x="24" y="46" width="14" height="24" rx="2" fill="#4b6355" />
                            <!-- Titik Berdenyut di Pintu Rumah (class pulse) -->
                            <circle cx="31" cy="58" r="9" fill="#0e7a4b" class="pulse-circle pulse" />
                            <circle cx="31" cy="58" r="3.5" fill="#0e7a4b" />
                            <!-- Label RT -->
                            <rect x="24" y="74" width="32" height="14" rx="4" fill="#ffffff" stroke="#dbe9e0" stroke-width="1" />
                            <text x="40" y="84" font-family="'Plus Jakarta Sans', sans-serif" font-size="8" font-weight="700" fill="#0f2a1d" text-anchor="middle">RT 04</text>
                        </g>
                    </svg>

                    <!-- Bottom Floating Badge (bob jeda 1.5s berbeda) -->
                    <div class="floating-badge bottom-badge">
                        <div class="floating-badge-icon gold">
                            <i class="fas fa-shield-halved" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="floating-badge-title">Siskamling & Patroli Malam</div>
                            <div class="floating-badge-sub">Jadwal & absensi digital aktif</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       3. STATISTIK 4 KOLOM (1 KARTU PUTIH DENGAN IKON DI KOTAK SOFT)
       ========================================================================== -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stats-grid-card">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-building-flag" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="stat-number count-up" data-target="{{ $statRt ?? 8 }}">{{ $statRt ?? 8 }}</div>
                        <div class="stat-label">Rukun Tetangga (RT) Terdaftar</div>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-id-card" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="stat-number count-up" data-target="{{ $statKk ?? 0 }}">{{ $statKk ?? 0 }}</div>
                        <div class="stat-label">Kartu Keluarga (KK) Tervalidasi</div>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="stat-number count-up" data-target="{{ $statWarga ?? 0 }}">{{ $statWarga ?? 0 }}</div>
                        <div class="stat-label">Warga Terdata di Sistem</div>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-store" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="stat-number count-up" data-target="{{ $statUmkm ?? 0 }}">{{ $statUmkm ?? 0 }}</div>
                        <div class="stat-label">UMKM Warga Berkembang</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       4. FITUR UTAMA RW SMART (SECTION 3)
       ========================================================================== -->
    <section class="section-wrapper" id="fitur">
        <div class="section-container">
            <div class="section-header-center">
                <span class="section-eyebrow">Fitur Unggulan</span>
                <h2 class="section-title">Semua Kebutuhan Lingkungan dalam Satu Dasbor Terpadu</h2>
                <p class="section-subtitle">Dirancang khusus dengan standar arsitektur modern untuk memudahkan koordinasi antara Pengurus RW, Ketua RT, dan seluruh warga.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-card-icon">
                        <i class="fas fa-users-viewfinder" aria-hidden="true"></i>
                    </div>
                    <h3 class="feature-card-title">Kependudukan & Keluarga</h3>
                    <p class="feature-card-desc">Manajemen data Kartu Keluarga, anggota keluarga, mutasi pindah/masuk, dan verifikasi status warga dengan keamanan tingkat data tinggi.</p>
                    <div class="feature-pill-list">
                        <span class="feature-pill">Data KK</span>
                        <span class="feature-pill">Mutasi Warga</span>
                        <span class="feature-pill">Demografi RT</span>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">
                        <i class="fas fa-wallet" aria-hidden="true"></i>
                    </div>
                    <h3 class="feature-card-title">Keuangan & Kas Transparan</h3>
                    <p class="feature-card-desc">Pencatatan iuran wajib dan sukarela, tracking pengeluaran berkuitansi, serta laporan saldo kas bulanan yang dapat diaudit secara akuntabel.</p>
                    <div class="feature-pill-list">
                        <span class="feature-pill">Iuran Online</span>
                        <span class="feature-pill">Bukti Pembayaran</span>
                        <span class="feature-pill">Arsip Laporan Kas</span>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    </div>
                    <h3 class="feature-card-title">Keamanan & Ronda Digital</h3>
                    <p class="feature-card-desc">Kalender jadwal ronda interaktif, sistem absensi swafoto/presensi, permohonan izin tukar jadwal, pengingat grup otomatis, dan siaga tombol darurat.</p>
                    <div class="feature-pill-list">
                        <span class="feature-pill">Kalender Siskamling</span>
                        <span class="feature-pill">Presensi Warga</span>
                        <span class="feature-pill">Notifikasi Pengingat</span>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">
                        <i class="fas fa-file-signature" aria-hidden="true"></i>
                    </div>
                    <h3 class="feature-card-title">Pelayanan Surat Pengantar</h3>
                    <p class="feature-card-desc">Pengajuan surat pengantar warga secara digital dengan alur verifikasi dua arah: persetujuan Ketua RT dilanjutkan ke Pengurus RW secara instan.</p>
                    <div class="feature-pill-list">
                        <span class="feature-pill">Verifikasi RT</span>
                        <span class="feature-pill">Validasi RW</span>
                        <span class="feature-pill">QR Digital Sign</span>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">
                        <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                    </div>
                    <h3 class="feature-card-title">Panic Button Darurat</h3>
                    <p class="feature-card-desc">Tombol darurat 1-klik untuk situasi medis, kebakaran, atau gangguan keamanan. Laporan langsung dikirim ke Ketua RT dan petugas ronda yang bertugas.</p>
                    <div class="feature-pill-list">
                        <span class="feature-pill">Siaga 24 Jam</span>
                        <span class="feature-pill">Lokasi Kejadian</span>
                        <span class="feature-pill">Status Penanganan</span>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">
                        <i class="fas fa-bullhorn" aria-hidden="true"></i>
                    </div>
                    <h3 class="feature-card-title">Aspirasi & Pengumuman</h3>
                    <p class="feature-card-desc">Saluran komunikasi dua arah bagi warga untuk menyampaikan masukan, kritik membangun, serta menerima pengumuman resmi wilayah tanpa hoaks.</p>
                    <div class="feature-pill-list">
                        <span class="feature-pill">Pengumuman Resmi</span>
                        <span class="feature-pill">Komentar Warga</span>
                        <span class="feature-pill">Moderasi Pengurus</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       5. LAYANAN MASYARAKAT (SECTION 4)
       ========================================================================== -->
    <section class="section-wrapper" id="layanan" style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
        <div class="section-container">
            <div class="section-header-center">
                <span class="section-eyebrow">Pemberdayaan Warga</span>
                <h2 class="section-title">Layanan Komunitas untuk Lingkungan Sehat & Produktif</h2>
                <p class="section-subtitle">Mendukung inisiatif ekonomi warga, kesehatan ibu dan anak, serta kepedulian lingkungan yang berkelanjutan.</p>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <span class="service-badge-tag">LINGKUNGAN HIJAU</span>
                    <h4 class="service-title">Bank Sampah Digital</h4>
                    <p class="service-desc">Warga dapat menyetorkan sampah daur ulang, menabung saldo sampah, dan mencairkannya secara transparan untuk mendukung lingkungan bersih.</p>
                </div>

                <div class="service-card">
                    <span class="service-badge-tag">KESEHATAN BALITA</span>
                    <h4 class="service-title">Posyandu & Tumbuh Kembang</h4>
                    <p class="service-desc">Jadwal posyandu bulanan, rekap pemeriksaan berat/tinggi badan balita, dan pencatatan imunisasi yang tersimpan rapi dalam buku riwayat digital.</p>
                </div>

                <div class="service-card">
                    <span class="service-badge-tag">EKONOMI WARGA</span>
                    <h4 class="service-title">Katalog UMKM Warga</h4>
                    <p class="service-desc">Etalase promosi produk olahan kuliner, jasa, dan kerajinan warga sekitar untuk mendorong perputaran ekonomi lokal di lingkungan rukun tetangga.</p>
                </div>

                <div class="service-card">
                    <span class="service-badge-tag">AGENDA BERSAMA</span>
                    <h4 class="service-title">Agenda & Kerja Bakti</h4>
                    <p class="service-desc">Publikasi jadwal kegiatan gotong royong, peringatan hari nasional, serta konfirmasi kehadiran (RSVP) warga dalam agenda kebersamaan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       6. MANFAAT BERDASARKAN ROLE (SECTION 5)
       ========================================================================== -->
    <section class="section-wrapper" id="manfaat">
        <div class="section-container">
            <div class="section-header-center">
                <span class="section-eyebrow">Nilai Tambah</span>
                <h2 class="section-title">Manfaat Dirancang Tepat untuk Setiap Peran</h2>
                <p class="section-subtitle">Setiap pemangku kepentingan mendapatkan tampilan, hak akses, dan alat kerja yang sesuai dengan tanggung jawabnya.</p>
            </div>

            <div class="roles-grid">
                <!-- Warga -->
                <div class="role-card">
                    <div class="role-header">
                        <div class="role-icon"><i class="fas fa-user-check" aria-hidden="true"></i></div>
                        <div>
                            <h3 class="role-title">Warga Lingkungan</h3>
                            <div class="role-subtitle">Masyarakat Mandiri & Aktif</div>
                        </div>
                    </div>
                    <ul class="role-checklist">
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Mengakses informasi pengumuman resmi RT/RW kapan saja.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Membayar iuran lingkungan tanpa harus menunggu petugas datang.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Mengajukan surat pengantar langsung dari smartphone.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Memeriksa jadwal ronda malam dan presensi hadir mandiri.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Tombol darurat cepat saat terjadi keadaan genting di rumah.</li>
                    </ul>
                </div>

                <!-- Ketua RT -->
                <div class="role-card highlighted">
                    <div class="role-tag-badge">PENGELOLA UTAMA</div>
                    <div class="role-header">
                        <div class="role-icon"><i class="fas fa-landmark" aria-hidden="true"></i></div>
                        <div>
                            <h3 class="role-title">Ketua RT</h3>
                            <div class="role-subtitle">Pengayom & Penanggung Jawab Wilayah</div>
                        </div>
                    </div>
                    <ul class="role-checklist">
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Isolasi data warga strictly terjaga dalam yurisdiksi RT-nya.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Verifikasi pembayaran iuran dan monitoring tunggakan warga.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Kelola regu ronda malam dan kirim pengingat 1-klik ke petugas.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Setujui atau teruskan permohonan surat warga ke RW.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Tangani laporan alarm darurat secara cepat dan terpantau.</li>
                    </ul>
                </div>

                <!-- Pengurus RW -->
                <div class="role-card">
                    <div class="role-header">
                        <div class="role-icon"><i class="fas fa-building" aria-hidden="true"></i></div>
                        <div>
                            <h3 class="role-title">Pengurus RW</h3>
                            <div class="role-subtitle">Koordinator & Pengawas Agregat</div>
                        </div>
                    </div>
                    <ul class="role-checklist">
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Monitoring perbandingan keaktifan dan demografi seluruh RT.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Transparansi laporan kas agregat per RT tanpa manipulasi.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Manajemen akun Ketua RT dan audit keamanan sistem (log aktivitas).</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Terbitkan surat pengantar tingkat kelurahan secara terintegrasi.</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Koordinasi program kerja lintas RT dengan efisiensi tinggi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       7. CARA KERJA APLIKASI (SECTION 6)
       ========================================================================== -->
    <section class="section-wrapper" id="cara-kerja" style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
        <div class="section-container">
            <div class="section-header-center">
                <span class="section-eyebrow">Alur Mudah</span>
                <h2 class="section-title">4 Langkah Memulai Lingkungan Digital</h2>
                <p class="section-subtitle">Implementasi praktis dan cepat tanpa instalasi server rumit di lingkungan Anda.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <h4 class="step-title">Pendaftaran & Data KK</h4>
                    <p class="step-desc">Warga mendaftar akun dan memasukkan nomor Kartu Keluarga. Data divalidasi langsung oleh Ketua RT wilayah terkait.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">02</div>
                    <h4 class="step-title">Aktivasi Iuran & Jadwal</h4>
                    <p class="step-desc">Pengurus RT mengatur jadwal ronda malam, tarif iuran bulanan, serta program bank sampah dan posyandu lingkungan.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">03</div>
                    <h4 class="step-title">Pelayanan Terintegrasi</h4>
                    <p class="step-desc">Warga menikmati pengurusan surat, pembayaran iuran berstruk, dan komunikasi aspirasi yang langsung terdisposisi.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">04</div>
                    <h4 class="step-title">Monitoring & Audit</h4>
                    <p class="step-desc">Pengurus RW dan warga dapat memantau keterbukaan kas dan laporan kegiatan yang terbit secara berkala dan otomatis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       8. KEAMANAN & TRANSPARANSI (SECTION 7)
       ========================================================================== -->
    <section class="section-wrapper" id="keamanan">
        <div class="section-container">
            <div class="security-box">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 800; letter-spacing: 1px; color: var(--accent); text-transform: uppercase;">Keamanan & Privasi Terjamin</span>
                    <h2 style="font-size: 2.1rem; font-weight: 800; margin: 10px 0 14px; line-height: 1.25;">Perlindungan Data Warga adalah Prioritas Tertinggi</h2>
                    <p style="font-size: 0.95rem; color: rgba(255,255,255,0.88); line-height: 1.6; margin: 0;">RW SMART menerapkan prinsip isolasi yurisdiksi, pembatasan hak akses berbasis peran (RBAC), serta rekaman audit log komprehensif untuk mencegah kebocoran data pribadi.</p>
                </div>

                <div class="security-grid">
                    <div class="security-item">
                        <i class="fas fa-database" aria-hidden="true"></i>
                        <h5>Isolasi Data Antar RT</h5>
                        <p>Ketua RT 01 hanya dapat mengakses warganya sendiri dan tidak memiliki wewenang melihat data internal RT lain.</p>
                    </div>

                    <div class="security-item">
                        <i class="fas fa-user-shield" aria-hidden="true"></i>
                        <h5>Audit Trail Lengkap</h5>
                        <p>Setiap perubahan status kas, mutasi penduduk, dan permohonan tercatat dengan identitas pengubah dan waktu presisi.</p>
                    </div>

                    <div class="security-item">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        <h5>Enkripsi Kredensial & CSRF</h5>
                        <p>Password akun dilindungi enkripsi hash tingkat industri dan seluruh transaksi formulir dilindungi token CSRF otomatis.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       9. CALL TO ACTION (SECTION 8)
       ========================================================================== -->
    <section class="section-wrapper" id="cta" style="padding-top: 20px;">
        <div class="section-container">
            <div class="cta-box">
                <span class="section-eyebrow">Mulai Transformasi</span>
                <h2 class="cta-title">Siap Wujudkan Rukun Warga Modern & Mandiri?</h2>
                <p class="cta-desc">Bergabunglah bersama kami untuk menciptakan lingkungan RT dan RW yang aman, nyaman, transparan, dan terhubung secara harmonis.</p>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" class="btn-saas-primary" style="padding: 14px 34px; font-size: 1rem;">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                        <span>Masuk ke Akun Anda</span>
                    </a>
                    <a href="{{ route('login') }}#register" class="btn-saas-outline" style="padding: 14px 28px; font-size: 1rem;">
                        <span>Daftarkan Keluarga Baru</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
       10. FOOTER LENGKAP (SECTION 9)
       ========================================================================== -->
    <footer class="landing-footer" id="tentang">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="{{ url('/') }}" class="brand-link">
                        <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="RW SMART Logo" width="34" height="34">
                        <span class="brand-title">RW <span>SMART</span></span>
                    </a>
                    <p>Sistem Informasi Manajemen Rukun Warga & Pelayanan Publik Lingkungan Modern berbasis SaaS yang akuntabel, mudah digunakan, dan terintegrasi.</p>
                </div>

                <div>
                    <h5 class="footer-col-title">Navigasi Fitur</h5>
                    <ul class="footer-links">
                        <li><a href="#fitur">Administrasi Warga</a></li>
                        <li><a href="#fitur">Kas & Iuran Transparan</a></li>
                        <li><a href="#fitur">Siskamling & Ronda Malam</a></li>
                        <li><a href="#fitur">Pelayanan Surat RT/RW</a></li>
                        <li><a href="#layanan">Bank Sampah & Posyandu</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="footer-col-title">Akses Cepat</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('login') }}">Masuk Dasbor RT & RW</a></li>
                        <li><a href="{{ route('login') }}#register">Registrasi Akun Warga</a></li>
                        <li><a href="{{ route('dashboard') }}">Portal Mandiri</a></li>
                        <li><a href="#keamanan">Pusat Keamanan Data</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="footer-col-title">Wilayah Pengabdian</h5>
                    <p style="font-size: 0.88rem; color: var(--ink-soft); line-height: 1.6; margin-bottom: 12px;">
                        Wilayah Rukun Warga 01<br>
                        Kelurahan Digital Percontohan<br>
                        Kecamatan Inovasi Modern
                    </p>
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary); background: var(--primary-soft); padding: 5px 12px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-signal" aria-hidden="true"></i> Sistem Aktif & Terhubung
                    </span>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; {{ date('Y') }} <strong>RW SMART</strong>. Hak Cipta Dilindungi Undang-Undang.</div>
                <div>Modern SaaS Community Architecture &bull; Clean, Minimalist, Premium</div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Help Partial -->
    @include('partials.whatsapp-help')

    <!-- Theme Switcher & Lightweight Count-Up Script -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');

        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('risma-theme', theme);
            if (themeToggleIcon) {
                themeToggleIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        setTheme(localStorage.getItem('risma-theme') || 'light');

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
                setTheme(current === 'dark' ? 'light' : 'dark');
            });
        }

        // Lightweight Number Count-up Animation (0.8s - 1.2s, exactly final original values)
        document.addEventListener('DOMContentLoaded', function () {
            const counters = document.querySelectorAll('.count-up');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target') || counter.innerText, 10);
                if (isNaN(target) || target <= 0) return;
                
                const duration = 1000;
                const startTime = performance.now();
                
                function updateCount(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease out quadratic
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const currentVal = Math.floor(easeOut * target);
                    counter.innerText = currentVal.toLocaleString('id-ID');
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                }
                requestAnimationFrame(updateCount);
            });
        });
    </script>
</body>
</html>
