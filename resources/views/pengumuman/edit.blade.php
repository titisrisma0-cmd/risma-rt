@extends('layouts.app')

@section('title', 'Edit Pengumuman RW')

@section('content')
<div class="content-container" style="max-width: 900px;">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pengumuman.index') }}">Pengumuman</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Pengumuman</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-edit text-success me-2"></i>Edit Pengumuman: {{ $pengumuman->judul }}</h1>
            <p class="page-subtitle">Perbarui informasi pengumuman, status publikasi, atau target sasaran warta.</p>
        </div>
        <div>
            <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="saas-card p-4 p-md-5">
        <form method="POST" action="{{ route('pengumuman.update', $pengumuman) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label-saas">Judul Pengumuman <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control-saas @error('judul') is-invalid @enderror" value="{{ old('judul', $pengumuman->judul) }}" required>
                @error('judul')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label-saas">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori" class="form-select-saas" required>
                        <option value="Umum" {{ old('kategori', $pengumuman->kategori) == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Keamanan" {{ old('kategori', $pengumuman->kategori) == 'Keamanan' ? 'selected' : '' }}>Keamanan / Ronda</option>
                        <option value="Kerja Bakti" {{ old('kategori', $pengumuman->kategori) == 'Kerja Bakti' ? 'selected' : '' }}>Kerja Bakti / Lingkungan</option>
                        <option value="Iuran" {{ old('kategori', $pengumuman->kategori) == 'Iuran' ? 'selected' : '' }}>Iuran & Keuangan</option>
                        <option value="Darurat" {{ old('kategori', $pengumuman->kategori) == 'Darurat' ? 'selected' : '' }}>Penting / Darurat</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-saas">Target Sasaran Penerima <span class="text-danger">*</span></label>
                    <select name="target_audience" id="targetAudienceSelect" class="form-select-saas" required onchange="toggleRtSelect()">
                        <option value="semua_rt" {{ old('target_audience', $pengumuman->target_audience) == 'semua_rt' ? 'selected' : '' }}>Semua Pengurus RT (RT 01–08)</option>
                        <option value="rt_tertentu" {{ old('target_audience', $pengumuman->target_audience) == 'rt_tertentu' ? 'selected' : '' }}>Pengurus RT Tertentu</option>
                        <option value="seluruh_warga" {{ old('target_audience', $pengumuman->target_audience) == 'seluruh_warga' ? 'selected' : '' }}>Seluruh Warga (Semua RT)</option>
                        <option value="warga_rt" {{ old('target_audience', $pengumuman->target_audience) == 'warga_rt' ? 'selected' : '' }}>Warga di RT Tertentu</option>
                    </select>
                </div>
            </div>

            <div class="mb-4" id="rtSelectContainer" style="display: {{ in_array($pengumuman->target_audience, ['rt_tertentu', 'warga_rt']) ? 'block' : 'none' }};">
                <label class="form-label-saas">Pilih Wilayah RT <span class="text-danger">*</span></label>
                <select name="rt_id" class="form-select-saas">
                    <option value="">-- Pilih Wilayah RT --</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ old('rt_id', $pengumuman->rt_id) == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }} ({{ $r->nama_rt }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label-saas">Isi Pengumuman / Pesan <span class="text-danger">*</span></label>
                <textarea name="isi" rows="6" class="form-control-saas @error('isi') is-invalid @enderror" required>{{ old('isi', $pengumuman->isi) }}</textarea>
                @error('isi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label-saas">Status Publikasi <span class="text-danger">*</span></label>
                    <select name="status" class="form-select-saas" required>
                        <option value="published" {{ old('status', $pengumuman->status) == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                        <option value="draft" {{ old('status', $pengumuman->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="arsip" {{ old('status', $pengumuman->status) == 'arsip' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-saas">Jadwal Tanggal Publikasi</label>
                    <input type="datetime-local" name="tanggal_publikasi" class="form-control-saas" value="{{ old('tanggal_publikasi', optional($pengumuman->tanggal_publikasi)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label-saas">Ganti Lampiran (Opsional)</label>
                    <input type="file" name="lampiran" class="form-control-saas" accept=".pdf,.jpg,.jpeg,.png">
                    @if($pengumuman->lampiran)
                        <small class="text-success d-block mt-1"><i class="fas fa-paperclip me-1"></i>Lampiran saat ini terunggah</small>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-saas px-4">Batal</a>
                <button type="submit" class="btn btn-saas-primary px-4">
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
