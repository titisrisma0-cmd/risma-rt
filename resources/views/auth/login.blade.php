<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#147A45" id="metaThemeColor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk & Pendaftaran | SMART RW</title>
    
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
    
    @include('partials.whatsapp-help')
    
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
            --bg: #f7faf8;
            --surface: #ffffff;
            --surface-2: #f0f5f2;
            --border: #dbe9e0;
            --ink: #0f2a1d;
            --ink-soft: #4b6355;
            --ink-mute: #6f8578;
            --primary: #0e7a4b;
            --primary-dark: #0a5c38;
            --primary-soft: #e3f4ea;
            --accent: #f4a62a;
            --accent-soft: #fdf0d8;
            --danger: #e5483a;
            --danger-soft: #fdeeee;
            --info: #2a6fdb;
            --info-soft: #eaf1fc;
            --on-primary: #ffffff;
            --radius-btn: 12px;
            --radius-input: 12px;
            --radius-card: 22px;
            --shadow-soft: 0 16px 36px rgba(15, 42, 29, 0.10);
        }

        [data-bs-theme="dark"],
        [data-theme="dark"] {
            color-scheme: dark;
            --bg: #0b1511;
            --surface: #12201a;
            --surface-2: #172a21;
            --border: #223a2e;
            --ink: #eaf5ee;
            --ink-soft: #b7cbbf;
            --ink-mute: #8aa395;
            --primary: #2fbf7f;
            --primary-dark: #1db36f;
            --primary-soft: #143325;
            --accent: #f4b13f;
            --accent-soft: #3a2d12;
            --danger: #e5483a;
            --danger-soft: #3a1916;
            --info: #3d86f5;
            --info-soft: #14283d;
            --on-primary: #0b1511;
            --shadow-soft: 0 16px 36px rgba(0, 0, 0, 0.45);
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
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            font-size: 15px;
            line-height: 1.6;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 1100px;
            margin: auto;
            animation: authRise 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes authRise {
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

        /* ==================== LEFT BRAND SHOWCASE PANEL (DESKTOP) ==================== */
        .brand-panel {
            background: linear-gradient(155deg, #0a5c38 0%, #0e7a4b 100%);
            color: #ffffff;
            padding: 44px 38px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
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
            margin-bottom: 24px;
        }

        .brand-header .brand-title {
            white-space: nowrap;
            color: #ffffff;
            font-weight: 800;
        }

        .brand-header .brand-title span {
            color: #F2D179;
        }

        .brand-heading {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.25;
            color: #ffffff;
            letter-spacing: -0.025em;
            margin-bottom: 12px;
        }

        .brand-description {
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* Dual-Scene Animation Stage */
        .stage-container {
            position: relative;
            width: 100%;
            height: 180px;
            border-radius: 16px;
            background: rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.14);
            margin-bottom: 24px;
            overflow: hidden !important;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.2);
            contain: paint;
        }

        .stage-scene {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            pointer-events: none;
            overflow: hidden !important;
            transform: scale(0.97);
            transition: opacity 0.4s cubic-bezier(0.2, 0.9, 0.3, 1), transform 0.4s cubic-bezier(0.2, 0.9, 0.3, 1);
            contain: paint;
        }

        .stage-scene.active {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
        }

        .stage-svg {
            width: 100%;
            height: 100%;
            display: block;
            overflow: hidden !important;
            max-width: 100% !important;
        }

        /* SVG Micro-Animations */
        .star-1 { animation: starPulse 2.4s ease-in-out infinite; }
        .star-2 { animation: starPulse 3.1s ease-in-out infinite 0.7s; }
        .star-3 { animation: starPulse 2.8s ease-in-out infinite 1.4s; }

        @keyframes starPulse {
            0%, 100% { opacity: 0.35; transform-origin: center; transform: scale(0.85); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .cloud-1 { animation: cloudShift 18s linear infinite alternate; }
        .cloud-2 { animation: cloudShift 24s linear infinite alternate-reverse; }
        .day-cloud-1 { animation: cloudShift 16s linear infinite alternate; }
        .day-cloud-2 { animation: cloudShift 22s linear infinite alternate-reverse; }

        @keyframes cloudShift {
            0% { transform: translateX(0); }
            100% { transform: translateX(24px); }
        }

        .window-glow { animation: winGlow 3.5s ease-in-out infinite alternate; }
        @keyframes winGlow {
            0% { opacity: 0.7; }
            100% { opacity: 1; filter: drop-shadow(0 0 6px rgba(255, 234, 167, 0.9)); }
        }

        .door-opening { animation: doorGlow 4s ease-in-out infinite alternate; }
        @keyframes doorGlow {
            0% { opacity: 0.8; }
            100% { opacity: 1; filter: drop-shadow(0 0 8px rgba(244, 166, 42, 0.95)); }
        }

        .shield-halo { animation: shieldPulse 3s ease-in-out infinite; transform-origin: center; }
        @keyframes shieldPulse {
            0%, 100% { transform: scale(1); opacity: 0.25; }
            50% { transform: scale(1.4); opacity: 0.75; }
        }

        .anim-coin { animation: coinFloat 2.6s ease-in-out infinite; }
        @keyframes coinFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .anim-mail { animation: mailHover 3s ease-in-out infinite; transform-origin: center; }
        @keyframes mailHover {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-4px) rotate(5deg); }
        }

        .cctv-red-dot { animation: dotBlink 1.4s ease-in-out infinite; }
        @keyframes dotBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.2; }
        }

        .sun-pulse { animation: sunRays 4s ease-in-out infinite alternate; transform-origin: center; }
        @keyframes sunRays {
            0% { transform: scale(0.95); opacity: 0.2; }
            100% { transform: scale(1.25); opacity: 0.55; }
        }

        .kite-group { animation: kiteFly 4.5s ease-in-out infinite; transform-origin: top center; }
        @keyframes kiteFly {
            0%, 100% { transform: translate(290px, 32px) rotate(-5deg); }
            50% { transform: translate(294px, 24px) rotate(7deg); }
        }

        .roof-descend { animation: roofDrop 1s cubic-bezier(0.18, 0.89, 0.32, 1.28) both; }
        @keyframes roofDrop {
            0% { transform: translateY(-24px); opacity: 0; }
            70% { transform: translateY(2px); opacity: 1; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .pin-drop-bounce { animation: pinBounce 1.2s cubic-bezier(0.18, 0.89, 0.32, 1.28) 0.3s both; }
        @keyframes pinBounce {
            0% { transform: translateY(-36px); opacity: 0; }
            60% { transform: translateY(3px); opacity: 1; }
            80% { transform: translateY(-6px); }
            100% { transform: translateY(0); opacity: 1; }
        }

        .pin-ring { animation: pinPulse 2s ease-out infinite 1.2s; }
        @keyframes pinPulse {
            0% { r: 2; opacity: 1; }
            100% { r: 14; opacity: 0; }
        }

        .house-1 { animation: houseScale 0.6s cubic-bezier(0.2, 0.9, 0.3, 1) 0.1s both; }
        .house-2 { animation: houseScale 0.6s cubic-bezier(0.2, 0.9, 0.3, 1) 0.2s both; }
        .house-4 { animation: houseScale 0.6s cubic-bezier(0.2, 0.9, 0.3, 1) 0.3s both; }
        .house-5 { animation: houseScale 0.6s cubic-bezier(0.2, 0.9, 0.3, 1) 0.4s both; }
        @keyframes houseScale {
            0% { transform: scale(0.65); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .waving-arm { animation: armWave 1.8s ease-in-out infinite; transform-origin: bottom left; }
        @keyframes armWave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(18deg); }
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 16px;
        }

        .feature-icon-box {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.14);
            border-radius: 11px;
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            color: #ffffff;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2px;
        }

        .feature-sub {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.82);
            margin: 0;
            line-height: 1.4;
        }

        .brand-footer {
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.82);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ==================== RIGHT FORM PANEL ==================== */
        .form-panel {
            padding: 40px 42px;
            background: var(--surface);
            position: relative;
        }

        /* Mobile Animated Header Strip (<= 900px) */
        .mobile-header-strip {
            display: none;
            background: linear-gradient(155deg, #0a5c38 0%, #0e7a4b 100%);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 22px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(10, 92, 56, 0.2);
        }

        .mobile-header-strip .stage-container {
            height: 105px;
            margin-bottom: 10px;
            background: rgba(0, 0, 0, 0.14);
        }

        /* Tabs with Smooth Sliding Pill Indicator */
        .auth-nav-tabs {
            position: relative;
            display: flex;
            background: var(--bg);
            padding: 5px;
            border-radius: var(--radius-btn);
            margin-bottom: 24px;
            border: 1.5px solid var(--border);
            user-select: none;
        }

        .tab-slider-pill {
            position: absolute;
            top: 5px;
            bottom: 5px;
            left: 5px;
            width: calc(50% - 5px);
            background: var(--surface);
            border-radius: 9px;
            box-shadow: 0 3px 10px rgba(15, 42, 29, 0.09);
            border: 1px solid var(--border);
            transition: transform 0.28s cubic-bezier(0.25, 1, 0.5, 1);
            pointer-events: none;
            z-index: 1;
        }

        .tab-btn {
            position: relative;
            flex: 1;
            background: transparent;
            border: none;
            padding: 10px 14px;
            border-radius: 9px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ink-soft);
            cursor: pointer;
            transition: color 0.2s ease, transform 0.18s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            z-index: 2;
        }

        .tab-btn:hover {
            color: var(--ink);
        }

        .tab-btn.active {
            color: var(--primary);
            font-weight: 800;
        }

        .tab-btn.active i {
            animation: tabIconBounce 0.35s ease;
        }

        @keyframes tabIconBounce {
            0% { transform: scale(1) rotate(0); }
            50% { transform: scale(1.22) rotate(-8deg); }
            100% { transform: scale(1) rotate(0); }
        }

        /* Form Animation Transition */
        .auth-form {
            transition: opacity 0.22s ease, transform 0.25s cubic-bezier(0.2, 0.9, 0.3, 1);
        }

        .auth-form.form-entering {
            animation: formStaggerIn 0.3s cubic-bezier(0.2, 0.9, 0.3, 1) both;
        }

        @keyframes formStaggerIn {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Group Soft Cards (Registration Form) */
        .form-group-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px 16px 4px;
            margin-bottom: 16px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .form-group-card:focus-within {
            border-color: rgba(14, 122, 75, 0.4);
            box-shadow: 0 4px 14px rgba(14, 122, 75, 0.06);
        }

        .card-group-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px dashed var(--border);
        }

        .card-group-header i {
            font-size: 0.95rem;
        }

        /* Inputs & Controls */
        .form-label-custom {
            font-size: 0.84rem;
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
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
            min-height: 44px;
            height: 44px;
        }

        .input-group-saas:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 122, 75, 0.20);
        }

        .input-group-saas.has-error {
            border-color: var(--danger) !important;
            animation: errShake 0.3s ease;
        }

        @keyframes errShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .input-icon {
            padding: 0 14px;
            color: var(--ink-mute);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            transition: color 0.2s ease;
        }

        .input-group-saas:focus-within .input-icon {
            color: var(--primary);
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
            color: var(--ink-mute);
            opacity: 0.8;
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
            transition: color 0.18s ease;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .btn-submit-saas {
            width: 100%;
            background-color: var(--primary);
            border: 1.5px solid var(--primary);
            color: #ffffff;
            padding: 12px;
            min-height: 46px;
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
            position: relative;
            overflow: hidden;
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

        /* Subtle Shimmer on Hover */
        .btn-submit-saas::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 40%;
            height: 200%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transform: rotate(25deg);
            transition: none;
            pointer-events: none;
        }

        .btn-submit-saas:hover::after {
            left: 130%;
            transition: left 0.75s ease;
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
            animation: slideDownErr 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes slideDownErr {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
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

        /* Responsive Breakpoints */
        @media (max-width: 991.98px) {
            .mobile-header-strip {
                display: block;
            }
            .form-panel {
                padding: 24px 20px;
            }
        }

        @media (max-width: 767.98px) {
            body {
                padding: 12px 8px 80px 8px;
                align-items: flex-start;
            }
            .input-group-saas input,
            .input-group-saas select {
                font-size: 16px !important; /* iOS zoom prevention */
            }
        }

        /* Accessibility: Prefers Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
            .stage-container {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="row g-0">
                <!-- ==================== LEFT PANEL: BRAND & MOTION SHOWCASE (DESKTOP) ==================== -->
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="brand-panel">
                        <div>
                            <a href="{{ url('/') }}" class="brand-header">
                                <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="SMART RW" width="42" height="42" class="rounded-3 shadow-sm bg-white p-1">
                                <span class="brand-title">SMART <span>RW</span></span>
                            </a>
                            
                            <h2 class="brand-heading">Sistem Informasi & Tata Kelola Warga Modern</h2>
                            <p class="brand-description">
                                Transformasi digital terpadu untuk pengurus RW, ketua RT, dan warga dalam satu platform yang bersih, transparan, dan akuntabel.
                            </p>
                            
                            <!-- Dual-Scene Motion Stage (400ms Crossfade) -->
                            <div class="stage-container" id="desktopStage" data-no-motion="true">
                                <!-- Scene 1: Night-to-Dawn Joglo RW Hall -->
                                <div class="stage-scene active" id="sceneLogin">
                                    <svg viewBox="0 0 460 180" class="stage-svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice">
                                        <defs>
                                            <linearGradient id="nightSky" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#052816"/>
                                                <stop offset="60%" stop-color="#0a4628"/>
                                                <stop offset="100%" stop-color="#11633a"/>
                                            </linearGradient>
                                            <linearGradient id="doorGlow" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#fff9d2"/>
                                                <stop offset="100%" stop-color="#f4a62a"/>
                                            </linearGradient>
                                            <filter id="glowFilt" x="-20%" y="-20%" width="140%" height="140%">
                                                <feGaussianBlur stdDeviation="3" result="blur"/>
                                                <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                                            </filter>
                                        </defs>
                                        <rect width="100%" height="100%" fill="url(#nightSky)"/>
                                        <!-- Stars -->
                                        <g class="stars-group">
                                            <circle cx="45" cy="25" r="1.5" fill="#fff" class="star-1"/>
                                            <circle cx="110" cy="40" r="1.2" fill="#ffeaa7" class="star-2"/>
                                            <circle cx="180" cy="20" r="1.8" fill="#fff" class="star-3"/>
                                            <circle cx="260" cy="35" r="1.2" fill="#fff" class="star-1"/>
                                            <circle cx="340" cy="18" r="1.6" fill="#ffeaa7" class="star-2"/>
                                            <circle cx="410" cy="32" r="1.4" fill="#fff" class="star-3"/>
                                            <circle cx="75" cy="65" r="1" fill="#fff" class="star-2"/>
                                            <circle cx="380" cy="60" r="1.2" fill="#fff" class="star-1"/>
                                        </g>
                                        <!-- Clouds -->
                                        <path class="cloud-1" d="M-40 45 Q-20 30 5 45 Q30 35 55 45 Q70 45 80 55 L-40 55 Z" fill="rgba(255,255,255,0.08)"/>
                                        <path class="cloud-2" d="M320 35 Q340 22 365 35 Q390 25 415 35 Q430 35 440 45 L320 45 Z" fill="rgba(255,255,255,0.06)"/>
                                        <!-- Ground Hills -->
                                        <path d="M0 160 Q120 148 230 152 Q340 156 460 148 L460 180 L0 180 Z" fill="#06321c"/>
                                        <path d="M0 168 Q230 160 460 168 L460 180 L0 180 Z" fill="#042213"/>
                                        <!-- Pathway -->
                                        <path d="M210 180 L224 140 L236 140 L250 180 Z" fill="url(#doorGlow)" opacity="0.35"/>
                                        
                                        <!-- Left House -->
                                        <g transform="translate(60, 105)">
                                            <polygon points="35,5 5,28 65,28" fill="#3b271a"/>
                                            <rect x="12" y="28" width="46" height="32" fill="#203d2b"/>
                                            <rect x="22" y="36" width="10" height="12" rx="2" fill="#f4a62a" class="window-glow" filter="url(#glowFilt)"/>
                                            <rect x="38" y="36" width="12" height="24" fill="#14261b"/>
                                        </g>

                                        <!-- Joglo Balai RW 01 -->
                                        <g transform="translate(185, 70)">
                                            <rect x="0" y="70" width="90" height="15" rx="3" fill="#1a3826"/>
                                            <rect x="8" y="42" width="5" height="28" fill="#d4a373"/>
                                            <rect x="25" y="42" width="5" height="28" fill="#d4a373"/>
                                            <rect x="60" y="42" width="5" height="28" fill="#d4a373"/>
                                            <rect x="77" y="42" width="5" height="28" fill="#d4a373"/>
                                            <rect x="20" y="42" width="50" height="28" fill="#0f2618"/>
                                            <rect x="24" y="47" width="10" height="12" rx="2" fill="#ffd166" class="window-glow" filter="url(#glowFilt)"/>
                                            <rect x="56" y="47" width="10" height="12" rx="2" fill="#ffd166" class="window-glow" filter="url(#glowFilt)"/>
                                            <rect x="39" y="47" width="12" height="23" rx="1" fill="#ffeaa7" class="door-opening" filter="url(#glowFilt)"/>
                                            <polygon points="45,0 30,18 60,18" fill="#5c2e0b"/>
                                            <polygon points="45,14 15,32 75,32" fill="#432005"/>
                                            <polygon points="45,28 -5,44 95,44" fill="#321602"/>
                                            <rect x="30" y="36" width="30" height="6" rx="2" fill="#147A45" stroke="#f4a62a" stroke-width="0.7"/>
                                            <text x="45" y="41" font-size="4.2" font-weight="bold" fill="#fff" text-anchor="middle" letter-spacing="0.4">BALAI RW 01</text>
                                            <g transform="translate(45, -6)">
                                                <circle cx="0" cy="0" r="8" fill="rgba(244, 166, 42, 0.25)" class="shield-halo"/>
                                                <path d="M-5 -4 L5 -4 L5 2 Q0 7 -5 2 Z" fill="#f4a62a" stroke="#fff" stroke-width="0.8"/>
                                                <path d="M0 -3 L0 3 M-3 0 L3 0" stroke="#0e7a4b" stroke-width="0.8"/>
                                            </g>
                                        </g>

                                        <!-- Right House -->
                                        <g transform="translate(325, 108)">
                                            <polygon points="35,6 8,26 62,26" fill="#3b271a"/>
                                            <rect x="15" y="26" width="40" height="30" fill="#203d2b"/>
                                            <rect x="23" y="34" width="10" height="10" rx="2" fill="#f4a62a" class="window-glow" filter="url(#glowFilt)"/>
                                            <rect x="38" y="34" width="10" height="22" fill="#14261b"/>
                                        </g>

                                        <!-- Feature Micro Badges -->
                                        <!-- Coin -->
                                        <g transform="translate(32, 45)">
                                            <rect width="28" height="28" rx="8" fill="rgba(14, 122, 75, 0.9)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                                            <circle cx="14" cy="14" r="7" fill="#f4a62a" class="anim-coin"/>
                                            <text x="14" y="17" font-size="8.5" font-weight="900" fill="#fff" text-anchor="middle">Rp</text>
                                        </g>
                                        <!-- Mail -->
                                        <g transform="translate(230, 18)">
                                            <rect width="28" height="28" rx="8" fill="rgba(14, 122, 75, 0.9)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                                            <g class="anim-mail" transform="translate(6, 7)">
                                                <rect width="16" height="11" rx="1.5" fill="#fff"/>
                                                <path d="M0 0 L8 6 L16 0" fill="none" stroke="#0e7a4b" stroke-width="1.2"/>
                                            </g>
                                        </g>
                                        <!-- CCTV -->
                                        <g transform="translate(395, 45)">
                                            <rect width="28" height="28" rx="8" fill="rgba(14, 122, 75, 0.9)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                                            <g transform="translate(7, 8)">
                                                <path d="M2 3 L10 1 L10 9 L2 7 Z" fill="#fff"/>
                                                <rect x="0" y="3" width="4" height="4" rx="1" fill="#cbd5e1"/>
                                                <circle cx="12" cy="5" r="2" fill="#ef4444" class="cctv-red-dot"/>
                                            </g>
                                        </g>
                                    </svg>
                                </div>

                                <!-- Scene 2: Daytime Kampung with New Citizen Joining -->
                                <div class="stage-scene" id="sceneRegister">
                                    <svg viewBox="0 0 460 180" class="stage-svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice">
                                        <defs>
                                            <linearGradient id="daySky" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#199859"/>
                                                <stop offset="60%" stop-color="#22b56d"/>
                                                <stop offset="100%" stop-color="#34d385"/>
                                            </linearGradient>
                                            <linearGradient id="sunGrad" x1="0" y1="0" x2="1" y2="1">
                                                <stop offset="0%" stop-color="#fff5c0"/>
                                                <stop offset="100%" stop-color="#f4a62a"/>
                                            </linearGradient>
                                            <linearGradient id="roofNew" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#e76f51"/>
                                                <stop offset="100%" stop-color="#f4a62a"/>
                                            </linearGradient>
                                            <linearGradient id="pinGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#ef4444"/>
                                                <stop offset="100%" stop-color="#b91c1c"/>
                                            </linearGradient>
                                        </defs>
                                        <rect width="100%" height="100%" fill="url(#daySky)"/>
                                        
                                        <!-- Sun -->
                                        <g transform="translate(60, 35)">
                                            <circle cx="0" cy="0" r="18" fill="url(#sunGrad)"/>
                                            <circle cx="0" cy="0" r="26" fill="rgba(255, 245, 192, 0.25)" class="sun-pulse"/>
                                        </g>

                                        <!-- Day Clouds -->
                                        <path class="day-cloud-1" d="M140 25 Q160 12 185 25 Q210 15 235 25 Q250 25 260 35 L140 35 Z" fill="rgba(255,255,255,0.45)"/>
                                        <path class="day-cloud-2" d="M340 18 Q360 8 385 18 Q410 10 430 18 Q440 22 450 30 L340 30 Z" fill="rgba(255,255,255,0.35)"/>

                                        <!-- Kite -->
                                        <g class="kite-group" transform="translate(290, 32)">
                                            <polygon points="0,-10 8,0 0,10 -8,0" fill="#f4a62a" stroke="#fff" stroke-width="0.8"/>
                                            <path d="M0 10 Q5 22 2 32 Q-3 42 1 52" stroke="rgba(255,255,255,0.6)" stroke-width="0.8" fill="none"/>
                                            <circle cx="3" cy="22" r="1.5" fill="#e76f51"/>
                                            <circle cx="0" cy="37" r="1.5" fill="#2a6fdb"/>
                                        </g>

                                        <!-- Green Ground & Street -->
                                        <path d="M0 145 Q120 132 240 138 Q360 144 460 135 L460 180 L0 180 Z" fill="#147A45"/>
                                        <path d="M0 158 Q230 150 460 158 L460 180 L0 180 Z" fill="#0d5c34"/>
                                        <path d="M0 172 L460 172" stroke="#dbe9e0" stroke-width="3" stroke-dasharray="12 8" opacity="0.6"/>

                                        <!-- Pop Houses -->
                                        <g class="house-1" transform="translate(30, 95)">
                                            <polygon points="28,4 4,24 52,24" fill="#2a6fdb"/>
                                            <rect x="10" y="24" width="36" height="34" fill="#ffffff" rx="1"/>
                                            <rect x="16" y="32" width="10" height="10" rx="2" fill="#93c5fd"/>
                                            <rect x="30" y="32" width="10" height="26" fill="#1e3a8a"/>
                                        </g>
                                        <g class="house-2" transform="translate(105, 102)">
                                            <polygon points="26,6 5,22 47,22" fill="#10b981"/>
                                            <rect x="10" y="22" width="32" height="30" fill="#ffffff" rx="1"/>
                                            <rect x="15" y="28" width="8" height="8" rx="2" fill="#6ee7b7"/>
                                            <rect x="26" y="28" width="10" height="24" fill="#065f46"/>
                                        </g>

                                        <!-- The New Citizen House -->
                                        <g transform="translate(195, 82)">
                                            <rect x="10" y="30" width="50" height="42" fill="#ffffff" rx="2" stroke="#0e7a4b" stroke-width="1.2"/>
                                            <rect x="18" y="40" width="14" height="14" rx="2" fill="#fef08a" stroke="#ca8a04" stroke-width="0.8"/>
                                            <rect x="38" y="40" width="14" height="32" rx="2" fill="#0e7a4b"/>
                                            <circle cx="48" cy="56" r="1.5" fill="#f4a62a"/>
                                            <rect x="36" y="72" width="18" height="3" rx="1" fill="#f4a62a"/>
                                            <!-- Descending Roof -->
                                            <g class="roof-descend">
                                                <polygon points="35,0 0,30 70,30" fill="url(#roofNew)" stroke="#fff" stroke-width="1"/>
                                                <rect x="23" y="10" width="24" height="6" rx="1" fill="#fff" opacity="0.95"/>
                                                <text x="35" y="14.5" font-size="3.8" font-weight="900" fill="#0e7a4b" text-anchor="middle">RW 01</text>
                                            </g>
                                            <!-- Bouncing Drop Pin -->
                                            <g class="pin-drop-bounce" transform="translate(35, -8)">
                                                <path d="M0 -14 C-7 -14 -11 -9 -11 -3 C-11 4 0 14 0 14 C0 14 11 4 11 -3 C11 -9 7 -14 0 -14 Z" fill="url(#pinGrad)" stroke="#fff" stroke-width="1.2"/>
                                                <circle cx="0" cy="-4" r="4.5" fill="#fff"/>
                                                <circle cx="0" cy="-4" r="2" fill="#b91c1c"/>
                                                <circle cx="0" cy="14" r="5" fill="none" stroke="#ef4444" stroke-width="1" class="pin-ring"/>
                                            </g>
                                        </g>

                                        <g class="house-4" transform="translate(285, 100)">
                                            <polygon points="26,5 5,22 47,22" fill="#8b5cf6"/>
                                            <rect x="10" y="22" width="32" height="32" fill="#ffffff" rx="1"/>
                                            <rect x="15" y="28" width="8" height="8" rx="2" fill="#c4b5fd"/>
                                            <rect x="26" y="28" width="10" height="26" fill="#4c1d95"/>
                                        </g>
                                        <g class="house-5" transform="translate(355, 95)">
                                            <polygon points="28,4 4,24 52,24" fill="#f59e0b"/>
                                            <rect x="10" y="24" width="36" height="34" fill="#ffffff" rx="1"/>
                                            <rect x="16" y="32" width="10" height="10" rx="2" fill="#fde68a"/>
                                            <rect x="30" y="32" width="10" height="26" fill="#78350f"/>
                                        </g>

                                        <!-- Walking Citizens -->
                                        <g transform="translate(160, 148)">
                                            <circle cx="0" cy="-14" r="3.5" fill="#06321c"/>
                                            <path d="M-3 -10 L3 -10 L4 0 L2 14 L-1 14 L-2 4 L-4 14 L-6 14 L-4 0 Z" fill="#06321c"/>
                                            <g transform="translate(100, 0)">
                                                <circle cx="0" cy="-14" r="3.5" fill="#06321c"/>
                                                <path d="M-3 -10 L3 -10 L4 0 L2 14 L-1 14 L-2 4 L-4 14 L-6 14 L-4 0 Z" fill="#06321c"/>
                                                <line x1="3" y1="-8" x2="8" y2="-15" stroke="#06321c" stroke-width="1.8" stroke-linecap="round" class="waving-arm"/>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            
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
                            <span>RW 01 Kelurahan Pintar</span>
                            <span>v2.5 Modern SaaS</span>
                        </div>
                    </div>
                </div>
                
                <!-- ==================== RIGHT PANEL: FORMS ==================== -->
                <div class="col-lg-7">
                    <div class="form-panel">
                        <!-- Mobile Header Strip (<= 900px) -->
                        <div class="mobile-header-strip">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <img src="{{ asset('images/rw-smart-icon.svg') }}" alt="SMART RW" width="32" height="32" class="bg-white rounded-2 p-1">
                                    <span class="fs-5 fw-bold text-white" style="letter-spacing: -0.02em; white-space: nowrap;">SMART <span style="color: #F2D179;">RW</span></span>
                                </div>
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-1 small">Wilayah RW 01</span>
                            </div>

                            <!-- Mobile Mini Animation Stage -->
                            <div class="stage-container" id="mobileStage" data-no-motion="true" style="overflow: hidden;">
                                <div class="stage-scene active" id="mobileSceneLogin" style="overflow: hidden;">
                                    <svg viewBox="0 0 350 100" class="stage-svg" aria-hidden="true" preserveAspectRatio="none" style="overflow: hidden; max-width: 100%;">
                                        <rect width="350" height="100" fill="#0a4628"/>
                                        <circle cx="25" cy="20" r="1.5" fill="#fff" class="star-1"/>
                                        <circle cx="130" cy="15" r="1.2" fill="#ffeaa7" class="star-2"/>
                                        <circle cx="320" cy="25" r="1.5" fill="#fff" class="star-3"/>
                                        <!-- Balai & houses mini -->
                                        <path d="M0 85 Q175 78 350 85 L350 100 L0 100 Z" fill="#042213"/>
                                        <g transform="translate(135, 32)">
                                            <polygon points="40,0 20,22 60,22" fill="#5c2e0b"/>
                                            <polygon points="40,16 -2,38 82,38" fill="#321602"/>
                                            <rect x="18" y="38" width="44" height="26" fill="#0f2618"/>
                                            <rect x="34" y="44" width="12" height="20" rx="1" fill="#ffeaa7" class="door-opening"/>
                                            <text x="40" y="32" font-size="4.5" font-weight="bold" fill="#fff" text-anchor="middle">BALAI RW 01</text>
                                        </g>
                                        <g transform="translate(40, 50)">
                                            <polygon points="20,0 2,16 38,16" fill="#2a6fdb"/>
                                            <rect x="8" y="16" width="24" height="20" fill="#ffffff"/>
                                        </g>
                                        <g transform="translate(260, 52)">
                                            <polygon points="20,0 2,16 38,16" fill="#f59e0b"/>
                                            <rect x="8" y="16" width="24" height="18" fill="#ffffff"/>
                                        </g>
                                    </svg>
                                </div>
                                <div class="stage-scene" id="mobileSceneRegister" style="overflow: hidden;">
                                    <svg viewBox="0 0 350 100" class="stage-svg" aria-hidden="true" preserveAspectRatio="none" style="overflow: hidden; max-width: 100%;">
                                        <rect width="350" height="100" fill="#199859"/>
                                        <circle cx="35" cy="25" r="14" fill="#f4a62a" opacity="0.9"/>
                                        <path d="M0 80 Q175 74 350 80 L350 100 L0 100 Z" fill="#0d5c34"/>
                                        <!-- House with pin -->
                                        <g transform="translate(145, 38)">
                                            <rect x="10" y="20" width="40" height="28" fill="#ffffff" rx="1"/>
                                            <polygon points="30,0 2,20 58,20" fill="#e76f51"/>
                                            <g class="pin-drop-bounce" transform="translate(30, -5)">
                                                <path d="M0 -10 C-5 -10 -8 -6 -8 -2 C-8 3 0 10 0 10 C0 10 8 3 8 -2 C8 -6 5 -10 0 -10 Z" fill="#ef4444"/>
                                                <circle cx="0" cy="-3" r="3" fill="#fff"/>
                                            </g>
                                        </g>
                                        <g transform="translate(45, 48)">
                                            <polygon points="18,0 2,15 34,15" fill="#2a6fdb"/>
                                            <rect x="6" y="15" width="24" height="20" fill="#ffffff"/>
                                        </g>
                                        <g transform="translate(255, 48)">
                                            <polygon points="18,0 2,15 34,15" fill="#10b981"/>
                                            <rect x="6" y="15" width="24" height="20" fill="#ffffff"/>
                                        </g>
                                    </svg>
                                </div>
                            </div>

                            <p class="small text-white-50 mb-0 text-center">Transformasi Digital Tata Kelola & Layanan Lingkungan</p>
                        </div>

                        <!-- Navigation Tabs with Sliding White Pill -->
                        <div class="auth-nav-tabs">
                            <div class="tab-slider-pill" id="tabSliderPill"></div>
                            <button class="tab-btn active" data-tab="login" type="button" id="tabBtnLogin">
                                <i class="fas fa-right-to-bracket" aria-hidden="true"></i> <span>Masuk Akun</span>
                            </button>
                            <button class="tab-btn" data-tab="register" type="button" id="tabBtnRegister">
                                <i class="fas fa-user-plus" aria-hidden="true"></i> <span>Pendaftaran Warga</span>
                            </button>
                        </div>
                        
                        <!-- ==================== TAB 1: LOGIN FORM ==================== -->
                        <div id="loginForm" class="auth-form active">
                            @if($errors->any() && !session('register_success'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>{{ $errors->first() }}</div>
                                </div>
                            @endif
                            
                            @if(session('status'))
                                <div class="alert-saas alert-saas-success">
                                    <i class="fas fa-circle-check fs-5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>{{ session('status') }}</div>
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="alert-saas alert-saas-success">
                                    <i class="fas fa-circle-check fs-5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('login') }}" id="mainLoginForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label-custom">Alamat Email</label>
                                    <div class="input-group-saas @error('email') has-error @enderror">
                                        <span class="input-icon"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                        <input type="email" name="email" id="loginEmail" placeholder="nama@email.com" required autofocus value="{{ old('email') }}">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label-custom">Kata Sandi</label>
                                    <div class="input-group-saas @error('password') has-error @enderror">
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
                                
                                <button type="submit" class="btn-submit-saas" id="btnLoginSubmit">
                                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i> <span>Masuk ke Dashboard</span>
                                </button>
                            </form>
                        </div>
                        
                        <!-- ==================== TAB 2: REGISTER FORM ==================== -->
                        <div id="registerForm" class="auth-form" style="display: none;">
                            @if($errors->any() && !session('register_success'))
                                <div class="alert-saas alert-saas-danger">
                                    <i class="fas fa-circle-exclamation fs-5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>
                                        <ul class="mb-0 ps-3">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('register') }}" id="mainRegisterForm">
                                @csrf
                                @php
                                    $regRts = \App\Models\Rt::orderBy('kode_rt')->get();
                                @endphp

                                <!-- Group 1: Wilayah Domisili (RW 01 & RT) -->
                                <div class="form-group-card">
                                    <div class="card-group-header">
                                        <i class="fas fa-map-location-dot"></i> Wilayah & Domisili
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Wilayah RW</label>
                                            <div class="input-group-saas" style="background-color: var(--bg);">
                                                <span class="input-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                                                <input type="text" class="form-control border-0 fw-bold" value="SMART RW 01" readonly style="background-color: transparent; color: var(--primary);">
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Wilayah RT Domisili <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('rt_id') has-error @enderror">
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
                                </div>

                                <!-- Group 2: Identitas Kependudukan -->
                                <div class="form-group-card">
                                    <div class="card-group-header">
                                        <i class="fas fa-id-card"></i> Identitas Kependudukan
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-12">
                                            <label class="form-label-custom">Nama Lengkap Sesuai KTP <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('name') has-error @enderror">
                                                <span class="input-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
                                                <input type="text" name="name" placeholder="Nama lengkap sesuai KTP" value="{{ old('name') }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('nik') has-error @enderror">
                                                <span class="input-icon"><i class="fas fa-address-card" aria-hidden="true"></i></span>
                                                <input type="text" name="nik" id="registerNik" placeholder="16 digit NIK" value="{{ old('nik') }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Nomor Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('no_kk') has-error @enderror">
                                                <span class="input-icon"><i class="fas fa-file-invoice" aria-hidden="true"></i></span>
                                                <input type="text" name="no_kk" id="registerNoKk" placeholder="16 digit No. KK" value="{{ old('no_kk') }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-12">
                                            <label class="form-label-custom">Jenis Kelamin <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('jenis_kelamin') has-error @enderror">
                                                <span class="input-icon"><i class="fas fa-venus-mars" aria-hidden="true"></i></span>
                                                <select name="jenis_kelamin" required>
                                                    <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih jenis kelamin</option>
                                                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Group 3: Kontak Warga -->
                                <div class="form-group-card">
                                    <div class="card-group-header">
                                        <i class="fas fa-address-book"></i> Kontak Warga
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('no_hp') has-error @enderror">
                                                <span class="input-icon"><i class="fas fa-phone" aria-hidden="true"></i></span>
                                                <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}" required>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Alamat Email <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('email') has-error @enderror">
                                                <span class="input-icon"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                                <input type="email" name="email" placeholder="nama@email.com" value="{{ old('email') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Group 4: Keamanan Sandi Akun -->
                                <div class="form-group-card">
                                    <div class="card-group-header">
                                        <i class="fas fa-shield-halved"></i> Keamanan Akun
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Kata Sandi <span class="text-danger">*</span></label>
                                            <div class="input-group-saas @error('password') has-error @enderror">
                                                <span class="input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                                                <input type="password" name="password" id="regPassword" placeholder="Min. 8 karakter" required>
                                                <button type="button" class="password-toggle" aria-label="Tampilkan password" data-target="regPassword">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label-custom">Konfirmasi Sandi <span class="text-danger">*</span></label>
                                            <div class="input-group-saas">
                                                <span class="input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                                                <input type="password" name="password_confirmation" id="regPasswordConfirm" placeholder="Ulangi sandi" required>
                                                <button type="button" class="password-toggle" aria-label="Tampilkan password" data-target="regPasswordConfirm">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert-saas alert-saas-info mb-4">
                                    <i class="fas fa-circle-info fs-5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>Setelah mendaftar, akun Anda akan diverifikasi oleh Ketua RT sesuai data kependudukan wilayah RW 01.</div>
                                </div>
                                
                                <button type="submit" class="btn-submit-saas" id="btnRegisterSubmit">
                                    <i class="fas fa-user-plus" aria-hidden="true"></i> <span>Daftar Sebagai Warga</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab Switching with Smooth Sliding Pill Indicator and Crossfade Stage
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabSliderPill = document.getElementById('tabSliderPill');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        
        // Stage scenes
        const sceneLogin = document.getElementById('sceneLogin');
        const sceneRegister = document.getElementById('sceneRegister');
        const mobileSceneLogin = document.getElementById('mobileSceneLogin');
        const mobileSceneRegister = document.getElementById('mobileSceneRegister');

        function switchTab(tab) {
            tabBtns.forEach(b => b.classList.remove('active'));
            
            if (tab === 'login') {
                const btn = document.getElementById('tabBtnLogin');
                if (btn) btn.classList.add('active');
                if (tabSliderPill) tabSliderPill.style.transform = 'translateX(0%)';
                
                // Form transition
                if (registerForm) registerForm.style.display = 'none';
                if (loginForm) {
                    loginForm.style.display = 'block';
                    loginForm.classList.remove('form-entering');
                    void loginForm.offsetWidth; // trigger reflow
                    loginForm.classList.add('form-entering');
                }
                
                // Stage crossfade
                if (sceneLogin) sceneLogin.classList.add('active');
                if (sceneRegister) sceneRegister.classList.remove('active');
                if (mobileSceneLogin) mobileSceneLogin.classList.add('active');
                if (mobileSceneRegister) mobileSceneRegister.classList.remove('active');
                
                if (window.location.hash === '#register') {
                    history.replaceState(null, null, ' ');
                }
            } else {
                const btn = document.getElementById('tabBtnRegister');
                if (btn) btn.classList.add('active');
                if (tabSliderPill) tabSliderPill.style.transform = 'translateX(100%)';
                
                // Form transition
                if (loginForm) loginForm.style.display = 'none';
                if (registerForm) {
                    registerForm.style.display = 'block';
                    registerForm.classList.remove('form-entering');
                    void registerForm.offsetWidth; // trigger reflow
                    registerForm.classList.add('form-entering');
                }
                
                // Stage crossfade
                if (sceneLogin) sceneLogin.classList.remove('active');
                if (sceneRegister) sceneRegister.classList.add('active');
                if (mobileSceneLogin) mobileSceneLogin.classList.remove('active');
                if (mobileSceneRegister) mobileSceneRegister.classList.add('active');
                
                window.location.hash = 'register';
            }
        }
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                switchTab(btn.dataset.tab);
            });
        });
        
        // Check URL hash for tab
        if (window.location.hash === '#register') {
            switchTab('register');
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
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
                toggle.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
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
        }
        setTheme(localStorage.getItem('risma-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                const cur = document.documentElement.getAttribute('data-bs-theme') || 'light';
                setTheme(cur === 'dark' ? 'light' : 'dark');
            });
        }

        // Visibilitychange lifecycle listener to pause continuous SVG animations when tab inactive
        document.addEventListener('visibilitychange', () => {
            const svgElements = document.querySelectorAll('.stage-svg *');
            if (document.hidden) {
                svgElements.forEach(el => el.style.animationPlayState = 'paused');
            } else {
                svgElements.forEach(el => el.style.animationPlayState = 'running');
            }
        });

        // Form submit loading spinner indication
        const loginFormElem = document.getElementById('mainLoginForm');
        if (loginFormElem) {
            loginFormElem.addEventListener('submit', () => {
                const btn = document.getElementById('btnLoginSubmit');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...';
                    setTimeout(() => { btn.disabled = true; }, 20);
                }
            });
        }
        const regFormElem = document.getElementById('mainRegisterForm');
        if (regFormElem) {
            regFormElem.addEventListener('submit', () => {
                const btn = document.getElementById('btnRegisterSubmit');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mendaftarkan...';
                    setTimeout(() => { btn.disabled = true; }, 20);
                }
            });
        }

        // Filter NIK dan No. KK: Hanya izinkan angka, dukung tepat 16 digit (tidak mentok/memotong 15 digit)
        const numericInputs = document.querySelectorAll('input[name="nik"], input[name="no_kk"]');
        numericInputs.forEach(input => {
            input.setAttribute('type', 'text');
            input.setAttribute('inputmode', 'numeric');
            input.setAttribute('pattern', '[0-9]{16}');
            input.setAttribute('maxlength', '16');

            input.addEventListener('input', function () {
                const clean = this.value.replace(/\D/g, '').slice(0, 16);
                if (this.value !== clean) {
                    this.value = clean;
                }
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || '';
                const clean = pasted.replace(/\D/g, '').slice(0, 16);
                const start = this.selectionStart || 0;
                const end = this.selectionEnd || 0;
                const current = this.value;
                const nextVal = (current.slice(0, start) + clean + current.slice(end)).replace(/\D/g, '').slice(0, 16);
                this.value = nextVal;
                this.dispatchEvent(new Event('input'));
            });
        });
    </script>
    
    <!-- Floating Theme Toggle for Login Page -->
    <button type="button" id="themeToggleBtn" class="btn btn-sm rounded-circle position-fixed top-0 end-0 m-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: var(--surface); border: 1.5px solid var(--border); color: var(--ink); z-index: 1050;" title="Ganti mode terang/gelap" aria-label="Toggle Theme">
        <i class="fas fa-sun" id="themeToggleIcon"></i>
    </button>

    <!-- SMART RW Motion Graphics Engine -->
    <script src="{{ asset('js/motion.js') }}"></script>
</body>
</html>
