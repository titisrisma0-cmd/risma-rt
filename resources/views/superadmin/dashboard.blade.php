@extends('layouts.app')

@section('title', 'Super Admin Command Center - Smart RW')

@section('content')
<div class="content-container">
    {{-- ==================== WELCOME & DEVELOPER HERO BANNER ==================== --}}
    <div class="card hero-surface hero-superadmin border-0 rounded-4 shadow-sm mb-4 overflow-hidden" data-hero="1">
        <div class="card-body p-4 p-lg-5 position-relative">
            {{-- 2.3 Shield decoration strictly behind content with low opacity --}}
            <div class="hero-shield-decor d-none d-lg-block" aria-hidden="true">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div class="row align-items-center hero-content-layer position-relative">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-white text-success px-3 py-1.5 rounded-pill fw-bold shadow-xs">
                            <i class="fas fa-crown text-warning me-1"></i> SUPER ADMIN & SYSTEM DEVELOPER
                        </span>
                        <span class="badge bg-black bg-opacity-25 text-white px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="fas fa-code-branch me-1"></i> Smart RW 01 v2.5
                        </span>
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-fingerprint me-1"></i> FULL ACCESS ACTIVE
                        </span>
                    </div>
                    <h1 class="h2 fw-extrabold text-white mb-2">
                        Selamat Datang, {{ $user->name }}
                    </h1>
                    <p class="text-white-50 mb-4 fs-6" style="max-width: 650px;">
                        Pusat kendali tertinggi sistem Smart RW. Pantau ekosistem kependudukan, keuangan, pelayanan masyarakat, dan simulasikan peran pengurus RT/RW maupun warga secara dinamis kapan saja tanpa logout.
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
                    <div class="hero-profile-card p-3 text-center" style="min-width: 220px;">
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
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Total RW</span>
                    <div class="p-2 rounded-3 icon-box-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-building fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statRw) }}</h3>
                <span class="small" style="color: var(--ink-mute);">Wilayah RW Terdaftar</span>
            </div>
        </div>

        {{-- Metric 2: RT --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Total RT</span>
                    <div class="p-2 rounded-3 icon-box-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-sitemap fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statRt) }}</h3>
                <span class="small" style="color: var(--ink-mute);">Unit RT Dinamis Aktif</span>
            </div>
        </div>

        {{-- Metric 3: Warga --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Total Warga Aktif</span>
                    <div class="p-2 rounded-3 icon-box-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statWarga) }}</h3>
                <span class="small" style="color: var(--ink-mute);">Jiwa Kependudukan</span>
            </div>
        </div>

        {{-- Metric 4: Kartu Keluarga --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Kartu Keluarga (KK)</span>
                    <div class="p-2 rounded-3 icon-box-accent d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-id-card fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statKk) }}</h3>
                <span class="small" style="color: var(--ink-mute);">Kepala & Berkas Keluarga</span>
            </div>
        </div>

        {{-- Metric 5: Akun Pengguna --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Akun Terdaftar</span>
                    <div class="p-2 rounded-3 icon-box-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user-lock fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statPengguna) }}</h3>
                <span class="small" style="color: var(--ink-mute);">Pengguna Login Sistem</span>
            </div>
        </div>

        {{-- Metric 6: Pengurus --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Pengurus Wilayah</span>
                    <div class="p-2 rounded-3 icon-box-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user-tie fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statPengurus) }}</h3>
                <span class="small" style="color: var(--ink-mute);">RW, RT & Pengurus</span>
            </div>
        </div>

        {{-- Metric 7: Aspirasi --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Aspirasi & Laporan</span>
                    <div class="p-2 rounded-3 icon-box-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-comment-dots fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statAspirasi) }}</h3>
                <span class="badge rounded-pill small" style="background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(229,72,58,0.25);">{{ $statAspirasiPending }} Menunggu</span>
            </div>
        </div>

        {{-- Metric 8: Kegiatan --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-xs h-100 p-3 hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold" style="color: var(--ink-soft);">Kegiatan & Agenda</span>
                    <div class="p-2 rounded-3 icon-box-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-calendar-check fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: var(--ink);">{{ number_format($statKegiatan) }}</h3>
                <span class="small" style="color: var(--ink-mute);">Agenda RW & RT</span>
            </div>
        </div>
    </div>

    {{-- ==================== ROLE SWITCHER & AUDIT TRAIL ROW ==================== --}}
    <div class="row g-4 mb-4">
        {{-- Card 1: Quick Role Switcher --}}
        <div class="col-12 col-xl-5">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-3 icon-box-accent d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-arrows-rotate"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--ink);">Pusat Beralih Peran (Role Switcher)</h6>
                            <small style="color: var(--ink-soft);">Simulasi instan tanpa logout</small>
                        </div>
                    </div>
                    <span class="badge bg-success rounded-pill px-2.5 py-1">Super Admin Mode</span>
                </div>
                <div class="card-body p-4">
                    <p class="small mb-3" style="color: var(--ink-soft);">
                        Pilih peran yang ingin Anda simulasikan. Seluruh tampilan dashboard, hak akses, data wilayah, permohonan surat, dan keuangan akan secara otomatis menyesuaikan:
                    </p>

                    <div class="list-group list-group-flush gap-2">
                        {{-- 1. Super Admin Asli --}}
                        <form method="POST" action="{{ route('superadmin.switch-role') }}">
                            @csrf
                            <input type="hidden" name="mode" value="super_admin">
                            <button type="submit" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between hover-lift">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2.5 rounded-circle icon-box-accent d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <div class="text-start">
                                        <strong class="d-block" style="color: var(--ink);">Super Admin (Default)</strong>
                                        <small style="color: var(--ink-soft);">Akses seluruh wilayah & konfigurasi master</small>
                                    </div>
                                </div>
                                <span class="badge border" style="background: var(--surface-2); color: var(--ink);">Pilih &rarr;</span>
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
                                    <div class="p-2.5 rounded-circle icon-box-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="text-start">
                                        <strong class="d-block" style="color: var(--ink);">Ketua / Pengurus RW</strong>
                                        <small style="color: var(--ink-soft);">Koordinasi antar-RT, persetujuan & audit keuangan</small>
                                    </div>
                                </div>
                                <span class="badge border" style="background: var(--surface-2); color: var(--ink);">Pilih &rarr;</span>
                            </button>
                        </form>

                        {{-- 3. Ketua RT (Dropdown Selector) --}}
                        <div class="border rounded-3 p-3" style="background: var(--surface-2); border-color: var(--border) !important;">
                            <form method="POST" action="{{ route('superadmin.switch-role') }}">
                                @csrf
                                <input type="hidden" name="mode" value="rt">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="p-2.5 rounded-circle icon-box-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-landmark"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block" style="color: var(--ink);">Ketua RT (Pilih Unit RT Dinamis)</strong>
                                        <small style="color: var(--ink-soft);">Pilih RT yang ingin dikelola:</small>
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
                        <div class="border rounded-3 p-3" style="background: var(--surface-2); border-color: var(--border) !important;">
                            <form method="POST" action="{{ route('superadmin.switch-role') }}">
                                @csrf
                                <input type="hidden" name="mode" value="warga">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="p-2.5 rounded-circle icon-box-info d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block" style="color: var(--ink);">Warga (Pilih Domisili / Akun Warga)</strong>
                                        <small style="color: var(--ink-soft);">Pilih unit domisili atau nama warga:</small>
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
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-3 icon-box-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--ink);">Log Aktivitas & Audit Trail Terbaru</h6>
                            <small style="color: var(--ink-soft);">Pencatatan real-time aksi pengguna sistem</small>
                        </div>
                    </div>
                    <a href="{{ route('superadmin.audit-logs') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="small" style="background: var(--surface-2); color: var(--ink-soft);">
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
                                        <td class="ps-4 text-nowrap small" style="color: var(--ink-soft);">
                                            {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                                        </td>
                                        <td class="small fw-semibold" style="color: var(--ink);">
                                            {{ optional($log->user)->name ?? 'Sistem' }}
                                        </td>
                                        <td class="small">
                                            @if($log->rt)
                                                <span class="badge border" style="background: var(--surface-2); color: var(--ink);">RT {{ $log->rt->kode_rt }}</span>
                                            @else
                                                <span class="badge border" style="background: var(--surface-2); color: var(--ink-soft);">RW / Master</span>
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
                                        <td class="small" style="max-width: 250px; color: var(--ink-soft);">
                                            <div class="text-truncate" title="{{ $log->deskripsi }}">
                                                {{ $log->deskripsi ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end small font-monospace" style="color: var(--ink-mute);">
                                            {{ $log->ip_address ?? '127.0.0.1' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 small" style="color: var(--ink-soft);">
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
    <div class="card border-0 rounded-4 shadow-sm p-4">
        <h6 class="fw-bold mb-3" style="color: var(--ink);">
            <i class="fas fa-toolbox text-success me-2"></i>Pintasan Cepat Manajemen Sistem
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('rts.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 icon-box-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-sitemap fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block small" style="color: var(--ink);">Manajemen RT</strong>
                        <small style="color: var(--ink-soft);">Kelola unit & ketua RT</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 icon-box-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block small" style="color: var(--ink);">Data Kependudukan</strong>
                        <small style="color: var(--ink-soft);">Data warga seluruh RW</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('rw.audit.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 icon-box-accent d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-wallet fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block small" style="color: var(--ink);">Audit Keuangan</strong>
                        <small style="color: var(--ink-soft);">Pantau kas & iuran RT/RW</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start rounded-3 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 icon-box-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </div>
                    <div>
                        <strong class="d-block small" style="color: var(--ink);">Profil Pengembang</strong>
                        <small style="color: var(--ink-soft);">Pengaturan akun Anda</small>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
