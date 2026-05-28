@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-white">
                        <i class="fas fa-users"></i> Data Warga
                    </h2>
                    <p class="text-white-50">Kelola data seluruh warga RT</p>
                </div>
                <div>
                    <a href="{{ route('warga.create') }}" class="btn btn-gradient">
                        <i class="fas fa-plus-circle"></i> Tambah Warga
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM PENCARIAN -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 text-white"><i class="fas fa-search"></i> Pencarian Warga</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('warga.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-white">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama..." value="{{ request('nama') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white">Email</label>
                    <input type="text" name="email" class="form-control bg-dark text-white border-secondary" placeholder="Cari email..." value="{{ request('email') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white">NIK</label>
                    <input type="text" name="nik" class="form-control bg-dark text-white border-secondary" placeholder="Cari NIK..." value="{{ request('nik') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white">No. KK</label>
                    <input type="text" name="no_kk" class="form-control bg-dark text-white border-secondary" placeholder="Cari No. KK..." value="{{ request('no_kk') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white">No. HP</label>
                    <input type="text" name="no_hp" class="form-control bg-dark text-white border-secondary" placeholder="Cari No. HP..." value="{{ request('no_hp') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-white">Status</label>
                    <select name="status" class="form-select bg-dark text-white border-secondary">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="{{ route('warga.index') }}" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-white">Total data: <strong>{{ $warga->total() }}</strong> warga</span>
                @if(request()->anyFilled(['nama', 'email', 'nik', 'no_kk', 'no_hp', 'status']))
                    <span class="text-info">Menampilkan hasil pencarian</span>
                @endif
            </div>
        </div>
    </div>

    <!-- DAFTAR DATA WARGA -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 text-white"><i class="fas fa-list"></i> Daftar Warga</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($warga as $index => $item)
                <div class="col-12">
                    <div class="card warga-card bg-dark border-secondary">
                        <div class="card-body py-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                <div class="warga-info">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">{{ $warga->firstItem() + $index }}</span>
                                        <h5 class="mb-0 text-white">{{ $item->profile->nama_lengkap ?? $item->name }}</h5>
                                    </div>
                                    <div class="text-white-50 mb-1">{{ $item->email }}</div>
                                    <div class="d-flex flex-wrap gap-2 mb-1">
                                        <span class="warga-meta">NIK: {{ $item->profile->nik ?? '-' }}</span>
                                        <span class="warga-meta">No. KK: {{ $item->profile->no_kk ?? '-' }}</span>
                                        <span class="warga-meta">HP: {{ $item->profile->no_hp ?? '-' }}</span>
                                    </div>
                                    <div class="text-white-75">Alamat: {{ Str::limit($item->profile->alamat ?? '-', 80) }}</div>
                                </div>
                                <div class="warga-actions text-md-end">
                                    <div class="mb-2">
                                        @if($item->profile && $item->profile->status == 'aktif')
                                            <span class="badge bg-success">Aktif</span>
                                        @elseif($item->profile && $item->profile->status == 'pending')
                                            <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                                        @else
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div class="btn-group" role="group">
                                        @if($item->profile && $item->profile->status == 'pending')
                                            <form action="{{ route('warga.verify', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Verifikasi warga">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('warga.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete('{{ route('warga.destroy', $item->id) }}', '{{ $item->name }}')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-4">
                    <i class="fas fa-inbox fa-2x text-secondary mb-2 d-block"></i>
                    <span class="text-secondary">Tidak ada data warga</span>
                    @if(request()->anyFilled(['nama', 'email', 'nik', 'no_kk', 'no_hp', 'status']))
                        <div class="mt-2">
                            <a href="{{ route('warga.index') }}" class="btn btn-sm btn-outline-primary">Reset Pencarian</a>
                        </div>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
        @if($warga->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-end">
                {{ $warga->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
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
.warga-card {
    border-radius: 1rem;
    border: 1px solid rgba(148, 163, 184, 0.15);
}
.warga-meta {
    display: inline-block;
    padding: 0.275rem 0.55rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.12);
    border-radius: 0.75rem;
    color: #cbd5e1;
    font-size: 0.85rem;
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
.pagination {
    --bs-pagination-color: #60a5fa;
    --bs-pagination-hover-color: #fff;
    --bs-pagination-hover-bg: #3b82f6;
    --bs-pagination-active-bg: #3b82f6;
    --bs-pagination-active-border-color: #3b82f6;
}
</style>
@endsection
