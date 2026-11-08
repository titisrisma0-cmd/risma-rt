@extends('layouts.app')

@section('title', 'Portal Warga — Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header Sambutan Warga --}}
    <div class="card p-4 mb-4 border-0" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(30, 41, 59, 0.8)); backdrop-filter: blur(10px); border: 1px solid rgba(16, 185, 129, 0.3) !important; border-radius: 16px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1 mb-2">
                    <i class="fas fa-home me-1"></i> Warga RT {{ $rtKode }} / RW 01
                </span>
                <h1 class="h2 text-white fw-bold mb-1">
                    Selamat Datang, <span class="text-emerald-400">{{ $user->name }}</span> 👋
                </h1>
                <p class="text-white-50 mb-0">Portal Pelayanan Mandiri Warga Rukun Warga Smart</p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('layanan.surat.index') }}" class="btn btn-emerald text-white px-3 py-2" style="background: linear-gradient(135deg, #059669, #10b981); border: none; border-radius: 8px;">
                    <i class="fas fa-file-signature me-1"></i> Ajukan Surat
                </a>
                <a href="{{ route('pembayaran.create') }}" class="btn btn-outline-light px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-credit-card me-1"></i> Bayar Iuran
                </a>
            </div>
        </div>
    </div>

    {{-- Kartu Ringkasan Pribadi Warga --}}
    <div class="row g-3 mb-4">
        {{-- Kartu Keluarga --}}
        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 small">Kartu Keluarga</span>
                    <i class="fas fa-id-card text-emerald-400 fa-lg"></i>
                </div>
                <div class="h5 text-white fw-bold font-monospace mb-1">
                    {{ $kartuKeluarga ? $kartuKeluarga->no_kk : 'Belum Ditautkan' }}
                </div>
                <small class="text-white-50">
                    {{ $kartuKeluarga ? $kartuKeluarga->anggota->count() . ' Anggota Terdaftar' : 'Hubungi Ketua RT' }}
                </small>
            </div>
        </div>

        {{-- Status Iuran --}}
        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 small">Iuran Menunggu Verifikasi</span>
                    <i class="fas fa-clock text-amber-400 fa-lg"></i>
                </div>
                <div class="h4 text-amber-400 fw-bold mb-1">{{ $tagihanPending }} Transaksi</div>
                <small class="text-white-50">Sedang diperiksa pengurus RT</small>
            </div>
        </div>

        {{-- Total Iuran Terbayar --}}
        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 small">Total Iuran Telah Lunas</span>
                    <i class="fas fa-receipt text-blue-400 fa-lg"></i>
                </div>
                <div class="h4 text-white fw-bold font-monospace mb-1">
                    Rp {{ number_format($totalBayarLunas, 0, ',', '.') }}
                </div>
                <small class="text-emerald-400"><i class="fas fa-check-circle me-1"></i>Tercatat resmi</small>
            </div>
        </div>

        {{-- Permohonan Surat Saya --}}
        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 small">Permohonan Surat Saya</span>
                    <i class="fas fa-envelope-open-text text-purple-400 fa-lg"></i>
                </div>
                <div class="h4 text-white fw-bold mb-1">{{ $suratSaya->count() }} Surat</div>
                <small class="text-white-50">Aktif & Riwayat</small>
            </div>
        </div>
    </div>

    {{-- Menu Layanan Cepat Warga --}}
    <div class="card p-4 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <h5 class="text-white mb-3"><i class="fas fa-th-large text-emerald-400 me-2"></i>Layanan Mandiri Warga</h5>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('layanan.surat.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                    <i class="fas fa-file-signature fa-2x text-blue-400 mb-2"></i>
                    <span class="text-white small fw-semibold d-block">Surat Pengantar</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('pembayaran.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                    <i class="fas fa-credit-card fa-2x text-emerald-400 mb-2"></i>
                    <span class="text-white small fw-semibold d-block">Iuran Warga</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('bank-sampah.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                    <i class="fas fa-recycle fa-2x text-green-400 mb-2"></i>
                    <span class="text-white small fw-semibold d-block">Bank Sampah</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('layanan.umkm.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                    <i class="fas fa-store fa-2x text-orange-400 mb-2"></i>
                    <span class="text-white small fw-semibold d-block">UMKM Warga</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('layanan.posyandu.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                    <i class="fas fa-baby fa-2x text-pink-400 mb-2"></i>
                    <span class="text-white small fw-semibold d-block">Posyandu</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('aspirasi.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                    <i class="fas fa-bullhorn fa-2x text-yellow-400 mb-2"></i>
                    <span class="text-white small fw-semibold d-block">Aspirasi</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Dua Kolom: Permohonan Surat Saya & Pengumuman --}}
    <div class="row g-4">
        {{-- Permohonan Surat Saya --}}
        <div class="col-lg-6">
            <div class="card border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-envelope-open-text text-emerald-400 me-2"></i>Permohonan Surat Terbaru</h5>
                    <a href="{{ route('layanan.surat.index') }}" class="small text-emerald-400 text-decoration-none">Lihat Semua</a>
                </div>
                <div class="p-3">
                    @forelse($suratSaya as $surat)
                        <div class="p-3 mb-2 rounded" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="text-white">{{ optional($surat->jenisSurat)->nama }}</strong>
                                <span class="badge {{ $surat->status_badge }} px-2 py-1">{{ $surat->status_label }}</span>
                            </div>
                            <small class="text-white-50 d-block font-monospace mb-1">{{ $surat->nomor_surat ?: $surat->nomor_pengajuan }}</small>
                            <small class="text-white-50 d-block">{{ Str::limit($surat->keperluan, 80) }}</small>
                            @if($surat->nomor_surat)
                                <div class="mt-2 text-end">
                                    <a href="{{ route('layanan.surat.cetak', $surat) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-print me-1"></i> Cetak Surat PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-white-50">
                            Belum ada permohonan surat yang diajukan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pengumuman & Agenda Lingkungan --}}
        <div class="col-lg-6">
            <div class="card border-0 h-100" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
                    <h5 class="text-white mb-0"><i class="fas fa-bullhorn text-amber-400 me-2"></i>Pengumuman & Agenda Lingkungan</h5>
                    <a href="{{ route('kegiatan.index') }}" class="small text-amber-400 text-decoration-none">Semua Agenda</a>
                </div>
                <div class="p-3">
                    @forelse($pengumumanRt as $k)
                        <div class="p-3 mb-2 rounded" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge {{ $k->cakupan === 'rw' ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300' }}">
                                    {{ $k->cakupan === 'rw' ? 'Tingkat RW' : 'RT ' . optional($k->rt)->kode_rt }}
                                </span>
                                <small class="text-white-50 font-monospace">{{ optional($k->tanggal_mulai ?? $k->tanggal)->format('d M Y') }}</small>
                            </div>
                            <strong class="text-white d-block mb-1">{{ $k->nama_kegiatan ?? $k->judul }}</strong>
                            <small class="text-white-50 d-block">{{ Str::limit($k->deskripsi, 85) }}</small>
                        </div>
                    @empty
                        <div class="text-center py-4 text-white-50">
                            Belum ada pengumuman baru untuk lingkungan Anda.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
