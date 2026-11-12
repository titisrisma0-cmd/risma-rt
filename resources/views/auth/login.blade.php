<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk & Pendaftaran | RW SMART</title>
    
    @include('partials.whatsapp-help')
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg: #f7faf8;
            --surface: #ffffff;
            --border: #dbe9e0;
            --ink: #0f2a1d;
            --ink-soft: #4b6355;
            --ink-mute: #5f7468;
            --primary: #0e7a4b;
            --primary-dark: #0a5c38;
            --primary-soft: #e3f4ea;
            --accent: #f4a62a;
            --accent-soft: #fdf0d8;
            --danger: #e5483a;
            --danger-soft: #fdeeee;
            --info: #2a6fdb;
            --info-soft: #eaf1fc;
            --radius-btn: 12px;
            --radius-input: 12px;
            --radius-card: 22px;
            --shadow-soft: 0 16px 36px rgba(15, 42, 29, 0.10);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            font-size: 15px;
            line-height: 1.6;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 1080px;
            margin: auto;
            animation: rise 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: none; }
        }

        .auth-card {
            background: var(--surface);
            border-radius: var(--radius-card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        /* Left Brand Showcase */
        .brand-panel {
            background: linear-gradient(155deg, #0a5c38 0%, #0e7a4b 100%);
            color: #ffffff;
            padding: 48px 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 240px;
            height: 240px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
            text-decoration: none;
            margin-bottom: 32px;
        }

        .brand-heading {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.25;
            color: #ffffff;
            letter-spacing: -0.025em;
            margin-bottom: 14px;
        }

        .brand-description {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.65;
            margin-bottom: 34px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }

        .feature-icon-box {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.14);
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.1rem;
            color: #ffffff;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2px;
        }

        .feature-sub {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
            line-height: 1.45;
        }

        .brand-footer {
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.78);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Right Form Panel */
        .form-panel {
            padding: 44px 44px;
            background: var(--surface);
        }

        .auth-nav-tabs {
            display: flex;
            gap: 8px;
            background: var(--bg);
            padding: 5px;
            border-radius: var(--radius-btn);
            margin-bottom: 26px;
            border: 1.5px solid var(--border);
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            padding: 10px 14px;
            border-radius: 9px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ink-soft);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
        }

        .tab-btn.active {
            background: var(--surface);
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(15, 42, 29, 0.08);
            font-weight: 800;
        }

        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
            display: block;
        }

        .input-group-saas {
            display: flex;
            align-items: center;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-input);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
            overflow: hidden;
            min-height: 44px;
            height: 44px;
        }

        .input-group-saas:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 122, 75, 0.18);
        }

        .input-icon {
            padding: 0 14px;
            color: var(--ink-mute);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .input-group-saas input,
        .input-group-saas select {
            width: 100%;
            border: none;
            padding: 10px 14px 10px 0;
            font-size: 0.92rem;
            color: var(--ink);
            background: transparent;
            outline: none;
            min-height: 42px;
            font-family: inherit;
        }

        .input-group-saas input::placeholder {
            color: #8c9d93;
        }

        .password-toggle {
            background: none;
            border: none;
            padding: 0 14px;
            color: var(--ink-mute);
            cursor: pointer;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            height: 100%;
        }

        .password-toggle:hover {
            color: var(--ink);
        }

        .btn-submit-saas {
            width: 100%;
            background-color: var(--primary);
            border: 1.5px solid var(--primary);
            color: #ffffff;
            padding: 12px;
            min-height: 44px;
            border-radius: var(--radius-btn);
            font-weight: 700;
            font-size: 0.95rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(14, 122, 75, 0.28);
            cursor: pointer;
        }

        .btn-submit-saas:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 122, 75, 0.38);
        }

        .btn-submit-saas:active {
            transform: translateY(0);
        }

        .form-check-input {
            border: 1.5px solid var(--border);
            width: 1.15em;
            height: 1.15em;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Demo Account Pills */
        .demo-section {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px dashed var(--border);
        }

        .demo-title {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--ink-soft);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .demo-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .demo-pill {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 6px 12px;
            font-size: 0.78rem;
            color: var(--ink);
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
        }

        .demo-pill:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .demo-pill strong {
            color: var(--primary);
            font-weight: 700;
        }

        /* Alerts */
        .alert-saas {
            border-radius: var(--radius-btn);
            padding: 12px 16px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
            border-left-width: 4px;
        }

        .alert-saas-danger {
            background: var(--danger-soft);
            border-color: rgba(229, 72, 58, 0.25);
            border-left-color: var(--danger);
            color: #a42014;
        }

        .alert-saas-success {
            background: var(--primary-soft);
            border-color: rgba(14, 122, 75, 0.25);
            border-left-color: var(--primary);
            color: var(--primary-dark);
        }

        .alert-saas-info {
            background: var(--info-soft);
            border-color: rgba(42, 111, 219, 0.25);
            border-left-color: var(--info);
            color: #174ea6;
        }

        @media (max-width: 991.98px) {
            .brand-panel {
                padding: 32px 24px;
            }
            .form-panel {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="row g-0">
                <!-- Left Panel - Brand Showcase -->
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="brand-panel">
                        <div>
                            <a href="{{ url('/') }}" class="brand-header">
                                <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="RW SMART" width="42" height="42" class="rounded-3 shadow-sm bg-white p-1">
                                <span>RW SMART</span>
                            </a>
                            
                            <h2 class="brand-heading">Sistem Informasi & Tata Kelola Warga Modern</h2>
                            <p class="brand-description">
                                Transformasi digital terpadu untuk pengurus RW, ketua RT, dan warga dalam satu platform yang bersih, transparan, dan akuntabel.
                            </p>
                            
                            <div class="feature-list">
                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fas fa-coins" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h6 class="feature-title">Keuangan & Kas Real-Time</h6>
                                        <p class="feature-sub">Pencatatan iuran, mutasi kas, dan audit trail otomatis</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fas fa-file-signature" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h6 class="feature-title">Layanan Surat & Permohonan</h6>
                                        <p class="feature-sub">Pengajuan surat pengantar warga dengan verifikasi bertingkat</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h6 class="feature-title">Keamanan Lingkungan & CCTV</h6>
                                        <p class="feature-sub">Monitoring jadwal ronda malam dan live streaming CCTV</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="brand-footer">
                            <span>RW 05 Kelurahan Pintar</span>
                            <span>v2.0 Modern SaaS</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Panel - Forms -->
                <div class="col-lg-7">
                    <div class="form-panel">
                        <!-- Navigation Tabs -->
                        <div class="auth-nav-tabs">
                            <button class="tab-btn active" data-tab="login" type="button">
                                <i class="fas fa-right-to-bracket" aria-hidden="true"></i> Masuk Akun
                            </button>
                            <button class="tab-btn" data-tab="register" type="button">
                                <i class="fas fa-user-plus" aria-hidden="true"></i> Pendaftaran Warga
                            </button>
                        </div>
                        
                        <!-- Login Form -->
                        <div id="loginForm" class="auth-form active">
                            @if($errors->any() && !session('register_success'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5" aria-hidden="true"></i>
                                    <div>{{ $errors->first() }}</div>
                                </div>
                            @endif
                            
                            @if(session('success'))
                                <div class="alert-saas alert-saas-success">
                                    <i class="fas fa-circle-check fs-5" aria-hidden="true"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5" aria-hidden="true"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label-custom">Alamat Email</label>
                                    <div class="input-group-saas">
                                        <span class="input-icon"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                        <input type="email" name="email" placeholder="nama@email.com" required autofocus value="{{ old('email') }}">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label-custom">Kata Sandi</label>
                                    <div class="input-group-saas">
                                        <span class="input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                                        <input type="password" name="password" id="loginPassword" placeholder="••••••••" required>
                                        <button type="button" class="password-toggle" aria-label="Tampilkan password" data-target="loginPassword">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                        <label class="form-check-label small" for="remember" style="color: var(--ink-soft); font-weight: 500;">Ingat Saya</label>
                                    </div>
                                    <a href="{{ route('password.request') }}" class="small text-decoration-none fw-bold" style="color: var(--primary);">Lupa password?</a>
                                </div>
                                
                                <button type="submit" class="btn-submit-saas">
                                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i> Masuk ke Dashboard
                                </button>
                            </form>
                            
                            <!-- Demo Accounts Toolbar -->
                            <div class="demo-section">
                                <div class="demo-title">
                                    <i class="fas fa-laptop-code" aria-hidden="true"></i> Akun Pengujian Cepat (Klik untuk Isi Otomatis)
                                </div>
                                <div class="demo-pills">
                                    <div class="demo-pill" role="button" tabindex="0" onclick="fillLogin('admin@rw-smart.test', 'password123')">
                                        <strong>Admin / RW</strong>
                                    </div>
                                    <div class="demo-pill" role="button" tabindex="0" onclick="fillLogin('rt01@rw-smart.test', 'password123')">
                                        <strong>Ketua RT 01</strong>
                                    </div>
                                    <div class="demo-pill" role="button" tabindex="0" onclick="fillLogin('rt03@rw-smart.test', 'password123')">
                                        <strong>Ketua RT 03</strong>
                                    </div>
                                    <div class="demo-pill" role="button" tabindex="0" onclick="fillLogin('bendahara@rw-smart.test', 'password123')">
                                        <strong>Bendahara</strong>
                                    </div>
                                    <div class="demo-pill" role="button" tabindex="0" onclick="fillLogin('warga@rw-smart.test', 'password123')">
                                        <strong>Warga</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Register Form -->
                        <div id="registerForm" class="auth-form" style="display: none;">
                            @if($errors->any() && !session('register_success'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5" aria-hidden="true"></i>
                                    <div>
                                        <ul class="mb-0 ps-3">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                @php
                                    $regRts = \App\Models\Rt::orderBy('kode_rt')->get();
                                @endphp

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Wilayah RW</label>
                                        <div class="input-group-saas" style="background-color: var(--bg);">
                                            <span class="input-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                                            <input type="text" class="form-control border-0" value="RW 05 SMART" readonly style="background-color: transparent;">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Wilayah RT Domisili <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span>
                                            <select name="rt_id" required>
                                                <option value="" disabled {{ old('rt_id') ? '' : 'selected' }}>-- Pilih RT Domisili --</option>
                                                @foreach($regRts as $rtOpt)
                                                    <option value="{{ $rtOpt->id }}" {{ old('rt_id') == $rtOpt->id ? 'selected' : '' }}>
                                                        RT {{ $rtOpt->kode_rt }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <label class="form-label-custom">Nama Lengkap Sesuai KTP <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
                                            <input type="text" name="name" placeholder="Nama lengkap sesuai KTP" value="{{ old('name') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-address-card" aria-hidden="true"></i></span>
                                            <input type="text" name="nik" placeholder="16 digit NIK" value="{{ old('nik') }}" maxlength="16" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Nomor Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-id-card" aria-hidden="true"></i></span>
                                            <input type="text" name="no_kk" placeholder="16 digit No. KK" value="{{ old('no_kk') }}" maxlength="16" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-phone" aria-hidden="true"></i></span>
                                            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}" required>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-venus-mars" aria-hidden="true"></i></span>
                                            <select name="jenis_kelamin" required>
                                                <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih jenis kelamin</option>
                                                <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                                <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Alamat Email <span class="text-danger">*</span></label>
                                    <div class="input-group-saas">
                                        <span class="input-icon"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                        <input type="email" name="email" placeholder="nama@email.com" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Kata Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                                            <input type="password" name="password" placeholder="Min. 8 karakter" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Konfirmasi Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                                            <input type="password" name="password_confirmation" placeholder="Ulangi sandi" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert-saas alert-saas-info mb-4">
                                    <i class="fas fa-circle-info fs-5" aria-hidden="true"></i>
                                    <div>Setelah mendaftar, akun Anda akan diverifikasi oleh Ketua RT sesuai data kependudukan.</div>
                                </div>
                                
                                <button type="submit" class="btn-submit-saas">
                                    <i class="fas fa-user-plus" aria-hidden="true"></i> Daftar Sebagai Warga
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab Switching
        const tabBtns = document.querySelectorAll('.tab-btn');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;
                
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                if (tab === 'login') {
                    loginForm.style.display = 'block';
                    registerForm.style.display = 'none';
                } else {
                    loginForm.style.display = 'none';
                    registerForm.style.display = 'block';
                }
            });
        });
        
        // Check URL hash for tab
        if (window.location.hash === '#register') {
            const regBtn = document.querySelector('.tab-btn[data-tab="register"]');
            if (regBtn) regBtn.click();
        }

        // Toggle password visibility
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const targetId = toggle.dataset.target;
                const input = document.getElementById(targetId);
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = toggle.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
                toggle.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            });
        });

        // Demo account auto-fill
        function fillLogin(email, password) {
            const emailInput = document.querySelector('#loginForm input[name="email"]');
            const passInput = document.getElementById('loginPassword');
            if (emailInput) {
                emailInput.value = email;
            }
            if (passInput) {
                passInput.value = password;
            }
        }
    </script>
</body>
</html>
