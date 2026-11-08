@extends('layouts.app')

@section('title', $pengumuman->judul)

@section('content')
<div class="content-container" style="max-width: 900px;">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pengumuman.index') }}">Pengumuman</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Pengumuman</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-bullhorn text-success me-2"></i>Pengumuman Resmi RW</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <a href="{{ route('pengumuman.edit', $pengumuman) }}" class="btn btn-saas-primary">
                    <i class="fas fa-edit me-1"></i> Edit Pengumuman
                </a>
            @endif
        </div>
    </div>

    <div class="saas-card p-4 p-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <span class="badge {{ $pengumuman->kategori === 'Darurat' ? 'badge-soft-danger' : ($pengumuman->kategori === 'Keamanan' ? 'badge-soft-warning' : 'badge-soft-primary') }} me-2">
                    {{ $pengumuman->kategori }}
                </span>
                <span class="badge badge-soft-secondary font-monospace">
                    Dipublikasikan: {{ optional($pengumuman->tanggal_publikasi)->format('d F Y H:i') }}
                </span>
            </div>
        </div>

        <h2 class="fw-bold text-dark mb-4">{{ $pengumuman->judul }}</h2>

        <div class="p-3 rounded-3 mb-4 bg-light border-start border-4 border-success">
            <div class="row g-2 text-secondary small">
                <div class="col-md-6">
                    <i class="fas fa-bullseye me-1 text-success"></i> Sasaran: <strong class="text-dark">{{ $pengumuman->target_label }}</strong>
                </div>
                <div class="col-md-6">
                    <i class="fas fa-user-shield me-1 text-primary"></i> Penerbit: <strong class="text-dark">{{ optional($pengumuman->pembuat)->name ?? 'Pengurus RW' }}</strong>
                </div>
            </div>
        </div>

        <div class="text-dark mb-4" style="line-height: 1.8; white-space: pre-line; font-size: 1.05rem;">
            {{ $pengumuman->isi }}
        </div>

        @if($pengumuman->lampiran)
            <div class="border-top pt-4 mt-4">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-paperclip text-success me-2"></i>Berkas Lampiran Resmi</h6>
                <a href="{{ asset('storage/' . $pengumuman->lampiran) }}" target="_blank" class="btn btn-outline-saas">
                    <i class="fas fa-download me-1"></i> Unduh / Buka Lampiran
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
