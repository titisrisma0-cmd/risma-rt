@extends('layouts.app')

@section('title', 'Posyandu RW')

@php
    $nextJadwal = $jadwals
        ->filter(fn ($jadwal) => $jadwal->tanggal_mulai->greaterThanOrEqualTo(now()))
        ->sortBy('tanggal_mulai')
        ->first() ?? $jadwals->sortByDesc('tanggal_mulai')->first();

    $latestRecords = $balitas->map(function ($balita) {
        $record = $balita->pemeriksaan->sortByDesc('tanggal_periksa')->first();
        return $record ? ['balita' => $balita, 'record' => $record] : null;
    })->filter()->values();

    $statusGizi = function ($record) {
        if (! $record) return 'Belum Ada';
        if (($record->berat_kg && (float) $record->berat_kg < 9) || ($record->tinggi_cm && (float) $record->tinggi_cm < 75)) {
            return 'Perlu Pantau';
        }
        return 'Normal';
    };

    $normalCount = $latestRecords->filter(fn ($item) => $statusGizi($item['record']) === 'Normal')->count();
    $watchCount = $latestRecords->filter(fn ($item) => $statusGizi($item['record']) === 'Perlu Pantau')->count();
    $queueCount = max($balitas->count() - $latestRecords->count(), 0);

    $growthData = $balitas->mapWithKeys(function ($balita) use ($statusGizi) {
        $latest = $balita->pemeriksaan->sortByDesc('tanggal_periksa')->first();
        return [(string) $balita->id => [
            'name' => $balita->nama,
            'parent' => $balita->orangTua->name ?? '-',
            'status' => $statusGizi($latest),
            'labels' => $balita->pemeriksaan->map(fn ($item) => $item->tanggal_periksa->format('d/m/Y'))->values(),
            'weight' => $balita->pemeriksaan->map(fn ($item) => $item->berat_kg ? (float) $item->berat_kg : null)->values(),
            'height' => $balita->pemeriksaan->map(fn ($item) => $item->tinggi_cm ? (float) $item->tinggi_cm : null)->values(),
        ]];
    });

    $tips = [
        ['icon' => 'fa-child-reaching', 'title' => 'Cegah Stunting', 'text' => 'Pantau berat dan tinggi badan setiap bulan serta cukupi protein hewani harian.'],
        ['icon' => 'fa-bowl-food', 'title' => 'MPASI Bergizi', 'text' => 'Lengkapi karbohidrat, protein, sayur, buah, dan lemak sehat di setiap porsi.'],
        ['icon' => 'fa-syringe', 'title' => 'Imunisasi Rutin', 'text' => 'Selalu bawa buku KIA / KMS agar jadwal vaksinasi imunisasi tercatat lengkap.'],
    ];
@endphp

@section('content')
<div class="content-container posyandu-page">
    <header class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1">
                <i class="fas fa-baby text-emerald me-2"></i>Posyandu RW
            </h1>
            <p class="page-subtitle">Informasi pertumbuhan balita, jadwal pemeriksaan, imunisasi rutin, dan kartu KMS digital.</p>
        </div>
        <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
        </a>
    </header>

    @if($isPengurus)
        <div class="d-flex flex-wrap gap-2 mb-4" aria-label="Akses cepat Posyandu">
            <button class="btn btn-emerald text-white px-3" data-bs-toggle="modal" data-bs-target="#modalTambahAnak"><i class="fas fa-user-plus me-1"></i> Tambah Anak</button>
            <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#modalPenimbangan"><i class="fas fa-weight-scale text-emerald me-1"></i> Input Penimbangan</button>
            <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="fas fa-calendar-plus text-info me-1"></i> Input Jadwal Posyandu</button>
        </div>
    @endif

    <section class="row g-3 mb-4" aria-label="Ringkasan Posyandu">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Jumlah Anak</span>
                        <h3 class="stat-value text-dark mb-0">{{ $balitas->count() }}</h3>
                        <small class="text-secondary">Balita Terdaftar</small>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: #E0F2FE; color: #0284C7;">
                        <i class="fas fa-children"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Jadwal Terdekat</span>
                        <h3 class="stat-value text-emerald mb-0 fs-5">{{ $nextJadwal?->tanggal_mulai?->format('d M Y') ?? '-' }}</h3>
                        <small class="text-secondary">{{ $nextJadwal?->lokasi ?? 'Lokasi belum diatur' }}</small>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Perlu Ditimbang</span>
                        <h3 class="stat-value text-warning mb-0">{{ $queueCount }}</h3>
                        <small class="text-secondary">Belum Melakukan Kunjungan</small>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: #FEF3C7; color: #D97706;">
                        <i class="fas fa-list-ol"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Status Gizi</span>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <span class="badge-soft-emerald">Normal {{ $normalCount }}</span>
                            <span class="badge-soft-warning">Pantau {{ $watchCount }}</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <nav class="nav nav-tabs-saas mb-4" role="tablist">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pos-ringkasan" type="button"><i class="fas fa-chart-line me-1"></i>Ringkasan & Pertumbuhan</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pos-medis" type="button"><i class="fas fa-file-medical me-1"></i>Rekam Medis & Imunisasi</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pos-tips" type="button"><i class="fas fa-lightbulb me-1"></i>Tips & Panduan Gizi</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pos-galeri" type="button"><i class="fas fa-images me-1"></i>Layanan Kegiatan</button>
    </nav>

    <div class="tab-content">
        <section class="tab-pane fade show active" id="pos-ringkasan">
            <div class="row g-4">
                <div class="col-lg-8">
                    {{-- Grafik Pertumbuhan --}}
                    <div class="saas-card p-4 mb-4" id="grafik-pertumbuhan">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-line text-emerald me-2"></i>Grafik Pertumbuhan Anak</h6>
                            <select id="growthChildSelect" class="form-select form-control-saas" style="width: auto; min-width: 200px;">
                                @forelse($balitas as $balita)
                                    <option value="{{ $balita->id }}">{{ $balita->nama }}</option>
                                @empty
                                    <option value="">Belum ada anak</option>
                                @endforelse
                            </select>
                        </div>
                        <div id="growthEmpty" class="text-center py-5 text-secondary d-none">
                            <i class="fas fa-chart-line fa-3x text-muted opacity-50 mb-2 d-block"></i>
                            Belum ada hasil penimbangan untuk anak ini.
                        </div>
                        <div style="position: relative; height: 280px;"><canvas id="growthChart"></canvas></div>
                        <div id="growthMeta" class="small text-secondary pt-2 border-top mt-2"></div>
                    </div>

                    {{-- Tabel Data Anak --}}
                    <div class="saas-card overflow-hidden">
                        <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-children text-emerald me-2"></i>Data Anak Terdaftar</h6>
                            <input id="childSearch" type="search" class="form-control form-control-sm form-control-saas" style="width: 220px;" placeholder="Cari nama anak...">
                        </div>
                        <div class="table-responsive">
                            <table class="table-saas" id="childTable">
                                <thead>
                                    <tr>
                                        <th>Nama Balita</th>
                                        <th>Orang Tua</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Status Gizi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($balitas as $balita)
                                        @php($latest = $balita->pemeriksaan->sortByDesc('tanggal_periksa')->first())
                                        <tr>
                                            <td><strong class="text-dark">{{ $balita->nama }}</strong></td>
                                            <td class="text-secondary">{{ $balita->orangTua->name ?? '-' }}</td>
                                            <td class="text-secondary small">{{ $balita->tanggal_lahir->format('d/m/Y') }}</td>
                                            <td>
                                                @if($statusGizi($latest) === 'Normal')
                                                    <span class="badge-soft-emerald">Normal</span>
                                                @elseif($statusGizi($latest) === 'Perlu Pantau')
                                                    <span class="badge-soft-warning">Perlu Pantau</span>
                                                @else
                                                    <span class="badge-soft-secondary">Belum Ada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-5 text-secondary">Belum ada data anak.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center">
                            <button id="childPrev" class="btn btn-sm btn-outline-secondary">Sebelumnya</button>
                            <span id="childPageInfo" class="small text-secondary"></span>
                            <button id="childNext" class="btn btn-sm btn-outline-secondary">Berikutnya</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    {{-- Jadwal Posyandu --}}
                    <div class="saas-card overflow-hidden mb-4" id="jadwal-lengkap">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-calendar-days text-emerald me-2"></i>Jadwal Posyandu</h6>
                            <span class="badge-soft-emerald">{{ $jadwals->count() }} Jadwal</span>
                        </div>
                        <div class="p-3">
                            @forelse($jadwals as $jadwal)
                                <div class="p-3 mb-2 rounded-3 border bg-light d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="text-center p-2 rounded-2 bg-white border" style="min-width: 48px;">
                                            <strong class="d-block text-emerald fs-5 line-height-1">{{ $jadwal->tanggal_mulai->format('d') }}</strong>
                                            <small class="text-secondary text-uppercase" style="font-size: 0.7rem;">{{ $jadwal->tanggal_mulai->format('M') }}</small>
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block mb-1">{{ $jadwal->judul }}</strong>
                                            <span class="small text-secondary d-block"><i class="fas fa-clock me-1 text-muted"></i>{{ $jadwal->tanggal_mulai->format('H:i') }} WIB · {{ $jadwal->lokasi }}</span>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @if($jadwal->nama_bidan)
                                                    <span class="badge-soft-info"><i class="fas fa-user-doctor me-1"></i>{{ $jadwal->nama_bidan }}</span>
                                                    @if($jadwal->kontak_bidan)
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $jadwal->kontak_bidan) }}" target="_blank" class="badge bg-success text-white text-decoration-none"><i class="fab fa-whatsapp me-1"></i>WA</a>
                                                    @endif
                                                @endif
                                                <span class="badge-soft-emerald">{{ ucfirst($jadwal->status) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($isPengurus)
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditJadwalPosyandu{{ $jadwal->id }}" title="Edit Jadwal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('layanan.posyandu.jadwal.destroy', $jadwal) }}" class="d-inline" onsubmit="return confirm('Hapus jadwal posyandu ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-secondary">Belum ada jadwal posyandu aktif.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Kunjungan Terakhir --}}
                    <div class="saas-card overflow-hidden">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-notes-medical text-emerald me-2"></i>Kunjungan Terakhir</h6>
                        </div>
                        <div class="p-3">
                            @forelse($latestRecords->take(5) as $item)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <strong class="text-dark d-block">{{ $item['balita']->nama }}</strong>
                                        <small class="text-secondary">{{ $item['record']->tanggal_periksa->format('d M Y') }}</small>
                                    </div>
                                    <span class="badge-soft-emerald">{{ $item['record']->berat_kg ?? '-' }} kg · {{ $item['record']->tinggi_cm ?? '-' }} cm</span>
                                </div>
                            @empty
                                <div class="text-center py-4 text-secondary">Belum ada data kunjungan.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="tab-pane fade" id="pos-medis">
            <div class="saas-card overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-file-medical text-emerald me-2"></i> Rekam Medis Terakhir Balita</h6>
                </div>
                <div class="table-responsive">
                    <table class="table-saas">
                        <thead>
                            <tr>
                                <th>Nama Anak</th>
                                <th>Tanggal Periksa</th>
                                <th>Berat Badan</th>
                                <th>Tinggi Badan</th>
                                <th>Status Gizi</th>
                                <th>Catatan Medis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestRecords as $item)
                                <tr>
                                    <td><strong class="text-dark">{{ $item['balita']->nama }}</strong></td>
                                    <td class="text-secondary small">{{ $item['record']->tanggal_periksa->format('d/m/Y') }}</td>
                                    <td class="fw-bold text-emerald">{{ $item['record']->berat_kg ?? '-' }} kg</td>
                                    <td class="fw-bold text-info">{{ $item['record']->tinggi_cm ?? '-' }} cm</td>
                                    <td>
                                        @if($statusGizi($item['record']) === 'Normal')
                                            <span class="badge-soft-emerald">Normal</span>
                                        @else
                                            <span class="badge-soft-warning">Perlu Pantau</span>
                                        @endif
                                    </td>
                                    <td class="small text-secondary">{{ $item['record']->catatan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-secondary">Belum ada rekam medis tersimpan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="saas-card overflow-hidden">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-syringe text-emerald me-2"></i> Status Imunisasi Balita</h6>
                </div>
                <div class="p-3">
                    @forelse($latestRecords as $item)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong class="text-dark d-block">{{ $item['balita']->nama }}</strong>
                                <small class="text-secondary">Sudah: {{ implode(', ', $item['record']->imunisasi_sudah ?? []) ?: '-' }}</small>
                            </div>
                            <span class="badge-soft-warning">Belum: {{ implode(', ', $item['record']->imunisasi_belum ?? []) ?: '-' }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-secondary">Belum ada data imunisasi tersimpan.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="tab-pane fade" id="pos-tips">
            <div class="row g-3">
                @foreach($tips as $tip)
                    <div class="col-md-4">
                        <div class="saas-card p-4 h-100">
                            <div class="stat-icon-wrapper mb-3" style="background-color: var(--soft-green); color: var(--primary-green);">
                                <i class="fas {{ $tip['icon'] }}"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">{{ $tip['title'] }}</h6>
                            <p class="text-secondary small mb-0">{{ $tip['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="tab-pane fade" id="pos-galeri">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="saas-card p-4 text-center h-100">
                        <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: #E0F2FE; color: #0284C7;">
                            <i class="fas fa-weight-scale"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Penimbangan Rutin</h6>
                        <span class="text-secondary small">Pengukuran berkala berat, tinggi, dan lingkar kepala balita.</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="saas-card p-4 text-center h-100">
                        <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: var(--soft-green); color: var(--primary-green);">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Pelayanan Imunisasi</h6>
                        <span class="text-secondary small">Pemberian vaksin dasar lengkap oleh bidan dan tenaga medis puskesmas.</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="saas-card p-4 text-center h-100">
                        <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: #FEF3C7; color: #D97706;">
                            <i class="fas fa-bowl-food"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Edukasi Gizi & MPASI</h6>
                        <span class="text-secondary small">Konseling pencegahan stunting dan panduan menu sehat keluarga.</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if($isPengurus)
        {{-- Modal Tambah Anak --}}
        <div class="modal fade" id="modalTambahAnak" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom bg-white">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-user-plus text-emerald me-2"></i>Tambah Data Anak</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('layanan.posyandu.balita.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nama Anak <span class="text-danger">*</span></label>
                                <input class="form-control form-control-saas" name="nama" value="{{ old('nama') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Orang Tua / Akun Warga</label>
                                <select class="form-select form-control-saas" name="orang_tua_user_id">
                                    <option value="">Belum dihubungkan</option>
                                    @foreach($wargas as $warga)
                                        <option value="{{ $warga->id }}">{{ $warga->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">NIK Anak</label>
                                <input class="form-control form-control-saas font-monospace" name="nik" value="{{ old('nik') }}">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" type="date" name="tanggal_lahir" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-saas" name="jenis_kelamin" required>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Catatan Kesehatan</label>
                                <textarea class="form-control form-control-saas" name="catatan_kesehatan" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-emerald text-white px-4">Simpan Anak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Input Penimbangan --}}
        <div class="modal fade" id="modalPenimbangan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom bg-white">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-weight-scale text-emerald me-2"></i>Input Hasil Penimbangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('layanan.posyandu.pemeriksaan.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Nama Anak <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-saas" name="balita_id" required>
                                        <option value="">Pilih anak</option>
                                        @foreach($semuaBalita as $balita)
                                            <option value="{{ $balita->id }}">{{ $balita->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Jadwal Posyandu</label>
                                    <select class="form-select form-control-saas" name="jadwal_posyandu_id">
                                        <option value="">Tanpa jadwal</option>
                                        @foreach($jadwals as $jadwal)
                                            <option value="{{ $jadwal->id }}">{{ $jadwal->tanggal_mulai->format('d/m/Y') }} - {{ $jadwal->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Tanggal Periksa <span class="text-danger">*</span></label>
                                <input class="form-control form-control-saas" type="date" name="tanggal_periksa" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Berat Badan (kg) <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" type="number" step="0.01" min="0" name="berat_kg" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Tinggi Badan (cm) <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" type="number" step="0.01" min="0" name="tinggi_cm" required>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Lingkar Kepala (cm)</label>
                                    <input class="form-control form-control-saas" type="number" step="0.01" min="0" name="lingkar_kepala_cm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Lingkar Lengan (cm)</label>
                                    <input class="form-control form-control-saas" type="number" step="0.01" min="0" name="lingkar_lengan_cm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Status Gizi</label>
                                    <select name="status_gizi" class="form-select form-control-saas">
                                        <option value="Gizi Baik" selected>Gizi Baik</option>
                                        <option value="Gizi Kurang">Gizi Kurang</option>
                                        <option value="Gizi Lebih">Gizi Lebih</option>
                                        <option value="Berisiko Stunting">Berisiko Stunting</option>
                                        <option value="Stunting">Stunting</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Imunisasi Diberikan</label>
                                    <input class="form-control form-control-saas" name="imunisasi_sudah" placeholder="BCG, Polio, DPT">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Imunisasi Berikutnya</label>
                                    <input class="form-control form-control-saas" name="imunisasi_belum" placeholder="Campak, DPT lanjutan">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Catatan Perkembangan Anak</label>
                                <textarea class="form-control form-control-saas" name="catatan" rows="2" placeholder="Catatan keaktifan, nafsu makan, dll..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-emerald text-white px-4">Simpan Penimbangan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Input Jadwal --}}
        <div class="modal fade" id="modalJadwal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom bg-white">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-plus text-emerald me-2"></i>Input Jadwal Posyandu RT</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('layanan.posyandu.jadwal.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Judul Kegiatan <span class="text-danger">*</span></label>
                                <input class="form-control form-control-saas" name="judul" value="{{ old('judul', 'Posyandu Balita Rutin RT') }}" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" type="datetime-local" name="tanggal_mulai" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
                                    <input class="form-control form-control-saas" type="datetime-local" name="tanggal_selesai">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Lokasi Posyandu <span class="text-danger">*</span></label>
                                <input class="form-control form-control-saas" name="lokasi" placeholder="Pos Ronda RT / Balai Warga" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Nama Bidan / Tenaga Medis</label>
                                    <input class="form-control form-control-saas" name="nama_bidan" placeholder="Bidan Siti / Puskesmas">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Kontak Bidan (WA/HP)</label>
                                    <input class="form-control form-control-saas" name="kontak_bidan" placeholder="081234567890">
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Kader Penanggung Jawab</label>
                                    <input class="form-control form-control-saas" name="kader_info" placeholder="Ibu Rahma">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Status Jadwal</label>
                                    <select name="status" class="form-select form-control-saas">
                                        <option value="terjadwal" selected>Terjadwal</option>
                                        <option value="berlangsung">Berlangsung</option>
                                        <option value="selesai">Selesai</option>
                                        <option value="ditunda">Ditunda</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Keterangan / Instruksi ke Orang Tua</label>
                                <textarea class="form-control form-control-saas" name="keterangan" rows="2" placeholder="Wajib membawa buku KIA / KMS..."></textarea>
                            </div>
                            <p class="small text-muted mb-0 mt-2"><i class="fas fa-bell text-warning me-1"></i>Notifikasi pengingat otomatis akan dikirim ke seluruh orang tua balita di RT ini.</p>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-emerald text-white px-4">Simpan & Siarkan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach($jadwals as $jEdit)
            <div class="modal fade" id="modalEditJadwalPosyandu{{ $jEdit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                        <div class="modal-header border-bottom bg-white">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Jadwal Posyandu</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('layanan.posyandu.jadwal.update', $jEdit) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Judul Kegiatan <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" name="judul" value="{{ old('judul', $jEdit->judul) }}" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input class="form-control form-control-saas" type="datetime-local" name="tanggal_mulai" value="{{ $jEdit->tanggal_mulai->format('Y-m-d\TH:i') }}" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
                                        <input class="form-control form-control-saas" type="datetime-local" name="tanggal_selesai" value="{{ $jEdit->tanggal_selesai ? $jEdit->tanggal_selesai->format('Y-m-d\TH:i') : '' }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Lokasi Posyandu <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" name="lokasi" value="{{ old('lokasi', $jEdit->lokasi) }}" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Nama Bidan / Tenaga Medis</label>
                                        <input class="form-control form-control-saas" name="nama_bidan" value="{{ old('nama_bidan', $jEdit->nama_bidan) }}" placeholder="Bidan Siti / Puskesmas">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Kontak Bidan (WA/HP)</label>
                                        <input class="form-control form-control-saas" name="kontak_bidan" value="{{ old('kontak_bidan', $jEdit->kontak_bidan) }}" placeholder="081234567890">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Kader Penanggung Jawab</label>
                                        <input class="form-control form-control-saas" name="kader_info" value="{{ old('kader_info', $jEdit->kader_info) }}" placeholder="Ibu Rahma">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Status Jadwal</label>
                                        <select name="status" class="form-select form-control-saas">
                                            <option value="terjadwal" {{ $jEdit->status === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                                            <option value="berlangsung" {{ $jEdit->status === 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                                            <option value="selesai" {{ $jEdit->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="dibatalkan" {{ $jEdit->status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-secondary">Keterangan / Instruksi ke Orang Tua</label>
                                    <textarea class="form-control form-control-saas" name="keterangan" rows="2">{{ old('keterangan', $jEdit->keterangan) }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const growthData = @json($growthData);
    const select = document.getElementById('growthChildSelect');
    const canvas = document.getElementById('growthChart');
    const empty = document.getElementById('growthEmpty');
    const meta = document.getElementById('growthMeta');
    let growthChart = null;

    function chartColors() {
        return { 
            label: '#758078', 
            grid: 'rgba(233, 238, 234, 0.8)', 
            weight: '#147A45', 
            weightFill: 'rgba(20, 122, 69, 0.12)', 
            height: '#3985E8', 
            heightFill: 'rgba(57, 133, 232, 0.12)', 
            point: '#ffffff' 
        };
    }

    function showSelectedChild() {
        if (!select || !canvas || typeof Chart === 'undefined') return;
        const child = growthData[select.value];
        const hasData = child && child.labels.length;
        if (empty) empty.classList.toggle('d-none', !!hasData);
        if (canvas.parentElement) canvas.parentElement.classList.toggle('d-none', !hasData);
        if (meta) meta.textContent = child ? `${child.name} · Orang tua: ${child.parent} · Status: ${child.status}` : '';
        if (!hasData) return;
        const colors = chartColors();
        if (!growthChart) {
            growthChart = new Chart(canvas.getContext('2d'), { 
                type:'line', 
                data:{ 
                    labels:child.labels, 
                    datasets:[
                        {
                            label:'Berat Badan (kg)',
                            data:child.weight,
                            borderColor:colors.weight,
                            backgroundColor:colors.weightFill,
                            pointBackgroundColor:colors.weight,
                            pointBorderColor:colors.point,
                            tension:.35,
                            fill:true
                        },
                        {
                            label:'Tinggi Badan (cm)',
                            data:child.height,
                            borderColor:colors.height,
                            backgroundColor:colors.heightFill,
                            pointBackgroundColor:colors.height,
                            pointBorderColor:colors.point,
                            tension:.35,
                            fill:true
                        }
                    ] 
                }, 
                options:{
                    responsive:true,
                    maintainAspectRatio:false,
                    interaction:{mode:'index',intersect:false},
                    plugins:{
                        legend:{labels:{color:colors.label,usePointStyle:true}}
                    },
                    scales:{
                        x:{ticks:{color:colors.label,maxRotation:0},grid:{color:colors.grid}},
                        y:{beginAtZero:true,ticks:{color:colors.label},grid:{color:colors.grid}}
                    }
                } 
            });
        } else {
            growthChart.data.labels = child.labels;
            growthChart.data.datasets[0].data = child.weight;
            growthChart.data.datasets[1].data = child.height;
            growthChart.update();
        }
    }
    select?.addEventListener('change', showSelectedChild);
    showSelectedChild();

    const search = document.getElementById('childSearch');
    const rows = Array.from(document.querySelectorAll('#childTable tbody tr'));
    const prev = document.getElementById('childPrev'); 
    const next = document.getElementById('childNext'); 
    const info = document.getElementById('childPageInfo');
    let page = 1; 
    const perPage = 8;
    function renderRows() {
        const term = (search?.value || '').toLowerCase();
        const filtered = rows.filter(row => row.textContent.toLowerCase().includes(term));
        const pages = Math.max(1, Math.ceil(filtered.length / perPage)); 
        page = Math.min(page, pages);
        rows.forEach(row => row.style.display = 'none'); 
        filtered.slice((page-1)*perPage,page*perPage).forEach(row => row.style.display = '');
        if (info) info.textContent = `${page} / ${pages}`; 
        if (prev) prev.disabled = page <= 1; 
        if (next) next.disabled = page >= pages;
    }
    search?.addEventListener('input', () => { page=1; renderRows(); }); 
    prev?.addEventListener('click', () => { if(page>1){page--;renderRows();} }); 
    next?.addEventListener('click', () => { page++;renderRows(); }); 
    renderRows();
});
</script>
@endpush
