@extends('layouts.app')

@section('title', 'Tambah Kartu Keluarga Baru')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 900px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1 class="h3 text-white mb-0">Tambah Kartu Keluarga (KK) Baru</h1>
    </div>

    <div class="card border-0 p-4" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="POST" action="{{ route('kartu-keluarga.store') }}">
            @csrf

            @if($user->isAdminRw())
                <div class="mb-3">
                    <label class="form-label text-white-50">Wilayah RT *</label>
                    <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="">-- Pilih RT --</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ old('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-white-50">Nomor Kartu Keluarga (16 Digit) *</label>
                    <input type="text" name="no_kk" maxlength="16" minlength="16" class="form-control text-white @error('no_kk') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Contoh: 3273010101010001" value="{{ old('no_kk') }}" required>
                    @error('no_kk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50">Status Kepemilikan Rumah *</label>
                    <select name="status_rumah" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="tetap" {{ old('status_rumah') == 'tetap' ? 'selected' : '' }}>Tetap (Milik Sendiri)</option>
                        <option value="kontrak" {{ old('status_rumah') == 'kontrak' ? 'selected' : '' }}>Kontrak / Sewa</option>
                        <option value="kos" {{ old('status_rumah') == 'kos' ? 'selected' : '' }}>Kos</option>
                        <option value="lainnya" {{ old('status_rumah') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-white-50">Alamat Lengkap Rumah *</label>
                <textarea name="alamat" rows="2" class="form-control text-white @error('alamat') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Jalan, Blok, Nomor Rumah..." required>{{ old('alamat') }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="border-white-10 my-4">
            <h5 class="text-emerald-400 mb-3"><i class="fas fa-user-tie me-2"></i>Data Kepala Keluarga</h5>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-white-50">Nama Lengkap Kepala Keluarga *</label>
                    <input type="text" name="nama_kepala" class="form-control text-white @error('nama_kepala') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Nama sesuai KTP" value="{{ old('nama_kepala') }}" required>
                    @error('nama_kepala')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50">NIK Kepala Keluarga (16 Digit) *</label>
                    <input type="text" name="nik_kepala" maxlength="16" minlength="16" class="form-control text-white @error('nik_kepala') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="NIK 16 digit" value="{{ old('nik_kepala') }}" required>
                    @error('nik_kepala')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">Jenis Kelamin Kepala Keluarga *</label>
                    <select name="jenis_kelamin_kepala" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="L" {{ old('jenis_kelamin_kepala') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin_kepala') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir_kepala" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Kota Kelahiran" value="{{ old('tempat_lahir_kepala') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir_kepala" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" value="{{ old('tanggal_lahir_kepala') }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-white-50">Pekerjaan</label>
                    <input type="text" name="pekerjaan_kepala" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Contoh: Karyawan Swasta, Wiraswasta" value="{{ old('pekerjaan_kepala') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="no_hp_kepala" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="08xxxxxxxxxx" value="{{ old('no_hp_kepala') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-emerald text-white px-4" style="background: linear-gradient(135deg, #059669, #10b981); border: none;">
                    <i class="fas fa-save me-1"></i> Simpan Kartu Keluarga
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
