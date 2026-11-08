@extends('layouts.app')

@section('title', 'Edit Pengumuman RW')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 900px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('pengumuman.index') }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1 class="h3 text-white mb-0">Edit Pengumuman: {{ $pengumuman->judul }}</h1>
    </div>

    <div class="card border-0 p-4" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="POST" action="{{ route('pengumuman.update', $pengumuman) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label text-white-50">Judul Pengumuman *</label>
                <input type="text" name="judul" class="form-control text-white @error('judul') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" value="{{ old('judul', $pengumuman->judul) }}" required>
                @error('judul')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-white-50">Kategori *</label>
                    <select name="kategori" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="Umum" {{ old('kategori', $pengumuman->kategori) == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Keamanan" {{ old('kategori', $pengumuman->kategori) == 'Keamanan' ? 'selected' : '' }}>Keamanan / Ronda</option>
                        <option value="Kerja Bakti" {{ old('kategori', $pengumuman->kategori) == 'Kerja Bakti' ? 'selected' : '' }}>Kerja Bakti / Lingkungan</option>
                        <option value="Iuran" {{ old('kategori', $pengumuman->kategori) == 'Iuran' ? 'selected' : '' }}>Iuran & Keuangan</option>
                        <option value="Darurat" {{ old('kategori', $pengumuman->kategori) == 'Darurat' ? 'selected' : '' }}>Penting / Darurat</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50">Target Sasaran Penerima *</label>
                    <select name="target_audience" id="targetAudienceSelect" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required onchange="toggleRtSelect()">
                        <option value="semua_rt" {{ old('target_audience', $pengumuman->target_audience) == 'semua_rt' ? 'selected' : '' }}>Semua Pengurus RT (RT 01–08)</option>
                        <option value="rt_tertentu" {{ old('target_audience', $pengumuman->target_audience) == 'rt_tertentu' ? 'selected' : '' }}>Pengurus RT Tertentu</option>
                        <option value="seluruh_warga" {{ old('target_audience', $pengumuman->target_audience) == 'seluruh_warga' ? 'selected' : '' }}>Seluruh Warga (Semua RT)</option>
                        <option value="warga_rt" {{ old('target_audience', $pengumuman->target_audience) == 'warga_rt' ? 'selected' : '' }}>Warga di RT Tertentu</option>
                    </select>
                </div>
            </div>

            <div class="mb-3" id="rtSelectContainer" style="display: {{ in_array($pengumuman->target_audience, ['rt_tertentu', 'warga_rt']) ? 'block' : 'none' }};">
                <label class="form-label text-white-50">Pilih Wilayah RT *</label>
                <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <option value="">-- Pilih Wilayah RT --</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ old('rt_id', $pengumuman->rt_id) == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }} ({{ $r->nama_rt }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-white-50">Isi Pengumuman / Pesan *</label>
                <textarea name="isi" rows="6" class="form-control text-white @error('isi') is-invalid @enderror" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>{{ old('isi', $pengumuman->isi) }}</textarea>
                @error('isi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label text-white-50">Status Publikasi *</label>
                    <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="published" {{ old('status', $pengumuman->status) == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                        <option value="draft" {{ old('status', $pengumuman->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="arsip" {{ old('status', $pengumuman->status) == 'arsip' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">Jadwal Tanggal Publikasi</label>
                    <input type="datetime-local" name="tanggal_publikasi" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" value="{{ old('tanggal_publikasi', optional($pengumuman->tanggal_publikasi)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">Ganti Lampiran (Opsional)</label>
                    <input type="file" name="lampiran" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" accept=".pdf,.jpg,.jpeg,.png">
                    @if($pengumuman->lampiran)
                        <small class="text-emerald-400 d-block mt-1"><i class="fas fa-paperclip me-1"></i>Lampiran saat ini terunggah</small>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
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
</script>
@endsection
