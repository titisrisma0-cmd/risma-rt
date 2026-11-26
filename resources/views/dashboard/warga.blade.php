@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Portal Warga — Dashboard')

@section('content')
<div class="py-2">
    {{-- Header Sambutan Warga --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2" style="min-width: 0;">
                <span class="badge badge-soft-primary px-3 py-1">
                    <i class="fas fa-home me-1"></i> Warga RT {{ $rtKode }} / RW 01
                </span>
                <span class="text-secondary small">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1 text-break">
                Selamat Datang, {{ $user->name }} 👋
            </h1>
            <p class="text-secondary mb-0 text-break">Portal Pelayanan Mandiri Warga Rukun Tetangga & Rukun Warga Digital.</p>
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

    {{-- 1. Kartu Ringkasan Pribadi Warga (Grid 2x2 di Mobile, 4 Kolom di Desktop) --}}
    <div class="warga-stat-grid grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full mb-4">
        {{-- Kartu Keluarga --}}
        <div class="min-w-0">
            <div class="stat-card-saas accent-emerald h-100 p-3.5 sm:p-5 space-y-1.5 min-w-0 rounded-2xl">
                <div class="d-flex justify-content-between align-items-center mb-1 min-w-0 gap-1">
                    <span class="stat-label text-[10px] font-medium uppercase tracking-wider text-slate-500 truncate">NO. KARTU KELUARGA</span>
                    <div class="stat-icon-wrapper stat-icon-green shrink-0">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="stat-value text-[13px] sm:text-base font-semibold tabular-nums tracking-tight text-slate-800 whitespace-nowrap">
                        {{ $kartuKeluarga ? $kartuKeluarga->no_kk : 'Belum Ditautkan' }}
                    </div>
                    <div class="stat-subtext text-secondary text-[11px] truncate mt-1">
                        <i class="fas fa-users text-primary me-1"></i>{{ $kartuKeluarga ? $kartuKeluarga->anggota->count() . ' Anggota Terdaftar' : 'Hubungi Ketua RT' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Iuran --}}
        <div class="min-w-0">
            <div class="stat-card-saas accent-amber h-100 p-3.5 sm:p-5 space-y-1.5 min-w-0 rounded-2xl">
                <div class="d-flex justify-content-between align-items-center mb-1 min-w-0 gap-1">
                    <span class="stat-label text-[10px] font-medium uppercase tracking-wider text-slate-500 truncate">IURAN MENUNGGU</span>
                    <div class="stat-icon-wrapper stat-icon-amber shrink-0">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="stat-value text-warning text-base sm:text-xl font-bold tracking-tight whitespace-nowrap">
                        {{ $tagihanPending }} <span class="text-xs font-medium text-slate-500">Transaksi</span>
                    </div>
                    <div class="stat-subtext text-secondary text-[11px] truncate mt-1">
                        <i class="fas fa-hourglass-half text-warning me-1"></i>Sedang diverifikasi RT
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Iuran Terbayar --}}
        <div class="min-w-0">
            <div class="stat-card-saas accent-info h-100 p-3.5 sm:p-5 space-y-1.5 min-w-0 rounded-2xl">
                <div class="d-flex justify-content-between align-items-center mb-1 min-w-0 gap-1">
                    <span class="stat-label text-[10px] font-medium uppercase tracking-wider text-slate-500 truncate">IURAN LUNAS</span>
                    <div class="stat-icon-wrapper stat-icon-blue shrink-0">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="stat-value text-success font-monospace text-base sm:text-xl font-bold tracking-tight whitespace-nowrap">
                        Rp {{ number_format($totalBayarLunas, 0, ',', '.') }}
                    </div>
                    <div class="stat-subtext text-success text-[11px] truncate mt-1">
                        <i class="fas fa-circle-check me-1"></i>Tercatat resmi lunas
                    </div>
                </div>
            </div>
        </div>

        {{-- Permohonan Surat Saya --}}
        <div class="min-w-0">
            <div class="stat-card-saas accent-primary h-100 p-3.5 sm:p-5 space-y-1.5 min-w-0 rounded-2xl">
                <div class="d-flex justify-content-between align-items-center mb-1 min-w-0 gap-1">
                    <span class="stat-label text-[10px] font-medium uppercase tracking-wider text-slate-500 truncate">PERMOHONAN SURAT</span>
                    <div class="stat-icon-wrapper stat-icon-green shrink-0">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="stat-value text-dark text-base sm:text-xl font-bold tracking-tight whitespace-nowrap">
                        {{ $suratSaya->count() }} <span class="text-xs font-medium text-slate-500">Surat</span>
                    </div>
                    <div class="stat-subtext text-secondary text-[11px] truncate mt-1">
                        <i class="fas fa-clipboard-check text-primary me-1"></i>Pengajuan aktif & riwayat
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 1B. Kartu Akses Palang Pos Gerbang Otomatis --}}
    @php
        $wargaProfile = $user->profile;
        $hasRfidCard = $wargaProfile && ! empty(trim((string) $wargaProfile->rfid_uid));
    @endphp
    <div class="saas-card p-3 p-md-4 mb-4 rounded-2xl shadow-xs border" id="widgetPalangGerbang">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-2xl d-flex align-items-center justify-content-center shrink-0"
                     style="width: 48px; height: 48px; background: {{ $hasRfidCard ? '#DCF8C6' : '#f1f5f9' }}; color: {{ $hasRfidCard ? '#00A884' : '#64748b' }}; font-size: 1.25rem;">
                    <i class="fas {{ $hasRfidCard ? 'fa-torii-gate' : 'fa-lock' }}" id="gateWidgetIcon"></i>
                </div>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h5 class="card-title-saas mb-0">Akses Palang Pos Gerbang</h5>
                        @if($hasRfidCard)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-1 small fw-semibold" id="gateStatusBadge">
                                <i class="fas fa-circle me-1" style="font-size: 0.55rem;"></i> Tertutup
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 small">
                                <i class="fas fa-id-card me-1"></i>{{ $wargaProfile->jenis_kartu ?: 'Kartu RFID' }} Terdaftar
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 small fw-semibold">
                                <i class="fas fa-lock me-1"></i> Terkunci
                            </span>
                        @endif
                    </div>

                    @if($hasRfidCard)
                        <p class="text-secondary small mb-0" id="gateStatusDesc">
                            Anda dapat membuka palang pos gerbang RW 01 secara otomatis dari aplikasi. Palang akan menutup kembali setelah 5 detik.
                        </p>
                    @else
                        <p class="text-danger small mb-0 fw-medium">
                            <i class="fas fa-circle-info me-1"></i>
                            Akses Terkunci — Daftarkan kartu RFID / E-KTP Anda ke pengurus RT/RW terlebih dahulu untuk membuka gerbang.
                        </p>
                    @endif
                </div>
            </div>

            <div class="shrink-0">
                @if($hasRfidCard)
                    <button type="button" id="btnBukaGerbang" class="btn btn-emerald fw-semibold px-3 py-2 w-100 w-md-auto shadow-sm">
                        <i class="fas fa-door-open me-1.5"></i>
                        <span id="btnBukaGerbangText">Buka Gerbang (Tutup Otomatis 5 Detik)</span>
                    </button>
                @else
                    <button type="button" class="btn btn-secondary fw-semibold px-3 py-2 w-100 w-md-auto" disabled>
                        <i class="fas fa-lock me-1.5"></i> Buka Gerbang Terkunci
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. Menu Layanan Mandiri Warga (Interactive E-Wallet Style Quick Grid) --}}
    <div class="saas-card p-3 p-md-4 mb-4 rounded-2xl shadow-xs border">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title-saas mb-0">
                    <i class="fas fa-th-large text-emerald me-1.5" style="color: #00A884;"></i> Layanan Mandiri Warga
                </h5>
                <small class="text-secondary d-none d-sm-inline">Akses cepat seluruh pelayanan masyarakat di lingkungan RT {{ $rtKode }} RW 01</small>
            </div>
            <span class="badge badge-soft-primary px-2.5 py-1 rounded-full" style="font-size: 0.72rem;">Akses Cepat</span>
        </div>

        <div class="warga-services-grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 sm:gap-4">
            {{-- 1. Surat Pengantar --}}
            <a href="{{ route('layanan.surat.index') }}" class="warga-service-card" title="Ajukan Surat Pengantar">
                <div class="warga-service-icon-box bg-emerald-soft text-emerald rounded-2xl">
                    <i class="fas fa-file-signature"></i>
                </div>
                <span class="warga-service-title text-[11px] sm:text-sm font-semibold text-center leading-tight">Surat Pengantar</span>
            </a>

            {{-- 2. Iuran Warga --}}
            <a href="{{ route('pembayaran.index') }}" class="warga-service-card" title="Tagihan & Bayar Iuran">
                <div class="warga-service-icon-box bg-blue-soft text-blue rounded-2xl">
                    <i class="fas fa-wallet"></i>
                </div>
                <span class="warga-service-title text-[11px] sm:text-sm font-semibold text-center leading-tight">Iuran Warga</span>
            </a>

            {{-- 3. Ronda Malam --}}
            <a href="{{ route('layanan.ronda.index') }}" class="warga-service-card" title="Jadwal & Presensi Ronda">
                <div class="warga-service-icon-box bg-rose-soft text-rose rounded-2xl">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <span class="warga-service-title text-[11px] sm:text-sm font-semibold text-center leading-tight">Ronda Malam</span>
            </a>

            {{-- 4. Bank Sampah --}}
            <a href="{{ route('bank-sampah.index') }}" class="warga-service-card" title="Tabungan Bank Sampah">
                <div class="warga-service-icon-box bg-green-soft text-green rounded-2xl">
                    <i class="fas fa-recycle"></i>
                </div>
                <span class="warga-service-title text-[11px] sm:text-sm font-semibold text-center leading-tight">Bank Sampah</span>
            </a>

            {{-- 5. UMKM Warga --}}
            <a href="{{ route('layanan.umkm.index') }}" class="warga-service-card" title="Pasar & UMKM Tetangga">
                <div class="warga-service-icon-box bg-amber-soft text-amber rounded-2xl">
                    <i class="fas fa-store"></i>
                </div>
                <span class="warga-service-title text-[11px] sm:text-sm font-semibold text-center leading-tight">UMKM Warga</span>
            </a>

            {{-- 6. Posyandu --}}
            <a href="{{ route('layanan.posyandu.index') }}" class="warga-service-card" title="Tumbuh Kembang & Posyandu">
                <div class="warga-service-icon-box bg-purple-soft text-purple rounded-2xl">
                    <i class="fas fa-baby"></i>
                </div>
                <span class="warga-service-title text-[11px] sm:text-sm font-semibold text-center leading-tight">Posyandu</span>
            </a>

            {{-- 7. Aspirasi --}}
            <a href="{{ route('aspirasi.index') }}" class="warga-service-card" title="Aspirasi & Laporan Warga">
                <div class="warga-service-icon-box bg-cyan-soft text-cyan rounded-2xl">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <span class="warga-service-title text-[11px] sm:text-sm font-semibold text-center leading-tight">Aspirasi</span>
            </a>
        </div>
    </div>

    {{-- Widget Ronda Malam Warga --}}
    @if(isset($rondaSayaTerdekat) && $rondaSayaTerdekat)
        @php
            $userProfId = optional($user->profile)->id;
            $myAbs = $rondaSayaTerdekat->absensi->first(function ($a) use ($user, $userProfId) {
                return ($a->user_id && $a->user_id === $user->id) || ($userProfId && $a->user_profile_id === $userProfId);
            });
            $statusAbsensi = $myAbs ? ucfirst($myAbs->status) : 'Belum Hadir';
            $jamShift = ($rondaSayaTerdekat->jam_mulai && $rondaSayaTerdekat->jam_selesai) 
                ? "{$rondaSayaTerdekat->jam_mulai} - {$rondaSayaTerdekat->jam_selesai} WIB" 
                : "22:00 - 02:00 WIB";
        @endphp
        <div class="saas-card p-3 p-md-4 mb-4 border-start border-4 border-success">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="stat-icon-wrapper stat-icon-green flex-shrink-0 mt-1" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <h6 class="fw-bold text-dark mb-0 fs-6">Jadwal Tugas Ronda Malam Anda</h6>
                            <span class="badge badge-soft-success">Shift {{ ucfirst($rondaSayaTerdekat->shift) }}</span>
                            <span class="badge bg-light text-dark border"><i class="fas fa-clock me-1 text-success"></i>{{ $jamShift }}</span>
                            @if($myAbs && $myAbs->status === 'hadir')
                                <span class="badge-soft-emerald"><i class="fas fa-check-circle me-1"></i>Hadir</span>
                            @elseif($myAbs && $myAbs->status === 'izin')
                                <span class="badge-soft-warning"><i class="fas fa-envelope-open-text me-1"></i>Izin</span>
                            @else
                                <span class="badge bg-light text-secondary border"><i class="fas fa-user-clock me-1"></i>Belum Hadir</span>
                            @endif
                        </div>
                        <div class="text-secondary small mt-1 mb-2">
                            <i class="fas fa-calendar-day me-1 text-success"></i>
                            <strong>{{ \Carbon\Carbon::parse($rondaSayaTerdekat->tanggal)->isoFormat('dddd, D MMMM Y') }}</strong>
                            <span class="mx-2">&bull;</span>
                            <i class="fas fa-map-marker-alt me-1 text-success"></i>
                            {{ $rondaSayaTerdekat->lokasi_pos ?? 'Pos Ronda RT' }}
                        </div>
                        {{-- Rekan Satu Regu --}}
                        <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                            <span class="small text-muted fw-semibold me-1"><i class="fas fa-users text-success me-1"></i>Rekan Satu Regu:</span>
                            @forelse($rondaSayaTerdekat->petugas as $petugasItem)
                                @php
                                    $namaP = $petugasItem->nama_petugas;
                                    $isMe = ($petugasItem->user_id === $user->id) || ($userProfId && $petugasItem->user_profile_id === $userProfId);
                                @endphp
                                <span class="badge {{ $isMe ? 'bg-success text-white' : 'bg-white text-dark border border-secondary border-opacity-25' }} px-2 py-1 shadow-xs fw-semibold">
                                    {{ $namaP }} {{ $isMe ? '(Anda)' : '' }}
                                </span>
                            @empty
                                <span class="text-muted small">Belum ada rekan regu yang tercatat.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                    <a href="{{ route('layanan.ronda.index') }}#modalTukar" class="btn btn-outline-warning btn-sm px-3 shadow-xs">
                        <i class="fas fa-exchange-alt me-1"></i> Ajukan Tukar Shift
                    </a>
                    <a href="{{ route('layanan.ronda.index') }}" class="btn btn-emerald text-white btn-sm px-3 shadow-xs">
                        <i class="fas fa-calendar-check me-1"></i> Detail Ronda
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Section Grafik Keuangan Iuran RT --}}
    <div class="saas-card rounded-2xl p-4 mb-4 border shadow-xs">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3 border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="stat-icon-wrapper stat-icon-green" style="width: 38px; height: 38px; font-size: 1rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="card-title-saas mb-0 fw-bold text-dark fs-6">
                        Grafik Keuangan Iuran RT {{ $rtKode }}
                    </h5>
                    <span class="badge-soft-emerald px-3 py-1 rounded-full">
                        <i class="fas fa-shield-halved me-1"></i> Transparansi Warga
                    </span>
                </div>
                <small class="text-secondary">Ringkasan penerimaan dan kepatuhan pembayaran iuran warga di lingkungan RT {{ $rtKode }} RW 01.</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge-soft-emerald px-3 py-1 rounded-full">
                    <i class="fas fa-circle-check me-1"></i> {{ $chartStatusData['lunas'] ?? 0 }} Lunas
                </span>
                <span class="badge-soft-amber px-3 py-1 rounded-full">
                    <i class="fas fa-clock me-1"></i> {{ $chartStatusData['pending'] ?? 0 }} Proses
                </span>
                <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1">
                    <i class="fas fa-wallet me-1 text-primary"></i> Iuran Saya
                </a>
            </div>
        </div>

        <div class="row g-4 align-items-center">
            {{-- Grafik Batang / Garis Tren Penerimaan --}}
            <div class="col-lg-8">
                <div class="p-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-dark small fw-bold">Tren Penerimaan Iuran (6 Bulan Terakhir)</span>
                        <span class="text-secondary small font-monospace"><i class="fas fa-calendar-alt me-1"></i>Data Riil RT {{ $rtKode }}</span>
                    </div>
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chartIuranRt"></canvas>
                    </div>
                </div>
            </div>

            {{-- 3 Kartu Mini Ringkasan Soft Pastel --}}
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-2.5">
                    {{-- Total Penerimaan Lunas --}}
                    <div class="p-3 rounded-xl border" style="background-color: #ecfdf5; border-color: rgba(167, 243, 208, 0.8) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="color: #047857;">Total Iuran Terkumpul</span>
                            <i class="fas fa-vault" style="color: #047857;"></i>
                        </div>
                        <div class="h5 fw-bold font-monospace mb-0" style="color: #065f46;">
                            Rp {{ number_format($totalIuranRt ?? 0, 0, ',', '.') }}
                        </div>
                        <small style="color: #047857; font-size: 0.72rem;">Akumulasi iuran berstatus resmi lunas di RT {{ $rtKode }}</small>
                    </div>

                    {{-- Total Transaksi Tercatat --}}
                    <div class="p-3 rounded-xl border" style="background-color: #f0f9ff; border-color: rgba(186, 230, 253, 0.8) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="color: #0369a1;">Total Transaksi RT</span>
                            <i class="fas fa-receipt" style="color: #0369a1;"></i>
                        </div>
                        <div class="h5 fw-bold font-monospace mb-0" style="color: #075985;">
                            {{ number_format($totalTransaksiRt ?? 0) }} <span class="fs-6 fw-normal" style="color: #0284c7;">Transaksi</span>
                        </div>
                        <small style="color: #0369a1; font-size: 0.72rem;">{{ $chartStatusData['lunas'] ?? 0 }} transaksi berhasil diverifikasi</small>
                    </div>

                    {{-- Menunggu Verifikasi --}}
                    <div class="p-3 rounded-xl border" style="background-color: #fffbeb; border-color: rgba(253, 230, 138, 0.8) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="color: #92400e;">Dalam Antrean Verifikasi</span>
                            <i class="fas fa-hourglass-half" style="color: #92400e;"></i>
                        </div>
                        <div class="h5 fw-bold font-monospace mb-0" style="color: #78350f;">
                            {{ number_format($chartStatusData['pending'] ?? 0) }} <span class="fs-6 fw-normal" style="color: #b45309;">Menunggu</span>
                        </div>
                        <small style="color: #92400e; font-size: 0.72rem;">Dalam proses verifikasi pengurus RT/RW</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('chartIuranRt');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = {!! json_encode($monthlyTrendLabels ?? ['Bulan 1', 'Bulan 2', 'Bulan 3', 'Bulan 4', 'Bulan 5', 'Bulan 6']) !!};
    const values = {!! json_encode($monthlyTrendData ?? [0, 0, 0, 0, 0, 0]) !!};

    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pemasukan Iuran (Rp)',
                data: values,
                backgroundColor: 'rgba(16, 185, 129, 0.25)',
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 42,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#0f172a',
                    bodyColor: '#0f172a',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4,
                    callbacks: {
                        label: function (context) {
                            const val = context.parsed.y || 0;
                            return ' Pemasukan: Rp ' + val.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, family: 'Plus Jakarta Sans, sans-serif' },
                        callback: function (value) {
                            if (value >= 1000000) return (value / 1000000) + ' Jt';
                            if (value >= 1000) return (value / 1000) + ' Rb';
                            return value;
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, family: 'Plus Jakarta Sans, sans-serif' }
                    }
                }
            }
        }
    });
});
</script>
@if($user->profile && ! empty(trim((string) $user->profile->rfid_uid)))
<script>
(function () {
    const btn = document.getElementById('btnBukaGerbang');
    const btnText = document.getElementById('btnBukaGerbangText');
    const badge = document.getElementById('gateStatusBadge');
    const desc = document.getElementById('gateStatusDesc');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!btn) return;

    let cooldownTimer = null;
    let gateTimer = null;

    function startCooldown(seconds) {
        if (cooldownTimer) clearInterval(cooldownTimer);
        let remaining = seconds;
        btn.disabled = true;

        const updateBtn = () => {
            if (remaining <= 0) {
                clearInterval(cooldownTimer);
                cooldownTimer = null;
                btn.disabled = false;
                btnText.textContent = 'Buka Gerbang (Tutup Otomatis 5 Detik)';
            } else {
                btnText.textContent = `Tunggu ${remaining} detik...`;
                remaining--;
            }
        };
        updateBtn();
        cooldownTimer = setInterval(updateBtn, 1000);
    }

    function showGateOpenCountdown(autoCloseSec) {
        if (gateTimer) clearInterval(gateTimer);
        let sec = autoCloseSec || 5;

        const renderOpen = () => {
            if (sec > 1) {
                badge.className = 'badge bg-success text-white rounded-pill px-2.5 py-1 small fw-semibold';
                badge.innerHTML = `<i class="fas fa-lock-open me-1"></i> Terbuka (${sec}d)`;
                desc.textContent = `Palang pos gerbang terbuka. Menutup otomatis dalam ${sec} detik...`;
                sec--;
            } else if (sec === 1) {
                badge.className = 'badge bg-warning text-dark rounded-pill px-2.5 py-1 small fw-semibold';
                badge.innerHTML = '<i class="fas fa-clock me-1"></i> Menutup Otomatis...';
                desc.textContent = 'Palang pos sedang menutup kembali secara otomatis...';
                sec--;
            } else {
                clearInterval(gateTimer);
                gateTimer = null;
                badge.className = 'badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-1 small fw-semibold';
                badge.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 0.55rem;"></i> Tertutup';
                desc.textContent = 'Anda dapat membuka palang pos gerbang RW 01 secara otomatis dari aplikasi. Palang akan menutup kembali setelah 5 detik.';
            }
        };

        renderOpen();
        gateTimer = setInterval(renderOpen, 1000);
    }

    btn.addEventListener('click', async function () {
        btn.disabled = true;
        btnText.textContent = 'Mengirim perintah...';

        try {
            const res = await fetch('{{ route("warga.gerbang.buka") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                showGateOpenCountdown(data.auto_close_seconds || 5);
                startCooldown(data.cooldown_seconds || 15);
            } else if (res.status === 429 && data.remaining_seconds) {
                desc.textContent = data.message;
                startCooldown(data.remaining_seconds);
            } else {
                alert(data.message || 'Gagal membuka gerbang.');
                btn.disabled = false;
                btnText.textContent = 'Buka Gerbang (Tutup Otomatis 5 Detik)';
            }
        } catch (err) {
            console.error('Error membuka gerbang:', err);
            alert('Terjadi gangguan koneksi saat menghubungi server.');
            btn.disabled = false;
            btnText.textContent = 'Buka Gerbang (Tutup Otomatis 5 Detik)';
        }
    });
})();
</script>
@endif
@endpush
