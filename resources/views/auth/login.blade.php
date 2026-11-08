<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk & Pendaftaran | RW SMART</title>
    
    @include('partials.whatsapp-help')
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-green: #147A45;
            --dark-green: #0D5737;
            --soft-green: #E8F5EC;
            --page-bg: #F6F8F7;
            --card-surface: #FFFFFF;
            --main-text: #17221C;
            --secondary-text: #758078;
            --border-color: #E9EEEA;
            --danger-color: #E65353;
            --warning-color: #F2B84B;
            --info-color: #3985E8;
            --radius-card: 16px;
            --radius-input: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: var(--page-bg);
            color: var(--main-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 1060px;
            margin: auto;
        }

        .auth-card {
            background: var(--card-surface);
            border-radius: var(--radius-card);
            border: 1px solid var(--border-color);
            box-shadow: 0 16px 40px -12px rgba(20, 122, 69, 0.08), 0 4px 16px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        /* Left Brand Showcase */
        .brand-panel {
            background: linear-gradient(145deg, #0D5737 0%, #147A45 100%);
            color: #FFFFFF;
            padding: 48px;
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
            background: rgba(255, 255, 255, 0.05);
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
            letter-spacing: -0.5px;
            color: #FFFFFF;
            text-decoration: none;
            margin-bottom: 32px;
        }

        .brand-logo-icon {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-heading {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 14px;
        }

        .brand-description {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.6;
            margin-bottom: 36px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }

        .feature-icon-box {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-size: 1rem;
            color: #FFFFFF;
            flex-shrink: 0;
        }

        .feature-title {
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .feature-sub {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.75);
            margin: 0;
        }

        .brand-footer {
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Right Form Panel */
        .form-panel {
            padding: 44px 48px;
            background: var(--card-surface);
        }

        .auth-nav-tabs {
            display: flex;
            gap: 8px;
            background: var(--page-bg);
            padding: 5px;
            border-radius: 12px;
            margin-bottom: 28px;
            border: 1px solid var(--border-color);
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--secondary-text);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tab-btn.active {
            background: var(--card-surface);
            color: var(--primary-green);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            font-weight: 700;
        }

        .form-label-custom {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--main-text);
            margin-bottom: 6px;
            display: block;
        }

        .input-group-saas {
            display: flex;
            align-items: center;
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-input);
            transition: all 0.2s ease-in-out;
            overflow: hidden;
        }

        .input-group-saas:focus-within {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(20, 122, 69, 0.12);
        }

        .input-icon {
            padding: 0 14px;
            color: var(--secondary-text);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .input-group-saas input,
        .input-group-saas select {
            width: 100%;
            border: none;
            padding: 11px 14px 11px 0;
            font-size: 0.9rem;
            color: var(--main-text);
            background: transparent;
            outline: none;
        }

        .input-group-saas input::placeholder {
            color: #9ca3af;
        }

        .password-toggle {
            background: none;
            border: none;
            padding: 0 14px;
            color: var(--secondary-text);
            cursor: pointer;
            font-size: 0.95rem;
        }

        .password-toggle:hover {
            color: var(--main-text);
        }

        .btn-submit-saas {
            width: 100%;
            background-color: var(--primary-green);
            border: 1px solid var(--primary-green);
            color: #FFFFFF;
            padding: 12px;
            border-radius: var(--radius-input);
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit-saas:hover {
            background-color: var(--dark-green);
            border-color: var(--dark-green);
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(20, 122, 69, 0.2);
        }

        .form-check-input:checked {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        /* Demo Account Pills */
        .demo-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-color);
        }

        .demo-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--secondary-text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .demo-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .demo-pill {
            background: var(--page-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 0.76rem;
            color: var(--main-text);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }

        .demo-pill:hover {
            background: var(--soft-green);
            border-color: var(--primary-green);
            color: var(--primary-green);
        }

        .demo-pill strong {
            color: var(--primary-green);
        }

        /* Alerts */
        .alert-saas {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-saas-danger {
            background: #FDF2F2;
            border: 1px solid #F8D7DA;
            color: var(--danger-color);
        }

        .alert-saas-success {
            background: var(--soft-green);
            border: 1px solid #C3E6CB;
            color: var(--primary-green);
        }

        .alert-saas-info {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            color: var(--info-color);
        }

        @media (max-width: 991px) {
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
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <div>
                                        <h6 class="feature-title">Keuangan & Kas Real-Time</h6>
                                        <p class="feature-sub">Pencatatan iuran, mutasi kas, dan audit trail otomatis</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fas fa-file-signature"></i>
                                    </div>
                                    <div>
                                        <h6 class="feature-title">Layanan Surat & Permohonan</h6>
                                        <p class="feature-sub">Pengajuan surat pengantar warga dengan verifikasi bertingkat</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fas fa-shield-halved"></i>
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
                                <i class="fas fa-right-to-bracket"></i> Masuk Akun
                            </button>
                            <button class="tab-btn" data-tab="register" type="button">
                                <i class="fas fa-user-plus"></i> Pendaftaran Warga
                            </button>
                        </div>
                        
                        <!-- Login Form -->
                        <div id="loginForm" class="auth-form active">
                            @if($errors->any() && !session('register_success'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5"></i>
                                    <div>{{ $errors->first() }}</div>
                                </div>
                            @endif
                            
                            @if(session('success'))
                                <div class="alert-saas alert-saas-success">
                                    <i class="fas fa-circle-check fs-5"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label-custom">Alamat Email</label>
                                    <div class="input-group-saas">
                                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" placeholder="nama@email.com" required autofocus value="{{ old('email') }}">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label-custom">Kata Sandi</label>
                                    <div class="input-group-saas">
                                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" id="loginPassword" placeholder="••••••••" required>
                                        <button type="button" class="password-toggle" aria-label="Tampilkan password" data-target="loginPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                        <label class="form-check-label small text-secondary" for="remember">Ingat Saya</label>
                                    </div>
                                    <a href="#" class="small text-decoration-none" style="color: var(--primary-green);">Lupa password?</a>
                                </div>
                                
                                <button type="submit" class="btn-submit-saas">
                                    <i class="fas fa-right-to-bracket"></i> Masuk ke Dashboard
                                </button>
                            </form>
                            
                            <!-- Demo Accounts Toolbar -->
                            <div class="demo-section">
                                <div class="demo-title">
                                    <i class="fas fa-laptop-code"></i> Akun Pengujian Cepat (Klik untuk Isi Otomatis)
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
                                    <i class="fas fa-circle-exclamation fs-5"></i>
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
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Nama Lengkap Sesuai KTP <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-user"></i></span>
                                            <input type="text" name="name" placeholder="Nama lengkap" value="{{ old('name') }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Nomor Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-id-card"></i></span>
                                            <input type="text" name="no_kk" placeholder="16 digit No. KK" value="{{ old('no_kk') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-phone"></i></span>
                                            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}" required>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-venus-mars"></i></span>
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
                                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" placeholder="nama@email.com" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Kata Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                                            <input type="password" name="password" placeholder="Min. 8 karakter" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Konfirmasi Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group-saas">
                                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                                            <input type="password" name="password_confirmation" placeholder="Ulangi sandi" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert-saas alert-saas-info mb-4">
                                    <i class="fas fa-circle-info fs-5"></i>
                                    <div>Setelah mendaftar, akun Anda akan diverifikasi oleh Admin RW sesuai data kependudukan.</div>
                                </div>
                                
                                <button type="submit" class="btn-submit-saas">
                                    <i class="fas fa-user-plus"></i> Daftar Sebagai Warga
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
