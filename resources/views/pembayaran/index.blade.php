@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-white">
                        <i class="fas fa-hand-holding-usd"></i> 
                        @if(Auth::user()->hasRole('warga'))
                            Riwayat Pembayaran Saya
                        @else
                            Manajemen Pembayaran
                        @endif
                    </h2>
                    <p class="text-white-50">
                        @if(Auth::user()->hasRole('warga'))
                            Lihat histori pembayaran iuran Anda
                        @else
                            Kelola dan verifikasi pembayaran iuran warga
                        @endif
                    </p>
                </div>
                <div>
                    @if(Auth::user()->hasRole('warga'))
                        <a href="{{ route('pembayaran.create') }}" class="btn btn-gradient">
                            <i class="fas fa-plus-circle"></i> Bayar Iuran
                        </a>
                    @else
                        <a href="{{ route('pembayaran.create') }}" class="btn btn-gradient">
                            <i class="fas fa-plus-circle"></i> Catat Pembayaran
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 text-white"><i class="fas fa-list"></i> Daftar Pembayaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            @if(!Auth::user()->hasRole('warga'))
                                <th>Warga</th>
                            @endif
                            <th>Jenis Iuran</th>
                            <th>Tanggal Bayar</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Bukti Transfer</th>
                            <th>Status</th>
                            @if(!Auth::user()->hasRole('warga'))
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayaran as $item)
                        <tr>
                            <td>
                                <strong class="text-white">{{ $item->no_transaksi }}</strong>
                                <br>
                                <small class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            @if(!Auth::user()->hasRole('warga'))
                                <td class="text-white">{{ $item->user->name }}</td>
                            @endif
                            <td class="text-white">{{ $item->iuran->nama_iuran }}</td>
                            <td class="text-white">{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                            <td class="text-success fw-bold">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                            <td>
                                @if($item->metode_bayar == 'tunai')
                                    <span class="badge bg-info">Tunai</span>
                                @else
                                    <span class="badge bg-primary">Transfer</span>
                                @endif
                            </td>
                            <td>
                                @if($item->bukti_transfer)
                                    <a href="{{ asset('storage/' . $item->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status == 'lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @elseif($item->status == 'pending')
                                    <span class="badge bg-warning">Menunggu Verifikasi</span>
                                @else
                                    <span class="badge bg-danger">Gagal</span>
                                @endif
                            </td>
                            @if(!Auth::user()->hasRole('warga'))
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($item->status == 'pending')
                                            <form action="{{ route('pembayaran.verifikasi', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Verifikasi pembayaran ini?')">
                                                    <i class="fas fa-check"></i> Verifikasi
                                                </button>
                                            </form>
                                        @endif
                                        <button onclick="confirmDelete('{{ route('pembayaran.destroy', $item->id) }}', 'pembayaran {{ $item->no_transaksi }}')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ Auth::user()->hasRole('warga') ? '8' : '9' }}" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-secondary mb-3 d-block"></i>
                                <p class="text-secondary">Belum ada data pembayaran</p>
                                @if(Auth::user()->hasRole('warga'))
                                    <a href="{{ route('pembayaran.create') }}" class="btn btn-gradient mt-2">
                                        <i class="fas fa-plus-circle"></i> Bayar Iuran Sekarang
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
.card-header {
    background: rgba(15, 23, 42, 0.5);
    border-bottom: 1px solid rgba(96, 165, 250, 0.1);
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
.btn-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
    transition: all 0.3s;
}
.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59,130,246,0.4);
    color: white;
}
.text-white {
    color: #f1f5f9 !important;
}
.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}
</style>
@endsection