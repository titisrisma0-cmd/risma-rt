@extends('layouts.app')

@section('title', 'Edit Iuran')

@section('content')
<div class="content-container" style="max-width: 860px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('iuran.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-edit text-warning me-2"></i> Edit Iuran
            </h1>
            <p class="page-subtitle">Perbarui data tarif {{ $iuran->nama_iuran }}</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form action="{{ route('iuran.update', $iuran->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Nama Iuran <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                    <input type="text" name="nama_iuran" value="{{ $iuran->nama_iuran }}" class="form-control form-control-saas border-start-0" required>
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Jenis Iuran <span class="text-danger">*</span></label>
                    <select name="jenis_iuran" class="form-select form-control-saas" required>
                        <option value="wajib" {{ $iuran->jenis_iuran == 'wajib' ? 'selected' : '' }}>Wajib</option>
                        <option value="sukarela" {{ $iuran->jenis_iuran == 'sukarela' ? 'selected' : '' }}>Sukarela</option>
                        <option value="khusus" {{ $iuran->jenis_iuran == 'khusus' ? 'selected' : '' }}>Khusus</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Nominal <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                        <input type="number" name="nominal" value="{{ $iuran->nominal }}" class="form-control form-control-saas border-start-0" required>
                    </div>
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Periode <span class="text-danger">*</span></label>
                    <select name="periode" class="form-select form-control-saas" required>
                        <option value="bulanan" {{ $iuran->periode == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        <option value="triwulan" {{ $iuran->periode == 'triwulan' ? 'selected' : '' }}>Triwulan (3 Bulan)</option>
                        <option value="tahunan" {{ $iuran->periode == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Status</label>
                    <select name="status" class="form-select form-control-saas">
                        <option value="aktif" {{ $iuran->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ $iuran->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Kategori Iuran <span class="text-danger">*</span></label>
                    <select name="kategori" class="form-select form-control-saas" required>
                        <option value="Ramadhan" {{ $iuran->kategori == 'Ramadhan' ? 'selected' : '' }}>Ramadhan</option>
                        <option value="Non-Ramadhan" {{ $iuran->kategori == 'Non-Ramadhan' ? 'selected' : '' }}>Non-Ramadhan</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-secondary small fw-medium">Keterangan</label>
                <textarea name="keterangan" rows="3" class="form-control form-control-saas">{{ $iuran->keterangan }}</textarea>
            </div>
            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('iuran.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                    <i class="fas fa-save me-1"></i> Update Iuran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
