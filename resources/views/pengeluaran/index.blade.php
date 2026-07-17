@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-white">
                        <i class="fas fa-shopping-cart"></i> Manajemen Pengeluaran
                    </h2>
                    <p class="text-white-50">Kelola semua pengeluaran kas RW</p>
                </div>
                <div>
                    <a href="{{ route('pengeluaran.create') }}" class="btn btn-gradient">
                        <i class="fas fa-plus-circle"></i> Tambah Pengeluaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 text-white"><i class="fas fa-list"></i> Daftar Pengeluaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                            <th>Dicatat oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengeluaran as $item)
                        <tr>
                            <td class="text-white">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                            <td>
                                @if($item->kategori == 'Kebersihan')
                                    <span class="badge bg-success"><i class="fas fa-broom"></i> Kebersihan</span>
                                @elseif($item->kategori == 'Keamanan')
                                    <span class="badge bg-info"><i class="fas fa-shield-alt"></i> Keamanan</span>
                                @elseif($item->kategori == 'Sosial')
                                    <span class="badge bg-warning"><i class="fas fa-hand-holding-heart"></i> Sosial</span>
                                @elseif($item->kategori == 'Pembangunan')
                                    <span class="badge bg-primary"><i class="fas fa-hard-hat"></i> Pembangunan</span>
                                @elseif($item->kategori == 'Perlengkapan')
                                    <span class="badge bg-secondary"><i class="fas fa-box"></i> Perlengkapan</span>
                                @else
                                    <span class="badge bg-dark">{{ $item->kategori }}</span>
                                @endif
                            </td>
                            <td class="text-white">{{ Str::limit($item->keterangan, 50) }}</td>
                            <td class="text-danger fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td class="text-white">{{ $item->creator->name }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('pengeluaran.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete('{{ route('pengeluaran.destroy', $item->id) }}', 'pengeluaran')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-active">
                        <tr>
                            <th colspan="3" class="text-end text-white">Total Pengeluaran:</th>
                            <th class="text-end text-danger fw-bold">Rp {{ number_format($pengeluaran->sum('nominal'), 0, ',', '.') }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
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
.table-active {
    background: rgba(15, 23, 42, 0.8);
}
.btn-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
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
