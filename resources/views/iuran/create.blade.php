@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-plus-circle"></i> Tambah Iuran Baru
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Buat jenis iuran baru untuk warga
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card animate__animated animate__fadeInUp">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-money-bill"></i> Form Iuran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('iuran.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Nama Iuran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-tag"></i></span>
                                <input type="text" name="nama_iuran" class="form-control bg-dark border-secondary text-white @error('nama_iuran') is-invalid @enderror" required>
                            </div>
                            @error('nama_iuran') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Jenis Iuran <span class="text-danger">*</span></label>
                                <select name="jenis_iuran" class="form-select bg-dark border-secondary text-white @error('jenis_iuran') is-invalid @enderror" required>
                                    <option value="" class="text-dark">Pilih Jenis</option>
                                    <option value="wajib">Wajib</option>
                                    <option value="sukarela">Sukarela</option>
                                    <option value="khusus">Khusus</option>
                                </select>
                                @error('jenis_iuran') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nominal <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white">Rp</span>
                                    <input type="number" name="nominal" class="form-control bg-dark border-secondary text-white @error('nominal') is-invalid @enderror" required>
                                </div>
                                @error('nominal') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode <span class="text-danger">*</span></label>
                                <select name="periode" class="form-select bg-dark border-secondary text-white @error('periode') is-invalid @enderror" required>
                                    <option value="" class="text-dark">Pilih Periode</option>
                                    <option value="bulanan">Bulanan</option>
                                    <option value="triwulan">Triwulan (3 Bulan)</option>
                                    <option value="tahunan">Tahunan</option>
                                </select>
                                @error('periode') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <!-- Tambahan kategori -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Kategori Iuran <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select bg-dark border-secondary text-white @error('kategori') is-invalid @enderror" required>
                                    <option value="" class="text-dark">Pilih Kategori</option>
                                    <option value="Ramadhan">Ramadhan</option>
                                    <option value="Non-Ramadhan">Non-Ramadhan</option>
                                </select>
                                @error('kategori') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Status</label>
                                <select name="status" class="form-select bg-dark border-secondary text-white">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="form-control bg-dark border-secondary text-white" placeholder="Deskripsi iuran..."></textarea>
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('iuran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save"></i> Simpan Iuran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus {
        background-color: #1e293b;
        border-color: #60a5fa;
        box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
    }
    
    .input-group-text {
        color: #94a3b8;
    }
    
    option {
        background-color: #1e293b;
        color: #f1f5f9;
    }
</style>
@endsection
