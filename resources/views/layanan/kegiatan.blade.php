@extends('layouts.app')

@section('title', 'Kegiatan Warga RT & RW')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1">
                <i class="fas fa-calendar-check text-emerald-400 me-2"></i>Pengelolaan Kegiatan RT & RW
            </h1>
            <p class="text-white-50 mb-0">Agenda kegiatan warga, pendaftaran peserta, serta dokumentasi terjadwal per periode</p>
        </div>
        <div class="d-flex gap-2">
            @if($canManage)
                <button type="button" class="btn btn-emerald text-white px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan" style="background: #10b981; border: none;">
                    <i class="fas fa-plus-circle me-1"></i> Buat Kegiatan Baru
                </button>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline-light px-3 py-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Bar: Bulan, Tahun, Status, Pencarian --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="GET" action="{{ route('kegiatan.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Bulan</label>
                <select name="bulan" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Tahun</label>
                <select name="tahun" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Status Kegiatan</label>
                <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="dibuka" {{ $status === 'dibuka' ? 'selected' : '' }}>Dibuka</option>
                    <option value="berlangsung" {{ $status === 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ $status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label text-white-50 small mb-1">Cari Kegiatan</label>
                <div class="input-group">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul, lokasi, atau deskripsi..." class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <button type="submit" class="btn btn-emerald text-white" style="background: #10b981; border: none;">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </div>

            <div class="col-md-2">
                @if(request()->hasAny(['bulan', 'tahun', 'status', 'q']))
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-outline-light w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                @else
                    <span class="text-white-50 small d-block text-end">Default: <strong>{{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</strong></span>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabs Grouping: Bulan Ini, Bulan Mendatang, Bulan Sebelumnya --}}
    <ul class="nav nav-pills mb-4 p-2 rounded" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1);" id="kegiatanGroupTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active text-white" id="bulan-ini-tab" data-bs-toggle="pill" data-bs-target="#tab-bulan-ini" type="button" role="tab">
                <i class="fas fa-calendar-day me-1 text-emerald-400"></i> Kegiatan Bulan Ini ({{ $kegiatanBulanIni->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="bulan-mendatang-tab" data-bs-toggle="pill" data-bs-target="#tab-bulan-mendatang" type="button" role="tab">
                <i class="fas fa-calendar-plus me-1 text-info"></i> Kegiatan Bulan Mendatang ({{ $kegiatanBulanMendatang->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-white" id="bulan-sebelumnya-tab" data-bs-toggle="pill" data-bs-target="#tab-bulan-sebelumnya" type="button" role="tab">
                <i class="fas fa-archive me-1 text-warning"></i> Arsip & Bulan Sebelumnya ({{ $kegiatanBulanSebelumnya->count() }})
            </button>
        </li>
    </ul>

    {{-- Tab Contents --}}
    <div class="tab-content" id="kegiatanGroupTabContent">
        {{-- ==================== TAB 1: BULAN INI ==================== --}}
        <div class="tab-pane fade show active" id="tab-bulan-ini" role="tabpanel">
            <div class="row g-4">
                @forelse($kegiatanBulanIni as $kegiatan)
                    @include('layanan.partials.kegiatan-card', ['kegiatan' => $kegiatan])
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center border-0 text-white-50" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                            <i class="fas fa-calendar-times fa-3x mb-3 text-secondary d-block"></i>
                            <h5>Tidak ada kegiatan pada bulan {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</h5>
                            <p class="small mb-0">Silakan periksa tab kegiatan bulan mendatang atau buat kegiatan baru.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ==================== TAB 2: BULAN MENDATANG ==================== --}}
        <div class="tab-pane fade" id="tab-bulan-mendatang" role="tabpanel">
            <div class="row g-4">
                @forelse($kegiatanBulanMendatang as $kegiatan)
                    @include('layanan.partials.kegiatan-card', ['kegiatan' => $kegiatan])
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center border-0 text-white-50" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                            <i class="fas fa-calendar-check fa-3x mb-3 text-secondary d-block"></i>
                            <h5>Belum ada agenda kegiatan yang direncanakan untuk bulan mendatang</h5>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ==================== TAB 3: BULAN SEBELUMNYA / ARSIP ==================== --}}
        <div class="tab-pane fade" id="tab-bulan-sebelumnya" role="tabpanel">
            <div class="row g-4">
                @forelse($kegiatanBulanSebelumnya as $kegiatan)
                    @include('layanan.partials.kegiatan-card', ['kegiatan' => $kegiatan])
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center border-0 text-white-50" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                            <i class="fas fa-archive fa-3x mb-3 text-secondary d-block"></i>
                            <h5>Belum ada arsip kegiatan lama yang tercatat</h5>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL TAMBAH KEGIATAN ==================== --}}
@if($canManage)
<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px;">
            <div class="modal-header border-bottom border-white-10">
                <h5 class="modal-title"><i class="fas fa-calendar-plus text-emerald-400 me-2"></i>Buat Kegiatan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('kegiatan.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label text-white-50 small">Judul Kegiatan *</label>
                            <input name="judul" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required placeholder="Contoh: Kerja Bakti Bersih Lingkungan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white-50 small">Kategori *</label>
                            <select name="kategori" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                <option value="kerja_bakti">Kerja Bakti</option>
                                <option value="sosial">Sosial</option>
                                <option value="keagamaan">Keagamaan</option>
                                <option value="olahraga">Olahraga</option>
                                <option value="rapat">Rapat</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50 small">Status Awal</label>
                            <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                <option value="draft">Draft</option>
                                <option value="dibuka" selected>Dibuka</option>
                                <option value="berlangsung">Berlangsung</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white-50 small">Cakupan Wilayah</label>
                            <select name="cakupan" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" {{ $isRt && !$isRw ? 'disabled' : '' }}>
                                <option value="rw">Tingkat RW</option>
                                <option value="rt" {{ $isRt && !$isRw ? 'selected' : '' }}>Tingkat RT</option>
                            </select>
                            @if($isRt && !$isRw)
                                <input type="hidden" name="cakupan" value="rt">
                            @endif
                        </div>

                        @if($isRw)
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">RT Terkait (Bila Cakupan RT)</label>
                                <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                    <option value="">Semua RT / Wilayah RW</option>
                                    @foreach($rts as $rt)
                                        <option value="{{ $rt->id }}">RT {{ $rt->kode_rt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">Kuota Peserta (Opsional)</label>
                                <input type="number" min="1" name="kuota_peserta" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Tak Terbatas">
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label text-white-50 small">Tanggal & Waktu Mulai *</label>
                            <input type="datetime-local" name="tanggal_mulai" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white-50 small">Tanggal & Waktu Selesai *</label>
                            <input type="datetime-local" name="tanggal_selesai" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label text-white-50 small">Lokasi Kegiatan *</label>
                            <input name="lokasi" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required placeholder="Contoh: Balai Warga / Lapangan Serbaguna">
                        </div>

                        @if($isRw)
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">Kuota Peserta (Opsional)</label>
                                <input type="number" min="1" name="kuota_peserta" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Tak Terbatas">
                            </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label text-white-50 small">Deskripsi Kegiatan</label>
                            <textarea name="deskripsi" class="form-control text-white" rows="3" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Jelaskan tujuan kegiatan, perlengkapan yang perlu dibawa, dll..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-white-10">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald text-white px-4" style="background: #10b981; border: none;">
                        <i class="fas fa-save me-1"></i> Simpan Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ==================== MODAL KONFIRMASI HAPUS KEGIATAN DIBATALKAN ==================== --}}
@php
    $allVisibleKegiatans = $kegiatanBulanIni->merge($kegiatanBulanMendatang)->merge($kegiatanBulanSebelumnya);
@endphp
@foreach($allVisibleKegiatans as $kegiatan)
    @if($kegiatan->status === 'dibatalkan' && $canManage && ($isRw || ($kegiatan->created_by === Auth::id() && $kegiatan->rt_id === Auth::user()->profile?->rt_id)))
        <div class="modal fade" id="modalHapusKegiatan{{ $kegiatan->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(239,68,68,0.4); border-radius: 12px;">
                    <div class="modal-header border-bottom border-white-10">
                        <h5 class="modal-title text-danger"><i class="fas fa-trash-alt me-2"></i>Hapus Kegiatan Dibatalkan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('kegiatan.soft-delete', $kegiatan) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-start">
                            <p class="text-white-50 mb-2">Apakah Anda yakin ingin menghapus kegiatan yang telah dibatalkan ini dari daftar utama?</p>
                            <div class="p-3 rounded mb-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.08);">
                                <strong class="text-white d-block">{{ $kegiatan->judul }}</strong>
                                <small class="text-white-50">Jadwal: {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->translatedFormat('d F Y, H:i') : '-' }}</small><br>
                                <small class="text-white-50">Lokasi: {{ $kegiatan->lokasi }}</small>
                            </div>
                            <div class="alert alert-warning py-2 px-3 small mb-0" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fcd34d;">
                                <i class="fas fa-info-circle me-1"></i> Tindakan penghapusan menggunakan <strong>soft delete</strong> dan akan dicatat secara permanen ke dalam <strong>Audit Trail</strong>.
                            </div>
                        </div>
                        <div class="modal-footer border-top border-white-10">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-trash-alt me-1"></i> Ya, Hapus Kegiatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
