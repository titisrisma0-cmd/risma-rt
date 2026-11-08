@extends('layouts.app')

@section('title', 'Live CCTV Monitoring')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('layanan.ronda.index') }}">Keamanan & Ronda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Live CCTV</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-video text-danger me-2"></i>Live CCTV Monitoring</h1>
            <p class="page-subtitle">Sistem pemantauan kamera pengawas lingkungan RT & RW secara real-time.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('layanan.ronda.index') }}" class="btn btn-outline-saas">
                <i class="fas fa-shield-halved me-1"></i>Ronda
            </a>
            <a href="{{ route('layanan.ronda.cctv.recordings') }}" class="btn btn-outline-saas">
                <i class="fas fa-clock-rotate-left me-1"></i>Arsip Rekaman
            </a>
            <a href="{{ route('layanan.ronda.cctv.manage') }}" class="btn btn-saas-primary">
                <i class="fas fa-gear me-1"></i>Kelola CCTV
            </a>
        </div>
    </div>

    {{-- FILTER FORM --}}
    <div class="saas-card p-3 mb-4">
        <form class="row g-2 align-items-center" method="GET">
            @if(Auth::user()->isAdminRw())
                <div class="col-12 col-md-3">
                    <select name="rt_id" class="form-select-saas">
                        <option value="">Semua Wilayah RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" @selected(request('rt_id') == $rt->id)>RT {{ $rt->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 col-md-3">
                <select name="status" class="form-select-saas">
                    <option value="">Semua Status</option>
                    @foreach(['aktif','nonaktif','rusak'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <input name="q" value="{{ request('q') }}" class="form-control-saas" placeholder="Cari lokasi kamera...">
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-saas-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    {{-- CAMERA GRIDS --}}
    @forelse($cameraGroups as $cameras)
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fas fa-building text-success me-2"></i>RT {{ $cameras->first()->rt->kode_rt }}
                </h5>
                <span class="badge badge-soft-secondary">{{ $cameras->count() }} Kamera Terdaftar</span>
            </div>
            <div class="cctv-grid">
                @foreach($cameras as $camera)
                    <article class="saas-card overflow-hidden">
                        <button class="cctv-preview" type="button" data-camera='@json(["name"=>$camera->nama_lokasi." — RT ".$camera->rt->kode_rt,"url"=>$camera->stream_url,"status"=>$camera->status])' @disabled($camera->status !== 'aktif' || !$camera->stream_url)>
                            @if($camera->status === 'aktif' && $camera->stream_url)
                                <video muted playsinline preload="metadata" data-stream="{{ $camera->stream_url }}"></video>
                                <span class="play-overlay"><i class="fas fa-expand"></i></span>
                            @else
                                <div class="camera-offline">
                                    <i class="fas fa-video-slash"></i>
                                    <span>{{ ucfirst($camera->status) }}</span>
                                </div>
                            @endif
                        </button>
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <strong class="text-dark">{{ $camera->nama_lokasi }}</strong>
                                <span class="badge {{ $camera->status === 'aktif' ? 'badge-soft-success' : ($camera->status === 'rusak' ? 'badge-soft-danger' : 'badge-soft-secondary') }}">
                                    {{ $camera->status === 'aktif' ? 'Online' : ucfirst($camera->status) }}
                                </span>
                            </div>
                            <small class="text-secondary"><i class="fas fa-location-dot me-1"></i>RT {{ $camera->rt->kode_rt }}</small>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @empty
        <div class="saas-card p-5 text-center text-secondary">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-video"></i></div>
                <h6 class="empty-state-title">Tidak Ada Kamera Sesuai Filter</h6>
                <p class="empty-state-desc">Silakan periksa kriteria pencarian atau tambahkan kamera baru.</p>
            </div>
        </div>
    @endforelse
</div>

{{-- MODAL STREAM --}}
<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-bottom border-secondary py-3 px-4">
                <h5 class="modal-title text-white fw-bold" id="cameraModalTitle"></h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <video id="cameraModalVideo" class="w-100" controls autoplay muted playsinline></video>
            </div>
        </div>
    </div>
</div>

<style>
.cctv-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}
.cctv-preview {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    padding: 0;
    border: 0;
    background: #0f172a;
    color: white;
}
.cctv-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.play-overlay {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    font-size: 1.6rem;
    background: rgba(0, 0, 0, 0.1);
    opacity: 0;
    transition: 0.2s;
}
.cctv-preview:hover .play-overlay {
    opacity: 1;
    background: rgba(0, 0, 0, 0.4);
}
.camera-offline {
    height: 100%;
    display: grid;
    place-content: center;
    gap: 8px;
    color: #94a3b8;
    font-size: 1.1rem;
}
.camera-offline i {
    font-size: 2rem;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<script>
const modalEl = document.getElementById('cameraModal'), modalVideo = document.getElementById('cameraModalVideo');
let modalHls;

function attachStream(video, url, autoplay = false) {
    if (!url) return;
    if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
    } else if (window.Hls && Hls.isSupported()) {
        const hls = new Hls({ maxBufferLength: 15 });
        hls.loadSource(url);
        hls.attachMedia(video);
        video._hls = hls;
    } else {
        video.src = url;
    }
    if (autoplay) video.play().catch(() => {});
}

document.querySelectorAll('video[data-stream]').forEach(v => attachStream(v, v.dataset.stream));

document.querySelectorAll('[data-camera]').forEach(btn => btn.addEventListener('click', () => {
    const c = JSON.parse(btn.dataset.camera);
    document.getElementById('cameraModalTitle').textContent = c.name;
    if (modalHls) modalHls.destroy();
    modalVideo.removeAttribute('src');
    modalVideo.load();
    attachStream(modalVideo, c.url, true);
    modalHls = modalVideo._hls;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}));

modalEl.addEventListener('hidden.bs.modal', () => {
    modalVideo.pause();
    if (modalHls) {
        modalHls.destroy();
        modalHls = null;
    }
    modalVideo.removeAttribute('src');
    modalVideo.load();
});
</script>
@endpush
@endsection
