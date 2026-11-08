@extends('layouts.app')

@section('title', 'Arsip Rekaman CCTV')

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
                    <li class="breadcrumb-item active" aria-current="page">Arsip Rekaman</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-clock-rotate-left text-success me-2"></i>Arsip Rekaman CCTV</h1>
            <p class="page-subtitle">Daftar arsip rekaman video kamera pengawas wilayah RT (demo 7 hari terakhir).</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('layanan.ronda.cctv.live') }}" class="btn btn-outline-saas">
                <i class="fas fa-video me-1"></i>Live CCTV
            </a>
            <a href="{{ route('layanan.ronda.cctv.manage') }}" class="btn btn-saas-primary">
                <i class="fas fa-gear me-1"></i>Kelola Kamera
            </a>
        </div>
    </div>

    {{-- FILTER FORM --}}
    <div class="saas-card p-3 mb-4">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-12 col-md-2">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="form-control-saas" title="Tanggal awal">
            </div>
            <div class="col-12 col-md-2">
                <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="form-control-saas" title="Tanggal akhir">
            </div>
            @if(Auth::user()->isAdminRw())
                <div class="col-12 col-md-2">
                    <select name="rt_id" class="form-select-saas">
                        <option value="">Semua Wilayah RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" @selected(request('rt_id') == $rt->id)>RT {{ $rt->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 col-md-4">
                <select name="camera_id" class="form-select-saas">
                    <option value="">Semua Kamera</option>
                    @foreach($cameras as $camera)
                        <option value="{{ $camera->id }}" @selected(request('camera_id') == $camera->id)>
                            RT {{ $camera->rt->kode_rt }} — {{ $camera->nama_lokasi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-saas-primary w-100">
                    <i class="fas fa-filter me-1"></i>Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- RECORDING PLAYER & LIST --}}
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="saas-card p-2 recording-list">
                @forelse($recordings as $recording)
                    <button type="button" class="recording-item" data-recording='@json(["url"=>$recording->file_path,"title"=>$recording->camera->nama_lokasi." — RT ".$recording->camera->rt->kode_rt,"meta"=>$recording->tanggal_rekaman->format("d/m/Y")." ".$recording->jam_mulai."–".$recording->jam_selesai])'>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark">{{ $recording->camera->nama_lokasi }}</strong>
                            <span class="badge badge-soft-secondary">RT {{ $recording->camera->rt->kode_rt }}</span>
                        </div>
                        <span class="text-secondary small">
                            <i class="fas fa-calendar-day me-1"></i>{{ $recording->tanggal_rekaman->format('d/m/Y') }} · <i class="fas fa-clock ms-1 me-1"></i>{{ substr($recording->jam_mulai,0,5) }}–{{ substr($recording->jam_selesai,0,5) }}
                        </span>
                        <small class="text-muted mt-1">
                            Durasi: {{ round($recording->durasi_detik/60) }} menit · Ukuran: {{ $recording->ukuran_file_mb }} MB
                        </small>
                    </button>
                @empty
                    <div class="p-5 text-center text-secondary">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-film"></i></div>
                            <h6 class="empty-state-title">Rekaman Tidak Ditemukan</h6>
                            <p class="empty-state-desc">Belum ada file rekaman untuk kriteria filter ini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
            @if($recordings->hasPages())
                <div class="mt-3">
                    {{ $recordings->links() }}
                </div>
            @endif
        </div>

        <div class="col-lg-7">
            <div class="saas-card sticky-lg-top overflow-hidden" style="top: 80px;">
                <video id="recordingPlayer" class="w-100 bg-black" controls preload="metadata" style="max-height: 400px;"></video>
                <div class="p-4">
                    <h5 id="recordingTitle" class="fw-bold text-dark mb-1">Pilih rekaman untuk diputar</h5>
                    <span id="recordingMeta" class="text-secondary small">Klik salah satu video dari daftar di sebelah kiri</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.recording-list {
    max-height: 620px;
    overflow-y: auto;
}
.recording-item {
    display: flex;
    flex-direction: column;
    width: 100%;
    padding: 14px 16px;
    text-align: left;
    border: 0;
    border-bottom: 1px solid var(--border-color);
    background: transparent;
    transition: background-color 0.15s ease-in-out;
    border-radius: 8px;
    margin-bottom: 4px;
}
.recording-item:hover {
    background: #E8F5EC;
}
.recording-item.active {
    background: #E8F5EC;
    border-left: 3px solid #147A45;
}
</style>

@push('scripts')
<script>
document.querySelectorAll('.recording-item').forEach(button => {
    button.addEventListener('click', () => {
        const data = JSON.parse(button.dataset.recording);
        document.querySelectorAll('.recording-item').forEach(x => x.classList.remove('active'));
        button.classList.add('active');
        const player = document.getElementById('recordingPlayer');
        player.src = data.url;
        document.getElementById('recordingTitle').textContent = data.title;
        document.getElementById('recordingMeta').textContent = data.meta;
        player.play().catch(() => {});
    });
});
</script>
@endpush
@endsection
