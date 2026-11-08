@extends('layouts.app')

@section('title', 'Keuangan dan Audit RT — Pengurus RW')

@section('content')
<div class="py-2">
    {{-- Header --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Keuangan Wilayah</span>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Audit RT</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Keuangan & Audit Wilayah RT
            </h1>
            <p class="text-secondary mb-0">Modul terpadu pengawasan iuran, pengeluaran kas, saldo riil, dan audit akuntabilitas RT 01 – RT 08.</p>
        </div>

        <div class="page-header-actions">
            <a href="{{ route('rw.audit.riwayat') }}" class="btn btn-light">
                <i class="fas fa-list-check me-1 text-primary"></i> Log Audit Trail
            </a>
            <a href="{{ route('laporan.index') }}" class="btn btn-emerald">
                <i class="fas fa-file-invoice me-1"></i> Ekspor Laporan
            </a>
        </div>
    </div>

    {{-- Filter Periode Bulan & Tahun --}}
    <div class="saas-card p-3 mb-4">
        <form method="GET" action="{{ route('rw.audit.index') }}" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label small text-secondary mb-1">Pilih Bulan</label>
                <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-secondary mb-1">Pilih Tahun</label>
                <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-secondary mb-1">Filter RT (Opsional)</label>
                <select name="rt_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua RT (RT 01–08)</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ $selectedRtId == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end justify-content-end">
                <span class="badge badge-soft-primary px-3 py-2">
                    <i class="fas fa-calendar-alt me-1"></i> Periode: <strong>{{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</strong>
                </span>
            </div>
        </form>
    </div>

    {{-- 6-Tab Navigation --}}
    <ul class="nav nav-tabs nav-tabs-saas mb-4" id="keuanganTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#tab-ringkasan" type="button" role="tab">
                <i class="fas fa-chart-pie me-1 text-success"></i> 1. Ringkasan Wilayah
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="iuran-tab" data-bs-toggle="tab" data-bs-target="#tab-iuran" type="button" role="tab">
                <i class="fas fa-hand-holding-usd me-1 text-info"></i> 2. Iuran Warga
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pengeluaran-tab" data-bs-toggle="tab" data-bs-target="#tab-pengeluaran" type="button" role="tab">
                <i class="fas fa-shopping-cart me-1 text-danger"></i> 3. Pengeluaran RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="kas-tab" data-bs-toggle="tab" data-bs-target="#tab-kas" type="button" role="tab">
                <i class="fas fa-book me-1 text-warning"></i> 4. Kas RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#tab-audit" type="button" role="tab">
                <i class="fas fa-stamp me-1 text-success"></i> 5. Audit Keuangan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#tab-riwayat" type="button" role="tab">
                <i class="fas fa-history me-1 text-primary"></i> 6. Riwayat Audit
            </button>
        </li>
    </ul>

    {{-- Tab Contents --}}
    <div class="tab-content" id="keuanganTabContent">
        {{-- ==================== TAB 1: RINGKASAN ==================== --}}
        <div class="tab-pane fade show active" id="tab-ringkasan" role="tabpanel">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card-saas">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="stat-label">Pemasukan Iuran / Kas (Periode)</span>
                            <div class="stat-icon-wrapper stat-icon-green">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                        <div>
                            <div class="stat-value text-success font-monospace" style="font-size: 1.5rem;">
                                Rp {{ number_format($totalPemasukanAll, 0, ',', '.') }}
                            </div>
                            <span class="stat-subtext text-secondary">Seluruh RT & RW</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-saas">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="stat-label">Pengeluaran Seluruh RT (Periode)</span>
                            <div class="stat-icon-wrapper stat-icon-red">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                        <div>
                            <div class="stat-value text-danger font-monospace" style="font-size: 1.5rem;">
                                Rp {{ number_format($totalPengeluaranAll, 0, ',', '.') }}
                            </div>
                            <span class="stat-subtext text-secondary">Belanja & operasional</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-saas">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="stat-label">Saldo Kas Gabungan Seluruh RT</span>
                            <div class="stat-icon-wrapper stat-icon-blue">
                                <i class="fas fa-vault"></i>
                            </div>
                        </div>
                        <div>
                            <div class="stat-value text-dark font-monospace" style="font-size: 1.5rem;">
                                Rp {{ number_format($totalSaldoAll, 0, ',', '.') }}
                            </div>
                            <span class="stat-subtext text-secondary">Posisi saldo kas aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="saas-card mb-4">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-sitemap text-success"></i> Rekapitulasi Wilayah RT 01 – RT 08 (Periode: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }})
                    </h5>
                    <span class="badge badge-soft-primary">Agregat Wilayah</span>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Wilayah RT</th>
                                <th>Ketua RT</th>
                                <th class="text-end">Pemasukan Periode</th>
                                <th class="text-end">Pengeluaran Periode</th>
                                <th class="text-end">Saldo Kas RT</th>
                                <th class="text-center">Status Audit</th>
                                <th class="text-end">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapPerRt as $item)
                                @php
                                    $rt = $item['rt'];
                                    $audit = $item['audit'];
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge badge-soft-primary fw-bold">
                                            RT {{ $rt->kode_rt }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ optional($rt->ketua)->name ?? 'Belum Ditentukan' }}</div>
                                        <small class="text-secondary font-monospace">{{ optional($rt->ketua->profile)->no_hp ?? '-' }}</small>
                                    </td>
                                    <td class="text-end font-monospace text-success fw-semibold small">
                                        Rp {{ number_format($item['pemasukan'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace text-danger fw-semibold small">
                                        Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace text-dark fw-bold small">
                                        Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $audit->status_badge }} px-2.5 py-1">
                                            {{ $audit->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-emerald" data-bs-toggle="modal" data-bs-target="#auditModal{{ $rt->id }}">
                                            <i class="fas fa-stamp me-1"></i> Audit
                                        </button>
                                        <a href="{{ route('rts.show', $rt) }}" class="btn btn-sm btn-light ms-1">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 2: IURAN ==================== --}}
        <div class="tab-pane fade" id="tab-iuran" role="tabpanel">
            <div class="saas-card mb-4">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-hand-holding-usd text-info"></i> Daftar Pembayaran Iuran Lunas (Hanya Baca)</h5>
                    <span class="badge badge-soft-primary">{{ $iuranList->count() }} Data Transaksi</span>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Warga / RT</th>
                                <th>Jenis Iuran</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($iuranList as $byr)
                                <tr>
                                    <td class="font-monospace small text-primary fw-bold">{{ $byr->no_transaksi }}</td>
                                    <td class="text-secondary small">{{ \Carbon\Carbon::parse($byr->tanggal_bayar)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ optional($byr->user)->name }}</div>
                                        <small class="text-secondary">RT {{ optional(optional($byr->user)->profile)->rt_id ? str_pad($byr->user->profile->rt_id, 2, '0', STR_PAD_LEFT) : '-' }}</small>
                                    </td>
                                    <td class="small">{{ optional($byr->iuran)->nama_iuran ?? 'Iuran Bulanan' }}</td>
                                    <td class="text-end font-monospace text-success fw-bold small">Rp {{ number_format($byr->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-soft-success">Lunas</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">Belum ada pembayaran iuran pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 3: PENGELUARAN ==================== --}}
        <div class="tab-pane fade" id="tab-pengeluaran" role="tabpanel">
            <div class="saas-card mb-4">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-shopping-cart text-danger"></i> Daftar Pengeluaran RT (Hanya Baca)</h5>
                    <span class="badge badge-soft-primary">{{ $pengeluaranList->count() }} Transaksi</span>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>RT</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengeluaranList as $p)
                                <tr>
                                    <td class="text-secondary font-monospace small">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-soft-primary">
                                            RT {{ $p->rt_id ? str_pad($p->rt_id, 2, '0', STR_PAD_LEFT) : 'RW' }}
                                        </span>
                                    </td>
                                    <td><span class="badge badge-soft-warning">{{ $p->kategori }}</span></td>
                                    <td class="small text-dark">{{ $p->keterangan }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold small">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                    <td class="text-center small text-secondary">{{ optional($p->creator)->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">Belum ada catatan pengeluaran pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 4: KAS RT ==================== --}}
        <div class="tab-pane fade" id="tab-kas" role="tabpanel">
            <div class="saas-card mb-4">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-book text-warning"></i> Buku Mutasi Kas RT</h5>
                    <span class="badge badge-soft-primary">{{ $kasList->count() }} Mutasi</span>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>RT</th>
                                <th>Jenis</th>
                                <th>Kategori / Keterangan</th>
                                <th class="text-end">Nominal Masuk</th>
                                <th class="text-end">Nominal Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kasList as $k)
                                <tr>
                                    <td class="text-secondary font-monospace small">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-soft-primary">
                                            RT {{ $k->rt_id ? str_pad($k->rt_id, 2, '0', STR_PAD_LEFT) : 'RW' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $k->jenis === 'masuk' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                            {{ strtoupper($k->jenis) }}
                                        </span>
                                    </td>
                                    <td class="small text-dark">{{ $k->keterangan }}</td>
                                    <td class="text-end font-monospace text-success fw-semibold small">
                                        {{ $k->jenis === 'masuk' ? 'Rp ' . number_format($k->nominal, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end font-monospace text-danger fw-semibold small">
                                        {{ $k->jenis === 'keluar' ? 'Rp ' . number_format($k->nominal, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">Belum ada data mutasi kas pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 5: AUDIT KEUANGAN ==================== --}}
        <div class="tab-pane fade" id="tab-audit" role="tabpanel">
            <div class="saas-card mb-4">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-stamp text-success"></i> Pemeriksaan & Verifikasi Audit RT 01 – RT 08</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Wilayah RT</th>
                                <th>Ketua RT</th>
                                <th class="text-end">Pemasukan</th>
                                <th class="text-end">Pengeluaran</th>
                                <th class="text-end">Saldo Kas</th>
                                <th class="text-center">Status Audit</th>
                                <th>Catatan Terakhir</th>
                                <th class="text-end">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapPerRt as $item)
                                @php
                                    $rt = $item['rt'];
                                    $audit = $item['audit'];
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge badge-soft-primary fw-bold">
                                            RT {{ $rt->kode_rt }}
                                        </span>
                                    </td>
                                    <td class="small fw-semibold text-dark">{{ optional($rt->ketua)->name ?? 'Belum Ditentukan' }}</td>
                                    <td class="text-end font-monospace text-success small">Rp {{ number_format($item['pemasukan'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger small">Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-dark fw-bold small">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $audit->status_badge }} px-2.5 py-1">
                                            {{ $audit->status_label }}
                                        </span>
                                    </td>
                                    <td class="small text-secondary" style="max-width: 200px;">
                                        {{ $audit->catatan_rw ? Str::limit($audit->catatan_rw, 40) : '-' }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('rw.audit.show-rt', [$rt, 'tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn btn-sm btn-light me-1">
                                            <i class="fas fa-search me-1 text-primary"></i> Periksa
                                        </a>
                                        <button type="button" class="btn btn-sm btn-emerald" data-bs-toggle="modal" data-bs-target="#auditModal{{ $rt->id }}">
                                            <i class="fas fa-stamp me-1"></i> Verifikasi
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 6: RIWAYAT AUDIT ==================== --}}
        <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
            <div class="saas-card mb-4">
                <div class="card-header-saas">
                    <h5 class="card-title-saas"><i class="fas fa-history text-primary"></i> Log Riwayat Perubahan Status Audit</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pemeriksa</th>
                                <th>Wilayah RT</th>
                                <th>Deskripsi Audit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditHistory as $h)
                                <tr>
                                    <td class="font-monospace text-secondary small" style="width: 20%;">{{ $h->created_at->format('d M Y H:i:s') }}</td>
                                    <td class="fw-semibold text-dark small" style="width: 25%;">{{ optional($h->user)->name ?? 'Pengurus RW' }}</td>
                                    <td style="width: 15%;">
                                        <span class="badge badge-soft-primary">
                                            RT {{ optional($h->rt)->kode_rt ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="small text-secondary">{{ $h->deskripsi }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">Belum ada riwayat audit tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODALS VERIFIKASI ==================== --}}
@foreach($rekapPerRt as $item)
    @php
        $rt = $item['rt'];
        $audit = $item['audit'];
    @endphp
    <div class="modal fade" id="auditModal{{ $rt->id }}" tabindex="-1" aria-labelledby="auditModalLabel{{ $rt->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="auditModalLabel{{ $rt->id }}">
                        <i class="fas fa-stamp text-success me-2"></i>Verifikasi Audit RT {{ $rt->kode_rt }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('rw.audit.update-status', $rt) }}">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <div class="modal-body p-4">
                        <div class="p-3 mb-3 rounded-3 border bg-light">
                            <div class="row g-2 text-secondary small">
                                <div class="col-6">Pemasukan: <strong class="text-success font-monospace">Rp {{ number_format($item['pemasukan'], 0, ',', '.') }}</strong></div>
                                <div class="col-6">Pengeluaran: <strong class="text-danger font-monospace">Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}</strong></div>
                                <div class="col-12 mt-2">Saldo Kas: <strong class="text-dark font-monospace">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</strong></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Pemeriksaan Audit *</label>
                            <select name="status" id="statusSelect{{ $rt->id }}" class="form-select" required onchange="toggleCatatanRequired({{ $rt->id }})">
                                <option value="belum_diperiksa" {{ $audit->status === 'belum_diperiksa' ? 'selected' : '' }}>Belum Diperiksa</option>
                                <option value="sedang_diperiksa" {{ $audit->status === 'sedang_diperiksa' ? 'selected' : '' }}>Sedang Diperiksa</option>
                                <option value="perlu_perbaikan" {{ $audit->status === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                                <option value="terverifikasi" {{ $audit->status === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi (Lolos Audit)</option>
                                <option value="ditolak" {{ $audit->status === 'ditolak' ? 'selected' : '' }}>Ditolak dengan Catatan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" id="catatanLabel{{ $rt->id }}">Catatan Audit RW untuk Ketua RT</label>
                            <textarea name="catatan_rw" id="catatanTextarea{{ $rt->id }}" rows="3" class="form-control" placeholder="Berikan catatan, temuan, atau rekomendasi perbaikan...">{{ $audit->catatan_rw }}</textarea>
                            <small class="text-secondary fst-italic" id="catatanHelper{{ $rt->id }}">Wajib diisi jika status Perlu Perbaikan atau Ditolak.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald px-4">
                            <i class="fas fa-check me-1"></i> Simpan Status Audit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
    function toggleCatatanRequired(rtId) {
        const select = document.getElementById('statusSelect' + rtId);
        const textarea = document.getElementById('catatanTextarea' + rtId);
        const label = document.getElementById('catatanLabel' + rtId);
        if (select.value === 'perlu_perbaikan' || select.value === 'ditolak') {
            textarea.required = true;
            label.innerHTML = 'Catatan Audit RW untuk Ketua RT <span class="text-danger">* (Wajib diisi)</span>';
        } else {
            textarea.required = false;
            label.innerHTML = 'Catatan Audit RW untuk Ketua RT';
        }
    }
</script>
@endsection
