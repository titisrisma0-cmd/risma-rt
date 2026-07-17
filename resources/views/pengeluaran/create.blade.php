@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-plus-circle"></i> Tambah Pengeluaran
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Catat pengeluaran kas RW dengan detail
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card animate__animated animate__fadeInUp">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-receipt"></i> Form Pengeluaran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pengeluaran.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select bg-dark text-white border-secondary" required>
                                <option value="" class="bg-dark text-white">-- Pilih Kategori --</option>
                                <option value="Kebersihan" class="bg-dark text-white">Kebersihan</option>
                                <option value="Keamanan" class="bg-dark text-white">Keamanan</option>
                                <option value="Sosial" class="bg-dark text-white">Sosial</option>
                                <option value="Pembangunan" class="bg-dark text-white">Pembangunan</option>
                                <option value="Perlengkapan" class="bg-dark text-white">Perlengkapan</option>
                                <option value="Lainnya" class="bg-dark text-white">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Nominal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white">Rp</span>
                                <input type="number" name="nominal" class="form-control bg-dark text-white border-secondary" required min="1" placeholder="Masukkan nominal">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Keterangan <span class="text-danger">*</span></label>
                            <textarea name="keterangan" rows="3" class="form-control bg-dark text-white border-secondary" required placeholder="Deskripsi pengeluaran..."></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Pastikan pengeluaran sudah disetujui oleh Ketua RW sebelum dicatat.
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('pengeluaran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save"></i> Simpan Pengeluaran
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
.alert-warning {
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: #fcd34d;
}
</style>
@endsection
