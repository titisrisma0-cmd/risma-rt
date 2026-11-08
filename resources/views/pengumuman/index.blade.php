@extends('layouts.app')

@section('title', 'Pengumuman Resmi RW')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pengumuman RW</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-bullhorn text-success me-2"></i>Pengumuman Resmi RW</h1>
            <p class="page-subtitle">Pusat warta informasi publik, surat edaran resmi, dan instruksi koordinasi pengurus wilayah.</p>
        </div>

        @if($user->isAdminRw() || $user->hasRole('bendahara'))
            <div>
                <a href="{{ route('pengumuman.create') }}" class="btn btn-saas-primary">
                    <i class="fas fa-plus me-1"></i> Buat Pengumuman Baru
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-saas alert-saas-success mb-4">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    @if($user->isAdminRw() || $user->hasRole('bendahara'))
        {{-- Filter Card untuk Pengurus RW --}}
        <div class="saas-card p-4 mb-4">
            <form method="GET" action="{{ route('pengumuman.index') }}" class="row g-3 align-items-center">
                <div class="col-12 col-md-4">
                    <label class="form-label-saas mb-1">Kategori Pengumuman</label>
                    <select name="kategori" class="form-select-saas" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="Umum" {{ request('kategori') == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Keamanan" {{ request('kategori') == 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                        <option value="Kerja Bakti" {{ request('kategori') == 'Kerja Bakti' ? 'selected' : '' }}>Kerja Bakti</option>
                        <option value="Iuran" {{ request('kategori') == 'Iuran' ? 'selected' : '' }}>Iuran & Keuangan</option>
                        <option value="Darurat" {{ request('kategori') == 'Darurat' ? 'selected' : '' }}>Darurat / Penting</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label-saas mb-1">Status Publikasi</label>
                    <select name="status" class="form-select-saas" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="arsip" {{ request('status') == 'arsip' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 d-flex align-items-end">
                    @if(request()->hasAny(['kategori', 'status']))
                        <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-saas w-100">
                            <i class="fas fa-redo me-1"></i> Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    {{-- Grid Pengumuman --}}
    <div class="row g-4">
        @forelse($pengumumans as $p)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="saas-card p-4 h-100 d-flex flex-column justify-content-between border">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge {{ $p->kategori === 'Darurat' ? 'badge-soft-danger' : ($p->kategori === 'Keamanan' ? 'badge-soft-warning' : 'badge-soft-primary') }}">
                                {{ $p->kategori }}
                            </span>
                            <span class="badge badge-soft-secondary font-monospace small">
                                {{ optional($p->tanggal_publikasi)->format('d M Y') }}
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-2">{{ $p->judul }}</h5>
                        <p class="text-secondary small mb-3" style="line-height: 1.5;">
                            {{ Str::limit($p->isi, 120) }}
                        </p>
                    </div>

                    <div>
                        <div class="p-2 rounded-3 mb-3 bg-light border small">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary"><i class="fas fa-bullseye me-1 text-success"></i>{{ $p->target_label }}</span>
                                <span class="badge {{ $p->status === 'published' ? 'badge-soft-success' : 'badge-soft-secondary' }} small">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('pengumuman.show', $p) }}" class="btn btn-outline-saas btn-sm text-primary">
                                <i class="fas fa-eye me-1"></i> Baca Lengkap
                            </a>

                            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                                <div class="d-flex gap-1">
                                    <a href="{{ route('pengumuman.edit', $p) }}" class="btn btn-outline-saas btn-sm text-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('pengumuman.destroy', $p) }}" data-confirm="Hapus pengumuman ini secara permanen?" data-confirm-title="Hapus Pengumuman" data-confirm-btn="Ya, Hapus" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-saas btn-sm text-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="saas-card p-5 text-center text-secondary">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-bullhorn"></i></div>
                        <h6 class="empty-state-title">Belum Ada Pengumuman</h6>
                        <p class="empty-state-desc">Belum ada pengumuman resmi yang diterbitkan saat ini.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($pengumumans->hasPages())
        <div class="mt-4">
            {{ $pengumumans->links() }}
        </div>
    @endif
</div>
@endsection
