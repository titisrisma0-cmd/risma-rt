@extends('layouts.app')

@section('title', 'Keamanan & Ronda Lingkungan RT')

@php
    $user = Auth::user();
    $userProfileId = optional($user->profile)->id;
    $absensiStats = $wargas->map(function ($warga) use ($jadwals) {
        $wProfileId = $warga->id;
        $wUserId = $warga->user_id ?? $warga->id;
        $absensi = $jadwals->flatMap(fn ($jadwal) => $jadwal->absensi)->filter(function ($a) use ($wProfileId, $wUserId) {
            return ($a->user_profile_id && $a->user_profile_id == $wProfileId)
                || ($a->user_id && $a->user_id == $wUserId);
        });

        return [
            'warga' => $warga,
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'alpa' => $absensi->where('status', 'alpa')->count(),
        ];
    })->sortByDesc('hadir')->values();

    $jadwalSaya = $jadwals->filter(function ($j) use ($user, $userProfileId) {
        return $j->petugas->contains(function ($p) use ($user, $userProfileId) {
            return ($p->user_id && $p->user_id == $user->id) ||
                   ($userProfileId && $p->user_profile_id && $p->user_profile_id == $userProfileId);
        });
    })->values();
    $upcomingJadwalSaya = $jadwalSaya->where('tanggal', '>=', now()->startOfDay())->sortBy('tanggal')->first();
    $activePanics = $panicReports->whereIn('status', ['aktif', 'baru']);
@endphp

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Layanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Keamanan & Ronda</li>
                </ol>
            </nav>
            <h1 class="page-title">
                <i class="fas fa-shield-halved text-success me-2"></i>Keamanan & Siskamling RT
                @if(isset($myRtId) && $isRt)
                    <span class="badge badge-soft-success fs-6 ms-1">RT {{ str_pad($myRtId, 2, '0', STR_PAD_LEFT) }}</span>
                @endif
            </h1>
            <p class="page-subtitle">Pusat tata kelola jadwal siskamling malam, kalender regu ronda, presensi digital, dan penanganan darurat warga.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($isPengurus)
                <button class="btn btn-saas-primary" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                    <i class="fas fa-calendar-plus me-1"></i>Buat Jadwal Ronda
                </button>
                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalGenerateOtomatisRonda">
                    <i class="fas fa-wand-magic-sparkles me-1"></i>Generate Otomatis Bulanan
                </button>
                <button class="btn btn-outline-saas" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda">
                    <i class="fas fa-user-check me-1"></i>Catat Presensi
                </button>
                <a href="{{ route('layanan.ronda.cctv.live') }}" class="btn btn-outline-saas text-danger border-danger">
                    <i class="fas fa-video me-1"></i>Live CCTV
                </a>
            @else
                @if($upcomingJadwalSaya)
                    <button class="btn btn-saas-primary" data-bs-toggle="modal" data-bs-target="#modalCheckinSaya">
                        <i class="fas fa-fingerprint me-1"></i>Presensi Hadir
                    </button>
                    <button class="btn btn-outline-saas text-secondary" data-bs-toggle="modal" data-bs-target="#modalIzinSaya">
                        <i class="fas fa-envelope-open-text me-1"></i>Ajukan Izin
                    </button>
                @endif
                <button class="btn btn-outline-saas text-warning border-warning" data-bs-toggle="modal" data-bs-target="#modalTukarJadwal">
                    <i class="fas fa-repeat me-1"></i>Ajukan Tukar Shift
                </button>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-saas alert-saas-success d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-check-circle fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-saas alert-saas-danger d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-circle fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- ALERT PANIC REPORT AKTIF --}}
    @if($activePanics->count() > 0)
        <div class="alert alert-saas alert-saas-danger d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 shadow-sm border border-danger">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper bg-danger text-white fs-4 p-3 rounded-circle">
                    <i class="fas fa-triangle-exclamation fa-beat"></i>
                </div>
                <div>
                    <h5 class="text-danger fw-bold mb-0">PERINGATAN DARURAT AKTIF ({{ $activePanics->count() }} Laporan Masuk)</h5>
                    <p class="text-muted small mb-0">
                        {{ $activePanics->first()->user->name ?? 'Warga' }}: {{ $activePanics->first()->deskripsi ?: 'Segera koordinasi tanggap darurat ke lokasi!' }} ({{ $activePanics->first()->created_at->diffForHumans() }})
                    </p>
                </div>
            </div>
            @if($isPengurus)
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('ronda.panic.status', $activePanics->first()) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="sedang_ditangani">
                        <button type="submit" class="btn btn-danger btn-sm fw-semibold shadow-sm">
                            <i class="fas fa-person-running me-1"></i>Tandai Ditangani
                        </button>
                    </form>
                    <form method="POST" action="{{ route('ronda.panic.status', $activePanics->first()) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit" class="btn btn-success btn-sm fw-semibold shadow-sm">
                            <i class="fas fa-check me-1"></i>Selesai
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-primary-soft text-primary">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Jadwal Shift</span>
                    <h3 class="stat-value">{{ $jadwals->count() }}</h3>
                    <span class="stat-desc">Shift terdata di sistem</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-success-soft text-success">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Jadwal Tugas Saya</span>
                    <h3 class="stat-value">{{ $jadwalSaya->count() }}</h3>
                    <span class="stat-desc">{{ $upcomingJadwalSaya ? 'Tugas terdekat: ' . $upcomingJadwalSaya->tanggal->format('d/m') : 'Belum ada giliran' }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-warning-soft text-warning">
                    <i class="fas fa-repeat"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Tukar Shift</span>
                    <h3 class="stat-value">{{ $tukarJadwals->where('status', 'menunggu')->count() }}</h3>
                    <span class="stat-desc">Menunggu persetujuan</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-danger-soft text-danger">
                    <i class="fas fa-phone-volume"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Kontak Darurat</span>
                    <h3 class="stat-value">{{ $kontaks->count() }}</h3>
                    <span class="stat-desc">Nomor siaga tanggap darurat</span>
                </div>
            </div>
        </div>
    </div>

    {{-- BANNER TUGAS SAYA (UNTUK WARGA) --}}
    @if(!$isPengurus && $upcomingJadwalSaya)
        <div class="saas-card mb-4 border border-success border-opacity-25 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-success-soft text-success p-3 rounded-circle">
                        <i class="fas fa-user-clock fs-3"></i>
                    </div>
                    <div>
                        <div class="badge badge-soft-success mb-1">JADWAL TUGAS BERIKUTNYA</div>
                        <h5 class="fw-bold text-dark mb-1">{{ $upcomingJadwalSaya->tanggal->isoFormat('dddd, D MMMM Y') }} — Shift {{ $upcomingJadwalSaya->shift }}</h5>
                        <p class="text-secondary small mb-0">
                            <i class="fas fa-location-dot text-danger me-1"></i>{{ $upcomingJadwalSaya->lokasi_pos }} • 
                            <i class="fas fa-users text-primary ms-2 me-1"></i>Rekan Regu: {{ $upcomingJadwalSaya->petugas->where('user_id', '!=', $user->id)->pluck('user.name')->join(', ') ?: 'Bertugas Tunggal' }}
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-saas-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCheckinSaya">
                        <i class="fas fa-fingerprint me-1"></i> Konfirmasi Hadir
                    </button>
                    <button class="btn btn-outline-saas btn-sm text-secondary" data-bs-toggle="modal" data-bs-target="#modalIzinSaya">
                        <i class="fas fa-envelope-open-text me-1"></i> Izin Tidak Hadir
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- TABS NAVIGATION --}}
    <div class="saas-card mb-4 p-2">
        <ul class="nav nav-tabs-saas" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-kalender" type="button">
                    <i class="fas fa-calendar-alt me-1"></i>Kalender Regu Ronda
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-jadwal" type="button">
                    <i class="fas fa-list-check me-1"></i>Daftar Jadwal Shift
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tukar" type="button">
                    <i class="fas fa-repeat me-1"></i>Pertukaran Shift
                    @if($tukarJadwals->where('status', 'menunggu')->count() > 0)
                        <span class="badge badge-soft-warning ms-1">{{ $tukarJadwals->where('status', 'menunggu')->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-absensi" type="button">
                    <i class="fas fa-chart-simple me-1"></i>Rekap Presensi
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-darurat" type="button">
                    <i class="fas fa-bell me-1"></i>Laporan Panic Button
                    @if($activePanics->count() > 0)
                        <span class="badge badge-soft-danger ms-1">{{ $activePanics->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kontak" type="button">
                    <i class="fas fa-address-book me-1"></i>Kontak Darurat
                </button>
            </li>
        </ul>
    </div>

    {{-- TABS CONTENT --}}
    <div class="tab-content">
        {{-- ==================== TAB 0: KALENDER INTERAKTIF ==================== --}}
        <div class="tab-pane fade show active" id="tab-kalender">
            <div class="saas-card p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="fas fa-calendar-days text-success me-2"></i>Kalender Jadwal Siskamling</h5>
                        <p class="text-secondary small mb-0">Klik tanggal untuk melihat daftar petugas regu yang bertugas.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-saas btn-sm" id="calPrevBtn"><i class="fas fa-chevron-left"></i></button>
                        <h6 class="fw-bold text-dark mb-0 px-2" id="calCurrentMonthTitle">Bulan Ini</h6>
                        <button class="btn btn-outline-saas btn-sm" id="calNextBtn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                {{-- Interactive Calendar Grid --}}
                <div class="calendar-wrapper">
                    <div class="calendar-header-row mb-2">
                        <div class="cal-day-header text-danger">Min</div>
                        <div class="cal-day-header">Sen</div>
                        <div class="cal-day-header">Sel</div>
                        <div class="cal-day-header">Rab</div>
                        <div class="cal-day-header">Kam</div>
                        <div class="cal-day-header">Jum</div>
                        <div class="cal-day-header text-primary">Sab</div>
                    </div>
                    <div class="calendar-days-grid" id="calendarDaysGrid">
                        {{-- Injected dynamically by JS based on actual $jadwals data --}}
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3 mt-3 pt-3 border-top small text-secondary">
                    <span class="d-flex align-items-center gap-1"><span class="badge badge-soft-success p-1">●</span> Jadwal Ronda Regu</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge badge-soft-primary p-1">●</span> Jadwal Tugas Saya</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge badge-soft-secondary p-1">●</span> Hari Ini</span>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 1: DAFTAR JADWAL SHIFT ==================== --}}
        <div class="tab-pane fade" id="tab-jadwal">
            <div class="saas-card">
                <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-calendar-check text-success me-2"></i>Daftar Penugasan Ronda Malam RT
                        </h6>
                        <small class="text-secondary">Pemisahan jadwal aktif hari ini, jadwal penugasan mendatang, dan riwayat tugas selesai</small>
                    </div>
                    <ul class="nav nav-pills gap-1" id="subJadwalTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#sub-hari-ini" type="button">
                                <i class="fas fa-clock me-1 text-warning"></i> Hari Ini ({{ ($jadwalHariIni ?? collect())->count() }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#sub-mendatang" type="button">
                                <i class="fas fa-calendar-plus me-1 text-primary"></i> Mendatang ({{ ($jadwalMendatang ?? collect())->count() }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#sub-riwayat" type="button">
                                <i class="fas fa-history me-1 text-secondary"></i> Riwayat ({{ ($jadwalRiwayat ?? collect())->count() }})
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content p-0">
                    {{-- Subtab 1: Hari Ini --}}
                    <div class="tab-pane fade show active" id="sub-hari-ini">
                        @include('layanan.partials.ronda-table', ['items' => $jadwalHariIni ?? collect(), 'emptyMessage' => 'Tidak ada jadwal penugasan ronda untuk hari ini.'])
                    </div>

                    {{-- Subtab 2: Mendatang --}}
                    <div class="tab-pane fade" id="sub-mendatang">
                        @include('layanan.partials.ronda-table', ['items' => $jadwalMendatang ?? collect(), 'emptyMessage' => 'Belum ada jadwal penugasan ronda mendatang yang dibuat.'])
                    </div>

                    {{-- Subtab 3: Riwayat --}}
                    <div class="tab-pane fade" id="sub-riwayat">
                        @include('layanan.partials.ronda-table', ['items' => $jadwalRiwayat ?? collect(), 'emptyMessage' => 'Belum ada riwayat penugasan ronda sebelumnya.'])
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 2: PERTUKARAN SHIFT ==================== --}}
        <div class="tab-pane fade" id="tab-tukar">
            <div class="saas-card mb-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-repeat text-warning me-2"></i>Daftar Pengajuan Pertukaran Jadwal Ronda
                    </h6>
                    <button class="btn btn-outline-saas btn-sm text-warning border-warning" data-bs-toggle="modal" data-bs-target="#modalTukarJadwal">
                        <i class="fas fa-plus me-1"></i>Ajukan Tukar Baru
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-saas align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Pemohon</th>
                                <th>Jadwal Asal</th>
                                <th>Jadwal / Pengganti</th>
                                <th>Alasan</th>
                                <th>Status Persetujuan</th>
                                <th class="text-end">Aksi Keputusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tukarJadwals as $tj)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $tj->user->name ?? 'Warga' }}</div>
                                        <small class="text-muted">{{ $tj->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $tj->jadwalRondaAsal?->tanggal?->format('d/m/Y') ?? '-' }} ({{ $tj->jadwalRondaAsal?->shift }})</div>
                                        <small class="text-muted">{{ $tj->jadwalRondaAsal?->lokasi_pos }}</small>
                                    </td>
                                    <td>
                                        @if($tj->targetJadwalRonda)
                                            <div>{{ $tj->targetJadwalRonda->tanggal->format('d/m/Y') }} ({{ $tj->targetJadwalRonda->shift }})</div>
                                        @endif
                                        <small class="text-muted">
                                            @if($tj->jenis_pengajuan === 'minta_pengganti')
                                                <i class="fas fa-hand-holding-hand text-warning me-1"></i>Minta Pengganti Melalui RT
                                            @else
                                                <i class="fas fa-user-group text-primary me-1"></i>Target: {{ $tj->targetUser->name ?? 'Warga RT' }}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="small text-muted">{{ $tj->alasan }}</td>
                                    <td>
                                        @if($tj->status === 'disetujui')
                                            <span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-double me-1"></i>Disetujui RT</span>
                                        @elseif($tj->status === 'ditolak')
                                            <span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-times me-1"></i>Ditolak</span>
                                        @elseif($tj->status === 'menunggu_rekan')
                                            <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-user-clock me-1"></i>Menunggu Respon Rekan</span>
                                        @else
                                            <span class="badge bg-primary text-white px-2 py-1"><i class="fas fa-landmark me-1"></i>Menunggu Keputusan RT</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(($isPengurus) && in_array($tj->status, ['menunggu_rt', 'menunggu', 'menunggu_verifikasi_rt']))
                                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalResponTukar{{ $tj->id }}">
                                                <i class="fas fa-gavel me-1"></i>Putuskan RT
                                            </button>
                                        @elseif((int) $tj->target_user_id === (int) $user->id && $tj->status === 'menunggu_rekan')
                                            <button class="btn btn-emerald text-white btn-sm" data-bs-toggle="modal" data-bs-target="#modalResponTukar{{ $tj->id }}">
                                                <i class="fas fa-check me-1"></i>Respon Rekan
                                            </button>
                                        @else
                                            <span class="text-muted small">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-repeat"></i></div>
                                            <h6 class="empty-state-title">Belum Ada Pengajuan Tukar</h6>
                                            <p class="empty-state-desc">Belum ada pengajuan pertukaran jadwal ronda tercatat.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 3: REKAP PRESENSI ==================== --}}
        <div class="tab-pane fade" id="tab-absensi">
            <div class="saas-card mb-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-simple text-info me-2"></i>Rekapitulasi Kehadiran Ronda Warga RT
                    </h6>
                    @if($isPengurus)
                        <button class="btn btn-outline-saas btn-sm text-info border-info" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda">
                            <i class="fas fa-plus me-1"></i>Input Presensi Manual
                        </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-saas align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Warga</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Izin</th>
                                <th class="text-center">Alpa</th>
                                <th>Evaluasi Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensiStats as $stat)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $stat['warga']->name }}</div>
                                        <small class="text-muted">NIK: {{ $stat['warga']->profile?->nik ?? '-' }}</small>
                                    </td>
                                    <td class="text-center"><span class="badge badge-soft-success fs-6">{{ $stat['hadir'] }}</span></td>
                                    <td class="text-center"><span class="badge badge-soft-warning fs-6">{{ $stat['izin'] }}</span></td>
                                    <td class="text-center"><span class="badge badge-soft-danger fs-6">{{ $stat['alpa'] }}</span></td>
                                    <td>
                                        @if($stat['hadir'] >= 3)
                                            <span class="badge badge-soft-success"><i class="fas fa-star me-1"></i>Sangat Aktif</span>
                                        @elseif($stat['alpa'] > $stat['hadir'])
                                            <span class="badge badge-soft-danger">Perlu Perhatian</span>
                                        @else
                                            <span class="badge badge-soft-secondary">Cukup</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-chart-simple"></i></div>
                                            <h6 class="empty-state-title">Belum Ada Rekap Presensi</h6>
                                            <p class="empty-state-desc">Belum ada data absensi ronda warga yang tercatat.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 4: LAPORAN DARURAT & PANIC BUTTON ==================== --}}
        <div class="tab-pane fade" id="tab-darurat">
            <div class="saas-card mb-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bell text-danger me-2"></i>Histori Laporan Keamanan & Panic Button
                    </h6>
                    <span class="badge badge-soft-danger">{{ $panicReports->count() }} Laporan Masuk</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-saas align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu Kejadian</th>
                                <th>Pelapor</th>
                                <th>Lokasi / Patokan</th>
                                <th>Keterangan / Laporan</th>
                                <th>Status Penanganan</th>
                                @if($isPengurus)
                                    <th class="text-end">Tindak Lanjut RT</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($panicReports as $pr)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $pr->created_at->format('d/m/Y H:i') }}</div>
                                        <small class="text-muted">{{ $pr->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $pr->user->name ?? 'Warga' }}</div>
                                        <small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $pr->user->profile?->no_hp ?? '-' }}</small>
                                    </td>
                                    <td>{{ $pr->lokasi ?: 'Wilayah RT' }}</td>
                                    <td>{{ $pr->deskripsi ?: 'Panggilan bahaya / darurat lingkungan' }}</td>
                                    <td>
                                        @if(in_array($pr->status, ['aktif', 'baru']))
                                            <span class="badge badge-soft-danger fa-beat">🚨 Baru / Aktif</span>
                                        @elseif(in_array($pr->status, ['sedang_ditangani', 'ditangani']))
                                            <span class="badge badge-soft-warning"><i class="fas fa-person-running me-1"></i>Sedang Ditangani</span>
                                        @elseif($pr->status === 'selesai')
                                            <span class="badge badge-soft-success"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                                        @elseif($pr->status === 'false_alarm' || $pr->status === 'ditolak')
                                            <span class="badge badge-soft-danger"><i class="fas fa-ban me-1"></i>False Alarm</span>
                                        @elseif($pr->status === 'diarsipkan')
                                            <span class="badge badge-soft-secondary"><i class="fas fa-box-archive me-1"></i>Diarsipkan</span>
                                        @else
                                            <span class="badge badge-soft-secondary">{{ ucfirst($pr->status) }}</span>
                                        @endif
                                    </td>
                                    @if($isPengurus)
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                @if(in_array($pr->status, ['aktif', 'baru']))
                                                    <form method="POST" action="{{ route('ronda.panic.status', $pr) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="sedang_ditangani">
                                                        <button type="submit" class="btn btn-outline-saas btn-sm text-warning border-warning" title="Mulai Tangani">
                                                            <i class="fas fa-person-running me-1"></i>Tangani
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($pr->status !== 'selesai' && $pr->status !== 'diarsipkan')
                                                    <form method="POST" action="{{ route('ronda.panic.status', $pr) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="selesai">
                                                        <button type="submit" class="btn btn-saas-primary btn-sm" data-confirm="Tandai laporan darurat ini telah selesai dan wilayah telah aman?">
                                                            <i class="fas fa-check me-1"></i>Selesai
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($pr->status === 'selesai')
                                                    <form method="POST" action="{{ route('ronda.panic.status', $pr) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="diarsipkan">
                                                        <button type="submit" class="btn btn-outline-saas btn-sm text-secondary" title="Arsipkan Laporan">
                                                            <i class="fas fa-box-archive me-1"></i>Tutup / Arsipkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isPengurus ? 6 : 5 }}" class="text-center py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-bell"></i></div>
                                            <h6 class="empty-state-title">Tidak Ada Laporan Darurat</h6>
                                            <p class="empty-state-desc">Kondisi lingkungan aman, tentram, dan kondusif.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 5: KONTAK DARURAT ==================== --}}
        <div class="tab-pane fade" id="tab-kontak">
            <div class="row g-3">
                @forelse($kontaks as $kontak)
                    <div class="col-sm-6 col-lg-4">
                        <div class="saas-card p-3 h-100 d-flex flex-row align-items-center gap-3">
                            <div class="stat-icon-wrapper bg-danger-soft text-danger p-3 rounded-circle">
                                <i class="fas fa-phone fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong class="d-block text-dark">{{ $kontak->kategori }}</strong>
                                <span class="text-muted small">{{ $kontak->nama }}</span>
                                <div class="mt-2">
                                    <a href="tel:{{ $kontak->nomor_telepon }}" class="btn btn-outline-saas btn-sm text-danger border-danger">
                                        <i class="fas fa-phone-alt me-1"></i>{{ $kontak->nomor_telepon }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="saas-card p-5 text-center text-muted">
                            <i class="fas fa-address-book fs-1 mb-2 text-secondary"></i>
                            <p class="mb-0">Belum ada daftar kontak darurat tersimpan.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- MODALS SECTION --}}
    {{-- MODAL CHECK-IN PRESENSI SAYA (WARGA) --}}
    @if($upcomingJadwalSaya)
        <div class="modal fade" id="modalCheckinSaya" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('ronda.absensi.store') }}">
                        @csrf
                        <input type="hidden" name="jadwal_ronda_id" value="{{ $upcomingJadwalSaya->id }}">
                        <input type="hidden" name="status" value="hadir">
                        <div class="modal-header border-bottom py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-fingerprint text-success me-2"></i>Konfirmasi Hadir Ronda</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 text-center">
                            <div class="stat-icon-wrapper bg-success-soft text-success mx-auto mb-3" style="width: 56px; height: 56px; display: grid; place-items: center; border-radius: 16px;">
                                <i class="fas fa-check-double fs-3"></i>
                            </div>
                            <h6 class="fw-bold text-dark">Konfirmasi Kehadiran Tugas Ronda</h6>
                            <p class="text-secondary small mb-3">
                                Anda akan mencatat presensi hadir untuk jadwal <strong>{{ $upcomingJadwalSaya->tanggal->isoFormat('dddd, D MMMM Y') }}</strong> (Shift {{ $upcomingJadwalSaya->shift }}).
                            </p>
                            <div class="text-start mb-3">
                                <label class="form-label-saas">Catatan Kehadiran (Opsional)</label>
                                <input type="text" name="catatan" class="form-control-saas" placeholder="Contoh: Hadir di Pos Ronda jam 22.00">
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 px-4">
                            <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-saas-primary"><i class="fas fa-check me-1"></i>Catat Hadir Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL IZIN SAYA (WARGA) --}}
        <div class="modal fade" id="modalIzinSaya" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('ronda.absensi.store') }}">
                        @csrf
                        <input type="hidden" name="jadwal_ronda_id" value="{{ $upcomingJadwalSaya->id }}">
                        <input type="hidden" name="status" value="izin">
                        <div class="modal-header border-bottom py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-envelope-open-text text-warning me-2"></i>Pengajuan Izin Tidak Hadir</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 text-start">
                            <div class="alert alert-saas alert-saas-warning mb-3 small">
                                Izin disampaikan untuk jadwal <strong>{{ $upcomingJadwalSaya->tanggal->isoFormat('dddd, D MMMM Y') }}</strong>. Harap berikan alasan yang jelas kepada Ketua RT.
                            </div>
                            <div class="mb-3">
                                <label class="form-label-saas">Alasan Tidak Dapat Bertugas <span class="text-danger">*</span></label>
                                <textarea name="catatan" rows="3" class="form-control-saas" placeholder="Contoh: Sedang dinas luar kota / ada keperluan keluarga mendesak..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 px-4">
                            <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-saas-primary"><i class="fas fa-paper-plane me-1"></i>Kirim Pengajuan Izin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODALS PENGURUS RT/RW --}}
    @if($isPengurus)
        {{-- MODAL BUAT JADWAL RONDA --}}
        {{-- REDESIGNED MODAL BUAT JADWAL RONDA (DONEZO MODERN SAAS, 2-COLUMN, MODAL-LG) --}}
        <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-labelledby="modalTambahJadwalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <form method="POST" action="{{ route('ronda.jadwal.store') }}">
                        @csrf
                        <div class="modal-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center bg-white">
                            <div>
                                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalTambahJadwalLabel">
                                    <i class="fas fa-calendar-plus text-emerald"></i>
                                    <span>Penjadwalan Tugas Ronda RT</span>
                                </h5>
                                <p class="small text-secondary mb-0">Atur tanggal penugasan dan pilih petugas ronda dari daftar warga RT</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalGenerateOtomatisRonda">
                                    <i class="fas fa-wand-magic-sparkles me-1"></i> Generate Otomatis
                                </button>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                        </div>

                        <div class="modal-body p-4 bg-light bg-opacity-25">
                            <div class="row g-4">
                                {{-- Kolom Kiri: Parameter Waktu & Lokasi --}}
                                <div class="col-md-5 border-end pe-md-4">
                                    <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-3">
                                        <i class="fas fa-clock text-emerald me-1"></i> Detail Waktu & Lokasi
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label-custom small fw-semibold">Tanggal Tugas <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="form-control form-control-saas" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label-custom small fw-semibold">Shift Penugasan <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-saas" name="shift" required>
                                            <option value="Shift 1 (Malam)">Shift 1 (Malam: 22:00 - 02:00)</option>
                                            <option value="Shift 2 (Dini Hari)">Shift 2 (Dini Hari: 02:00 - 05:00)</option>
                                            <option value="Malam Penuh">Malam Penuh (22:00 - 05:00)</option>
                                        </select>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label-custom small fw-semibold">Jam Mulai</label>
                                            <input type="time" name="jam_mulai" value="22:00" class="form-control form-control-saas">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label-custom small fw-semibold">Jam Selesai</label>
                                            <input type="time" name="jam_selesai" value="02:00" class="form-control form-control-saas">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label-custom small fw-semibold">Lokasi Pos Titik Kumpul</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-map-pin"></i></span>
                                            <input type="text" name="lokasi_pos" value="Pos Ronda RT" class="form-control form-control-saas border-start-0" placeholder="Pos Ronda RT">
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Pemilihan Petugas Warga RT --}}
                                <div class="col-md-7 ps-md-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-0">
                                            <i class="fas fa-users text-emerald me-1"></i> Pilih Petugas Ronda
                                        </h6>
                                        <span class="badge bg-emerald text-white rounded-pill px-2 py-1" id="officerCountBadge">0 Petugas Terpilih</span>
                                    </div>

                                    {{-- Search Box & Filter --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-8">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                                <input type="text" id="officerSearchInput" class="form-control border-start-0 form-control-saas" placeholder="Cari nama warga..." oninput="filterOfficerList()">
                                            </div>
                                        </div>
                                        <div class="col-4 d-flex align-items-center">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="eligibleOnlySwitch" checked onchange="filterOfficerList()">
                                                <label class="form-check-label small text-secondary" for="eligibleOnlySwitch" style="font-size:0.75rem;">Eligible</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Scrollable List of Citizens --}}
                                    <div class="officer-selection-box p-2 bg-white rounded-3 border" style="max-height: 270px; overflow-y: auto;" id="officerListContainer">
                                        @forelse($wargas as $w)
                                            @php
                                                $namaW = $w->nama_lengkap ?? $w->name;
                                                $isLaki = $w->jenis_kelamin === 'L';
                                                $isSiap = $w->siap_ronda ?? true;
                                                $isKK = strtolower((string)$w->status_hubungan_keluarga) === 'kepala keluarga';
                                                $age = $w->age ?? ($w->tanggal_lahir instanceof \Carbon\CarbonInterface ? $w->tanggal_lahir->age : ($w->tanggal_lahir ? \Carbon\Carbon::parse($w->tanggal_lahir)->age : null));
                                                $isEligible = $isLaki && $isSiap && (!$age || $age >= 17) && (!$age || $age < 60 || $isSiap);
                                            @endphp
                                            <div class="officer-item d-flex align-items-center justify-content-between p-2 mb-1 rounded border-bottom-subtle {{ $isEligible ? 'is-eligible' : 'is-not-eligible' }}"
                                                 data-nama="{{ strtolower($namaW) }}"
                                                 data-eligible="{{ $isEligible ? '1' : '0' }}">
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="checkbox" name="petugas[]" value="{{ $w->id }}" id="chkOff{{ $w->id }}" class="form-check-input officer-check mt-0" onchange="calcSelectedOfficers()">
                                                    <label for="chkOff{{ $w->id }}" class="mb-0 cursor-pointer">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="fw-semibold text-dark small">{{ $namaW }}</span>
                                                            @if($isKK)
                                                                <span class="badge bg-light text-primary border" style="font-size: 0.62rem;">KK</span>
                                                            @endif
                                                            @if($isEligible)
                                                                <span class="badge-soft-emerald" style="font-size: 0.62rem;">Siap Ronda</span>
                                                            @else
                                                                <span class="badge bg-light text-muted border" style="font-size: 0.62rem;">Dikecualikan</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-secondary" style="font-size: 0.72rem;">
                                                            NIK: {{ $w->nik ?? optional($w->profile)->nik ?? '-' }} &bull; HP: {{ $w->no_hp ?? optional($w->profile)->no_hp ?? '-' }}
                                                            @if($age)
                                                                &bull; Usia: {{ $age }} thn
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted small">
                                                Belum ada data warga terdaftar di RT ini.
                                            </div>
                                        @endforelse
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                                        <i class="fas fa-info-circle me-1 text-emerald"></i>Centang warga yang akan bertugas pada regu malam ini.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-top py-3 px-4 d-flex justify-content-between align-items-center bg-white">
                            <button type="button" class="btn btn-outline-success d-inline-flex align-items-center gap-1" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalGenerateOtomatisRonda">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                <span>Buka Generator Otomatis</span>
                            </button>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-emerald text-white px-4">
                                    <i class="fas fa-save me-1"></i> Simpan Jadwal Manual
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL PARAMETER GENERATOR OTOMATIS BULANAN --}}
        <div class="modal fade" id="modalGenerateOtomatisRonda" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-bottom py-3 px-4 bg-white">
                        <div>
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-wand-magic-sparkles text-emerald"></i>
                                <span>Generator Ronda Otomatis Berkeadilan</span>
                            </h5>
                            <p class="small text-secondary mb-0">Sistem otomatis menyusun giliran ronda 1 bulan penuh secara proporsional</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form id="formGenerateRonda" onsubmit="event.preventDefault(); fetchPreviewGenerate();">
                        <div class="modal-body p-4">
                            <div class="alert-saas alert-saas-info mb-3">
                                <i class="fas fa-circle-info fs-5"></i>
                                <div style="font-size: 0.82rem;">
                                    Algoritma hanya memilih warga laki-laki dewasa eligible, memprioritaskan Kepala Keluarga, memberi jeda istirahat yang adil, dan mencegah penugasan ganda dalam satu malam.
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Bulan Penjadwalan <span class="text-danger">*</span></label>
                                    <select name="bulan" id="genBulan" class="form-select form-control-saas" required>
                                        @php
                                            $currMonth = (int) date('n');
                                            $monthsIndo = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                                        @endphp
                                        @foreach($monthsIndo as $mNum => $mName)
                                            <option value="{{ $mNum }}" {{ $currMonth === $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Tahun <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun" id="genTahun" value="{{ date('Y') }}" min="2020" max="2035" class="form-control form-control-saas" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Petugas per Malam <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah_petugas" id="genJumlahPetugas" value="3" min="1" max="10" class="form-control form-control-saas" required>
                                    <small class="text-muted" style="font-size:0.7rem;">Default: 3-4 orang / regu</small>
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Jumlah Shift / Malam <span class="text-danger">*</span></label>
                                    <select name="jumlah_shift" id="genJumlahShift" class="form-select form-control-saas" onchange="toggleShift2Config(this.value)">
                                        <option value="1" selected>1 Shift (Malam)</option>
                                        <option value="2">2 Shift (Malam & Dini Hari)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Shift 1 Jam Mulai</label>
                                    <input type="time" name="jam_mulai_1" id="genJamMulai1" value="22:00" class="form-control form-control-saas" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Shift 1 Jam Selesai</label>
                                    <input type="time" name="jam_selesai_1" id="genJamSelesai1" value="02:00" class="form-control form-control-saas" required>
                                </div>
                            </div>

                            <div class="row g-2 mb-3" id="shift2ConfigBox" style="display: none;">
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Shift 2 Jam Mulai</label>
                                    <input type="time" name="jam_mulai_2" id="genJamMulai2" value="02:00" class="form-control form-control-saas">
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom small fw-semibold">Shift 2 Jam Selesai</label>
                                    <input type="time" name="jam_selesai_2" id="genJamSelesai2" value="05:00" class="form-control form-control-saas">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label-custom small fw-semibold">Lokasi Pos Ronda Default</label>
                                <input type="text" name="lokasi_pos" id="genLokasiPos" value="Pos Ronda RT" class="form-control form-control-saas" placeholder="Pos Ronda RT">
                            </div>
                        </div>

                        <div class="modal-footer border-top py-3 px-4 d-flex justify-content-end gap-2 bg-white">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4" id="btnSubmitGenerate">
                                <i class="fas fa-eye me-1"></i> Tampilkan Preview Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL PREVIEW JADWAL OTOMATIS BULAN PENUH (MODAL-XL) --}}
        <div class="modal fade" id="modalPreviewJadwalOtomatis" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-bottom py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-calendar-check text-emerald"></i>
                                <span>Preview Hasil Penjadwalan Ronda Bulanan</span>
                            </h5>
                            <p class="small text-secondary mb-0" id="previewMetaSubtitle">Memuat rancangan distribusi jadwal ronda...</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-light bg-opacity-25">
                        {{-- Warning Box (jika ada) --}}
                        <div id="previewWarningBox" class="alert-saas alert-saas-warning mb-3" style="display: none;">
                            <i class="fas fa-triangle-exclamation fs-5"></i>
                            <div id="previewWarningText"></div>
                        </div>

                        {{-- Metric Cards --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="saas-card p-3 border text-center">
                                    <div class="text-secondary small fw-medium">Warga Eligible</div>
                                    <h4 class="fw-bold mb-0 text-dark" id="statPreviewEligible">0</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="saas-card p-3 border text-center">
                                    <div class="text-secondary small fw-medium">Total Jadwal Malam</div>
                                    <h4 class="fw-bold mb-0 text-emerald" id="statPreviewTotalJadwal">0</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="saas-card p-3 border text-center">
                                    <div class="text-secondary small fw-medium">Petugas per Malam</div>
                                    <h4 class="fw-bold mb-0 text-dark" id="statPreviewPetugasPerMalam">0</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="saas-card p-3 border text-center">
                                    <div class="text-secondary small fw-medium">Rata-rata Tugas/Warga</div>
                                    <h4 class="fw-bold mb-0 text-primary" id="statPreviewAvgTugas">0x</h4>
                                </div>
                            </div>
                        </div>

                        {{-- Tabs Preview --}}
                        <ul class="nav nav-pills mb-3 gap-2" id="pills-tab-preview" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="btn btn-sm btn-outline-saas active" id="pill-jadwal-tab" data-bs-toggle="pill" data-bs-target="#pill-jadwal" type="button" role="tab">
                                    <i class="fas fa-list me-1"></i> Rincian Jadwal Harian
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="btn btn-sm btn-outline-saas" id="pill-distribusi-tab" data-bs-toggle="pill" data-bs-target="#pill-distribusi" type="button" role="tab">
                                    <i class="fas fa-chart-pie me-1"></i> Rekap Distribusi Petugas (Fairness)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="pills-tabContentPreview">
                            {{-- Tab 1: Rincian Jadwal Harian --}}
                            <div class="tab-pane fade show active" id="pill-jadwal" role="tabpanel">
                                <div class="table-responsive bg-white rounded-3 border">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3" style="width: 140px;">Tanggal</th>
                                                <th style="width: 130px;">Shift & Jam</th>
                                                <th style="width: 160px;">Lokasi Pos</th>
                                                <th>Regu Petugas Ronda Bertugas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewJadwalTableBody">
                                            {{-- Rows inserted via JavaScript --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab 2: Rekap Distribusi Petugas --}}
                            <div class="tab-pane fade" id="pill-distribusi" role="tabpanel">
                                <div class="saas-card p-3 border bg-white">
                                    <h6 class="fw-bold text-dark small mb-3">Distribusi Frekuensi Penugasan Tiap Warga</h6>
                                    <div class="d-flex flex-wrap gap-2" id="previewDistribusiPills">
                                        {{-- Pills inserted via JavaScript --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-3 px-4 d-flex justify-content-between align-items-center bg-white">
                        <button type="button" class="btn btn-outline-secondary" onclick="fetchPreviewGenerate()">
                            <i class="fas fa-rotate me-1"></i> Acak Ulang (Re-generate)
                        </button>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-emerald text-white px-4" id="btnCommitAutoSchedule" onclick="commitAutoSchedule()">
                                <i class="fas fa-check-double me-1"></i> Simpan Seluruh Jadwal ke Database
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL INPUT ABSENSI RONDA --}}
        <div class="modal fade" id="modalAbsensiRonda" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('ronda.absensi.store') }}">
                        @csrf
                        <div class="modal-header border-bottom py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="fas fa-user-check text-info me-2"></i>Pencatatan Presensi Ronda
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start p-4">
                            <div class="mb-3">
                                <label class="form-label-saas">Pilih Jadwal Shift <span class="text-danger">*</span></label>
                                <select name="jadwal_ronda_id" class="form-select-saas" required>
                                    @foreach($jadwals->take(15) as $j)
                                        <option value="{{ $j->id }}">{{ $j->tanggal->format('d/m/Y') }} - {{ $j->shift }} ({{ $j->lokasi_pos }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-saas">Pilih Petugas Warga <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select-saas" required>
                                    @foreach($wargas as $w)
                                        <option value="{{ $w->user_id ?? $w->id }}">{{ $w->nama_lengkap ?? $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-saas">Status Kehadiran <span class="text-danger">*</span></label>
                                <select name="status" class="form-select-saas" required>
                                    <option value="hadir">Hadir Bertugas</option>
                                    <option value="izin">Izin (Dengan Pemberitahuan)</option>
                                    <option value="alpa">Alpa (Tanpa Keterangan)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label-saas">Catatan</label>
                                <input type="text" name="catatan" class="form-control-saas" placeholder="Contoh: Hadir tepat waktu jam 22.00 WIB">
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 px-4">
                            <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-saas-primary"><i class="fas fa-check me-1"></i>Simpan Presensi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT JADWAL RONDA --}}
        @foreach($jadwals as $jEdit)
            <div class="modal fade" id="modalEditJadwalRonda{{ $jEdit->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <form method="POST" action="{{ route('ronda.jadwal.update', $jEdit) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-header border-bottom py-3 px-4">
                                <h5 class="modal-title fw-bold text-dark">
                                    <i class="fas fa-edit text-primary me-2"></i>Edit Jadwal Ronda
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start p-4">
                                <div class="mb-3">
                                    <label class="form-label-saas">Tanggal Tugas <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" value="{{ $jEdit->tanggal->format('Y-m-d') }}" class="form-control-saas" required>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label-saas">Shift <span class="text-danger">*</span></label>
                                        <input type="text" name="shift" value="{{ $jEdit->shift }}" class="form-control-saas" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label-saas">Status</label>
                                        <select name="status" class="form-select-saas">
                                            <option value="aktif" {{ $jEdit->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="selesai" {{ $jEdit->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="dibatalkan" {{ $jEdit->status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-saas">Lokasi Pos Titik Kumpul</label>
                                    <input type="text" name="lokasi_pos" value="{{ $jEdit->lokasi_pos }}" class="form-control-saas">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label-saas">Petugas yang Ditugaskan</label>
                                    @php
                                        $assignedUserIds = $jEdit->petugas->pluck('user_id')->filter()->toArray();
                                        $assignedProfileIds = $jEdit->petugas->pluck('user_profile_id')->filter()->toArray();
                                    @endphp
                                    <select name="petugas[]" class="form-select-saas" multiple size="4">
                                        @foreach($wargas as $w)
                                            @php
                                                $wId = $w->id;
                                                $wUserId = $w->user_id ?? $w->id;
                                                $isSelected = in_array($wId, $assignedProfileIds) || in_array($wUserId, $assignedUserIds);
                                            @endphp
                                            <option value="{{ $wId }}" {{ $isSelected ? 'selected' : '' }}>{{ $w->nama_lengkap ?? $w->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted mt-1 d-block">Tahan Ctrl/Command untuk memilih lebih dari 1 warga.</small>
                                </div>
                            </div>
                            <div class="modal-footer border-top py-3 px-4">
                                <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-saas-primary"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- MODAL RESPON TUKAR JADWAL --}}
        @foreach($tukarJadwals as $tjResp)
            <div class="modal fade" id="modalResponTukar{{ $tjResp->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <form method="POST" action="{{ route('ronda.tukar.respon', $tjResp) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-header border-bottom py-3 px-4">
                                <h5 class="modal-title fw-bold text-dark">
                                    <i class="fas fa-gavel text-warning me-2"></i>Persetujuan Pertukaran Shift Ronda
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start p-4">
                                <div class="alert alert-saas alert-saas-warning mb-3">
                                    Warga <strong>{{ $tjResp->user->name ?? 'Pemohon' }}</strong> mengajukan tukar jadwal shift <strong>{{ $tjResp->jadwalRondaAsal?->tanggal?->format('d/m/Y') }} ({{ $tjResp->jadwalRondaAsal?->shift }})</strong>.
                                    <div class="mt-1">Tipe: <strong>{{ $tjResp->jenis_pengajuan === 'minta_pengganti' ? 'Meminta Pengganti Melalui RT' : 'Tukar dengan Rekan' }}</strong></div>
                                    <div>Alasan: <em>"{{ $tjResp->alasan }}"</em></div>
                                </div>

                                @if(($isPengurus) && $tjResp->jenis_pengajuan === 'minta_pengganti')
                                    <div class="mb-3">
                                        <label class="form-label-saas fw-bold">Tentukan Warga Pengganti <span class="text-danger">*</span></label>
                                        <select name="pengganti_user_id" class="form-select-saas" required>
                                            <option value="">-- Pilih Warga Pengganti Dari RT --</option>
                                            @foreach($wargas as $pw)
                                                @php
                                                    $pwId = $pw->user_id ?? $pw->id;
                                                    $pwName = $pw->nama_lengkap ?? $pw->name;
                                                    $pwNik = $pw->nik ?? optional($pw->profile)->nik ?? '-';
                                                @endphp
                                                @if($pwId != $tjResp->user_id)
                                                    <option value="{{ $pwId }}">{{ $pwName }} (NIK: {{ $pwNik }})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <small class="text-secondary">Warga ini akan otomatis ditugaskan ke jadwal shift pemohon.</small>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label-saas">Keputusan <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select-saas" required>
                                        <option value="disetujui">Setujui Pertukaran Shift</option>
                                        <option value="ditolak">Tolak Pertukaran Shift</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label-saas">Catatan Pengurus / Rekan</label>
                                    <textarea name="catatan_rt" class="form-control-saas" rows="2" placeholder="Catatan persetujuan / alasan jika ditolak..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top py-3 px-4">
                                <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-saas-primary">Simpan Keputusan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- MODAL AJUKAN TUKAR JADWAL (WARGA & UMUM) --}}
    <div class="modal fade" id="modalTukarJadwal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form method="POST" action="{{ route('ronda.tukar.store') }}">
                    @csrf
                    <div class="modal-header border-bottom py-3 px-4">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="fas fa-repeat text-warning me-2"></i>Pengajuan Tukar Jadwal Ronda
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-start p-4">
                        <div class="mb-3">
                            <label class="form-label-saas">Pilih Alur Pengajuan <span class="text-danger">*</span></label>
                            <div class="p-3 bg-light rounded border">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="jenis_pengajuan" id="radioTukarRekan" value="tukar_rekan" checked onchange="toggleAlurTukarForm()">
                                    <label class="form-check-label fw-bold text-dark" for="radioTukarRekan">
                                        1. Tukar dengan Rekan Tertentu
                                    </label>
                                    <div class="text-secondary small">Kirim permohonan ke rekan. Rekan menyetujui lebih dulu, lalu Ketua RT memberikan verifikasi akhir.</div>
                                </div>
                                <div class="form-check pt-2 border-top">
                                    <input class="form-check-input" type="radio" name="jenis_pengajuan" id="radioMintaPengganti" value="minta_pengganti" onchange="toggleAlurTukarForm()">
                                    <label class="form-check-label fw-bold text-dark" for="radioMintaPengganti">
                                        2. Meminta Pengganti Melalui Ketua RT
                                    </label>
                                    <div class="text-secondary small">Kirim langsung ke Ketua RT untuk mencarikan dan menugaskan warga pengganti.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-saas">Pilih Jadwal Tugas Saya <span class="text-danger">*</span></label>
                            <select name="jadwal_ronda_asal_id" class="form-select-saas" required>
                                <option value="">-- Pilih Jadwal Saya --</option>
                                @foreach($jadwalSaya as $myJ)
                                    <option value="{{ $myJ->id }}">{{ $myJ->tanggal->format('d/m/Y') }} ({{ $myJ->shift }}) - {{ $myJ->lokasi_pos }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="groupPilihRekan">
                            <label class="form-label-saas">Pilih Rekan Warga Yang Diajak Bertukar <span class="text-danger">*</span></label>
                            <select name="target_user_id" id="selectTargetWarga" class="form-select-saas" required>
                                <option value="">-- Pilih Rekan Warga Satu RT --</option>
                                @foreach($wargas as $tw)
                                    @php
                                        $twId = $tw->user_id ?? $tw->id;
                                        $twName = $tw->nama_lengkap ?? $tw->name;
                                        $twNik = $tw->nik ?? optional($tw->profile)->nik ?? '-';
                                    @endphp
                                    @if($twId != $user->id)
                                        <option value="{{ $twId }}">{{ $twName }} (NIK: {{ $twNik }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="groupPilihJadwalRekan">
                            <label class="form-label-saas">Pilih Jadwal Rekan Pengganti (Opsional)</label>
                            <select name="target_jadwal_ronda_id" class="form-select-saas">
                                <option value="">-- Pilih Jadwal Pengganti --</option>
                                @foreach($jadwals as $allJ)
                                    <option value="{{ $allJ->id }}">{{ $allJ->tanggal->format('d/m/Y') }} ({{ $allJ->shift }}) - {{ $allJ->lokasi_pos }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label-saas">Alasan Pengajuan <span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control-saas" rows="2" placeholder="Contoh: Ada tugas dinas luar kota / urusan keluarga mendadak" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-saas-primary"><i class="fas fa-paper-plane me-1"></i>Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL POPUP DETAIL REGU PADA TANGGAL (INTERAKTIF KALENDER) --}}
    <div class="modal fade" id="modalDetailJadwalTanggal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom bg-light py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark mb-0">
                        <i class="fas fa-calendar-day text-emerald me-2"></i>Detail Regu Ronda: <span id="detailModalTanggalText">-</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detailModalBody">
                    {{-- Diisi secara dinamis oleh JavaScript saat tanggal diklik --}}
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-wrapper {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
}
.calendar-header-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    font-weight: 700;
    font-size: 0.82rem;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-color);
}
.calendar-days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
    margin-top: 8px;
}
.cal-day-cell {
    min-height: 85px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 6px;
    background: #FAFCFA;
    transition: all 0.15s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.cal-day-cell:hover {
    background: var(--soft-green);
    border-color: var(--primary-green);
}
.cal-day-cell.other-month {
    opacity: 0.35;
    background: #F3F4F6;
}
.cal-day-cell.today {
    border-color: var(--primary-green);
    background: #FFFFFF;
    box-shadow: 0 0 0 2px rgba(20, 122, 69, 0.2);
}
.cal-day-num {
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--main-text);
}
.cal-day-events {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-top: 4px;
}
.cal-event-pill {
    font-size: 0.68rem;
    padding: 2px 4px;
    border-radius: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    line-height: 1.2;
}
@media (max-width: 768px) {
    .cal-day-cell {
        min-height: 60px;
        padding: 3px;
    }
    .cal-event-pill {
        font-size: 0.6rem;
    }
}
</style>

<script>
window.toggleAlurTukarForm = function() {
    const isTukarRekan = document.getElementById('radioTukarRekan')?.checked;
    const groupRekan = document.getElementById('groupPilihRekan');
    const groupJadwal = document.getElementById('groupPilihJadwalRekan');
    const selectTargetWarga = document.getElementById('selectTargetWarga');
    
    if (groupRekan) {
        groupRekan.style.display = isTukarRekan ? 'block' : 'none';
    }
    if (groupJadwal) {
        groupJadwal.style.display = isTukarRekan ? 'block' : 'none';
    }
    if (selectTargetWarga) {
        selectTargetWarga.required = !!isTukarRekan;
    }
};

document.addEventListener('DOMContentLoaded', function () {
    // Jalankan inisialisasi alur tukar form
    if (typeof window.toggleAlurTukarForm === 'function') {
        window.toggleAlurTukarForm();
    }

    // Mode toggle acak vs manual
    const modeSelect = document.getElementById('modeJadwalSelect');
    const acakParams = document.getElementById('acakParams');
    if (modeSelect && acakParams) {
        modeSelect.addEventListener('change', function () {
            acakParams.style.display = this.value === 'acak' ? 'flex' : 'none';
        });
    }

    // Dynamic Calendar Implementation
    const rawJadwals = @json($calendarJadwals ?? []);
    let currentCalDate = new Date();

    function formatTanggalIndonesia(dateStr) {
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return `${days[d.getDay()]}, ${parts[2]} ${months[d.getMonth()]} ${parts[0]}`;
    }

    function openDetailModal(dateStr, dayJadwals) {
        const titleEl = document.getElementById('detailModalTanggalText');
        const bodyEl = document.getElementById('detailModalBody');
        if (!titleEl || !bodyEl) return;

        titleEl.textContent = formatTanggalIndonesia(dateStr);

        if (!dayJadwals || dayJadwals.length === 0) {
            bodyEl.innerHTML = `
                <div class="text-center py-4">
                    <div class="text-muted mb-2"><i class="fas fa-calendar-xmark fa-2x text-secondary"></i></div>
                    <p class="text-secondary mb-0">Tidak ada jadwal ronda yang terdaftar pada tanggal ini.</p>
                </div>
            `;
        } else {
            let html = '';
            dayJadwals.forEach((j, idx) => {
                let officerBadges = '';
                if (j.petugas && j.petugas.length > 0) {
                    officerBadges = j.petugas.map(pName => {
                        const isMine = j.is_mine && pName === "{{ $user->name }}";
                        if (isMine) {
                            return `<span class="badge" style="background-color: #147A45; color: #FFFFFF; border: 1px solid #0D5737; font-size: 0.8rem; padding: 6px 10px; border-radius: 6px;"><i class="fas fa-user-shield me-1"></i>${pName} (Saya)</span>`;
                        } else {
                            return `<span class="badge" style="background-color: #E8F5EC; color: #0D5737; border: 1px solid #A3D9B8; font-size: 0.8rem; padding: 6px 10px; border-radius: 6px;"><i class="fas fa-user me-1"></i>${pName}</span>`;
                        }
                    }).join(' ');
                } else {
                    officerBadges = '<span class="text-muted fst-italic">Belum ada petugas ditugaskan</span>';
                }

                html += `
                    <div class="p-3 mb-3 rounded-3 border bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark"><i class="fas fa-clock text-emerald me-1"></i>Shift: ${j.shift}</span>
                            <span class="badge badge-soft-success border border-success"><i class="fas fa-map-marker-alt me-1"></i>${j.lokasi}</span>
                        </div>
                        <div class="mb-2">
                            <div class="text-xs text-muted mb-1 fw-semibold text-uppercase tracking-wider">Regu Petugas:</div>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                ${officerBadges}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top text-xs text-muted mt-2">
                            <span>Status Jadwal: <strong class="text-dark">${j.status ? j.status.toUpperCase() : 'AKTIF'}</strong></span>
                            ${j.has_swap_pending ? '<span class="badge badge-soft-warning"><i class="fas fa-arrows-rotate me-1"></i>Ada Pengajuan Tukar</span>' : ''}
                        </div>
                    </div>
                `;
            });
            bodyEl.innerHTML = html;
        }

        const modalEl = document.getElementById('modalDetailJadwalTanggal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }
    }

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const titleEl = document.getElementById('calCurrentMonthTitle');
        if (titleEl) {
            titleEl.textContent = `${monthNames[month]} ${year}`;
        }

        const firstDayIndex = new Date(year, month, 1).getDay();
        const lastDay = new Date(year, month + 1, 0).getDate();
        const prevLastDay = new Date(year, month, 0).getDate();

        const grid = document.getElementById('calendarDaysGrid');
        if (!grid) return;
        grid.innerHTML = '';

        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;

        // Previous month filler days
        for (let i = firstDayIndex; i > 0; i--) {
            const dayNum = prevLastDay - i + 1;
            const cell = document.createElement('div');
            cell.className = 'cal-day-cell other-month';
            cell.innerHTML = `<span class="cal-day-num">${dayNum}</span>`;
            grid.appendChild(cell);
        }

        // Current month days
        for (let i = 1; i <= lastDay; i++) {
            const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
            const isToday = dateStr === todayStr;
            const dayJadwals = Array.isArray(rawJadwals) ? rawJadwals.filter(j => j.tanggal === dateStr) : [];

            const cell = document.createElement('div');
            cell.className = `cal-day-cell ${isToday ? 'today' : ''}`;
            cell.style.cursor = 'pointer';
            cell.setAttribute('title', 'Klik untuk melihat regu ronda');

            let eventsHtml = '';
            dayJadwals.forEach(j => {
                const pillClass = j.is_mine ? 'badge badge-soft-primary border border-primary' : 'badge badge-soft-success';
                const officerNames = j.petugas && j.petugas.length ? j.petugas.slice(0, 2).join(', ') : 'Belum ada';
                const swapBadge = j.has_swap_pending ? '<span class="badge badge-soft-warning ms-1" style="font-size:0.55rem;"><i class="fas fa-arrows-rotate"></i></span>' : '';
                const absensiBadge = j.my_absensi === 'hadir' ? '<span class="badge badge-soft-success ms-1" style="font-size:0.55rem;"><i class="fas fa-check"></i></span>' : '';
                
                eventsHtml += `<span class="cal-event-pill ${pillClass} d-inline-flex align-items-center" title="Pos: ${j.lokasi} | Petugas: ${(j.petugas || []).join(', ')}">
                    <i class="fas fa-shield-alt me-1"></i>${j.shift}: ${officerNames} ${swapBadge} ${absensiBadge}
                </span>`;
            });

            cell.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span class="cal-day-num ${isToday ? 'text-success fw-bold' : ''}">${i}</span>
                    ${isToday ? '<span class="badge badge-soft-secondary" style="font-size:0.6rem;">Hari Ini</span>' : ''}
                </div>
                <div class="cal-day-events">${eventsHtml}</div>
            `;

            cell.addEventListener('click', function() {
                openDetailModal(dateStr, dayJadwals);
            });

            grid.appendChild(cell);
        }
    }

    renderCalendar(currentCalDate);

    const prevBtn = document.getElementById('calPrevBtn');
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentCalDate.setMonth(currentCalDate.getMonth() - 1);
            renderCalendar(currentCalDate);
        });
    }

    const nextBtn = document.getElementById('calNextBtn');
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentCalDate.setMonth(currentCalDate.getMonth() + 1);
            renderCalendar(currentCalDate);
        });
    }
});

// ==========================================
// LOGIKA PENJADWALAN RONDA OTOMATIS & MODAL
// ==========================================
function filterOfficerList() {
    const search = (document.getElementById('officerSearchInput')?.value || '').toLowerCase();
    const onlyEligible = document.getElementById('eligibleOnlySwitch')?.checked ?? true;
    const items = document.querySelectorAll('.officer-item');

    items.forEach(item => {
        const name = item.getAttribute('data-nama') || '';
        const isEligible = item.getAttribute('data-eligible') === '1';

        const matchSearch = name.includes(search);
        const matchEligible = !onlyEligible || isEligible;

        if (matchSearch && matchEligible) {
            item.style.setProperty('display', 'flex', 'important');
        } else {
            item.style.setProperty('display', 'none', 'important');
        }
    });
}

function calcSelectedOfficers() {
    const checked = document.querySelectorAll('.officer-check:checked');
    const badge = document.getElementById('officerCountBadge');
    if (badge) {
        badge.textContent = `${checked.length} Petugas Terpilih`;
    }
}

function toggleShift2Config(val) {
    const box = document.getElementById('shift2ConfigBox');
    if (box) {
        box.style.display = val === '2' ? 'flex' : 'none';
    }
}

let currentGeneratedData = null;

async function fetchPreviewGenerate() {
    const btn = document.getElementById('btnSubmitGenerate');
    const origBtnHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyusun Jadwal...';
    }

    const bulan = document.getElementById('genBulan').value;
    const tahun = document.getElementById('genTahun').value;
    const jumlahPetugas = document.getElementById('genJumlahPetugas').value;
    const jumlahShift = document.getElementById('genJumlahShift').value;
    const jamMulai1 = document.getElementById('genJamMulai1').value;
    const jamSelesai1 = document.getElementById('genJamSelesai1').value;
    const jamMulai2 = document.getElementById('genJamMulai2')?.value || '';
    const jamSelesai2 = document.getElementById('genJamSelesai2')?.value || '';
    const lokasiPos = document.getElementById('genLokasiPos').value;

    try {
        const resp = await fetch("{{ route('ronda.generate-preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                bulan: bulan,
                tahun: tahun,
                jumlah_petugas: jumlahPetugas,
                jumlah_shift: jumlahShift,
                jam_mulai_1: jamMulai1,
                jam_selesai_1: jamSelesai1,
                jam_mulai_2: jamMulai2,
                jam_selesai_2: jamSelesai2,
                lokasi_pos: lokasiPos
            })
        });

        const data = await resp.json();

        if (!resp.ok || !data.success) {
            alert(data.message || 'Gagal menghasilkan preview jadwal ronda.');
            return;
        }

        currentGeneratedData = data;
        renderGeneratedPreview(data);

        const genModalEl = document.getElementById('modalGenerateOtomatisRonda');
        if (genModalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getInstance(genModalEl)?.hide();
        }

        const previewModalEl = document.getElementById('modalPreviewJadwalOtomatis');
        if (previewModalEl && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(previewModalEl).show();
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan saat menghubungi server: ' + err.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origBtnHtml;
        }
    }
}

function renderGeneratedPreview(data) {
    const meta = data.meta;
    document.getElementById('previewMetaSubtitle').textContent = 
        `Periode: ${meta.nama_bulan} • ${meta.total_jadwal} Jadwal Tugas • ${meta.total_eligible} Warga Eligible`;

    const warnBox = document.getElementById('previewWarningBox');
    const warnText = document.getElementById('previewWarningText');
    if (meta.warning) {
        warnText.textContent = meta.warning;
        warnBox.style.display = 'flex';
    } else {
        warnBox.style.display = 'none';
    }

    document.getElementById('statPreviewEligible').textContent = meta.total_eligible;
    document.getElementById('statPreviewTotalJadwal').textContent = meta.total_jadwal;
    document.getElementById('statPreviewPetugasPerMalam').textContent = `${meta.jumlah_petugas} orang`;
    
    const avg = meta.total_eligible > 0 ? (meta.total_jadwal * meta.jumlah_petugas / meta.total_eligible).toFixed(1) : 0;
    document.getElementById('statPreviewAvgTugas').textContent = `~${avg}x`;

    const tbody = document.getElementById('previewJadwalTableBody');
    tbody.innerHTML = '';

    data.jadwals.forEach((j, jIndex) => {
        const tr = document.createElement('tr');
        
        let officersHtml = '';
        j.petugas.forEach((p, pIndex) => {
            officersHtml += `
                <span class="badge bg-white text-dark border px-2 py-1 shadow-xs d-inline-flex align-items-center gap-1 me-1 mb-1">
                    <i class="fas fa-user-shield text-emerald" style="font-size:0.75rem;"></i>
                    <span class="fw-semibold small">${p.nama}</span>
                    <button type="button" class="btn-close ms-1" style="font-size:0.5rem;" title="Hapus petugas dari tanggal ini" onclick="removePreviewOfficer(${jIndex}, ${pIndex})"></button>
                </span>
            `;
        });

        tr.innerHTML = `
            <td class="ps-3 fw-bold small text-dark">${j.tanggal_formatted || j.tanggal}</td>
            <td>
                <span class="badge badge-soft-success">${j.shift}</span>
                <div class="small text-muted font-monospace" style="font-size:0.7rem;">${j.jam_mulai} - ${j.jam_selesai}</div>
            </td>
            <td class="small text-muted">${j.lokasi_pos}</td>
            <td id="officerCell_${jIndex}">
                <div class="d-flex flex-wrap align-items-center">
                    ${officersHtml}
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-outline-success btn-sm py-0 px-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size:0.68rem;" title="Tambah petugas">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="max-height: 200px; overflow-y: auto;">
                            ${(data.available_officers || []).map(off => `
                                <li><a class="dropdown-item small" href="javascript:void(0)" onclick="addPreviewOfficer(${jIndex}, ${off.user_profile_id}, '${off.nama.replace(/'/g, "\\'")}', ${off.user_id || 'null'})">+ ${off.nama}</a></li>
                            `).join('')}
                        </ul>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    const pillsContainer = document.getElementById('previewDistribusiPills');
    pillsContainer.innerHTML = '';
    (data.petugas_rekap || []).forEach(item => {
        const pill = document.createElement('span');
        pill.className = 'badge bg-light text-dark border px-3 py-2 d-inline-flex align-items-center gap-2';
        pill.innerHTML = `
            <span class="fw-bold">${item.nama}</span>
            <span class="badge ${item.total_tugas > 0 ? 'bg-emerald text-white' : 'bg-secondary'} rounded-pill">${item.total_tugas}x Tugas</span>
        `;
        pillsContainer.appendChild(pill);
    });
}

function removePreviewOfficer(jIndex, pIndex) {
    if (!currentGeneratedData || !currentGeneratedData.jadwals[jIndex]) return;
    currentGeneratedData.jadwals[jIndex].petugas.splice(pIndex, 1);
    renderGeneratedPreview(currentGeneratedData);
}

function addPreviewOfficer(jIndex, profileId, nama, userId) {
    if (!currentGeneratedData || !currentGeneratedData.jadwals[jIndex]) return;
    const exists = currentGeneratedData.jadwals[jIndex].petugas.some(p => p.user_profile_id === profileId);
    if (exists) {
        alert(nama + ' sudah terdaftar pada shift tanggal ini.');
        return;
    }
    currentGeneratedData.jadwals[jIndex].petugas.push({
        user_profile_id: profileId,
        user_id: userId,
        nama: nama
    });
    renderGeneratedPreview(currentGeneratedData);
}

async function commitAutoSchedule() {
    if (!currentGeneratedData || !currentGeneratedData.jadwals || currentGeneratedData.jadwals.length === 0) {
        alert('Tidak ada jadwal untuk disimpan.');
        return;
    }

    if (!confirm(`Simpan ${currentGeneratedData.jadwals.length} jadwal ronda ini ke dalam database? Notifikasi tugas akan langsung dikirim kepada warga yang telah memiliki akun.`)) {
        return;
    }

    const btn = document.getElementById('btnCommitAutoSchedule');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan ke Database...';
    }

    try {
        const resp = await fetch("{{ route('ronda.simpan-otomatis') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                jadwals: currentGeneratedData.jadwals
            })
        });

        const res = await resp.json();
        if (resp.ok && res.success) {
            alert(res.message || 'Jadwal ronda berhasil disimpan!');
            window.location.reload();
        } else {
            alert(res.message || 'Gagal menyimpan jadwal.');
        }
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan koneksi: ' + e.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}
</script>
@endsection
