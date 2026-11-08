@extends('layouts.app')

@section('title', 'Edit Kartu Keluarga ' . $kartuKeluarga->no_kk)

@section('content')
<div class="content-container" style="max-width: 920px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('kartu-keluarga.show', $kartuKeluarga) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">Edit Kartu Keluarga: {{ $kartuKeluarga->no_kk }}</h1>
            <p class="page-subtitle">Perbarui data alamat dan informasi Kartu Keluarga</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form method="POST" action="{{ route('kartu-keluarga.update', $kartuKeluarga) }}">
            @csrf
            @method('PUT')

            @if($user->isAdminRw())
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-medium">Wilayah RT *</label>
                    <select name="rt_id" class="form-select form-control-saas" required>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ (old('rt_id', $kartuKeluarga->rt_id) == $r->id) ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Nomor Kartu Keluarga (16 Digit) *</label>
                    <input type="text" name="no_kk" maxlength="16" minlength="16" class="form-control form-control-saas @error('no_kk') is-invalid @enderror font-monospace" value="{{ old('no_kk', $kartuKeluarga->no_kk) }}" required>
                    @error('no_kk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Status Kepemilikan Rumah *</label>
                    <select name="status_rumah" class="form-select form-control-saas" required>
                        <option value="tetap" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'tetap' ? 'selected' : '' }}>Tetap (Milik Sendiri)</option>
                        <option value="kontrak" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'kontrak' ? 'selected' : '' }}>Kontrak / Sewa</option>
                        <option value="kos" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'kos' ? 'selected' : '' }}>Kos</option>
                        <option value="lainnya" {{ old('status_rumah', $kartuKeluarga->status_rumah) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary small fw-medium">Alamat Lengkap Rumah *</label>
                <textarea name="alamat" rows="2" class="form-control form-control-saas @error('alamat') is-invalid @enderror" required>{{ old('alamat', $kartuKeluarga->alamat) }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('kartu-keluarga.show', $kartuKeluarga) }}" class="btn btn-outline-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
