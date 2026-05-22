@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-file-alt"></i> Detail Laporan
            </h2>
        </div>
    </div>

    <div class="card animate__animated animate__fadeInUp">
        <div class="card-header bg-primary">
            <h5 class="mb-0 text-white">Laporan: {{ $laporan->kode_laporan }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="text-secondary" width="200">Jenis Laporan</th>
                            <td class="text-white">: {{ ucfirst($laporan->jenis_laporan) }}</td>
                        </tr>
                        <tr>
                            <th class="text-secondary">Periode</th>
                            <td class="text-white">: {{ date('d/m/Y', strtotime($laporan->periode_awal)) }} - {{ date('d/m/Y', strtotime($laporan->periode_akhir)) }}</td>
                        </tr>
                        <tr>
                            <th class="text-secondary">Total Pemasukan</th>
                            <td class="text-success">: Rp {{ number_format($laporan->total_pemasukan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="text-secondary">Total Pengeluaran</th>
                            <td class="text-danger">: Rp {{ number_format($laporan->total_pengeluaran, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="text-secondary">Saldo Akhir</th>
                            <td class="text-primary fw-bold">: Rp {{ number_format($laporan->saldo_akhir, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="text-secondary">Dibuat oleh</th>
                            <td class="text-white">: {{ $laporan->creator->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-secondary">Tanggal Buat</th>
                            <td class="text-white">: {{ $laporan->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="text-end mt-4">
                <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(96, 165, 250, 0.1);
}

.text-secondary {
    color: #94a3b8 !important;
}

.text-white {
    color: #f1f5f9 !important;
}
</style>
@endsection