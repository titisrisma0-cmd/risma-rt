<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan | RW SMART</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg: #f7faf8;
            --surface: #ffffff;
            --border: #dbe9e0;
            --ink: #0f2a1d;
            --ink-soft: #4b6355;
            --primary: #0e7a4b;
            --primary-dark: #0a5c38;
            --primary-soft: #e3f4ea;
            --accent: #f4a62a;
            --radius-btn: 12px;
            --radius-card: 22px;
            --shadow-soft: 0 16px 36px rgba(15, 42, 29, 0.10);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            line-height: 1.6;
        }

        .error-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            padding: 48px 36px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow-soft);
            animation: rise 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: none; }
        }

        .error-badge {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            background: #fdf0d8;
            padding: 4px 14px;
            border-radius: 9999px;
            margin-bottom: 18px;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            letter-spacing: -0.04em;
            margin-bottom: 12px;
        }

        .error-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -0.02em;
            margin-bottom: 10px;
        }

        .error-desc {
            font-size: 0.95rem;
            color: var(--ink-soft);
            margin-bottom: 28px;
            line-height: 1.65;
        }

        .btn-home {
            background: var(--primary);
            color: #ffffff;
            border: 1.5px solid var(--primary);
            border-radius: var(--radius-btn);
            padding: 12px 24px;
            min-height: 44px;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(14, 122, 75, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .btn-home:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 122, 75, 0.38);
        }

        .btn-home:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <span class="error-badge">Status HTTP</span>
        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-desc">
            Mohon maaf, halaman yang Anda cari tidak tersedia atau tautan telah dipindahkan.
        </p>
        <a href="{{ Auth::check() ? route('dashboard') : url('/') }}" class="btn-home">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            <span>Kembali ke {{ Auth::check() ? 'Dashboard' : 'Beranda' }}</span>
        </a>
    </div>
</body>
</html>
