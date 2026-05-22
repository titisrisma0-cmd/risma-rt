@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-header" data-aos="fade-right">
                <h1 class="display-5 fw-bold mb-2">
                    Selamat Datang, <span class="gradient-text">{{ Auth::user()->name }}</span> 👋
                </h1>
                <p class="text-secondary mb-0">
                    Anda login sebagai 
                    <span class="badge bg-primary bg-opacity-20 text-primary px-3 py-2 rounded-pill">
                        <i class="fas fa-user-check me-1"></i> {{ ucfirst(Auth::user()->role->nama_role ?? 'Warga') }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Total Pemasukan</p>
                        <h2 class="fw-bold mb-0 text-success">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h2>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="fas fa-arrow-down text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Total Pengeluaran</p>
                        <h2 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h2>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="fas fa-arrow-up text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Saldo Kas</p>
                        <h2 class="fw-bold mb-0 text-primary">Rp {{ number_format($saldoKas, 0, ',', '.') }}</h2>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="fas fa-wallet text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-secondary mb-1">Total Warga</p>
                        <h2 class="fw-bold mb-0 text-info">{{ $totalWarga }}</h2>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="fas fa-users text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Comparison -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6" data-aos="fade-right">
            <div class="glass-card">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line gradient-text me-2"></i> Grafik Pemasukan {{ date('Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="incomeChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
            <div class="glass-card">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area gradient-text me-2"></i> Grafik Pengeluaran {{ date('Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="expenseChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Info -->
    <div class="row g-4 mb-4">
        <div class="col-12" data-aos="fade-up">
            <div class="glass-card">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle gradient-text me-2"></i> Informasi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="row gy-3">
                            <div class="col-md-3">
                                <span class="text-secondary">Total Iuran Aktif</span>
                                <div class="fw-bold text-primary">{{ $totalIuranAktif }} Jenis</div>
                            </div>
                            <div class="col-md-3">
                                <span class="text-secondary">Pembayaran Bulan Ini</span>
                                <div class="fw-bold text-success">Rp {{ number_format($pembayaranBulanIni, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-md-3">
                                <span class="text-secondary">Total Pengeluaran</span>
                                <div class="fw-bold text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-md-3">
                                <span class="text-secondary">Bulan Saat Ini</span>
                                <div class="fw-bold">{{ date('F Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Iuran -->
    <div class="row mb-4">
        <div class="col-12" data-aos="fade-up">
            <div class="glass-card">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie gradient-text me-2"></i> Statistik Pembayaran per Iuran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Jenis Iuran</th>
                                    <th>Nominal</th>
                                    <th>Total Pembayaran</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statistikIuran as $item)
                                <tr>
                                    <td>{{ $item->nama_iuran }}</td>
                                    <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $item->total_bayar ?? 0 }}x Bayar</span>
                                    </td>
                                    <td>
                                        @if($item->status == 'aktif')
                                            <span class="badge bg-primary">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data iuran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments & Top Warga -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6" data-aos="fade-right">
            <div class="glass-card">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-history gradient-text me-2"></i> Pembayaran Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Warga</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPembayaran as $item)
                                <tr>
                                    <td>{{ $item->no_transaksi }}</td>
                                    <td>{{ $item->user->name }}</td>
                                    <td>Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td>
                                        @if($item->status == 'lunas')
                                            <span class="badge bg-success">Lunas</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada pembayaran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
            <div class="glass-card">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy gradient-text me-2"></i> Top 5 Warga Terbanyak Bayar
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $rank = 1;
                        $displayed = 0;
                    @endphp
                    @foreach($topWarga as $item)
                        @if($item->total_bayar > 0 && $displayed < 5)
                        <div class="top-warga-item">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rank-badge rank-{{ $rank }}">
                                    @if($rank == 1)
                                        <i class="fas fa-crown"></i>
                                    @elseif($rank == 2)
                                        <i class="fas fa-medal"></i>
                                    @elseif($rank == 3)
                                        <i class="fas fa-medal"></i>
                                    @else
                                        {{ $rank }}
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $item->name }}</h6>
                                    <small class="text-secondary">{{ $item->email }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">
                                        Rp {{ number_format($item->total_bayar, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @php 
                            $rank++; 
                            $displayed++;
                        @endphp
                        @endif
                    @endforeach
                    
                    @if($displayed == 0)
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-secondary mb-3"></i>
                        <p class="text-secondary">Belum ada data pembayaran</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="row mb-4">
        <div class="col-12" data-aos="fade-up">
            <div class="glass-card">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart gradient-text me-2"></i> Pengeluaran Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Keterangan</th>
                                    <th>Nominal</th>
                                    <th>Dicatat oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPengeluaran as $item)
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                                    <td>
                                        <span class="badge bg-warning">{{ $item->kategori }}</span>
                                    </td>
                                    <td>{{ Str::limit($item->keterangan, 40) }}</td>
                                    <td class="text-danger">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $item->creator->name }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada pengeluaran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard Custom Styles - Lebaran Islamic Theme */
    .gradient-text {
        color: #ffd700;
        text-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
    }
    
    .stat-card {
        background: linear-gradient(135deg, rgba(26, 95, 63, 0.4), rgba(45, 134, 89, 0.2));
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 20px;
        border: 2px solid rgba(255, 215, 0, 0.3);
        transition: all 0.3s;
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1), inset 0 1px 0 rgba(255, 251, 240, 0.1);
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        border-color: rgba(255, 215, 0, 0.5);
        background: linear-gradient(135deg, rgba(26, 95, 63, 0.5), rgba(45, 134, 89, 0.3));
        box-shadow: 0 15px 40px rgba(255, 215, 0, 0.2), inset 0 1px 0 rgba(255, 251, 240, 0.15);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 215, 0, 0.1) !important;
    }
    
    .stat-card .text-success {
        color: #4ade80 !important;
    }
    
    .stat-card .text-danger {
        color: #f87171 !important;
    }
    
    .stat-card .text-primary {
        color: #60d5ff !important;
    }
    
    .stat-card .text-info {
        color: #a8e6ff !important;
    }
    
    .stat-card .text-secondary {
        color: #cbd5e1 !important;
    }
    
    .glass-card {
        background: rgba(26, 95, 63, 0.3);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        border: 2px solid rgba(255, 215, 0, 0.2);
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 251, 240, 0.1);
    }
    
    .card-header-custom {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255, 215, 0, 0.15);
        background: linear-gradient(135deg, rgba(26, 95, 63, 0.5), rgba(45, 134, 89, 0.3));
        color: #ffd700;
    }
    
    .glass-card .card-body {
        padding: 20px;
    }
    
    .table-dark {
        background: transparent;
        color: #fffbf0;
    }
    
    .table-dark th {
        border-bottom-color: rgba(255, 215, 0, 0.2);
        color: #ffd700;
        font-weight: 600;
        background: rgba(255, 215, 0, 0.05);
    }
    
    .table-dark td {
        border-bottom-color: rgba(255, 215, 0, 0.05);
        color: #fffbf0;
    }
    
    .table-dark tbody tr:hover {
        background: rgba(255, 215, 0, 0.05);
    }
    
    .top-warga-item {
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 215, 0, 0.1);
    }
    
    .top-warga-item:last-child {
        border-bottom: none;
    }
    
    .rank-badge {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
    
    .rank-1 {
        background: linear-gradient(135deg, #ffd700, #ffed4e);
        color: #1a5f3f;
    }
    
    .rank-2 {
        background: linear-gradient(135deg, #c0a080, #a08060);
        color: white;
    }
    
    .rank-3 {
        background: linear-gradient(135deg, #d4a574, #a8825c);
        color: white;
    }
    
    .rank-4, .rank-5 {
        background: linear-gradient(135deg, #2d8659, #1a5f3f);
        color: #ffd700;
        font-weight: 700;
    }
    
    .bg-opacity-20 {
        --bs-bg-opacity: 0.2;
    }
    
    .info-item {
        background: rgba(255, 215, 0, 0.05);
        border: 1px solid rgba(255, 215, 0, 0.15);
        border-radius: 15px;
        padding: 15px;
    }
    
    .info-item .text-secondary {
        color: #cbd5e1 !important;
    }
    
    .welcome-header {
        background: linear-gradient(135deg, rgba(26, 95, 63, 0.3), rgba(45, 134, 89, 0.15));
        border: 2px solid rgba(255, 215, 0, 0.2);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
    }
    
    .welcome-header h1 {
        color: #fffbf0;
    }
    
    .badge {
        background: rgba(255, 215, 0, 0.2) !important;
        color: #ffd700 !important;
        border: 1px solid rgba(255, 215, 0, 0.3);
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stat-card h2 {
            font-size: 1.3rem;
        }
        .welcome-header h1 {
            font-size: 1.8rem;
        }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = {!! json_encode($bulanLabels) !!};
    const incomeData = {!! json_encode($chartData) !!};
    const expenseData = {!! json_encode($expenseChartData) !!};

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                labels: { color: '#ffd700', font: { weight: 'bold' } }
            },
            tooltip: {
                backgroundColor: 'rgba(26, 95, 63, 0.9)',
                titleColor: '#ffd700',
                bodyColor: '#fffbf0',
                borderColor: 'rgba(255, 215, 0, 0.3)',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        return 'Rp ' + context.raw.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255, 215, 0, 0.1)' },
                ticks: {
                    color: '#ffd700',
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            },
            x: {
                grid: { color: 'rgba(255, 215, 0, 0.05)' },
                ticks: { color: '#ffd700' }
            }
        }
    };

    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    new Chart(incomeCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pemasukan (Rp)',
                data: incomeData,
                borderColor: '#2d8659',
                backgroundColor: 'rgba(45, 134, 89, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffd700',
                pointBorderColor: '#fffbf0',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: chartOptions
    });

    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expenseCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pengeluaran (Rp)',
                data: expenseData,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(248, 113, 113, 0.14)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fde68a',
                pointBorderColor: '#fffbf0',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: chartOptions
    });
</script>
@endpush
@endsection