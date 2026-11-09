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
                                        <th>No. KK / NIK</th>
                                        <th>Orang Tua / Wali</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Status Gizi</th>
                                        @if($isPengurus)
                                            <th class="text-end">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($balitas as $balita)
                                        @php($latest = $balita->pemeriksaan->sortByDesc('tanggal_periksa')->first())
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-saas bg-light text-emerald fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                        {{ strtoupper(substr($balita->nama, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark d-block">{{ $balita->nama }}</strong>
                                                        <span class="text-secondary small">{{ $balita->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-secondary">
                                                <span class="d-block text-dark small fw-medium">{{ $balita->nik ?: '-' }}</span>
                                                <small class="text-muted">KK: {{ $balita->kartuKeluarga?->no_kk ?: '-' }}</small>
                                            </td>
                                            <td class="text-secondary">
                                                <span class="d-block text-dark small">{{ $balita->nama_wali }}</span>
                                                @if($balita->no_hp_orang_tua)
                                                    <small class="text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 0.7rem;"></i>{{ $balita->no_hp_orang_tua }}</small>
                                                @endif
                                            </td>
                                            <td class="text-secondary small">
                                                {{ $balita->tanggal_lahir->format('d/m/Y') }}
                                                <span class="text-muted d-block" style="font-size: 0.75rem;">({{ $balita->tanggal_lahir->diffInMonths(now()) }} bln)</span>
                                            </td>
                                            <td>
                                                @if($statusGizi($latest) === 'Normal')
                                                    <span class="badge-soft-emerald">Normal</span>
                                                @elseif($statusGizi($latest) === 'Perlu Pantau')
                                                    <span class="badge-soft-warning">Perlu Pantau</span>
                                                @else
                                                    <span class="badge-soft-secondary">Belum Ada</span>
                                                @endif
                                            </td>
                                            @if($isPengurus)
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditBalita{{ $balita->id }}" title="Edit Balita">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('layanan.posyandu.balita.destroy', $balita) }}" class="d-inline" data-confirm="Hapus data balita ini? Seluruh riwayat pemeriksaan juga akan terhapus." data-confirm-title="Hapus Balita" data-confirm-btn="Ya, Hapus">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ $isPengurus ? 6 : 5 }}" class="text-center py-5 text-secondary">Belum ada data anak.</td></tr>
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
                                            <form method="POST" action="{{ route('layanan.posyandu.jadwal.destroy', $jadwal) }}" class="d-inline" data-confirm="Hapus jadwal posyandu ini?" data-confirm-title="Hapus Jadwal Posyandu" data-confirm-btn="Ya, Hapus">
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
        {{-- Modal Tambah Anak Terintegrasi KK --}}
        <div class="modal fade" id="modalTambahAnak" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom bg-white">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0"><i class="fas fa-id-card-clip text-emerald me-2"></i>Tambah Data Anak dari Kartu Keluarga</h5>
                            <small class="text-secondary">Pilih Kartu Keluarga untuk menghubungkan data kependudukan anak secara otomatis.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('layanan.posyandu.balita.store') }}" id="formTambahAnakPosyandu">
                        @csrf
                        <input type="hidden" name="user_profile_id" id="posyandu_user_profile_id">
                        <input type="hidden" name="kartu_keluarga_id" id="posyandu_kartu_keluarga_id">
                        <input type="hidden" name="orang_tua_user_id" id="posyandu_orang_tua_user_id">
                        @if($isRt)
                            <input type="hidden" name="rt_id" value="{{ $myRtId }}">
                        @endif

                        <div class="modal-body p-4">
                            {{-- Step 1: Pilih Kartu Keluarga & Anggota Anak --}}
                            <div class="p-3 mb-3 rounded-3" style="background-color: #F7FAF8; border: 1px solid #E9EEEA;">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-7">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            <i class="fas fa-users text-emerald me-1"></i>1. Cari & Pilih Kartu Keluarga (KK) <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select form-control-saas" id="selectKkPosyandu" required>
                                            <option value="">-- Cari No. KK atau Kepala Keluarga --</option>
                                            @foreach($kkOptions as $kk)
                                                <option value="{{ $kk['id'] }}">
                                                    No. KK: {{ $kk['no_kk'] }} — {{ $kk['kepala_keluarga'] }} ({{ Str::limit($kk['alamat'], 22) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            <i class="fas fa-child text-emerald me-1"></i>2. Pilih Anggota Keluarga (Anak) <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select form-control-saas" id="selectAnakPosyandu" required disabled>
                                            <option value="">-- Pilih KK terlebih dahulu --</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="kkHintAlert" class="small text-muted mt-2 d-none">
                                    <i class="fas fa-info-circle text-info me-1"></i><span id="kkHintText"></span>
                                </div>
                                <div id="posyanduDuplicateAlert" class="alert alert-warning py-2 px-3 small mt-2 mb-0 d-none">
                                    <i class="fas fa-triangle-exclamation me-1"></i> Anak ini sudah terdaftar dalam data Posyandu. Silakan pilih anak yang lain.
                                </div>
                            </div>

                            {{-- Step 2: Data Otomatis & Konfirmasi --}}
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-clipboard-check text-emerald me-2"></i>Data Kependudukan Anak</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label small fw-semibold text-secondary">Nama Lengkap Anak <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas bg-light" name="nama" id="posyandu_nama" placeholder="Otomatis dari data KK" required readonly>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold text-secondary">NIK Anak</label>
                                    <input class="form-control form-control-saas bg-light font-monospace" name="nik" id="posyandu_nik" placeholder="Otomatis dari data KK" readonly>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Nomor Kartu Keluarga</label>
                                    <input class="form-control form-control-saas bg-light font-monospace" id="posyandu_no_kk" placeholder="Otomatis" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas bg-light" type="date" name="tanggal_lahir" id="posyandu_tanggal_lahir" required readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-saas bg-light" name="jenis_kelamin" id="posyandu_jenis_kelamin" required>
                                        <option value="L">Laki-laki (L)</option>
                                        <option value="P">Perempuan (P)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Nama Orang Tua / Wali <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" name="nama_orang_tua" id="posyandu_nama_orang_tua" placeholder="Nama Orang Tua / Wali" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-secondary">Nomor HP / WhatsApp Orang Tua</label>
                                    <input class="form-control form-control-saas" name="no_hp_orang_tua" id="posyandu_no_hp_orang_tua" placeholder="Contoh: 08123456789">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Catatan Kesehatan / Riwayat Khusus</label>
                                <textarea class="form-control form-control-saas" name="catatan_kesehatan" id="posyandu_catatan_kesehatan" rows="2" placeholder="Catatan alergi, riwayat penyakit, atau catatan gizi khusus..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4" id="btnSimpanAnakPosyandu" disabled>
                                <i class="fas fa-save me-1"></i>Simpan Data Anak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Edit Balita --}}
        @foreach($balitas as $bEdit)
            <div class="modal fade" id="modalEditBalita{{ $bEdit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                        <div class="modal-header border-bottom bg-white">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Data Balita</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('layanan.posyandu.balita.update', $bEdit) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Nama Anak <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-saas" name="nama" value="{{ old('nama', $bEdit->nama) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">NIK Anak</label>
                                    <input class="form-control form-control-saas font-monospace" name="nik" value="{{ old('nik', $bEdit->nik) }}">
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Tanggal Lahir <span class="text-danger">*</span></label>
                                        <input class="form-control form-control-saas" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $bEdit->tanggal_lahir?->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-saas" name="jenis_kelamin" required>
                                            <option value="L" {{ $bEdit->jenis_kelamin === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="P" {{ $bEdit->jenis_kelamin === 'P' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">Nama Orang Tua / Wali</label>
                                        <input class="form-control form-control-saas" name="nama_orang_tua" value="{{ old('nama_orang_tua', $bEdit->nama_orang_tua ?: ($bEdit->orangTua?->name ?? '')) }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">No. HP Orang Tua</label>
                                        <input class="form-control form-control-saas" name="no_hp_orang_tua" value="{{ old('no_hp_orang_tua', $bEdit->no_hp_orang_tua) }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-secondary">Catatan Kesehatan</label>
                                    <textarea class="form-control form-control-saas" name="catatan_kesehatan" rows="2">{{ old('catatan_kesehatan', $bEdit->catatan_kesehatan) }}</textarea>
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

    // KK & Child Selection in Posyandu Modal
    const kkOptionsData = @json($kkOptions ?? []);
    const selectKk = document.getElementById('selectKkPosyandu');
    const selectAnak = document.getElementById('selectAnakPosyandu');
    const dupAlert = document.getElementById('posyanduDuplicateAlert');
    const btnSimpan = document.getElementById('btnSimpanAnakPosyandu');
    const hintAlert = document.getElementById('kkHintAlert');
    const hintText = document.getElementById('kkHintText');

    const inpUserProfileId = document.getElementById('posyandu_user_profile_id');
    const inpKkId = document.getElementById('posyandu_kartu_keluarga_id');
    const inpOrangTuaUserId = document.getElementById('posyandu_orang_tua_user_id');
    const inpNama = document.getElementById('posyandu_nama');
    const inpNik = document.getElementById('posyandu_nik');
    const inpNoKk = document.getElementById('posyandu_no_kk');
    const inpTanggalLahir = document.getElementById('posyandu_tanggal_lahir');
    const inpJenisKelamin = document.getElementById('posyandu_jenis_kelamin');
    const inpNamaOrangTua = document.getElementById('posyandu_nama_orang_tua');
    const inpNoHp = document.getElementById('posyandu_no_hp_orang_tua');

    function resetChildForm() {
        if (inpUserProfileId) inpUserProfileId.value = '';
        if (inpKkId) inpKkId.value = '';
        if (inpOrangTuaUserId) inpOrangTuaUserId.value = '';
        if (inpNama) inpNama.value = '';
        if (inpNik) inpNik.value = '';
        if (inpNoKk) inpNoKk.value = '';
        if (inpTanggalLahir) inpTanggalLahir.value = '';
        if (inpNamaOrangTua) inpNamaOrangTua.value = '';
        if (inpNoHp) inpNoHp.value = '';
        if (dupAlert) dupAlert.classList.add('d-none');
        if (btnSimpan) btnSimpan.disabled = true;
    }

    selectKk?.addEventListener('change', function () {
        resetChildForm();
        const kkId = this.value;
        if (!kkId) {
            if (selectAnak) {
                selectAnak.innerHTML = '<option value="">-- Pilih KK terlebih dahulu --</option>';
                selectAnak.disabled = true;
            }
            if (hintAlert) hintAlert.classList.add('d-none');
            return;
        }

        const selectedKk = kkOptionsData.find(item => String(item.id) === String(kkId));
        if (!selectedKk) return;

        if (inpKkId) inpKkId.value = selectedKk.id;
        if (inpNoKk) inpNoKk.value = selectedKk.no_kk;

        if (hintAlert && hintText) {
            hintText.textContent = `Kepala Keluarga: ${selectedKk.kepala_keluarga} · Alamat: ${selectedKk.alamat}`;
            hintAlert.classList.remove('d-none');
        }

        if (selectAnak) {
            selectAnak.innerHTML = '<option value="">-- Pilih Anggota Anak --</option>';
            if (!selectedKk.children || selectedKk.children.length === 0) {
                selectAnak.innerHTML = '<option value="">Tidak ada data anggota keluarga dalam KK ini</option>';
                selectAnak.disabled = true;
            } else {
                selectedKk.children.forEach(child => {
                    const opt = document.createElement('option');
                    opt.value = child.id;
                    opt.textContent = `${child.nama} (${child.jenis_kelamin === 'L' ? 'L' : 'P'}) ${child.status_hubungan ? '· ' + child.status_hubungan : ''} ${child.is_registered ? '— [Sudah Terdaftar]' : ''}`;
                    if (child.is_registered) {
                        opt.classList.add('text-muted');
                    }
                    selectAnak.appendChild(opt);
                });
                selectAnak.disabled = false;
            }
        }
    });

    selectAnak?.addEventListener('change', function () {
        const memberId = this.value;
        const kkId = selectKk ? selectKk.value : null;
        if (!memberId || !kkId) {
            resetChildForm();
            return;
        }

        const selectedKk = kkOptionsData.find(item => String(item.id) === String(kkId));
        if (!selectedKk) return;

        const child = (selectedKk.children || []).find(c => String(c.id) === String(memberId));
        if (!child) return;

        if (inpUserProfileId) inpUserProfileId.value = child.id;
        if (inpKkId) inpKkId.value = selectedKk.id;
        if (inpOrangTuaUserId) inpOrangTuaUserId.value = child.orang_tua_user_id || '';
        if (inpNama) inpNama.value = child.nama || '';
        if (inpNik) inpNik.value = child.nik || '';
        if (inpNoKk) inpNoKk.value = selectedKk.no_kk || '';
        if (inpTanggalLahir) inpTanggalLahir.value = child.tanggal_lahir || '';
        if (inpJenisKelamin) inpJenisKelamin.value = child.jenis_kelamin || 'L';
        if (inpNamaOrangTua) inpNamaOrangTua.value = child.nama_orang_tua || selectedKk.kepala_keluarga || '';
        if (inpNoHp) inpNoHp.value = child.no_hp_orang_tua || '';

        if (child.is_registered) {
            if (dupAlert) dupAlert.classList.remove('d-none');
            if (btnSimpan) btnSimpan.disabled = true;
        } else {
            if (dupAlert) dupAlert.classList.add('d-none');
            if (btnSimpan) btnSimpan.disabled = false;
        }
    });
});
</script>
@endpush
