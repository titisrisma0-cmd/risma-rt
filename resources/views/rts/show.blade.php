@extends('layouts.app')

@section('title', 'Detail RT ' . $rt->kode_rt . ' — Manajemen RT')

@section('content')
<div class="py-2">
    {{-- Header & Breadcrumb --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <a href="{{ route('rts.index') }}">Manajemen RT</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>RT {{ $rt->kode_rt }}</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Wilayah RT {{ $rt->kode_rt }} — RW {{ $rt->rw->kode_rw ?? '01' }}
            </h1>
            <p class="text-secondary mb-0">Pemantauan operasional, kependudukan, keuangan kas, kegiatan, dan layanan terpadu.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('rts.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar RT
            </a>
            <a href="{{ route('rw.audit.index') }}" class="btn btn-emerald">
                <i class="fas fa-clipboard-check me-1"></i> Keuangan & Audit
            </a>
        </div>
    </div>

    {{-- Kartu Statistik Ringkas (Donezo Metric Cards) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Ketua RT</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div>
                    <h5 class="text-dark fw-bold mb-0 text-truncate">{{ optional($rt->ketua)->name ?? 'Belum Ditentukan' }}</h5>
                    <span class="stat-subtext text-success font-monospace"><i class="fab fa-whatsapp"></i> {{ optional($rt->ketua->profile)->no_hp ?? '-' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Kependudukan</span>
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ $totalKk }} <small class="fs-6 text-secondary fw-normal">KK / {{ $totalWarga }} Jiwa</small></div>
                    <span class="stat-subtext text-secondary">{{ $totalLaki }} L &bull; {{ $totalPerempuan }} P &bull; {{ $totalBelumDiketahui }} N/A</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Saldo Kas RT</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-success font-monospace" style="font-size: 1.4rem;">
                        Rp {{ number_format($saldoKas, 0, ',', '.') }}
                    </div>
                    <span class="stat-subtext text-secondary">Pemasukan: Rp {{ number_format($pemasukanKas, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Status Audit Bulan Ini</span>
                    <div class="stat-icon-wrapper stat-icon-amber">
                        <i class="fas fa-stamp"></i>
                    </div>
                </div>
                <div class="mt-1">
                    @if($auditBulanIni)
                        <span class="badge {{ $auditBulanIni->status_badge }} px-2.5 py-1">
                            {{ $auditBulanIni->status_label }}
                        </span>
                    @else
                        <span class="badge badge-soft-warning px-2.5 py-1">Belum Diperiksa</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Tab Navigation --}}
    <ul class="nav nav-tabs nav-tabs-saas mb-4" id="rtTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="profil-tab" data-bs-toggle="tab" data-bs-target="#tab-profil" type="button" role="tab">
                <i class="fas fa-id-card me-1 text-primary"></i> 1. Profil RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="warga-tab" data-bs-toggle="tab" data-bs-target="#tab-warga" type="button" role="tab">
                <i class="fas fa-users me-1 text-info"></i> 2. Data KK & Warga
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="keuangan-tab" data-bs-toggle="tab" data-bs-target="#tab-keuangan" type="button" role="tab">
                <i class="fas fa-wallet me-1 text-success"></i> 3. Keuangan RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="kegiatan-tab" data-bs-toggle="tab" data-bs-target="#tab-kegiatan" type="button" role="tab">
                <i class="fas fa-calendar-check me-1 text-primary"></i> 4. Kegiatan RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="layanan-tab" data-bs-toggle="tab" data-bs-target="#tab-layanan" type="button" role="tab">
                <i class="fas fa-handshake-angle me-1 text-warning"></i> 5. Layanan RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="aktivitas-tab" data-bs-toggle="tab" data-bs-target="#tab-aktivitas" type="button" role="tab">
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
                    <div class="saas-card h-100">
                        <div class="card-header-saas">
                            <h5 class="card-title-saas"><i class="fas fa-map-marker-alt text-warning"></i> Informasi Wilayah RT</h5>
                        </div>
                        <div class="card-body p-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-secondary" style="width: 38%;">Kode Wilayah</td>
                                    <td class="fw-bold text-dark">RT {{ $rt->kode_rt }} / RW {{ $rt->rw->kode_rw ?? '01' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Nama RT</td>
                                    <td class="text-dark">{{ $rt->nama_rt ?: 'RT ' . $rt->kode_rt }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Kecamatan / Kelurahan</td>
                                    <td class="text-dark">{{ config('app.kelurahan', 'Kelurahan Setempat') }} / {{ config('app.kecamatan', 'Kecamatan Setempat') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Terdaftar Sejak</td>
                                    <td class="text-dark">{{ $rt->created_at ? $rt->created_at->translatedFormat('d F Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Status Wilayah</td>
                                    <td><span class="badge badge-soft-success">Aktif Beroperasi</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="saas-card h-100">
                        <div class="card-header-saas">
                            <h5 class="card-title-saas"><i class="fas fa-user-shield text-primary"></i> Profil Ketua RT</h5>
                        </div>
                        <div class="card-body p-3">
                            @if($rt->ketua)
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-secondary" style="width: 38%;">Nama Lengkap</td>
                                        <td class="fw-bold text-success">{{ $rt->ketua->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Email Login</td>
                                        <td class="font-monospace text-dark small">{{ $rt->ketua->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Nomor WhatsApp / HP</td>
                                        <td class="text-dark small font-monospace">{{ optional($rt->ketua->profile)->no_hp ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Role Akses</td>
                                        <td><span class="badge badge-soft-primary">{{ $rt->ketua->role->nama_role ?? 'rt' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Status Akun</td>
                                        <td>
                                            @php $st = optional($rt->ketua->profile)->status ?? 'aktif'; @endphp
                                            <span class="badge {{ $st === 'aktif' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                                {{ ucfirst($st) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="empty-state-saas py-3 border-0">
                                    <i class="fas fa-user-slash text-secondary mb-2"></i>
                                    <p class="text-secondary small mb-2">Belum ada akun Ketua RT yang ditautkan ke wilayah ini.</p>
                                    <a href="{{ route('rts.index') }}" class="btn btn-sm btn-emerald">
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
            <div class="saas-card p-3 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-venus-mars text-warning"></i>
                        <span class="text-dark fw-bold">Statistik Kependudukan RT {{ $rt->kode_rt }}:</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small">
                        <span class="badge badge-soft-info"><i class="fas fa-id-card me-1"></i> Total KK: <strong>{{ $totalKk }}</strong></span>
                        <span class="badge badge-soft-primary"><i class="fas fa-users me-1"></i> Total Jiwa: <strong>{{ $totalWarga }}</strong></span>
                        <span class="badge badge-soft-success"><i class="fas fa-male me-1"></i> Laki-laki: <strong>{{ $totalLaki }}</strong></span>
                        <span class="badge badge-soft-danger"><i class="fas fa-female me-1"></i> Perempuan: <strong>{{ $totalPerempuan }}</strong></span>
                        <span class="badge badge-soft-warning"><i class="fas fa-question-circle me-1"></i> N/A: <strong>{{ $totalBelumDiketahui }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Daftar Kepala Keluarga --}}
                <div class="col-lg-5">
                    <div class="saas-card">
                        <div class="card-header-saas">
                            <h5 class="card-title-saas"><i class="fas fa-house-user text-info"></i> Kartu Keluarga ({{ $totalKk }})</h5>
                        </div>
                        <div class="table-responsive" style="max-height: 480px;">
                            <table class="table-saas table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No. KK</th>
                                        <th>Kepala Keluarga</th>
                                        <th class="text-center">Anggota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rt->kartuKeluargas as $kk)
                                        <tr>
                                            <td class="font-monospace small text-primary fw-bold">{{ $kk->nomor_kk }}</td>
                                            <td class="small fw-semibold text-dark">{{ optional($kk->kepalaKeluarga)->name ?? '-' }}</td>
                                            <td class="text-center"><span class="badge badge-soft-info">{{ $kk->anggota->count() }} Jiwa</span></td>
                                        </tr>
                                    @empty
                                        @php
                                            $uniqueKk = $rt->wargaProfiles->whereNotNull('no_kk')->groupBy('no_kk');
                                        @endphp
                                        @forelse($uniqueKk as $noKk => $anggota)
                                            <tr>
                                                <td class="font-monospace small text-primary fw-bold">{{ $noKk }}</td>
                                                <td class="small fw-semibold text-dark">{{ $anggota->first()->nama_lengkap ?? $anggota->first()->user?->name ?? '-' }}</td>
                                                <td class="text-center"><span class="badge badge-soft-info">{{ $anggota->count() }} Jiwa</span></td>
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
                    <div class="saas-card">
                        <div class="card-header-saas">
                            <h5 class="card-title-saas"><i class="fas fa-users text-warning"></i> Daftar Warga ({{ $totalWarga }} Jiwa)</h5>
                            <a href="{{ route('warga.index', ['rt_id' => $rt->id]) }}" class="btn btn-sm btn-light">
                                <i class="fas fa-external-link-alt me-1"></i> Buka Warga
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 480px;">
                            <table class="table-saas table align-middle mb-0">
                                <thead>
                                    <tr>
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
                                            <td class="fw-semibold text-dark small">{{ $wp->nama_lengkap ?: optional($wp->user)->name }}</td>
                                            <td class="font-monospace small text-secondary">{{ $wp->nik ?: '-' }}</td>
                                            <td>
                                                @php
                                                    $jkVal = strtoupper(trim((string)($wp->jenis_kelamin ?: optional($wp->user)->jenis_kelamin)));
                                                @endphp
                                                @if($jkVal === 'L' || $jkVal === 'LAKI-LAKI')
                                                    <span class="badge badge-soft-primary">L</span>
                                                @elseif($jkVal === 'P' || $jkVal === 'PEREMPUAN')
                                                    <span class="badge badge-soft-danger">P</span>
                                                @else
                                                    <span class="badge badge-soft-warning" title="Belum Diketahui">-</span>
                                                @endif
                                            </td>
                                            <td class="small font-monospace text-secondary">{{ $wp->no_hp ?: '-' }}</td>
                                            <td>
                                                <span class="badge {{ ($wp->status ?? 'aktif') === 'aktif' ? 'badge-soft-success' : 'badge-soft-secondary' }}">
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
                    <div class="saas-card p-3">
                        <span class="text-secondary small fw-semibold d-block mb-1">Total Pemasukan Kas</span>
                        <h4 class="text-success fw-bold font-monospace mb-0">Rp {{ number_format($pemasukanKas, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="saas-card p-3">
                        <span class="text-secondary small fw-semibold d-block mb-1">Total Pengeluaran Kas</span>
                        <h4 class="text-danger fw-bold font-monospace mb-0">Rp {{ number_format($pengeluaranKas, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="saas-card p-3">
                        <span class="text-secondary small fw-semibold d-block mb-1">Saldo Kas RT Saat Ini</span>
                        <h4 class="text-dark fw-bold font-monospace mb-0">Rp {{ number_format($saldoKas, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>

            <div class="saas-card">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-exchange-alt text-success"></i> Transaksi Kas Terbaru RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Kategori / Deskripsi</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentKas as $k)
                                <tr>
                                    <td class="text-secondary font-monospace small">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $k->jenis === 'masuk' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                            {{ strtoupper($k->jenis) }}
                                        </span>
                                    </td>
                                    <td class="small text-dark">{{ $k->keterangan }}</td>
                                    <td class="text-end font-monospace fw-bold small {{ $k->jenis === 'masuk' ? 'text-success' : 'text-danger' }}">
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
            <div class="saas-card">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-calendar-check text-primary"></i> Daftar Kegiatan RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
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
                                    <td class="fw-bold text-dark small">{{ $keg->nama_kegiatan }}</td>
                                    <td class="text-secondary small">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="small">{{ $keg->lokasi ?: '-' }}</td>
                                    <td class="font-monospace text-success small">Rp {{ number_format($keg->anggaran ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ $keg->status === 'selesai' ? 'badge-soft-secondary' : 'badge-soft-primary' }}">
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
            <div class="saas-card">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-envelope-open-text text-warning"></i> Permohonan Surat Warga RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
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
                                        <div class="fw-bold font-monospace text-dark small">{{ $srt->nomor_surat ?: '-' }}</div>
                                        <small class="text-secondary">{{ $srt->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="small fw-semibold">{{ optional($srt->user)->name }}</td>
                                    <td class="small">{{ optional($srt->jenisSurat)->nama_surat ?? 'Surat Pengantar' }}</td>
                                    <td class="small text-secondary">{{ Str::limit($srt->keperluan, 40) }}</td>
                                    <td>
                                        <span class="badge {{ $srt->status_badge ?? 'badge-soft-secondary' }}">
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
            <div class="saas-card">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-history text-danger"></i> Audit Trail & Log Aktivitas RT {{ $rt->kode_rt }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($aktivitas as $act)
                                <tr>
                                    <td class="font-monospace text-secondary small" style="width: 18%;">{{ $act->created_at->format('d M Y H:i:s') }}</td>
                                    <td style="width: 22%;">
                                        <div class="fw-semibold text-dark small">{{ optional($act->user)->name ?? 'Sistem' }}</div>
                                        <small class="text-secondary">{{ optional($act->user)->email }}</small>
                                    </td>
                                    <td style="width: 15%;">
                                        <span class="badge badge-soft-info font-monospace small">{{ $act->action }}</span>
                                    </td>
                                    <td class="small text-secondary">{{ $act->deskripsi }}</td>
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
