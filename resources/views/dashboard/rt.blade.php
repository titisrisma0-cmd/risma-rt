@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Dashboard Ketua RT ' . ($rtKode ?? '01'))

@section('content')
<div class="py-2">
    <!-- Welcome Header RT -->
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-soft-primary px-3 py-1">
                    <i class="fas fa-shield-alt me-1"></i> Ketua RT {{ $rtKode }} &bull; Pengurus Wilayah
                </span>
                <span class="badge badge-soft-info px-3 py-1">
                    <i class="fas fa-map-marker-alt me-1"></i> Wilayah {{ $namaRt }} / RW 01
                </span>
                <span class="text-secondary small d-none d-md-inline">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Selamat Datang, Ketua RT {{ $rtKode }} 👋
            </h1>
            <p class="text-secondary mb-0">
                Pusat komando administrasi kependudukan, keuangan kas warga, dan pelayanan masyarakat {{ $namaRt }}.
            </p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-light">
                <i class="fas fa-id-card me-1 text-secondary"></i> Data KK & Warga
            </a>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-emerald">
                <i class="fas fa-hand-holding-usd me-1"></i> Kelola Iuran RT
            </a>
        </div>
    </div>

    <!-- 8 Kartu Statistik Utama RT (Donezo SaaS Metric Style) -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Kepala Keluarga -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Kepala Keluarga</span>
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($totalKk) }} <small class="text-secondary fw-normal fs-6">KK</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-check-circle text-success"></i> Terdata resmi
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Warga -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Total Penduduk</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($totalWarga) }} <small class="text-secondary fw-normal fs-6">Jiwa</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-user-check text-success"></i> Warga RT {{ $rtKode }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Laki-laki -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Laki-laki</span>
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-male"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($totalLakiLaki) }} <small class="text-secondary fw-normal fs-6">Jiwa</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-venus-mars"></i> Gender L
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Perempuan -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Perempuan</span>
                    <div class="stat-icon-wrapper stat-icon-red">
                        <i class="fas fa-female"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($totalPerempuan) }} <small class="text-secondary fw-normal fs-6">Jiwa</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-venus-mars"></i> Gender P
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Total Iuran Terkumpul -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Iuran Terkumpul</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-success font-monospace" style="font-size: 1.4rem;">
                        Rp {{ number_format($totalIuranTerkumpul, 0, ',', '.') }}
                    </div>
                    <div class="stat-subtext text-success">
                        <i class="fas fa-check-circle"></i> Pembayaran lunas
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Total Tunggakan Iuran -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Tunggakan / Pending</span>
                    <div class="stat-icon-wrapper stat-icon-amber">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-warning font-monospace" style="font-size: 1.4rem;">
                        Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                    </div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-exclamation-circle text-warning"></i> Menunggu verifikasi
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Saldo Kas RT -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Saldo Kas RT {{ $rtKode }}</span>
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark font-monospace" style="font-size: 1.4rem;">
                        Rp {{ number_format($saldoKasRt, 0, ',', '.') }}
                    </div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-arrow-down text-success"></i> Masuk: Rp {{ number_format($kasMasukRt, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Pengumuman RW -->
        <div class="col-6 col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Pengumuman RW</span>
                    <div class="stat-icon-wrapper stat-icon-amber">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ $pengumumanRw->count() }} <small class="text-secondary fw-normal fs-6">Info</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-tower-broadcast text-primary"></i> Warta resmi pusat
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demographic Summary Bar -->
    <div class="saas-card p-3 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="stat-icon-wrapper stat-icon-green" style="width: 32px; height: 32px; font-size: 0.95rem;">
                    <i class="fas fa-venus-mars"></i>
                </div>
                <strong class="text-dark small">Demografi RT {{ $rtKode }}:</strong>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge badge-soft-info"><i class="fas fa-id-card me-1"></i> KK: <strong>{{ $totalKk }}</strong></span>
                <span class="badge badge-soft-primary"><i class="fas fa-users me-1"></i> Jiwa: <strong>{{ $totalWarga }}</strong></span>
                <span class="badge badge-soft-success"><i class="fas fa-male me-1"></i> L: <strong>{{ $totalLakiLaki }}</strong></span>
                <span class="badge badge-soft-danger"><i class="fas fa-female me-1"></i> P: <strong>{{ $totalPerempuan }}</strong></span>
                <span class="badge badge-soft-warning"><i class="fas fa-question-circle me-1"></i> N/A: <strong>{{ $totalBelumDiketahui }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Quick Access Layanan Masyarakat RT (Clean SaaS Interactive Cards) -->
    <div class="saas-card p-4 mb-4" id="layanan-masyarakat">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title-saas">
                    <i class="fas fa-th-large text-success"></i> Modul Pelayanan Masyarakat RT {{ $rtKode }}
                </h5>
                <small class="text-secondary">Akses cepat menu operasional dan pelayanan warga</small>
            </div>
            <span class="badge badge-soft-primary">7 Layanan Aktif</span>
        </div>
        <div class="row g-3">
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ route('bank-sampah.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <span class="quick-service-title">Bank Sampah</span>
                    <span class="quick-service-desc">Setoran & Saldo</span>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ route('layanan.umkm.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-store"></i>
                    </div>
                    <span class="quick-service-title">UMKM RT</span>
                    <span class="quick-service-desc">Etalase Warga</span>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ route('rt.verifikasi.permohonan') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <span class="quick-service-title">Permohonan</span>
                    <span class="quick-service-desc">Verifikasi Surat</span>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ route('layanan.posyandu.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-baby"></i>
                    </div>
                    <span class="quick-service-title">Posyandu</span>
                    <span class="quick-service-desc">Gizi & Balita</span>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ route('layanan.ronda.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <span class="quick-service-title">Ronda Malam</span>
                    <span class="quick-service-desc">Jadwal & Patroli</span>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ route('kegiatan.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span class="quick-service-title">Kegiatan RT</span>
                    <span class="quick-service-desc">Agenda & Acara</span>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ route('aspirasi.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <span class="quick-service-title">Aspirasi</span>
                    <span class="quick-service-desc">Keluhan Warga</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Bagian 1 & 2: Pembayaran Terbaru & Pengumuman RW -->
    <div class="row g-4 mb-4">
        <!-- Daftar Pembayaran Terbaru RT -->
        <div class="col-lg-7">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-receipt text-success"></i> Pembayaran Iuran Terbaru Warga
                    </h5>
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-light">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Warga</th>
                                <th>Jenis Iuran</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPembayaran as $pay)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ optional($pay->user)->name ?? '-' }}</div>
                                        <small class="text-secondary">{{ optional($pay->user)->email }}</small>
                                    </td>
                                    <td class="small">{{ optional($pay->iuran)->nama_iuran ?? 'Iuran' }}</td>
                                    <td class="fw-bold font-monospace text-dark">Rp {{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td>
                                        @if($pay->status === 'lunas')
                                            <span class="badge badge-soft-success">Lunas</span>
                                        @elseif($pay->status === 'pending')
                                            <span class="badge badge-soft-warning">Menunggu</span>
                                        @else
                                            <span class="badge badge-soft-danger">{{ $pay->status }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-secondary">{{ $pay->created_at ? $pay->created_at->format('d/m/Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Belum ada transaksi pembayaran di RT {{ $rtKode }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pengumuman dari RW -->
        <div class="col-lg-5">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-bullhorn text-warning"></i> Pengumuman & Agenda RW
                    </h5>
                    <span class="badge badge-soft-primary">Pusat RW</span>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        @forelse($pengumumanRw as $rwInfo)
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 text-dark fw-bold small">{{ $rwInfo->nama_kegiatan }}</h6>
                                    <span class="text-secondary small" style="font-size: 0.72rem;">{{ $rwInfo->created_at ? $rwInfo->created_at->diffForHumans() : '' }}</span>
                                </div>
                                <p class="text-secondary small mb-1" style="font-size: 0.8rem;">{{ Str::limit($rwInfo->deskripsi, 90) }}</p>
                                <div class="d-flex align-items-center gap-3 small text-secondary" style="font-size: 0.75rem;">
                                    <span><i class="fas fa-calendar-alt text-success me-1"></i>{{ $rwInfo->tanggal_mulai ? \Carbon\Carbon::parse($rwInfo->tanggal_mulai)->format('d M Y') : '-' }}</span>
                                    <span><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $rwInfo->lokasi ?? 'Wilayah RW' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state-saas py-3 border-0">
                                <i class="fas fa-info-circle text-secondary mb-1"></i>
                                <p class="small text-secondary mb-0">Belum ada pengumuman baru dari pengurus RW.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 3 & 4: Agenda Kegiatan RT & Warga Terbaru -->
    <div class="row g-4 mb-4">
        <!-- Agenda Kegiatan RT -->
        <div class="col-lg-6">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-calendar-check text-primary"></i> Agenda Kegiatan RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-sm btn-light">+ Kelola</a>
                </div>
                <div class="card-body p-3">
                    @forelse($agendaKegiatan as $keg)
                        <div class="p-3 rounded-3 border mb-2 bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-dark fw-bold mb-1">{{ $keg->nama_kegiatan }}</h6>
                                    <div class="small text-secondary mb-2">{{ Str::limit($keg->deskripsi, 85) }}</div>
                                    <div class="d-flex gap-3 small text-secondary">
                                        <span><i class="fas fa-clock text-warning me-1"></i>{{ $keg->tanggal_mulai ? \Carbon\Carbon::parse($keg->tanggal_mulai)->format('d M Y H:i') : '-' }}</span>
                                        <span><i class="fas fa-map-pin text-danger me-1"></i>{{ $keg->lokasi }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-soft-primary">RT {{ $rtKode }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-saas py-3 border-0">
                            <p class="small text-secondary mb-0">Belum ada agenda kegiatan internal RT {{ $rtKode }}.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Warga Terbaru di RT -->
        <div class="col-lg-6">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-user-friends text-info"></i> Warga Terbaru RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-sm btn-light">Data Warga</a>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Warga</th>
                                <th>No. KK</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wargaTerbaru as $w)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $w->name }}</div>
                                        <small class="text-secondary">{{ $w->email }}</small>
                                    </td>
                                    <td><span class="badge badge-soft-info font-monospace">{{ optional($w->profile)->no_kk ?? '-' }}</span></td>
                                    <td>
                                        @php $st = optional($w->profile)->status ?? 'aktif'; @endphp
                                        <span class="badge {{ $st === 'aktif' ? 'badge-soft-success' : 'badge-soft-warning' }}">
                                            {{ ucfirst($st) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('warga.show', $w->id) }}" class="btn btn-sm btn-light">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">Belum ada warga terdaftar di RT {{ $rtKode }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 5 & 6: Aspirasi & Pengeluaran Kas RT -->
    <div class="row g-4 mb-4">
        <!-- Aspirasi Warga RT -->
        <div class="col-lg-7">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-comment-dots text-warning"></i> Aspirasi & Pengaduan Warga RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('aspirasi.index') }}" class="btn btn-sm btn-light">Kelola Aspirasi</a>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        @forelse($aspirasiWarga as $asp)
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="text-dark mb-0 fw-bold small">{{ $asp->judul }}</h6>
                                    <span class="badge {{ $asp->status === 'selesai' ? 'badge-soft-success' : ($asp->status === 'diproses' ? 'badge-soft-info' : 'badge-soft-warning') }}">
                                        {{ ucfirst($asp->status) }}
                                    </span>
                                </div>
                                <p class="text-secondary small mb-1" style="font-size: 0.8rem;">{{ Str::limit($asp->deskripsi, 100) }}</p>
                                <div class="small text-secondary" style="font-size: 0.75rem;">
                                    <i class="fas fa-clock me-1"></i>{{ $asp->created_at ? $asp->created_at->diffForHumans() : '' }}
                                    <span class="ms-2"><i class="fas fa-tag me-1 text-primary"></i>{{ $asp->kategori ?? 'Umum' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state-saas py-3 border-0">
                                <p class="small text-secondary mb-0">Belum ada aspirasi dari warga RT {{ $rtKode }}.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengeluaran Kas RT -->
        <div class="col-lg-5">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-coins text-danger"></i> Pengeluaran Kas RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('pengeluaran.index') }}" class="btn btn-sm btn-light">+ Catat</a>
                </div>
                <div class="table-responsive">
                    <table class="table-saas table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th>Nominal</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPengeluaran as $out)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $out->keterangan }}</div>
                                        <small class="text-secondary">{{ $out->kategori }}</small>
                                    </td>
                                    <td class="text-danger fw-bold font-monospace small">Rp {{ number_format($out->nominal, 0, ',', '.') }}</td>
                                    <td class="small text-secondary">{{ $out->tanggal ? \Carbon\Carbon::parse($out->tanggal)->format('d/m/Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">Belum ada pengeluaran kas dicatat.</td>
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
