@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-user-plus"></i> Tambah Warga Baru
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Isi data warga baru dengan lengkap dan benar
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-address-card"></i> Form Data Warga</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('warga.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-user"></i></span>
                                    <input type="text" name="nama_lengkap" class="form-control bg-dark text-white border-secondary @error('nama_lengkap') is-invalid @enderror" required>
                                </div>
                                @error('nama_lengkap') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" required>
                                </div>
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">NIK <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="nik" class="form-control bg-dark text-white border-secondary @error('nik') is-invalid @enderror" required>
                                </div>
                                @error('nik') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">No. KK <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-address-book"></i></span>
                                    <input type="text" name="no_kk" class="form-control bg-dark text-white border-secondary @error('no_kk') is-invalid @enderror" required>
                                </div>
                                @error('no_kk') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" name="tempat_lahir" class="form-control bg-dark text-white border-secondary @error('tempat_lahir') is-invalid @enderror" required>
                                @error('tempat_lahir') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control bg-dark text-white border-secondary @error('tanggal_lahir') is-invalid @enderror" required>
                                @error('tanggal_lahir') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select bg-dark text-white border-secondary @error('jenis_kelamin') is-invalid @enderror" required>
                                    <option value="" class="bg-dark text-white">Pilih</option>
                                    <option value="L" class="bg-dark text-white">Laki-laki</option>
                                    <option value="P" class="bg-dark text-white">Perempuan</option>
                                </select>
                                @error('jenis_kelamin') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">No. HP <span class="text-danger">*</span></label>
                                <input type="text" name="no_hp" class="form-control bg-dark text-white border-secondary @error('no_hp') is-invalid @enderror" required>
                                @error('no_hp') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Alamat <span class="text-danger">*</span></label>
                            <textarea name="alamat" rows="3" class="form-control bg-dark text-white border-secondary @error('alamat') is-invalid @enderror" required></textarea>
                            @error('alamat') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-control bg-dark text-white border-secondary">
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('warga.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save"></i> Simpan Warga
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
.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}
</style>
@endsection