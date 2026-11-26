<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#00A884" id="metaThemeColor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk & Pendaftaran | SMART RW 01</title>
    
    <script>
        (function () {
            const savedTheme = localStorage.getItem('risma-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme ? savedTheme : (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme;
            const metaTheme = document.getElementById('metaThemeColor');
            if (metaTheme) metaTheme.setAttribute('content', theme === 'dark' ? '#111b21' : '#00A884');
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
            --wa-bg: #F0F2F5;
            --wa-card: #FFFFFF;
            --wa-card-border: #E9EDEF;
            --wa-mint: #E7FCEB;
            --wa-mint-soft: #DCF8C6;
            --wa-mint-border: #C4EDCF;
            --wa-teal: #00A884;
            --wa-teal-hover: #008F6F;
            --wa-teal-active: #007A5E;
            --wa-teal-soft: #E7FCEB;
            --wa-dark: #111B21;
            --wa-body: #3B4A54;
            --wa-muted: #54656F;
            --wa-placeholder: #8696A0;
            --wa-border: #E9EDEF;
            --wa-border-hover: #D1D7DB;
            --wa-danger: #EA0038;
            --wa-danger-soft: #FDE8EC;
            --wa-info: #027EB5;
            --wa-info-soft: #E5F6FD;
            --radius-btn: 14px;
            --radius-input: 12px;
            --radius-card: 26px;
            --radius-panel: 22px;
            --shadow-card: 0 16px 44px rgba(11, 20, 26, 0.07);
            --shadow-float: 0 8px 24px rgba(11, 20, 26, 0.08);
            --transition-smooth: 420ms cubic-bezier(0.22, 1, 0.36, 1);
        }

        [data-bs-theme="dark"],
        [data-theme="dark"] {
            color-scheme: dark;
            --wa-bg: #0C1317;
            --wa-card: #111B21;
            --wa-card-border: #222E35;
            --wa-mint: #152A22;
            --wa-mint-soft: #18372B;
            --wa-mint-border: #1F4535;
            --wa-teal: #00A884;
            --wa-teal-hover: #00C89C;
            --wa-teal-active: #008F6F;
            --wa-teal-soft: #18372B;
            --wa-dark: #E9EDEF;
            --wa-body: #D1D7DB;
            --wa-muted: #8696A0;
            --wa-placeholder: #667781;
            --wa-border: #222E35;
            --wa-border-hover: #2A3942;
            --wa-danger: #F15C6D;
            --wa-danger-soft: #301920;
            --wa-info: #53BDEB;
            --wa-info-soft: #162C3A;
            --shadow-card: 0 16px 44px rgba(0, 0, 0, 0.45);
            --shadow-float: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--wa-bg);
            color: var(--wa-body);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px 80px 16px;
            font-size: 15px;
            line-height: 1.5;
            transition: background-color 0.3s ease;
        }

        .auth-container {
            width: 100%;
            max-width: 1080px;
            margin: auto;
            animation: authRise 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes authRise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Top Bar Above Card */
        .auth-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 16px 16px 12px 16px;
            margin-bottom: 12px;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--wa-card);
            color: var(--wa-muted);
            border: 1px solid var(--wa-border);
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(11, 20, 26, 0.04);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-back-home:hover {
            color: var(--wa-teal);
            border-color: var(--wa-teal);
            transform: translateX(-2px);
        }

        .top-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--wa-muted);
            background: var(--wa-card);
            border: 1px solid var(--wa-border);
            padding: 6px 12px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .topbar-theme-pill {
            background: var(--wa-card);
            border: 1px solid var(--wa-border);
            color: var(--wa-muted);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            font-size: 0.8rem;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(11, 20, 26, 0.04);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .topbar-theme-pill:hover {
            color: var(--wa-teal);
            border-color: var(--wa-teal);
        }

        .status-dot-pulse {
            width: 8px;
            height: 8px;
            background-color: var(--wa-teal);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(0, 168, 132, 0.7);
            animation: dotPulse 2s infinite;
        }

        @keyframes dotPulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 168, 132, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(0, 168, 132, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 168, 132, 0); }
        }

        /* Main Card */
        .auth-card {
            background: var(--wa-card);
            border-radius: var(--radius-card);
            border: 1px solid var(--wa-card-border);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            position: relative;
            min-height: 660px;
        }

        /* Desktop 2-Column Coordinate Layout (>= 768px) */
        @media (min-width: 768px) {
            .auth-card-inner {
                display: flex;
                position: relative;
                width: 100%;
                min-height: 660px;
            }

            .forms-col {
                width: 52%;
                flex-shrink: 0;
                padding: 38px 36px;
                position: relative;
                z-index: 2;
                transition: transform var(--transition-smooth);
                background: var(--wa-card);
                display: flex;
                flex-direction: column;
                justify-content: center;
                max-height: 88vh;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: rgba(0, 168, 132, 0.25) transparent;
            }

            .forms-col::-webkit-scrollbar {
                width: 5px;
            }
            .forms-col::-webkit-scrollbar-track {
                background: transparent;
            }
            .forms-col::-webkit-scrollbar-thumb {
                background: rgba(0, 168, 132, 0.25);
                border-radius: 6px;
            }
            .forms-col::-webkit-scrollbar-thumb:hover {
                background: rgba(0, 168, 132, 0.45);
            }

            .showcase-col {
                width: 48%;
                flex-shrink: 0;
                padding: 14px;
                position: relative;
                z-index: 1;
                transition: transform var(--transition-smooth);
                display: flex;
            }

            /* In Register Mode: Mint Showcase moves LEFT, Form moves RIGHT */
            .auth-card.mode-register .forms-col {
                transform: translateX(92.3%);
            }

            .auth-card.mode-register .showcase-col {
                transform: translateX(-108.3%);
            }

            /* In Login & Forgot Mode: Form is on LEFT, Showcase is on RIGHT */
            .auth-card.mode-login .forms-col,
            .auth-card.mode-forgot .forms-col {
                transform: translateX(0%);
            }

            .auth-card.mode-login .showcase-col,
            .auth-card.mode-forgot .showcase-col {
                transform: translateX(0%);
            }
        }

        /* Showcase Mint Box */
        .showcase-box {
            width: 100%;
            height: 100%;
            background: var(--wa-mint);
            border: 1px solid var(--wa-mint-border);
            border-radius: var(--radius-panel);
            padding: 32px 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.4);
        }

        /* WhatsApp Doodle Background Pattern */
        .showcase-doodle-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            opacity: 0.08;
            background-repeat: repeat;
            background-size: 260px;
            background-image: url("data:image/svg+xml,%3Csvg width='260' height='260' viewBox='0 0 260 260' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M28 35C28 30.5817 31.5817 27 36 27H54C58.4183 27 62 30.5817 62 35V45C62 49.4183 58.4183 53 54 53H40L30 60V53C28.8954 53 28 52.1046 28 51V35Z' stroke='%2300A884' stroke-width='2'/%3E%3Cpath d='M40 37H50M40 43H47' stroke='%2300A884' stroke-width='2' stroke-linecap='round'/%3E%3Ccircle cx='130' cy='45' r='12' stroke='%2300A884' stroke-width='2'/%3E%3Cpath d='M125 45L129 48L136 41' stroke='%2300A884' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M205 25L215 35L205 45L195 35Z' stroke='%2300A884' stroke-width='2'/%3E%3Cpath d='M30 135C30 120 45 120 45 135V150H30V135Z' stroke='%2300A884' stroke-width='2'/%3E%3Cpath d='M120 125L135 110L150 125V145H120V125Z' stroke='%2300A884' stroke-width='2'/%3E%3Crect x='130' y='132' width='10' height='13' stroke='%2300A884' stroke-width='1.5'/%3E%3Cpath d='M205 130C202 125 195 125 192 130C189 135 195 142 205 150C215 142 221 135 218 130C215 125 208 125 205 130Z' stroke='%2300A884' stroke-width='2'/%3E%3Cpath d='M50 215L58 200L66 215Z' stroke='%2300A884' stroke-width='2'/%3E%3Ccircle cx='130' cy='210' r='14' stroke='%2300A884' stroke-width='2'/%3E%3Cpath d='M125 215C127 218 133 218 135 215M126 207H126.01M134 207H134.01' stroke='%2300A884' stroke-width='2' stroke-linecap='round'/%3E%3Cpath d='M200 205H220M210 195V215' stroke='%2300A884' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
        }

        /* Showcase Top Row */
        .showcase-top-pills {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 2;
        }

        .pill-realtime {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0d5c38;
            box-shadow: 0 2px 6px rgba(0, 168, 132, 0.08);
            border: 1px solid rgba(0, 168, 132, 0.15);
        }

        .pill-rw-code {
            font-size: 0.82rem;
            font-weight: 800;
            color: #0e6c43;
            letter-spacing: -0.01em;
        }

        /* Center Hub Motion Graphic */
        .hub-container {
            position: relative;
            width: 100%;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            z-index: 2;
        }

        .hub-svg-lines {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: visible;
        }

        .hub-line-dash {
            stroke: #00A884;
            stroke-width: 1.8;
            stroke-dasharray: 4, 4;
            opacity: 0.35;
            animation: dashMove 30s linear infinite;
        }

        @keyframes dashMove {
            to { stroke-dashoffset: -100; }
        }

        /* Central Node */
        .hub-center-node {
            width: 72px;
            height: 72px;
            background: #FFFFFF;
            border: 3px solid #00A884;
            border-radius: 50%;
            display: grid;
            place-items: center;
            position: relative;
            z-index: 3;
            box-shadow: 0 10px 28px rgba(0, 168, 132, 0.22);
            font-size: 1.6rem;
            color: #00A884;
            transition: transform 0.3s ease;
        }

        .hub-center-node:hover {
            transform: scale(1.08);
        }

        .hub-pulse-ring {
            position: absolute;
            inset: -9px;
            border-radius: 50%;
            border: 2px solid #00A884;
            opacity: 0.35;
            animation: ringPulse 2.8s cubic-bezier(0.25, 1, 0.5, 1) infinite;
        }

        @keyframes ringPulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.35); opacity: 0; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        /* 4 Floating Badges */
        .floating-hub-card {
            position: absolute;
            background: #FFFFFF;
            border-radius: 14px;
            padding: 8px 14px;
            box-shadow: var(--shadow-float);
            border: 1px solid rgba(0, 168, 132, 0.18);
            z-index: 4;
            display: flex;
            align-items: center;
            gap: 10px;
            pointer-events: auto;
            max-width: 180px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .floating-hub-card:hover {
            box-shadow: 0 12px 30px rgba(0, 168, 132, 0.18);
        }

        .fhc-title {
            font-size: 0.82rem;
            font-weight: 800;
            color: #111B21;
            line-height: 1.2;
            margin-bottom: 2px;
            white-space: nowrap;
        }

        .fhc-sub {
            font-size: 0.72rem;
            font-weight: 600;
            color: #54656F;
            line-height: 1.2;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .fhc-check-double {
            color: #00A884;
            font-weight: 900;
            font-size: 0.85rem;
            letter-spacing: -2px;
        }

        .fhc-icon-box {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #DCF8C6;
            color: #00A884;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 800;
            flex-shrink: 0;
            border: 1px solid #BBF7D0;
        }

        [data-bs-theme="dark"] .fhc-icon-box,
        [data-theme="dark"] .fhc-icon-box {
            background: #18372B;
            border-color: #1F4535;
            color: #00A884;
        }

        .fhc-badge-rp {
            font-size: 0.72rem;
            font-weight: 900;
        }

        /* Positions and Floating Keyframes */
        .fhc-top-left {
            top: 24px;
            left: 6px;
            animation: floatTL 4.2s ease-in-out infinite;
        }

        .fhc-top-right {
            top: 24px;
            right: 6px;
            animation: floatTR 4.8s ease-in-out infinite 0.6s;
        }

        .fhc-bottom-left {
            bottom: 24px;
            left: 6px;
            animation: floatBL 4.4s ease-in-out infinite 1.2s;
        }

        .fhc-bottom-right {
            bottom: 24px;
            right: 6px;
            animation: floatBR 5s ease-in-out infinite 1.8s;
        }

        @keyframes floatTL {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-3px, -7px); }
        }

        @keyframes floatTR {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(3px, -6px); }
        }

        @keyframes floatBL {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-3px, 7px); }
        }

        @keyframes floatBR {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(3px, 6px); }
        }

        /* Dynamic Bottom CTA inside Showcase Box */
        .showcase-cta-wrap {
            text-align: center;
            position: relative;
            z-index: 2;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .cta-scene {
            display: none;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.35s ease, transform 0.35s ease;
        }

        .cta-scene.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .cta-heading {
            font-size: 1.25rem;
            font-weight: 800;
            color: #111B21;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .cta-desc {
            font-size: 0.84rem;
            color: #54656F;
            line-height: 1.45;
            margin-bottom: 16px;
            max-width: 340px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-showcase-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
            color: #111B21;
            border: 1.5px solid #00A884;
            padding: 12px 32px;
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 168, 132, 0.12);
            transition: all 0.25s ease;
            text-align: center;
            text-decoration: none;
        }

        .btn-showcase-action:hover {
            background: #00A884;
            color: #FFFFFF;
            border-color: #00A884;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 168, 132, 0.25);
        }

        /* ==================== FORMS SECTION (420MS SMOOTH TRANSITION) ==================== */
        .forms-slider-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            min-height: 440px;
        }

        .form-stage {
            display: none;
            width: 100%;
            opacity: 0;
            will-change: transform, opacity;
        }

        .form-stage.active {
            display: block;
            opacity: 1;
            transform: translateX(0);
            position: relative;
            z-index: 2;
        }

        /* Leaving to Left */
        .form-stage.anim-exit-left {
            display: block !important;
            position: absolute !important;
            top: 0;
            left: 0;
            width: 100% !important;
            z-index: 1;
            pointer-events: none;
            transform: translateX(0);
            opacity: 1;
            transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), opacity 420ms cubic-bezier(0.22, 1, 0.36, 1) !important;
        }
        .form-stage.anim-exit-left.anim-run {
            transform: translateX(-36px);
            opacity: 0;
        }

        /* Leaving to Right */
        .form-stage.anim-exit-right {
            display: block !important;
            position: absolute !important;
            top: 0;
            left: 0;
            width: 100% !important;
            z-index: 1;
            pointer-events: none;
            transform: translateX(0);
            opacity: 1;
            transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), opacity 420ms cubic-bezier(0.22, 1, 0.36, 1) !important;
        }
        .form-stage.anim-exit-right.anim-run {
            transform: translateX(36px);
            opacity: 0;
        }

        /* Entering from Right */
        .form-stage.anim-enter-right {
            display: block !important;
            position: relative !important;
            z-index: 2;
            transform: translateX(36px);
            opacity: 0;
            transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), opacity 420ms cubic-bezier(0.22, 1, 0.36, 1) !important;
        }
        .form-stage.anim-enter-right.anim-run {
            transform: translateX(0);
            opacity: 1;
        }

        /* Entering from Left */
        .form-stage.anim-enter-left {
            display: block !important;
            position: relative !important;
            z-index: 2;
            transform: translateX(-36px);
            opacity: 0;
            transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), opacity 420ms cubic-bezier(0.22, 1, 0.36, 1) !important;
        }
        .form-stage.anim-enter-left.anim-run {
            transform: translateX(0);
            opacity: 1;
        }

        /* Brand Header for Login (Gemini Canvas Layout) */
        .brand-header-flex {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }

        .brand-icon-box {
            width: 48px;
            height: 48px;
            background: #DCF8C6;
            border: 1.5px solid #BBF7D0;
            border-radius: 16px;
            color: #00A884;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 168, 132, 0.12);
        }

        [data-bs-theme="dark"] .brand-icon-box,
        [data-theme="dark"] .brand-icon-box {
            background: #18372B;
            border-color: #1F4535;
            color: #00A884;
        }

        .brand-eyebrow {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--wa-teal);
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .pill-badge-rw {
            background: #DCF8C6;
            color: #0b6740;
            border: 1px solid #BBF7D0;
            font-size: 0.78rem;
            font-weight: 800;
            padding: 3px 12px;
            border-radius: 999px;
            letter-spacing: 0.02em;
        }

        [data-bs-theme="dark"] .pill-badge-rw,
        [data-theme="dark"] .pill-badge-rw {
            background: #18372B;
            color: #00A884;
            border-color: #1F4535;
        }

        /* Form Header Icons & Badges */
        .form-badge-icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: #DCF8C6;
            border-radius: 16px;
            color: #00A884;
            font-size: 1.3rem;
            margin-bottom: 14px;
            border: 1px solid #BBF7D0;
            box-shadow: 0 2px 8px rgba(0, 168, 132, 0.08);
        }

        [data-bs-theme="dark"] .form-badge-icon-wrap,
        [data-theme="dark"] .form-badge-icon-wrap {
            background: #18372B;
            border-color: #1F4535;
            color: #00A884;
        }

        .badge-brand-subtle {
            display: inline-block;
            background: var(--wa-mint);
            color: #0b6740;
            font-weight: 800;
            font-size: 0.76rem;
            padding: 3px 10px;
            border-radius: 999px;
            letter-spacing: 0.04em;
            vertical-align: middle;
            margin-left: 8px;
        }

        .form-main-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--wa-dark);
            letter-spacing: -0.025em;
            line-height: 1.25;
            margin-bottom: 8px;
        }

        .form-main-desc {
            font-size: 0.92rem;
            color: var(--wa-muted);
            line-height: 1.5;
            margin-bottom: 24px;
        }

        /* Inputs & Labels */
        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--wa-dark);
            margin-bottom: 6px;
            display: block;
        }

        .input-group-wa {
            display: flex;
            align-items: center;
            background: var(--wa-card);
            border: 1.5px solid var(--wa-border);
            border-radius: var(--radius-input);
            overflow: hidden;
            min-height: 46px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-group-wa:focus-within {
            border-color: var(--wa-teal);
            box-shadow: 0 0 0 3px rgba(0, 168, 132, 0.16);
        }

        .input-group-wa.has-error {
            border-color: var(--wa-danger) !important;
            animation: errShake 0.3s ease;
        }

        @keyframes errShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        .input-icon-wa {
            padding: 0 14px;
            color: var(--wa-placeholder);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            transition: color 0.2s ease;
        }

        .input-group-wa:focus-within .input-icon-wa {
            color: var(--wa-teal);
        }

        .input-group-wa input,
        .input-group-wa select {
            width: 100%;
            border: none;
            padding: 11px 14px 11px 0;
            font-size: 0.93rem;
            color: var(--wa-dark);
            background: transparent;
            outline: none;
            font-family: inherit;
        }

        .input-group-wa input::placeholder {
            color: var(--wa-placeholder);
            opacity: 0.9;
        }

        .password-toggle-btn {
            background: none;
            border: none;
            padding: 0 14px;
            color: var(--wa-placeholder);
            cursor: pointer;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            height: 100%;
            transition: color 0.18s ease;
        }

        .password-toggle-btn:hover {
            color: var(--wa-teal);
        }

        /* Buttons */
        .btn-teal-primary {
            width: 100%;
            background-color: var(--wa-teal);
            border: 1.5px solid var(--wa-teal);
            color: #FFFFFF;
            padding: 12px 20px;
            min-height: 48px;
            border-radius: var(--radius-btn);
            font-weight: 700;
            font-size: 0.96rem;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0, 168, 132, 0.25);
            cursor: pointer;
            transition: all 0.22s ease;
        }

        .btn-teal-primary:hover {
            background-color: var(--wa-teal-hover);
            border-color: var(--wa-teal-hover);
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 168, 132, 0.35);
        }

        .btn-teal-primary:active {
            transform: translateY(0);
        }

        .link-teal {
            color: var(--wa-teal);
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
            transition: color 0.18s ease;
        }

        .link-teal:hover {
            color: var(--wa-teal-hover);
            text-decoration: underline;
        }

        /* Alerts */
        .alert-wa {
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid transparent;
            line-height: 1.45;
        }

        .alert-wa-danger {
            background: var(--wa-danger-soft);
            border-color: rgba(234, 0, 56, 0.2);
            color: #9c0025;
        }

        .alert-wa-success {
            background: var(--wa-mint);
            border-color: rgba(0, 168, 132, 0.25);
            color: #0b6740;
        }

        .alert-wa-info {
            background: var(--wa-info-soft);
            border-color: rgba(2, 126, 181, 0.25);
            color: #015277;
        }

        /* Register Form Groups */
        .register-group-box {
            background: var(--wa-bg);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--wa-border);
        }

        .reg-group-title {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--wa-teal);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .counter-badge {
            font-size: 0.72rem;
            color: var(--wa-muted);
            float: right;
            font-weight: 600;
        }

        /* ==================== FORGOT PASSWORD (3-STEP) ==================== */
        .forgot-steps-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--wa-bg);
            padding: 10px 14px;
            border-radius: 14px;
            border: 1px solid var(--wa-border);
            margin-bottom: 22px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--wa-muted);
            transition: color 0.3s ease;
        }

        .step-item.active {
            color: var(--wa-teal);
        }

        .step-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--wa-border);
            color: var(--wa-muted);
            font-size: 0.72rem;
            font-weight: 800;
            transition: all 0.3s ease;
        }

        .step-item.active .step-num {
            background: var(--wa-teal);
            color: #FFFFFF;
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: var(--wa-border);
            margin: 0 8px;
            transition: background 0.3s ease;
        }

        .step-line.active {
            background: var(--wa-teal);
        }

        /* OTP 6 Digit Grid */
        .otp-boxes-wrap {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin: 18px 0;
        }

        .otp-box-digit {
            width: 100%;
            height: 54px;
            text-align: center;
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--wa-dark);
            background: var(--wa-card);
            border: 2px solid var(--wa-border);
            border-radius: 12px;
            outline: none;
            transition: all 0.2s ease;
        }

        .otp-box-digit:focus {
            border-color: var(--wa-teal);
            box-shadow: 0 0 0 3px rgba(0, 168, 132, 0.16);
            transform: scale(1.04);
        }

        .masked-email-card {
            background: var(--wa-bg);
            border: 1px solid var(--wa-border);
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--wa-dark);
        }

        .btn-change-email {
            background: none;
            border: none;
            color: var(--wa-teal);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            padding: 2px 6px;
        }

        .btn-change-email:hover {
            text-decoration: underline;
        }

        .btn-resend-link {
            background: none;
            border: none;
            color: var(--wa-teal);
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-resend-link:hover:not(:disabled) {
            background: var(--wa-mint);
        }

        .btn-resend-link:disabled {
            color: var(--wa-muted);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .spam-info-note {
            background: var(--wa-bg);
            border: 1px dashed var(--wa-border);
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.82rem;
            color: var(--wa-muted);
            margin: 16px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .link-back-login {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--wa-muted);
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            margin-top: 18px;
            transition: color 0.18s ease;
        }

        .link-back-login:hover {
            color: var(--wa-teal);
        }

        /* ==================== MOBILE LAYOUT (< 768px) ==================== */
        @media (max-width: 767.98px) {
            body {
                padding: 0 10px 70px 10px;
                align-items: flex-start;
            }

            .auth-container {
                max-width: 100%;
                padding-top: max(8px, env(safe-area-inset-top, 8px));
            }

            .auth-topbar {
                padding: 16px 16px 12px 16px !important;
                margin-bottom: 8px !important;
            }

            .auth-card {
                border-radius: 20px;
                min-height: auto;
            }

            .auth-card-inner {
                display: flex;
                flex-direction: column;
            }

            .forms-col {
                width: 100% !important;
                transform: none !important;
                padding: 16px 14px 28px 14px !important;
            }

            /* Hide full desktop showcase column on mobile (< 768px) */
            .showcase-col {
                display: none !important;
            }

            /* Mobile Quick Tab Switcher with Glider */
            .mobile-tabs-bar {
                display: flex !important;
                position: relative;
                background: var(--wa-bg);
                padding: 4px;
                margin: 4px 0 16px 0;
                border-radius: 14px;
                border: 1px solid var(--wa-border);
            }

            .mobile-tabs-glider {
                position: absolute;
                top: 4px;
                left: 4px;
                width: calc(50% - 4px);
                height: calc(100% - 8px);
                background: #FFFFFF;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(11, 20, 26, 0.08);
                transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), opacity 300ms ease;
                pointer-events: none;
                z-index: 1;
            }

            [data-bs-theme="dark"] .mobile-tabs-glider,
            [data-theme="dark"] .mobile-tabs-glider {
                background: var(--wa-card);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
            }

            .mobile-tab-btn {
                flex: 1;
                border: none;
                background: transparent !important;
                padding: 8px 12px;
                border-radius: 10px;
                font-size: 0.85rem;
                font-weight: 700;
                color: var(--wa-muted);
                cursor: pointer;
                transition: color 300ms ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                position: relative;
                z-index: 2;
            }

            .mobile-tab-btn.active {
                color: var(--wa-teal) !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .input-group-wa input,
            .input-group-wa select {
                font-size: 16px !important; /* iOS Zoom prevention */
            }

            .otp-box-digit {
                height: 46px;
                font-size: 1.25rem;
            }
        }

        @media (min-width: 768px) {
            .mobile-tabs-bar {
                display: none !important;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Top Bar Above Card -->
        <div class="auth-topbar pt-4 pb-3 px-4 flex items-center justify-between gap-2 mb-3">
            <a href="{{ url('/') }}" class="btn-back-home text-xs px-3 py-1.5 whitespace-nowrap">
                <i class="fas fa-arrow-left"></i>
                <span class="d-none d-sm-inline">Kembali ke Beranda</span>
                <span class="d-inline d-sm-none">← Beranda</span>
            </a>
            <div class="d-flex align-items-center gap-1.5 shrink-0">
                <div class="top-tag text-xs px-3 py-1.5 whitespace-nowrap">
                    <span class="status-dot-pulse"></span>
                    <span class="d-none d-sm-inline">SMART RW 01 • Layanan Warga Terpadu</span>
                    <span class="d-inline d-sm-none">SMART RW 01</span>
                </div>
                <button type="button" id="themeToggleBtn" class="topbar-theme-pill text-xs px-2.5 py-1.5 whitespace-nowrap" title="Ganti Mode Terang/Gelap" aria-label="Toggle Theme">
                    <i class="fas fa-sun" id="themeToggleIcon"></i>
                </button>
            </div>
        </div>

        <!-- Main Card -->
        <div class="auth-card mode-login" id="mainAuthCard">
            <div class="auth-card-inner">
                
                <!-- ==================== FORMS COLUMN ==================== -->
                <div class="forms-col" id="formsCol">
                    
                    <!-- Mobile Tab Switcher (< 768px) -->
                    <div class="mobile-tabs-bar" id="mobileTabsBar">
                        <div class="mobile-tabs-glider" id="mobileTabsGlider"></div>
                        <button type="button" class="mobile-tab-btn active" id="mobBtnLogin" onclick="switchMode('login')">
                            <i class="fas fa-right-to-bracket"></i> Masuk Akun
                        </button>
                        <button type="button" class="mobile-tab-btn" id="mobBtnRegister" onclick="switchMode('register')">
                            <i class="fas fa-user-plus"></i> Pendaftaran
                        </button>
                    </div>

                    <!-- Forms Slider Container -->
                    <div class="forms-slider-container overflow-hidden">
                        <!-- ==================== FORM 1: MASUK KE AKUN ==================== -->
                        <div class="form-stage active" id="panelLogin">
                            <div class="brand-header-flex">
                                <div class="brand-icon-box">
                                    <i class="fas fa-house-chimney"></i>
                                </div>
                                <div>
                                    <div class="brand-eyebrow">SMART RW 01</div>
                                    <h1 class="form-main-title mb-0">Masuk ke Akun</h1>
                                </div>
                            </div>
                            <p class="form-main-desc">
                                Silakan masuk menggunakan email terdaftar untuk mengakses layanan surat, iuran kas, dan informasi warga.
                            </p>

                        <!-- Alerts -->
                        @if($errors->any() && !old('name') && !session('from_forgot'))
                            <div class="alert-wa alert-wa-danger">
                                <i class="fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0"></i>
                                <div>{{ $errors->first() }}</div>
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="alert-wa alert-wa-success">
                                <i class="fas fa-circle-check mt-0.5 fs-6 flex-shrink-0"></i>
                                <div>{{ session('status') }}</div>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert-wa alert-wa-success">
                                <i class="fas fa-circle-check mt-0.5 fs-6 flex-shrink-0"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert-wa alert-wa-danger">
                                <i class="fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginFormElem">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label-custom">Alamat Email</label>
                                <div class="input-group-wa @error('email') has-error @enderror">
                                    <span class="input-icon-wa"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" id="loginEmail" placeholder="nama@email.com" required autofocus value="{{ old('email') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom">Kata Sandi</label>
                                <div class="input-group-wa @error('password') has-error @enderror">
                                    <span class="input-icon-wa"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" id="loginPassword" placeholder="••••••••" required>
                                    <button type="button" class="password-toggle-btn" aria-label="Tampilkan password" data-target="loginPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                    <label class="form-check-label small" for="remember" style="color: var(--wa-muted); font-weight: 600;">Ingat Saya</label>
                                </div>
                                <a href="#forgot" onclick="switchMode('forgot'); return false;" class="link-teal">Lupa password?</a>
                            </div>

                            <button type="submit" class="btn-teal-primary" id="btnLoginSubmit">
                                <i class="fas fa-right-to-bracket"></i>
                                <span>Masuk ke Dashboard</span>
                            </button>
                        </form>
                    </div>

                    <!-- ==================== FORM 2: PENDAFTARAN WARGA ==================== -->
                    <div class="form-stage" id="panelRegister">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <h2 class="form-main-title mb-0">Pendaftaran Warga</h2>
                            <span class="pill-badge-rw">RW 01</span>
                        </div>
                        <p class="form-main-desc mb-3">
                            Lengkapi data kependudukan sesuai KTP & Kartu Keluarga
                        </p>

                        @if($errors->any() && (old('name') || $errors->has('nik') || $errors->has('no_kk') || $errors->has('rt_id')))
                            <div class="alert-wa alert-wa-danger">
                                <i class="fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0"></i>
                                <div>
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" id="registerFormElem">
                            @csrf
                            @php
                                $regRts = \App\Models\Rt::orderBy('kode_rt')->get();
                            @endphp

                            <!-- Section 1: Wilayah Domisili -->
                            <div class="register-group-box">
                                <div class="reg-group-title">
                                    <i class="fas fa-map-location-dot"></i> Wilayah & Domisili
                                </div>
                                <div class="row g-2">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Wilayah RW</label>
                                        <div class="input-group-wa" style="background: var(--wa-bg);">
                                            <span class="input-icon-wa"><i class="fas fa-building"></i></span>
                                            <input type="text" class="fw-bold" value="SMART RW 01" readonly style="color: var(--wa-teal);">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Wilayah RT Domisili <span class="text-danger">*</span></label>
                                        <div class="input-group-wa @error('rt_id') has-error @enderror">
                                            <span class="input-icon-wa"><i class="fas fa-map-marker-alt"></i></span>
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

                            <!-- Section 2: Identitas Kependudukan -->
                            <div class="register-group-box">
                                <div class="reg-group-title">
                                    <i class="fas fa-id-card"></i> Identitas Kependudukan
                                </div>
                                
                                <div class="mb-2.5">
                                    <label class="form-label-custom">Nama Lengkap Sesuai KTP <span class="text-danger">*</span></label>
                                    <div class="input-group-wa @error('name') has-error @enderror">
                                        <span class="input-icon-wa"><i class="fas fa-user"></i></span>
                                        <input type="text" name="name" placeholder="Nama lengkap sesuai KTP" value="{{ old('name') }}" required>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2.5">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">
                                            Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span>
                                            <span class="counter-badge" id="nikCounter">0/16</span>
                                        </label>
                                        <div class="input-group-wa @error('nik') has-error @enderror">
                                            <span class="input-icon-wa"><i class="fas fa-address-card"></i></span>
                                            <input type="text" name="nik" id="registerNik" placeholder="16 digit NIK" value="{{ old('nik') }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">
                                            Nomor Kartu Keluarga (KK) <span class="text-danger">*</span>
                                            <span class="counter-badge" id="kkCounter">0/16</span>
                                        </label>
                                        <div class="input-group-wa @error('no_kk') has-error @enderror">
                                            <span class="input-icon-wa"><i class="fas fa-file-invoice"></i></span>
                                            <input type="text" name="no_kk" id="registerNoKk" placeholder="16 digit No. KK" value="{{ old('no_kk') }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label-custom">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <div class="input-group-wa @error('jenis_kelamin') has-error @enderror">
                                        <span class="input-icon-wa"><i class="fas fa-venus-mars"></i></span>
                                        <select name="jenis_kelamin" required>
                                            <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih jenis kelamin</option>
                                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Kontak Warga & Akun -->
                            <div class="register-group-box">
                                <div class="reg-group-title">
                                    <i class="fas fa-address-book"></i> Kontak Warga & Akun
                                </div>
                                <div class="row g-2 mb-2.5">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                        <div class="input-group-wa @error('no_hp') has-error @enderror">
                                            <span class="input-icon-wa"><i class="fas fa-phone"></i></span>
                                            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Alamat Email <span class="text-danger">*</span></label>
                                        <div class="input-group-wa @error('email') has-error @enderror">
                                            <span class="input-icon-wa"><i class="fas fa-envelope"></i></span>
                                            <input type="email" name="email" placeholder="nama@email.com" value="{{ old('email') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Kata Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group-wa @error('password') has-error @enderror">
                                            <span class="input-icon-wa"><i class="fas fa-lock"></i></span>
                                            <input type="password" name="password" id="regPassword" placeholder="Min. 8 karakter" required>
                                            <button type="button" class="password-toggle-btn" aria-label="Tampilkan password" data-target="regPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label-custom">Konfirmasi Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group-wa">
                                            <span class="input-icon-wa"><i class="fas fa-lock"></i></span>
                                            <input type="password" name="password_confirmation" id="regPasswordConfirm" placeholder="Ulangi sandi" required>
                                            <button type="button" class="password-toggle-btn" aria-label="Tampilkan password" data-target="regPasswordConfirm">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert-wa alert-wa-info mb-4">
                                <i class="fas fa-circle-info mt-0.5 fs-6 flex-shrink-0"></i>
                                <div>Setelah mendaftar, akun Anda akan diverifikasi oleh Ketua RT sesuai data kependudukan wilayah RW 01.</div>
                            </div>

                            <button type="submit" class="btn-teal-primary" id="btnRegisterSubmit">
                                <i class="fas fa-user-plus"></i>
                                <span>Daftar Sebagai Warga</span>
                            </button>
                        </form>
                    </div>

                    <!-- ==================== FORM 3: LUPA KATA SANDI (3 TAHAP) ==================== -->
                    <div class="form-stage" id="panelForgot">
                        <div class="form-badge-icon-wrap w-11 h-11 rounded-2xl bg-[#DCF8C6] text-[#00A884] flex items-center justify-center mb-3">
                            <i class="fas fa-key"></i>
                        </div>
                        <h2 class="form-main-title">
                            Lupa Kata Sandi <span class="badge-brand-subtle">SMART RW 01</span>
                        </h2>

                        <!-- 3-Step Progress Bar -->
                        <div class="forgot-steps-bar">
                            <div class="step-item active" id="fStepBar1">
                                <span class="step-num" id="fStepNum1">1</span>
                                <span class="step-label">Email</span>
                            </div>
                            <div class="step-line" id="fStepLine1"></div>
                            <div class="step-item" id="fStepBar2">
                                <span class="step-num" id="fStepNum2">2</span>
                                <span class="step-label">Verifikasi OTP</span>
                            </div>
                            <div class="step-line" id="fStepLine2"></div>
                            <div class="step-item" id="fStepBar3">
                                <span class="step-num" id="fStepNum3">3</span>
                                <span class="step-label">Sandi Baru</span>
                            </div>
                        </div>

                        <!-- Dynamic Alert Feedback for Forgot Password -->
                        <div id="forgotAlertBox" class="alert-wa d-none" role="alert">
                            <i class="fas fa-circle-info mt-0.5 fs-6 flex-shrink-0" id="forgotAlertIcon"></i>
                            <div id="forgotAlertText"></div>
                        </div>

                        <!-- ===== TAHAP 1: INPUT EMAIL ===== -->
                        <div id="forgotSubStep1">
                            <p class="form-main-desc">
                                Masukkan alamat email terdaftar Anda. Kami akan mengirimkan 6 digit kode OTP verifikasi untuk mengatur ulang kata sandi akun warga.
                            </p>

                            <div class="mb-4">
                                <label class="form-label-custom">Alamat Email Terdaftar <span class="text-danger">*</span></label>
                                <div class="input-group-wa" id="forgotEmailGroup">
                                    <span class="input-icon-wa"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="forgotEmailInput" placeholder="nama@email.com" value="{{ old('email') }}" required>
                                </div>
                                <small class="text-muted d-block mt-1.5" style="font-size: 0.8rem;">
                                    Gunakan email yang terdaftar pada sistem SMART RW 01.
                                </small>
                            </div>

                            <button type="button" class="btn-teal-primary" id="btnForgotStep1Submit" onclick="submitForgotStep1()">
                                <i class="fas fa-paper-plane"></i>
                                <span>Kirim Kode OTP</span>
                            </button>
                        </div>

                        <!-- ===== TAHAP 2: VERIFIKASI OTP (6 DIGIT) ===== -->
                        <div id="forgotSubStep2" class="d-none">
                            <p class="form-main-desc mb-2">
                                Masukkan 6 digit kode verifikasi yang telah dikirimkan ke email terdaftar Anda (berlaku 10 menit).
                            </p>

                            <!-- Masked Email Card -->
                            <div class="masked-email-card">
                                <div>
                                    <i class="fas fa-envelope text-success me-2"></i>
                                    <span id="displayMaskedEmail">t*****@gmail.com</span>
                                </div>
                                <button type="button" class="btn-change-email" onclick="goToForgotStep(1)">
                                    Ganti Email
                                </button>
                            </div>

                            <!-- 6-Digit OTP Box Grid -->
                            <div class="text-center">
                                <label class="form-label-custom mb-1">
                                    Kode OTP 6 Digit <span class="text-danger">*</span>
                                </label>
                                <div class="otp-boxes-wrap" id="otpBoxesWrap">
                                    <input type="text" class="otp-box-digit" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-idx="0" autocomplete="off" autofocus>
                                    <input type="text" class="otp-box-digit" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-idx="1" autocomplete="off">
                                    <input type="text" class="otp-box-digit" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-idx="2" autocomplete="off">
                                    <input type="text" class="otp-box-digit" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-idx="3" autocomplete="off">
                                    <input type="text" class="otp-box-digit" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-idx="4" autocomplete="off">
                                    <input type="text" class="otp-box-digit" maxlength="1" inputmode="numeric" pattern="[0-9]*" data-idx="5" autocomplete="off">
                                </div>
                            </div>

                            <button type="button" class="btn-teal-primary mt-2" id="btnForgotStep2Submit" onclick="submitForgotStep2()">
                                <i class="fas fa-check-circle"></i>
                                <span>Verifikasi OTP</span>
                            </button>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button type="button" class="btn-resend-link" id="btnResendOtp" onclick="resendOtpCode()">
                                    <i class="fas fa-rotate-right"></i>
                                    <span id="resendBtnText">Kirim Ulang Kode OTP</span>
                                </button>
                            </div>

                            <div class="spam-info-note">
                                <i class="fas fa-circle-info text-success fs-6 flex-shrink-0"></i>
                                <div>Tidak menerima email? Cek folder Spam atau hubungi Ketua RT Anda untuk bantuan langsung.</div>
                            </div>
                        </div>

                        <!-- ===== TAHAP 3: UPDATE KATA SANDI BARU ===== -->
                        <div id="forgotSubStep3" class="d-none">
                            <p class="form-main-desc">
                                OTP berhasil diverifikasi! Silakan buat kata sandi baru untuk mengamankan akun warga Anda.
                            </p>

                            <input type="hidden" id="resetTokenInput">
                            <input type="hidden" id="resetEmailInput">

                            <div class="mb-3">
                                <label class="form-label-custom">Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group-wa" id="newPassGroup">
                                    <span class="input-icon-wa"><i class="fas fa-key"></i></span>
                                    <input type="password" id="forgotNewPassword" placeholder="Minimal 8 karakter" required>
                                    <button type="button" class="password-toggle-btn" aria-label="Tampilkan password" data-target="forgotNewPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label-custom">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                <div class="input-group-wa" id="newPassConfirmGroup">
                                    <span class="input-icon-wa"><i class="fas fa-lock"></i></span>
                                    <input type="password" id="forgotNewPasswordConfirm" placeholder="Ulangi sandi baru" required>
                                    <button type="button" class="password-toggle-btn" aria-label="Tampilkan password" data-target="forgotNewPasswordConfirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="button" class="btn-teal-primary" id="btnForgotStep3Submit" onclick="submitForgotStep3()">
                                <i class="fas fa-shield-halved"></i>
                                <span>Simpan Kata Sandi Baru</span>
                            </button>
                        </div>

                        <div class="text-center mt-3 pt-3 border-top" style="border-color: var(--wa-border) !important;">
                            <a href="#login" onclick="switchMode('login'); return false;" class="link-back-login">
                                <i class="fas fa-arrow-left"></i>
                                <span>Kembali ke Halaman Masuk</span>
                            </a>
                        </div>
                    </div>
                    </div> <!-- /forms-slider-container -->
                </div>

                <!-- ==================== SHOWCASE COLUMN (MINT GREEN PANEL) ==================== -->
                <div class="showcase-col hidden md:flex">
                    <div class="showcase-box">
                        <div class="showcase-doodle-bg"></div>

                        <!-- Top Pills -->
                        <div class="showcase-top-pills">
                            <span class="pill-realtime">
                                <span class="status-dot-pulse"></span>
                                <span>Terhubung Real-Time</span>
                            </span>
                            <span class="pill-rw-code">RW 01 Digital</span>
                        </div>

                        <!-- Center Hub Motion Graphic -->
                        <div class="hub-container">
                            <!-- SVG Connecting Lines -->
                            <svg class="hub-svg-lines" viewBox="0 0 380 280">
                                <!-- Line to Top-Left -->
                                <path d="M 190 140 Q 130 90 85 55" fill="none" class="hub-line-dash" />
                                <!-- Line to Top-Right -->
                                <path d="M 190 140 Q 250 90 295 55" fill="none" class="hub-line-dash" />
                                <!-- Line to Bottom-Left -->
                                <path d="M 190 140 Q 130 190 85 225" fill="none" class="hub-line-dash" />
                                <!-- Line to Bottom-Right -->
                                <path d="M 190 140 Q 250 190 295 225" fill="none" class="hub-line-dash" />
                            </svg>

                            <!-- Center Node -->
                            <div class="hub-center-node">
                                <div class="hub-pulse-ring"></div>
                                <i class="fas fa-house-chimney"></i>
                            </div>

                            <!-- Floating Badge 1: Surat Pengantar -->
                            <div class="floating-hub-card fhc-top-left">
                                <div class="fhc-icon-box"><i class="fas fa-check"></i></div>
                                <div>
                                    <div class="fhc-title">Surat Pengantar</div>
                                    <div class="fhc-sub">Disetujui Ketua RT <span class="fhc-check-double">✓✓</span></div>
                                </div>
                            </div>

                            <!-- Floating Badge 2: Kas & Iuran RT -->
                            <div class="floating-hub-card fhc-top-right">
                                <div class="fhc-icon-box fhc-badge-rp">Rp</div>
                                <div>
                                    <div class="fhc-title">Kas & Iuran RT</div>
                                    <div class="fhc-sub">Laporan Real-Time</div>
                                </div>
                            </div>

                            <!-- Floating Badge 3: Ronda & CCTV -->
                            <div class="floating-hub-card fhc-bottom-left">
                                <div class="fhc-icon-box"><i class="fas fa-shield-halved"></i></div>
                                <div>
                                    <div class="fhc-title">Ronda & CCTV</div>
                                    <div class="fhc-sub">Lingkungan Aman</div>
                                </div>
                            </div>

                            <!-- Floating Badge 4: Notifikasi Warga -->
                            <div class="floating-hub-card fhc-bottom-right">
                                <div class="fhc-icon-box"><i class="fas fa-plus"></i></div>
                                <div>
                                    <div class="fhc-title">Notifikasi Warga</div>
                                    <div class="fhc-sub">Info & Pengumuman</div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Bottom Action CTA (Desktop) -->
                        <div class="showcase-cta-wrap">
                            <!-- In Login Mode: Show CTA to Register -->
                            <div class="cta-scene active" id="ctaSceneLogin">
                                <h3 class="cta-heading">Belum Gabung di RW 01?</h3>
                                <p class="cta-desc">
                                    Daftarkan data diri & nomor KK Anda untuk terhubung langsung dengan pengurus RT dan layanan digital warga.
                                </p>
                                <button type="button" class="btn-showcase-action" onclick="switchMode('register')">
                                    DAFTAR WARGA SEKARANG
                                </button>
                            </div>

                            <!-- In Register Mode: Show CTA to Login -->
                            <div class="cta-scene" id="ctaSceneRegister">
                                <h3 class="cta-heading">Sudah Punya Akun Warga?</h3>
                                <p class="cta-desc">
                                    Masuk kembali menggunakan email terdaftar untuk mengecek status verifikasi akun atau layanan warga lainnya.
                                </p>
                                <button type="button" class="btn-showcase-action" onclick="switchMode('login')">
                                    MASUK KE AKUN
                                </button>
                            </div>

                            <!-- In Forgot Mode: Show CTA back to Login -->
                            <div class="cta-scene" id="ctaSceneForgot">
                                <h3 class="cta-heading">Ingat Kata Sandi?</h3>
                                <p class="cta-desc">
                                    Jika Anda mengingat kata sandi atau sudah selesai mengatur ulang, silakan masuk kembali ke akun Anda.
                                </p>
                                <button type="button" class="btn-showcase-action" onclick="switchMode('login')">
                                    KEMBALI KE LOGIN
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // State variables
        let currentMode = 'login'; // 'login', 'register', 'forgot'
        let currentForgotStep = 1;  // 1, 2, 3
        let resendTimerInterval = null;
        let resendCountdown = 60;

        const mainAuthCard = document.getElementById('mainAuthCard');
        const panelLogin = document.getElementById('panelLogin');
        const panelRegister = document.getElementById('panelRegister');
        const panelForgot = document.getElementById('panelForgot');

        const ctaSceneLogin = document.getElementById('ctaSceneLogin');
        const ctaSceneRegister = document.getElementById('ctaSceneRegister');
        const ctaSceneForgot = document.getElementById('ctaSceneForgot');

        const mobBtnLogin = document.getElementById('mobBtnLogin');
        const mobBtnRegister = document.getElementById('mobBtnRegister');
        const mobGlider = document.getElementById('mobileTabsGlider');
        let isTransitioning = false;

        function switchMode(newMode, animate = true) {
            if (newMode === currentMode && document.querySelector('.form-stage.active')) return;
            if (isTransitioning) return;

            const oldMode = currentMode;
            currentMode = newMode;

            // Desktop layout card classes
            mainAuthCard.classList.remove('mode-login', 'mode-register', 'mode-forgot');
            mainAuthCard.classList.add('mode-' + newMode);

            // Desktop showcase CTA scenes
            [ctaSceneLogin, ctaSceneRegister, ctaSceneForgot].forEach(el => el && el.classList.remove('active'));
            if (newMode === 'login' && ctaSceneLogin) ctaSceneLogin.classList.add('active');
            if (newMode === 'register' && ctaSceneRegister) ctaSceneRegister.classList.add('active');
            if (newMode === 'forgot' && ctaSceneForgot) ctaSceneForgot.classList.add('active');

            // Mobile switcher buttons
            if (mobBtnLogin) mobBtnLogin.classList.toggle('active', newMode === 'login');
            if (mobBtnRegister) mobBtnRegister.classList.toggle('active', newMode === 'register');

            // Mobile glider slide
            if (mobGlider) {
                if (newMode === 'login') {
                    mobGlider.style.opacity = '1';
                    mobGlider.style.transform = 'translateX(0%)';
                } else if (newMode === 'register') {
                    mobGlider.style.opacity = '1';
                    mobGlider.style.transform = 'translateX(100%)';
                } else {
                    mobGlider.style.opacity = '0';
                }
            }

            // Update URL hash
            if (newMode === 'login') {
                if (window.location.hash) {
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                }
            } else if (newMode === 'register') {
                window.location.hash = 'register';
            } else if (newMode === 'forgot') {
                window.location.hash = 'forgot';
                goToForgotStep(1);
            }

            const panels = {
                login: panelLogin,
                register: panelRegister,
                forgot: panelForgot
            };
            const oldPanel = panels[oldMode];
            const newPanel = panels[newMode];

            if (!oldPanel || !newPanel || !animate) {
                Object.values(panels).forEach(p => {
                    if (p) {
                        p.className = 'form-stage';
                        p.style.display = 'none';
                    }
                });
                if (newPanel) {
                    newPanel.classList.add('active');
                    newPanel.style.display = 'block';
                }
                return;
            }

            isTransitioning = true;

            const goingRight = (oldMode === 'login' && (newMode === 'register' || newMode === 'forgot')) ||
                               (oldMode === 'forgot' && newMode === 'register');

            const exitClass = goingRight ? 'anim-exit-left' : 'anim-exit-right';
            const enterClass = goingRight ? 'anim-enter-right' : 'anim-enter-left';

            const animClasses = ['anim-exit-left', 'anim-exit-right', 'anim-enter-right', 'anim-enter-left', 'anim-run', 'active'];

            // Prepare old panel exit
            oldPanel.classList.remove('active');
            oldPanel.classList.add(exitClass);

            // Prepare new panel enter
            newPanel.className = 'form-stage ' + enterClass;

            // Trigger reflow for CSS transition
            void newPanel.offsetWidth;

            // Trigger 420ms transitions
            oldPanel.classList.add('anim-run');
            newPanel.classList.add('anim-run');

            setTimeout(() => {
                animClasses.forEach(c => oldPanel.classList.remove(c));
                oldPanel.style.display = 'none';

                animClasses.forEach(c => newPanel.classList.remove(c));
                newPanel.classList.add('active');
                newPanel.style.display = 'block';

                isTransitioning = false;

                if (window.innerWidth < 768 && newMode !== 'login') {
                    mainAuthCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 420);
        }

        // ==================== LUPA KATA SANDI (3 TAHAP) ====================
        const forgotSubStep1 = document.getElementById('forgotSubStep1');
        const forgotSubStep2 = document.getElementById('forgotSubStep2');
        const forgotSubStep3 = document.getElementById('forgotSubStep3');

        const fStepBar1 = document.getElementById('fStepBar1');
        const fStepBar2 = document.getElementById('fStepBar2');
        const fStepBar3 = document.getElementById('fStepBar3');
        const fStepLine1 = document.getElementById('fStepLine1');
        const fStepLine2 = document.getElementById('fStepLine2');

        const forgotAlertBox = document.getElementById('forgotAlertBox');
        const forgotAlertIcon = document.getElementById('forgotAlertIcon');
        const forgotAlertText = document.getElementById('forgotAlertText');

        function showForgotAlert(type, message) {
            forgotAlertBox.className = 'alert-wa alert-wa-' + type;
            forgotAlertIcon.className = type === 'danger' 
                ? 'fas fa-circle-exclamation mt-0.5 fs-6 flex-shrink-0' 
                : 'fas fa-circle-check mt-0.5 fs-6 flex-shrink-0';
            forgotAlertText.innerText = message;
            forgotAlertBox.classList.remove('d-none');
        }

        function hideForgotAlert() {
            forgotAlertBox.classList.add('d-none');
        }

        function goToForgotStep(step) {
            currentForgotStep = step;
            hideForgotAlert();

            forgotSubStep1.classList.add('d-none');
            forgotSubStep2.classList.add('d-none');
            forgotSubStep3.classList.add('d-none');

            fStepBar1.classList.remove('active');
            fStepBar2.classList.remove('active');
            fStepBar3.classList.remove('active');
            fStepLine1.classList.remove('active');
            fStepLine2.classList.remove('active');

            if (step === 1) {
                forgotSubStep1.classList.remove('d-none');
                fStepBar1.classList.add('active');
                setTimeout(() => document.getElementById('forgotEmailInput')?.focus(), 150);
            } else if (step === 2) {
                forgotSubStep2.classList.remove('d-none');
                fStepBar1.classList.add('active');
                fStepLine1.classList.add('active');
                fStepBar2.classList.add('active');
                startResendTimer();
                setTimeout(() => {
                    document.querySelector('.otp-box-digit[data-idx="0"]')?.focus();
                }, 150);
            } else if (step === 3) {
                forgotSubStep3.classList.remove('d-none');
                fStepBar1.classList.add('active');
                fStepLine1.classList.add('active');
                fStepBar2.classList.add('active');
                fStepLine2.classList.add('active');
                fStepBar3.classList.add('active');
                setTimeout(() => document.getElementById('forgotNewPassword')?.focus(), 150);
            }
        }

        // TAHAP 1: Kirim OTP
        async function submitForgotStep1() {
            hideForgotAlert();
            const emailInput = document.getElementById('forgotEmailInput');
            const email = (emailInput.value || '').trim();

            if (!email || !email.includes('@')) {
                showForgotAlert('danger', 'Harap masukkan alamat email yang valid.');
                emailInput.focus();
                return;
            }

            const btn = document.getElementById('btnForgotStep1Submit');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengirimkan OTP...';
            btn.disabled = true;

            try {
                const res = await fetch("{{ route('password.email') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    document.getElementById('displayMaskedEmail').innerText = data.masked_email || email;
                    document.getElementById('resetEmailInput').value = data.email || email;
                    showForgotAlert('success', data.message || 'Kode OTP telah dikirimkan ke email terdaftar Anda.');
                    goToForgotStep(2);
                } else {
                    const msg = (data.errors && data.errors.email) ? data.errors.email[0] : (data.message || 'Gagal mengirim kode OTP.');
                    showForgotAlert('danger', msg);
                }
            } catch (err) {
                showForgotAlert('danger', 'Koneksi bermasalah. Harap periksa jaringan Anda.');
            } finally {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }
        }

        // TAHAP 2: Verifikasi 6 Digit OTP
        async function submitForgotStep2() {
            hideForgotAlert();
            const otpBoxes = document.querySelectorAll('.otp-box-digit');
            let otp = '';
            otpBoxes.forEach(b => { otp += b.value; });

            if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
                showForgotAlert('danger', 'Harap masukkan 6 digit angka kode OTP.');
                return;
            }

            const email = document.getElementById('resetEmailInput').value;
            const btn = document.getElementById('btnForgotStep2Submit');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memverifikasi...';
            btn.disabled = true;

            try {
                const res = await fetch("{{ route('password.otp.verify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: email, otp: otp })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    document.getElementById('resetTokenInput').value = data.token;
                    document.getElementById('resetEmailInput').value = data.email;
                    showForgotAlert('success', data.message || 'Kode OTP berhasil diverifikasi!');
                    goToForgotStep(3);
                } else {
                    const msg = (data.errors && data.errors.otp) ? data.errors.otp[0] : (data.message || 'Kode OTP salah atau kedaluwarsa.');
                    showForgotAlert('danger', msg);
                    otpBoxes.forEach(b => b.value = '');
                    otpBoxes[0]?.focus();
                }
            } catch (err) {
                showForgotAlert('danger', 'Terjadi kesalahan sistem saat verifikasi. Silakan coba lagi.');
            } finally {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }
        }

        // Resend OTP Code
        async function resendOtpCode() {
            const email = document.getElementById('resetEmailInput').value;
            const btn = document.getElementById('btnResendOtp');
            btn.disabled = true;

            try {
                const res = await fetch("{{ route('password.otp.resend') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showForgotAlert('success', data.message || 'Kode OTP baru telah dikirimkan ke email Anda.');
                    startResendTimer();
                } else {
                    const msg = (data.errors && data.errors.otp) ? data.errors.otp[0] : (data.message || 'Gagal mengirim ulang OTP.');
                    showForgotAlert('danger', msg);
                    btn.disabled = false;
                }
            } catch (e) {
                showForgotAlert('danger', 'Gagal menghubungi server.');
                btn.disabled = false;
            }
        }

        function startResendTimer() {
            clearInterval(resendTimerInterval);
            resendCountdown = 60;
            const btn = document.getElementById('btnResendOtp');
            const txt = document.getElementById('resendBtnText');
            btn.disabled = true;

            resendTimerInterval = setInterval(() => {
                resendCountdown--;
                if (resendCountdown <= 0) {
                    clearInterval(resendTimerInterval);
                    btn.disabled = false;
                    txt.innerText = 'Kirim Ulang Kode OTP';
                } else {
                    txt.innerText = `Kirim Ulang (${resendCountdown}s)`;
                }
            }, 1000);
        }

        // TAHAP 3: Submit New Password
        async function submitForgotStep3() {
            hideForgotAlert();
            const token = document.getElementById('resetTokenInput').value;
            const email = document.getElementById('resetEmailInput').value;
            const password = document.getElementById('forgotNewPassword').value;
            const password_confirmation = document.getElementById('forgotNewPasswordConfirm').value;

            if (!password || password.length < 8) {
                showForgotAlert('danger', 'Kata sandi baru minimal 8 karakter.');
                return;
            }

            if (password !== password_confirmation) {
                showForgotAlert('danger', 'Konfirmasi kata sandi tidak cocok.');
                return;
            }

            const btn = document.getElementById('btnForgotStep3Submit');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan Sandi...';
            btn.disabled = true;

            try {
                const res = await fetch("{{ route('password.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        token: token,
                        email: email,
                        password: password,
                        password_confirmation: password_confirmation
                    })
                });

                const data = await res.json();

                if (res.ok) {
                    showForgotAlert('success', 'Kata sandi berhasil diubah! Mengalihkan ke halaman masuk...');
                    setTimeout(() => {
                        const loginEmailInput = document.getElementById('loginEmail');
                        if (loginEmailInput) loginEmailInput.value = email;
                        switchMode('login');
                    }, 1400);
                } else {
                    const msg = (data.errors && data.errors.email) ? data.errors.email[0] : (data.message || 'Gagal mengubah kata sandi.');
                    showForgotAlert('danger', msg);
                }
            } catch (err) {
                showForgotAlert('danger', 'Terjadi kesalahan sistem saat menyimpan sandi.');
            } finally {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }
        }

        // 6 Digit OTP Box Autonavigation
        const otpDigitInputs = document.querySelectorAll('.otp-box-digit');
        otpDigitInputs.forEach((input, index) => {
            input.addEventListener('input', function (e) {
                this.value = this.value.replace(/\D/g, '');
                if (this.value.length === 1 && index < otpDigitInputs.length - 1) {
                    otpDigitInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpDigitInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                pasteData.split('').forEach((char, i) => {
                    if (otpDigitInputs[i]) otpDigitInputs[i].value = char;
                });
                const nextIdx = Math.min(pasteData.length, otpDigitInputs.length - 1);
                otpDigitInputs[nextIdx]?.focus();
            });
        });

        // NIK & No. KK 16-Digit Filter & Live Counter
        const nikInput = document.getElementById('registerNik');
        const kkInput = document.getElementById('registerNoKk');
        const nikCounter = document.getElementById('nikCounter');
        const kkCounter = document.getElementById('kkCounter');

        function setup16DigitInput(input, counter) {
            if (!input) return;
            const updateCount = () => {
                const len = input.value.length;
                if (counter) counter.innerText = `${len}/16`;
            };
            input.addEventListener('input', function () {
                const clean = this.value.replace(/\D/g, '').slice(0, 16);
                if (this.value !== clean) this.value = clean;
                updateCount();
            });
            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || '';
                const clean = pasted.replace(/\D/g, '').slice(0, 16);
                this.value = clean;
                updateCount();
            });
            updateCount();
        }
        setup16DigitInput(nikInput, nikCounter);
        setup16DigitInput(kkInput, kkCounter);

        // Password Visibility Toggles
        document.querySelectorAll('.password-toggle-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.dataset.target;
                const input = document.getElementById(targetId);
                if (!input) return;
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                const icon = this.querySelector('i');
                if (icon) {
                    icon.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
                }
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
            if (metaTheme) metaTheme.setAttribute('content', theme === 'dark' ? '#111b21' : '#00A884');
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

        // Form Submit Loading Feedback
        document.getElementById('loginFormElem')?.addEventListener('submit', function () {
            const btn = document.getElementById('btnLoginSubmit');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...';
                setTimeout(() => { btn.disabled = true; }, 15);
            }
        });

        document.getElementById('registerFormElem')?.addEventListener('submit', function () {
            const btn = document.getElementById('btnRegisterSubmit');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mendaftarkan...';
                setTimeout(() => { btn.disabled = true; }, 15);
            }
        });

        // Initialize view based on hash or Laravel validation error
        document.addEventListener('DOMContentLoaded', () => {
            const hasRegErrors = @json($errors->has('name') || $errors->has('nik') || $errors->has('no_kk') || $errors->has('rt_id') || $errors->has('no_hp') || $errors->has('jenis_kelamin') || old('name'));
            
            if (hasRegErrors || window.location.hash === '#register') {
                switchMode('register', false);
            } else if (window.location.hash === '#forgot') {
                switchMode('forgot', false);
            } else {
                switchMode('login', false);
            }
        });
    </script>

    <!-- SMART RW Motion Graphics Engine -->
    <script src="{{ asset('js/motion.js') }}"></script>
</body>
</html>
