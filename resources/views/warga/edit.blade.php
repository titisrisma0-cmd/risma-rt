@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-user-edit"></i> Edit Data Warga
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Perbarui data warga {{ $warga->name }}
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-address-card"></i> Form Edit Warga</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('warga.update', $warga->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-user"></i></span>
                                    <input type="text" name="nama_lengkap" value="{{ $warga->profile->nama_lengkap ?? $warga->name }}" class="form-control bg-dark text-white border-secondary" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" value="{{ $warga->email }}" class="form-control bg-dark text-white border-secondary" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">NIK <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="nik" value="{{ $warga->profile->nik ?? '' }}" class="form-control bg-dark text-white border-secondary" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">No. KK <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-address-book"></i></span>
                                    <input type="text" name="no_kk" value="{{ $warga->profile->no_kk ?? '' }}" class="form-control bg-dark text-white border-secondary" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="{{ $warga->profile->tempat_lahir ?? '' }}" class="form-control bg-dark text-white border-secondary">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ $warga->profile->tanggal_lahir ?? '' }}" class="form-control bg-dark text-white border-secondary">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select bg-dark text-white border-secondary">
                                    <option value="" class="bg-dark text-white">Pilih</option>
                                    <option value="L" {{ ($warga->profile->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }} class="bg-dark text-white">Laki-laki</option>
                                    <option value="P" {{ ($warga->profile->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }} class="bg-dark text-white">Perempuan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">No. HP</label>
                                <input type="text" name="no_hp" value="{{ $warga->profile->no_hp ?? '' }}" class="form-control bg-dark text-white border-secondary">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Alamat</label>
                            <textarea name="alamat" rows="3" class="form-control bg-dark text-white border-secondary">{{ $warga->profile->alamat ?? '' }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Pekerjaan</label>
                                <input type="text" name="pekerjaan" value="{{ $warga->profile->pekerjaan ?? '' }}" class="form-control bg-dark text-white border-secondary">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Status</label>
                                <select name="status" class="form-select bg-dark text-white border-secondary">
                                    <option value="pending" {{ ($warga->profile->status ?? '') == 'pending' ? 'selected' : '' }} class="bg-dark text-white">Menunggu Verifikasi</option>
                                    <option value="aktif" {{ ($warga->profile->status ?? '') == 'aktif' ? 'selected' : '' }} class="bg-dark text-white">Aktif</option>
                                    <option value="nonaktif" {{ ($warga->profile->status ?? '') == 'nonaktif' ? 'selected' : '' }} class="bg-dark text-white">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('warga.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save"></i> Update Warga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(96, 165, 250, 0.1);
}
.card-header {
    background: rgba(15, 23, 42, 0.5);
    border-bottom: 1px solid rgba(96, 165, 250, 0.1);
}
.form-control:focus, .form-select:focus {
    background-color: #1e293b;
    border-color: #60a5fa;
    box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
}
.btn-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
}
.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59,130,246,0.4);
    color: white;
}
.text-white {
    color: #f1f5f9 !important;
}
</style>
@endsection
