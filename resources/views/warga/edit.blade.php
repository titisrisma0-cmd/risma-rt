@extends('layouts.app')

@section('title', 'Edit Data Warga')

@section('content')
<div class="content-container" style="max-width: 860px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('warga.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-user-edit text-warning me-2"></i> Edit Data Warga
            </h1>
            <p class="page-subtitle">Perbarui data warga {{ $warga->name }}</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form action="{{ route('warga.update', $warga->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">RT <span class="text-danger">*</span></label>
                @if(Auth::user()->isAdminRt())
                    <input type="text" class="form-control form-control-saas bg-light" value="RT {{ $rts->first()?->kode_rt ?? '-' }}" readonly>
                @else
                    <select name="rt_id" class="form-select form-control-saas" required>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" @selected(old('rt_id', $warga->profile?->rt_id) == $rt->id)>RW {{ $rt->rw?->kode_rw }} / RT {{ $rt->kode_rt }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user"></i></span>
                        <input type="text" name="nama_lengkap" value="{{ $warga->profile->nama_lengkap ?? $warga->name }}" class="form-control form-control-saas border-start-0" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" value="{{ $warga->email }}" class="form-control form-control-saas border-start-0 bg-light" readonly>
                    </div>
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">NIK <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="nik" value="{{ $warga->profile->nik ?? '' }}" class="form-control form-control-saas border-start-0 font-monospace" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">No. KK <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-address-book"></i></span>
                        <input type="text" name="no_kk" value="{{ $warga->profile->no_kk ?? '' }}" class="form-control form-control-saas border-start-0 font-monospace" required>
                    </div>
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ $warga->profile->tempat_lahir ?? '' }}" class="form-control form-control-saas">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ $warga->profile->tanggal_lahir ?? '' }}" class="form-control form-control-saas">
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select form-control-saas">
                        <option value="">Pilih</option>
                        <option value="L" {{ ($warga->profile->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ ($warga->profile->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">No. HP</label>
                    <input type="text" name="no_hp" value="{{ $warga->profile->no_hp ?? '' }}" class="form-control form-control-saas">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Alamat</label>
                <textarea name="alamat" rows="3" class="form-control form-control-saas">{{ $warga->profile->alamat ?? '' }}</textarea>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Pekerjaan</label>
                    <input type="text" name="pekerjaan" value="{{ $warga->profile->pekerjaan ?? '' }}" class="form-control form-control-saas">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Status</label>
                    <select name="status" class="form-select form-control-saas">
                        <option value="pending" {{ ($warga->profile->status ?? '') == 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="aktif" {{ ($warga->profile->status ?? '') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ ($warga->profile->status ?? '') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                    <i class="fas fa-save me-1"></i> Update Warga
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
