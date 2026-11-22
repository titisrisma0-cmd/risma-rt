<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#147A45" id="metaThemeColor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Autentikasi') | SMART RW</title>
    
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
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SMART RW Motion Graphics Engine -->
    <link href="{{ asset('css/motion.css') }}" rel="stylesheet">
    
    <style>
        :root,
        [data-bs-theme="light"],
        [data-theme="light"] {
            color-scheme: light;
            --bg: #F0F2F5;
            --surface: #ffffff;
            --surface-2: #E7FCEB;
            --border: #E9EDEF;
            --border-focus: #00A884;
            --ink: #111B21;
            --ink-soft: #3B4A54;
            --ink-mute: #54656F;
            --primary: #00A884;
            --primary-dark: #008F6F;
            --primary-soft: #E7FCEB;
            --accent: #f4a62a;
            --accent-soft: #fdf0d8;
            --danger: #EA0038;
            --danger-soft: #FDE8EC;
            --info: #027EB5;
            --info-soft: #E5F6FD;
            --on-primary: #ffffff;
            --radius-btn: 14px;
            --radius-input: 12px;
            --radius-card: 24px;
            --shadow-soft: 0 16px 36px rgba(11, 20, 26, 0.08);
            --shadow-card: 0 12px 30px rgba(11, 20, 26, 0.06);
        }

        [data-bs-theme="dark"],
        [data-theme="dark"] {
            color-scheme: dark;
            --bg: #0C1317;
            --surface: #111B21;
            --surface-2: #152A22;
            --border: #222E35;
            --border-focus: #00A884;
            --ink: #E9EDEF;
            --ink-soft: #D1D7DB;
            --ink-mute: #8696A0;
            --primary: #00A884;
            --primary-dark: #00C89C;
            --primary-soft: #18372B;
            --accent: #f4b13f;
            --accent-soft: #3a2d12;
            --danger: #F15C6D;
            --danger-soft: #301920;
            --info: #53BDEB;
            --info-soft: #162C3A;
            --on-primary: #0b1511;
            --shadow-soft: 0 16px 36px rgba(0, 0, 0, 0.45);
            --shadow-card: 0 12px 30px rgba(0, 0, 0, 0.40);
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
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 15px;
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
        }

        /* Subtle Ambient Background Decoration */
        .auth-bg-decor {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden !important;
            contain: paint;
            clip-path: inset(0);
            z-index: 0;
            max-width: 100vw;
        }

        .auth-bg-blob-1 {
            position: absolute;
            top: 0;
            right: 0;
            width: clamp(200px, 35vw, 380px);
            height: clamp(200px, 35vw, 380px);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 122, 75, 0.12) 0%, rgba(14, 122, 75, 0) 70%);
            filter: blur(40px);
            max-width: 100%;
        }

        .auth-bg-blob-2 {
            position: absolute;
            bottom: 0;
            left: 0;
            width: clamp(200px, 35vw, 380px);
            height: clamp(200px, 35vw, 380px);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(47, 191, 127, 0.10) 0%, rgba(47, 191, 127, 0) 70%);
            filter: blur(50px);
            max-width: 100%;
        }

        /* Top Utility Navbar */
        .auth-topbar {
            position: relative;
            z-index: 10;
            width: 100%;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ink);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
            transition: opacity 0.2s ease;
        }

        .auth-brand:hover {
            opacity: 0.9;
            color: var(--ink);
        }

        .auth-brand-text {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .auth-brand-text span {
            color: var(--primary);
        }

        .theme-toggle-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--ink);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 42, 29, 0.05);
            transition: all 0.2s ease;
        }

        .theme-toggle-pill:hover {
            background: var(--surface-2);
            border-color: var(--primary);
            color: var(--primary);
            transform: scale(1.05);
        }

        /* Centered Auth Main Container */
        .auth-main {
            position: relative;
            z-index: 10;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px 40px;
            width: 100%;
        }

        .auth-card-wrapper {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            animation: authFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes authFadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-card {
            background: var(--surface);
            border-radius: var(--radius-card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-soft);
            padding: 38px 34px;
            position: relative;
            overflow: hidden;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 28px 20px;
                border-radius: 18px;
            }
        }

        /* Header in Card */
        .auth-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: var(--primary-soft);
            color: var(--primary);
            display: grid;
            place-items: center;
            font-size: 1.5rem;
            margin: 0 auto 18px;
            border: 1px solid rgba(14, 122, 75, 0.18);
            box-shadow: 0 4px 14px rgba(14, 122, 75, 0.12);
        }

        .auth-card-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--ink);
            text-align: center;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .auth-card-desc {
            font-size: 0.9rem;
            color: var(--ink-soft);
            text-align: center;
            line-height: 1.55;
            margin-bottom: 24px;
        }

        /* Form Controls - Vertical Stacking & Proportional */
        .form-label-custom {
            font-size: 0.86rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            display: flex;
            align-items: center;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-input);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
            min-height: 48px;
            height: 48px;
            width: 100%;
        }

        .input-group-custom:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 122, 75, 0.20);
        }

        .input-group-custom .input-icon {
            width: 44px;
            display: grid;
            place-items: center;
            color: var(--ink-mute);
            font-size: 1rem;
            flex-shrink: 0;
            transition: color 0.2s ease;
        }

        .input-group-custom:focus-within .input-icon {
            color: var(--primary);
        }

        .input-group-custom input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            padding: 0 14px 0 0;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--ink);
            width: 100%;
        }

        .input-group-custom input::placeholder {
            color: var(--ink-mute);
            opacity: 0.75;
        }

        .btn-toggle-password {
            background: transparent;
            border: none;
            color: var(--ink-mute);
            padding: 0 14px;
            height: 100%;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: color 0.2s ease;
        }

        .btn-toggle-password:hover {
            color: var(--primary);
        }

        /* Buttons */
        .btn-primary-custom {
            background: linear-gradient(135deg, #0e7a4b 0%, #0a5c38 100%);
            color: #ffffff !important;
            border: none;
            padding: 13px 20px;
            border-radius: var(--radius-btn);
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(14, 122, 75, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            text-decoration: none;
        }

        [data-bs-theme="dark"] .btn-primary-custom,
        [data-theme="dark"] .btn-primary-custom {
            background: linear-gradient(135deg, #2fbf7f 0%, #1db36f 100%);
            color: #0b1511 !important;
            box-shadow: 0 8px 20px rgba(47, 191, 127, 0.28);
        }

        .btn-primary-custom:hover {
            filter: brightness(1.06);
            box-shadow: 0 10px 22px rgba(14, 122, 75, 0.32);
            transform: translateY(-1px);
        }

        .btn-primary-custom:active {
            transform: scale(0.98);
        }

        .btn-secondary-custom {
            background: var(--surface-2);
            color: var(--ink) !important;
            border: 1.5px solid var(--border);
            padding: 12px 20px;
            border-radius: var(--radius-btn);
            font-weight: 600;
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            cursor: pointer;
            transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
            text-decoration: none;
        }

        .btn-secondary-custom:hover {
            background: var(--border);
            color: var(--ink);
            transform: translateY(-1px);
        }

        /* Alerts */
        .alert-custom {
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            line-height: 1.5;
        }

        .alert-custom-success {
            background: var(--primary-soft);
            border: 1px solid rgba(14, 122, 75, 0.28);
            color: var(--primary-dark);
        }

        [data-bs-theme="dark"] .alert-custom-success {
            color: #72e0a8;
        }

        .alert-custom-danger {
            background: var(--danger-soft);
            border: 1px solid rgba(229, 72, 58, 0.28);
            color: var(--danger);
        }

        .alert-custom-info {
            background: var(--info-soft);
            border: 1px solid rgba(42, 111, 219, 0.28);
            color: var(--info);
        }

        /* 6-Digit OTP Box Grid */
        .otp-box-grid {
            display: flex;
            gap: 8px;
            justify-content: space-between;
            margin: 22px 0;
            width: 100%;
        }

        .otp-digit-input {
            width: clamp(42px, 13vw, 56px);
            height: clamp(50px, 15vw, 62px);
            border: 2px solid var(--border);
            background: var(--surface);
            border-radius: 12px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(15, 42, 29, 0.04);
            font-family: 'Plus Jakarta Sans', monospace;
        }

        .otp-digit-input:focus {
            border-color: var(--primary);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(14, 122, 75, 0.20);
            transform: translateY(-2px);
        }

        .otp-digit-input.filled {
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        /* Footer */
        .auth-footer {
            position: relative;
            z-index: 10;
            padding: 16px 24px;
            text-align: center;
            font-size: 0.82rem;
            color: var(--ink-mute);
        }

        .auth-footer a {
            color: var(--ink-soft);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            color: var(--primary);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Ambient Background blobs -->
    <div class="auth-bg-decor" aria-hidden="true">
        <div class="auth-bg-blob-1"></div>
        <div class="auth-bg-blob-2"></div>
    </div>

    <!-- Top Utility Bar -->
    <header class="auth-topbar">
        <a href="{{ url('/') }}" class="auth-brand" title="Menuju Beranda SMART RW">
            <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="SMART RW Icon" width="34" height="34" class="flex-shrink-0">
            <div class="auth-brand-text">
                SMART <span>RW</span>
            </div>
        </a>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="theme-toggle-pill" id="themeToggleBtn" title="Ganti Tema Terang/Gelap" aria-label="Toggle Theme">
                <i class="fas fa-moon" id="themeToggleIcon"></i>
            </button>
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-none d-sm-inline-flex align-items-center gap-1.5" style="border-color: var(--border); color: var(--ink-soft);">
                <i class="fas fa-arrow-left small"></i> Halaman Masuk
            </a>
        </div>
    </header>

    <!-- Centered Content Area -->
    <main class="auth-main">
        <div class="auth-card-wrapper">
            @yield('content')
        </div>
    </main>

    <!-- Footer Copyright -->
    <footer class="auth-footer">
        <div>
            &copy; {{ date('Y') }} <strong>SMART RW 01</strong> &bull; Sistem Layanan Warga Digital Terpadu
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Theme toggle logic
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeToggleIcon');
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            
            function updateIcon(theme) {
                if (!icon) return;
                if (theme === 'dark') {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }

            updateIcon(currentTheme);

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    const activeTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                    const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
                    
                    document.documentElement.setAttribute('data-bs-theme', nextTheme);
                    document.documentElement.setAttribute('data-theme', nextTheme);
                    document.documentElement.style.colorScheme = nextTheme;
                    localStorage.setItem('risma-theme', nextTheme);

                    const metaTheme = document.getElementById('metaThemeColor');
                    if (metaTheme) metaTheme.setAttribute('content', nextTheme === 'dark' ? '#12201a' : '#147A45');

                    updateIcon(nextTheme);
                });
            }
        });
    </script>
    @include('partials.whatsapp-help')
    @stack('scripts')
</body>
</html>
