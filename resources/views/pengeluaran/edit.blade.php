@extends('layouts.app')

@section('title', 'Edit Pengeluaran')

@section('content')
<div class="content-container" style="max-width: 860px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('pengeluaran.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-edit text-warning me-2"></i> Edit Pengeluaran
            </h1>
            <p class="page-subtitle">Perbarui data catatan pengeluaran kas</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form action="{{ route('pengeluaran.update', $pengeluaran->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" value="{{ $pengeluaran->tanggal }}" class="form-control form-control-saas" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-select form-control-saas" required>
                    <option value="Kebersihan" {{ $pengeluaran->kategori == 'Kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                    <option value="Keamanan" {{ $pengeluaran->kategori == 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                    <option value="Sosial" {{ $pengeluaran->kategori == 'Sosial' ? 'selected' : '' }}>Sosial</option>
                    <option value="Pembangunan" {{ $pengeluaran->kategori == 'Pembangunan' ? 'selected' : '' }}>Pembangunan</option>
                    <option value="Perlengkapan" {{ $pengeluaran->kategori == 'Perlengkapan' ? 'selected' : '' }}>Perlengkapan</option>
                    <option value="Lainnya" {{ $pengeluaran->kategori == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Nominal <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                    <input type="number" name="nominal" value="{{ $pengeluaran->nominal }}" class="form-control form-control-saas border-start-0" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-secondary small fw-medium">Keterangan <span class="text-danger">*</span></label>
                <textarea name="keterangan" rows="3" class="form-control form-control-saas" required>{{ $pengeluaran->keterangan }}</textarea>
            </div>
            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                    <i class="fas fa-save me-1"></i> Update Pengeluaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
