@extends('layouts.app')

@section('title', $pengumuman->judul)

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 900px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('pengumuman.index') }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
        <h1 class="h3 text-white mb-0">Pengumuman Resmi RW</h1>
    </div>

    <div class="card border-0 p-4" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 16px;">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <span class="badge {{ $pengumuman->kategori === 'Darurat' ? 'bg-danger' : ($pengumuman->kategori === 'Keamanan' ? 'bg-warning text-dark' : 'bg-primary') }} me-2">
                    {{ $pengumuman->kategori }}
                </span>
                <span class="badge bg-slate-700 text-white font-monospace">
                    Dipublikasikan: {{ optional($pengumuman->tanggal_publikasi)->format('d F Y H:i') }}
                </span>
            </div>

            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <div class="d-flex gap-2">
                    <a href="{{ route('pengumuman.edit', $pengumuman) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                </div>
            @endif
        </div>

        <h2 class="text-white fw-bold mb-3">{{ $pengumuman->judul }}</h2>

        <div class="p-3 rounded mb-4" style="background: rgba(15,23,42,0.6); border-left: 4px solid #10b981;">
            <div class="row g-2 text-white-50 small">
                <div class="col-md-6">
                    <i class="fas fa-bullseye me-1 text-emerald-400"></i> Sasaran: <strong class="text-white">{{ $pengumuman->target_label }}</strong>
                </div>
                <div class="col-md-6">
                    <i class="fas fa-user-shield me-1 text-blue-400"></i> Penerbit: <strong class="text-white">{{ optional($pengumuman->pembuat)->name ?? 'Pengurus RW' }}</strong>
                </div>
            </div>
        </div>

        <div class="text-white mb-4" style="line-height: 1.8; white-space: pre-line; font-size: 1.05rem;">
            {{ $pengumuman->isi }}
        </div>

        @if($pengumuman->lampiran)
            <div class="border-top border-white-10 pt-3 mt-4">
                <h6 class="text-emerald-400 mb-2"><i class="fas fa-paperclip me-2"></i>Berkas Lampiran Resmi</h6>
                <a href="{{ asset('storage/' . $pengumuman->lampiran) }}" target="_blank" class="btn btn-outline-info text-white">
                    <i class="fas fa-download me-1"></i> Unduh / Lihat Lampiran
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
