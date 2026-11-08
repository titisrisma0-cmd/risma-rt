@extends('layouts.app')

@section('title', 'Detail Laporan ' . $laporan->kode_laporan)

@section('content')
<div class="content-container" style="max-width: 860px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-file-alt text-emerald me-2"></i> Detail Laporan: {{ $laporan->kode_laporan }}
            </h1>
            <p class="page-subtitle">Rincian data ikhtisar laporan yang telah digenerate</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <tbody>
                    <tr class="border-bottom">
                        <th class="text-secondary py-3" width="220">Kode Laporan</th>
                        <td class="text-emerald font-monospace fw-bold py-3">{{ $laporan->kode_laporan }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-secondary py-3">Jenis Laporan</th>
                        <td class="text-dark fw-medium py-3">{{ ucwords(str_replace('_', ' ', $laporan->jenis_laporan)) }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-secondary py-3">Periode</th>
                        <td class="text-dark py-3">{{ date('d/m/Y', strtotime($laporan->periode_awal)) }} – {{ date('d/m/Y', strtotime($laporan->periode_akhir)) }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-secondary py-3">Total Pemasukan</th>
                        <td class="text-emerald fw-bold font-monospace py-3">Rp {{ number_format($laporan->total_pemasukan, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-secondary py-3">Total Pengeluaran</th>
                        <td class="text-danger fw-bold font-monospace py-3">Rp {{ number_format($laporan->total_pengeluaran, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-secondary py-3">Saldo Akhir</th>
                        <td class="text-dark fw-bold font-monospace fs-5 py-3">Rp {{ number_format($laporan->saldo_akhir, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-secondary py-3">Dibuat oleh</th>
                        <td class="text-dark py-3">{{ optional($laporan->creator)->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary py-3">Waktu Pembuatan</th>
                        <td class="text-secondary py-3">{{ $laporan->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end border-top pt-3 mt-3">
            <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
        </div>
    </div>
</div>
@endsection
