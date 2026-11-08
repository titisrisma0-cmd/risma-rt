@extends('layouts.app')

@section('title', 'Edit Kartu Keluarga ' . $kartuKeluarga->no_kk)

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 900px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('kartu-keluarga.show', $kartuKeluarga) }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1 class="h3 text-white mb-0">Edit Kartu Keluarga: {{ $kartuKeluarga->no_kk }}</h1>
    </div>

    <div class="card border-0 p-4" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="POST" action="{{ route('kartu-keluarga.update', $kartuKeluarga) }}">
            @csrf
            @method('PUT')

            @if($user->isAdminRw())
                <div class="mb-3">
                    <label class="form-label text-white-50">Wilayah RT *</label>
                    <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ (old('rt_id', $kartuKeluarga->rt_id) == $r->id) ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-white-50">Nomor Kartu Keluarga (16 Digit) *</label>
                    <input type="text" name="no_kk" maxlength="16" minlength="16" class="form-control text-white @error('no_kk') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" value="{{ old('no_kk', $kartuKeluarga->no_kk) }}" required>
                    @error('no_kk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50">Status Kepemilikan Rumah *</label>
                    <select name="status_rumah" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="tetap" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'tetap' ? 'selected' : '' }}>Tetap (Milik Sendiri)</option>
                        <option value="kontrak" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'kontrak' ? 'selected' : '' }}>Kontrak / Sewa</option>
                        <option value="kos" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'kos' ? 'selected' : '' }}>Kos</option>
                        <option value="lainnya" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-white-50">Alamat Lengkap Rumah *</label>
                <textarea name="alamat" rows="2" class="form-control text-white @error('alamat') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>{{ old('alamat', $kartuKeluarga->alamat) }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('kartu-keluarga.show', $kartuKeluarga) }}" class="btn btn-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
