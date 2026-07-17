<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RW SMART</title>
    <script>
        (function () {
            document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('risma-theme') || 'dark');
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link href="{{ asset('css/theme-welcome.css') }}" rel="stylesheet">
</head>
<body>

    <!-- ORNAMEN -->
    <div class="ornament top-left"></div>
    <div class="ornament top-right"></div>
    <div class="ornament bottom-left"></div>
    <div class="ornament bottom-right"></div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-mosque"></i> RW SMART
        </div>

        <div class="nav-links">
            <a href="#layanan">Layanan</a>
            <a href="#fitur">Fitur</a>
            <a href="#program">Program</a>
            <a href="#tentang">Tentang</a>
            <button type="button" class="theme-toggle" id="themeToggleBtn" title="Ganti mode tampilan" aria-label="Ganti mode tampilan">
                <i class="fas fa-moon" id="themeToggleIcon"></i>
            </button>
            <a href="{{ route('login') }}" class="btn-login">Log Masuk</a>
            <a href="{{ route('register') }}" class="btn-register">Daftar</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">

        <div class="hero-text">
            <h1>
                Kelola Iuran RW <br>
                <span>Lebih Mudah, Berkah</span> <br>
                & Transparan
            </h1>

            <p>
                RW SMART adalah platform digital yang membantu mengelola iuran warga
                dengan prinsip syariah. Praktis, aman, dan mudah digunakan untuk kebersamaan warga RW.
            </p>

            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn-primary">
                    <i class="fas fa-home"></i> Mulai Berbagi Sekarang
                </a>

                <a href="#program" class="btn-outline">
                    <i class="fas fa-play"></i> Lihat Program & Fitur
                </a>
            </div>

            <div class="trust">
                <span><i class="fas fa-shield-alt"></i> Amanah</span>
                <span><i class="fas fa-eye"></i> Transparan</span>
                <span><i class="fas fa-mosque"></i> Sesuai Syariah</span>
            </div>
        </div>

        <!-- GAMBAR HERO -->
        <div class="hero-card">
            <div class="card-box">
                <img src="{{ asset('images/rw-smart-hero.png') }}" alt="Logo RW Smart">
            </div>
        </div>

    </section>

    <section class="section" id="program">
        <div class="section-header">
            <div class="eyebrow">Program Kas RW</div>
            <h2>Program iuran warga untuk <span>1 bulan Lebaran</span></h2>
            <p>
                RW SMART membantu pengurus mengelola kebutuhan kas musiman maupun rutin,
                mulai dari iuran keamanan, kebersihan, takjil, hingga kegiatan Ramadhan dan Lebaran.
            </p>
        </div>

        <div class="grid">
            <div class="program-card">
                <div class="program-icon"><i class="fas fa-moon"></i></div>
                <h3>Iuran Ramadhan & Lebaran</h3>
                <p>Program satu bulan untuk kebutuhan berbuka, takjil, kegiatan warga, dan persiapan hari raya.</p>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Target iuran per periode</li>
                    <li><i class="fas fa-check-circle"></i> Status lunas/pending terlihat jelas</li>
                    <li><i class="fas fa-check-circle"></i> Rekap pemasukan siap dilaporkan</li>
                </ul>
            </div>

            <div class="program-card">
                <div class="program-icon"><i class="fas fa-broom"></i></div>
                <h3>Operasional RW</h3>
                <p>Kelola iuran rutin untuk kebersihan, keamanan, dan kebutuhan lingkungan tanpa catatan tercecer.</p>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Kelola jenis iuran aktif</li>
                    <li><i class="fas fa-check-circle"></i> Catat pengeluaran kas RW</li>
                    <li><i class="fas fa-check-circle"></i> Pantau saldo kas harian</li>
                </ul>
            </div>

            <div class="program-card">
                <div class="program-icon"><i class="fas fa-hands-helping"></i></div>
                <h3>Dana Sosial Warga</h3>
                <p>Transparansi dana sosial untuk bantuan warga, santunan, atau kebutuhan bersama yang mendesak.</p>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Riwayat transaksi tersimpan</li>
                    <li><i class="fas fa-check-circle"></i> Bukti pembayaran bisa dicek</li>
                    <li><i class="fas fa-check-circle"></i> Laporan mudah dibagikan</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section" id="layanan">
        <div class="section-header">
            <div class="eyebrow">Layanan Masyarakat</div>
            <h2>Layanan warga dalam <span>satu platform</span></h2>
            <p>
                RW SMART menghubungkan kebutuhan administrasi, kesehatan, keamanan,
                lingkungan, kegiatan, dan ekonomi warga agar lebih mudah diakses.
            </p>
        </div>

        <div class="service-grid">
            <article class="service-card">
                <i class="fas fa-leaf"></i>
                <strong>Bank Sampah</strong>
                <span>Setoran, saldo, harga, dan jadwal layanan sampah warga.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-shop"></i>
                <strong>UMKM</strong>
                <span>Etalase produk dan jasa milik warga di lingkungan sekitar.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-file-signature"></i>
                <strong>Surat Menyurat</strong>
                <span>Pengajuan, pemantauan status, dan pencetakan surat warga.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-baby-carriage"></i>
                <strong>Posyandu</strong>
                <span>Jadwal layanan, pertumbuhan anak, dan informasi imunisasi.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-shield-halved"></i>
                <strong>Keamanan / Ronda</strong>
                <span>Jadwal piket, absensi petugas, CCTV, dan laporan darurat.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-calendar-day"></i>
                <strong>Kegiatan</strong>
                <span>Agenda RT/RW, pendaftaran peserta, dan pencatatan kehadiran.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-map-location-dot"></i>
                <strong>Peta Wilayah</strong>
                <span>Pemetaan CCTV, kegiatan, Posyandu, dan lokasi UMKM warga.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-bullhorn"></i>
                <strong>Aspirasi</strong>
                <span>Penyampaian saran, keluhan, serta pemantauan tindak lanjut.</span>
            </article>
            <article class="service-card">
                <i class="fas fa-coins"></i>
                <strong>Kas</strong>
                <span>Informasi pemasukan, pengeluaran, dan saldo kas lingkungan.</span>
            </article>
        </div>
    </section>

    <section class="section" id="fitur">
        <div class="section-header">
            <div class="eyebrow">Fitur Utama</div>
            <h2>Semua kebutuhan kas RW dalam <span>satu aplikasi</span></h2>
            <p>
                Dirancang untuk admin, bendahara, dan warga agar proses iuran lebih rapi,
                terbuka, dan mudah dipantau.
            </p>
        </div>

        <div class="feature-list">
            <div class="feature-row">
                <i class="fas fa-wallet"></i>
                <div>
                    <strong>Pembayaran Iuran Warga</strong>
                    <span>Warga memilih iuran, periode, metode bayar, lalu mengunggah bukti pembayaran.</span>
                </div>
            </div>
            <div class="feature-row">
                <i class="fas fa-user-check"></i>
                <div>
                    <strong>Verifikasi Admin/Bendahara</strong>
                    <span>Pembayaran warga masuk sebagai menunggu verifikasi sebelum tercatat lunas.</span>
                </div>
            </div>
            <div class="feature-row">
                <i class="fas fa-receipt"></i>
                <div>
                    <strong>Catatan Kas Masuk & Keluar</strong>
                    <span>Pemasukan dan pengeluaran tersusun dalam riwayat kas yang mudah diperiksa.</span>
                </div>
            </div>
            <div class="feature-row">
                <i class="fas fa-file-excel"></i>
                <div>
                    <strong>Laporan Keuangan</strong>
                    <span>Rekap pembayaran per warga, periode, dan jenis iuran untuk kebutuhan rapat RW.</span>
                </div>
            </div>
            <div class="feature-row">
                <i class="fas fa-bell"></i>
                <div>
                    <strong>Notifikasi & Pengingat</strong>
                    <span>Pengurus dan warga mendapat pemberitahuan saat ada pembayaran atau tagihan.</span>
                </div>
            </div>
            <div class="feature-row">
                <i class="fas fa-sun"></i>
                <div>
                    <strong>Mode Gelap & Terang</strong>
                    <span>Tampilan nyaman dibaca, termasuk tema terang lembut bernuansa Lebaran.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="alur">
        <div class="section-header">
            <div class="eyebrow">Alur Praktis</div>
            <h2>Dari tagihan sampai laporan <span>lebih tertib</span></h2>
        </div>

        <div class="flow">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Admin buat iuran</h3>
                <p>Tentukan nama iuran, nominal, periode, kategori, dan status aktif.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Warga membayar</h3>
                <p>Warga melihat tagihan, memilih metode, dan mengirim bukti pembayaran.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Petugas verifikasi</h3>
                <p>Admin atau bendahara mengecek bukti sebelum mengubah status menjadi lunas.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Laporan siap</h3>
                <p>Saldo, pemasukan, pengeluaran, dan rekap warga bisa dipantau kapan saja.</p>
            </div>
        </div>
    </section>

    <section class="section" id="tentang">
        <div class="about-panel">
            <div class="section-header">
                <div class="eyebrow">Tentang RW SMART</div>
                <h2>Transparansi kecil yang membuat warga <span>lebih percaya</span></h2>
                <p>
                    Aplikasi ini dibuat untuk membantu RW mengurangi pencatatan manual,
                    mempercepat verifikasi pembayaran, dan membuat laporan kas lebih mudah
                    dipahami oleh pengurus maupun warga.
                </p>
            </div>
            <div class="about-stats">
                <div class="stat-box">
                    <strong>24/7</strong>
                    <span>Riwayat kas dapat dicek kapan saja.</span>
                </div>
                <div class="stat-box">
                    <strong>1</strong>
                    <span>Dashboard untuk admin, bendahara, dan warga.</span>
                </div>
                <div class="stat-box">
                    <strong>PDF</strong>
                    <span>Laporan siap dibagikan saat rapat RW.</span>
                </div>
                <div class="stat-box">
                    <strong>RW</strong>
                    <span>Dibuat khusus untuk kebutuhan lingkungan warga.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="cta-band">
            <div>
                <div class="eyebrow">Mulai Sekarang</div>
                <h2>Siapkan kas RW Lebaran dengan lebih rapi.</h2>
                <p>Daftarkan warga, kelola iuran, dan pantau pembayaran dalam satu aplikasi.</p>
            </div>
            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Daftar Warga
                </a>
                <a href="{{ route('login') }}" class="btn-outline">
                    <i class="fas fa-sign-in-alt"></i> Log Masuk
                </a>
            </div>
        </div>
    </section>

    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');

        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('risma-theme', theme);
            themeToggleIcon.className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
        }

        setTheme(localStorage.getItem('risma-theme') || 'dark');

        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    </script>

    @include('partials.whatsapp-help')

</body>
</html>
