@extends('layouts.app')

@section('title', 'Tambah Kartu Keluarga Baru')

@section('content')
<div class="content-container" style="max-width: 920px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">Tambah Kartu Keluarga (KK) Baru</h1>
            <p class="page-subtitle">Daftarkan nomor KK baru beserta data Kepala Keluarga ke sistem</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form method="POST" action="{{ route('kartu-keluarga.store') }}">
            @csrf

            @if($user->isAdminRw())
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-medium">Wilayah RT *</label>
                    <select name="rt_id" class="form-select form-control-saas" required>
                        <option value="">-- Pilih RT --</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ old('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Nomor Kartu Keluarga (16 Digit) *</label>
                    <input type="text" name="no_kk" maxlength="16" minlength="16" class="form-control form-control-saas @error('no_kk') is-invalid @enderror font-monospace" placeholder="Contoh: 3273010101010001" value="{{ old('no_kk') }}" required>
                    @error('no_kk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Status Kepemilikan Rumah *</label>
                    <select name="status_rumah" class="form-select form-control-saas" required>
                        <option value="tetap" {{ old('status_rumah') == 'tetap' ? 'selected' : '' }}>Tetap (Milik Sendiri)</option>
                        <option value="kontrak" {{ old('status_rumah') == 'kontrak' ? 'selected' : '' }}>Kontrak / Sewa</option>
                        <option value="kos" {{ old('status_rumah') == 'kos' ? 'selected' : '' }}>Kos</option>
                        <option value="lainnya" {{ old('status_rumah') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary small fw-medium">Alamat Lengkap Rumah *</label>
                <textarea name="alamat" rows="2" class="form-control form-control-saas @error('alamat') is-invalid @enderror" placeholder="Jalan, Blok, Nomor Rumah..." required>{{ old('alamat') }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="border-top pt-4 my-4">
                <h6 class="text-emerald fw-bold mb-3"><i class="fas fa-user-tie me-2"></i>Data Kepala Keluarga</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-medium">Nama Lengkap Kepala Keluarga *</label>
                        <input type="text" name="nama_kepala" class="form-control form-control-saas @error('nama_kepala') is-invalid @enderror" placeholder="Nama sesuai KTP" value="{{ old('nama_kepala') }}" required>
                        @error('nama_kepala')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-medium">NIK Kepala Keluarga (16 Digit) *</label>
                        <input type="text" name="nik_kepala" maxlength="16" minlength="16" class="form-control form-control-saas @error('nik_kepala') is-invalid @enderror font-monospace" placeholder="NIK 16 digit" value="{{ old('nik_kepala') }}" required>
                        @error('nik_kepala')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-medium">Jenis Kelamin Kepala Keluarga *</label>
                        <select name="jenis_kelamin_kepala" class="form-select form-control-saas" required>
                            <option value="L" {{ old('jenis_kelamin_kepala') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ old('jenis_kelamin_kepala') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-medium">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir_kepala" class="form-control form-control-saas" placeholder="Kota Kelahiran" value="{{ old('tempat_lahir_kepala') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-medium">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir_kepala" class="form-control form-control-saas" value="{{ old('tanggal_lahir_kepala') }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-medium">Pekerjaan</label>
                        <input type="text" name="pekerjaan_kepala" class="form-control form-control-saas" placeholder="Contoh: Karyawan Swasta, Wiraswasta" value="{{ old('pekerjaan_kepala') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-medium">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="no_hp_kepala" class="form-control form-control-saas" placeholder="08xxxxxxxxxx" value="{{ old('no_hp_kepala') }}">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-emerald text-white px-4">
                    <i class="fas fa-save me-1"></i> Simpan Kartu Keluarga
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
