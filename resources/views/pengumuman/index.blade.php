@extends('layouts.app')

@section('title', 'Pengumuman Resmi RW')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    @php
        $pageTitle = 'Pengumuman Resmi RW';
        $rtLabel = '';
        if (($scope ?? '') === 'rt') {
            $rtKode = $user->isAdminRt() ? ($user->getRtKode() ?? '01') : (optional(optional($user->profile)->wilayahRt)->kode_rt ?? optional($user->profile)->rt ?? '01');
            $pageTitle = 'Pengumuman RT ' . $rtKode;
            $rtLabel = 'RT ' . $rtKode;
        } elseif (($scope ?? '') === 'rw') {
            $pageTitle = 'Pengumuman Resmi RW';
            $rtLabel = 'RW 01';
        } else {
            $pageTitle = $user->isAdminRt() ? 'Pengumuman RT & RW' : ($user->isWarga() ? 'Pengumuman Lingkungan' : 'Pengumuman Resmi RW');
            $rtLabel = $user->isAdminRt() ? 'RT ' . ($user->getRtKode() ?? '01') : ($user->isWarga() ? 'Warga' : 'Pengurus RW');
        }
    @endphp
    <header class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center flex-wrap gap-2 gap-sm-3">
            <div class="page-head-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0">{{ $pageTitle }}</h1>
                    <span class="badge-rt-pill" style="position: static !important; display: inline-flex !important;">{{ $rtLabel }}</span>
                </div>
                <p class="page-subtitle mb-0">Pusat warta informasi publik, surat edaran resmi, dan instruksi koordinasi pengurus wilayah.</p>
            </div>
        </div>

        <div class="page-header-actions">
            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <a href="{{ route('pengumuman.create') }}" class="btn btn-saas-primary">
                    <i class="fas fa-plus me-1"></i> Buat Pengumuman Baru
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-light border">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-saas alert-saas-success mb-4">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="form-card saas-card p-4 mb-4">
        <form method="GET" action="{{ $user->isWarga() ? route('warga.pengumuman') : ($user->isAdminRt() ? route('rt.informasi.pengumuman') : route('pengumuman.index')) }}" class="row g-3 align-items-end">
            @if(!empty($scope))
                <input type="hidden" name="scope" value="{{ $scope }}">
            @endif
            <div class="col-12 col-md-{{ ($user->isAdminRw() || $user->hasRole('bendahara')) ? '4' : '6' }}">
                <div class="d-flex flex-column gap-2.5">
                    <label for="filterKategoriPengumuman" class="form-label-saas fw-semibold mb-0 text-dark">Kategori Pengumuman</label>
                    <select name="kategori" id="filterKategoriPengumuman" class="form-select form-select-saas rounded-xl border border-slate-200 bg-white px-3.5 py-2 appearance-none" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="Umum" {{ request('kategori') == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Keamanan" {{ request('kategori') == 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                        <option value="Kerja Bakti" {{ request('kategori') == 'Kerja Bakti' ? 'selected' : '' }}>Kerja Bakti</option>
                        <option value="Iuran" {{ request('kategori') == 'Iuran' ? 'selected' : '' }}>Iuran & Keuangan</option>
                        <option value="Darurat" {{ request('kategori') == 'Darurat' ? 'selected' : '' }}>Darurat / Penting</option>
                    </select>
                </div>
            </div>

            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <div class="col-12 col-md-4">
                    <div class="d-flex flex-column gap-2.5">
                        <label for="filterStatusPengumuman" class="form-label-saas fw-semibold mb-0 text-dark">Status Publikasi</label>
                        <select name="status" id="filterStatusPengumuman" class="form-select form-select-saas rounded-xl border border-slate-200 bg-white px-3.5 py-2 appearance-none" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="arsip" {{ request('status') == 'arsip' ? 'selected' : '' }}>Diarsipkan</option>
                        </select>
                    </div>
                </div>
            @endif

            <div class="col-12 col-md-{{ ($user->isAdminRw() || $user->hasRole('bendahara')) ? '4' : '6' }} d-flex align-items-end">
                @php
                    $resetRoute = $user->isWarga() ? route('warga.pengumuman', !empty($scope) ? ['scope' => $scope] : []) : ($user->isAdminRt() ? route('rt.informasi.pengumuman') : route('pengumuman.index'));
                @endphp
                <a href="{{ $resetRoute }}" class="btn btn-outline-saas w-100 rounded-xl" style="min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fas fa-redo me-1"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>

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
        <div class="mt-4 d-flex justify-content-center">
            {{ $pengumumans->links() }}
        </div>
    @endif
</div>
@endsection
