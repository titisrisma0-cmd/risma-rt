@extends('layouts.app')

@php
    $absensiStats = $wargas->map(function ($warga) use ($jadwals) {
        $absensi = $jadwals->flatMap(fn ($jadwal) => $jadwal->absensi)->where('user_id', $warga->id);

        return [
            'warga' => $warga,
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'alpa' => $absensi->where('status', 'alpa')->count(),
        ];
    })->sortByDesc('hadir')->values();
@endphp

@section('content')
<div class="container-fluid px-4 layanan-page ronda-compact-page">
    <div class="page-head">
        <div>
            <h2><i class="fas fa-shield-halved text-danger me-2"></i> Keamanan / Ronda</h2>
            <p>Jadwal ronda malam, kontak darurat, panic button, dan absensi keamanan warga.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(Auth::user()->isAdminRw() || Auth::user()->isAdminRt())
                <a href="{{ route('layanan.ronda.cctv.live') }}" class="btn btn-danger"><i class="fas fa-video me-1"></i> Live CCTV</a>
                <a href="{{ route('layanan.ronda.cctv.recordings') }}" class="btn btn-outline-primary"><i class="fas fa-clock-rotate-left me-1"></i> Arsip CCTV</a>
            @endif
            <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-outline-light"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </div>

    @if($activePanicReports->count())
        <div class="ronda-alert mb-3">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <strong>Ada laporan darurat aktif</strong>
                <span>{{ $activePanicReports->first()->user->name ?? 'Warga' }}: {{ $activePanicReports->first()->deskripsi ?: 'Mohon warga sekitar waspada.' }}</span>
            </div>
        </div>
    @endif

    <div class="row g-2 mb-3 ronda-summary-row">
        <div class="col-md-4">
            <div class="ronda-summary-card">
                <i class="fas fa-moon"></i>
                <div>
                    <strong>{{ $jadwalSaya->count() }}</strong>
                    <span>Jadwal Saya</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ronda-summary-card">
                <i class="fas fa-users-viewfinder"></i>
                <div>
                    <strong>{{ $jadwals->count() }}</strong>
                    <span>Jadwal RW Aktif</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ronda-summary-card">
                <i class="fas fa-phone-volume"></i>
                <div>
                    <strong>{{ $kontaks->count() }}</strong>
                    <span>Kontak Darurat</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            @if(!$isPengurus)
                <div class="glass-card mb-4">
                    <div class="card-header-custom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i> Jadwal Ronda Saya</h5>
                        <span class="badge bg-primary">{{ $jadwalSaya->count() }} Giliran</span>
                    </div>
                    <div class="card-body">
                        <div class="ronda-schedule-grid ronda-list-scroll">
                            @forelse($jadwalSaya as $jadwal)
                                <div class="ronda-schedule-card is-mine">
                                    <div class="ronda-date">
                                        <strong>{{ $jadwal->tanggal->format('d') }}</strong>
                                        <span>{{ $jadwal->tanggal->format('M') }}</span>
                                    </div>
                                    <div>
                                        <strong>{{ ucfirst($jadwal->shift) }}</strong>
                                        <span>{{ $jadwal->lokasi_pos ?: 'Pos Ronda' }}</span>
                                        <small>Petugas: {{ $jadwal->petugas->pluck('user.name')->filter()->join(', ') ?: '-' }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">Belum ada giliran ronda untuk akun Anda.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <div class="glass-card mb-4">
                <div class="card-header-custom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Jadwal Ronda Warga Satu RW</h5>
                    <span class="badge bg-primary">{{ $jadwals->count() }} Jadwal</span>
                </div>
                <div class="card-body table-responsive ronda-table-scroll">
                    <table class="table table-dark table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Lokasi</th>
                                <th>Petugas</th>
                                <th>Absensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwals as $jadwal)
                                <tr>
                                    <td>{{ $jadwal->tanggal->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-info">{{ ucfirst($jadwal->shift) }}</span></td>
                                    <td>{{ $jadwal->lokasi_pos ?: 'Pos Ronda' }}</td>
                                    <td>{{ $jadwal->petugas->pluck('user.name')->filter()->join(', ') ?: '-' }}</td>
                                    <td>
                                        <span class="ronda-mini-stat hadir">{{ $jadwal->absensi->where('status', 'hadir')->count() }} Hadir</span>
                                        <span class="ronda-mini-stat alpa">{{ $jadwal->absensi->where('status', 'alpa')->count() }} Alpa</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Belum ada jadwal ronda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($isPengurus)
                <div class="glass-card">
                    <div class="card-header-custom"><h5 class="mb-0"><i class="fas fa-chart-simple me-2"></i> Rekap Absensi Warga</h5></div>
                    <div class="card-body table-responsive ronda-table-scroll">
                        <table class="table table-dark table-hover align-middle">
                            <thead><tr><th>Warga</th><th>Hadir</th><th>Izin</th><th>Alpa</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($absensiStats as $item)
                                    <tr>
                                        <td>{{ $item['warga']->name }}</td>
                                        <td><span class="ronda-mini-stat hadir">{{ $item['hadir'] }}</span></td>
                                        <td><span class="ronda-mini-stat izin">{{ $item['izin'] }}</span></td>
                                        <td><span class="ronda-mini-stat alpa">{{ $item['alpa'] }}</span></td>
                                        <td>{{ $item['alpa'] > $item['hadir'] ? 'Perlu diingatkan' : 'Terpantau' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary">Belum ada data absensi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="glass-card mb-4">
                <div class="card-header-custom"><h5 class="mb-0"><i class="fas fa-phone-volume me-2"></i> Nomor Kontak Darurat</h5></div>
                <div class="card-body ronda-contact-scroll">
                    @foreach($kontaks as $kontak)
                        <a class="contact-item emergency-contact" href="tel:{{ $kontak->nomor_telepon }}">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>{{ $kontak->kategori }}</strong>
                                <span>{{ $kontak->nama }} - {{ $kontak->nomor_telepon }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            @if(!$isPengurus)
                <form method="POST" action="{{ route('layanan.ronda.panic.store') }}" class="glass-card p-3 panic-panel">
                    @csrf
                    <h5><i class="fas fa-triangle-exclamation me-2"></i> Tombol Darurat</h5>
                    <p>Gunakan hanya saat ada maling, kebakaran, bencana, atau kejadian yang membutuhkan bantuan warga sekitar.</p>
                    <textarea class="form-control mb-3" name="deskripsi" rows="3" placeholder="Contoh: Ada orang mencurigakan di dekat pos ronda."></textarea>
                    <button class="panic-btn" onclick="return confirm('Kirim laporan darurat ke warga sekitar?')">
                        <i class="fas fa-bell"></i> Panic Button
                    </button>
                </form>
            @else
                <div class="glass-card mb-3">
                    <button class="card-header-custom ronda-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rondaScheduleForm" aria-expanded="false" aria-controls="rondaScheduleForm">
                        <span><i class="fas fa-shuffle me-2"></i> Pengaturan Jadwal</span><i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="card-body collapse" id="rondaScheduleForm">
                        <form method="POST" action="{{ route('layanan.ronda.jadwal.store') }}">
                            @csrf
                            <label class="form-label">Mode Jadwal</label>
                            <select class="form-select mb-2" name="mode">
                                <option value="manual">Manual</option>
                                <option value="acak">Acak Otomatis</option>
                            </select>
                            <label class="form-label">Tanggal Mulai</label>
                            <input class="form-control mb-2" type="date" name="tanggal" required>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">Jumlah Hari</label>
                                    <input class="form-control mb-2" type="number" name="jumlah_hari" min="1" max="31" value="7">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Petugas/Hari</label>
                                    <input class="form-control mb-2" type="number" name="jumlah_petugas" min="1" max="10" value="3">
                                </div>
                            </div>
                            <label class="form-label">Shift</label>
                            <input class="form-control mb-2" name="shift" value="Malam" required>
                            <label class="form-label">Lokasi Pos</label>
                            <input class="form-control mb-2" name="lokasi_pos" placeholder="Pos ronda RW">
                            <label class="form-label">Petugas Manual / Pool Acak</label>
                            <select class="form-select mb-3" name="petugas[]" multiple size="6">
                                @foreach($wargas as $warga)
                                    <option value="{{ $warga->id }}">{{ $warga->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-gradient w-100">Simpan Jadwal</button>
                        </form>
                    </div>
                </div>

                <div class="glass-card mb-3">
                    <button class="card-header-custom ronda-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rondaAttendanceForm" aria-expanded="false" aria-controls="rondaAttendanceForm">
                        <span><i class="fas fa-user-check me-2"></i> Input Absensi</span><i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="card-body collapse" id="rondaAttendanceForm">
                        <form method="POST" action="{{ route('layanan.ronda.absensi.store') }}">
                            @csrf
                            <label class="form-label">Jadwal</label>
                            <select class="form-select mb-2" name="jadwal_ronda_id" required>
                                @foreach($jadwals as $jadwal)
                                    <option value="{{ $jadwal->id }}">{{ $jadwal->tanggal->format('d/m/Y') }} - {{ $jadwal->shift }}</option>
                                @endforeach
                            </select>
                            <label class="form-label">Warga</label>
                            <select class="form-select mb-2" name="user_id" required>
                                @foreach($wargas as $warga)
                                    <option value="{{ $warga->id }}">{{ $warga->name }}</option>
                                @endforeach
                            </select>
                            <label class="form-label">Status</label>
                            <select class="form-select mb-2" name="status" required>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="alpa">Alpa</option>
                            </select>
                            <input class="form-control mb-3" name="catatan" placeholder="Catatan absensi">
                            <button class="btn btn-gradient w-100">Simpan Absensi</button>
                        </form>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="card-header-custom"><h5 class="mb-0"><i class="fas fa-bell me-2"></i> Laporan Panic Button</h5></div>
                    <div class="card-body ronda-report-scroll">
                        @forelse($panicReports as $panic)
                            <div class="timeline-item">
                                <strong>{{ $panic->judul }}</strong>
                                <span>{{ $panic->user->name ?? 'Warga' }} - {{ $panic->created_at->format('d/m/Y H:i') }} - {{ ucwords(str_replace('_', ' ', $panic->status)) }}</span>
                                <p>{{ $panic->deskripsi ?: 'Laporan darurat dari warga.' }}</p>
                            </div>
                        @empty
                            <div class="empty-state">Belum ada laporan darurat.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<style>
    /* Scoped: hanya memadatkan halaman Keamanan / Ronda. */
    .ronda-compact-page { --ronda-gap: 12px; }
    .ronda-compact-page .page-head { margin-bottom: 12px; }
    .ronda-compact-page .page-head h2 { margin-bottom: 2px; font-size: clamp(1.25rem, 2vw, 1.65rem); }
    .ronda-compact-page .page-head p { margin: 0; font-size: .82rem; }
    .ronda-compact-page .ronda-alert { padding: 10px 13px; border-radius: 12px; }
    .ronda-compact-page .ronda-alert i { font-size: 1.25rem; }
    .ronda-compact-page .ronda-summary-card { min-height: 70px; padding: 10px 12px; gap: 10px; border-radius: 12px; }
    .ronda-compact-page .ronda-summary-card i { width: 36px; height: 36px; border-radius: 10px; font-size: 1rem; }
    .ronda-compact-page .ronda-summary-card strong { font-size: 1.3rem; }
    .ronda-compact-page .ronda-summary-card span { font-size: .75rem; }
    .ronda-compact-page .glass-card { border-radius: 14px; }
    .ronda-compact-page .glass-card.mb-4 { margin-bottom: var(--ronda-gap) !important; }
    .ronda-compact-page .card-header-custom { padding: 10px 13px; }
    .ronda-compact-page .card-header-custom h5 { font-size: .9rem; }
    .ronda-compact-page .card-body { padding: 11px 13px; }
    .ronda-compact-page .ronda-list-scroll { max-height: 230px; overflow-y: auto; padding-right: 3px; }
    .ronda-compact-page .ronda-schedule-grid { gap: 8px; }
    .ronda-compact-page .ronda-schedule-card { grid-template-columns: 44px 1fr; gap: 8px; padding: 8px; border-radius: 10px; }
    .ronda-compact-page .ronda-date { width: 42px; min-height: 42px; border-radius: 9px; }
    .ronda-compact-page .ronda-date strong { font-size: 1rem; }
    .ronda-compact-page .ronda-schedule-card span,
    .ronda-compact-page .ronda-schedule-card small { font-size: .7rem; }
    .ronda-compact-page .ronda-table-scroll { max-height: 310px; overflow: auto; }
    .ronda-compact-page .ronda-table-scroll .table { font-size: .78rem; white-space: nowrap; }
    .ronda-compact-page .ronda-table-scroll thead { position: sticky; top: 0; z-index: 2; }
    .ronda-compact-page .ronda-table-scroll th,
    .ronda-compact-page .ronda-table-scroll td { padding: 7px 9px; }
    .ronda-compact-page .ronda-mini-stat { padding: 3px 6px; font-size: .68rem; }
    .ronda-compact-page .ronda-contact-scroll { max-height: 220px; overflow-y: auto; }
    .ronda-compact-page .contact-item { padding: 8px; margin-bottom: 6px; }
    .ronda-compact-page .emergency-contact > i { width: 32px; height: 32px; border-radius: 9px; }
    .ronda-compact-page .contact-item strong { font-size: .78rem; }
    .ronda-compact-page .contact-item span { font-size: .7rem; }
    .ronda-compact-page .panic-panel { padding: 12px !important; }
    .ronda-compact-page .panic-panel p { font-size: .75rem; }
    .ronda-compact-page .panic-panel textarea { min-height: 68px; }
    .ronda-compact-page .ronda-collapse-toggle { width: 100%; display: flex; align-items: center; justify-content: space-between; border: 0; color: var(--text-primary); font-size: .86rem; font-weight: 700; text-align: left; }
    .ronda-compact-page .ronda-collapse-toggle > i { transition: transform .2s ease; }
    .ronda-compact-page .ronda-collapse-toggle:not(.collapsed) > i { transform: rotate(180deg); }
    .ronda-compact-page .ronda-report-scroll { max-height: 240px; overflow-y: auto; }
    .ronda-compact-page .timeline-item { padding: 8px 0; }
    @media (max-width: 767.98px) {
        .ronda-compact-page { padding-left: 8px !important; padding-right: 8px !important; }
        .ronda-compact-page .page-head p { display: none; }
        .ronda-compact-page .ronda-summary-row > div { width: 33.333%; }
        .ronda-compact-page .ronda-summary-card { display: block; min-height: 64px; text-align: center; }
        .ronda-compact-page .ronda-summary-card i { display: none; }
        .ronda-compact-page .ronda-table-scroll { max-height: 280px; }
    }
</style>
@include('layanan.partials.styles')
@endsection
