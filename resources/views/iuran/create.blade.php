@extends('layouts.app')

@section('title', 'Tambah Iuran Baru')

@section('content')
<div class="content-container" style="max-width: 860px;">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('iuran.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-plus-circle text-emerald me-2"></i> Tambah Iuran Baru
            </h1>
            <p class="page-subtitle">Tetapkan jenis iuran baru, nominal, dan ketentuan penagihan warga</p>
        </div>
    </div>

    <div class="saas-card p-4">
        <form action="{{ route('iuran.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary small fw-medium">Nama Iuran <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                    <input type="text" name="nama_iuran" class="form-control form-control-saas border-start-0 @error('nama_iuran') is-invalid @enderror" placeholder="Contoh: Iuran Kebersihan RT" value="{{ old('nama_iuran') }}" required>
                </div>
                @error('nama_iuran') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Jenis Iuran <span class="text-danger">*</span></label>
                    <select name="jenis_iuran" class="form-select form-control-saas @error('jenis_iuran') is-invalid @enderror" required>
                        <option value="">Pilih Jenis</option>
                        <option value="wajib" @selected(old('jenis_iuran') == 'wajib')>Wajib</option>
                        <option value="sukarela" @selected(old('jenis_iuran') == 'sukarela')>Sukarela</option>
                        <option value="khusus" @selected(old('jenis_iuran') == 'khusus')>Khusus</option>
                    </select>
                    @error('jenis_iuran') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Nominal <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                        <input type="number" name="nominal" class="form-control form-control-saas border-start-0 @error('nominal') is-invalid @enderror" placeholder="50000" value="{{ old('nominal') }}" required>
                    </div>
                    @error('nominal') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Periode Penagihan <span class="text-danger">*</span></label>
                    <select name="periode" class="form-select form-control-saas @error('periode') is-invalid @enderror" required>
                        <option value="">Pilih Periode</option>
                        <option value="bulanan" @selected(old('periode') == 'bulanan')>Bulanan</option>
                        <option value="triwulan" @selected(old('periode') == 'triwulan')>Triwulan (3 Bulan)</option>
                        <option value="tahunan" @selected(old('periode') == 'tahunan')>Tahunan</option>
                    </select>
                    @error('periode') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Kategori Iuran <span class="text-danger">*</span></label>
                    <select name="kategori" class="form-select form-control-saas @error('kategori') is-invalid @enderror" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Ramadhan" @selected(old('kategori') == 'Ramadhan')>Ramadhan</option>
                        <option value="Non-Ramadhan" @selected(old('kategori') == 'Non-Ramadhan')>Non-Ramadhan</option>
                    </select>
                    @error('kategori') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-medium">Status</label>
                    <select name="status" class="form-select form-control-saas">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-secondary small fw-medium">Keterangan (Opsional)</label>
                <textarea name="keterangan" rows="3" class="form-control form-control-saas" placeholder="Deskripsi peruntukan iuran..."></textarea>
            </div>
            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('iuran.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-emerald text-white px-4">
                    <i class="fas fa-save me-1"></i> Simpan Iuran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
