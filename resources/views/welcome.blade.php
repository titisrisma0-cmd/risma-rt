<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISMA-RT</title>
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', localStorage.getItem('risma-theme') || 'dark');
        })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Inter', sans-serif;
            background: radial-gradient(circle at top right, #17394a 0%, #0b1d28 40%, #07131d 100%);
            color:white;
            min-height:100vh;
            overflow-x:hidden;
            position:relative;
        }

        html{
            scroll-behavior:smooth;
        }

        /* ORNAMEN 4 POJOK */
        .ornament{
            position:absolute;
            width:180px;
            height:180px;
            border:2px solid rgba(216,180,106,0.2);
            border-radius:50%;
            z-index:1;
        }

        .ornament::before{
            content:"";
            position:absolute;
            inset:20px;
            border:2px solid rgba(216,180,106,0.15);
            border-radius:50%;
        }

        .ornament::after{
            content:"";
            position:absolute;
            inset:40px;
            border:2px solid rgba(216,180,106,0.1);
            border-radius:50%;
        }

        .top-left{
            top:-60px;
            left:-60px;
        }

        .top-right{
            top:-60px;
            right:-60px;
        }

        .bottom-left{
            bottom:-60px;
            left:-60px;
        }

        .bottom-right{
            bottom:-60px;
            right:-60px;
        }

        /* NAVBAR */
        .navbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:25px 8%;
            position:relative;
            z-index:10;
        }

        .logo{
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:700;
            font-size:22px;
            color:#d8b46a;
        }

        .nav-links{
            display:flex;
            align-items:center;
            gap:20px;
        }

        .nav-links a{
            color:white;
            text-decoration:none;
            font-size:15px;
            transition:0.3s;
        }

        .nav-links a:hover{
            color:#d8b46a;
        }

        .btn-login{
            padding:10px 24px;
            border-radius:30px;
            border:none;
            background:linear-gradient(135deg,#d8b46a,#8c6b2d);
            color:white;
            font-weight:600;
            box-shadow:0 0 15px rgba(216,180,106,0.5);
        }

        .btn-register{
            padding:10px 24px;
            border-radius:30px;
            border:1px solid #d8b46a;
            color:#d8b46a;
            background:transparent;
            font-weight:600;
        }

        .theme-toggle{
            width:42px;
            height:42px;
            border-radius:50%;
            border:1px solid rgba(216,180,106,.6);
            background:rgba(255,255,255,.04);
            color:#d8b46a;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            transition:.25s;
        }

        .theme-toggle:hover{
            background:rgba(216,180,106,.14);
        }

        /* HERO */
        .hero{
            display:flex;
            justify-content:space-between;
            align-items:center;
            min-height:85vh;
            padding:0 8%;
            gap:50px;
            position:relative;
            z-index:10;
        }

        .hero-text{
            flex:1;
        }

        .hero-text h1{
            font-family:'Cinzel', serif;
            font-size:4rem;
            line-height:1.2;
            color:#d8b46a;
            margin-bottom:20px;
        }

        .hero-text h1 span{
            color:white;
        }

        .hero-text p{
            color:#d4d4d4;
            max-width:500px;
            line-height:1.7;
            margin-bottom:30px;
        }

        .hero-buttons{
            display:flex;
            gap:15px;
            margin-bottom:25px;
        }

        .btn-primary{
            background:linear-gradient(135deg,#7b3ff2,#5f2eea);
            color:white;
            padding:14px 28px;
            border-radius:30px;
            text-decoration:none;
            font-weight:600;
        }

        .btn-outline{
            border:1px solid #d8b46a;
            color:#d8b46a;
            padding:14px 28px;
            border-radius:30px;
            text-decoration:none;
            font-weight:600;
        }

        .trust{
            display:flex;
            gap:25px;
            flex-wrap:wrap;
            color:#d8b46a;
            font-size:14px;
        }

        /* CARD HERO */
        .hero-card{
            flex:1;
            display:flex;
            justify-content:center;
        }

        .card-box{
            width:400px;
            height:400px;
            border:1px solid rgba(216,180,106,0.5);
            border-radius:20px;
            background:rgba(255,255,255,0.03);
            backdrop-filter:blur(10px);
            box-shadow:0 0 30px rgba(216,180,106,0.15);
            overflow:hidden;
        }

        .card-box img{
            width:100%;
            height:100%;
            object-fit:cover;
            border-radius:20px;
        }

        .section{
            position:relative;
            z-index:10;
            padding:85px 8%;
        }

        .section-header{
            max-width:760px;
            margin-bottom:34px;
        }

        .eyebrow{
            color:#d8b46a;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
            font-size:13px;
            margin-bottom:10px;
        }

        .section h2{
            font-family:'Cinzel', serif;
            font-size:2.5rem;
            line-height:1.2;
            color:#fff;
            margin-bottom:14px;
        }

        .section h2 span{
            color:#d8b46a;
        }

        .section p{
            color:#d4d4d4;
            line-height:1.7;
        }

        .grid{
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:20px;
        }

        .feature-card,
        .program-card,
        .step-card{
            border:1px solid rgba(216,180,106,.28);
            background:linear-gradient(145deg, rgba(255,255,255,.07), rgba(255,255,255,.025));
            border-radius:20px;
            padding:24px;
            box-shadow:0 16px 34px rgba(0,0,0,.18);
        }

        .feature-icon,
        .program-icon,
        .step-number{
            width:48px;
            height:48px;
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:rgba(216,180,106,.14);
            color:#d8b46a;
            margin-bottom:16px;
        }

        .feature-card h3,
        .program-card h3,
        .step-card h3{
            color:#fff;
            font-size:1.1rem;
            margin-bottom:10px;
        }

        .program-card ul{
            list-style:none;
            margin-top:18px;
        }

        .program-card li{
            color:#d4d4d4;
            margin-bottom:10px;
            display:flex;
            gap:10px;
            align-items:flex-start;
        }

        .program-card li i{
            color:#d8b46a;
            margin-top:3px;
        }

        .feature-list{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .feature-row{
            display:flex;
            gap:14px;
            align-items:flex-start;
            padding:18px;
            border-radius:18px;
            border:1px solid rgba(216,180,106,.22);
            background:rgba(255,255,255,.04);
        }

        .feature-row i{
            color:#d8b46a;
            font-size:1.25rem;
            margin-top:3px;
        }

        .feature-row strong{
            display:block;
            margin-bottom:5px;
            color:#fff;
        }

        .feature-row span{
            color:#d4d4d4;
            font-size:.94rem;
            line-height:1.6;
        }

        .flow{
            display:grid;
            grid-template-columns:repeat(4, minmax(0, 1fr));
            gap:16px;
        }

        .step-number{
            font-weight:800;
        }

        .cta-band{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:24px;
            border:1px solid rgba(216,180,106,.36);
            background:linear-gradient(135deg, rgba(216,180,106,.16), rgba(21,69,55,.34));
            border-radius:28px;
            padding:32px;
        }

        .cta-band h2{
            font-size:2rem;
            margin-bottom:8px;
        }

        .about-panel{
            display:grid;
            grid-template-columns:1.1fr .9fr;
            gap:24px;
            align-items:stretch;
        }

        .about-stats{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
        }

        .stat-box{
            border:1px solid rgba(216,180,106,.24);
            border-radius:20px;
            padding:22px;
            background:rgba(255,255,255,.045);
        }

        .stat-box strong{
            display:block;
            font-size:2rem;
            color:#d8b46a;
            margin-bottom:4px;
        }

        .stat-box span{
            color:#d4d4d4;
            font-size:.92rem;
        }

        html[data-theme="light"] body{
            background:
                radial-gradient(ellipse at 86% 12%, rgba(216,180,106,.22), transparent 34%),
                radial-gradient(ellipse at 10% 88%, rgba(45,97,63,.2), transparent 34%),
                linear-gradient(135deg, #edf1e8 0%, #f4f6ee 45%, #e4eadf 100%);
            color:#1f2933;
        }

        html[data-theme="light"] .ornament{
            border-color:rgba(138,116,40,.18);
        }

        html[data-theme="light"] .ornament::before,
        html[data-theme="light"] .ornament::after{
            border-color:rgba(138,116,40,.12);
        }

        html[data-theme="light"] .nav-links a,
        html[data-theme="light"] .hero-text h1 span,
        html[data-theme="light"] .section h2,
        html[data-theme="light"] .feature-card h3,
        html[data-theme="light"] .program-card h3,
        html[data-theme="light"] .step-card h3,
        html[data-theme="light"] .feature-row strong{
            color:#1f2933;
        }

        html[data-theme="light"] .hero-text p,
        html[data-theme="light"] .section p,
        html[data-theme="light"] .feature-row span,
        html[data-theme="light"] .program-card li,
        html[data-theme="light"] .stat-box span{
            color:#5f6f63;
        }

        html[data-theme="light"] .hero-text h1,
        html[data-theme="light"] .logo,
        html[data-theme="light"] .eyebrow,
        html[data-theme="light"] .section h2 span,
        html[data-theme="light"] .trust,
        html[data-theme="light"] .feature-row i,
        html[data-theme="light"] .stat-box strong{
            color:#8a7428;
        }

        html[data-theme="light"] .card-box,
        html[data-theme="light"] .program-card,
        html[data-theme="light"] .feature-card,
        html[data-theme="light"] .step-card,
        html[data-theme="light"] .feature-row,
        html[data-theme="light"] .stat-box,
        html[data-theme="light"] .cta-band{
            background:rgba(248,250,244,.82);
            border-color:rgba(125,138,113,.24);
            box-shadow:0 16px 34px rgba(57,73,56,.09);
        }

        html[data-theme="light"] .btn-outline,
        html[data-theme="light"] .btn-register,
        html[data-theme="light"] .theme-toggle{
            color:#8a7428;
            border-color:#8a7428;
        }

        @media(max-width:992px){
            .hero{
                flex-direction:column;
                text-align:center;
                padding-top:40px;
            }

            .hero-text h1{
                font-size:2.5rem;
            }

            .hero-buttons,
            .trust{
                justify-content:center;
            }

            .card-box{
                width:300px;
                height:300px;
            }

            .nav-links{
                flex-wrap:wrap;
                justify-content:center;
            }

            .grid,
            .feature-list,
            .flow,
            .about-panel{
                grid-template-columns:1fr;
            }

            .section{
                padding:60px 6%;
            }

            .section h2{
                font-size:2rem;
            }

            .cta-band{
                flex-direction:column;
                align-items:flex-start;
            }
        }
    </style>
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
            <i class="fas fa-mosque"></i> RISMA-RT
        </div>

        <div class="nav-links">
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
                Kelola Iuran RT <br>
                <span>Lebih Mudah, Berkah</span> <br>
                & Transparan
            </h1>

            <p>
                RISMA-RT adalah platform digital yang membantu mengelola iuran warga
                dengan prinsip syariah. Praktis, aman, dan mudah digunakan untuk kebersamaan warga RT.
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
                <img src="{{ asset('images/banner.png') }}" alt="Banner">
            </div>
        </div>

    </section>

    <section class="section" id="program">
        <div class="section-header">
            <div class="eyebrow">Program Kas RT</div>
            <h2>Program iuran warga untuk <span>1 bulan Lebaran</span></h2>
            <p>
                RISMA-RT membantu pengurus mengelola kebutuhan kas musiman maupun rutin,
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
                <h3>Operasional RT</h3>
                <p>Kelola iuran rutin untuk kebersihan, keamanan, dan kebutuhan lingkungan tanpa catatan tercecer.</p>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Kelola jenis iuran aktif</li>
                    <li><i class="fas fa-check-circle"></i> Catat pengeluaran kas RT</li>
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

    <section class="section" id="fitur">
        <div class="section-header">
            <div class="eyebrow">Fitur Utama</div>
            <h2>Semua kebutuhan kas RT dalam <span>satu aplikasi</span></h2>
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
                    <span>Rekap pembayaran per warga, periode, dan jenis iuran untuk kebutuhan rapat RT.</span>
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
                <div class="eyebrow">Tentang RISMA-RT</div>
                <h2>Transparansi kecil yang membuat warga <span>lebih percaya</span></h2>
                <p>
                    Aplikasi ini dibuat untuk membantu RT mengurangi pencatatan manual,
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
                    <span>Laporan siap dibagikan saat rapat RT.</span>
                </div>
                <div class="stat-box">
                    <strong>RT</strong>
                    <span>Dibuat khusus untuk kebutuhan lingkungan warga.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="cta-band">
            <div>
                <div class="eyebrow">Mulai Sekarang</div>
                <h2>Siapkan kas RT Lebaran dengan lebih rapi.</h2>
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
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('risma-theme', theme);
            themeToggleIcon.className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
        }

        setTheme(localStorage.getItem('risma-theme') || 'dark');

        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    </script>

</body>
</html>
