@extends('layouts.app')

@section('title', 'Tambah Warga Baru')

@section('content')
<div class="content-container" style="max-width: 860px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('warga.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-user-plus text-emerald me-2"></i> Tambah Warga Baru
            </h1>
            <p class="page-subtitle">Isi data warga baru dengan lengkap dan benar</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form action="{{ route('warga.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">RT <span class="text-danger">*</span></label>
                @if(Auth::user()->isAdminRt())
                    <input type="text" class="form-control form-control-saas bg-light" value="RT {{ $rts->first()?->kode_rt ?? '-' }}" readonly>
                @else
                    <select name="rt_id" class="form-select form-control-saas @error('rt_id') is-invalid @enderror" required>
                        <option value="">Pilih RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" @selected(old('rt_id') == $rt->id)>RW {{ $rt->rw?->kode_rw }} / RT {{ $rt->kode_rt }}</option>
                        @endforeach
                    </select>
                @endif
                @error('rt_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user"></i></span>
                        <input type="text" name="nama_lengkap" class="form-control form-control-saas border-start-0 @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap') }}" required>
                    </div>
                    @error('nama_lengkap') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control form-control-saas border-start-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    </div>
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">NIK <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="nik" class="form-control form-control-saas border-start-0 font-monospace @error('nik') is-invalid @enderror" value="{{ old('nik') }}" required>
                    </div>
                    @error('nik') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">No. KK <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-address-book"></i></span>
                        <input type="text" name="no_kk" class="form-control form-control-saas border-start-0 font-monospace @error('no_kk') is-invalid @enderror" value="{{ old('no_kk') }}" required>
                    </div>
                    @error('no_kk') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Tempat Lahir <span class="text-danger">*</span></label>
                    <input type="text" name="tempat_lahir" class="form-control form-control-saas @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir') }}" required>
                    @error('tempat_lahir') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_lahir" class="form-control form-control-saas @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}" required>
                    @error('tanggal_lahir') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="jenis_kelamin" class="form-select form-control-saas @error('jenis_kelamin') is-invalid @enderror" required>
                        <option value="">Pilih</option>
                        <option value="L" @selected(old('jenis_kelamin') == 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin') == 'P')>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">No. HP <span class="text-danger">*</span></label>
                    <input type="text" name="no_hp" class="form-control form-control-saas @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" required>
                    @error('no_hp') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Alamat <span class="text-danger">*</span></label>
                <textarea name="alamat" rows="3" class="form-control form-control-saas @error('alamat') is-invalid @enderror" required>{{ old('alamat') }}</textarea>
                @error('alamat') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            
            <div class="mb-4">
                <label class="form-label text-secondary small fw-medium">Pekerjaan</label>
                <input type="text" name="pekerjaan" class="form-control form-control-saas" value="{{ old('pekerjaan') }}">
            </div>
            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-emerald text-white px-4">
                    <i class="fas fa-save me-1"></i> Simpan Warga
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
