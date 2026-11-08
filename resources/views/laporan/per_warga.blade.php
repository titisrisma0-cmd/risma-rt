@extends('layouts.app')

@section('title', 'Laporan Pembayaran Warga')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1 class="page-title mb-0">Laporan Pembayaran Warga</h1>
            </div>
            <p class="page-subtitle">
                Nama: <strong class="text-dark">{{ $user->name }}</strong> | 
                Periode: <strong class="text-emerald">{{ date('d/m/Y', strtotime($request->periode_awal)) }} – {{ date('d/m/Y', strtotime($request->periode_akhir)) }}</strong>
            </p>
        </div>
        <div>
            @if(request()->has('export_excel'))
                <a href="{{ route('laporan.index') }}" class="btn btn-emerald text-white">
                    <i class="fas fa-download me-1"></i> Download Excel
                </a>
            @endif
        </div>
    </div>

    <div class="saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt text-emerald me-2"></i> Detail Pembayaran Iuran</h6>
        </div>
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th width="45">No</th>
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
                        <td class="text-secondary">{{ $index + 1 }}</td>
                        <td class="text-secondary small">{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                        <td class="text-dark fw-medium">{{ $item->iuran->nama_iuran }}</td>
                        <td class="text-end text-emerald fw-bold">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                        <td>
                            @if($item->metode_bayar == 'tunai')
                                <span class="badge-soft-info">Tunai</span>
                            @else
                                <span class="badge-soft-primary">Transfer</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status == 'lunas')
                                <span class="badge-soft-emerald">Lunas</span>
                            @else
                                <span class="badge-soft-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                            Belum ada riwayat pembayaran pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($pembayaran->count() > 0)
                <tfoot class="bg-light border-top">
                    <tr>
                        <th colspan="3" class="text-end text-dark fw-bold">Total Pembayaran:</th>
                        <th class="text-end text-emerald fw-bold fs-6">Rp {{ number_format($pembayaran->sum('jumlah_bayar'), 0, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
