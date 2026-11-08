@extends('layouts.app')

@section('title', 'Manajemen Iuran')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-money-bill-wave text-emerald me-2"></i> Manajemen Iuran
            </h1>
            <p class="page-subtitle">Kelola master tarif iuran, nominal, kategori, dan periode penagihan</p>
        </div>
        <div>
            <a href="{{ route('iuran.create') }}" class="btn btn-emerald text-white">
                <i class="fas fa-plus-circle me-1"></i> Tambah Iuran Baru
            </a>
        </div>
    </div>

    <!-- Bagian Iuran Ramadhan -->
    <div class="mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-moon text-emerald me-2"></i>Iuran Ramadhan</h5>
        <div class="row g-3">
            @forelse($iuranRamadhan as $item)
            <div class="col-md-6 col-lg-4">
                <div class="saas-card h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="fw-bold text-dark mb-0">{{ $item->nama_iuran }}</h6>
                            @if($item->status == 'aktif')
                                <span class="badge-soft-emerald">Aktif</span>
                            @else
                                <span class="badge-soft-danger">Nonaktif</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary small d-block">Kode Iuran</span>
                            <span class="font-monospace text-dark fw-semibold">{{ $item->kode_iuran }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary small d-block">Nominal Tarif</span>
                            <span class="h4 text-emerald fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge-soft-info">{{ ucfirst($item->periode) }}</span>
                            <span class="badge-soft-warning">{{ $item->kategori }}</span>
                        </div>
                    </div>
                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara'))
                        <div class="border-top pt-3 mt-3 d-flex gap-2">
                            <a href="{{ route('iuran.edit', $item->id) }}" class="btn btn-sm btn-outline-warning flex-fill">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('iuran.destroy', $item->id) }}" method="POST" class="flex-fill m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100" data-confirm="Hapus iuran {{ $item->nama_iuran }}?" data-confirm-title="Hapus Iuran" data-confirm-btn="Ya, Hapus">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="saas-card p-4 text-center text-secondary">
                    Tidak ada iuran Ramadhan yang terdaftar.
                </div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Bagian Iuran Non-Ramadhan -->
    <div class="mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-wallet text-emerald me-2"></i>Iuran Reguler & Kebersihan</h5>
        <div class="row g-3">
            @forelse($iuranNonRamadhan as $item)
            <div class="col-md-6 col-lg-4">
                <div class="saas-card h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="fw-bold text-dark mb-0">{{ $item->nama_iuran }}</h6>
                            @if($item->status == 'aktif')
                                <span class="badge-soft-emerald">Aktif</span>
                            @else
                                <span class="badge-soft-danger">Nonaktif</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary small d-block">Kode Iuran</span>
                            <span class="font-monospace text-dark fw-semibold">{{ $item->kode_iuran }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary small d-block">Nominal Tarif</span>
                            <span class="h4 text-emerald fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge-soft-info">{{ ucfirst($item->periode) }}</span>
                            <span class="badge-soft-secondary">{{ $item->kategori }}</span>
                        </div>
                    </div>
                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara'))
                        <div class="border-top pt-3 mt-3 d-flex gap-2">
                            <a href="{{ route('iuran.edit', $item->id) }}" class="btn btn-sm btn-outline-warning flex-fill">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('iuran.destroy', $item->id) }}" method="POST" class="flex-fill m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100" data-confirm="Hapus iuran {{ $item->nama_iuran }}?" data-confirm-title="Hapus Iuran" data-confirm-btn="Ya, Hapus">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="saas-card p-4 text-center text-secondary">
                    Tidak ada iuran reguler yang terdaftar.
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
