@extends('layouts.app')

@section('title', 'Data Warga')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title d-flex align-items-center gap-2">
                <i class="fas fa-users text-emerald"></i>
                <span>Data Warga</span>
            </h1>
            <p class="page-subtitle mb-0">
                {{ Auth::user()->isAdminRt() ? 'Kelola data kependudukan dan status akun warga di RT Anda' : 'Kelola dan pantau seluruh data kependudukan warga RW 05 per RT' }}
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(Auth::user()->isAdminRt() || Auth::user()->isAdminRw())
                <a href="{{ route('warga.verifikasi.index') }}" class="btn btn-outline-emerald position-relative">
                    <i class="fas fa-user-check me-1"></i> Verifikasi Warga
                    @php
                        $pendingCount = \App\Models\UserProfile::where('status', 'pending')
                            ->when(Auth::user()->isAdminRt(), fn($q) => $q->where('rt_id', Auth::user()->managedRtId()))
                            ->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('warga.create') }}" class="btn btn-emerald text-white">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Warga
                </a>
            @endif
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert-saas alert-saas-success mb-4">
            <i class="fas fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert-saas alert-saas-danger mb-4">
            <i class="fas fa-circle-exclamation fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- FORM PENCARIAN & FILTER -->
    <div class="saas-card p-4 mb-4">
        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-search text-emerald me-2"></i>Pencarian & Filter Warga</h6>
        <form method="GET" action="{{ route('warga.index') }}" class="row g-3">
            @if(Auth::user()->isAdminRw() || Auth::user()->hasRole('bendahara'))
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-medium">Wilayah RT</label>
                    <select name="rt_id" class="form-select form-control-saas">
                        <option value="">Semua Wilayah RT</option>
                        @foreach($rts as $rtOpt)
                            <option value="{{ $rtOpt->id }}" {{ request('rt_id') == $rtOpt->id ? 'selected' : '' }}>
                                RT {{ $rtOpt->kode_rt }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control form-control-saas" placeholder="Cari nama..." value="{{ request('nama') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">Email Akun</label>
                <input type="text" name="email" class="form-control form-control-saas" placeholder="Cari email..." value="{{ request('email') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">NIK</label>
                <input type="text" name="nik" class="form-control form-control-saas font-monospace" placeholder="16 digit NIK..." value="{{ request('nik') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">No. KK</label>
                <input type="text" name="no_kk" class="form-control form-control-saas font-monospace" placeholder="16 digit No. KK..." value="{{ request('no_kk') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">No. HP / WA</label>
                <input type="text" name="no_hp" class="form-control form-control-saas" placeholder="08xxxxxxxxxx" value="{{ request('no_hp') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-medium">Status Akun / Warga</label>
                <select name="status" class="form-select form-control-saas">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif (Sudah Berakun)</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending (Menunggu Verifikasi)</option>
                    <option value="belum_ada_akun" {{ request('status') == 'belum_ada_akun' ? 'selected' : '' }}>Belum Ada Akun (Hanya Kependudukan)</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Pendaftaran Ditolak</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-emerald text-white px-4">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
            <span class="text-secondary small">Total data: <strong class="text-dark">{{ $warga->total() }}</strong> warga kependudukan</span>
            @if(request()->anyFilled(['rt_id', 'nama', 'email', 'nik', 'no_kk', 'no_hp', 'status']))
                <span class="badge-soft-info">Menampilkan hasil filter pencarian</span>
            @endif
        </div>
    </div>

    <!-- DAFTAR DATA WARGA -->
    <div class="saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-emerald me-2"></i> Daftar Warga Kependudukan</h6>
        </div>
        <div class="p-3">
            <div class="row g-3">
                @forelse($warga as $index => $item)
                @php
                    $targetId = $item->user_id ?? $item->id;
                    $hasAccount = (bool) $item->user_id;
                @endphp
                <div class="col-12">
                    <div class="saas-card p-3 border" style="background-color: #FAFCFA;">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-secondary">{{ $warga->firstItem() + $index }}</span>
                                    <h6 class="mb-0 fw-bold text-dark fs-6">{{ $item->nama_lengkap ?? optional($item->user)->name }}</h6>
                                    @if($hasAccount)
                                        <span class="badge bg-light text-success border"><i class="fas fa-envelope me-1"></i>{{ $item->user->email }}</span>
                                    @else
                                        <span class="badge bg-light text-secondary border"><i class="fas fa-user-clock me-1"></i>Belum Ada Akun</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2 mt-2">
                                    <span class="badge bg-light text-secondary border">NIK: <span class="font-monospace text-dark">{{ $item->nik ?? '-' }}</span></span>
                                    <span class="badge bg-light text-secondary border">No. KK: <span class="font-monospace text-dark">{{ $item->no_kk ?? '-' }}</span></span>
                                    @if($item->no_kk)
                                        <span class="badge bg-light text-secondary border">Anggota KK: {{ $familyCounts[$item->no_kk] ?? 1 }} jiwa</span>
                                    @endif
                                    <span class="badge bg-light text-secondary border">HP: {{ $item->no_hp ?? '-' }}</span>
                                    <span class="badge-soft-info">RT {{ $item->wilayahRt->kode_rt ?? str_pad($item->rt_id, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="badge bg-light text-secondary border">
                                        <i class="fas {{ $item->jenis_kelamin === 'L' ? 'fa-mars text-primary' : 'fa-venus text-danger' }} me-1"></i>
                                        {{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                    @if($item->jenis_kelamin === 'L')
                                        @if($item->siap_ronda)
                                            <span class="badge bg-light text-emerald border"><i class="fas fa-shield-alt me-1"></i>Siap Ronda</span>
                                        @else
                                            <span class="badge bg-light text-warning border" title="{{ $item->alasan_pengecualian_ronda }}"><i class="fas fa-ban me-1"></i>Bebas Ronda</span>
                                        @endif
                                    @endif
                                </div>
                                <div class="text-secondary small"><i class="fas fa-map-marker-alt text-muted me-1"></i> Alamat: {{ Str::limit($item->alamat ?? '-', 90) }}</div>
                            </div>
                            <div class="text-md-end d-flex flex-column justify-content-between align-items-md-end">
                                <div class="mb-2">
                                    @if(!$hasAccount)
                                        <span class="badge bg-light text-secondary border"><i class="fas fa-id-badge me-1"></i> Belum Ada Akun</span>
                                    @elseif($item->status == 'aktif')
                                        <span class="badge-soft-emerald"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                                    @elseif($item->status == 'pending')
                                        <span class="badge-soft-warning"><i class="fas fa-clock me-1"></i> Menunggu Verifikasi</span>
                                    @elseif($item->status == 'ditolak')
                                        <span class="badge-soft-danger"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
                                    @else
                                        <span class="badge-soft-danger">Nonaktif</span>
                                    @endif
                                </div>
                                @if(Auth::user()->isAdminRw() || Auth::user()->isAdminRt())
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($item->status == 'pending')
                                            <form action="{{ route('warga.verify', $targetId) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Verifikasi warga">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('warga.edit', $targetId) }}" class="btn btn-outline-warning" title="Edit data warga">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick='confirmDelete(@json(route('warga.destroy', $targetId)), @json($item->nama_lengkap ?? optional($item->user)->name))' class="btn btn-outline-danger" title="Hapus warga">
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
                    <span class="text-secondary">Tidak ada data warga ditemukan</span>
                    @if(request()->anyFilled(['rt_id', 'nama', 'email', 'nik', 'no_kk', 'no_hp', 'status']))
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
