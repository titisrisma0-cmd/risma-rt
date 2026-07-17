@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-calendar-check text-warning me-2"></i>Kegiatan Warga</h2>
            <p class="text-muted mb-0">Agenda tingkat RT dan RW, pendaftaran peserta, serta kehadiran warga.</p>
        </div>
        <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    @if($canManage)
    <div class="card shadow-sm mb-4">
        <div class="card-header"><strong><i class="fas fa-plus-circle me-2"></i>Buat Kegiatan</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('kegiatan.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6"><label class="form-label">Judul</label><input name="judul" class="form-control" required value="{{ old('judul') }}"></div>
                <div class="col-md-3"><label class="form-label">Kategori</label><select name="kategori" class="form-select" required>@foreach(['kerja_bakti'=>'Kerja bakti','sosial'=>'Sosial','keagamaan'=>'Keagamaan','olahraga'=>'Olahraga','rapat'=>'Rapat','lainnya'=>'Lainnya'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="draft">Draft</option><option value="dibuka" selected>Dibuka</option><option value="berlangsung">Berlangsung</option></select></div>
                <div class="col-md-3"><label class="form-label">Cakupan</label><select name="cakupan" class="form-select" {{ $isRt && !$isRw ? 'disabled' : '' }}><option value="rw">RW</option><option value="rt" {{ $isRt && !$isRw ? 'selected' : '' }}>RT</option></select>@if($isRt && !$isRw)<input type="hidden" name="cakupan" value="rt">@endif</div>
                @if($isRw)<div class="col-md-3"><label class="form-label">RT (bila cakupan RT)</label><select name="rt_id" class="form-select"><option value="">Semua RT / RW</option>@foreach($rts as $rt)<option value="{{ $rt->id }}">{{ $rt->kode_rt }} - {{ $rt->nama_rt }}</option>@endforeach</select></div>@endif
                <div class="col-md-3"><label class="form-label">Mulai</label><input type="datetime-local" name="tanggal_mulai" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Selesai</label><input type="datetime-local" name="tanggal_selesai" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Lokasi</label><input name="lokasi" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Latitude (opsional)</label><input type="number" step="0.0000001" min="-90" max="90" name="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="-6.2000000"></div>
                <div class="col-md-3"><label class="form-label">Longitude (opsional)</label><input type="number" step="0.0000001" min="-180" max="180" name="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="106.8166667"></div>
                <div class="col-md-3"><label class="form-label">Kuota (opsional)</label><input type="number" min="1" name="kuota_peserta" class="form-control"></div>
                <div class="col-md-9"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
                <div class="col-12"><button class="btn btn-warning"><i class="fas fa-save me-1"></i>Simpan Kegiatan</button></div>
            </form>
        </div>
    </div>
    @endif

    <div class="row g-4">
        @forelse($kegiatans as $kegiatan)
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-2 mb-2"><span class="badge {{ $kegiatan->cakupan === 'rw' ? 'bg-primary' : 'bg-success' }}">{{ strtoupper($kegiatan->cakupan) }}</span><span class="badge bg-secondary">{{ ucfirst($kegiatan->status) }}</span></div>
                    <h4>{{ $kegiatan->judul }}</h4><p class="text-muted">{{ $kegiatan->deskripsi }}</p>
                    <div class="small mb-3"><div><i class="fas fa-calendar me-2"></i>{{ $kegiatan->tanggal_mulai->format('d M Y, H:i') }} - {{ $kegiatan->tanggal_selesai->format('H:i') }}</div><div><i class="fas fa-location-dot me-2"></i>{{ $kegiatan->lokasi }}</div><div><i class="fas fa-users me-2"></i>{{ $kegiatan->peserta_count }} / {{ $kegiatan->kuota_peserta ?? 'Tanpa batas' }} peserta</div></div>
                    @if($kegiatan->peserta->contains('user_id', Auth::id()))
                    <form method="POST" action="{{ route('kegiatan.batal-daftar', $kegiatan) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="fas fa-user-minus me-1"></i>Batal Daftar</button></form>
                    @elseif(in_array($kegiatan->status, ['dibuka','berlangsung']))
                    <form method="POST" action="{{ route('kegiatan.daftar', $kegiatan) }}" class="d-inline">@csrf <button class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i>Daftar Saya</button></form>
                    @endif
                    @if($canManage && ($isRw || ($kegiatan->created_by === Auth::id() && $kegiatan->rt_id === Auth::user()->profile?->rt_id)))
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('kegiatan.peserta', $kegiatan) }}"><i class="fas fa-list me-1"></i>Peserta</a>
                    <form method="POST" action="{{ route('kegiatan.destroy', $kegiatan) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" onclick="return confirm('Batalkan kegiatan ini?')">Batalkan</button></form>
                    @endif
                </div>
            </div>
        </div>
        @empty <div class="col-12"><div class="alert alert-info">Belum ada kegiatan yang dapat ditampilkan.</div></div>
        @endforelse
    </div>
</div>
@endsection
