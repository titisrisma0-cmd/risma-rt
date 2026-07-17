@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 cctv-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h2><i class="fas fa-video text-danger me-2"></i>Live CCTV</h2><p class="text-secondary mb-0">Monitoring keamanan per RT — mode demo.</p></div>
        <div class="d-flex gap-2"><a href="{{ route('layanan.ronda.index') }}" class="btn btn-outline-secondary">Ronda</a><a href="{{ route('layanan.ronda.cctv.recordings') }}" class="btn btn-outline-primary">Arsip</a><a href="{{ route('layanan.ronda.cctv.manage') }}" class="btn btn-primary">Kelola CCTV</a></div>
    </div>
    <form class="glass-card p-3 mb-4 row g-2" method="GET">
        @if(Auth::user()->isAdminRw())<div class="col-md-3"><select name="rt_id" class="form-select"><option value="">Semua RT</option>@foreach($rts as $rt)<option value="{{ $rt->id }}" @selected(request('rt_id') == $rt->id)>RT {{ $rt->kode_rt }}</option>@endforeach</select></div>@endif
        <div class="col-md-3"><select name="status" class="form-select"><option value="">Semua Status</option>@foreach(['aktif','nonaktif','rusak'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari lokasi kamera..."></div><div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
    @forelse($cameraGroups as $cameras)
        <section class="mb-4"><h4 class="mb-3">RT {{ $cameras->first()->rt->kode_rt }} <span class="badge bg-secondary">{{ $cameras->count() }} kamera</span></h4>
            <div class="cctv-grid">@foreach($cameras as $camera)
                <article class="cctv-card">
                    <button class="cctv-preview" type="button" data-camera='@json(["name"=>$camera->nama_lokasi." — RT ".$camera->rt->kode_rt,"url"=>$camera->stream_url,"status"=>$camera->status])' @disabled($camera->status !== 'aktif' || !$camera->stream_url)>
                        @if($camera->status === 'aktif' && $camera->stream_url)<video muted playsinline preload="metadata" data-stream="{{ $camera->stream_url }}"></video><span class="play-overlay"><i class="fas fa-expand"></i></span>@else<div class="camera-offline"><i class="fas fa-video-slash"></i><span>{{ ucfirst($camera->status) }}</span></div>@endif
                    </button>
                    <div class="p-3"><div class="d-flex justify-content-between gap-2"><strong>{{ $camera->nama_lokasi }}</strong><span class="badge {{ $camera->status === 'aktif' ? 'bg-success' : ($camera->status === 'rusak' ? 'bg-danger' : 'bg-secondary') }}">{{ $camera->status === 'aktif' ? 'Online' : ucfirst($camera->status) }}</span></div><small class="text-secondary">RT {{ $camera->rt->kode_rt }}</small></div>
                </article>
            @endforeach</div>
        </section>
    @empty<div class="glass-card p-5 text-center text-secondary">Tidak ada kamera sesuai filter.</div>@endforelse
</div>
<div class="modal fade" id="cameraModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content bg-dark"><div class="modal-header"><h5 class="modal-title" id="cameraModalTitle"></h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-0"><video id="cameraModalVideo" class="w-100" controls autoplay muted playsinline></video></div></div></div></div>
<style>.cctv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}.cctv-card{overflow:hidden;border:1px solid rgba(148,163,184,.25);border-radius:16px;background:var(--card-bg);box-shadow:0 8px 22px rgba(15,23,42,.12)}.cctv-preview{position:relative;width:100%;aspect-ratio:16/9;padding:0;border:0;background:#050b12;color:white}.cctv-preview video{width:100%;height:100%;object-fit:cover}.play-overlay{position:absolute;inset:0;display:grid;place-items:center;font-size:1.6rem;background:rgba(0,0,0,.08);opacity:0;transition:.2s}.cctv-preview:hover .play-overlay{opacity:1;background:rgba(0,0,0,.3)}.camera-offline{height:100%;display:grid;place-content:center;gap:8px;color:#94a3b8;font-size:1.1rem}.camera-offline i{font-size:2rem}</style>
@push('scripts')<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script><script>
const modalEl=document.getElementById('cameraModal'),modalVideo=document.getElementById('cameraModalVideo');let modalHls;
function attachStream(video,url,autoplay=false){if(!url)return;if(video.canPlayType('application/vnd.apple.mpegurl')){video.src=url;}else if(window.Hls&&Hls.isSupported()){const hls=new Hls({maxBufferLength:15});hls.loadSource(url);hls.attachMedia(video);video._hls=hls;}else{video.src=url;}if(autoplay)video.play().catch(()=>{});}
document.querySelectorAll('video[data-stream]').forEach(v=>attachStream(v,v.dataset.stream));
document.querySelectorAll('[data-camera]').forEach(btn=>btn.addEventListener('click',()=>{const c=JSON.parse(btn.dataset.camera);document.getElementById('cameraModalTitle').textContent=c.name;if(modalHls)modalHls.destroy();modalVideo.removeAttribute('src');modalVideo.load();attachStream(modalVideo,c.url,true);modalHls=modalVideo._hls;bootstrap.Modal.getOrCreateInstance(modalEl).show();}));
modalEl.addEventListener('hidden.bs.modal',()=>{modalVideo.pause();if(modalHls){modalHls.destroy();modalHls=null;}modalVideo.removeAttribute('src');modalVideo.load();});
</script>@endpush
@endsection
