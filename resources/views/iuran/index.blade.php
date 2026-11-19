@extends('layouts.app')

@section('title', 'Manajemen Iuran')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">
                <i class="fas fa-money-bill-wave text-emerald me-2"></i> Manajemen Iuran
            </h1>
            <p class="page-subtitle mb-0">Kelola master tarif iuran, jenis kewajiban, nominal, kategori, dan periode penagihan warga</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(Auth::user()->isAdminRw() || Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara') || Auth::user()->isSuperAdmin())
                <a href="{{ route('rw.audit.index') }}#tab-iuran" class="btn btn-light border rounded-pill px-3">
                    <i class="fas fa-scale-balanced me-1 text-primary"></i> Keuangan Wilayah
                </a>
                <a href="{{ route('iuran.create') }}" class="btn btn-emerald text-white rounded-pill px-3 shadow-xs">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Iuran Baru
                </a>
            @endif
        </div>
    </div>

    @php
        $canManage = Auth::user()->isAdminRw() || Auth::user()->hasRole('admin') || Auth::user()->hasRole('bendahara') || Auth::user()->isSuperAdmin();
    @endphp

    <!-- Bagian Iuran Ramadhan -->
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-moon text-emerald me-2"></i>Iuran Ramadhan & Hari Raya</h5>
            <span class="badge-soft-amber px-3 py-1 rounded-full">{{ $iuranRamadhan->count() }} Pos Iuran</span>
        </div>
        <div class="row g-3">
            @forelse($iuranRamadhan as $item)
            <div class="col-md-6 col-lg-4">
                <div class="saas-card rounded-xl border h-100 p-4 d-flex flex-column justify-content-between shadow-xs">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 fs-6">{{ $item->nama_iuran }}</h6>
                            @if($item->status == 'aktif')
                                <span class="badge-soft-emerald px-3 py-1 rounded-full"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                            @else
                                <span class="badge-soft-secondary px-3 py-1 rounded-full"><i class="fas fa-ban me-1"></i> Nonaktif</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary small d-block">Kode Iuran</span>
                            <span class="font-monospace text-dark fw-semibold small">{{ $item->kode_iuran }}</span>
                        </div>
                        <div class="mb-3">
                            <span class="text-secondary small d-block">Nominal Tarif</span>
                            <span class="h4 text-emerald fw-bold mb-0">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @if($item->jenis_iuran === 'wajib')
                                <span class="badge-soft-rose px-3 py-1 rounded-full"><i class="fas fa-circle-exclamation me-1"></i> Wajib</span>
                            @elseif($item->jenis_iuran === 'sukarela')
                                <span class="badge-soft-sky px-3 py-1 rounded-full"><i class="fas fa-hand-holding-heart me-1"></i> Sukarela</span>
                            @else
                                <span class="badge-soft-amber px-3 py-1 rounded-full"><i class="fas fa-star me-1"></i> Khusus</span>
                            @endif
                            <span class="badge-soft-sky px-3 py-1 rounded-full">{{ ucfirst($item->periode) }}</span>
                            <span class="badge-soft-secondary px-3 py-1 rounded-full">{{ $item->kategori ?: 'Ramadhan' }}</span>
                        </div>
                        @if($item->keterangan)
                            <p class="text-secondary small mt-2 mb-0">{{ Str::limit($item->keterangan, 90) }}</p>
                        @endif
                    </div>
                    @if($canManage)
                        <div class="border-top pt-3 mt-3 d-flex flex-wrap gap-2">
                            <form action="{{ route('iuran.toggle-status', $item->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $item->status === 'aktif' ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-2.5" title="{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas {{ $item->status === 'aktif' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('iuran.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-fill text-center">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('iuran.destroy', $item->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" data-confirm="Hapus atau nonaktifkan iuran {{ $item->nama_iuran }}? Data pembayaran warga akan tetap aman." data-confirm-title="Hapus / Nonaktifkan Iuran" data-confirm-btn="Ya, Lanjutkan" title="Hapus atau nonaktifkan iuran">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="saas-card rounded-xl p-4 text-center text-secondary border">
                    Tidak ada iuran Ramadhan yang terdaftar.
                </div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Bagian Iuran Non-Ramadhan -->
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-wallet text-emerald me-2"></i>Iuran Reguler & Kebersihan</h5>
            <span class="badge-soft-sky px-3 py-1 rounded-full">{{ $iuranNonRamadhan->count() }} Pos Iuran</span>
        </div>
        <div class="row g-3">
            @forelse($iuranNonRamadhan as $item)
            <div class="col-md-6 col-lg-4">
                <div class="saas-card rounded-xl border h-100 p-4 d-flex flex-column justify-content-between shadow-xs">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 fs-6">{{ $item->nama_iuran }}</h6>
                            @if($item->status == 'aktif')
                                <span class="badge-soft-emerald px-3 py-1 rounded-full"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                            @else
                                <span class="badge-soft-secondary px-3 py-1 rounded-full"><i class="fas fa-ban me-1"></i> Nonaktif</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary small d-block">Kode Iuran</span>
                            <span class="font-monospace text-dark fw-semibold small">{{ $item->kode_iuran }}</span>
                        </div>
                        <div class="mb-3">
                            <span class="text-secondary small d-block">Nominal Tarif</span>
                            <span class="h4 text-emerald fw-bold mb-0">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @if($item->jenis_iuran === 'wajib')
                                <span class="badge-soft-rose px-3 py-1 rounded-full"><i class="fas fa-circle-exclamation me-1"></i> Wajib</span>
                            @elseif($item->jenis_iuran === 'sukarela')
                                <span class="badge-soft-sky px-3 py-1 rounded-full"><i class="fas fa-hand-holding-heart me-1"></i> Sukarela</span>
                            @else
                                <span class="badge-soft-amber px-3 py-1 rounded-full"><i class="fas fa-star me-1"></i> Khusus</span>
                            @endif
                            <span class="badge-soft-sky px-3 py-1 rounded-full">{{ ucfirst($item->periode) }}</span>
                            <span class="badge-soft-secondary px-3 py-1 rounded-full">{{ $item->kategori ?: 'Reguler' }}</span>
                        </div>
                        @if($item->keterangan)
                            <p class="text-secondary small mt-2 mb-0">{{ Str::limit($item->keterangan, 90) }}</p>
                        @endif
                    </div>
                    @if($canManage)
                        <div class="border-top pt-3 mt-3 d-flex flex-wrap gap-2">
                            <form action="{{ route('iuran.toggle-status', $item->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $item->status === 'aktif' ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-2.5" title="{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas {{ $item->status === 'aktif' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('iuran.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-fill text-center">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('iuran.destroy', $item->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" data-confirm="Hapus atau nonaktifkan iuran {{ $item->nama_iuran }}? Data pembayaran warga akan tetap aman." data-confirm-title="Hapus / Nonaktifkan Iuran" data-confirm-btn="Ya, Lanjutkan" title="Hapus atau nonaktifkan iuran">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="saas-card rounded-xl p-4 text-center text-secondary border">
                    Tidak ada iuran reguler yang terdaftar.
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
