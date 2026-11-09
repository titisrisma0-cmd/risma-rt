@extends('layouts.app')

@section('title', 'Kegiatan Warga RT & RW')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Layanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kegiatan Warga</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-calendar-check text-success me-2"></i>Pengelolaan Kegiatan RT & RW</h1>
            <p class="page-subtitle">Agenda kegiatan warga, pendaftaran partisipasi peserta, serta dokumentasi terjadwal per periode.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($canManage)
                <button type="button" class="btn btn-saas-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan">
                    <i class="fas fa-plus-circle me-1"></i> Buat Kegiatan Baru
                </button>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-saas alert-saas-success d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-check-circle fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-saas alert-saas-danger d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-circle fs-5"></i>
            <div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- FILTER BAR: Modern Clean Green & White SaaS Style --}}
    <div class="saas-card p-4 mb-4" style="background: #ffffff; border: 1px solid #E9EEEA; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <form method="GET" action="{{ route('kegiatan.index') }}">
            @if(request('tingkat'))
                <input type="hidden" name="tingkat" value="{{ request('tingkat') }}">
            @endif
            @if(request('cakupan'))
                <input type="hidden" name="cakupan" value="{{ request('cakupan') }}">
            @endif

            <div class="row g-3 align-items-center">
                {{-- A. Kolom Pencarian Luas dengan Ikon di Kiri --}}
                <div class="col-12 col-lg-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pencarian Kegiatan</label>
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary" style="font-size: 0.95rem; color: #758078 !important; pointer-events: none;"></i>
                        <input type="text" 
                               name="q" 
                               value="{{ $search }}" 
                               placeholder="Cari judul agenda, deskripsi, atau lokasi..." 
                               class="form-control form-control-saas ps-5" 
                               style="height: 44px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 0.9rem; transition: border-color 0.2s, box-shadow 0.2s;">
                    </div>
                </div>

                {{-- B. Filter Dropdown Bulan --}}
                <div class="col-6 col-sm-3 col-lg-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Bulan</label>
                    <select name="bulan" class="form-select form-control-saas" style="height: 44px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 0.88rem;">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- B. Filter Dropdown Tahun --}}
                <div class="col-6 col-sm-3 col-lg-1">
                    <label class="form-label small fw-semibold text-secondary mb-1">Tahun</label>
                    <select name="tahun" class="form-select form-control-saas" style="height: 44px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 0.88rem;">
                        @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- B. Filter Dropdown Status --}}
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                    <select name="status" class="form-select form-control-saas" style="height: 44px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 0.88rem;">
                        <option value="">Semua Status</option>
                        <option value="dibuka" {{ $status === 'dibuka' ? 'selected' : '' }}>Akan Datang / Dibuka</option>
                        <option value="berlangsung" {{ $status === 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                        <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ $status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                {{-- C. Action Buttons: Terapkan Filter & Reset --}}
                <div class="col-12 col-sm-6 col-lg-2 d-flex gap-2 align-items-end pt-sm-4">
                    <button type="submit" class="btn btn-emerald text-white flex-grow-1 d-flex align-items-center justify-content-center" style="height: 44px; border-radius: 12px; font-weight: 600; font-size: 0.88rem; white-space: nowrap;">
                        <i class="fas fa-filter me-1"></i> Terapkan Filter
                    </button>
                    @if(request()->hasAny(['bulan', 'tahun', 'status', 'q']))
                        <a href="{{ route('kegiatan.index', array_filter(['tingkat' => request('tingkat'), 'cakupan' => request('cakupan')])) }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-3" style="height: 44px; border-radius: 12px; font-weight: 500; font-size: 0.88rem; white-space: nowrap;" title="Reset Filter">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Info Bar Status Aktif --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top small text-secondary">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-soft-emerald"><i class="fas fa-calendar-alt me-1"></i>Periode: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</span>
                    @if($status)
                        <span class="badge-soft-info"><i class="fas fa-tag me-1"></i>Status: {{ ucfirst($status) }}</span>
                    @endif
                    @if($search)
                        <span class="badge-soft-warning"><i class="fas fa-search me-1"></i>Kata Kunci: "{{ $search }}"</span>
                    @endif
                    @if(request('tingkat') || request('cakupan'))
                        <span class="badge-soft-primary"><i class="fas fa-layer-group me-1"></i>Cakupan: {{ strtoupper(request('tingkat') ?: request('cakupan')) }}</span>
                    @endif
                </div>
                <div class="text-secondary small">
                    Total: <strong>{{ $allKegiatans->total() }}</strong> kegiatan ditemukan
                </div>
            </div>
        </form>
    </div>

    {{-- Tabs Grouping: Bulan Ini, Bulan Mendatang, Bulan Sebelumnya --}}
    <div class="saas-card mb-4 p-2">
        <ul class="nav nav-tabs-saas" id="kegiatanGroupTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="bulan-ini-tab" data-bs-toggle="pill" data-bs-target="#tab-bulan-ini" type="button" role="tab">
                    <i class="fas fa-calendar-day me-1 text-success"></i> Kegiatan Bulan Ini ({{ $kegiatanBulanIni->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bulan-mendatang-tab" data-bs-toggle="pill" data-bs-target="#tab-bulan-mendatang" type="button" role="tab">
                    <i class="fas fa-calendar-plus me-1 text-info"></i> Bulan Mendatang ({{ $kegiatanBulanMendatang->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bulan-sebelumnya-tab" data-bs-toggle="pill" data-bs-target="#tab-bulan-sebelumnya" type="button" role="tab">
                    <i class="fas fa-archive me-1 text-warning"></i> Arsip Kegiatan ({{ $kegiatanBulanSebelumnya->count() }})
                </button>
            </li>
        </ul>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-content" id="kegiatanGroupTabContent">
        {{-- ==================== TAB 1: BULAN INI ==================== --}}
        <div class="tab-pane fade show active" id="tab-bulan-ini" role="tabpanel">
            <div class="row g-4">
                @forelse($kegiatanBulanIni as $kegiatan)
                    @include('layanan.partials.kegiatan-card', ['kegiatan' => $kegiatan])
                @empty
                    <div class="col-12">
                        <div class="saas-card p-5 text-center text-secondary">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-calendar-times"></i></div>
                                <h6 class="empty-state-title">Tidak Ada Kegiatan Bulan Ini</h6>
                                <p class="empty-state-desc">Belum ada kegiatan terjadwal pada bulan {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}.</p>
                            </div>
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
                        <div class="saas-card p-5 text-center text-secondary">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-calendar-check"></i></div>
                                <h6 class="empty-state-title">Belum Ada Agenda Mendatang</h6>
                                <p class="empty-state-desc">Belum ada agenda kegiatan yang direncanakan untuk bulan mendatang.</p>
                            </div>
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
                        <div class="saas-card p-5 text-center text-secondary">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-archive"></i></div>
                                <h6 class="empty-state-title">Belum Ada Arsip Kegiatan</h6>
                                <p class="empty-state-desc">Belum ada riwayat arsip kegiatan lampau yang tersimpan.</p>
                            </div>
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
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-plus text-success me-2"></i>Buat Kegiatan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('kegiatan.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label-saas">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input name="judul" class="form-control-saas" required placeholder="Contoh: Kerja Bakti Bersih Lingkungan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-saas">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select-saas" required>
                                <option value="kerja_bakti">Kerja Bakti</option>
                                <option value="sosial">Sosial</option>
                                <option value="keagamaan">Keagamaan</option>
                                <option value="olahraga">Olahraga</option>
                                <option value="rapat">Rapat</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-saas">Status Awal</label>
                            <select name="status" class="form-select-saas">
                                <option value="draft">Draft</option>
                                <option value="dibuka" selected>Dibuka</option>
                                <option value="berlangsung">Berlangsung</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-saas">Cakupan Wilayah</label>
                            <select name="cakupan" class="form-select-saas" {{ $isRt && !$isRw ? 'disabled' : '' }}>
                                <option value="rw">Tingkat RW</option>
                                <option value="rt" {{ $isRt && !$isRw ? 'selected' : '' }}>Tingkat RT</option>
                            </select>
                            @if($isRt && !$isRw)
                                <input type="hidden" name="cakupan" value="rt">
                            @endif
                        </div>

                        @if($isRw)
                            <div class="col-md-4">
                                <label class="form-label-saas">RT Terkait (Bila Cakupan RT)</label>
                                <select name="rt_id" class="form-select-saas">
                                    <option value="">Semua RT / Wilayah RW</option>
                                    @foreach($rts as $rt)
                                        <option value="{{ $rt->id }}">RT {{ $rt->kode_rt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="col-md-4">
                                <label class="form-label-saas">Kuota Peserta (Opsional)</label>
                                <input type="number" min="1" name="kuota_peserta" class="form-control-saas" placeholder="Tak Terbatas">
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label-saas">Tanggal & Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_mulai" class="form-control-saas" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-saas">Tanggal & Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_selesai" class="form-control-saas" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label-saas">Lokasi Kegiatan <span class="text-danger">*</span></label>
                            <input name="lokasi" class="form-control-saas" required placeholder="Contoh: Balai Warga / Lapangan Serbaguna">
                        </div>

                        @if($isRw)
                            <div class="col-md-4">
                                <label class="form-label-saas">Kuota Peserta (Opsional)</label>
                                <input type="number" min="1" name="kuota_peserta" class="form-control-saas" placeholder="Tak Terbatas">
                            </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label-saas">Deskripsi Kegiatan</label>
                            <textarea name="deskripsi" class="form-control-saas" rows="3" placeholder="Jelaskan tujuan kegiatan, perlengkapan yang perlu dibawa, dll..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-saas-primary">
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
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom py-3 px-4">
                        <h5 class="modal-title text-danger fw-bold"><i class="fas fa-trash-alt me-2"></i>Hapus Kegiatan Dibatalkan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('kegiatan.soft-delete', $kegiatan) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-start p-4">
                            <p class="text-secondary mb-2">Apakah Anda yakin ingin menghapus kegiatan yang telah dibatalkan ini dari daftar utama?</p>
                            <div class="p-3 rounded-3 mb-3 bg-light border">
                                <strong class="text-dark d-block">{{ $kegiatan->judul }}</strong>
                                <small class="text-muted">Jadwal: {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->translatedFormat('d F Y, H:i') : '-' }}</small><br>
                                <small class="text-muted">Lokasi: {{ $kegiatan->lokasi }}</small>
                            </div>
                            <div class="alert alert-saas alert-saas-warning py-2 px-3 small mb-0">
                                <i class="fas fa-info-circle me-1"></i> Tindakan penghapusan menggunakan <strong>soft delete</strong> dan akan dicatat secara permanen ke dalam <strong>Audit Trail</strong>.
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 px-4">
                            <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Ya, Hapus Kegiatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

{{-- ==================== MODAL DETAIL KEGIATAN ==================== --}}
@foreach($allVisibleKegiatans as $kegiatan)
    <div class="modal fade" id="modalDetailKegiatan{{ $kegiatan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-check text-emerald me-2"></i>Detail Agenda Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex gap-2 align-items-center mb-3">
                        <span class="badge {{ $kegiatan->cakupan === 'rw' ? 'badge-soft-emerald' : 'badge-soft-info' }}">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $kegiatan->cakupan === 'rw' ? 'Tingkat RW' : ($kegiatan->rt ? 'RT ' . $kegiatan->rt->kode_rt : 'Tingkat RT') }}
                        </span>
                        <span class="badge badge-soft-secondary text-uppercase">
                            {{ str_replace('_', ' ', $kegiatan->kategori) }}
                        </span>
                        @if($kegiatan->status === 'dibuka')
                            <span class="badge-soft-info"><i class="fas fa-bullhorn me-1"></i>Akan Datang</span>
                        @elseif($kegiatan->status === 'berlangsung')
                            <span class="badge-soft-warning"><i class="fas fa-play me-1"></i>Berlangsung</span>
                        @elseif($kegiatan->status === 'selesai')
                            <span class="badge-soft-emerald"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                        @elseif($kegiatan->status === 'dibatalkan')
                            <span class="badge-soft-danger"><i class="fas fa-ban me-1"></i>Dibatalkan</span>
                        @else
                            <span class="badge-soft-secondary">Draft</span>
                        @endif
                    </div>

                    <h5 class="fw-bold text-dark mb-2">{{ $kegiatan->judul }}</h5>
                    <p class="text-secondary small mb-4" style="line-height: 1.6;">
                        {{ $kegiatan->deskripsi ?? 'Tidak ada deskripsi rinci untuk kegiatan ini.' }}
                    </p>

                    <div class="p-3 rounded-3 bg-light border small mb-3" style="border-color: #E9EEEA !important; border-radius: 12px;">
                        <div class="mb-2">
                            <span class="text-secondary d-block">Waktu Pelaksanaan:</span>
                            <strong class="text-dark">
                                {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->translatedFormat('l, d F Y · H:i') : '-' }} s/d 
                                {{ $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->translatedFormat('d F Y · H:i') : '-' }} WIB
                            </strong>
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary d-block">Lokasi:</span>
                            <strong class="text-dark">{{ $kegiatan->lokasi }}</strong>
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary d-block">Penyelenggara:</span>
                            <strong class="text-dark">{{ $kegiatan->cakupan === 'rw' ? 'Pengurus RW' : ($kegiatan->rt ? 'RT ' . $kegiatan->rt->kode_rt : 'Pengurus RT') }}</strong> (Dibuat oleh {{ $kegiatan->pembuat?->name ?? 'Pengurus' }})
                        </div>
                        <div>
                            <span class="text-secondary d-block">Partisipasi Peserta:</span>
                            <strong class="text-dark">{{ $kegiatan->peserta_count ?? ($kegiatan->peserta ? $kegiatan->peserta->count() : 0) }} Peserta</strong> {{ $kegiatan->kuota_peserta ? 'dari kuota ' . $kegiatan->kuota_peserta . ' orang' : '(Tanpa batas kuota)' }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    @if($kegiatan->peserta && $kegiatan->peserta->contains('user_id', Auth::id()))
                        <form method="POST" action="{{ route('kegiatan.batal-daftar', $kegiatan) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-user-minus me-1"></i> Batal Daftar
                            </button>
                        </form>
                    @elseif(in_array($kegiatan->status, ['dibuka', 'berlangsung']))
                        <form method="POST" action="{{ route('kegiatan.daftar', $kegiatan) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-emerald text-white">
                                <i class="fas fa-user-plus me-1"></i> Daftar Kegiatan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
