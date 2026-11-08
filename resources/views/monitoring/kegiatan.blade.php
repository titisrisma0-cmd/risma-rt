@extends('layouts.app')

@section('title', 'Monitoring Kegiatan RT — Pengurus RW')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1"><i class="fas fa-calendar-check text-emerald-400 me-2"></i>Monitoring Kegiatan RT</h1>
            <p class="text-white-50 mb-0">Pemantauan agenda dan aktivitas masyarakat di setiap wilayah RT</p>
        </div>

        <div>
            <a href="{{ route('kegiatan.index') }}" class="btn btn-emerald text-white px-3 py-2" style="background: linear-gradient(135deg, #059669, #10b981); border: none; border-radius: 8px;">
                <i class="fas fa-plus me-1"></i> Buat Agenda RW
            </a>
        </div>
    </div>

    {{-- Filter Wilayah RT --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="GET" action="{{ route('rw.monitoring.kegiatan') }}" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label text-white-50 small mb-1">Filter Wilayah RT</label>
                <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                    <option value="">Semua Wilayah RT & RW</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('rt_id'))
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('rw.monitoring.kegiatan') }}" class="btn btn-outline-light w-100">Reset Filter</a>
                </div>
            @endif
        </form>
    </div>

    {{-- Daftar Kegiatan --}}
    <div class="row g-3">
        @forelse($kegiatans as $k)
            <div class="col-md-6 col-xl-4">
                <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge {{ $k->cakupan === 'rw' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                            {{ $k->cakupan === 'rw' ? 'Tingkat RW' : 'RT ' . optional($k->rt)->kode_rt }}
                        </span>
                        <small class="text-white-50 font-monospace">{{ optional($k->tanggal)->format('d M Y') ?? optional($k->tanggal_mulai)->format('d M Y') }}</small>
                    </div>

                    <h5 class="text-white fw-bold mb-2">{{ $k->nama_kegiatan ?? $k->judul }}</h5>
                    <p class="text-white-50 small mb-3 flex-grow-1">{{ Str::limit($k->deskripsi, 90) }}</p>

                    <div class="border-top border-white-10 pt-2 mt-auto d-flex justify-content-between align-items-center">
                        <small class="text-white-50"><i class="fas fa-map-pin me-1"></i>{{ $k->lokasi ?? 'Wilayah RT' }}</small>
                        <span class="badge bg-slate-700 text-white-50">{{ $k->peserta_count ?? 0 }} Peserta</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <i class="fas fa-calendar-times fa-3x text-white-50 mb-3"></i>
                    <h5 class="text-white">Belum Ada Agenda Kegiatan</h5>
                    <p class="text-white-50 mb-0">Belum ada kegiatan yang tercatat untuk wilayah yang dipilih.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($kegiatans->hasPages())
        <div class="p-3 mt-4 border-top border-white-10">
            {{ $kegiatans->links() }}
        </div>
    @endif
</div>
@endsection
