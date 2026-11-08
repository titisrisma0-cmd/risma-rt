@extends('layouts.app')

@section('title', 'Keamanan & Ronda RT')

@php
    $absensiStats = $wargas->map(function ($warga) use ($jadwals) {
        $absensi = $jadwals->flatMap(fn ($jadwal) => $jadwal->absensi)->where('user_id', $warga->id);

        return [
            'warga' => $warga,
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'alpa' => $absensi->where('status', 'alpa')->count(),
        ];
    })->sortByDesc('hadir')->values();

    $jadwalSaya = $jadwals->filter(fn ($j) => $j->petugas->contains('user_id', Auth::id()))->values();
    $activePanics = $panicReports->where('status', 'aktif');
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
                <i class="fas fa-shield-halved text-success me-2"></i>Keamanan & Ronda RT
                @if(isset($myRtId) && $isRt)
                    <span class="badge badge-soft-success fs-6 ms-1">RT {{ str_pad($myRtId, 2, '0', STR_PAD_LEFT) }}</span>
                @endif
            </h1>
            <p class="page-subtitle">Jadwal ronda malam, pertukaran giliran tugas, rekap absensi kehadiran, dan sistem tanggap darurat warga.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($isPengurus)
                <button class="btn btn-saas-primary" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                    <i class="fas fa-calendar-plus me-1"></i>Buat Jadwal Ronda
                </button>
                <button class="btn btn-outline-saas" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda">
                    <i class="fas fa-user-check me-1"></i>Catat Absensi
                </button>
                <a href="{{ route('layanan.ronda.cctv.live') }}" class="btn btn-outline-saas text-danger border-danger">
                    <i class="fas fa-video me-1"></i>Live CCTV
                </a>
            @else
                <button class="btn btn-outline-saas text-warning border-warning" data-bs-toggle="modal" data-bs-target="#modalTukarJadwal">
                    <i class="fas fa-repeat me-1"></i>Ajukan Tukar Jadwal
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
                    <h5 class="text-danger fw-bold mb-0">PERINGATAN DARURAT AKTIF ({{ $activePanics->count() }} Laporan)</h5>
                    <p class="text-muted small mb-0">
                        {{ $activePanics->first()->user->name ?? 'Warga' }}: {{ $activePanics->first()->deskripsi ?: 'Segera koordinasi ke lokasi!' }} ({{ $activePanics->first()->created_at->diffForHumans() }})
                    </p>
                </div>
            </div>
            @if($isPengurus)
                <form method="POST" action="{{ route('ronda.panic.status', $activePanics->first()) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="sedang_ditangani">
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold shadow-sm">
                        <i class="fas fa-person-running me-1"></i>Tandai Ditangani
                    </button>
                </form>
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
                    <span class="stat-desc">Shift terdaftar di sistem</span>
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
                    <span class="stat-desc">Giliran penugasan pribadi</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-warning-soft text-warning">
                    <i class="fas fa-repeat"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Pengajuan Tukar</span>
                    <h3 class="stat-value">{{ $tukarJadwals->where('status', 'menunggu')->count() }}</h3>
                    <span class="stat-desc">Menunggu persetujuan RT</span>
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
                    <span class="stat-desc">Nomor tanggap darurat</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <div class="saas-card mb-4 p-2">
        <ul class="nav nav-tabs-saas" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-jadwal" type="button">
                    <i class="fas fa-calendar-alt me-1"></i>Jadwal Penugasan
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tukar" type="button">
                    <i class="fas fa-repeat me-1"></i>Pertukaran Jadwal
                    @if($tukarJadwals->where('status', 'menunggu')->count() > 0)
                        <span class="badge badge-soft-warning ms-1">{{ $tukarJadwals->where('status', 'menunggu')->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-absensi" type="button">
                    <i class="fas fa-chart-simple me-1"></i>Rekap Kehadiran
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
        {{-- TAB 1: JADWAL RONDA --}}
        <div class="tab-pane fade show active" id="tab-jadwal">
            @if(!$isPengurus && $jadwalSaya->count() > 0)
                <div class="saas-card mb-4 border border-primary border-opacity-25">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-user-shield me-2"></i>Jadwal Ronda Saya Bulan Ini
                        </h6>
                        <span class="badge badge-soft-primary">{{ $jadwalSaya->count() }} Giliran Tugas</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach($jadwalSaya as $myJ)
                                <div class="col-md-6 col-lg-4">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <div class="fw-bold text-dark">{{ $myJ->tanggal->isoFormat('dddd, D MMMM Y') }}</div>
                                        <small class="text-secondary d-block mt-1">
                                            <i class="fas fa-clock text-muted me-1"></i>{{ $myJ->shift }} • <i class="fas fa-location-dot text-muted ms-1 me-1"></i>{{ $myJ->lokasi_pos }}
                                        </small>
                                        <div class="mt-2 pt-2 border-top">
                                            <span class="badge badge-soft-secondary">
                                                Rekan: {{ $myJ->petugas->where('user_id', '!=', Auth::id())->pluck('user.name')->join(', ') ?: 'Bertugas Tunggal' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="saas-card">
                <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-calendar-check text-success me-2"></i>Daftar Jadwal Penugasan Ronda Malam RT
                        </h6>
                    </div>
                    <span class="badge badge-soft-secondary">{{ $jadwals->count() }} Shift Terjadwal</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-saas align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Shift & Pos</th>
                                <th>Petugas Ronda Warga</th>
                                <th>Status Absensi</th>
                                @if($isPengurus)
                                    <th class="text-end">Aksi Pengurus</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwals as $j)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $j->tanggal->isoFormat('dddd, D MMMM Y') }}</div>
                                        <small class="text-muted">{{ $j->tanggal->isToday() ? 'Malam Ini' : $j->tanggal->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-info">{{ ucfirst($j->shift) }}</span>
                                        <div class="small text-muted mt-1"><i class="fas fa-location-dot me-1"></i>{{ $j->lokasi_pos }}</div>
                                    </td>
                                    <td>
                                        @if($j->petugas->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($j->petugas as $p)
                                                    <span class="badge badge-soft-secondary">{{ $p->user->name ?? 'Warga' }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">Belum ada petugas ditentukan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="badge badge-soft-success">{{ $j->absensi->where('status', 'hadir')->count() }} Hadir</span>
                                            <span class="badge badge-soft-warning">{{ $j->absensi->where('status', 'izin')->count() }} Izin</span>
                                            <span class="badge badge-soft-danger">{{ $j->absensi->where('status', 'alpa')->count() }} Alpa</span>
                                        </div>
                                    </td>
                                    @if($isPengurus)
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-saas btn-sm text-info" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda" data-jadwal="{{ $j->id }}" title="Catat Absensi">
                                                    <i class="fas fa-check-double me-1"></i>Absen
                                                </button>
                                                <button class="btn btn-outline-saas btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditJadwalRonda{{ $j->id }}" title="Edit Jadwal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('ronda.jadwal.destroy', $j) }}" class="d-inline" onsubmit="return confirm('Hapus jadwal ronda shift ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-saas btn-sm text-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isPengurus ? 5 : 4 }}" class="text-center py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-shield-halved"></i>
                                            </div>
                                            <h6 class="empty-state-title">Belum Ada Jadwal Ronda</h6>
                                            <p class="empty-state-desc">Belum ada jadwal ronda aktif di wilayah ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: TUKAR JADWAL --}}
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
                                <th>Jadwal / Warga Pengganti</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
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
                                        <small class="text-muted">Target: {{ $tj->targetUser->name ?? 'Belum ditentukan' }}</small>
                                    </td>
                                    <td class="small text-muted">{{ $tj->alasan }}</td>
                                    <td>
                                        @if($tj->status === 'disetujui')
                                            <span class="badge badge-soft-success">Disetujui</span>
                                        @elseif($tj->status === 'ditolak')
                                            <span class="badge badge-soft-danger">Ditolak</span>
                                        @else
                                            <span class="badge badge-soft-warning">Menunggu Persetujuan RT</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($isPengurus && in_array($tj->status, ['menunggu', 'menunggu_verifikasi_rt']))
                                            <button class="btn btn-outline-saas btn-sm text-success" data-bs-toggle="modal" data-bs-target="#modalResponTukar{{ $tj->id }}">
                                                <i class="fas fa-gavel me-1"></i>Putuskan RT
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-repeat"></i>
                                            </div>
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

        {{-- TAB 3: REKAP ABSENSI --}}
        <div class="tab-pane fade" id="tab-absensi">
            <div class="saas-card mb-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-simple text-info me-2"></i>Rekapitulasi Kehadiran Ronda Warga RT
                    </h6>
                    @if($isPengurus)
                        <button class="btn btn-outline-saas btn-sm text-info border-info" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda">
                            <i class="fas fa-plus me-1"></i>Input Absensi
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
                                            <span class="badge badge-soft-danger">Perlu Ditegur</span>
                                        @else
                                            <span class="badge badge-soft-secondary">Cukup</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-chart-simple"></i>
                                            </div>
                                            <h6 class="empty-state-title">Belum Ada Rekap Absensi</h6>
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

        {{-- TAB 4: LAPORAN DARURAT & PANIC BUTTON --}}
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
                                <th>Waktu</th>
                                <th>Pelapor</th>
                                <th>Lokasi Kejadian</th>
                                <th>Keterangan / Laporan</th>
                                <th>Status</th>
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
                                        <small class="text-muted">{{ $pr->user->profile?->telepon ?? '-' }}</small>
                                    </td>
                                    <td>{{ $pr->lokasi ?: 'Wilayah RT' }}</td>
                                    <td>{{ $pr->deskripsi ?: 'Laporan bahaya / darurat' }}</td>
                                    <td>
                                        @if($pr->status === 'aktif')
                                            <span class="badge badge-soft-danger fa-beat">🚨 Aktif</span>
                                        @elseif($pr->status === 'sedang_ditangani')
                                            <span class="badge badge-soft-warning">Sedang Ditangani</span>
                                        @else
                                            <span class="badge badge-soft-success">Selesai Ditangani</span>
                                        @endif
                                    </td>
                                    @if($isPengurus)
                                        <td class="text-end">
                                            @if($pr->status !== 'selesai')
                                                <form method="POST" action="{{ route('ronda.panic.status', $pr) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if($pr->status === 'aktif')
                                                        <button type="submit" name="status" value="sedang_ditangani" class="btn btn-outline-saas btn-sm text-warning border-warning">
                                                            <i class="fas fa-person-running me-1"></i>Tangani
                                                        </button>
                                                    @endif
                                                    <button type="submit" name="status" value="selesai" class="btn btn-saas-primary btn-sm" onclick="return confirm('Tandai laporan darurat ini selesai dan aman?');">
                                                        <i class="fas fa-check me-1"></i>Selesai
                                                    </button>
                                                </form>
                                            @else
                                                <span class="small text-muted"><i class="fas fa-circle-check text-success me-1"></i>Ditutup ({{ $pr->handler?->name ?? 'RT' }})</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isPengurus ? 6 : 5 }}" class="text-center py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-bell"></i>
                                            </div>
                                            <h6 class="empty-state-title">Tidak Ada Laporan Darurat</h6>
                                            <p class="empty-state-desc">Kondisi lingkungan aman dan kondusif.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 5: KONTAK DARURAT --}}
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
    @if($isPengurus)
        {{-- MODAL BUAT JADWAL RONDA --}}
        <div class="modal fade" id="modalTambahJadwal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('ronda.jadwal.store') }}">
                        @csrf
                        <div class="modal-header border-bottom py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="fas fa-calendar-plus text-success me-2"></i>Buat Jadwal Penugasan Ronda
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start p-4">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label-saas">Mode Penjadwalan</label>
                                    <select class="form-select-saas" name="mode" id="modeJadwalSelect">
                                        <option value="manual">Manual (Pilih Warga Sendiri)</option>
                                        <option value="acak">Acak Otomatis (Roda Giliran)</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label-saas">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="form-control-saas" required>
                                </div>
                            </div>
                            <div class="row g-3 mb-3" id="acakParams" style="display: none;">
                                <div class="col-6">
                                    <label class="form-label-saas">Jumlah Hari Berturut</label>
                                    <input type="number" name="jumlah_hari" value="7" min="1" max="31" class="form-control-saas">
                                </div>
                                <div class="col-6">
                                    <label class="form-label-saas">Petugas per Shift</label>
                                    <input type="number" name="jumlah_petugas" value="3" min="1" max="10" class="form-control-saas">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label-saas">Shift Penugasan <span class="text-danger">*</span></label>
                                    <input type="text" name="shift" value="Malam" class="form-control-saas" required placeholder="Contoh: Malam / Regu A">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label-saas">Lokasi Pos Titik Kumpul</label>
                                    <input type="text" name="lokasi_pos" value="Pos Ronda RT" class="form-control-saas" placeholder="Pos Ronda RT">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label-saas">Pilih Petugas Warga (Bisa Pilih Banyak)</label>
                                <select name="petugas[]" class="form-select-saas" multiple size="5">
                                    @foreach($wargas as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted mt-1 d-block">Tahan Ctrl/Command untuk memilih lebih dari 1 warga.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 px-4">
                            <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-saas-primary"><i class="fas fa-save me-1"></i>Simpan & Kirim Notifikasi</button>
                        </div>
                    </form>
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
                                <i class="fas fa-user-check text-info me-2"></i>Pencatatan Absensi Ronda
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
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
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
                            <button type="submit" class="btn btn-saas-primary"><i class="fas fa-check me-1"></i>Simpan Kehadiran</button>
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
                                        $assignedUserIds = $jEdit->petugas->pluck('user_id')->toArray();
                                    @endphp
                                    <select name="petugas[]" class="form-select-saas" multiple size="4">
                                        @foreach($wargas as $w)
                                            <option value="{{ $w->id }}" {{ in_array($w->id, $assignedUserIds) ? 'selected' : '' }}>{{ $w->name }}</option>
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
                                    <i class="fas fa-gavel text-warning me-2"></i>Persetujuan Pertukaran Jadwal Ronda
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start p-4">
                                <div class="alert alert-saas alert-saas-warning mb-3">
                                    Warga <strong>{{ $tjResp->user->name ?? 'Pemohon' }}</strong> mengajukan tukar jadwal shift <strong>{{ $tjResp->jadwalRondaAsal?->tanggal?->format('d/m/Y') }} ({{ $tjResp->jadwalRondaAsal?->shift }})</strong>.
                                    <br>Alasan: <em>"{{ $tjResp->alasan }}"</em>.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-saas">Keputusan Ketua RT <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select-saas" required>
                                        <option value="disetujui">Setujui Pertukaran Jadwal</option>
                                        <option value="ditolak">Tolak Pertukaran Jadwal</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label-saas">Catatan Pengurus</label>
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

    {{-- MODAL AJUKAN TUKAR JADWAL (UNTUK WARGA) --}}
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
                            <label class="form-label-saas">Pilih Jadwal Tugas Saya <span class="text-danger">*</span></label>
                            <select name="jadwal_ronda_asal_id" class="form-select-saas" required>
                                <option value="">-- Pilih Jadwal Saya --</option>
                                @foreach($jadwalSaya as $myJ)
                                    <option value="{{ $myJ->id }}">{{ $myJ->tanggal->format('d/m/Y') }} ({{ $myJ->shift }}) - {{ $myJ->lokasi_pos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-saas">Pilih Warga Pengganti / Rekan Bertukar</label>
                            <select name="target_user_id" class="form-select-saas">
                                <option value="">-- Bebas (Ditentukan Pengurus RT) --</option>
                                @foreach($wargas->where('id', '!=', Auth::id()) as $tw)
                                    <option value="{{ $tw->id }}">{{ $tw->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-saas">Pilih Jadwal Pengganti (Opsional)</label>
                            <select name="target_jadwal_ronda_id" class="form-select-saas">
                                <option value="">-- Pilih Jadwal Lain --</option>
                                @foreach($jadwals as $allJ)
                                    <option value="{{ $allJ->id }}">{{ $allJ->tanggal->format('d/m/Y') }} ({{ $allJ->shift }}) - {{ $allJ->lokasi_pos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label-saas">Alasan Penukaran Jadwal <span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control-saas" rows="2" placeholder="Contoh: Ada tugas luar kota / urusan keluarga mendadak" required></textarea>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeSelect = document.getElementById('modeJadwalSelect');
    const acakParams = document.getElementById('acakParams');
    if (modeSelect && acakParams) {
        modeSelect.addEventListener('change', function () {
            acakParams.style.display = this.value === 'acak' ? 'flex' : 'none';
        });
    }
});
</script>
@endsection
