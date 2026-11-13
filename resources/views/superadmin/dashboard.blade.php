@extends('layouts.app')

@section('title', 'Super Admin Command Center - RW SMART')

@section('content')
<div class="content-container">
    {{-- ==================== WELCOME & DEVELOPER HERO BANNER ==================== --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden" 
         style="background: linear-gradient(135deg, #0e7a4b 0%, #159b5f 55%, #084c2e 100%); color: #ffffff;">
        <div class="card-body p-4 p-lg-5 position-relative">
            <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-lg-block me-4 mb-2 pointer-events-none">
                <i class="fas fa-shield-halved" style="font-size: 13rem; color: #fff;"></i>
            </div>
            <div class="row align-items-center position-relative">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-white text-success px-3 py-1.5 rounded-pill fw-bold shadow-xs">
                            <i class="fas fa-crown text-warning me-1"></i> SUPER ADMIN & SYSTEM DEVELOPER
                        </span>
                        <span class="badge bg-black bg-opacity-25 text-white px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="fas fa-code-branch me-1"></i> RW SMART v2.5
                        </span>
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-fingerprint me-1"></i> FULL ACCESS ACTIVE
                        </span>
                    </div>
                    <h1 class="h2 fw-extrabold text-white mb-2">
                        Selamat Datang, {{ $user->name }}
                    </h1>
                    <p class="text-white-50 mb-4 fs-6" style="max-width: 650px;">
                        Pusat kendali tertinggi sistem RW SMART. Pantau ekosistem kependudukan, keuangan, pelayanan masyarakat, dan simulasikan peran pengurus RT/RW maupun warga secara dinamis kapan saja tanpa logout.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                            <i class="fas fa-arrows-rotate me-1.5"></i> Beralih Peran (Ganti Role)
                        </button>
                        <a href="{{ route('superadmin.audit-logs') }}" class="btn btn-outline-light px-3 py-2 rounded-3 fw-semibold">
                            <i class="fas fa-shield-halved me-1.5"></i> Log Audit Sistem
                        </a>
                        <a href="{{ route('rts.index') }}" class="btn btn-outline-light px-3 py-2 rounded-3 fw-semibold">
                            <i class="fas fa-sitemap me-1.5"></i> Manajemen RT
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-flex justify-content-end">
                    <div class="bg-white bg-opacity-10 border border-white border-opacity-20 rounded-4 p-3 text-center" style="backdrop-filter: blur(8px); min-width: 220px;">
                        <div class="d-inline-flex p-3 rounded-circle bg-warning text-dark mb-2 shadow-sm">
                            <i class="fas fa-user-astronaut fa-2x"></i>
                        </div>
                        <h6 class="text-white fw-bold mb-0">Titis Rismawati</h6>
                        <small class="text-white-50 d-block">Lead System Architect</small>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-15 small text-white-50">
                            Status Akun: <span class="badge bg-success text-white">Super Admin Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== SYSTEM KEY STATS (8 METRICS) ==================== --}}
    <div class="row g-3 mb-4">
        {{-- Metric 1: RW --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Total RW</span>
                    <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="fas fa-building fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statRw) }}</h3>
                <span class="text-muted small">Wilayah RW Terdaftar</span>
            </div>
        </div>

        {{-- Metric 2: RT --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Total RT</span>
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-sitemap fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statRt) }}</h3>
                <span class="text-muted small">Unit RT Dinamis Aktif</span>
            </div>
        </div>

        {{-- Metric 3: Warga --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Total Warga Aktif</span>
                    <div class="p-2 rounded-3 bg-info bg-opacity-10 text-info">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statWarga) }}</h3>
                <span class="text-muted small">Jiwa Kependudukan</span>
            </div>
        </div>

        {{-- Metric 4: Kartu Keluarga --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Kartu Keluarga (KK)</span>
                    <div class="p-2 rounded-3 bg-warning bg-opacity-15 text-warning">
                        <i class="fas fa-id-card fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statKk) }}</h3>
                <span class="text-muted small">Kepala & Berkas Keluarga</span>
            </div>
        </div>

        {{-- Metric 5: Akun Pengguna --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Akun Terdaftar</span>
                    <div class="p-2 rounded-3 bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-user-lock fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statPengguna) }}</h3>
                <span class="text-muted small">Pengguna Login Sistem</span>
            </div>
        </div>

        {{-- Metric 6: Pengurus --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Pengurus Wilayah</span>
                    <div class="p-2 rounded-3 bg-success bg-opacity-15 text-success">
                        <i class="fas fa-user-tie fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statPengurus) }}</h3>
                <span class="text-muted small">RW, RT & Bendahara</span>
            </div>
        </div>

        {{-- Metric 7: Aspirasi --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Aspirasi & Laporan</span>
                    <div class="p-2 rounded-3 bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-comment-dots fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statAspirasi) }}</h3>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill small">{{ $statAspirasiPending }} Menunggu</span>
            </div>
        </div>

        {{-- Metric 8: Kegiatan --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold">Kegiatan & Agenda</span>
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-calendar-check fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ number_format($statKegiatan) }}</h3>
                <span class="text-muted small">Agenda RW & RT</span>
            </div>
        </div>
    </div>

    {{-- ==================== ROLE SWITCHER & AUDIT TRAIL ROW ==================== --}}
    <div class="row g-4 mb-4">
        {{-- Card 1: Quick Role Switcher --}}
        <div class="col-12 col-xl-5">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-3 bg-warning bg-opacity-20 text-warning">
                            <i class="fas fa-arrows-rotate"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Pusat Beralih Peran (Role Switcher)</h6>
                            <small class="text-secondary">Simulasi instan tanpa logout</small>
                        </div>
                    </div>
                    <span class="badge bg-success rounded-pill px-2.5 py-1">Super Admin Mode</span>
                </div>
                <div class="card-body p-4">
                    <p class="small text-secondary mb-3">
                        Pilih peran yang ingin Anda simulasikan. Seluruh tampilan dashboard, hak akses, data wilayah, permohonan surat, dan keuangan akan secara otomatis menyesuaikan:
                    </p>

                    <div class="list-group list-group-flush gap-2">
                        {{-- 1. Super Admin Asli --}}
                        <form method="POST" action="{{ route('superadmin.switch-role') }}">
                            @csrf
                            <input type="hidden" name="mode" value="super_admin">
                            <button type="submit" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between hover-lift">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2.5 rounded-circle bg-warning bg-opacity-15 text-warning">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <div class="text-start">
                                        <strong class="text-dark d-block">Super Admin (Default)</strong>
                                        <small class="text-muted">Akses seluruh wilayah & konfigurasi master</small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">Pilih &rarr;</span>
                            </button>
                        </form>

                        {{-- 2. Ketua RW --}}
                        <form method="POST" action="{{ route('superadmin.switch-role') }}">
                            @csrf
                            <input type="hidden" name="mode" value="rw">
                            @if($allRws->count() > 0)
                                <input type="hidden" name="rw_id" value="{{ $allRws->first()->id }}">
                            @endif
                            <button type="submit" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between hover-lift">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2.5 rounded-circle bg-success bg-opacity-15 text-success">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="text-start">
                                        <strong class="text-dark d-block">Ketua / Pengurus RW</strong>
                                        <small class="text-muted">Koordinasi antar-RT, persetujuan & audit keuangan</small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">Pilih &rarr;</span>
                            </button>
                        </form>

                        {{-- 3. Ketua RT (Dropdown Selector) --}}
                        <div class="border rounded-3 p-3 bg-light">
                            <form method="POST" action="{{ route('superadmin.switch-role') }}">
                                @csrf
                                <input type="hidden" name="mode" value="rt">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="p-2.5 rounded-circle bg-primary bg-opacity-15 text-primary">
                                        <i class="fas fa-landmark"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block">Ketua RT (Pilih Unit RT Dinamis)</strong>
                                        <small class="text-muted">Pilih RT yang ingin dikelola:</small>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <select name="rt_id" class="form-select form-select-sm" required>
                                        @foreach($allRts as $rtItem)
                                            <option value="{{ $rtItem->id }}">
                                                RT {{ $rtItem->kode_rt }} - {{ $rtItem->nama_rt }} (RW {{ optional($rtItem->rw)->kode_rw ?? '01' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary btn-sm px-3" type="submit">
                                        <i class="fas fa-play me-1"></i> Beralih
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- 4. Warga (Dropdown Selector) --}}
                        <div class="border rounded-3 p-3 bg-light">
                            <form method="POST" action="{{ route('superadmin.switch-role') }}">
                                @csrf
                                <input type="hidden" name="mode" value="warga">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="p-2.5 rounded-circle bg-info bg-opacity-15 text-info">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block">Warga (Pilih Domisili / Akun Warga)</strong>
                                        <small class="text-muted">Pilih unit domisili atau nama warga:</small>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <select name="rt_id" class="form-select form-select-sm" required>
                                        @foreach($allRts as $rtItem)
                                            <option value="{{ $rtItem->id }}">
                                                Warga di RT {{ $rtItem->kode_rt }} (RW {{ optional($rtItem->rw)->kode_rw ?? '01' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-info text-white btn-sm px-3" type="submit">
                                        <i class="fas fa-play me-1"></i> Beralih
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Recent Audit Logs & System Activity --}}
        <div class="col-12 col-xl-7">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-3 bg-success bg-opacity-15 text-success">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Log Aktivitas & Audit Trail Terbaru</h6>
                            <small class="text-secondary">Pencatatan real-time aksi pengguna sistem</small>
                        </div>
                    </div>
                    <a href="{{ route('superadmin.audit-logs') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-secondary small">
                                    <th class="ps-4">Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Unit / RT</th>
                                    <th>Aksi</th>
                                    <th>Deskripsi</th>
                                    <th class="pe-4 text-end">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                    <tr>
                                        <td class="ps-4 text-nowrap small text-muted">
                                            {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                                        </td>
                                        <td class="small fw-semibold text-dark">
                                            {{ optional($log->user)->name ?? 'Sistem' }}
                                        </td>
                                        <td class="small">
                                            @if($log->rt)
                                                <span class="badge bg-light text-dark border">RT {{ $log->rt->kode_rt }}</span>
                                            @else
                                                <span class="badge bg-light text-muted border">RW / Master</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $actionClass = 'bg-secondary bg-opacity-10 text-secondary';
                                                if (str_contains($log->action, 'create') || str_contains($log->action, 'store') || str_contains($log->action, 'approve')) {
                                                    $actionClass = 'bg-success bg-opacity-15 text-success';
                                                } elseif (str_contains($log->action, 'switch_role')) {
                                                    $actionClass = 'bg-warning bg-opacity-20 text-dark';
                                                } elseif (str_contains($log->action, 'delete') || str_contains($log->action, 'reject')) {
                                                    $actionClass = 'bg-danger bg-opacity-15 text-danger';
                                                }
                                            @endphp
                                            <span class="badge {{ $actionClass }} rounded-pill small">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td class="small text-secondary" style="max-width: 250px;">
                                            <div class="text-truncate" title="{{ $log->deskripsi }}">
                                                {{ $log->deskripsi ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end small text-muted font-monospace">
                                            {{ $log->ip_address ?? '127.0.0.1' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                            Belum ada log aktivitas tercatat
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== QUICK SYSTEM MANAGEMENT SHORTCUTS ==================== --}}
    <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
        <h6 class="fw-bold text-dark mb-3">
            <i class="fas fa-toolbox text-success me-2"></i>Pintasan Cepat Manajemen Sistem
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('rts.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="fas fa-sitemap fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark small">Manajemen RT</strong>
                        <small class="text-muted">Kelola unit & ketua RT</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark small">Data Kependudukan</strong>
                        <small class="text-muted">Data warga seluruh RW</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('rw.audit.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-warning bg-opacity-15 text-warning">
                        <i class="fas fa-wallet fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark small">Audit Keuangan</strong>
                        <small class="text-muted">Pantau kas & iuran RT/RW</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-info bg-opacity-10 text-info">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark small">Profil Pengembang</strong>
                        <small class="text-muted">Pengaturan akun Anda</small>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
