@extends('layouts.app')

@section('title', 'Keuangan dan Audit RT — Pengurus RW')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1">
                <i class="fas fa-wallet text-emerald-400 me-2"></i>Keuangan dan Audit RT
            </h1>
            <p class="text-white-50 mb-0">Modul terpadu pengawasan iuran, pengeluaran, kas, dan akuntabilitas keuangan RT 01 – RT 08</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('rw.audit.riwayat') }}" class="btn btn-outline-light px-3 py-2">
                <i class="fas fa-list-check me-1"></i> Log Audit Trail
            </a>
            <a href="{{ route('laporan.index') }}" class="btn btn-emerald text-white px-3 py-2" style="background: #10b981; border: none;">
                <i class="fas fa-file-invoice me-1"></i> Ekspor Laporan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Periode Bulan & Tahun --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="GET" action="{{ route('rw.audit.index') }}" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label text-white-50 small mb-1">Pilih Bulan</label>
                <select name="bulan" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-white-50 small mb-1">Pilih Tahun</label>
                <select name="tahun" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-white-50 small mb-1">Filter RT (Opsional)</label>
                <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                    <option value="">Semua RT (RT 01–08)</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ $selectedRtId == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end justify-content-end">
                <span class="text-white-50 small">
                    <i class="fas fa-calendar-alt text-emerald-400 me-1"></i> Periode: <strong>{{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</strong>
                </span>
            </div>
        </form>
    </div>

    {{-- 6-Tab Navigation --}}
    <ul class="nav nav-pills mb-4 p-2 rounded" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1);" id="keuanganTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active text-white" id="ringkasan-tab" data-bs-toggle="pill" data-bs-target="#tab-ringkasan" type="button" role="tab">
                <i class="fas fa-chart-pie me-1 text-emerald-400"></i> 1. Ringkasan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="iuran-tab" data-bs-toggle="pill" data-bs-target="#tab-iuran" type="button" role="tab">
                <i class="fas fa-hand-holding-usd me-1 text-info"></i> 2. Iuran
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="pengeluaran-tab" data-bs-toggle="pill" data-bs-target="#tab-pengeluaran" type="button" role="tab">
                <i class="fas fa-shopping-cart me-1 text-danger"></i> 3. Pengeluaran
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="kas-tab" data-bs-toggle="pill" data-bs-target="#tab-kas" type="button" role="tab">
                <i class="fas fa-book me-1 text-warning"></i> 4. Kas RT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="audit-tab" data-bs-toggle="pill" data-bs-target="#tab-audit" type="button" role="tab">
                <i class="fas fa-stamp me-1 text-emerald-400"></i> 5. Audit Keuangan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="riwayat-tab" data-bs-toggle="pill" data-bs-target="#tab-riwayat" type="button" role="tab">
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
                    <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <span class="text-white-50 small mb-1">Total Pemasukan Iuran / Kas (Periode)</span>
                        <h3 class="text-emerald-400 fw-bold font-monospace mb-0">Rp {{ number_format($totalPemasukanAll, 0, ',', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <span class="text-white-50 small mb-1">Total Pengeluaran Seluruh RT (Periode)</span>
                        <h3 class="text-danger fw-bold font-monospace mb-0">Rp {{ number_format($totalPengeluaranAll, 0, ',', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                        <span class="text-white-50 small mb-1">Saldo Kas Gabungan Seluruh RT</span>
                        <h3 class="text-white fw-bold font-monospace mb-0">Rp {{ number_format($totalSaldoAll, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>

            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-sitemap text-emerald-400 me-2"></i>Rekapitulasi Wilayah RT 01 – RT 08 (Periode: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }})</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
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
                                        <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.95rem;">
                                            RT {{ $rt->kode_rt }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white">{{ optional($rt->ketua)->name ?? 'Belum Ditentukan' }}</div>
                                        <small class="text-white-50">{{ optional($rt->ketua->profile)->no_hp ?? '-' }}</small>
                                    </td>
                                    <td class="text-end font-monospace text-emerald-400 fw-semibold">
                                        Rp {{ number_format($item['pemasukan'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace text-danger fw-semibold">
                                        Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace text-white fw-bold">
                                        Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $audit->status_badge }} px-2 py-1">
                                            {{ $audit->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#auditModal{{ $rt->id }}" style="background: #10b981; border: none;">
                                            <i class="fas fa-stamp me-1"></i> Audit
                                        </button>
                                        <a href="{{ route('rts.show', $rt) }}" class="btn btn-sm btn-outline-info text-white ms-1">
                                            <i class="fas fa-eye"></i>
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
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-hand-holding-usd text-info me-2"></i>Daftar Transaksi Pembayaran Iuran Lunas (Hanya Baca)</h5>
                    <span class="badge bg-slate-700 text-white">{{ $iuranList->count() }} Data Transaksi</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
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
                                    <td class="font-monospace small text-emerald-400">{{ $byr->no_transaksi }}</td>
                                    <td class="text-white-50 small">{{ \Carbon\Carbon::parse($byr->tanggal_bayar)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-bold text-white">{{ optional($byr->user)->name }}</div>
                                        <small class="text-white-50">RT {{ optional(optional($byr->user)->profile)->rt_id ? str_pad($byr->user->profile->rt_id, 2, '0', STR_PAD_LEFT) : '-' }}</small>
                                    </td>
                                    <td>{{ optional($byr->iuran)->nama_iuran ?? 'Iuran Bulanan' }}</td>
                                    <td class="text-end font-monospace text-emerald-400 fw-bold">Rp {{ number_format($byr->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">Lunas</span>
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
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-shopping-cart text-danger me-2"></i>Daftar Pengeluaran RT (Hanya Baca)</h5>
                    <span class="badge bg-slate-700 text-white">{{ $pengeluaranList->count() }} Transaksi</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
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
                                    <td class="text-white-50 font-monospace small">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-25 text-info">
                                            RT {{ $p->rt_id ? str_pad($p->rt_id, 2, '0', STR_PAD_LEFT) : 'RW' }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-warning text-dark">{{ $p->kategori }}</span></td>
                                    <td class="small">{{ $p->keterangan }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                    <td class="text-center small text-white-50">{{ optional($p->creator)->name ?? '-' }}</td>
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
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-book text-warning me-2"></i>Buku Mutasi Kas RT</h5>
                    <span class="badge bg-slate-700 text-white">{{ $kasList->count() }} Mutasi</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
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
                                    <td class="text-white-50 font-monospace small">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-blue-500/20 text-blue-300">
                                            RT {{ $k->rt_id ? str_pad($k->rt_id, 2, '0', STR_PAD_LEFT) : 'RW' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $k->jenis === 'masuk' ? 'bg-success' : 'bg-danger' }}">
                                            {{ strtoupper($k->jenis) }}
                                        </span>
                                    </td>
                                    <td class="small">{{ $k->keterangan }}</td>
                                    <td class="text-end font-monospace text-emerald-400 fw-semibold">
                                        {{ $k->jenis === 'masuk' ? 'Rp ' . number_format($k->nominal, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end font-monospace text-danger fw-semibold">
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
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-stamp text-emerald-400 me-2"></i>Pemeriksaan & Verifikasi Audit RT 01 – RT 08</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
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
                                        <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.95rem;">
                                            RT {{ $rt->kode_rt }}
                                        </span>
                                    </td>
                                    <td>{{ optional($rt->ketua)->name ?? 'Belum Ditentukan' }}</td>
                                    <td class="text-end font-monospace text-emerald-400">Rp {{ number_format($item['pemasukan'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-danger">Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-white fw-bold">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $audit->status_badge }} px-2 py-1">
                                            {{ $audit->status_label }}
                                        </span>
                                    </td>
                                    <td class="small text-white-50" style="max-width: 200px;">
                                        {{ $audit->catatan_rw ? Str::limit($audit->catatan_rw, 40) : '-' }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('rw.audit.show-rt', [$rt, 'tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn btn-sm btn-outline-info text-white me-1">
                                            <i class="fas fa-search me-1"></i> Periksa
                                        </a>
                                        <button type="button" class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#auditModal{{ $rt->id }}" style="background: #10b981; border: none;">
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
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-history text-primary me-2"></i>Log Riwayat Perubahan Status Audit</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead style="background: rgba(15, 23, 42, 0.6);">
                            <tr class="text-secondary small">
                                <th>Waktu</th>
                                <th>Pemeriksa</th>
                                <th>Wilayah RT</th>
                                <th>Deskripsi Audit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditHistory as $h)
                                <tr>
                                    <td class="font-monospace text-white-50 small" style="width: 20%;">{{ $h->created_at->format('d M Y H:i:s') }}</td>
                                    <td class="fw-semibold text-white" style="width: 25%;">{{ optional($h->user)->name ?? 'Pengurus RW' }}</td>
                                    <td style="width: 15%;">
                                        <span class="badge bg-blue-500/20 text-blue-300">
                                            RT {{ optional($h->rt)->kode_rt ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="small text-white-50">{{ $h->deskripsi }}</td>
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

{{-- ==================== MODALS VERIFIKASI (RENDERED OUTSIDE TABLE) ==================== --}}
@foreach($rekapPerRt as $item)
    @php
        $rt = $item['rt'];
        $audit = $item['audit'];
    @endphp
    <div class="modal fade" id="auditModal{{ $rt->id }}" tabindex="-1" aria-labelledby="auditModalLabel{{ $rt->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px;">
                <div class="modal-header border-bottom border-white-10">
                    <h5 class="modal-title" id="auditModalLabel{{ $rt->id }}">
                        <i class="fas fa-stamp text-emerald-400 me-2"></i>Verifikasi Audit RT {{ $rt->kode_rt }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('rw.audit.update-status', $rt) }}">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <div class="modal-body">
                        <div class="p-3 mb-3 rounded" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.08);">
                            <div class="row g-2 text-white-50 small">
                                <div class="col-6">Pemasukan: <strong class="text-emerald-400 font-monospace">Rp {{ number_format($item['pemasukan'], 0, ',', '.') }}</strong></div>
                                <div class="col-6">Pengeluaran: <strong class="text-danger font-monospace">Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}</strong></div>
                                <div class="col-12 mt-2">Saldo Kas: <strong class="text-white font-monospace">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</strong></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Status Pemeriksaan Audit *</label>
                            <select name="status" id="statusSelect{{ $rt->id }}" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required onchange="toggleCatatanRequired({{ $rt->id }})">
                                <option value="belum_diperiksa" {{ $audit->status === 'belum_diperiksa' ? 'selected' : '' }}>Belum Diperiksa</option>
                                <option value="sedang_diperiksa" {{ $audit->status === 'sedang_diperiksa' ? 'selected' : '' }}>Sedang Diperiksa</option>
                                <option value="perlu_perbaikan" {{ $audit->status === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                                <option value="terverifikasi" {{ $audit->status === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi (Lolos Audit)</option>
                                <option value="ditolak" {{ $audit->status === 'ditolak' ? 'selected' : '' }}>Ditolak dengan Catatan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small" id="catatanLabel{{ $rt->id }}">Catatan Audit RW untuk Ketua RT</label>
                            <textarea name="catatan_rw" id="catatanTextarea{{ $rt->id }}" rows="3" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Berikan catatan, temuan, atau rekomendasi perbaikan...">{{ $audit->catatan_rw }}</textarea>
                            <small class="text-white-50 fst-italic" id="catatanHelper{{ $rt->id }}">Wajib diisi jika status Perlu Perbaikan atau Ditolak.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white-10">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald text-white px-4" style="background: #10b981; border: none;">
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
