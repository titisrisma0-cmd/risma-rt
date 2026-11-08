@extends('layouts.app')

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
<div class="container-fluid px-3 px-lg-4 py-3 ronda-page">
    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="h4 mb-1 fw-bold text-danger">
                <i class="fas fa-shield-halved me-2"></i>Keamanan & Ronda RT @if(isset($myRtId) && $isRt)<span class="badge bg-danger-subtle text-danger border fs-6 ms-1">RT {{ str_pad($myRtId, 2, '0', STR_PAD_LEFT) }}</span>@endif
            </h2>
            <p class="text-muted small mb-0">Jadwal ronda malam, pertukaran jadwal tugas, absensi kehadiran, dan penanganan laporan darurat.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($isPengurus)
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                    <i class="fas fa-calendar-plus me-1"></i>Buat Jadwal Ronda
                </button>
                <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda">
                    <i class="fas fa-user-check me-1"></i>Catat Absensi
                </button>
                <a href="{{ route('layanan.ronda.cctv.live') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-video me-1"></i>Live CCTV</a>
            @else
                <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalTukarJadwal">
                    <i class="fas fa-repeat me-1"></i>Ajukan Tukar Jadwal
                </button>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}</div>
    @endif

    {{-- ALERT PANIC REPORT AKTIF --}}
    @if($activePanics->count() > 0)
        <div class="alert alert-danger d-flex align-items-center gap-3 mb-3 shadow-sm border-2">
            <i class="fas fa-triangle-exclamation fa-2x fa-beat"></i>
            <div class="flex-grow-1">
                <strong class="d-block text-danger fs-6">PERINGATAN DARURAT AKTIF ({{ $activePanics->count() }} Laporan)</strong>
                <span class="small">{{ $activePanics->first()->user->name ?? 'Warga' }}: {{ $activePanics->first()->deskripsi ?: 'Segera koordinasi ke lokasi!' }} ({{ $activePanics->first()->created_at->diffForHumans() }})</span>
            </div>
            @if($isPengurus)
                <form method="POST" action="{{ route('ronda.panic.status', $activePanics->first()) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="sedang_ditangani">
                    <button type="submit" class="btn btn-sm btn-light fw-bold text-danger">Tandai Ditangani</button>
                </form>
            @endif
        </div>
    @endif

    {{-- STATS CARDS --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card border p-2 h-100 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="bg-primary-subtle text-primary p-3 rounded-3"><i class="fas fa-calendar-day fs-4"></i></div>
                <div>
                    <strong class="fs-5 d-block text-primary">{{ $jadwals->count() }} Shift</strong>
                    <span class="text-muted small">Total Jadwal Terdata</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border p-2 h-100 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="bg-success-subtle text-success p-3 rounded-3"><i class="fas fa-user-shield fs-4"></i></div>
                <div>
                    <strong class="fs-5 d-block text-success">{{ $jadwalSaya->count() }} Shift</strong>
                    <span class="text-muted small">Jadwal Tugas Saya</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border p-2 h-100 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="bg-warning-subtle text-warning p-3 rounded-3"><i class="fas fa-repeat fs-4"></i></div>
                <div>
                    <strong class="fs-5 d-block text-warning">{{ $tukarJadwals->where('status', 'menunggu')->count() }} Pengajuan</strong>
                    <span class="text-muted small">Tukar Jadwal Pending</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border p-2 h-100 shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="bg-danger-subtle text-danger p-3 rounded-3"><i class="fas fa-phone-volume fs-4"></i></div>
                <div>
                    <strong class="fs-5 d-block text-danger">{{ $kontaks->count() }} Nomor</strong>
                    <span class="text-muted small">Kontak Tanggap Darurat</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-jadwal" type="button">
                <i class="fas fa-calendar-alt me-1"></i>Jadwal Penugasan Ronda
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tukar" type="button">
                <i class="fas fa-repeat me-1"></i>Pertukaran Jadwal
                @if($tukarJadwals->where('status', 'menunggu')->count() > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $tukarJadwals->where('status', 'menunggu')->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-absensi" type="button">
                <i class="fas fa-chart-simple me-1"></i>Rekap Kehadiran Ronda
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-darurat" type="button">
                <i class="fas fa-bell me-1"></i>Laporan Keamanan & Panic Button
                @if($activePanics->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $activePanics->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kontak" type="button">
                <i class="fas fa-address-book me-1"></i>Kontak Darurat
            </button>
        </li>
    </ul>

    {{-- TABS CONTENT --}}
    <div class="tab-content">
        {{-- TAB 1: JADWAL RONDA --}}
        <div class="tab-pane fade show active" id="tab-jadwal">
            @if(!$isPengurus && $jadwalSaya->count() > 0)
                <div class="card border-primary border-2 mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-user-shield me-2"></i>Jadwal Ronda Saya Bulan Ini</h6>
                        <span class="badge bg-light text-primary">{{ $jadwalSaya->count() }} Giliran Tugas</span>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2">
                            @foreach($jadwalSaya as $myJ)
                                <div class="col-md-6 col-lg-4">
                                    <div class="p-2 border rounded bg-light-subtle">
                                        <div class="fw-bold text-primary">{{ $myJ->tanggal->isoFormat('dddd, D MMMM Y') }}</div>
                                        <small class="text-muted d-block">{{ $myJ->shift }} • {{ $myJ->lokasi_pos }}</small>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary-subtle text-dark border">
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

            <div class="card border shadow-sm">
                <div class="card-header bg-card d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-semibold text-danger"><i class="fas fa-calendar-check me-2"></i>Daftar Jadwal Penugasan Ronda Malam RT</h6>
                    <span class="badge bg-secondary">{{ $jadwals->count() }} Shift Terjadwal</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
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
                                        <div class="fw-bold">{{ $j->tanggal->isoFormat('dddd, D MMMM Y') }}</div>
                                        <small class="text-muted">{{ $j->tanggal->isToday() ? 'Malam Ini' : $j->tanggal->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">{{ ucfirst($j->shift) }}</span>
                                        <div class="small text-muted">{{ $j->lokasi_pos }}</div>
                                    </td>
                                    <td>
                                        @if($j->petugas->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($j->petugas as $p)
                                                    <span class="badge bg-secondary-subtle text-dark border">{{ $p->user->name ?? 'Warga' }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">Belum ada petugas ditentukan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">{{ $j->absensi->where('status', 'hadir')->count() }} Hadir</span>
                                        <span class="badge bg-warning-subtle text-warning text-dark">{{ $j->absensi->where('status', 'izin')->count() }} Izin</span>
                                        <span class="badge bg-danger-subtle text-danger">{{ $j->absensi->where('status', 'alpa')->count() }} Alpa</span>
                                    </td>
                                    @if($isPengurus)
                                        <td class="text-end">
                                            <button class="btn btn-xs btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda" data-jadwal="{{ $j->id }}" title="Catat Absensi"><i class="fas fa-check-double me-1"></i>Absen</button>
                                            <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditJadwalRonda{{ $j->id }}" title="Edit Jadwal"><i class="fas fa-edit"></i></button>
                                            <form method="POST" action="{{ route('ronda.jadwal.destroy', $j) }}" class="d-inline" onsubmit="return confirm('Hapus jadwal ronda shift ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $isPengurus ? 5 : 4 }}" class="text-center py-4 text-muted">Belum ada jadwal ronda aktif di wilayah ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: TUKAR JADWAL --}}
        <div class="tab-pane fade" id="tab-tukar">
            <div class="card border mb-3 shadow-sm">
                <div class="card-header bg-card d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-semibold text-warning"><i class="fas fa-repeat me-2"></i>Daftar Pengajuan Pertukaran Jadwal Ronda</h6>
                    <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalTukarJadwal"><i class="fas fa-plus me-1"></i>Ajukan Tukar Baru</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
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
                                        <div class="fw-bold">{{ $tj->user->name ?? 'Warga' }}</div>
                                        <small class="text-muted">{{ $tj->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $tj->jadwalRondaAsal?->tanggal?->format('d/m/Y') ?? '-' }} ({{ $tj->jadwalRondaAsal?->shift }})</div>
                                        <small class="text-muted">{{ $tj->jadwalRondaAsal?->lokasi_pos }}</small>
                                    </td>
                                    <td>
                                        @if($tj->targetJadwalRonda)
                                            <div>{{ $tj->targetJadwalRonda->tanggal->format('d/m/Y') }} ({{ $tj->targetJadwalRonda->shift }})</div>
                                        @endif
                                        <small class="text-muted">Target: {{ $tj->targetUser->name ?? 'Belum ditentukan' }}</small>
                                    </td>
                                    <td class="small">{{ $tj->alasan }}</td>
                                    <td>
                                        @if($tj->status === 'disetujui')
                                            <span class="badge bg-success">Disetujui</span>
                                        @elseif($tj->status === 'ditolak')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Menunggu Persetujuan RT</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($isPengurus && in_array($tj->status, ['menunggu', 'menunggu_verifikasi_rt']))
                                            <button class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#modalResponTukar{{ $tj->id }}">
                                                <i class="fas fa-gavel me-1"></i>Putuskan RT
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada pengajuan tukar jadwal ronda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 3: REKAP ABSENSI --}}
        <div class="tab-pane fade" id="tab-absensi">
            <div class="card border shadow-sm">
                <div class="card-header bg-card d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-semibold text-info"><i class="fas fa-chart-simple me-2"></i>Rekapitulasi Kehadiran Ronda Warga RT</h6>
                    @if($isPengurus)
                        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda"><i class="fas fa-plus me-1"></i>Input Absensi</button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
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
                                        <div class="fw-semibold">{{ $stat['warga']->name }}</div>
                                        <small class="text-muted">NIK: {{ $stat['warga']->profile?->nik ?? '-' }}</small>
                                    </td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success fs-6">{{ $stat['hadir'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning-subtle text-warning text-dark fs-6">{{ $stat['izin'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-danger-subtle text-danger fs-6">{{ $stat['alpa'] }}</span></td>
                                    <td>
                                        @if($stat['hadir'] >= 3)
                                            <span class="badge bg-success"><i class="fas fa-star me-1"></i>Sangat Aktif</span>
                                        @elseif($stat['alpa'] > $stat['hadir'])
                                            <span class="badge bg-danger">Perlu Ditegur</span>
                                        @else
                                            <span class="badge bg-secondary">Cukup</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada rekap data absensi warga.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: LAPORAN DARURAT & PANIC BUTTON --}}
        <div class="tab-pane fade" id="tab-darurat">
            <div class="card border shadow-sm">
                <div class="card-header bg-card d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-semibold text-danger"><i class="fas fa-bell me-2"></i>Histori Laporan Keamanan & Panic Button Warga</h6>
                    <span class="badge bg-danger">{{ $panicReports->count() }} Laporan Masuk</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
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
                                        <div class="fw-semibold">{{ $pr->created_at->format('d/m/Y H:i') }}</div>
                                        <small class="text-muted">{{ $pr->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $pr->user->name ?? 'Warga' }}</div>
                                        <small class="text-muted">{{ $pr->user->profile?->telepon ?? '-' }}</small>
                                    </td>
                                    <td>{{ $pr->lokasi ?: 'Wilayah RT' }}</td>
                                    <td>{{ $pr->deskripsi ?: 'Laporan bahaya / darurat' }}</td>
                                    <td>
                                        @if($pr->status === 'aktif')
                                            <span class="badge bg-danger fa-beat">🚨 Aktif</span>
                                        @elseif($pr->status === 'sedang_ditangani')
                                            <span class="badge bg-warning text-dark">Sedang Ditangani</span>
                                        @else
                                            <span class="badge bg-success">Selesai Ditangani</span>
                                        @endif
                                    </td>
                                    @if($isPengurus)
                                        <td class="text-end">
                                            @if($pr->status !== 'selesai')
                                                <form method="POST" action="{{ route('ronda.panic.status', $pr) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if($pr->status === 'aktif')
                                                        <button type="submit" name="status" value="sedang_ditangani" class="btn btn-xs btn-warning text-dark">
                                                            <i class="fas fa-person-running me-1"></i>Tangani
                                                        </button>
                                                    @endif
                                                    <button type="submit" name="status" value="selesai" class="btn btn-xs btn-success" onclick="return confirm('Tandai laporan darurat ini selesai dan aman?');">
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
                                <tr><td colspan="{{ $isPengurus ? 6 : 5 }}" class="text-center py-4 text-muted">Tidak ada laporan darurat keamanan di wilayah RT.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 5: KONTAK DARURAT --}}
        <div class="tab-pane fade" id="tab-kontak">
            <div class="row g-2">
                @foreach($kontaks as $kontak)
                    <div class="col-sm-6 col-lg-4">
                        <div class="card border p-3 h-100 shadow-sm d-flex flex-row align-items-center gap-3">
                            <div class="bg-danger-subtle text-danger p-3 rounded-circle"><i class="fas fa-phone fs-4"></i></div>
                            <div>
                                <strong class="d-block">{{ $kontak->kategori }}</strong>
                                <span class="text-muted small">{{ $kontak->nama }}</span>
                                <div class="mt-1">
                                    <a href="tel:{{ $kontak->nomor_telepon }}" class="btn btn-xs btn-outline-danger"><i class="fas fa-phone-alt me-1"></i>{{ $kontak->nomor_telepon }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- MODALS SECTION --}}
    @if($isPengurus)
        {{-- MODAL BUAT JADWAL RONDA --}}
        <div class="modal fade" id="modalTambahJadwal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('ronda.jadwal.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-danger"><i class="fas fa-calendar-plus me-2"></i>Buat Jadwal Penugasan Ronda</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Mode Penjadwalan</label>
                                    <select class="form-select" name="mode" id="modeJadwalSelect">
                                        <option value="manual">Manual (Pilih Warga Sendiri)</option>
                                        <option value="acak">Acak Otomatis (Roda Giliran)</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-2 mb-3" id="acakParams" style="display: none;">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Jumlah Hari Berturut</label>
                                    <input type="number" name="jumlah_hari" value="7" min="1" max="31" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Petugas per Shift</label>
                                    <input type="number" name="jumlah_petugas" value="3" min="1" max="10" class="form-control">
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Shift Penugasan <span class="text-danger">*</span></label>
                                    <input type="text" name="shift" value="Malam" class="form-control" required placeholder="Contoh: Malam / Regu A">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold">Lokasi Pos Titik Kumpul</label>
                                    <input type="text" name="lokasi_pos" value="Pos Ronda RT" class="form-control" placeholder="Pos Ronda RT">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Pilih Petugas Warga (Bisa Pilih Banyak)</label>
                                <select name="petugas[]" class="form-select" multiple size="5">
                                    @foreach($wargas as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Tahan Ctrl/Command untuk memilih lebih dari 1 warga.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-save me-1"></i>Simpan & Kirim Notifikasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL INPUT ABSENSI RONDA --}}
        <div class="modal fade" id="modalAbsensiRonda" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('ronda.absensi.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-info"><i class="fas fa-user-check me-2"></i>Pencatatan Absensi Ronda</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Jadwal Shift <span class="text-danger">*</span></label>
                                <select name="jadwal_ronda_id" class="form-select" required>
                                    @foreach($jadwals->take(15) as $j)
                                        <option value="{{ $j->id }}">{{ $j->tanggal->format('d/m/Y') }} - {{ $j->shift }} ({{ $j->lokasi_pos }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Petugas Warga <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    @foreach($wargas as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Status Kehadiran <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="hadir">Hadir Bertugas</option>
                                    <option value="izin">Izin (Dengan Pemberitahuan)</option>
                                    <option value="alpa">Alpa (Tanpa Keterangan)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Catatan</label>
                                <input type="text" name="catatan" class="form-control" placeholder="Contoh: Hadir tepat waktu jam 22.00 WIB">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-info text-white"><i class="fas fa-check me-1"></i>Simpan Kehadiran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT JADWAL RONDA --}}
        @foreach($jadwals as $jEdit)
            <div class="modal fade" id="modalEditJadwalRonda{{ $jEdit->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('ronda.jadwal.update', $jEdit) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-edit me-2"></i>Edit Jadwal Ronda</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Tanggal Tugas <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" value="{{ $jEdit->tanggal->format('Y-m-d') }}" class="form-control" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold">Shift <span class="text-danger">*</span></label>
                                        <input type="text" name="shift" value="{{ $jEdit->shift }}" class="form-control" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="aktif" {{ $jEdit->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="selesai" {{ $jEdit->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="dibatalkan" {{ $jEdit->status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Lokasi Pos Titik Kumpul</label>
                                    <input type="text" name="lokasi_pos" value="{{ $jEdit->lokasi_pos }}" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Petugas yang Ditugaskan</label>
                                    @php
                                        $assignedUserIds = $jEdit->petugas->pluck('user_id')->toArray();
                                    @endphp
                                    <select name="petugas[]" class="form-select" multiple size="4">
                                        @foreach($wargas as $w)
                                            <option value="{{ $w->id }}" {{ in_array($w->id, $assignedUserIds) ? 'selected' : '' }}>{{ $w->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Tahan Ctrl/Command untuk memilih lebih dari 1 warga.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
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
                    <div class="modal-content">
                        <form method="POST" action="{{ route('ronda.tukar.respon', $tjResp) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold text-warning"><i class="fas fa-gavel me-2"></i>Persetujuan Pertukaran Jadwal Ronda</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start">
                                <p class="small text-muted mb-2">
                                    Warga <strong>{{ $tjResp->user->name ?? 'Pemohon' }}</strong> mengajukan tukar jadwal shift <strong>{{ $tjResp->jadwalRondaAsal?->tanggal?->format('d/m/Y') }} ({{ $tjResp->jadwalRondaAsal?->shift }})</strong>.
                                    Alasan: <em>"{{ $tjResp->alasan }}"</em>.
                                </p>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Keputusan Ketua RT <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="disetujui">Setujui Pertukaran Jadwal</option>
                                        <option value="ditolak">Tolak Pertukaran Jadwal</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Catatan Pengurus</label>
                                    <textarea name="catatan_rt" class="form-control" rows="2" placeholder="Catatan persetujuan / alasan jika ditolak..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm btn-primary">Simpan Keputusan</button>
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
            <div class="modal-content">
                <form method="POST" action="{{ route('ronda.tukar.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-warning"><i class="fas fa-repeat me-2"></i>Pengajuan Tukar Jadwal Ronda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pilih Jadwal Tugas Saya <span class="text-danger">*</span></label>
                            <select name="jadwal_ronda_asal_id" class="form-select" required>
                                <option value="">-- Pilih Jadwal Saya --</option>
                                @foreach($jadwalSaya as $myJ)
                                    <option value="{{ $myJ->id }}">{{ $myJ->tanggal->format('d/m/Y') }} ({{ $myJ->shift }}) - {{ $myJ->lokasi_pos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pilih Warga Pengganti / Rekan Bertukar</label>
                            <select name="target_user_id" class="form-select">
                                <option value="">-- Bebas (Ditentukan Pengurus RT) --</option>
                                @foreach($wargas->where('id', '!=', Auth::id()) as $tw)
                                    <option value="{{ $tw->id }}">{{ $tw->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pilih Jadwal Pengganti (Opsional)</label>
                            <select name="target_jadwal_ronda_id" class="form-select">
                                <option value="">-- Pilih Jadwal Lain --</option>
                                @foreach($jadwals as $allJ)
                                    <option value="{{ $allJ->id }}">{{ $allJ->tanggal->format('d/m/Y') }} ({{ $allJ->shift }}) - {{ $allJ->lokasi_pos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Alasan Penukaran Jadwal <span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control" rows="2" placeholder="Contoh: Ada tugas luar kota / urusan keluarga mendadak" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-warning text-dark"><i class="fas fa-paper-plane me-1"></i>Kirim Pengajuan</button>
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

<style>
.btn-xs {
    padding: 0.18rem 0.45rem;
    font-size: 0.75rem;
}
</style>
@endsection
