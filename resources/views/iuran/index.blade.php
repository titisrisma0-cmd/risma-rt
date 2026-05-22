@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-white">
                        <i class="fas fa-money-bill-wave"></i> Manajemen Iuran
                    </h2>
                    <p class="text-white-50">Kelola jenis iuran, nominal, dan periode pembayaran</p>
                </div>
                <div>
                    <a href="{{ route('iuran.create') }}" class="btn btn-gradient">
                        <i class="fas fa-plus-circle"></i> Tambah Iuran Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($iuran as $item)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">{{ $item->nama_iuran }}</h5>
                    @if($item->status == 'aktif')
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> Aktif
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="fas fa-times-circle"></i> Nonaktif
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-secondary">Kode Iuran</small>
                        <h6 class="text-white">{{ $item->kode_iuran }}</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-secondary">Jenis Iuran</small>
                        <div>
                            @if($item->jenis_iuran == 'wajib')
                                <span class="badge bg-primary"><i class="fas fa-exclamation-circle"></i> Wajib</span>
                            @elseif($item->jenis_iuran == 'sukarela')
                                <span class="badge bg-info"><i class="fas fa-heart"></i> Sukarela</span>
                            @else
                                <span class="badge bg-warning"><i class="fas fa-star"></i> Khusus</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-secondary">Nominal</small>
                        <h4 class="text-primary">Rp {{ number_format($item->nominal, 0, ',', '.') }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-secondary">Periode</small>
                        <div class="text-white">
                            @if($item->periode == 'bulanan')
                                <i class="fas fa-calendar-alt"></i> Bulanan
                            @elseif($item->periode == 'triwulan')
                                <i class="fas fa-calendar-week"></i> Triwulan
                            @else
                                <i class="fas fa-calendar-year"></i> Tahunan
                            @endif
                        </div>
                    </div>
                    @if($item->keterangan)
                        <div class="mb-3">
                            <small class="text-secondary">Keterangan</small>
                            <p class="text-white mb-0">{{ $item->keterangan }}</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <div class="d-flex gap-2">
                        <a href="{{ route('iuran.edit', $item->id) }}" class="btn btn-warning flex-fill">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('iuran.toggle-status', $item->id) }}" method="POST" class="flex-fill">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-info w-100" onclick="return confirm('Ubah status iuran ini?')">
                                @if($item->status == 'aktif')
                                    <i class="fas fa-ban"></i> Nonaktifkan
                                @else
                                    <i class="fas fa-check"></i> Aktifkan
                                @endif
                            </button>
                        </form>
                        <form action="{{ route('iuran.destroy', $item->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Yakin hapus {{ $item->nama_iuran }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
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
.btn-group {
    display: flex;
    gap: 8px;
}
.flex-fill {
    flex: 1;
}
</style>
@endsection