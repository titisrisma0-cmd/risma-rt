@extends('layouts.app')

@section('title', 'Buat Pengumuman Baru RW')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 900px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('pengumuman.index') }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1 class="h3 text-white mb-0">Buat Pengumuman Resmi RW</h1>
    </div>

    <div class="card border-0 p-4" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="POST" action="{{ route('pengumuman.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label text-white-50">Judul Pengumuman *</label>
                <input type="text" name="judul" class="form-control text-white @error('judul') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Contoh: Kerja Bakti Massal Menyambut HUT Kemerdekaan" value="{{ old('judul') }}" required>
                @error('judul')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-white-50">Kategori *</label>
                    <select name="kategori" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="Umum" {{ old('kategori') == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Keamanan" {{ old('kategori') == 'Keamanan' ? 'selected' : '' }}>Keamanan / Ronda</option>
                        <option value="Kerja Bakti" {{ old('kategori') == 'Kerja Bakti' ? 'selected' : '' }}>Kerja Bakti / Lingkungan</option>
                        <option value="Iuran" {{ old('kategori') == 'Iuran' ? 'selected' : '' }}>Iuran & Keuangan</option>
                        <option value="Darurat" {{ old('kategori') == 'Darurat' ? 'selected' : '' }}>Penting / Darurat</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50">Target Sasaran Penerima *</label>
                    <select name="target_audience" id="targetAudienceSelect" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required onchange="toggleRtSelect()">
                        <option value="semua_rt" {{ old('target_audience') == 'semua_rt' ? 'selected' : '' }}>Semua Pengurus RT (RT 01–08)</option>
                        <option value="rt_tertentu" {{ old('target_audience') == 'rt_tertentu' ? 'selected' : '' }}>Pengurus RT Tertentu</option>
                        <option value="seluruh_warga" {{ old('target_audience') == 'seluruh_warga' ? 'selected' : '' }}>Seluruh Warga (Semua RT)</option>
                        <option value="warga_rt" {{ old('target_audience') == 'warga_rt' ? 'selected' : '' }}>Warga di RT Tertentu</option>
                    </select>
                </div>
            </div>

            <div class="mb-3" id="rtSelectContainer" style="display: none;">
                <label class="form-label text-white-50">Pilih Wilayah RT *</label>
                <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <option value="">-- Pilih Wilayah RT --</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ old('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }} ({{ $r->nama_rt }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-white-50">Isi Pengumuman / Pesan *</label>
                <textarea name="isi" rows="6" class="form-control text-white @error('isi') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Tuliskan isi pengumuman atau instruksi secara lengkap..." required>{{ old('isi') }}</textarea>
                @error('isi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label text-white-50">Status Publikasi *</label>
                    <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Langsung Publikasikan</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Simpan sebagai Draft</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">Jadwal Tanggal Publikasi</label>
                    <input type="datetime-local" name="tanggal_publikasi" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" value="{{ old('tanggal_publikasi', date('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">Berkas Lampiran (Opsional)</label>
                    <input type="file" name="lampiran" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-white-50">PDF atau Gambar, maks 4MB</small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-emerald text-white px-4" style="background: linear-gradient(135deg, #059669, #10b981); border: none;">
                    <i class="fas fa-paper-plane me-1"></i> Simpan & Terbitkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRtSelect() {
    var val = document.getElementById('targetAudienceSelect').value;
    var container = document.getElementById('rtSelectContainer');
    if (val === 'rt_tertentu' || val === 'warga_rt') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', toggleRtSelect);
</script>
@endsection
