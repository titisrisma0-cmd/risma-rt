@extends('layouts.app')

@section('title', 'Detail RT ' . $rt->kode_rt . ' — Manajemen RT')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header & Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('rts.index') }}" class="text-emerald-400">Manajemen RT</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">RT {{ $rt->kode_rt }}</li>
                </ol>
            </nav>
            <h1 class="h3 text-white mb-0">
                <i class="fas fa-landmark text-emerald-400 me-2"></i>Wilayah RT {{ $rt->kode_rt }} — RW {{ $rt->rw->kode_rw ?? '01' }}
            </h1>
            <p class="text-white-50 mb-0 small">Pemantauan operasional, kependudukan, keuangan, kegiatan, dan layanan terpadu</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rts.index') }}" class="btn btn-outline-light px-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar RT
            </a>
            <a href="{{ route('rw.audit.index') }}" class="btn btn-emerald text-white px-3" style="background: #10b981; border: none;">
                <i class="fas fa-clipboard-check me-1"></i> Keuangan & Audit
            </a>
        </div>
    </div>

    {{-- Kartu Statistik Ringkas --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Ketua RT</span>
                <h5 class="text-white fw-bold mb-0 text-truncate">{{ optional($rt->ketua)->name ?? 'Belum Ditentukan' }}</h5>
                <span class="small text-emerald-400">{{ optional($rt->ketua->profile)->no_hp ?? '-' }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Kependudukan</span>
                <h5 class="text-info fw-bold mb-0">{{ $totalKk }} KK / {{ $totalWarga }} Jiwa</h5>
                <span class="small text-white-50">{{ $totalLaki }} L &bull; {{ $totalPerempuan }} P &bull; {{ $totalBelumDiketahui }} Belum Diketahui</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Saldo Kas RT</span>
                <h5 class="text-emerald-400 fw-bold mb-0 font-monospace">Rp {{ number_format($saldoKas, 0, ',', '.') }}</h5>
                <span class="small text-white-50">Masuk: Rp {{ number_format($pemasukanKas, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Status Audit Bulan Ini</span>
                <div class="mt-1">
                    @if($auditBulanIni)
                        <span class="badge {{ $auditBulanIni->status_badge }} px-2 py-1">
                            {{ $auditBulanIni->status_label }}
                        </span>
                    @else
                        <span class="badge bg-secondary px-2 py-1">Belum Diperiksa</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-pills mb-4 p-2 rounded" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1);" id="rtTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active text-white" id="profil-tab" data-bs-toggle="pill" data-bs-target="#tab-profil" type="button" role="tab">
                <i class="fas fa-id-card me-1 text-warning"></i> 1. Profil RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="warga-tab" data-bs-toggle="pill" data-bs-target="#tab-warga" type="button" role="tab">
                <i class="fas fa-users me-1 text-info"></i> 2. Data KK & Warga
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="keuangan-tab" data-bs-toggle="pill" data-bs-target="#tab-keuangan" type="button" role="tab">
                <i class="fas fa-wallet me-1 text-success"></i> 3. Keuangan RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="kegiatan-tab" data-bs-toggle="pill" data-bs-target="#tab-kegiatan" type="button" role="tab">
                <i class="fas fa-calendar-check me-1 text-primary"></i> 4. Kegiatan RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="layanan-tab" data-bs-toggle="pill" data-bs-target="#tab-layanan" type="button" role="tab">
                <i class="fas fa-handshake-angle me-1 text-warning"></i> 5. Layanan RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="aktivitas-tab" data-bs-toggle="pill" data-bs-target="#tab-aktivitas" type="button" role="tab">
                <i class="fas fa-history me-1 text-danger"></i> 6. Riwayat Aktivitas
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="rtTabContent">
        {{-- ==================== TAB 1: PROFIL RT ==================== --}}
        <div class="tab-pane fade show active" id="tab-profil" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                            <h5 class="text-white mb-0"><i class="fas fa-map-marker-alt text-warning me-2"></i>Informasi Wilayah RT</h5>
                        </div>
                        <div class="card-body text-white">
                            <table class="table table-dark table-borderless mb-0">
                                <tr>
                                    <td class="text-white-50" style="width: 35%;">Kode Wilayah</td>
                                    <td class="fw-bold">RT {{ $rt->kode_rt }} / RW {{ $rt->rw->kode_rw ?? '01' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-white-50">Nama RT</td>
                                    <td>{{ $rt->nama_rt ?: 'RT ' . $rt->kode_rt }}</td>
                                </tr>
                                <tr>
                                    <td class="text-white-50">Kecamatan / Kelurahan</td>
                                    <td>{{ config('app.kelurahan', 'Kelurahan Setempat') }} / {{ config('app.kecamatan', 'Kecamatan Setempat') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-white-50">Terdaftar Sejak</td>
                                    <td>{{ $rt->created_at ? $rt->created_at->translatedFormat('d F Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-white-50">Status Wilayah</td>
                                    <td><span class="badge bg-success">Aktif Beroperasi</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                            <h5 class="text-white mb-0"><i class="fas fa-user-shield text-info me-2"></i>Profil Ketua RT</h5>
                        </div>
                        <div class="card-body text-white">
                            @if($rt->ketua)
                                <table class="table table-dark table-borderless mb-0">
                                    <tr>
                                        <td class="text-white-50" style="width: 35%;">Nama Lengkap</td>
                                        <td class="fw-bold text-emerald-400">{{ $rt->ketua->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white-50">Email Akun</td>
                                        <td class="font-monospace">{{ $rt->ketua->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white-50">Nomor WhatsApp / HP</td>
                                        <td>{{ optional($rt->ketua->profile)->no_hp ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white-50">Role Akses</td>
                                        <td><span class="badge bg-primary">{{ $rt->ketua->role->nama_role ?? 'rt' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-white-50">Status Akun</td>
                                        <td>
                                            @php $st = optional($rt->ketua->profile)->status ?? 'aktif'; @endphp
                                            <span class="badge {{ $st === 'aktif' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($st) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-user-slash fa-3x text-secondary mb-3"></i>
                                    <p class="text-white-50">Belum ada akun Ketua RT yang ditautkan ke wilayah ini.</p>
                                    <a href="{{ route('rts.index') }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-user-plus me-1"></i> Buat Akun Ketua RT
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 2: DATA KK & WARGA ==================== --}}
        <div class="tab-pane fade" id="tab-warga" role="tabpanel">
            {{-- Statistik Kependudukan Banner --}}
            <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-venus-mars text-warning"></i>
                        <span class="text-white fw-bold">Statistik Kependudukan RT {{ $rt->kode_rt }}:</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 text-white small">
                        <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-id-card me-1"></i> Total KK: <strong>{{ $totalKk }}</strong></span>
                        <span class="badge bg-primary px-3 py-2"><i class="fas fa-users me-1"></i> Total Jiwa: <strong>{{ $totalWarga }}</strong></span>
                        <span class="badge bg-success px-3 py-2"><i class="fas fa-male me-1"></i> Laki-laki: <strong>{{ $totalLaki }}</strong></span>
                        <span class="badge bg-danger px-3 py-2"><i class="fas fa-female me-1"></i> Perempuan: <strong>{{ $totalPerempuan }}</strong></span>
                        <span class="badge bg-secondary px-3 py-2"><i class="fas fa-question-circle me-1"></i> Belum Diketahui: <strong>{{ $totalBelumDiketahui }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Daftar Kepala Keluarga --}}
                <div class="col-lg-5">
                    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                            <h5 class="text-white mb-0"><i class="fas fa-house-user text-info me-2"></i>Kartu Keluarga ({{ $totalKk }})</h5>
                        </div>
                        <div class="table-responsive" style="max-height: 480px;">
                            <table class="table table-hover align-middle mb-0 text-white">
                                <thead style="background: rgba(15, 23, 42, 0.6);">
                                    <tr class="text-secondary small">
                                        <th>No. KK</th>
                                        <th>Kepala Keluarga</th>
                                        <th class="text-center">Anggota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rt->kartuKeluargas as $kk)
                                        <tr>
                                            <td class="font-monospace small text-emerald-400">{{ $kk->nomor_kk }}</td>
                                            <td>{{ optional($kk->kepalaKeluarga)->name ?? '-' }}</td>
                                            <td class="text-center"><span class="badge bg-secondary">{{ $kk->anggota->count() }} Jiwa</span></td>
                                        </tr>
                                    @empty
                                        @php
                                            $uniqueKk = $rt->wargaProfiles->whereNotNull('no_kk')->groupBy('no_kk');
                                        @endphp
                                        @forelse($uniqueKk as $noKk => $anggota)
                                            <tr>
                                                <td class="font-monospace small text-emerald-400">{{ $noKk }}</td>
                                                <td>{{ $anggota->first()->nama_lengkap ?? $anggota->first()->user?->name ?? '-' }}</td>
                                                <td class="text-center"><span class="badge bg-secondary">{{ $anggota->count() }} Jiwa</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-3 text-secondary">Belum ada Kartu Keluarga terdaftar.</td>
                                            </tr>
                                        @endforelse
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Daftar Warga --}}
                <div class="col-lg-7">
                    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
                            <h5 class="text-white mb-0"><i class="fas fa-users text-warning me-2"></i>Daftar Warga ({{ $totalWarga }} Jiwa)</h5>
                            <a href="{{ route('warga.index', ['rt_id' => $rt->id]) }}" class="btn btn-sm btn-outline-info text-white">
                                <i class="fas fa-external-link-alt me-1"></i> Buka Modul Warga
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 480px;">
                            <table class="table table-hover align-middle mb-0 text-white">
                                <thead style="background: rgba(15, 23, 42, 0.6);">
                                    <tr class="text-secondary small">
                                        <th>Nama Lengkap</th>
                                        <th>NIK</th>
                                        <th>L/P</th>
                                        <th>No. HP</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rt->wargaProfiles->take(25) as $wp)
                                        <tr>
                                            <td class="fw-semibold text-white">{{ $wp->nama_lengkap ?: optional($wp->user)->name }}</td>
                                            <td class="font-monospace small text-white-50">{{ $wp->nik ?: '-' }}</td>
                                            <td>
                                                @php
                                                    $jkVal = strtoupper(trim((string)($wp->jenis_kelamin ?: optional($wp->user)->jenis_kelamin)));
                                                @endphp
                                                @if($jkVal === 'L' || $jkVal === 'LAKI-LAKI')
                                                    <span class="badge bg-primary">L</span>
                                                @elseif($jkVal === 'P' || $jkVal === 'PEREMPUAN')
                                                    <span class="badge bg-danger">P</span>
                                                @else
                                                    <span class="badge bg-secondary" title="Belum Diketahui">-</span>
                                                @endif
                                            </td>
                                            <td class="small">{{ $wp->no_hp ?: '-' }}</td>
                                            <td>
                                                <span class="badge {{ ($wp->status ?? 'aktif') === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ucfirst($wp->status ?? 'aktif') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-secondary">Belum ada warga terdaftar di RT ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 3: KEUANGAN RT ==================== --}}
        <div class="tab-pane fade" id="tab-keuangan" role="tabpanel">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <span class="text-white-50 small mb-1">Total Pemasukan Kas</span>
                        <h4 class="text-emerald-400 fw-bold font-monospace mb-0">Rp {{ number_format($pemasukanKas, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <span class="text-white-50 small mb-1">Total Pengeluaran Kas</span>
                        <h4 class="text-danger fw-bold font-monospace mb-0">Rp {{ number_format($pengeluaranKas, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <span class="text-white-50 small mb-1">Saldo Kas RT Saat Ini</span>
                        <h4 class="text-white fw-bold font-monospace mb-0">Rp {{ number_format($saldoKas, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>

            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-exchange-alt text-emerald-400 me-2"></i>Transaksi Kas Terbaru RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Kategori / Deskripsi</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentKas as $k)
                                <tr>
                                    <td class="text-white-50 font-monospace small">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $k->jenis === 'masuk' ? 'bg-success' : 'bg-danger' }}">
                                            {{ strtoupper($k->jenis) }}
                                        </span>
                                    </td>
                                    <td>{{ $k->keterangan }}</td>
                                    <td class="text-end font-monospace fw-bold {{ $k->jenis === 'masuk' ? 'text-emerald-400' : 'text-danger' }}">
                                        {{ $k->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($k->nominal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">Belum ada transaksi kas tercatat untuk RT ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 4: KEGIATAN RT ==================== --}}
        <div class="tab-pane fade" id="tab-kegiatan" role="tabpanel">
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Daftar Kegiatan RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
                                <th>Nama Kegiatan</th>
                                <th>Tanggal & Waktu</th>
                                <th>Lokasi</th>
                                <th>Anggaran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kegiatanRt as $keg)
                                <tr>
                                    <td class="fw-bold text-white">{{ $keg->nama_kegiatan }}</td>
                                    <td class="text-white-50 small">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->translatedFormat('d M Y, H:i') }}</td>
                                    <td>{{ $keg->lokasi ?: '-' }}</td>
                                    <td class="font-monospace text-emerald-400">Rp {{ number_format($keg->anggaran ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ $keg->status === 'selesai' ? 'bg-secondary' : 'bg-primary' }}">
                                            {{ ucfirst($keg->status ?? 'terencana') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">Belum ada agenda kegiatan yang diadakan oleh RT ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 5: LAYANAN RT ==================== --}}
        <div class="tab-pane fade" id="tab-layanan" role="tabpanel">
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-envelope-open-text text-warning me-2"></i>Permohonan Surat Warga RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
                                <th>Nomor Surat / Tanggal</th>
                                <th>Pemohon</th>
                                <th>Jenis Surat</th>
                                <th>Keperluan</th>
                                <th>Status Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($layananSurat as $srt)
                                <tr>
                                    <td>
                                        <div class="fw-bold font-monospace text-white">{{ $srt->nomor_surat ?: '-' }}</div>
                                        <small class="text-white-50">{{ $srt->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>{{ optional($srt->user)->name }}</td>
                                    <td>{{ optional($srt->jenisSurat)->nama_surat ?? 'Surat Pengantar' }}</td>
                                    <td class="small text-white-50">{{ Str::limit($srt->keperluan, 40) }}</td>
                                    <td>
                                        <span class="badge {{ $srt->status_badge ?? 'bg-secondary' }}">
                                            {{ ucfirst($srt->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">Belum ada permohonan surat dari warga RT ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 6: RIWAYAT AKTIVITAS ==================== --}}
        <div class="tab-pane fade" id="tab-aktivitas" role="tabpanel">
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-history text-danger me-2"></i>Audit Trail & Log Aktivitas RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($aktivitas as $act)
                                <tr>
                                    <td class="font-monospace text-white-50 small" style="width: 15%;">{{ $act->created_at->format('d M Y H:i:s') }}</td>
                                    <td style="width: 20%;">
                                        <div class="fw-semibold text-white">{{ optional($act->user)->name ?? 'Sistem' }}</div>
                                        <small class="text-white-50">{{ optional($act->user)->email }}</small>
                                    </td>
                                    <td style="width: 15%;">
                                        <span class="badge bg-slate-700 font-monospace small">{{ $act->action }}</span>
                                    </td>
                                    <td class="small text-white-50">{{ $act->deskripsi }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">Belum ada log aktivitas tercatat untuk RT ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
