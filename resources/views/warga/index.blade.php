@extends('layouts.app')

@section('title', 'Data Warga')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users text-emerald me-2"></i> Data Warga
            </h1>
            <p class="page-subtitle">{{ Auth::user()->isAdminRt() ? 'Kelola warga RT Anda' : 'Kelola data seluruh warga per RT' }}</p>
        </div>
        @if(Auth::user()->isAdminRw() || Auth::user()->isAdminRt())
            <div>
                <a href="{{ route('warga.create') }}" class="btn btn-emerald text-white">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Warga
                </a>
            </div>
        @endif
    </div>

    <!-- FORM PENCARIAN -->
    <div class="saas-card p-4 mb-4">
        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-search text-emerald me-2"></i>Pencarian Warga</h6>
        <form method="GET" action="{{ route('warga.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control form-control-saas" placeholder="Cari nama..." value="{{ request('nama') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">Email</label>
                <input type="text" name="email" class="form-control form-control-saas" placeholder="Cari email..." value="{{ request('email') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">NIK</label>
                <input type="text" name="nik" class="form-control form-control-saas font-monospace" placeholder="Cari NIK..." value="{{ request('nik') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">No. KK</label>
                <input type="text" name="no_kk" class="form-control form-control-saas font-monospace" placeholder="Cari No. KK..." value="{{ request('no_kk') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">No. HP</label>
                <input type="text" name="no_hp" class="form-control form-control-saas" placeholder="Cari No. HP..." value="{{ request('no_hp') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">Status</label>
                <select name="status" class="form-select form-control-saas">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-emerald text-white px-4">
                    <i class="fas fa-search me-1"></i> Cari
                </button>
                <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
            <span class="text-secondary small">Total data: <strong class="text-dark">{{ $warga->total() }}</strong> warga</span>
            @if(request()->anyFilled(['nama', 'email', 'nik', 'no_kk', 'no_hp', 'status']))
                <span class="badge-soft-info">Menampilkan hasil pencarian</span>
            @endif
        </div>
    </div>

    <!-- DAFTAR DATA WARGA -->
    <div class="saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-emerald me-2"></i> Daftar Warga</h6>
        </div>
        <div class="p-3">
            <div class="row g-3">
                @forelse($warga as $index => $item)
                <div class="col-12">
                    <div class="saas-card p-3 border" style="background-color: #FAFCFA;">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-secondary me-2">{{ $warga->firstItem() + $index }}</span>
                                    <h6 class="mb-0 fw-bold text-dark fs-6">{{ $item->profile->nama_lengkap ?? $item->name }}</h6>
                                </div>
                                <div class="text-secondary small mb-2">{{ $item->email }}</div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-light text-secondary border">NIK: <span class="font-monospace text-dark">{{ $item->profile->nik ?? '-' }}</span></span>
                                    <span class="badge bg-light text-secondary border">No. KK: <span class="font-monospace text-dark">{{ $item->profile->no_kk ?? '-' }}</span></span>
                                    @if($item->profile?->no_kk)
                                        <span class="badge bg-light text-secondary border">Anggota KK: {{ $familyCounts[$item->profile->no_kk] ?? 0 }}</span>
                                    @endif
                                    <span class="badge bg-light text-secondary border">HP: {{ $item->profile->no_hp ?? '-' }}</span>
                                    <span class="badge-soft-info">RT {{ $item->profile->wilayahRt->kode_rt ?? '-' }}</span>
                                </div>
                                <div class="text-secondary small"><i class="fas fa-map-marker-alt text-muted me-1"></i> Alamat: {{ Str::limit($item->profile->alamat ?? '-', 80) }}</div>
                            </div>
                            <div class="text-md-end d-flex flex-column justify-content-between align-items-md-end">
                                <div class="mb-2">
                                    @if($item->profile && $item->profile->status == 'aktif')
                                        <span class="badge-soft-emerald">Aktif</span>
                                    @elseif($item->profile && $item->profile->status == 'pending')
                                        <span class="badge-soft-warning">Menunggu Verifikasi</span>
                                    @else
                                        <span class="badge-soft-danger">Nonaktif</span>
                                    @endif
                                </div>
                                @if(Auth::user()->isAdminRw() || Auth::user()->isAdminRt())
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($item->profile && $item->profile->status == 'pending')
                                            <form action="{{ route('warga.verify', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Verifikasi warga">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('warga.edit', $item->id) }}" class="btn btn-outline-warning" title="Edit warga">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick='confirmDelete(@json(route('warga.destroy', $item->id)), @json($item->name))' class="btn btn-outline-danger" title="Hapus warga">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                    <span class="text-secondary">Tidak ada data warga</span>
                    @if(request()->anyFilled(['nama', 'email', 'nik', 'no_kk', 'no_hp', 'status']))
                        <div class="mt-2">
                            <a href="{{ route('warga.index') }}" class="btn btn-sm btn-outline-secondary">Reset Pencarian</a>
                        </div>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
        @if($warga->hasPages())
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex justify-content-end">
                {{ $warga->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
