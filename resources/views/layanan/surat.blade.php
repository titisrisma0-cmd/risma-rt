@extends('layouts.app')

@php
    $statusClass = [
        'pending' => 'bg-warning text-dark',
        'menunggu_rt' => 'bg-warning text-dark',
        'diproses' => 'bg-info',
        'diproses_rt' => 'bg-info',
        'menunggu_rw' => 'bg-purple-600 text-white',
        'siap_diambil' => 'bg-success',
        'disetujui' => 'bg-success',
        'selesai' => 'bg-primary',
        'ditolak' => 'bg-danger',
    ];

    $statusLabel = fn ($status) => ucwords(str_replace('_', ' ', $status));
@endphp

@section('content')
<div class="container-fluid px-4 layanan-page">
    <div class="page-head">
        <div>
            <h2><i class="fas fa-envelope-open-text text-info me-2"></i> Surat Menyurat</h2>
            <p>Pengajuan surat pengantar RW yang mudah dipantau dari rumah.</p>
        </div>
        <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(!$isPengurus)
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="surat-guide">
                    <i class="fas fa-file-circle-plus"></i>
                    <strong>Ajukan</strong>
                    <span>Pilih jenis surat, tulis keperluan, lalu unggah foto KTP atau KK.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="surat-guide">
                    <i class="fas fa-clock-rotate-left"></i>
                    <strong>Pantau</strong>
                    <span>Status akan berubah dari Pending, Diproses, sampai Siap Diambil.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="surat-guide">
                    <i class="fas fa-print"></i>
                    <strong>Ambil / Cetak</strong>
                    <span>Jika disetujui, nomor surat otomatis dibuat dan PDF dapat dicetak.</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card">
                    <div class="card-header-custom">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Ajukan Surat Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('layanan.surat.store') }}" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label">Jenis Surat</label>
                            <select class="form-select mb-3 @error('jenis_surat_id') is-invalid @enderror" name="jenis_surat_id" required>
                                <option value="">Pilih jenis surat</option>
                                @foreach($jenisSurats as $jenis)
                                    <option value="{{ $jenis->id }}" @selected(old('jenis_surat_id') == $jenis->id)>{{ $jenis->nama }}</option>
                                @endforeach
                            </select>
                            @error('jenis_surat_id') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

                            <label class="form-label">Keperluan</label>
                            <textarea class="form-control mb-3 @error('keperluan') is-invalid @enderror" name="keperluan" rows="4" placeholder="Contoh: Untuk melengkapi administrasi sekolah / bank / domisili." required>{{ old('keperluan') }}</textarea>
                            @error('keperluan') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

                            <label class="form-label">Berkas Pendukung</label>
                            <input class="form-control mb-2 @error('berkas_pendukung') is-invalid @enderror" type="file" name="berkas_pendukung[]" multiple accept="image/*" required>
                            <small class="text-secondary d-block mb-3">Upload foto KTP/KK. Format gambar, maksimal 2MB per file.</small>
                            @error('berkas_pendukung') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
                            @error('berkas_pendukung.*') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

                            <button class="btn btn-gradient w-100">
                                <i class="fas fa-paper-plane me-1"></i> Ajukan Surat
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="card-header-custom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i> Riwayat Pengajuan Surat</h5>
                        <span class="badge bg-primary">{{ $pengajuanSaya->count() }} Pengajuan</span>
                    </div>
                    <div class="card-body">
                        @forelse($pengajuanSaya as $surat)
                            <div class="surat-history-item">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <strong>{{ $surat->jenisSurat->nama ?? 'Surat Pengantar' }}</strong>
                                        <span class="badge {{ $statusClass[$surat->status] ?? 'bg-secondary' }}">{{ $statusLabel($surat->status) }}</span>
                                    </div>
                                    <span>{{ $surat->nomor_surat ?: $surat->nomor_pengajuan }}</span>
                                    <p>{{ \Illuminate\Support\Str::limit($surat->keperluan, 130) }}</p>
                                    @if($surat->catatan)
                                        <small class="surat-note"><i class="fas fa-message me-1"></i> {{ $surat->catatan }}</small>
                                    @endif
                                </div>
                                <div class="text-lg-end">
                                    <small class="text-secondary d-block mb-2">{{ $surat->created_at->format('d/m/Y H:i') }}</small>
                                    @if($surat->nomor_surat)
                                        <a class="btn btn-sm btn-outline-info" href="{{ route('layanan.surat.cetak', $surat) }}">
                                            <i class="fas fa-print me-1"></i> Cetak PDF
                                        </a>
                                    @else
                                        <span class="btn btn-sm btn-outline-secondary disabled">Menunggu PDF</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">Belum ada pengajuan surat. Gunakan form di samping untuk membuat pengajuan pertama.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="surat-guide">
                    <i class="fas fa-inbox"></i>
                    <strong>Surat Masuk</strong>
                    <span>Periksa data warga, jenis surat, keperluan, dan berkas pendukung.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="surat-guide">
                    <i class="fas fa-clipboard-check"></i>
                    <strong>Validasi</strong>
                    <span>Ubah status menjadi Diproses, Siap Diambil, Selesai, atau Ditolak.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="surat-guide">
                    <i class="fas fa-file-pdf"></i>
                    <strong>Cetak PDF</strong>
                    <span>Nomor surat dibuat otomatis saat pengajuan disetujui.</span>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header-custom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-list-check me-2"></i> Manajemen Surat Masuk</h5>
                <span class="badge bg-primary">{{ $suratMasuk->count() }} Pengajuan</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Warga</th>
                                <th>Pengajuan</th>
                                <th>Keperluan & Berkas</th>
                                <th>Status</th>
                                <th style="min-width: 310px;">Catatan / Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suratMasuk as $surat)
                                <tr>
                                    <td>
                                        <strong>{{ $surat->user->name ?? '-' }}</strong><br>
                                        <small class="text-secondary">NIK: {{ $surat->user->profile->nik ?? '-' }}</small><br>
                                        <small class="text-secondary">{{ $surat->user->profile->alamat ?? '-' }}</small>
                                    </td>
                                    <td>
                                        {{ $surat->jenisSurat->nama ?? '-' }}<br>
                                        <small class="text-secondary">{{ $surat->nomor_surat ?: $surat->nomor_pengajuan }}</small>
                                    </td>
                                    <td>
                                        <div>{{ \Illuminate\Support\Str::limit($surat->keperluan, 95) }}</div>
                                        @if(!empty($surat->berkas_pendukung))
                                            <div class="surat-file-list mt-2">
                                                @foreach($surat->berkas_pendukung as $index => $file)
                                                    <a target="_blank" href="{{ asset('storage/' . $file) }}">
                                                        <i class="fas fa-image me-1"></i> Berkas {{ $index + 1 }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClass[$surat->status] ?? 'bg-secondary' }}">{{ $statusLabel($surat->status) }}</span>
                                        @if($surat->approved_at)
                                            <small class="text-secondary d-block mt-2">{{ $surat->approved_at->format('d/m/Y H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($surat->catatan)
                                            <small class="surat-note d-block mb-2">{{ $surat->catatan }}</small>
                                        @endif
                                        <form method="POST" action="{{ route('layanan.surat.update', $surat) }}" class="surat-action-form">
                                            @csrf
                                            @method('PATCH')
                                            <textarea class="form-control form-control-sm" name="catatan" rows="2" placeholder="Catatan untuk warga">{{ $surat->catatan }}</textarea>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button name="status" value="diproses" class="btn btn-sm btn-info">Diproses</button>
                                                <button name="status" value="siap_diambil" class="btn btn-sm btn-success">Setujui</button>
                                                @if(!empty($isRt))
                                                    <button name="status" value="menunggu_rw" class="btn btn-sm text-white" style="background: #8b5cf6;" title="Eskalasi persetujuan ke RW">Ke RW</button>
                                                @endif
                                                <button name="status" value="ditolak" class="btn btn-sm btn-outline-danger">Tolak</button>
                                                @if($surat->nomor_surat)
                                                    <a class="btn btn-sm btn-outline-light" href="{{ route('layanan.surat.cetak', $surat) }}">
                                                        <i class="fas fa-print me-1"></i> Cetak PDF
                                                    </a>
                                                @endif
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Belum ada surat masuk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@include('layanan.partials.styles')
@endsection
