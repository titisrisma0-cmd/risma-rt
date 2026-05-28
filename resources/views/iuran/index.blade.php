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

    <!-- Bagian Iuran Ramadhan -->
    <h3 class="text-white">Iuran Ramadhan</h3>
    <div class="row">
        @foreach($iuranRamadhan as $item)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">{{ $item->nama_iuran }}</h5>
                    @if($item->status == 'aktif')
                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Aktif</span>
                    @else
                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Nonaktif</span>
                    @endif
                </div>
                <div class="card-body">
                    <small class="text-secondary">Kode Iuran</small>
                    <h6 class="text-white">{{ $item->kode_iuran }}</h6>
                    <small class="text-secondary">Nominal</small>
                    <h4 class="text-primary">Rp {{ number_format($item->nominal, 0, ',', '.') }}</h4>
                    <small class="text-secondary">Periode</small>
                    <p class="text-white">{{ ucfirst($item->periode) }}</p>
                    <small class="text-secondary">Kategori</small>
                    <p class="text-info">{{ $item->kategori }}</p>
                </div>
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara'))
                    <div class="card-footer bg-transparent border-top-0 d-flex gap-2">
                        <a href="{{ route('iuran.edit', $item->id) }}" class="btn btn-sm btn-outline-info flex-fill">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <form action="{{ route('iuran.destroy', $item->id) }}" method="POST" class="flex-fill m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Hapus iuran {{ $item->nama_iuran }}?');">
                                <i class="fas fa-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Bagian Iuran Non-Ramadhan -->
    <h3 class="text-white mt-4">Iuran Non-Ramadhan</h3>
    <div class="row">
        @foreach($iuranNonRamadhan as $item)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">{{ $item->nama_iuran }}</h5>
                    @if($item->status == 'aktif')
                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Aktif</span>
                    @else
                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Nonaktif</span>
                    @endif
                </div>
                <div class="card-body">
                    <small class="text-secondary">Kode Iuran</small>
                    <h6 class="text-white">{{ $item->kode_iuran }}</h6>
                    <small class="text-secondary">Nominal</small>
                    <h4 class="text-primary">Rp {{ number_format($item->nominal, 0, ',', '.') }}</h4>
                    <small class="text-secondary">Periode</small>
                    <p class="text-white">{{ ucfirst($item->periode) }}</p>
                    <small class="text-secondary">Kategori</small>
                    <p class="text-info">{{ $item->kategori }}</p>
                </div>
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara'))
                    <div class="card-footer bg-transparent border-top-0 d-flex gap-2">
                        <a href="{{ route('iuran.edit', $item->id) }}" class="btn btn-sm btn-outline-info flex-fill">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <form action="{{ route('iuran.destroy', $item->id) }}" method="POST" class="flex-fill m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Hapus iuran {{ $item->nama_iuran }}?');">
                                <i class="fas fa-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                @endif
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
</style>
@endsection
