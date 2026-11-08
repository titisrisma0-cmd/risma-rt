@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="container-fluid px-4">
    <!-- Welcome Header RT -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-header p-4 rounded-4 bg-dark border border-secondary shadow-sm" data-aos="fade-right">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="display-6 fw-bold mb-1 text-white">
                            Selamat Datang, <span class="text-warning">Ketua RT {{ $rtKode }}</span> 👋
                        </h1>
                        <p class="text-white-50 mb-2">
                            Pusat Komando Administrasi dan Pelayanan Warga {{ $namaRt }}
                        </p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
                                <i class="fas fa-shield-alt me-1"></i> Anda login sebagai Ketua RT {{ $rtKode }}
                            </span>
                            <span class="badge bg-primary bg-opacity-25 text-info px-3 py-2 rounded-pill">
                                <i class="fas fa-map-marker-alt me-1"></i> Wilayah: {{ $namaRt }} / RW 01
                            </span>
                        </div>
                    </div>
                    <div class="ms-3 d-none d-md-block">
                        <div class="text-end">
                            <span class="badge bg-secondary px-3 py-2">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 8 Kartu Statistik Utama RT -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Kepala Keluarga -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Kepala Keluarga</span>
                            <h3 class="fw-bold text-info mb-0">{{ number_format($totalKk) }}</h3>
                            <small class="text-secondary">KK Terdata</small>
                        </div>
                        <div class="p-3 bg-info bg-opacity-10 text-info rounded-3">
                            <i class="fas fa-id-card fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Warga -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Total Warga</span>
                            <h3 class="fw-bold text-primary mb-0">{{ number_format($totalWarga) }}</h3>
                            <small class="text-secondary">Jiwa Terdaftar</small>
                        </div>
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Laki-laki -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Warga Laki-laki</span>
                            <h3 class="fw-bold text-success mb-0">{{ number_format($totalLakiLaki) }}</h3>
                            <small class="text-secondary">Jiwa (L)</small>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                            <i class="fas fa-male fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Perempuan -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Warga Perempuan</span>
                            <h3 class="fw-bold text-danger mb-0">{{ number_format($totalPerempuan) }}</h3>
                            <small class="text-secondary">Jiwa (P)</small>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3">
                            <i class="fas fa-female fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demographic Summary Bar -->
        <div class="col-12">
            <div class="p-3 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.08);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-venus-mars text-warning"></i>
                    <span class="text-white fw-bold">Statistik Kependudukan RT {{ $rtKode }}:</span>
                </div>
                <div class="d-flex flex-wrap gap-2 text-white small">
                    <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-id-card me-1"></i> Total KK: <strong>{{ $totalKk }}</strong></span>
                    <span class="badge bg-primary px-3 py-2"><i class="fas fa-users me-1"></i> Total Jiwa: <strong>{{ $totalWarga }}</strong></span>
                    <span class="badge bg-success px-3 py-2"><i class="fas fa-male me-1"></i> Laki-laki: <strong>{{ $totalLakiLaki }}</strong></span>
                    <span class="badge bg-danger px-3 py-2"><i class="fas fa-female me-1"></i> Perempuan: <strong>{{ $totalPerempuan }}</strong></span>
                    <span class="badge bg-secondary px-3 py-2"><i class="fas fa-question-circle me-1"></i> Belum Diketahui: <strong>{{ $totalBelumDiketahui }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Quick Access Layanan Masyarakat RT -->
        <div class="col-12" id="layanan-masyarakat">
            <div class="card p-3 border-0 shadow-sm" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-white fw-bold mb-0"><i class="fas fa-handshake-angle text-emerald-400 me-2"></i>Modul Pelayanan Masyarakat RT {{ $rtKode }}</h6>
                    <span class="text-white-50 small">Akses Langsung Operasional</span>
                </div>
                <div class="row g-2">
                    <div class="col-6 col-md-3 col-xl">
                        <a href="{{ route('bank-sampah.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                            <i class="fas fa-recycle fa-2x text-success mb-2"></i>
                            <span class="text-white small fw-semibold d-block">Bank Sampah</span>
                            <small class="text-white-50" style="font-size: 0.7rem;">Setoran & Saldo</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <a href="{{ route('layanan.umkm.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                            <i class="fas fa-store fa-2x text-warning mb-2"></i>
                            <span class="text-white small fw-semibold d-block">UMKM RT</span>
                            <small class="text-white-50" style="font-size: 0.7rem;">Etalase Warga</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <a href="{{ route('rt.verifikasi.permohonan') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                            <i class="fas fa-file-signature fa-2x text-primary mb-2"></i>
                            <span class="text-white small fw-semibold d-block">Permohonan</span>
                            <small class="text-white-50" style="font-size: 0.7rem;">Verifikasi Surat</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <a href="{{ route('layanan.posyandu.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                            <i class="fas fa-baby fa-2x text-info mb-2"></i>
                            <span class="text-white small fw-semibold d-block">Posyandu</span>
                            <small class="text-white-50" style="font-size: 0.7rem;">Gizi & Imunisasi</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <a href="{{ route('layanan.ronda.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                            <i class="fas fa-shield-halved fa-2x text-danger mb-2"></i>
                            <span class="text-white small fw-semibold d-block">Ronda Malam</span>
                            <small class="text-white-50" style="font-size: 0.7rem;">Jadwal & Absensi</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <a href="{{ route('kegiatan.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                            <i class="fas fa-calendar-check fa-2x text-emerald-400 mb-2"></i>
                            <span class="text-white small fw-semibold d-block">Kegiatan RT</span>
                            <small class="text-white-50" style="font-size: 0.7rem;">Agenda Warga</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <a href="{{ route('aspirasi.index') }}" class="card p-3 text-center border-0 text-decoration-none h-100" style="background: rgba(15,23,42,0.6); border-radius: 10px; transition: all 0.2s;">
                            <i class="fas fa-comment-dots fa-2x text-warning mb-2"></i>
                            <span class="text-white small fw-semibold d-block">Aspirasi</span>
                            <small class="text-white-50" style="font-size: 0.7rem;">Pusat Keluhan</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Total Iuran Terkumpul -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Iuran Terkumpul</span>
                            <h4 class="fw-bold text-success mb-0">Rp {{ number_format($totalIuranTerkumpul, 0, ',', '.') }}</h4>
                            <small class="text-success small"><i class="fas fa-check-circle me-1"></i>Pembayaran Lunas</small>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Total Tunggakan Iuran -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Tunggakan Iuran</span>
                            <h4 class="fw-bold text-warning mb-0">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</h4>
                            <small class="text-warning small"><i class="fas fa-clock me-1"></i>Menunggu Verifikasi</small>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Saldo Kas RT -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Saldo Kas RT {{ $rtKode }}</span>
                            <h4 class="fw-bold text-info mb-0">Rp {{ number_format($saldoKasRt, 0, ',', '.') }}</h4>
                            <small class="text-secondary">Masuk: {{ number_format($kasMasukRt) }}</small>
                        </div>
                        <div class="p-3 bg-info bg-opacity-10 text-info rounded-3">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Pengumuman dari RW -->
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small d-block">Pengumuman RW</span>
                            <h3 class="fw-bold text-light mb-0">{{ $pengumumanRw->count() }}</h3>
                            <small class="text-info small"><i class="fas fa-bullhorn me-1"></i>Informasi Pusat</small>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3">
                            <i class="fas fa-broadcast-tower fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 1 & 2: Pembayaran Terbaru & Pengumuman RW -->
    <div class="row g-4 mb-4">
        <!-- Daftar Pembayaran Terbaru RT -->
        <div class="col-lg-7">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-header bg-black bg-opacity-25 border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-receipt text-success me-2"></i>Daftar Pembayaran Terbaru Warga RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-info">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr class="text-secondary small border-secondary">
                                    <th>Warga</th>
                                    <th>Iuran</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPembayaran as $pay)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-white">{{ optional($pay->user)->name ?? '-' }}</div>
                                            <small class="text-white-50">{{ optional($pay->user)->email }}</small>
                                        </td>
                                        <td>{{ optional($pay->iuran)->nama_iuran ?? 'Iuran' }}</td>
                                        <td class="text-warning fw-bold">Rp {{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</td>
                                        <td>
                                            @if($pay->status === 'lunas')
                                                <span class="badge bg-success">Lunas</span>
                                            @elseif($pay->status === 'pending')
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $pay->status }}</span>
                                            @endif
                                        </td>
                                        <td class="small text-white-50">{{ $pay->created_at ? $pay->created_at->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">Belum ada transaksi pembayaran di RT {{ $rtKode }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengumuman dari RW -->
        <div class="col-lg-5">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-header bg-black bg-opacity-25 border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-bullhorn text-warning me-2"></i>Pengumuman & Agenda RW
                    </h5>
                    <span class="badge bg-warning text-dark">Pusat RW</span>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush bg-transparent">
                        @forelse($pengumumanRw as $rwInfo)
                            <div class="list-group-item bg-transparent text-white border-secondary px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 text-info fw-bold">{{ $rwInfo->nama_kegiatan }}</h6>
                                    <span class="badge bg-secondary small">{{ $rwInfo->created_at ? $rwInfo->created_at->diffForHumans() : '' }}</span>
                                </div>
                                <p class="text-white-50 small mb-1">{{ Str::limit($rwInfo->deskripsi, 100) }}</p>
                                <div class="d-flex align-items-center gap-2 small text-secondary">
                                    <span><i class="fas fa-calendar-alt me-1"></i>{{ $rwInfo->tanggal_mulai ? \Carbon\Carbon::parse($rwInfo->tanggal_mulai)->format('d M Y') : '-' }}</span>
                                    <span><i class="fas fa-map-marker-alt me-1"></i>{{ $rwInfo->lokasi ?? 'Wilayah RW' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary text-center my-4 fst-italic">Belum ada pengumuman baru dari pengurus RW.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 3 & 4: Agenda Kegiatan RT & Informasi Warga -->
    <div class="row g-4 mb-4">
        <!-- Agenda Kegiatan RT -->
        <div class="col-lg-6">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-header bg-black bg-opacity-25 border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>Agenda Kegiatan RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-sm btn-outline-primary">+ Kelola</a>
                </div>
                <div class="card-body">
                    @forelse($agendaKegiatan as $keg)
                        <div class="p-3 rounded bg-black bg-opacity-25 border border-secondary mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-white fw-bold mb-1">{{ $keg->nama_kegiatan }}</h6>
                                    <div class="small text-white-50 mb-2">{{ Str::limit($keg->deskripsi, 90) }}</div>
                                    <div class="d-flex gap-3 small text-secondary">
                                        <span><i class="fas fa-clock text-warning me-1"></i>{{ $keg->tanggal_mulai ? \Carbon\Carbon::parse($keg->tanggal_mulai)->format('d M Y H:i') : '-' }}</span>
                                        <span><i class="fas fa-map-pin text-danger me-1"></i>{{ $keg->lokasi }}</span>
                                    </div>
                                </div>
                                <span class="badge bg-primary">RT {{ $rtKode }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-secondary py-4 fst-italic">
                            Belum ada agenda kegiatan internal RT {{ $rtKode }}.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Informasi Warga RT Terkini -->
        <div class="col-lg-6">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-header bg-black bg-opacity-25 border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-user-friends text-info me-2"></i>Warga Terbaru di Wilayah RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('warga.index') }}" class="btn btn-sm btn-outline-info">Data Warga</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr class="text-secondary small border-secondary">
                                    <th>Nama Warga</th>
                                    <th>No. KK</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wargaTerbaru as $w)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-white">{{ $w->name }}</div>
                                            <small class="text-white-50">{{ $w->email }}</small>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ optional($w->profile)->no_kk ?? '-' }}</span></td>
                                        <td>
                                            @php $st = optional($w->profile)->status ?? 'aktif'; @endphp
                                            <span class="badge {{ $st === 'aktif' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ ucfirst($st) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('warga.show', $w->id) }}" class="btn btn-sm btn-outline-light">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">Belum ada warga terdaftar di RT {{ $rtKode }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 5 & 6: Aspirasi & Pengeluaran Kas RT -->
    <div class="row g-4 mb-4">
        <!-- Aspirasi Warga RT -->
        <div class="col-lg-7">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-header bg-black bg-opacity-25 border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-comment-dots text-warning me-2"></i>Aspirasi & Pengaduan Warga RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('aspirasi.index') }}" class="btn btn-sm btn-outline-warning">Kelola Aspirasi</a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush bg-transparent">
                        @forelse($aspirasiWarga as $asp)
                            <div class="list-group-item bg-transparent text-white border-secondary px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="text-warning mb-1 fw-bold">{{ $asp->judul }}</h6>
                                    <span class="badge {{ $asp->status === 'selesai' ? 'bg-success' : ($asp->status === 'diproses' ? 'bg-info' : 'bg-secondary') }}">
                                        {{ ucfirst($asp->status) }}
                                    </span>
                                </div>
                                <p class="text-white-50 small mb-1">{{ Str::limit($asp->deskripsi, 110) }}</p>
                                <div class="small text-secondary">
                                    <i class="fas fa-clock me-1"></i>{{ $asp->created_at ? $asp->created_at->diffForHumans() : '' }}
                                    <span class="ms-2"><i class="fas fa-tag me-1"></i>{{ $asp->kategori ?? 'Umum' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary text-center my-4 fst-italic">Belum ada aspirasi dari warga RT {{ $rtKode }}.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengeluaran Kas RT -->
        <div class="col-lg-5">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-header bg-black bg-opacity-25 border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">
                        <i class="fas fa-coins text-danger me-2"></i>Pengeluaran Kas RT {{ $rtKode }}
                    </h5>
                    <a href="{{ route('pengeluaran.index') }}" class="btn btn-sm btn-outline-danger">+ Catat</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr class="text-secondary small border-secondary">
                                    <th>Keterangan</th>
                                    <th>Nominal</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPengeluaran as $out)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-white">{{ $out->keterangan }}</div>
                                            <small class="text-white-50">{{ $out->kategori }}</small>
                                        </td>
                                        <td class="text-danger fw-bold">Rp {{ number_format($out->nominal, 0, ',', '.') }}</td>
                                        <td class="small text-white-50">{{ $out->tanggal ? \Carbon\Carbon::parse($out->tanggal)->format('d/m/y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-secondary py-4">Belum ada pengeluaran kas dicatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
