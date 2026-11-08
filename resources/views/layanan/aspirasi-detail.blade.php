@extends('layouts.app')

@section('title', 'Detail Aspirasi: ' . $aspirasi->judul)

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('aspirasi.index') }}">Aspirasi Warga</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Aspirasi</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-comment-dots text-success me-2"></i>Detail Aspirasi & Pengaduan</h1>
            <p class="page-subtitle">Informasi lengkap laporan, foto bukti permasalahan, dan catatan tindak lanjut pengurus.</p>
        </div>
        <div>
            <a href="{{ route('aspirasi.index') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-saas alert-saas-success mb-4">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="saas-card mb-4 p-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge badge-soft-secondary">{{ ucwords(str_replace('_', ' ', $aspirasi->kategori)) }}</span>
                    @php
                        $badgeSt = match($aspirasi->status) {
                            'terkirim' => 'badge-soft-warning',
                            'diproses', 'ditanggapi' => 'badge-soft-info',
                            'ditindaklanjuti' => 'badge-soft-primary',
                            'dieskalasi' => 'badge-soft-secondary',
                            'selesai' => 'badge-soft-success',
                            'ditolak' => 'badge-soft-danger',
                            default => 'badge-soft-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badgeSt }}">{{ ucwords(str_replace('_', ' ', $aspirasi->status)) }}</span>
                </div>

                <h3 class="fw-bold text-dark mb-2">{{ $aspirasi->judul }}</h3>
                <p class="text-secondary small mb-3">
                    <i class="fas fa-user me-1"></i> {{ $aspirasi->anonim ? 'Pengirim Anonim' : ($aspirasi->pelapor?->name ?? 'Warga') }} · 
                    <i class="fas fa-clock ms-1 me-1"></i> {{ $aspirasi->created_at->format('d M Y H:i') }}
                </p>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <p class="text-dark mb-0" style="line-height: 1.6; white-space: pre-line;">{{ $aspirasi->deskripsi }}</p>
                </div>

                @if($aspirasi->lokasi)
                    <p class="small text-secondary mb-3">
                        <i class="fas fa-location-dot text-danger me-1"></i> <strong>Lokasi:</strong> {{ $aspirasi->lokasi }}
                    </p>
                @endif

                @if($aspirasi->lampirans->count())
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-paperclip text-success me-1"></i>Foto Lampiran Pendukung</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($aspirasi->lampirans as $l)
                                <a href="{{ asset('storage/' . $l->file_path) }}" target="_blank" class="btn btn-outline-saas btn-sm">
                                    <i class="fas fa-image me-1"></i> Lihat Lampiran {{ $loop->iteration }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIWAYAT TANGGAPAN --}}
            <div class="saas-card p-4">
                <h5 class="fw-bold text-dark mb-3"><i class="fas fa-comments text-success me-2"></i>Riwayat Tanggapan Pengurus</h5>
                @forelse($aspirasi->tanggapans as $t)
                    <div class="p-3 rounded-3 border bg-light mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark">{{ $t->user?->name ?? 'Pengurus' }}</strong>
                            <small class="text-muted">{{ $t->created_at->format('d M Y H:i') }}</small>
                        </div>
                        <p class="mb-0 text-secondary small">{{ $t->isi }}</p>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-comment-slash fs-2 mb-2 d-block text-secondary"></i>
                        Belum ada tanggapan resmi pengurus untuk aspirasi ini.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4">
            @if(Auth::user()->hasRole('rw') || Auth::user()->hasRole('rt') || Auth::user()->hasRole('admin'))
                <div class="saas-card p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-reply text-success me-2"></i>Tindak Lanjut Pengurus</h6>
                    
                    <form method="POST" action="{{ route('aspirasi.tanggapan', $aspirasi) }}" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label-saas">Beri Tanggapan Resmi <span class="text-danger">*</span></label>
                            <textarea name="isi" class="form-control-saas" rows="3" placeholder="Tuliskan respon atau rencana penanganan..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-saas-primary w-100">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Tanggapan
                        </button>
                    </form>

                    <div class="border-top pt-3">
                        <label class="form-label-saas">Perbarui Status Penanganan</label>
                        <form method="POST" action="{{ route('aspirasi.status', $aspirasi) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <select name="status" class="form-select-saas">
                                    @foreach(['terkirim','diproses','ditanggapi','dieskalasi','selesai','ditolak'] as $s)
                                        <option value="{{ $s }}" @selected($aspirasi->status === $s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-outline-saas w-100">
                                <i class="fas fa-arrows-rotate me-1"></i> Ubah Status
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
