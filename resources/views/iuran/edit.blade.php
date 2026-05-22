@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-edit"></i> Edit Iuran
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Edit {{ $iuran->nama_iuran }}
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card animate__animated animate__fadeInUp">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-money-bill"></i> Form Edit Iuran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('iuran.update', $iuran->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Nama Iuran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white"><i class="fas fa-tag"></i></span>
                                <input type="text" name="nama_iuran" value="{{ $iuran->nama_iuran }}" class="form-control bg-dark border-secondary text-white" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Jenis Iuran <span class="text-danger">*</span></label>
                                <select name="jenis_iuran" class="form-select bg-dark border-secondary text-white" required>
                                    <option value="wajib" {{ $iuran->jenis_iuran == 'wajib' ? 'selected' : '' }}>Wajib</option>
                                    <option value="sukarela" {{ $iuran->jenis_iuran == 'sukarela' ? 'selected' : '' }}>Sukarela</option>
                                    <option value="khusus" {{ $iuran->jenis_iuran == 'khusus' ? 'selected' : '' }}>Khusus</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nominal <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white">Rp</span>
                                    <input type="number" name="nominal" value="{{ $iuran->nominal }}" class="form-control bg-dark border-secondary text-white" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode <span class="text-danger">*</span></label>
                                <select name="periode" class="form-select bg-dark border-secondary text-white" required>
                                    <option value="bulanan" {{ $iuran->periode == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="triwulan" {{ $iuran->periode == 'triwulan' ? 'selected' : '' }}>Triwulan (3 Bulan)</option>
                                    <option value="tahunan" {{ $iuran->periode == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Status</label>
                                <select name="status" class="form-select bg-dark border-secondary text-white">
                                    <option value="aktif" {{ $iuran->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ $iuran->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="form-control bg-dark border-secondary text-white">{{ $iuran->keterangan }}</textarea>
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('iuran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save"></i> Update Iuran
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
</style>
@endsection