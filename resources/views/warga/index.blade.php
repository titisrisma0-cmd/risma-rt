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

    <!-- TABEL DATA WARGA -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 text-white"><i class="fas fa-list"></i> Daftar Warga</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>NIK</th>
                            <th>No. KK</th>
                            <th>No. HP</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warga as $index => $item)
                        <tr>
                            <td>{{ $warga->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong class="text-white">{{ $item->profile->nama_lengkap ?? $item->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->profile->nik ?? '-' }}</td>
                            <td>{{ $item->profile->no_kk ?? '-' }}</td>
                            <td>{{ $item->profile->no_hp ?? '-' }}</td>
                            <td>{{ Str::limit($item->profile->alamat ?? '-', 30) }}</td>
                            <td>
                                @if($item->profile && $item->profile->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('warga.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete('{{ route('warga.destroy', $item->id) }}', '{{ $item->name }}')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-secondary mb-2 d-block"></i>
                                <span class="text-secondary">Tidak ada data warga</span>
                                @if(request()->anyFilled(['nama', 'email', 'nik', 'no_kk', 'no_hp', 'status']))
                                    <div class="mt-2">
                                        <a href="{{ route('warga.index') }}" class="btn btn-sm btn-outline-primary">Reset Pencarian</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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