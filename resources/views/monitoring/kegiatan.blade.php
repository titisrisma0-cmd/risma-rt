@extends('layouts.app')

@section('title', 'Monitoring Kegiatan RT — Pengurus RW')

@section('content')
<div class="py-2">
    {{-- Page Header --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Monitoring Wilayah</span>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Kegiatan RT</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Monitoring Kegiatan RT & RW
            </h1>
            <p class="text-secondary mb-0">Pemantauan agenda kemasyarakatan, rapat warga, dan aktivitas gotong royong seluruh wilayah.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('kegiatan.index') }}" class="btn btn-emerald">
                <i class="fas fa-plus me-1"></i> Buat Agenda RW
            </a>
        </div>
    </div>

    {{-- Filter Wilayah RT --}}
    <div class="saas-card p-3 mb-4">
        <form method="GET" action="{{ route('rw.monitoring.kegiatan') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-secondary small fw-semibold text-nowrap">Filter RT:</label>
                    <select name="rt_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Wilayah RT & RW</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if(request('rt_id'))
                <div class="col-md-2">
                    <a href="{{ route('rw.monitoring.kegiatan') }}" class="btn btn-sm btn-light">Reset Filter</a>
                </div>
            @endif
        </form>
    </div>

    {{-- Daftar Kegiatan --}}
    <div class="row g-3">
        @forelse($kegiatans as $k)
            <div class="col-md-6 col-xl-4">
                <div class="saas-card p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge {{ $k->cakupan === 'rw' ? 'badge-soft-primary' : 'badge-soft-info' }}">
                            {{ $k->cakupan === 'rw' ? 'Tingkat RW' : 'RT ' . optional($k->rt)->kode_rt }}
                        </span>
                        <small class="text-secondary font-monospace" style="font-size: 0.75rem;">
                            {{ optional($k->tanggal ?? $k->tanggal_mulai)->format('d M Y') }}
                        </small>
                    </div>

                    <h5 class="text-dark fw-bold mb-2">{{ $k->nama_kegiatan ?? $k->judul }}</h5>
                    <p class="text-secondary small mb-3 flex-grow-1" style="font-size: 0.825rem;">{{ Str::limit($k->deskripsi, 90) }}</p>

                    <div class="border-top pt-2 mt-auto d-flex justify-content-between align-items-center">
                        <small class="text-secondary"><i class="fas fa-map-pin text-danger me-1"></i>{{ $k->lokasi ?? 'Wilayah RT' }}</small>
                        <span class="badge badge-soft-primary">{{ $k->peserta_count ?? 0 }} Peserta</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state-saas p-5">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h5 class="empty-state-title">Belum Ada Agenda Kegiatan</h5>
                    <p class="empty-state-desc">Belum ada kegiatan yang tercatat untuk wilayah yang dipilih.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($kegiatans->hasPages())
        <div class="p-3 mt-4">
            {{ $kegiatans->links() }}
        </div>
    @endif
</div>
@endsection
