@extends('layouts.app')

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
        ['icon' => 'fa-child-reaching', 'title' => 'Cegah Stunting', 'text' => 'Pantau berat dan tinggi badan setiap bulan serta cukupi protein harian.'],
        ['icon' => 'fa-bowl-food', 'title' => 'MPASI Bergizi', 'text' => 'Lengkapi karbohidrat, protein, sayur, buah, dan lemak sehat.'],
        ['icon' => 'fa-syringe', 'title' => 'Imunisasi', 'text' => 'Bawa buku KIA agar riwayat dan jadwal imunisasi tercatat lengkap.'],
    ];
@endphp

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3 posyandu-page">
    <header class="pos-head">
        <div>
            <h2><i class="fas fa-baby me-2"></i>Posyandu RW</h2>
            <p>Informasi pertumbuhan, jadwal, imunisasi, dan kunjungan anak.</p>
        </div>
        <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </header>

    @if($isPengurus)
        <div class="pos-quick-actions" aria-label="Akses cepat Posyandu">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahAnak"><i class="fas fa-user-plus"></i> Tambah Anak</button>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalPenimbangan"><i class="fas fa-weight-scale"></i> Input Penimbangan</button>
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="fas fa-calendar-plus"></i> Input Jadwal</button>
        </div>
    @endif

    <section class="pos-summary-grid" aria-label="Ringkasan Posyandu">
        <article class="pos-summary-card">
            <i class="fas fa-children pos-icon blue"></i>
            <div><strong>{{ $balitas->count() }}</strong><span>Jumlah Anak</span></div>
        </article>
        <article class="pos-summary-card">
            <i class="fas fa-calendar-check pos-icon purple"></i>
            <div><strong>{{ $nextJadwal?->tanggal_mulai?->format('d M') ?? '-' }}</strong><span>Jadwal Terdekat</span></div>
        </article>
        <article class="pos-summary-card">
            <i class="fas fa-list-ol pos-icon amber"></i>
            <div><strong>{{ $queueCount }}</strong><span>Perlu Kunjungan</span></div>
        </article>
        <article class="pos-summary-card nutrition-card">
            <i class="fas fa-heart-pulse pos-icon green"></i>
            <div>
                <span>Status Gizi</span>
                <div class="pos-status-row"><b class="ok">Normal {{ $normalCount }}</b><b class="watch">Pantau {{ $watchCount }}</b><b class="danger">Buruk 0</b></div>
            </div>
        </article>
    </section>

    <nav class="pos-tabs nav nav-tabs" role="tablist">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pos-ringkasan" type="button">Ringkasan</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pos-medis" type="button">Rekam Medis & Imunisasi</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pos-tips" type="button">Tips & Panduan</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pos-galeri" type="button">Galeri</button>
    </nav>

    <div class="tab-content pos-tab-content">
        <section class="tab-pane fade show active" id="pos-ringkasan">
            <div class="pos-main-grid">
                <div class="pos-column">
                    <article class="pos-card" id="grafik-pertumbuhan">
                        <div class="pos-card-head">
                            <h5><i class="fas fa-chart-line"></i> Grafik Pertumbuhan</h5>
                            <select id="growthChildSelect" class="form-select form-select-sm">
                                @forelse($balitas as $balita)
                                    <option value="{{ $balita->id }}">{{ $balita->nama }}</option>
                                @empty
                                    <option value="">Belum ada anak</option>
                                @endforelse
                            </select>
                        </div>
                        <div id="growthEmpty" class="pos-empty d-none">Belum ada hasil penimbangan untuk anak ini.</div>
                        <div class="pos-chart-wrap"><canvas id="growthChart"></canvas></div>
                        <div id="growthMeta" class="pos-chart-meta"></div>
                    </article>

                    <article class="pos-card">
                        <div class="pos-card-head">
                            <h5><i class="fas fa-children"></i> Data Anak Terdaftar</h5>
                            <input id="childSearch" type="search" class="form-control form-control-sm" placeholder="Cari anak...">
                        </div>
                        <div class="table-responsive pos-table-scroll">
                            <table class="table table-sm align-middle mb-0" id="childTable">
                                <thead><tr><th>Nama</th><th>Orang Tua</th><th>Lahir</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($balitas as $balita)
                                        @php($latest = $balita->pemeriksaan->sortByDesc('tanggal_periksa')->first())
                                        <tr>
                                            <td><strong>{{ $balita->nama }}</strong></td>
                                            <td>{{ $balita->orangTua->name ?? '-' }}</td>
                                            <td>{{ $balita->tanggal_lahir->format('d/m/Y') }}</td>
                                            <td><span class="pos-badge {{ $statusGizi($latest) === 'Normal' ? 'ok' : 'watch' }}">{{ $statusGizi($latest) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">Belum ada data anak.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pos-pagination"><button id="childPrev" class="btn btn-sm btn-outline-secondary">Sebelumnya</button><span id="childPageInfo"></span><button id="childNext" class="btn btn-sm btn-outline-secondary">Berikutnya</button></div>
                    </article>
                </div>

                <aside class="pos-column">
                    <article class="pos-card" id="jadwal-lengkap">
                        <div class="pos-card-head"><h5><i class="fas fa-calendar-days"></i> Jadwal Posyandu</h5><span class="pos-count">{{ $jadwals->count() }}</span></div>
                        <div class="pos-compact-list schedule-list">
                            @forelse($jadwals as $jadwal)
                                <div class="pos-list-item">
                                    <time><strong>{{ $jadwal->tanggal_mulai->format('d') }}</strong><span>{{ $jadwal->tanggal_mulai->format('M') }}</span></time>
                                    <div><strong>{{ $jadwal->judul }}</strong><span>{{ $jadwal->tanggal_mulai->format('H:i') }} · {{ $jadwal->lokasi }}</span></div>
                                </div>
                            @empty
                                <div class="pos-empty">Belum ada jadwal.</div>
                            @endforelse
                        </div>
                    </article>

                    <article class="pos-card">
                        <div class="pos-card-head"><h5><i class="fas fa-clipboard-list"></i> Antrian Posyandu</h5></div>
                        <div class="pos-queue-grid">
                            <div><strong>{{ $balitas->count() }}</strong><span>Terdata</span></div>
                            <div><strong>{{ $latestRecords->count() }}</strong><span>Ditimbang</span></div>
                            <div><strong>{{ $queueCount }}</strong><span>Menunggu</span></div>
                        </div>
                        <small class="pos-help">Warga datang sesuai jadwal dan melakukan konfirmasi di meja pendaftaran.</small>
                    </article>

                    <article class="pos-card">
                        <div class="pos-card-head"><h5><i class="fas fa-notes-medical"></i> Kunjungan Terakhir</h5></div>
                        <div class="pos-compact-list visit-list">
                            @forelse($latestRecords->take(5) as $item)
                                <div class="pos-visit">
                                    <div><strong>{{ $item['balita']->nama }}</strong><span>{{ $item['record']->tanggal_periksa->format('d M Y') }}</span></div>
                                    <span>{{ $item['record']->berat_kg ?? '-' }} kg · {{ $item['record']->tinggi_cm ?? '-' }} cm</span>
                                </div>
                            @empty
                                <div class="pos-empty">Belum ada kunjungan.</div>
                            @endforelse
                        </div>
                    </article>
                </aside>
            </div>
        </section>

        <section class="tab-pane fade" id="pos-medis">
            <div class="pos-card mb-3">
                <div class="pos-card-head"><h5><i class="fas fa-file-medical"></i> Rekam Medis Terakhir</h5></div>
                <div class="table-responsive pos-large-table-scroll">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Anak</th><th>Tanggal</th><th>Berat</th><th>Tinggi</th><th>Status Gizi</th><th>Catatan</th></tr></thead>
                        <tbody>
                            @forelse($latestRecords as $item)
                                <tr><td>{{ $item['balita']->nama }}</td><td>{{ $item['record']->tanggal_periksa->format('d/m/Y') }}</td><td>{{ $item['record']->berat_kg ?? '-' }} kg</td><td>{{ $item['record']->tinggi_cm ?? '-' }} cm</td><td><span class="pos-badge {{ $statusGizi($item['record']) === 'Normal' ? 'ok' : 'watch' }}">{{ $statusGizi($item['record']) }}</span></td><td>{{ $item['record']->catatan ?: '-' }}</td></tr>
                            @empty
                                <tr><td colspan="6" class="text-center">Belum ada rekam medis.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="pos-card">
                <div class="pos-card-head"><h5><i class="fas fa-syringe"></i> Status Imunisasi</h5></div>
                <div class="pos-compact-list immun-list">
                    @forelse($latestRecords as $item)
                        <div class="pos-visit"><div><strong>{{ $item['balita']->nama }}</strong><span>Sudah: {{ implode(', ', $item['record']->imunisasi_sudah ?? []) ?: '-' }}</span></div><span>Belum: {{ implode(', ', $item['record']->imunisasi_belum ?? []) ?: '-' }}</span></div>
                    @empty
                        <div class="pos-empty">Belum ada data imunisasi.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="tab-pane fade" id="pos-tips">
            <div class="pos-info-grid">
                @foreach($tips as $tip)
                    <article class="pos-card pos-tip"><i class="fas {{ $tip['icon'] }}"></i><div><h5>{{ $tip['title'] }}</h5><p>{{ $tip['text'] }}</p></div></article>
                @endforeach
            </div>
        </section>

        <section class="tab-pane fade" id="pos-galeri">
            <div class="pos-info-grid">
                <article class="pos-card pos-gallery"><i class="fas fa-weight-scale"></i><strong>Penimbangan Balita</strong><span>Pengukuran berat dan tinggi badan.</span></article>
                <article class="pos-card pos-gallery"><i class="fas fa-syringe"></i><strong>Imunisasi</strong><span>Pelayanan imunisasi rutin.</span></article>
                <article class="pos-card pos-gallery"><i class="fas fa-bowl-food"></i><strong>Edukasi Gizi</strong><span>Panduan MPASI dan pencegahan stunting.</span></article>
            </div>
        </section>
    </div>

    @if($isPengurus)
        <div class="modal fade" id="modalTambahAnak" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Data Anak</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('layanan.posyandu.balita.store') }}">@csrf<div class="modal-body">
            <label class="form-label">Nama Anak</label><input class="form-control mb-2" name="nama" value="{{ old('nama') }}" required>
            <label class="form-label">Orang Tua / Akun Warga</label><select class="form-select mb-2" name="orang_tua_user_id"><option value="">Belum dihubungkan</option>@foreach($wargas as $warga)<option value="{{ $warga->id }}">{{ $warga->name }}</option>@endforeach</select>
            <label class="form-label">NIK Anak</label><input class="form-control mb-2" name="nik" value="{{ old('nik') }}">
            <div class="row g-2"><div class="col-sm-6"><label class="form-label">Tanggal Lahir</label><input class="form-control" type="date" name="tanggal_lahir" required></div><div class="col-sm-6"><label class="form-label">Jenis Kelamin</label><select class="form-select" name="jenis_kelamin" required><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div></div>
            <label class="form-label mt-2">Catatan Kesehatan</label><textarea class="form-control" name="catatan_kesehatan" rows="2"></textarea>
        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Anak</button></div></form></div></div></div>

        <div class="modal fade" id="modalPenimbangan" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Input Hasil Penimbangan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('layanan.posyandu.pemeriksaan.store') }}">@csrf<div class="modal-body">
            <div class="row g-2"><div class="col-md-6"><label class="form-label">Nama Anak</label><select class="form-select" name="balita_id" required><option value="">Pilih anak</option>@foreach($semuaBalita as $balita)<option value="{{ $balita->id }}">{{ $balita->nama }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">Jadwal</label><select class="form-select" name="jadwal_posyandu_id"><option value="">Tanpa jadwal</option>@foreach($jadwals as $jadwal)<option value="{{ $jadwal->id }}">{{ $jadwal->tanggal_mulai->format('d/m/Y') }} - {{ $jadwal->judul }}</option>@endforeach</select></div></div>
            <label class="form-label mt-2">Tanggal Periksa</label><input class="form-control mb-2" type="date" name="tanggal_periksa" value="{{ date('Y-m-d') }}" required>
            <div class="row g-2"><div class="col-md-6"><label class="form-label">Berat Badan (kg)</label><input class="form-control" type="number" step="0.01" min="0" name="berat_kg"></div><div class="col-md-6"><label class="form-label">Tinggi Badan (cm)</label><input class="form-control" type="number" step="0.01" min="0" name="tinggi_cm"></div></div>
            <div class="row g-2 mt-1"><div class="col-md-6"><label class="form-label">Imunisasi Sudah</label><input class="form-control" name="imunisasi_sudah" placeholder="BCG, Polio, DPT"></div><div class="col-md-6"><label class="form-label">Imunisasi Belum</label><input class="form-control" name="imunisasi_belum" placeholder="Campak, DPT lanjutan"></div></div>
            <label class="form-label mt-2">Catatan</label><textarea class="form-control" name="catatan" rows="2"></textarea>
        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Simpan Penimbangan</button></div></form></div></div></div>

        <div class="modal fade" id="modalJadwal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Input Jadwal Posyandu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('layanan.posyandu.jadwal.store') }}">@csrf<div class="modal-body">
            <label class="form-label">Judul Kegiatan</label><input class="form-control mb-2" name="judul" value="{{ old('judul', 'Posyandu Balita Bulanan') }}" required>
            <div class="row g-2"><div class="col-sm-6"><label class="form-label">Tanggal Mulai</label><input class="form-control" type="datetime-local" name="tanggal_mulai" required></div><div class="col-sm-6"><label class="form-label">Tanggal Selesai</label><input class="form-control" type="datetime-local" name="tanggal_selesai"></div></div>
            <label class="form-label mt-2">Lokasi</label><input class="form-control mb-2" name="lokasi" placeholder="Balai RW" required>
            <label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan" rows="2"></textarea>
        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-info">Simpan Jadwal</button></div></form></div></div></div>
    @endif
</div>

<style>
    .posyandu-page { --pos-surface: var(--surface, #10271e); --pos-raised: var(--surface-raised, #18382b); --pos-text: var(--text-primary, #f8fafc); --pos-muted: var(--text-muted, #b8c7be); --pos-border: var(--border-color, #526b5c); color: var(--pos-text); }
    .posyandu-page .pos-head { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:12px; }
    .posyandu-page .pos-head h2 { margin:0; font-size:1.45rem; color:var(--pos-text); }
    .posyandu-page .pos-head p { margin:3px 0 0; color:var(--pos-muted); font-size:.88rem; }
    .posyandu-page .pos-quick-actions { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
    .posyandu-page .pos-summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; margin-bottom:14px; }
    .posyandu-page .pos-summary-card { min-height:78px; display:flex; align-items:center; gap:11px; padding:12px 14px; border:1px solid var(--pos-border); border-radius:14px; background:var(--pos-surface); }
    .posyandu-page .pos-summary-card > div { min-width:0; }
    .posyandu-page .pos-summary-card strong { display:block; font-size:1.35rem; line-height:1.1; color:var(--pos-text); }
    .posyandu-page .pos-summary-card span { color:var(--pos-muted); font-size:.76rem; }
    .posyandu-page .pos-icon { width:36px; height:36px; display:grid; place-items:center; flex:0 0 36px; border-radius:10px; }
    .posyandu-page .pos-icon.blue { color:#60a5fa; background:rgba(59,130,246,.15); }.posyandu-page .pos-icon.purple { color:#c084fc; background:rgba(168,85,247,.15); }.posyandu-page .pos-icon.amber { color:#fbbf24; background:rgba(245,158,11,.15); }.posyandu-page .pos-icon.green { color:#4ade80; background:rgba(34,197,94,.15); }
    .posyandu-page .nutrition-card { grid-column:auto; }.posyandu-page .pos-status-row { display:flex; flex-wrap:wrap; gap:4px; margin-top:5px; }.posyandu-page .pos-status-row b,.posyandu-page .pos-badge { padding:3px 7px; border-radius:999px; font-size:.68rem; white-space:nowrap; }.posyandu-page .ok { color:#166534!important; background:#dcfce7!important; }.posyandu-page .watch { color:#854d0e!important; background:#fef3c7!important; }.posyandu-page .danger { color:#991b1b!important; background:#fee2e2!important; }
    .posyandu-page .pos-tabs { gap:3px; border-color:var(--pos-border); overflow-x:auto; flex-wrap:nowrap; }.posyandu-page .pos-tabs .nav-link { white-space:nowrap; font-size:.82rem; padding:8px 12px; color:var(--pos-muted)!important; }.posyandu-page .pos-tabs .nav-link.active { color:var(--pos-text)!important; background:var(--pos-raised)!important; border-color:var(--pos-border)!important; }
    .posyandu-page .pos-tab-content { padding-top:12px; }.posyandu-page .pos-main-grid { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(300px,.65fr); gap:12px; }.posyandu-page .pos-column { display:grid; align-content:start; gap:12px; }
    .posyandu-page .pos-card { border:1px solid var(--pos-border); border-radius:14px; background:var(--pos-surface); padding:13px; color:var(--pos-text); overflow:hidden; }.posyandu-page .pos-card-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }.posyandu-page .pos-card-head h5 { margin:0; font-size:.94rem; color:var(--pos-text); }.posyandu-page .pos-card-head h5 i { color:var(--accent,#f6d860); margin-right:6px; }.posyandu-page .pos-card-head .form-select,.posyandu-page .pos-card-head .form-control { width:min(220px,48%); }
    .posyandu-page .pos-chart-wrap { position:relative; height:280px; max-height:280px; }.posyandu-page .pos-chart-meta { min-height:24px; padding-top:7px; font-size:.78rem; color:var(--pos-muted); }.posyandu-page .pos-empty { padding:18px; color:var(--pos-muted); text-align:center; }
    .posyandu-page .pos-table-scroll { max-height:310px; overflow:auto; }.posyandu-page .pos-large-table-scroll { max-height:390px; overflow:auto; }.posyandu-page .table { color:var(--pos-text); font-size:.78rem; }.posyandu-page .table thead { position:sticky; top:0; z-index:1; }.posyandu-page .table th { background:var(--pos-raised)!important; color:var(--pos-text)!important; }.posyandu-page .pos-pagination { display:flex; justify-content:flex-end; align-items:center; gap:9px; padding-top:9px; font-size:.75rem; color:var(--pos-muted); }
    .posyandu-page .pos-compact-list { max-height:245px; overflow-y:auto; padding-right:3px; }.posyandu-page .pos-list-item,.posyandu-page .pos-visit { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:8px 0; border-bottom:1px solid var(--pos-border); }.posyandu-page .pos-list-item:last-child,.posyandu-page .pos-visit:last-child { border-bottom:0; }.posyandu-page .pos-list-item time { width:42px; height:42px; display:grid; place-content:center; text-align:center; border-radius:9px; background:var(--pos-raised); }.posyandu-page .pos-list-item time strong { font-size:1rem; line-height:1; }.posyandu-page .pos-list-item time span,.posyandu-page .pos-list-item div span,.posyandu-page .pos-visit span { display:block; color:var(--pos-muted); font-size:.72rem; }.posyandu-page .pos-list-item > div { flex:1; min-width:0; }.posyandu-page .pos-list-item > div strong,.posyandu-page .pos-visit strong { display:block; font-size:.8rem; color:var(--pos-text); }
    .posyandu-page .pos-count { padding:2px 7px; border-radius:999px; background:var(--pos-raised); font-size:.72rem; }.posyandu-page .pos-queue-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; }.posyandu-page .pos-queue-grid div { padding:8px; border-radius:9px; text-align:center; background:var(--pos-raised); }.posyandu-page .pos-queue-grid strong { display:block; font-size:1.1rem; }.posyandu-page .pos-queue-grid span,.posyandu-page .pos-help { color:var(--pos-muted); font-size:.68rem; }.posyandu-page .pos-help { display:block; margin-top:8px; }
    .posyandu-page .visit-list { max-height:218px; }.posyandu-page .immun-list { max-height:330px; }.posyandu-page .pos-info-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }.posyandu-page .pos-tip { display:flex; gap:12px; align-items:flex-start; }.posyandu-page .pos-tip > i,.posyandu-page .pos-gallery > i { font-size:1.35rem; color:var(--accent,#f6d860); }.posyandu-page .pos-tip h5 { margin:0 0 4px; font-size:.92rem; }.posyandu-page .pos-tip p,.posyandu-page .pos-gallery span { margin:0; color:var(--pos-muted); font-size:.78rem; }.posyandu-page .pos-gallery { min-height:130px; display:flex; flex-direction:column; justify-content:center; align-items:center; gap:7px; text-align:center; }
    .posyandu-page .modal .form-label { font-size:.78rem; margin-bottom:3px; }
    html[data-bs-theme="light"] .posyandu-page .pos-icon.blue { color:#1d4ed8; } html[data-bs-theme="light"] .posyandu-page .pos-icon.purple { color:#7e22ce; } html[data-bs-theme="light"] .posyandu-page .pos-icon.amber { color:#92400e; } html[data-bs-theme="light"] .posyandu-page .pos-icon.green { color:#166534; }
    @media(max-width:1100px){.posyandu-page .pos-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.posyandu-page .pos-main-grid{grid-template-columns:1fr;}.posyandu-page .pos-info-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
    @media(max-width:576px){.posyandu-page{padding-left:8px!important;padding-right:8px!important}.posyandu-page .pos-head{align-items:flex-start}.posyandu-page .pos-head p{display:none}.posyandu-page .pos-summary-grid{grid-template-columns:1fr 1fr;gap:7px}.posyandu-page .pos-summary-card{min-height:68px;padding:9px}.posyandu-page .nutrition-card{grid-column:1/-1}.posyandu-page .pos-icon{display:none}.posyandu-page .pos-card-head{align-items:flex-start;flex-direction:column}.posyandu-page .pos-card-head .form-select,.posyandu-page .pos-card-head .form-control{width:100%}.posyandu-page .pos-chart-wrap{height:250px}.posyandu-page .pos-info-grid{grid-template-columns:1fr}.posyandu-page .pos-quick-actions{flex-wrap:nowrap;overflow-x:auto}.posyandu-page .pos-quick-actions .btn{white-space:nowrap}}
</style>
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
        const styles = getComputedStyle(document.documentElement);
        const get = (name, fallback) => styles.getPropertyValue(name).trim() || fallback;
        return { label:get('--chart-label','#37443b'), grid:get('--chart-grid','rgba(55,68,59,.24)'), weight:get('--chart-income','#166534'), weightFill:get('--chart-income-fill','rgba(22,101,52,.14)'), height:get('--chart-secondary','#075985'), heightFill:get('--chart-secondary-fill','rgba(7,89,133,.12)'), point:get('--chart-point-border','#f5f7f2') };
    }

    function showSelectedChild() {
        if (!select || !canvas || typeof Chart === 'undefined') return;
        const child = growthData[select.value];
        const hasData = child && child.labels.length;
        empty.classList.toggle('d-none', !!hasData);
        canvas.parentElement.classList.toggle('d-none', !hasData);
        meta.textContent = child ? `${child.name} · Orang tua: ${child.parent} · Status: ${child.status}` : '';
        if (!hasData) return;
        const colors = chartColors();
        if (!growthChart) {
            growthChart = new Chart(canvas.getContext('2d'), { type:'line', data:{ labels:child.labels, datasets:[{label:'Berat Badan (kg)',data:child.weight,borderColor:colors.weight,backgroundColor:colors.weightFill,pointBackgroundColor:colors.weight,pointBorderColor:colors.point,tension:.35,fill:true},{label:'Tinggi Badan (cm)',data:child.height,borderColor:colors.height,backgroundColor:colors.heightFill,pointBackgroundColor:colors.height,pointBorderColor:colors.point,tension:.35,fill:true}] }, options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{color:colors.label,usePointStyle:true}}},scales:{x:{ticks:{color:colors.label,maxRotation:0},grid:{color:colors.grid}},y:{beginAtZero:true,ticks:{color:colors.label},grid:{color:colors.grid}}}} });
        } else {
            growthChart.data.labels = child.labels;
            growthChart.data.datasets[0].data = child.weight;
            growthChart.data.datasets[1].data = child.height;
            growthChart.update();
        }
    }
    select?.addEventListener('change', showSelectedChild);
    showSelectedChild();

    window.addEventListener('themechange', function () {
        if (!growthChart) return;
        const colors = chartColors();
        Object.assign(growthChart.data.datasets[0], {borderColor:colors.weight,backgroundColor:colors.weightFill,pointBackgroundColor:colors.weight,pointBorderColor:colors.point});
        Object.assign(growthChart.data.datasets[1], {borderColor:colors.height,backgroundColor:colors.heightFill,pointBackgroundColor:colors.height,pointBorderColor:colors.point});
        growthChart.options.plugins.legend.labels.color = colors.label;
        growthChart.options.scales.x.ticks.color = colors.label; growthChart.options.scales.x.grid.color = colors.grid;
        growthChart.options.scales.y.ticks.color = colors.label; growthChart.options.scales.y.grid.color = colors.grid;
        growthChart.update();
    });

    const search = document.getElementById('childSearch');
    const rows = Array.from(document.querySelectorAll('#childTable tbody tr'));
    const prev = document.getElementById('childPrev'); const next = document.getElementById('childNext'); const info = document.getElementById('childPageInfo');
    let page = 1; const perPage = 8;
    function renderRows() {
        const term = (search?.value || '').toLowerCase();
        const filtered = rows.filter(row => row.textContent.toLowerCase().includes(term));
        const pages = Math.max(1, Math.ceil(filtered.length / perPage)); page = Math.min(page, pages);
        rows.forEach(row => row.style.display = 'none'); filtered.slice((page-1)*perPage,page*perPage).forEach(row => row.style.display = '');
        info.textContent = `${page} / ${pages}`; prev.disabled = page <= 1; next.disabled = page >= pages;
    }
    search?.addEventListener('input', () => { page=1; renderRows(); }); prev?.addEventListener('click', () => { if(page>1){page--;renderRows();} }); next?.addEventListener('click', () => { page++;renderRows(); }); renderRows();
});
</script>
@endpush
