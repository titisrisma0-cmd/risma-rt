@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-edit"></i> Edit Pengeluaran
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Edit data pengeluaran
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card animate__animated animate__fadeInUp">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-edit"></i> Form Edit Pengeluaran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pengeluaran.update', $pengeluaran->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" value="{{ $pengeluaran->tanggal }}" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select bg-dark text-white border-secondary" required>
                                <option value="Kebersihan" {{ $pengeluaran->kategori == 'Kebersihan' ? 'selected' : '' }} class="bg-dark text-white">Kebersihan</option>
                                <option value="Keamanan" {{ $pengeluaran->kategori == 'Keamanan' ? 'selected' : '' }} class="bg-dark text-white">Keamanan</option>
                                <option value="Sosial" {{ $pengeluaran->kategori == 'Sosial' ? 'selected' : '' }} class="bg-dark text-white">Sosial</option>
                                <option value="Pembangunan" {{ $pengeluaran->kategori == 'Pembangunan' ? 'selected' : '' }} class="bg-dark text-white">Pembangunan</option>
                                <option value="Perlengkapan" {{ $pengeluaran->kategori == 'Perlengkapan' ? 'selected' : '' }} class="bg-dark text-white">Perlengkapan</option>
                                <option value="Lainnya" {{ $pengeluaran->kategori == 'Lainnya' ? 'selected' : '' }} class="bg-dark text-white">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Nominal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white">Rp</span>
                                <input type="number" name="nominal" value="{{ $pengeluaran->nominal }}" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Keterangan <span class="text-danger">*</span></label>
                            <textarea name="keterangan" rows="3" class="form-control bg-dark text-white border-secondary" required>{{ $pengeluaran->keterangan }}</textarea>
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('pengeluaran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save"></i> Update Pengeluaran
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