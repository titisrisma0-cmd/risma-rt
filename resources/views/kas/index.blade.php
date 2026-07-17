@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-chart-line"></i> Laporan Kas RW
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Ringkasan pemasukan dan pengeluaran kas RW
            </p>
        </div>
    </div>

    <!-- Statistik Kas -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="stat-card animate__animated animate__fadeInUp">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">Total Pemasukan</h6>
                        <h3 class="mb-0 text-white">Rp {{ number_format($saldoMasuk, 0, ',', '.') }}</h3>
                    </div>
                    <i class="fas fa-arrow-down fa-2x text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-card animate__animated animate__fadeInUp animate__delay-1s" style="background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid rgba(239, 68, 68, 0.3);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">Total Pengeluaran</h6>
                        <h3 class="mb-0 text-white">Rp {{ number_format($saldoKeluar, 0, ',', '.') }}</h3>
                    </div>
                    <i class="fas fa-arrow-up fa-2x text-danger"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-card animate__animated animate__fadeInUp animate__delay-2s" style="background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid rgba(96, 165, 250, 0.3);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">Saldo Akhir</h6>
                        <h3 class="mb-0 text-white">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</h3>
                    </div>
                    <i class="fas fa-wallet fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Pemasukan -->
    <div class="card animate__animated animate__fadeInLeft mb-4">
        <div class="card-header" style="background: rgba(34, 197, 94, 0.1); border-bottom: 1px solid rgba(34, 197, 94, 0.3);">
            <h5 class="mb-0 text-white"><i class="fas fa-arrow-down text-success"></i> Riwayat Pemasukan</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Sumber</th>
                            <th>Keterangan</th>
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasMasuk as $item)
                        <tr>
                            <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                            <td>
                                @if($item->sumber == 'Iuran')
                                    <span class="badge bg-primary"><i class="fas fa-hand-holding-usd"></i> Iuran</span>
                                @else
                                    <span class="badge bg-info">{{ $item->sumber }}</span>
                                @endif
                            </td>
                            <td>{{ $item->keterangan }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-secondary mb-2 d-block"></i>
                                Belum ada data pemasukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-active">
                        <tr>
                            <th colspan="3" class="text-end">Total Pemasukan:</th>
                            <th class="text-end text-success">Rp {{ number_format($saldoMasuk, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabel Pengeluaran -->
    <div class="card animate__animated animate__fadeInRight">
        <div class="card-header" style="background: rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.3);">
            <h5 class="mb-0 text-white"><i class="fas fa-arrow-up text-danger"></i> Riwayat Pengeluaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasKeluar as $item)
                        <tr>
                            <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                            <td>
                                @if($item->kategori == 'Kebersihan')
                                    <span class="badge bg-success"><i class="fas fa-broom"></i> Kebersihan</span>
                                @elseif($item->kategori == 'Keamanan')
                                    <span class="badge bg-info"><i class="fas fa-shield-alt"></i> Keamanan</span>
                                @elseif($item->kategori == 'Sosial')
                                    <span class="badge bg-warning"><i class="fas fa-hand-holding-heart"></i> Sosial</span>
                                @elseif($item->kategori == 'Pembangunan')
                                    <span class="badge bg-primary"><i class="fas fa-hard-hat"></i> Pembangunan</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->kategori }}</span>
                                @endif
                            </td>
                            <td>{{ $item->keterangan }}</td>
                            <td class="text-end text-danger fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-secondary mb-2 d-block"></i>
                                Belum ada data pengeluaran
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-active">
                        <tr>
                            <th colspan="3" class="text-end">Total Pengeluaran:</th>
                            <th class="text-end text-danger">Rp {{ number_format($saldoKeluar, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 20px;
    padding: 20px;
    transition: all 0.3s;
    border: 1px solid rgba(96, 165, 250, 0.1);
}

.stat-card:hover {
    transform: translateY(-5px);
    border-color: rgba(96, 165, 250, 0.3);
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.5);
}

.table-dark {
    background: transparent;
}

.table-dark th {
    border-bottom-color: rgba(96, 165, 250, 0.2);
    color: #94a3b8;
    font-weight: 500;
}

.table-dark td {
    border-bottom-color: rgba(96, 165, 250, 0.05);
    color: #f1f5f9;
}

.table-active {
    background: rgba(15, 23, 42, 0.8);
}
</style>
@endsection
