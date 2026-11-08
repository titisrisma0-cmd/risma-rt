@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Dashboard Pengurus RW')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Welcome Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h1 class="display-6 fw-bold mb-1 text-white">
                        Selamat Datang, <span class="gradient-text">{{ Auth::user()->name }}</span> 👋
                    </h1>
                    <p class="text-white-50 mb-0">
                        Anda login sebagai 
                        <span class="badge bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-pill border border-emerald-500/30">
                            <i class="fas fa-building me-1"></i> Pengurus RW &bull; Pengawas & Koordinator
                        </span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('rts.index') }}" class="btn btn-outline-light px-3 py-2">
                        <i class="fas fa-sitemap me-1"></i> Manajemen RT
                    </a>
                    <a href="{{ route('rw.audit.index') }}" class="btn btn-emerald text-white px-3 py-2" style="background: #10b981; border: none;">
                        <i class="fas fa-wallet me-1"></i> Keuangan & Audit RT
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Pending Action Banners --}}
    @if($laporanMenungguAudit > 0 || $pengajuanMenungguRw > 0)
        <div class="row g-3 mb-4">
            @if($laporanMenungguAudit > 0)
                <div class="col-md-6">
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0 p-3 rounded-3 border-0" style="background: rgba(245, 158, 11, 0.15); border-left: 4px solid #f59e0b !important;">
                        <div>
                            <strong class="text-warning"><i class="fas fa-exclamation-circle me-2"></i>Audit Keuangan Bulanan:</strong>
                            <span class="text-white ms-1">{{ $laporanMenungguAudit }} RT belum diverifikasi pada bulan ini.</span>
                        </div>
                        <a href="{{ route('rw.audit.index') }}" class="btn btn-sm btn-warning fw-semibold">
                            Periksa Sekarang <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endif

            @if($pengajuanMenungguRw > 0)
                <div class="col-md-6">
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-0 p-3 rounded-3 border-0" style="background: rgba(59, 130, 246, 0.15); border-left: 4px solid #3b82f6 !important;">
                        <div>
                            <strong class="text-info"><i class="fas fa-stamp me-2"></i>Persetujuan Surat:</strong>
                            <span class="text-white ms-1">{{ $pengajuanMenungguRw }} permohonan surat menunggu persetujuan RW.</span>
                        </div>
                        <a href="{{ route('rw.pengawasan.persetujuan') }}" class="btn btn-sm btn-info text-white fw-semibold">
                            Tinjau Surat <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- 1. STATISTIK UTAMA WILAYAH RW --}}
    <div class="row g-3 mb-4">
        {{-- Total RT --}}
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white-50 small mb-1 d-block">Wilayah RT Aktif</span>
                        <h3 class="text-white fw-bold mb-0">{{ $totalRtAktif }} Rukun Tetangga</h3>
                        <small class="text-emerald-400">RT 01 sampai RT 08</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                        <i class="fas fa-landmark fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total KK --}}
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white-50 small mb-1 d-block">Total Kepala Keluarga</span>
                        <h3 class="text-info fw-bold mb-0">{{ $totalKk }} KK</h3>
                        <small class="text-white-50">Terdata di seluruh wilayah RT</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info p-3 rounded-3">
                        <i class="fas fa-house-user fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Warga --}}
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white-50 small mb-1 d-block">Total Warga Terdaftar</span>
                        <h3 class="text-warning fw-bold mb-0">{{ $totalWarga }} Jiwa</h3>
                        <small class="text-white-50">Penduduk aktif se-RW</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Saldo Kas Gabungan --}}
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white-50 small mb-1 d-block">Saldo Kas Gabungan</span>
                        <h3 class="text-emerald-400 fw-bold font-monospace mb-0">Rp {{ number_format($saldoKas, 0, ',', '.') }}</h3>
                        <small class="text-white-50">Total kas seluruh wilayah</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success p-3 rounded-3">
                        <i class="fas fa-wallet fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Highlight Keuangan & Agenda RW --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Total Pemasukan Wilayah</span>
                <h4 class="text-emerald-400 fw-bold font-monospace mb-0">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Total Pengeluaran Wilayah</span>
                <h4 class="text-danger fw-bold font-monospace mb-0">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Kegiatan RW Terdekat</span>
                @if($kegiatanRwTerdekat)
                    <h5 class="text-white fw-bold mb-0 text-truncate">{{ $kegiatanRwTerdekat->nama_kegiatan }}</h5>
                    <small class="text-emerald-400">{{ \Carbon\Carbon::parse($kegiatanRwTerdekat->tanggal_mulai)->translatedFormat('d F Y') }}</small>
                @else
                    <h5 class="text-white-50 mb-0">Tidak ada agenda dekat</h5>
                    <small class="text-white-50">Semua kegiatan up to date</small>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. MONITORING WILAYAH CEPAT (RT 01 SAMPAI RT 08) --}}
    <div class="card border-0 mb-4" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
            <div>
                <h5 class="text-white mb-0">
                    <i class="fas fa-sitemap text-emerald-400 me-2"></i>Monitoring Wilayah Cepat (RT 01 – RT 08)
                </h5>
                <small class="text-white-50">Ringkasan kependudukan, keuangan, dan status audit masing-masing RT</small>
            </div>
            <a href="{{ route('rts.index') }}" class="btn btn-sm btn-outline-light">
                <i class="fas fa-cog me-1"></i> Kelola Manajemen RT
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.6);">
                    <tr class="text-secondary small">
                        <th class="py-3 px-3">RT</th>
                        <th class="py-3 px-3">Ketua RT</th>
                        <th class="py-3 px-3">Kontak HP</th>
                        <th class="py-3 px-3 text-center">Jumlah KK</th>
                        <th class="py-3 px-3 text-center">Jumlah Warga</th>
                        <th class="py-3 px-3 text-end">Kas RT</th>
                        <th class="py-3 px-3 text-center">Status Audit Terakhir</th>
                        <th class="py-3 px-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rtStats as $rt)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3">
                                <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.95rem;">
                                    RT {{ $rt->kode_rt }}
                                </span>
                            </td>
                            <td class="py-3 px-3 fw-semibold text-white">
                                {{ $rt->ketua_nama }}
                            </td>
                            <td class="py-3 px-3 text-white-50 small">
                                {{ $rt->ketua_hp }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge bg-info text-dark font-monospace">{{ $rt->total_kk }} KK</span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge bg-secondary font-monospace">{{ $rt->total_warga }} Jiwa</span>
                            </td>
                            <td class="py-3 px-3 text-end font-monospace fw-bold {{ $rt->saldo_kas >= 0 ? 'text-emerald-400' : 'text-danger' }}">
                                Rp {{ number_format($rt->saldo_kas, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($rt->audit)
                                    <span class="badge {{ $rt->audit->status_badge }} px-2 py-1">
                                        {{ $rt->audit->status_label }}
                                    </span>
                                @else
                                    <span class="badge bg-dark border border-secondary text-secondary px-2 py-1">Belum Ada</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-end">
                                <a href="{{ route('rts.show', $rt->id) }}" class="btn btn-sm btn-outline-info text-white">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. GRAFIK RINGKAS PEMASUKAN VS PENGELUARAN SELURUH WILAYAH --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-chart-line text-emerald-400 me-2"></i>Tren Keuangan Wilayah {{ date('Y') }} (Pemasukan vs Pengeluaran)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div style="height: 320px; position: relative;">
                        <canvas id="kasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .gradient-text {
        color: #ffd700;
        text-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
    }
    .stat-icon {
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4
                },
                {
                    label: 'Pengeluaran (Rp)',
                    data: expenseData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#f8fafc', font: { weight: 'bold' } }
                },
                tooltip: {
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
                    grid: { color: 'rgba(255, 255, 255, 0.08)' },
                    ticks: {
                        color: '#94a3b8',
                        callback: function(value) {
                            return 'Rp ' + (value >= 1000000 ? (value / 1000000) + ' Jt' : value.toLocaleString('id-ID'));
                        }
                    }
                },
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94a3b8' }
                }
            }
        }
    });
</script>
@endpush
@endsection
