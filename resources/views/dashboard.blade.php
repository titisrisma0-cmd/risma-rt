@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Dashboard Pengurus RW')

@section('content')
<div class="py-2">
    {{-- Welcome Header --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-soft-primary px-3 py-1">
                    <i class="fas fa-building me-1"></i> Pengurus RW 01 &bull; Pengawas & Koordinator
                </span>
                <span class="text-secondary small">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Selamat Datang, {{ Auth::user()->name }} 👋
            </h1>
            <p class="text-secondary mb-0">
                Pusat kendali dan monitoring terpadu seluruh rukun tetangga, keuangan wilayah, dan pelayanan masyarakat.
            </p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('rts.index') }}" class="btn btn-light">
                <i class="fas fa-sitemap me-1 text-secondary"></i> Manajemen RT
            </a>
            <a href="{{ route('rw.audit.index') }}" class="btn btn-emerald">
                <i class="fas fa-wallet me-1"></i> Keuangan & Audit RT
            </a>
        </div>
    </div>

    {{-- Alert Pending Action Banners --}}
    @if($laporanMenungguAudit > 0 || $pengajuanMenungguRw > 0)
        <div class="row g-3 mb-4">
            @if($laporanMenungguAudit > 0)
                <div class="col-md-6">
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0 p-3 shadow-xs" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-triangle-exclamation fa-lg me-3 text-warning"></i>
                            <div>
                                <strong class="d-block text-dark">Audit Keuangan Bulanan</strong>
                                <span class="small text-secondary">{{ $laporanMenungguAudit }} RT belum diverifikasi pada bulan ini.</span>
                            </div>
                        </div>
                        <a href="{{ route('rw.audit.index') }}" class="btn btn-sm btn-warning text-dark fw-bold ms-2">
                            Periksa <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endif

            @if($pengajuanMenungguRw > 0)
                <div class="col-md-6">
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-0 p-3 shadow-xs" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-stamp fa-lg me-3 text-primary"></i>
                            <div>
                                <strong class="d-block text-dark">Persetujuan Surat Menunggu</strong>
                                <span class="small text-secondary">{{ $pengajuanMenungguRw }} permohonan surat warga membutuhkan verifikasi RW.</span>
                            </div>
                        </div>
                        <a href="{{ route('rw.pengawasan.persetujuan') }}" class="btn btn-sm btn-primary fw-bold ms-2">
                            Tinjau Surat <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- 1. STATISTIK UTAMA WILAYAH RW (DONEZO METRIC CARDS) --}}
    <div class="row g-3 mb-4">
        {{-- Total RT --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Wilayah RT Aktif</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-landmark"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ $totalRtAktif }} <small class="text-secondary fw-normal fs-6">RT</small></div>
                    <div class="stat-subtext text-success">
                        <i class="fas fa-circle-check"></i> RT 01 sampai RT 08 terkoordinasi
                    </div>
                </div>
            </div>
        </div>

        {{-- Total KK --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Total Kepala Keluarga</span>
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-house-user"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($totalKk) }} <small class="text-secondary fw-normal fs-6">KK</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-database"></i> Terdata di seluruh wilayah RT
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Warga --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Total Warga Terdaftar</span>
                    <div class="stat-icon-wrapper stat-icon-amber">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($totalWarga) }} <small class="text-secondary fw-normal fs-6">Jiwa</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-id-card"></i> Penduduk aktif berdomisili
                    </div>
                </div>
            </div>
        </div>

        {{-- Saldo Kas Gabungan --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Saldo Kas Gabungan</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-success font-monospace" style="font-size: 1.5rem;">
                        Rp {{ number_format($saldoKas, 0, ',', '.') }}
                    </div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-vault"></i> Akumulasi kas RT & RW
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Highlight Keuangan & Agenda RW --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="saas-card p-3">
                <span class="text-secondary small fw-semibold d-block mb-1">Total Pemasukan Wilayah</span>
                <h4 class="text-success fw-bold font-monospace mb-0">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h4>
                <small class="text-secondary" style="font-size: 0.75rem;">Iuran & penerimaan resmi tahun {{ date('Y') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="saas-card p-3">
                <span class="text-secondary small fw-semibold d-block mb-1">Total Pengeluaran Wilayah</span>
                <h4 class="text-danger fw-bold font-monospace mb-0">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h4>
                <small class="text-secondary" style="font-size: 0.75rem;">Operasional & belanja kegiatan wilayah</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="saas-card p-3">
                <span class="text-secondary small fw-semibold d-block mb-1">Kegiatan RW Terdekat</span>
                @if($kegiatanRwTerdekat)
                    <h5 class="text-dark fw-bold mb-0 text-truncate">{{ $kegiatanRwTerdekat->nama_kegiatan }}</h5>
                    <small class="text-success fw-semibold">
                        <i class="fas fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($kegiatanRwTerdekat->tanggal_mulai)->translatedFormat('d F Y') }}
                    </small>
                @else
                    <h5 class="text-secondary mb-0">Tidak ada agenda dekat</h5>
                    <small class="text-secondary">Semua kegiatan up-to-date</small>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. MONITORING WILAYAH CEPAT (RT 01 SAMPAI RT 08) --}}
    <div class="saas-card mb-4">
        <div class="card-header-saas">
            <div>
                <h5 class="card-title-saas">
                    <i class="fas fa-sitemap text-success"></i> Monitoring Wilayah Cepat (RT 01 – RT 08)
                </h5>
                <small class="text-secondary">Ringkasan kependudukan, keuangan, dan status audit masing-masing RT</small>
            </div>
            <a href="{{ route('rts.index') }}" class="btn btn-sm btn-light">
                <i class="fas fa-cog me-1"></i> Kelola RT
            </a>
        </div>
        <div class="table-responsive">
            <table class="table-saas table align-middle mb-0">
                <thead>
                    <tr>
                        <th>RT</th>
                        <th>Ketua RT</th>
                        <th>Kontak HP</th>
                        <th class="text-center">Jumlah KK</th>
                        <th class="text-center">Jumlah Warga</th>
                        <th class="text-end">Kas RT</th>
                        <th class="text-center">Status Audit</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rtStats as $rt)
                        <tr>
                            <td>
                                <span class="badge badge-soft-primary fw-bold">
                                    RT {{ $rt->kode_rt }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                {{ $rt->ketua_nama }}
                            </td>
                            <td class="text-secondary small font-monospace">
                                {{ $rt->ketua_hp ?? '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-info font-monospace">{{ $rt->total_kk }} KK</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary font-monospace">{{ $rt->total_warga }} Jiwa</span>
                            </td>
                            <td class="text-end font-monospace fw-bold {{ $rt->saldo_kas >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($rt->saldo_kas, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($rt->audit)
                                    <span class="badge {{ $rt->audit->status_badge }} px-2.5 py-1">
                                        {{ $rt->audit->status_label }}
                                    </span>
                                @else
                                    <span class="badge badge-soft-warning px-2.5 py-1">Belum Ada</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rts.show', $rt->id) }}" class="btn btn-sm btn-light text-primary">
                                    <i class="fas fa-arrow-right me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. GRAFIK RINGKAS PEMASUKAN VS PENGELUARAN --}}
    <div class="saas-card mb-4">
        <div class="card-header-saas">
            <h5 class="card-title-saas">
                <i class="fas fa-chart-line text-success"></i> Tren Keuangan Wilayah {{ date('Y') }} (Pemasukan vs Pengeluaran)
            </h5>
            <span class="badge badge-soft-success">Agregat RW 01</span>
        </div>
        <div class="card-body p-4">
            <div style="height: 320px; position: relative;">
                <canvas id="kasChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const labels = {!! json_encode($bulanLabels) !!};
    const incomeData = {!! json_encode($chartData) !!};
    const expenseData = {!! json_encode($expenseChartData) !!};

    const ctx = document.getElementById('kasChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan (Rp)',
                    data: incomeData,
                    borderColor: '#147A45',
                    backgroundColor: 'rgba(20, 122, 69, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#147A45',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Pengeluaran (Rp)',
                    data: expenseData,
                    borderColor: '#E65353',
                    backgroundColor: 'rgba(230, 83, 83, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#E65353',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { color: '#17221C', font: { family: 'Plus Jakarta Sans', weight: '600', size: 12 } }
                },
                tooltip: {
                    backgroundColor: '#FFFFFF',
                    titleColor: '#17221C',
                    bodyColor: '#17221C',
                    borderColor: '#E9EEEA',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F0F3F1' },
                    ticks: {
                        color: '#758078',
                        font: { family: 'Plus Jakarta Sans', size: 11 },
                        callback: function(value) {
                            return 'Rp ' + (value >= 1000000 ? (value / 1000000) + ' Jt' : value.toLocaleString('id-ID'));
                        }
                    }
                },
                x: {
                    grid: { color: '#F0F3F1' },
                    ticks: {
                        color: '#758078',
                        font: { family: 'Plus Jakarta Sans', size: 11 }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
