<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login & Register | RW Smart</title>
    <style>
    </style>
    @include('partials.whatsapp-help')
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', localStorage.getItem('risma-theme') || 'dark');
        })();
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --page-bg: radial-gradient(circle at top right, #dbe9e1 0%, #eef3ed 45%, #f5f7f2 100%);
            --card-bg: rgba(245, 247, 242, 0.9);
            --card-border: rgba(20, 132, 110, 0.22);
            --card-shadow: 0 25px 50px -12px rgba(14, 45, 59, 0.2);
            --info-bg: linear-gradient(135deg, rgba(20, 132, 110, 0.88), rgba(88, 173, 112, 0.72));
            --info-glow: radial-gradient(circle, rgba(255, 201, 71, 0.15) 0%, transparent 70%);
            --form-bg: rgba(238, 243, 237, 0.82);
            --text-primary: #17394a;
            --text-strong: #113527;
            --text-muted: #4b6573;
            --text-soft: #5f7885;
            --input-bg: #f8faf6;
            --input-bg-focus: #eef3ed;
            --input-border: rgba(20, 132, 110, 0.2);
            --accent: #0f9f75;
            --accent-strong: #0b6f55;
            --accent-soft: rgba(15, 159, 117, 0.14);
            --button-bg: linear-gradient(135deg, #0f9f75, #64c85b);
            --button-text: #ffffff;
            --button-shadow: rgba(15, 159, 117, 0.32);
            --warning-bg: rgba(255, 193, 7, 0.14);
            --warning-border: rgba(255, 193, 7, 0.36);
            --warning-text: #996f00;
            --decoration-gold: #ffc947;
            --decoration-green: #2d8659;
        }

        :root[data-theme="dark"] {
            --page-bg: radial-gradient(circle at top right, #17394a 0%, #0b1d28 40%, #07131d 100%);
            --card-bg: rgba(26, 95, 63, 0.15);
            --card-border: rgba(255, 215, 0, 0.2);
            --card-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --info-bg: linear-gradient(135deg, rgba(26, 95, 63, 0.8), rgba(45, 134, 89, 0.6));
            --info-glow: radial-gradient(circle, rgba(255, 215, 0, 0.05) 0%, transparent 70%);
            --form-bg: rgba(11, 29, 40, 0.3);
            --text-primary: #fffbf0;
            --text-strong: #fffbf0;
            --text-muted: #cbd5e1;
            --text-soft: #94a3b8;
            --input-bg: rgba(255, 251, 240, 0.1);
            --input-bg-focus: rgba(255, 251, 240, 0.15);
            --input-border: rgba(255, 215, 0, 0.2);
            --accent: #ffd700;
            --accent-strong: #ffd700;
            --accent-soft: rgba(255, 215, 0, 0.2);
            --button-bg: linear-gradient(135deg, #ffd700, #ffed4e);
            --button-text: #1a5f3f;
            --button-shadow: rgba(255, 215, 0, 0.4);
            --warning-bg: rgba(255, 215, 0, 0.1);
            --warning-border: rgba(255, 215, 0, 0.3);
            --warning-text: #ffd700;
            --decoration-gold: #ffd700;
            --decoration-green: #2d8659;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            transition: background 0.3s ease;
        }
        
        /* Ornamental Decorations */
        .decoration {
            position: fixed;
            pointer-events: none;
            z-index: 0;
        }
        
        .lantern-left {
            top: 20px;
            left: 30px;
            font-size: 4rem;
            color: var(--decoration-gold);
            animation: float 4s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.4));
        }
        
        .lantern-right {
            top: 10px;
            right: 40px;
            font-size: 5rem;
            color: var(--decoration-gold);
            animation: float 3.5s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.4));
        }
        
        .moon {
            top: 50px;
            right: 120px;
            font-size: 3rem;
            color: var(--decoration-gold);
            animation: twinkle 3s ease-in-out infinite;
            filter: drop-shadow(0 0 15px rgba(255, 237, 74, 0.3));
        }

        :root:not([data-theme="dark"]) .moon i::before {
            content: "\f185";
        }
        
        .stars {
            font-size: 1.2rem;
            color: var(--decoration-gold);
            animation: twinkle 2s ease-in-out infinite;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.3));
        }
        
        .star-1 { top: 60px; left: 100px; }
        .star-2 { top: 100px; right: 200px; }
        .star-3 { top: 150px; left: 80px; }
        .star-4 { bottom: 100px; right: 100px; }
        
        .leaves {
            font-size: 2.5rem;
            color: var(--decoration-green);
            opacity: 0.6;
            animation: float 5s ease-in-out infinite;
        }
        
        .leaf-1 { top: 200px; left: 50px; }
        .leaf-2 { bottom: 150px; left: 100px; }
        .leaf-3 { top: 300px; right: 50px; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes twinkle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .auth-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 2px solid var(--card-border);
            max-width: 1100px;
            width: 100%;
        }
        
        /* Left Side - Info Panel */
        .info-panel {
            background: var(--info-bg);
            padding: 48px;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .info-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: var(--info-glow);
            pointer-events: none;
        }
        
        .brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--accent);
            margin-bottom: 40px;
            display: inline-block;
        }
        
        .brand i {
            margin-right: 10px;
        }
        
        .info-panel h3 {
            font-family: 'Poppins', sans-serif;
            color: #fffbf0;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .info-panel h3 .gold-text {
            color: #ffd700;
        }
        
        .info-panel > p {
            color: #edf7f2;
            font-size: 0.95rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .feature-list {
            margin-top: 40px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.16);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .feature-icon i {
            font-size: 1.5rem;
            color: #ffd700;
        }
        
        .feature-text h6 {
            font-weight: 600;
            margin-bottom: 5px;
            color: #fff1a6;
            font-size: 0.95rem;
        }
        
        .feature-text p {
            font-size: 0.8rem;
            color: #e5f5ed;
            margin: 0;
        }
        
        /* Right Side - Form Panel */
        .form-panel {
            padding: 48px;
            background: var(--form-bg);
        }

        .auth-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--input-border);
            padding-bottom: 15px;
        }
        
        .tab-buttons {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 999px;
            color: var(--text-primary);
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1;
            padding: 8px 11px;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .theme-toggle:hover {
            border-color: var(--accent);
            color: var(--accent-strong);
        }

        .theme-toggle .theme-icon {
            color: var(--accent);
            width: 16px;
        }
        
        .tab-btn {
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-soft);
            padding: 8px 0;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab-btn.active {
            color: var(--accent);
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -16px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }
        
        .tab-btn:hover {
            color: var(--accent);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: var(--text-strong);
        }
        
        .input-group-custom {
            display: flex;
            align-items: center;
            background: var(--input-bg);
            border-radius: 12px;
            border: 1px solid var(--input-border);
            transition: all 0.3s;
        }
        
        .input-group-custom:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
            background: var(--input-bg-focus);
        }
        
        .input-group-custom span {
            padding: 0 15px;
            color: var(--text-soft);
        }
        
        .input-group-custom input,
        .input-group-custom select {
            background: transparent;
            border: none;
            padding: 14px 15px 14px 0;
            width: 100%;
            color: var(--text-primary);
            outline: none;
        }
        
        .input-group-custom input::placeholder {
            color: var(--text-soft);
            opacity: 0.85;
        }

        .input-group-custom select option {
            color: #17394a;
        }

        .password-toggle {
            background: none;
            border: none;
            color: var(--text-soft);
            padding: 0 15px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover {
            color: var(--accent);
        }
        
        .form-check-input {
            background-color: transparent !important;
            border: 1px solid var(--accent) !important;
        }
        
        .form-check-input:checked {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
        }
        
        .btn-submit {
            width: 100%;
            background: var(--button-bg);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            color: var(--button-text);
            transition: all 0.3s;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--button-shadow);
            color: var(--button-text);
        }
        
        .demo-accounts {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--input-border);
        }
        
        .demo-accounts h6 {
            font-size: 0.8rem;
            color: var(--accent);
            margin-bottom: 12px;
        }
        
        .demo-item {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 8px;
            cursor: pointer;
        }
        
        .demo-item strong {
            color: var(--accent);
        }
        
        .alert-custom {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #f87171;
        }
        
        .alert-success-custom {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }

        .info-note {
            background: var(--warning-bg);
            border-color: var(--warning-border);
            color: var(--warning-text);
            margin-top: 15px;
        }
        
        .form-check-label {
            color: var(--text-muted);
        }
        
        a {
            color: var(--accent);
        }
        
        @media (max-width: 768px) {
            .info-panel {
                display: none;
            }
            .form-panel {
                padding: 32px;
            }
            .auth-top {
                align-items: center;
            }
            .decoration {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .auth-top {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Ornamental Decorations -->
    <div class="decoration lantern-left"><i class="fas fa-mosque"></i></div>
    <div class="decoration lantern-right"><i class="fas fa-lantern"></i></div>
    <div class="decoration moon"><i class="fas fa-moon"></i></div>
    <div class="decoration stars star-1"><i class="fas fa-star"></i></div>
    <div class="decoration stars star-2"><i class="fas fa-star"></i></div>
    <div class="decoration stars star-3"><i class="fas fa-star"></i></div>
    <div class="decoration stars star-4"><i class="fas fa-star"></i></div>
    <div class="decoration leaves leaf-1"><i class="fas fa-leaf"></i></div>
    <div class="decoration leaves leaf-2"><i class="fas fa-leaf"></i></div>
    <div class="decoration leaves leaf-3"><i class="fas fa-leaf"></i></div>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="row g-0">
                <!-- Left Panel - Info & Features -->
                <div class="col-lg-6">
                    <div class="info-panel">
                        <a href="{{ url('/') }}" class="brand">
                            <i class="fas fa-home"></i> RW Smart
                        </a>
                        
                        <h3>Kelola Iuran RW<br><span class="gold-text">Lebih Mudah</span></h3>
                        <p>
                            Platform digital untuk mengelola iuran warga, pembayaran, dan laporan keuangan RW secara real-time.
                        </p>
                        
                        <div class="feature-list">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Laporan Real-time</h6>
                                    <p>Pantau keuangan RW langsung</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-file-excel"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Export Laporan</h6>
                                    <p>Excel & PDF siap cetak</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Mobile Friendly</h6>
                                    <p>Akses dari smartphone</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Aman & Terpercaya</h6>
                                    <p>Data terjaga keamanannya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Panel - Login & Register Forms -->
                <div class="col-lg-6">
                    <div class="form-panel">
                        <!-- Tab Buttons -->
                        <div class="auth-top">
                            <div class="tab-buttons">
                                <button class="tab-btn active" data-tab="login">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                                <button class="tab-btn" data-tab="register">
                                    <i class="fas fa-user-plus"></i> Daftar
                                </button>
                            </div>
                            <button type="button" class="theme-toggle" id="themeToggle" aria-label="Ganti ke mode siang">
                                <i class="fas fa-moon theme-icon"></i>
                                <span>Mode Malam</span>
                            </button>
                        </div>
                        
                        <!-- Login Form -->
                        <div id="loginForm" class="auth-form active">
                            @if($errors->any() && !session('register_success'))
                                <div class="alert-custom">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ $errors->first() }}
                                </div>
                            @endif
                            
                            @if(session('success'))
                                <div class="alert-custom alert-success-custom">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert-custom">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" placeholder="contoh@email.com" required autofocus>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Password</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" id="loginPassword" placeholder="••••••••" required>
                                        <button type="button" class="password-toggle" aria-label="Tampilkan password" data-target="loginPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember" style="font-size: 0.8rem;">Ingat saya</label>
                                    </div>
                                    <a href="#" style="font-size: 0.8rem;">Lupa password?</a>
                                </div>
                                
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-sign-in-alt"></i> Masuk
                                </button>
                            </form>
                            
                            <div class="demo-accounts">
                                <h6><i class="fas fa-laptop-code"></i> Akun Demo</h6>
                                <div class="demo-item" role="button" tabindex="0" onclick="fillLogin('admin@rw-smart.test', 'password123')"><strong>Admin / RW:</strong> admin@rw-smart.test / password123</div>
                                <div class="demo-item" role="button" tabindex="0" onclick="fillLogin('rt01@rw-smart.test', 'password123')"><strong>Ketua RT 01:</strong> rt01@rw-smart.test / password123</div>
                                <div class="demo-item" role="button" tabindex="0" onclick="fillLogin('rt03@rw-smart.test', 'password123')"><strong>Ketua RT 03:</strong> rt03@rw-smart.test / password123</div>
                                <div class="demo-item" role="button" tabindex="0" onclick="fillLogin('bendahara@rw-smart.test', 'password123')"><strong>Bendahara:</strong> bendahara@rw-smart.test / password123</div>
                                <div class="demo-item" role="button" tabindex="0" onclick="fillLogin('warga@rw-smart.test', 'password123')"><strong>Warga:</strong> warga@rw-smart.test / password123</div>
                            </div>
                        </div>
                        
                        <!-- Register Form -->
                        <div id="registerForm" class="auth-form" style="display: none;">
                            @if($errors->any() && !session('register_success'))
                                <div class="alert-custom">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-user"></i></span>
                                        <input type="text" name="name" placeholder="Nama lengkap" value="{{ old('name') }}" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Nomor Kartu Keluarga</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-id-card"></i></span>
                                        <input type="text" name="no_kk" placeholder="Nomor Kartu Keluarga" value="{{ old('no_kk') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Nomor Telepon</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-phone"></i></span>
                                        <input type="text" name="no_hp" placeholder="Nomor telepon" value="{{ old('no_hp') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Jenis Kelamin</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-venus-mars"></i></span>
                                        <select name="jenis_kelamin" required>
                                            <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih jenis kelamin</option>
                                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Email Address</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" placeholder="contoh@email.com" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Password</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" placeholder="Minimal 8 karakter" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Konfirmasi Password</label>
                                    <div class="input-group-custom">
                                        <span><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
                                    </div>
                                </div>
                                
                                <div class="alert-custom info-note">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Setelah mendaftar, akun Anda akan diverifikasi oleh Admin RW.</small>
                                </div>
                                
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Theme Switching
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = themeToggle.querySelector('.theme-icon');
        const themeLabel = themeToggle.querySelector('span');

        function setTheme(theme) {
            const isDark = theme === 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('risma-theme', theme);
            themeIcon.classList.toggle('fa-moon', isDark);
            themeIcon.classList.toggle('fa-sun', !isDark);
            themeLabel.textContent = isDark ? 'Mode Malam' : 'Mode Siang';
            themeToggle.setAttribute('aria-label', isDark ? 'Ganti ke mode siang' : 'Ganti ke mode malam');
        }

        setTheme(document.documentElement.getAttribute('data-theme') || 'dark');

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });

        // Tab Switching
        const tabBtns = document.querySelectorAll('.tab-btn');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;
                
                // Update active tab button
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Show/hide forms
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
            document.querySelector('.tab-btn[data-tab="register"]').click();
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
            const emailInput = document.querySelector('#loginForm input[name="email"]') || document.querySelector('input[name="email"]');
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
