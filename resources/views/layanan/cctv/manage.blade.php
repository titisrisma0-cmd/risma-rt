@extends('layouts.app')

@section('title', 'Kelola Kamera CCTV')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('layanan.ronda.index') }}">Keamanan & Ronda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('layanan.ronda.cctv.live') }}">Live CCTV</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kelola Kamera</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-gear text-success me-2"></i>Kelola CCTV Lingkungan</h1>
            <p class="page-subtitle">Konfigurasi perangkat kamera keamanan, koordinat lokasi, dan feed streaming RT.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('layanan.ronda.cctv.live') }}" class="btn btn-outline-saas">
                <i class="fas fa-video me-1"></i>Live Stream
            </a>
            <a href="{{ route('layanan.ronda.cctv.recordings') }}" class="btn btn-outline-saas">
                <i class="fas fa-clock-rotate-left me-1"></i>Arsip Rekaman
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-saas alert-saas-danger mb-4">
            <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-saas alert-saas-success mb-4">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
        </div>
    @endif

    {{-- FORM TAMBAH KAMERA --}}
    <div class="saas-card mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-plus-circle text-success me-2"></i>Tambah Kamera Pengawas Baru
            </h6>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('layanan.ronda.cctv.cameras.store') }}" class="row g-3">
                @csrf
                @if(Auth::user()->isAdminRw())
                    <div class="col-12 col-md-3">
                        <label class="form-label-saas">Wilayah RT <span class="text-danger">*</span></label>
                        <select name="rt_id" class="form-select-saas" required>
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}">RT {{ $rt->kode_rt }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="col-12 col-md-3">
                        <label class="form-label-saas">Wilayah RT</label>
                        <input class="form-control-saas bg-light" value="RT {{ $rts->first()?->kode_rt }}" readonly>
                    </div>
                @endif
                <div class="col-12 col-md-3">
                    <label class="form-label-saas">Nama Lokasi Kamera <span class="text-danger">*</span></label>
                    <input name="nama_lokasi" class="form-control-saas" required placeholder="Contoh: Gerbang Masuk Utama">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label-saas">Stream URL (HLS / m3u8)</label>
                    <input name="stream_url" type="url" class="form-control-saas" placeholder="https://media-server/live/playlist.m3u8">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label-saas">Status Operasional</label>
                    <select name="status" class="form-select-saas">
                        @foreach(['aktif','nonaktif','rusak'] as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label-saas">Latitude (opsional)</label>
                    <input name="latitude" type="number" step="0.0000001" min="-90" max="90" class="form-control-saas" placeholder="-6.2088">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label-saas">Longitude (opsional)</label>
                    <input name="longitude" type="number" step="0.0000001" min="-180" max="180" class="form-control-saas" placeholder="106.8456">
                </div>
                <div class="col-12">
                    <div class="alert alert-saas alert-saas-info py-2 mb-0">
                        <i class="fas fa-circle-info me-1"></i> <strong>Catatan Teknis:</strong> Stream URL diisi dengan URL HLS format <code>.m3u8</code> dari media server. Untuk kamera fisik CCTV, URL ini didapat dari converter RTSP-to-HLS.
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-saas-primary">
                        <i class="fas fa-plus me-1"></i>Tambah Kamera
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DAFTAR KAMERA --}}
    <div class="saas-card">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-list text-success me-2"></i>Daftar Kamera CCTV Terdaftar
            </h6>
            <span class="badge badge-soft-secondary">{{ $cameras->count() }} Kamera</span>
        </div>
        <div class="table-responsive">
            <table class="table table-saas align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width: 120px;">RT</th>
                        <th style="min-width: 220px;">Lokasi & Koordinat</th>
                        <th style="min-width: 260px;">Stream URL</th>
                        <th style="min-width: 130px;">Status</th>
                        <th class="text-center" style="min-width: 80px;">Arsip</th>
                        <th class="text-end" style="min-width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cameras as $camera)
                        <tr>
                            <td>
                                <form id="form-update-{{ $camera->id }}" method="POST" action="{{ route('layanan.ronda.cctv.cameras.update', $camera) }}">
                                    @csrf
                                    @method('PUT')
                                    @if(Auth::user()->isAdminRw())
                                        <select name="rt_id" class="form-select-saas form-select-sm">
                                            @foreach($rts as $rt)
                                                <option value="{{ $rt->id }}" @selected($camera->rt_id == $rt->id)>RT {{ $rt->kode_rt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="badge badge-soft-secondary">RT {{ $camera->rt->kode_rt }}</span>
                                    @endif
                            </td>
                            <td>
                                    <input name="nama_lokasi" value="{{ $camera->nama_lokasi }}" class="form-control-saas form-control-sm mb-1" required placeholder="Nama Lokasi">
                                    <div class="d-flex gap-1">
                                        <input name="latitude" type="number" step="0.0000001" min="-90" max="90" value="{{ $camera->latitude }}" class="form-control-saas form-control-sm" placeholder="Lat">
                                        <input name="longitude" type="number" step="0.0000001" min="-180" max="180" value="{{ $camera->longitude }}" class="form-control-saas form-control-sm" placeholder="Lng">
                                    </div>
                            </td>
                            <td>
                                    <input name="stream_url" type="url" value="{{ $camera->stream_url }}" class="form-control-saas form-control-sm" placeholder="URL .m3u8">
                            </td>
                            <td>
                                    <select name="status" class="form-select-saas form-select-sm">
                                        @foreach(['aktif','nonaktif','rusak'] as $status)
                                            <option value="{{ $status }}" @selected($camera->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary">{{ $camera->recordings_count }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="submit" form="form-update-{{ $camera->id }}" class="btn btn-outline-saas btn-sm text-warning" title="Simpan Perubahan">
                                    <i class="fas fa-save"></i>
                                </button>
                                <form method="POST" class="d-inline" action="{{ route('layanan.ronda.cctv.cameras.status', $camera) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-outline-saas btn-sm text-secondary" title="Aktif/Nonaktif">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline" action="{{ route('layanan.ronda.cctv.cameras.destroy', $camera) }}" data-confirm="Hapus kamera beserta arsip demonya?" data-confirm-title="Hapus Kamera CCTV" data-confirm-btn="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-saas btn-sm text-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-video"></i></div>
                                    <h6 class="empty-state-title">Belum Ada Kamera</h6>
                                    <p class="empty-state-desc">Tambahkan kamera pertama menggunakan formulir di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
