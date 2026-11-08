@extends('layouts.app')

@section('title', 'Layanan Surat Menyurat')

@php
    $statusClass = [
        'pending' => 'badge-soft-warning',
        'menunggu_rt' => 'badge-soft-warning',
        'diproses' => 'badge-soft-info',
        'diproses_rt' => 'badge-soft-info',
        'menunggu_rw' => 'badge-soft-primary',
        'siap_diambil' => 'badge-soft-emerald',
        'disetujui' => 'badge-soft-emerald',
        'selesai' => 'badge-soft-emerald',
        'ditolak' => 'badge-soft-danger',
    ];

    $statusLabel = fn ($status) => ucwords(str_replace('_', ' ', $status));
@endphp

@section('content')
<div class="content-container layanan-page">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title mb-1">
                <i class="fas fa-envelope-open-text text-emerald me-2"></i> Surat Menyurat
            </h1>
            <p class="page-subtitle">Pengajuan surat pengantar RT & RW yang transparan dan dapat dipantau langsung dari rumah.</p>
        </div>
        <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    @if(!$isPengurus)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="saas-card p-3 h-100 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                        <i class="fas fa-file-circle-plus"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">1. Ajukan Surat</strong>
                        <span class="text-secondary small">Pilih jenis surat, tulis keperluan, dan unggah foto berkas pendukung.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="saas-card p-3 h-100 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper" style="background-color: #FEF3C7; color: #D97706;">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">2. Pantau Proses</strong>
                        <span class="text-secondary small">Status diperbarui oleh Ketua RT & RW hingga surat selesai diproses.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="saas-card p-3 h-100 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper" style="background-color: #E0F2FE; color: #0284C7;">
                        <i class="fas fa-print"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">3. Cetak PDF / Ambil</strong>
                        <span class="text-secondary small">Nomor surat otomatis terbit dan file resmi PDF langsung dapat dicetak.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5 col-xl-4">
                <div class="saas-card p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-plus-circle text-emerald me-2"></i> Ajukan Surat Baru</h6>
                    <form method="POST" action="{{ route('layanan.surat.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Jenis Surat <span class="text-danger">*</span></label>
                            <select class="form-select form-control-saas @error('jenis_surat_id') is-invalid @enderror" name="jenis_surat_id" required>
                                <option value="">-- Pilih jenis surat --</option>
                                @foreach($jenisSurats as $jenis)
                                    <option value="{{ $jenis->id }}" @selected(old('jenis_surat_id') == $jenis->id)>{{ $jenis->nama }}</option>
                                @endforeach
                            </select>
                            @error('jenis_surat_id') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Keperluan <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-saas @error('keperluan') is-invalid @enderror" name="keperluan" rows="4" placeholder="Contoh: Untuk melengkapi administrasi sekolah / bank / domisili." required>{{ old('keperluan') }}</textarea>
                            @error('keperluan') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Berkas Pendukung <span class="text-danger">*</span></label>
                            <input class="form-control form-control-saas @error('berkas_pendukung') is-invalid @enderror" type="file" name="berkas_pendukung[]" multiple accept="image/*" required>
                            <small class="text-secondary d-block mt-1">Upload foto KTP/KK. Format gambar JPG/PNG, maksimal 2MB per file.</small>
                            @error('berkas_pendukung') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            @error('berkas_pendukung.*') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        <button class="btn btn-emerald text-white w-100 py-2">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan Permohonan
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7 col-xl-8">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-folder-open text-emerald me-2"></i> Riwayat Pengajuan Surat</h6>
                        <span class="badge-soft-emerald">{{ $pengajuanSaya->count() }} Pengajuan</span>
                    </div>
                    <div class="p-3">
                        @forelse($pengajuanSaya as $surat)
                            <div class="saas-card p-3 mb-3 border bg-light">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <strong class="text-dark fs-6">{{ $surat->jenisSurat->nama ?? 'Surat Pengantar' }}</strong>
                                            <span class="{{ $statusClass[$surat->status] ?? 'badge-soft-secondary' }}">{{ $statusLabel($surat->status) }}</span>
                                        </div>
                                        <div class="font-monospace text-emerald small fw-semibold mb-1">{{ $surat->nomor_surat ?: $surat->nomor_pengajuan }}</div>
                                        <p class="text-secondary small mb-2">{{ \Illuminate\Support\Str::limit($surat->keperluan, 130) }}</p>
                                        @if($surat->catatan)
                                            <div class="small p-2 rounded bg-white border text-secondary"><i class="fas fa-comment-dots text-warning me-1"></i> {{ $surat->catatan }}</div>
                                        @endif
                                    </div>
                                    <div class="text-md-end d-flex flex-column justify-content-between">
                                        <small class="text-secondary d-block mb-2">{{ $surat->created_at->format('d/m/Y H:i') }}</small>
                                        <div>
                                            @if($surat->nomor_surat)
                                                <a class="btn btn-sm btn-outline-success" href="{{ route('layanan.surat.cetak', $surat) }}" target="_blank">
                                                    <i class="fas fa-print me-1"></i> Cetak PDF
                                                </a>
                                            @else
                                                <span class="btn btn-sm btn-outline-secondary disabled">Menunggu PDF</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-secondary">
                                <i class="fas fa-envelope-open fa-3x text-muted mb-3 d-block opacity-50"></i>
                                Belum ada pengajuan surat. Gunakan formulir di samping untuk mengajukan surat pertama.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- VIEW UNTUK PENGURUS RT / RW --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="saas-card p-3 h-100 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Surat Masuk</strong>
                        <span class="text-secondary small">Periksa data warga pemohon, jenis surat, dan kelengkapan berkas KTP/KK.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="saas-card p-3 h-100 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper" style="background-color: #FEF3C7; color: #D97706;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Validasi Cepat</strong>
                        <span class="text-secondary small">Setujui langsung, proses berkas, teruskan ke RW, atau tolak dengan catatan.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="saas-card p-3 h-100 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper" style="background-color: #E0F2FE; color: #0284C7;">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Penerbitan Otomatis</strong>
                        <span class="text-secondary small">Nomor surat resmi digenerate otomatis saat pengajuan disetujui.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="saas-card overflow-hidden">
            <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-check text-emerald me-2"></i> Manajemen Surat Masuk</h6>
                <span class="badge-soft-emerald">{{ $suratMasuk->count() }} Pengajuan</span>
            </div>
            <div class="table-responsive">
                <table class="table-saas">
                    <thead>
                        <tr>
                            <th>Warga</th>
                            <th>Pengajuan</th>
                            <th>Keperluan & Berkas</th>
                            <th>Status</th>
                            <th style="min-width: 320px;">Catatan / Aksi Pengurus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratMasuk as $surat)
                            <tr>
                                <td>
                                    <strong class="text-dark">{{ $surat->user->name ?? '-' }}</strong><br>
                                    <small class="text-secondary">NIK: {{ $surat->user->profile->nik ?? '-' }}</small><br>
                                    <small class="text-secondary">{{ $surat->user->profile->alamat ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge-soft-info d-inline-block mb-1">{{ $surat->jenisSurat->nama ?? '-' }}</span><br>
                                    <small class="font-monospace text-dark fw-medium">{{ $surat->nomor_surat ?: $surat->nomor_pengajuan }}</small>
                                </td>
                                <td>
                                    <div class="text-dark small mb-1">{{ \Illuminate\Support\Str::limit($surat->keperluan, 95) }}</div>
                                    @if(!empty($surat->berkas_pendukung))
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($surat->berkas_pendukung as $index => $file)
                                                <a target="_blank" href="{{ asset('storage/' . $file) }}" class="badge bg-light text-secondary border text-decoration-none">
                                                    <i class="fas fa-image text-emerald me-1"></i> Berkas {{ $index + 1 }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $statusClass[$surat->status] ?? 'badge-soft-secondary' }}">{{ $statusLabel($surat->status) }}</span>
                                    @if($surat->approved_at)
                                        <small class="text-secondary d-block mt-1">{{ $surat->approved_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($surat->catatan)
                                        <div class="small p-2 rounded bg-light border text-secondary mb-2"><i class="fas fa-comment-dots text-warning me-1"></i>{{ $surat->catatan }}</div>
                                    @endif
                                    <form method="POST" action="{{ route('layanan.surat.update', $surat) }}">
                                        @csrf
                                        @method('PATCH')
                                        <textarea class="form-control form-control-sm form-control-saas mb-2" name="catatan" rows="2" placeholder="Catatan untuk warga">{{ $surat->catatan }}</textarea>
                                        <div class="d-flex flex-wrap gap-1">
                                            <button name="status" value="diproses" class="btn btn-sm btn-outline-info">Diproses</button>
                                            <button name="status" value="siap_diambil" class="btn btn-sm btn-emerald text-white">Setujui</button>
                                            @if(!empty($isRt))
                                                <button name="status" value="menunggu_rw" class="btn btn-sm btn-outline-primary" title="Eskalasi persetujuan ke RW">Ke RW</button>
                                            @endif
                                            <button name="status" value="ditolak" class="btn btn-sm btn-outline-danger">Tolak</button>
                                            @if($surat->nomor_surat)
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('layanan.surat.cetak', $surat) }}" target="_blank">
                                                    <i class="fas fa-print me-1 text-emerald"></i> Cetak PDF
                                                </a>
                                            @endif
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                                    Belum ada surat masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
