@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Portal Warga — Dashboard')

@section('content')
<div class="py-2">
    {{-- Header Sambutan Warga --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-soft-primary px-3 py-1">
                    <i class="fas fa-home me-1"></i> Warga RT {{ $rtKode }} / RW 01
                </span>
                <span class="text-secondary small">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Selamat Datang, {{ $user->name }} 👋
            </h1>
            <p class="text-secondary mb-0">Portal Pelayanan Mandiri Warga Rukun Tetangga & Rukun Warga Digital.</p>
        </div>

        <div class="page-header-actions">
            <a href="{{ route('layanan.surat.index') }}" class="btn btn-emerald">
                <i class="fas fa-file-signature me-1"></i> Ajukan Surat
            </a>
            <a href="{{ route('pembayaran.create') }}" class="btn btn-light">
                <i class="fas fa-credit-card me-1 text-primary"></i> Bayar Iuran
            </a>
        </div>
    </div>

    {{-- Kartu Ringkasan Pribadi Warga (Donezo SaaS Metric Cards) --}}
    <div class="row g-3 mb-4">
        {{-- Kartu Keluarga --}}
        <div class="col-md-6 col-xl-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Kartu Keluarga</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark font-monospace" style="font-size: 1.25rem;">
                        {{ $kartuKeluarga ? $kartuKeluarga->no_kk : 'Belum Ditautkan' }}
                    </div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-users text-primary"></i> {{ $kartuKeluarga ? $kartuKeluarga->anggota->count() . ' Anggota Terdaftar' : 'Hubungi Ketua RT' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Iuran --}}
        <div class="col-md-6 col-xl-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Iuran Menunggu Verifikasi</span>
                    <div class="stat-icon-wrapper stat-icon-amber">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-warning">{{ $tagihanPending }} <small class="text-secondary fw-normal fs-6">Transaksi</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-hourglass-half text-warning"></i> Sedang diverifikasi RT
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Iuran Terbayar --}}
        <div class="col-md-6 col-xl-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Total Iuran Telah Lunas</span>
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-success font-monospace" style="font-size: 1.35rem;">
                        Rp {{ number_format($totalBayarLunas, 0, ',', '.') }}
                    </div>
                    <div class="stat-subtext text-success">
                        <i class="fas fa-circle-check"></i> Tercatat resmi lunas
                    </div>
                </div>
            </div>
        </div>

        {{-- Permohonan Surat Saya --}}
        <div class="col-md-6 col-xl-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Permohonan Surat Saya</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ $suratSaya->count() }} <small class="text-secondary fw-normal fs-6">Surat</small></div>
                    <div class="stat-subtext text-secondary">
                        <i class="fas fa-clipboard-check text-primary"></i> Pengajuan aktif & riwayat
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Menu Layanan Cepat Warga (Interactive SaaS Quick Cards) --}}
    <div class="saas-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title-saas">
                    <i class="fas fa-th-large text-success"></i> Layanan Mandiri Warga
                </h5>
                <small class="text-secondary">Pilih layanan cepat yang ingin Anda akses</small>
            </div>
            <span class="badge badge-soft-primary">Akses Cepat</span>
        </div>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('layanan.surat.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <span class="quick-service-title">Surat Pengantar</span>
                    <span class="quick-service-desc">Ajukan Surat</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('pembayaran.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <span class="quick-service-title">Iuran Warga</span>
                    <span class="quick-service-desc">Tagihan & Bayar</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('layanan.ronda.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <span class="quick-service-title">Ronda Malam</span>
                    <span class="quick-service-desc">Jadwal & Presensi</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('bank-sampah.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <span class="quick-service-title">Bank Sampah</span>
                    <span class="quick-service-desc">Tabungan Sampah</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('layanan.umkm.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-store"></i>
                    </div>
                    <span class="quick-service-title">UMKM Warga</span>
                    <span class="quick-service-desc">Pasar Tetangga</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('layanan.posyandu.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-baby"></i>
                    </div>
                    <span class="quick-service-title">Posyandu</span>
                    <span class="quick-service-desc">Tumbuh Kembang</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('aspirasi.index') }}" class="quick-service-card">
                    <div class="quick-service-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <span class="quick-service-title">Aspirasi</span>
                    <span class="quick-service-desc">Saran & Laporan</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Widget Ronda Malam Warga --}}
    @if(isset($rondaSayaTerdekat) && $rondaSayaTerdekat)
        <div class="saas-card p-3 p-md-4 mb-4 border-start border-4 border-success">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper stat-icon-green flex-shrink-0" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold text-dark mb-0 fs-6">Jadwal Tugas Ronda Malam Anda</h6>
                            <span class="badge badge-soft-success">Shift {{ ucfirst($rondaSayaTerdekat->shift) }}</span>
                        </div>
                        <div class="text-secondary small mt-1">
                            <i class="fas fa-calendar-day me-1 text-success"></i>
                            <strong>{{ \Carbon\Carbon::parse($rondaSayaTerdekat->tanggal)->isoFormat('dddd, D MMMM Y') }}</strong>
                            <span class="mx-2">&bull;</span>
                            <i class="fas fa-map-marker-alt me-1 text-success"></i>
                            {{ $rondaSayaTerdekat->lokasi_pos ?? 'Pos Ronda RT' }}
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('layanan.ronda.index') }}" class="btn btn-emerald text-white btn-sm px-3 shadow-xs">
                        <i class="fas fa-calendar-check me-1"></i> Buka Modul Ronda
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Dua Kolom: Permohonan Surat Saya & Pengumuman --}}
    <div class="row g-4">
        {{-- Permohonan Surat Saya --}}
        <div class="col-lg-6">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-envelope-open-text text-primary"></i> Permohonan Surat Terbaru
                    </h5>
                    <a href="{{ route('layanan.surat.index') }}" class="btn btn-sm btn-light">Lihat Semua</a>
                </div>
                <div class="card-body p-3">
                    @forelse($suratSaya as $surat)
                        <div class="p-3 mb-2 rounded-3 border bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="text-dark small">{{ optional($surat->jenisSurat)->nama }}</strong>
                                <span class="badge {{ $surat->status_badge }} px-2 py-1">{{ $surat->status_label }}</span>
                            </div>
                            <small class="text-secondary d-block font-monospace mb-1" style="font-size: 0.75rem;">{{ $surat->nomor_surat ?: $surat->nomor_pengajuan }}</small>
                            <small class="text-secondary d-block" style="font-size: 0.8rem;">{{ Str::limit($surat->keperluan, 80) }}</small>
                            @if($surat->nomor_surat)
                                <div class="mt-2 text-end">
                                    <a href="{{ route('layanan.surat.cetak', $surat) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-print me-1"></i> Cetak Surat PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state-saas py-3 border-0">
                            <i class="fas fa-inbox text-secondary mb-1"></i>
                            <p class="small text-secondary mb-0">Belum ada permohonan surat yang diajukan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pengumuman & Agenda Lingkungan --}}
        <div class="col-lg-6">
            <div class="saas-card h-100">
                <div class="card-header-saas">
                    <h5 class="card-title-saas">
                        <i class="fas fa-bullhorn text-warning"></i> Pengumuman & Agenda Lingkungan
                    </h5>
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-sm btn-light">Semua Agenda</a>
                </div>
                <div class="card-body p-3">
                    @forelse($pengumumanRt as $k)
                        <div class="p-3 mb-2 rounded-3 border bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge {{ $k->cakupan === 'rw' ? 'badge-soft-primary' : 'badge-soft-info' }}">
                                    {{ $k->cakupan === 'rw' ? 'Tingkat RW' : 'RT ' . optional($k->rt)->kode_rt }}
                                </span>
                                <small class="text-secondary font-monospace" style="font-size: 0.75rem;">{{ optional($k->tanggal_mulai ?? $k->tanggal)->format('d M Y') }}</small>
                            </div>
                            <strong class="text-dark d-block mb-1 small">{{ $k->nama_kegiatan ?? $k->judul }}</strong>
                            <small class="text-secondary d-block" style="font-size: 0.8rem;">{{ Str::limit($k->deskripsi, 85) }}</small>
                        </div>
                    @empty
                        <div class="empty-state-saas py-3 border-0">
                            <i class="fas fa-calendar-xmark text-secondary mb-1"></i>
                            <p class="small text-secondary mb-0">Belum ada pengumuman baru untuk lingkungan Anda.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
