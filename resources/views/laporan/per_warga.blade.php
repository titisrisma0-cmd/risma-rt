@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-user"></i> Laporan Pembayaran Warga
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Nama: <strong class="text-primary">{{ $user->name }}</strong> | 
                Periode: <strong class="text-info">{{ date('d/m/Y', strtotime($request->periode_awal)) }} - {{ date('d/m/Y', strtotime($request->periode_akhir)) }}</strong>
            </p>
        </div>
    </div>

    <div class="card animate__animated animate__fadeInUp">
        <div class="card-header bg-primary">
            <h5 class="mb-0 text-white"><i class="fas fa-receipt"></i> Detail Pembayaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Bayar</th>
                            <th>Jenis Iuran</th>
                            <th class="text-end">Jumlah Bayar</th>
                            <th>Metode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayaran as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                            <td>{{ $item->iuran->nama_iuran }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                            <td>
                                @if($item->metode_bayar == 'tunai')
                                    <span class="badge bg-info">Tunai</span>
                                @else
                                    <span class="badge bg-primary">Transfer</span>
                                @endif
                            </td>
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
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-secondary mb-2 d-block"></i>
                                <span class="text-secondary">Belum ada riwayat pembayaran</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-active">
                        <tr>
                            <th colspan="3" class="text-end">Total Pembayaran:</th>
                            <th class="text-end text-success fw-bold">Rp {{ number_format($pembayaran->sum('jumlah_bayar'), 0, ',', '.') }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent text-end">
            <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @if(request()->has('export_excel'))
                <a href="{{ route('laporan.index') }}" class="btn btn-success">
                    <i class="fas fa-download"></i> Download Excel
                </a>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(96, 165, 250, 0.1);
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

.text-white {
    color: #f1f5f9 !important;
}
</style>
@endsection