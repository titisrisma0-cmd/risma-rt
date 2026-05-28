<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RISMA-RT - Sistem Iuran RT</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('risma-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #1a4d3e 0%, #0d2818 40%, #051412 100%);
            color: #fffbf0;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0;
            z-index: 0;
            background-image:
                linear-gradient(45deg, rgba(138, 116, 40, 0.05) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(45, 97, 63, 0.045) 25%, transparent 25%);
            background-size: 56px 56px;
            background-position: 0 0, 28px 28px;
            transition: opacity 0.25s ease;
        }
        
        /* Ornamental Decorations */
        .decoration {
            position: fixed;
            pointer-events: none;
            z-index: 0;
            opacity: 0.8;
        }
        
        .lantern-top-right {
            top: 30px;
            right: 40px;
            font-size: 5rem;
            color: #ffd700;
            animation: float 4s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.4));
        }
        
        .ketupat-bottom-right {
            bottom: 60px;
            right: 30px;
            font-size: 4rem;
            color: #ffd700;
            animation: float 5s ease-in-out infinite 1s;
            filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.3));
        }
        
        .stars {
            font-size: 1.2rem;
            color: #ffd700;
            animation: twinkle 2s ease-in-out infinite;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.3));
        }
        
        .star-1 { top: 80px; right: 150px; position: fixed; }
        .star-2 { top: 200px; right: 200px; position: fixed; }
        .star-3 { bottom: 100px; right: 150px; position: fixed; }

        .eid-light-decor {
            display: none;
            position: fixed;
            pointer-events: none;
            z-index: 0;
            color: rgba(138, 116, 40, 0.16);
            filter: none;
        }

        .eid-decor-moon {
            top: 118px;
            right: 7vw;
            font-size: 4.4rem;
            transform: rotate(-10deg);
        }

        .eid-decor-mosque {
            bottom: 48px;
            left: 320px;
            font-size: 5rem;
            color: rgba(45, 97, 63, 0.12);
        }

        .eid-decor-ketupat {
            bottom: 92px;
            right: 38px;
            font-size: 4rem;
            color: rgba(138, 116, 40, 0.14);
            transform: rotate(9deg);
        }

        .eid-decor-star {
            top: 46%;
            right: 26px;
            font-size: 2rem;
            color: rgba(138, 116, 40, 0.12);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        /* Navbar Modern */
        .navbar {
            background: linear-gradient(135deg, rgba(26, 95, 63, 0.95), rgba(45, 134, 89, 0.85));
            backdrop-filter: blur(12px);
            padding: 0.8rem 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.15);
            border-bottom: 2px solid rgba(255, 215, 0, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }

        .navbar.scrolled {
            background: linear-gradient(135deg, rgba(26, 95, 63, 0.98), rgba(45, 134, 89, 0.9));
            box-shadow: 0 6px 25px rgba(255, 215, 0, 0.2);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffd700;
            text-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }

        .navbar-brand i {
            color: #ffd700;
            margin-right: 8px;
        }

        .nav-link {
            color: #fffbf0 !important;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: #ffd700 !important;
        }

        .dropdown-menu {
            background: rgba(26, 95, 63, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 12px;
        }

        .dropdown-item {
            color: #fffbf0;
            transition: all 0.3s;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #1a5f3f;
            font-weight: 600;
        }

        /* Sidebar Modern */
        .sidebar {
            position: fixed;
            left: 0;
            top: 70px;
            width: 280px;
            height: calc(100vh - 70px);
            background: linear-gradient(180deg, rgba(26, 95, 63, 0.9), rgba(13, 40, 24, 0.85));
            backdrop-filter: blur(12px);
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.4);
            transition: all 0.3s;
            border-right: 2px solid rgba(255, 215, 0, 0.2);
            overflow-y: auto;
            z-index: 1020;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(13, 40, 24, 0.3);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            border-radius: 3px;
        }

        .sidebar .nav-link {
            color: #fffbf0;
            padding: 12px 18px;
            margin: 5px 10px;
            border-radius: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .sidebar .nav-link:hover {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #1a5f3f;
            transform: translateX(6px);
            box-shadow: 0 6px 18px rgba(255, 215, 0, 0.2);
            font-weight: 600;
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #1a5f3f;
            box-shadow: 0 6px 18px rgba(255, 215, 0, 0.25);
            font-weight: 700;
        }
        
        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            color: #ffd700;
        }

        .sidebar .sidebar-brand {
            padding: 16px 20px 18px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.15);
            margin-bottom: 20px;
        }

        .sidebar .sidebar-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffd700;
            margin-bottom: 4px;
        }

        .sidebar .sidebar-subtitle {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 0;
        }

        .sidebar-section {
            margin-bottom: 1.5rem;
        }

        .sidebar-section-title {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            color: rgba(255, 215, 0, 0.7);
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-left: 20px;
        }

        .sidebar-logo {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: rgba(255, 215, 0, 0.12);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .sidebar-logo i {
            color: #ffd700;
            filter: drop-shadow(0 0 12px rgba(255, 215, 0, 0.35));
        }

        /* Main Content */
        .main-content {
            padding: 20px;
            animation: fadeInUp 0.6s;
            margin-left: 280px;
            margin-top: 70px;
        }

        /* Glass Card */
        .card {
            background: rgba(26, 95, 63, 0.3);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 2px solid rgba(255, 215, 0, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 215, 0, 0.4);
            background: rgba(26, 95, 63, 0.4);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(26, 95, 63, 0.5), rgba(45, 134, 89, 0.3));
            border-bottom: 1px solid rgba(255, 215, 0, 0.15);
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 20px 20px 0 0 !important;
            color: #ffd700;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            border: none;
            color: #1a5f3f;
            transition: all 0.3s;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
            color: #1a5f3f;
        }

        /* Tables */
        .table {
            color: #f1f5f9;
            margin-bottom: 0;
        }

        .table thead th {
            background: rgba(26, 95, 63, 0.6);
            color: #ffd700;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
            font-weight: 600;
        }

        .table td {
            border-bottom: 1px solid rgba(255, 215, 0, 0.05);
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: rgba(255, 215, 0, 0.05);
        }

        /* Alert */
        .alert {
            border-radius: 12px;
            animation: fadeInUp 0.5s;
            background: rgba(26, 95, 63, 0.8);
            backdrop-filter: blur(10px);
            border: none;
            color: #f1f5f9;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            border-left: 4px solid #22c55e;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            border-left: 4px solid #ef4444;
        }

        /* Footer */
        footer {
            background: rgba(26, 95, 63, 0.5);
            backdrop-filter: blur(10px);
            color: #94a3b8;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            border-radius: 12px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            border-radius: 10px;
        }

        /* Toggle Sidebar Button */
        .toggle-sidebar {
            background: transparent;
            border: none;
            color: #ffd700;
            font-size: 1.5rem;
            margin-right: 15px;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                left: -280px;
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .lantern-top-right, .ketupat-bottom-right, .stars {
                display: none;
            }
        }

        /* Form Controls */
        .form-control, .form-select {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            color: #f1f5f9;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: #ffd700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
            color: #f1f5f9;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.85rem;
            color: #ffd700;
            margin-bottom: 0.5rem;
        }

        /* Modal */
        .modal-content {
            background: rgba(26, 95, 63, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 20px;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 215, 0, 0.2);
        }

        .btn-close {
            filter: invert(1);
        }
        .topbar-profile-photo {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 1px rgba(255, 215, 0, 0.18);
        }

        .theme-toggle {
            width: 42px;
            height: 42px;
            border: 1px solid rgba(255, 215, 0, 0.28);
            border-radius: 14px;
            background: rgba(255, 251, 240, 0.08);
            color: #ffd700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
        }

        .theme-toggle:hover {
            background: rgba(255, 215, 0, 0.16);
            color: #ffed4e;
            transform: translateY(-1px);
        }

        html[data-theme="light"] body {
            background:
                radial-gradient(ellipse at 88% 10%, rgba(221, 204, 126, 0.2) 0%, transparent 36%),
                radial-gradient(ellipse at 12% 92%, rgba(98, 133, 91, 0.18) 0%, transparent 34%),
                linear-gradient(135deg, #edf1e8 0%, #f3f5ed 44%, #e4eadf 100%);
            color: #1f2933;
        }

        html[data-theme="light"] body::before {
            opacity: 1;
        }

        html[data-theme="light"] .decoration {
            opacity: 0.12;
        }

        html[data-theme="light"] .eid-light-decor {
            display: block;
        }

        html[data-theme="light"] .navbar {
            background: rgba(246, 248, 240, 0.92);
            border-bottom: 1px solid rgba(123, 107, 52, 0.18);
            box-shadow: 0 8px 26px rgba(57, 73, 56, 0.08);
        }

        html[data-theme="light"] .navbar.scrolled {
            background: rgba(243, 246, 237, 0.96);
            box-shadow: 0 10px 30px rgba(57, 73, 56, 0.12);
        }

        html[data-theme="light"] .navbar-brand,
        html[data-theme="light"] .navbar-brand i,
        html[data-theme="light"] .toggle-sidebar,
        html[data-theme="light"] .theme-toggle {
            color: #8a7428;
            text-shadow: none;
        }

        html[data-theme="light"] .nav-link {
            color: #2f3a31 !important;
        }

        html[data-theme="light"] .nav-link:hover {
            color: #75611f !important;
        }

        html[data-theme="light"] .theme-toggle {
            background: rgba(134, 119, 60, 0.08);
            border-color: rgba(134, 119, 60, 0.2);
        }

        html[data-theme="light"] .sidebar {
            background: rgba(246, 248, 240, 0.9);
            border-right: 1px solid rgba(123, 107, 52, 0.16);
            box-shadow: 12px 0 30px rgba(57, 73, 56, 0.08);
        }

        html[data-theme="light"] .sidebar .sidebar-brand {
            border-bottom-color: rgba(123, 107, 52, 0.14);
        }

        html[data-theme="light"] .sidebar .sidebar-title,
        html[data-theme="light"] .sidebar-logo i {
            color: #8a7428;
        }

        html[data-theme="light"] .sidebar .sidebar-subtitle,
        html[data-theme="light"] .sidebar-section-title {
            color: #6f7769;
        }

        html[data-theme="light"] .sidebar-logo {
            background: rgba(138, 116, 40, 0.12);
        }

        html[data-theme="light"] .sidebar .nav-link {
            color: #324034 !important;
        }

        html[data-theme="light"] .sidebar .nav-link i {
            color: #67715f;
        }

        html[data-theme="light"] .sidebar .nav-link:hover,
        html[data-theme="light"] .sidebar .nav-link.active {
            background: #e7ecdf;
            color: #243126 !important;
            box-shadow: inset 3px 0 0 #8a7428, 0 8px 20px rgba(57, 73, 56, 0.08);
        }

        html[data-theme="light"] .sidebar .nav-link:hover i,
        html[data-theme="light"] .sidebar .nav-link.active i {
            color: #8a7428;
        }

        html[data-theme="light"] .card,
        html[data-theme="light"] .glass-card,
        html[data-theme="light"] .stat-card,
        html[data-theme="light"] .welcome-header {
            background: rgba(248, 250, 244, 0.84) !important;
            border-color: rgba(125, 138, 113, 0.22) !important;
            box-shadow: 0 10px 28px rgba(57, 73, 56, 0.08) !important;
        }

        html[data-theme="light"] .card:hover,
        html[data-theme="light"] .stat-card:hover {
            background: rgba(251, 252, 248, 0.92) !important;
            box-shadow: 0 16px 34px rgba(57, 73, 56, 0.12) !important;
        }

        html[data-theme="light"] .card-header,
        html[data-theme="light"] .card-header-custom {
            background: rgba(232, 238, 224, 0.78) !important;
            border-bottom-color: rgba(125, 138, 113, 0.2) !important;
            color: #2f3a31 !important;
        }

        html[data-theme="light"] .dropdown-menu,
        html[data-theme="light"] .modal-content {
            background: rgba(248, 250, 244, 0.98);
            border-color: rgba(125, 138, 113, 0.24);
            box-shadow: 0 18px 40px rgba(57, 73, 56, 0.16);
        }

        html[data-theme="light"] .dropdown-item {
            color: #2f3a31;
        }

        html[data-theme="light"] .dropdown-item:hover {
            background: #e7ecdf;
            color: #243126;
        }

        html[data-theme="light"] .text-white,
        html[data-theme="light"] .table-dark,
        html[data-theme="light"] .table-dark td,
        html[data-theme="light"] .welcome-header h1 {
            color: #1f2933 !important;
        }

        html[data-theme="light"] .text-white-50,
        html[data-theme="light"] .text-secondary,
        html[data-theme="light"] .text-muted,
        html[data-theme="light"] .small.text-secondary {
            color: #687466 !important;
        }

        html[data-theme="light"] .gradient-text {
            color: #7d6925 !important;
            text-shadow: none !important;
        }

        html[data-theme="light"] .table thead th,
        html[data-theme="light"] .table-dark th {
            background: rgba(226, 233, 218, 0.82) !important;
            color: #384336 !important;
            border-bottom-color: rgba(125, 138, 113, 0.24) !important;
        }

        html[data-theme="light"] .table,
        html[data-theme="light"] .table-dark {
            --bs-table-color: #1f2933;
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(125, 138, 113, 0.16);
            --bs-table-striped-bg: rgba(226, 233, 218, 0.36);
            --bs-table-striped-color: #1f2933;
            --bs-table-hover-bg: rgba(226, 233, 218, 0.58);
            --bs-table-hover-color: #1f2933;
            background-color: transparent !important;
            color: #1f2933 !important;
        }

        html[data-theme="light"] .table tbody,
        html[data-theme="light"] .table tr,
        html[data-theme="light"] .table td,
        html[data-theme="light"] .table-dark tbody,
        html[data-theme="light"] .table-dark tr,
        html[data-theme="light"] .table-dark td {
            background-color: transparent !important;
            color: #1f2933 !important;
        }

        html[data-theme="light"] .table td,
        html[data-theme="light"] .table-dark td {
            border-bottom-color: rgba(125, 138, 113, 0.16) !important;
        }

        html[data-theme="light"] .table-hover tbody tr:hover,
        html[data-theme="light"] .table-dark tbody tr:hover {
            background: rgba(226, 233, 218, 0.55) !important;
        }

        html[data-theme="light"] .form-control,
        html[data-theme="light"] .form-select,
        html[data-theme="light"] .input-group-text,
        html[data-theme="light"] .bg-dark {
            background-color: #edf2e8 !important;
            border-color: rgba(125, 138, 113, 0.28) !important;
            color: #1f2933 !important;
        }

        html[data-theme="light"] .form-control:focus,
        html[data-theme="light"] .form-select:focus {
            background-color: #f7f9f2 !important;
            border-color: #8a7428 !important;
            box-shadow: 0 0 0 3px rgba(138, 116, 40, 0.14) !important;
            color: #1f2933 !important;
        }

        html[data-theme="light"] .form-label {
            color: #485443 !important;
        }

        html[data-theme="light"] footer {
            background: rgba(246, 248, 240, 0.72);
            color: #687466;
            border: 1px solid rgba(125, 138, 113, 0.16);
        }

        html[data-theme="light"] .btn-close {
            filter: none;
        }
    </style>
</head>

<body>
    <!-- Ornamental Decorations -->
    <div class="decoration lantern-top-right"><i class="fas fa-lantern"></i></div>
    <div class="decoration ketupat-bottom-right"><i class="fas fa-gem"></i></div>
    <div class="decoration stars star-1"><i class="fas fa-star"></i></div>
    <div class="decoration stars star-2"><i class="fas fa-star"></i></div>
    <div class="decoration stars star-3"><i class="fas fa-star"></i></div>
    <div class="eid-light-decor eid-decor-moon"><i class="fas fa-moon"></i></div>
    <div class="eid-light-decor eid-decor-mosque"><i class="fas fa-mosque"></i></div>
    <div class="eid-light-decor eid-decor-ketupat"><i class="fas fa-gem"></i></div>
    <div class="eid-light-decor eid-decor-star"><i class="fas fa-star-and-crescent"></i></div>
    
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="toggle-sidebar" id="toggleSidebarBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i> RISMA-RT
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    @php
                        $user = Auth::user();
                        $defaultName = $user ? $user->name : 'Guest';
                        $fotoUrl = 'https://ui-avatars.com/api/?background=3b82f6&color=fff&rounded=true&size=128&name=' . urlencode($defaultName);
                        if ($user && $user->profile && $user->profile->foto_base64) {
                            $fotoUrl = $user->profile->foto_base64;
                        }
                        $unreadCount = 0;
                        $latestNotifs = collect();
                        if ($user) {
                            $unreadCount = \App\Models\Notifikasi::where('user_id', $user->id)->where('dibaca', false)->count();
                            $latestNotifs = \App\Models\Notifikasi::where('user_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        }
                    @endphp

                    <li class="nav-item">
                        <button type="button" class="theme-toggle" id="themeToggleBtn" title="Ganti mode tampilan" aria-label="Ganti mode tampilan">
                            <i class="fas fa-moon" id="themeToggleIcon"></i>
                        </button>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill position-absolute" style="top:0; right:0; transform: translate(40%, -40%); font-size: 0.65rem;">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 300px;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifikasi Terbaru</span>
                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifikasi.read-all') }}" class="m-0">
                                        @csrf
                                        <button class="btn btn-sm btn-link text-decoration-none">Tandai semua</button>
                                    </form>
                                @endif
                            </li>
                            @if($latestNotifs->count())
                                @foreach($latestNotifs as $notif)
                                    <li>
                                        <a href="{{ route('notifikasi.index') }}" class="dropdown-item {{ $notif->dibaca ? '' : 'bg-warning bg-opacity-10' }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="d-block">{{ $notif->judul }}</strong>
                                                    <span class="small text-secondary">{{ \Illuminate\Support\Str::limit($notif->pesan, 60) }}</span>
                                                </div>
                                                @if(!$notif->dibaca)
                                                    <span class="badge bg-danger">Baru</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="{{ route('notifikasi.index') }}">Lihat semua notifikasi</a></li>
                            @else
                                <li><div class="dropdown-item text-center text-secondary">Tidak ada notifikasi baru</div></li>
                            @endif
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="{{ $fotoUrl }}" alt="Foto Profil" class="topbar-profile-photo">
                            <span>{{ $defaultName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="fas fa-id-card me-2"></i> Profil Saya
                                </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-3 d-flex flex-column h-100">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center gap-3">
                    <div class="sidebar-logo">
                        <i class="fas fa-wallet fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="sidebar-title">KAS RT</h5>
                        <p class="sidebar-subtitle">Keuangan RT</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Utama</div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Transaksi</div>
                <nav class="nav flex-column">
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara') || Auth::user()->hasRole('warga')))
                        <a class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                            <i class="fas fa-hand-holding-usd"></i> Pembayaran
                        </a>
                    @endif
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}" href="{{ route('pengeluaran.index') }}">
                            <i class="fas fa-shopping-cart"></i> Pengeluaran
                        </a>
                    @endif
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('kas.*') ? 'active' : '' }}" href="{{ route('kas.index') }}">
                            <i class="fas fa-chart-line"></i> Kas RT
                        </a>
                    @endif
                </nav>
            </div>

            <!-- BAGIAN YANG DI-RECOVERY DARI ERROR -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Master Data</div>
                <nav class="nav flex-column">
                    @if(Auth::check() && Auth::user()->hasRole('admin'))
                        <a class="nav-link {{ request()->routeIs('warga.*') ? 'active' : '' }}" href="{{ route('warga.index') }}">
                            <i class="fas fa-users"></i> Data Warga
                        </a>
                    @endif
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('iuran.*') ? 'active' : '' }}" href="{{ route('iuran.index') }}">
                            <i class="fas fa-money-bill-wave"></i> Kelola Iuran
                        </a>
                    @endif
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Laporan</div>
                <nav class="nav flex-column">
                    @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara')))
                        <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                            <i class="fas fa-file-alt"></i> Laporan
                        </a>
                    @endif
                </nav>
            </div>

            <div class="sidebar-section mt-auto">
                <div class="sidebar-section-title">Akun</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="{{ route('profile.index') }}">
                        <i class="fas fa-user-circle"></i> Profil Saya
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')

        <style>
            html[data-theme="light"] .welcome-header h1,
            html[data-theme="light"] h1,
            html[data-theme="light"] h2,
            html[data-theme="light"] h3,
            html[data-theme="light"] h4,
            html[data-theme="light"] h5,
            html[data-theme="light"] h6 {
                color: #1f2933 !important;
            }

            html[data-theme="light"] .glass-card,
            html[data-theme="light"] .stat-card,
            html[data-theme="light"] .welcome-header,
            html[data-theme="light"] .info-item,
            html[data-theme="light"] .warga-card {
                background: rgba(248, 250, 244, 0.86) !important;
                border-color: rgba(125, 138, 113, 0.22) !important;
                box-shadow: 0 10px 28px rgba(57, 73, 56, 0.08) !important;
            }

            html[data-theme="light"] .card-header-custom {
                background: rgba(232, 238, 224, 0.78) !important;
                color: #2f3a31 !important;
                border-bottom-color: rgba(125, 138, 113, 0.2) !important;
            }

            html[data-theme="light"] .table-dark,
            html[data-theme="light"] .table-dark th,
            html[data-theme="light"] .table-dark td {
                color: #1f2933 !important;
            }

            html[data-theme="light"] .table,
            html[data-theme="light"] .table-dark {
                --bs-table-color: #1f2933;
                --bs-table-bg: transparent;
                --bs-table-border-color: rgba(125, 138, 113, 0.16);
                --bs-table-striped-bg: rgba(226, 233, 218, 0.36);
                --bs-table-striped-color: #1f2933;
                --bs-table-hover-bg: rgba(226, 233, 218, 0.58);
                --bs-table-hover-color: #1f2933;
                background-color: transparent !important;
                color: #1f2933 !important;
            }

            html[data-theme="light"] .table thead th,
            html[data-theme="light"] .table-dark thead th {
                background-color: rgba(226, 233, 218, 0.86) !important;
                color: #384336 !important;
                border-bottom-color: rgba(125, 138, 113, 0.24) !important;
            }

            html[data-theme="light"] .table tbody,
            html[data-theme="light"] .table tr,
            html[data-theme="light"] .table td,
            html[data-theme="light"] .table-dark tbody,
            html[data-theme="light"] .table-dark tr,
            html[data-theme="light"] .table-dark td {
                background-color: transparent !important;
                color: #1f2933 !important;
                border-color: rgba(125, 138, 113, 0.16) !important;
            }

            html[data-theme="light"] .table-hover tbody tr:hover,
            html[data-theme="light"] .table-dark.table-hover tbody tr:hover,
            html[data-theme="light"] .table-hover tbody tr:hover > * {
                background-color: rgba(226, 233, 218, 0.58) !important;
                color: #1f2933 !important;
            }

            html[data-theme="light"] .text-muted,
            html[data-theme="light"] .small.text-muted {
                color: #687466 !important;
            }

            html[data-theme="light"] .badge.bg-warning.text-dark {
                color: #3b2f08 !important;
            }

            html[data-theme="light"] .badge {
                text-shadow: none !important;
            }
        </style>
        
        <footer>
            <p>&copy; {{ date('Y') }} RISMA-RT - Sistem Informasi Iuran RT | Dibangun dengan <i class="fas fa-heart text-danger"></i> untuk warga</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init({
            duration: 800,
            once: true
        });

        $(document).ready(function () {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                responsive: true
            });

        });

        function confirmDelete(url, name) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data " + name + " akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                buttonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#f1f5f9'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.style.display = 'none';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';

                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Toggle sidebar for mobile
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');

        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('risma-theme', theme);

            if (themeToggleIcon) {
                themeToggleIcon.className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
            }

            window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
        }

        setTheme(localStorage.getItem('risma-theme') || 'dark');

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
