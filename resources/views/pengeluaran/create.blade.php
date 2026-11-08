@extends('layouts.app')

@section('title', 'Tambah Pengeluaran')

@section('content')
<div class="content-container" style="max-width: 860px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('pengeluaran.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-plus-circle text-emerald me-2"></i> Tambah Pengeluaran
            </h1>
            <p class="page-subtitle">Catat rincian pengeluaran kas dengan detail dan akurat</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form action="{{ route('pengeluaran.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" class="form-control form-control-saas" value="{{ date('Y-m-d') }}" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-select form-control-saas" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Kebersihan">Kebersihan</option>
                    <option value="Keamanan">Keamanan</option>
                    <option value="Sosial">Sosial</option>
                    <option value="Pembangunan">Pembangunan</option>
                    <option value="Perlengkapan">Perlengkapan</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Nominal <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                    <input type="number" name="nominal" class="form-control form-control-saas border-start-0" required min="1" placeholder="Masukkan nominal pengeluaran">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-secondary small fw-medium">Keterangan <span class="text-danger">*</span></label>
                <textarea name="keterangan" rows="3" class="form-control form-control-saas" required placeholder="Deskripsi rincian peruntukan pengeluaran..."></textarea>
            </div>
            
            <div class="alert alert-warning py-2 small mb-4">
                <i class="fas fa-exclamation-triangle me-1"></i> 
                Pastikan pengeluaran telah disetujui oleh pengurus berwenang sebelum dicatat ke dalam buku kas.
            </div>
            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-emerald text-white px-4">
                    <i class="fas fa-save me-1"></i> Simpan Pengeluaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
