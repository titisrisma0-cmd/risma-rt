@extends('layouts.app')

@section('title', 'Manajemen Mutasi Warga Terpadu')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-people-arrows text-emerald me-2"></i>
                @if($user->isAdminRt())
                    Mutasi Warga — RT {{ $user->getRtKode() ?? '01' }}
                @else
                    Mutasi Warga — Seluruh Wilayah RW
                @endif
            </h1>
            <p class="page-subtitle">Sistem administrasi mutasi kependudukan terpadu: Mutasi Masuk, Mutasi Keluar, Mutasi Antar-RT, dan Riwayat Mutasi</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if($activeTab === 'masuk')
                <button type="button" class="btn btn-emerald text-white px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalMutasiMasuk">
                    <i class="fas fa-user-plus me-1"></i> Daftarkan Mutasi Masuk
                </button>
            @elseif($activeTab === 'keluar')
                <button type="button" class="btn btn-warning text-dark px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalMutasiKeluar">
                    <i class="fas fa-sign-out-alt me-1"></i> Catat Mutasi Keluar
                </button>
            @elseif($activeTab === 'antar_rt')
                <button type="button" class="btn btn-primary px-3 py-2 text-white" data-bs-toggle="modal" data-bs-target="#modalMutasiAntarRt">
                    <i class="fas fa-exchange-alt me-1"></i> Ajukan Pindah Antar-RT
                </button>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 4 Integrated Nav Tabs --}}
    <div class="saas-card mb-4 p-2 bg-white border">
        <ul class="nav nav-pills nav-fill gap-2" id="mutasiTabs">
            <li class="nav-item">
                <a href="{{ route('warga.mutasi', ['tab' => 'masuk', 'rt_id' => request('rt_id')]) }}" 
                   class="nav-link py-2 px-3 fw-semibold {{ $activeTab === 'masuk' ? 'active bg-emerald text-white' : 'text-secondary' }}" style="border-radius: 8px;">
                    <i class="fas fa-door-open me-2"></i> 1. Mutasi Masuk
                    @php $pendingMasuk = $mutasiMasukList->where('status_verifikasi', 'menunggu')->count(); @endphp
                    @if($pendingMasuk > 0)
                        <span class="badge bg-danger ms-1">{{ $pendingMasuk }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('warga.mutasi', ['tab' => 'keluar', 'rt_id' => request('rt_id')]) }}" 
                   class="nav-link py-2 px-3 fw-semibold {{ $activeTab === 'keluar' ? 'active bg-emerald text-white' : 'text-secondary' }}" style="border-radius: 8px;">
                    <i class="fas fa-door-closed me-2"></i> 2. Mutasi Keluar
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('warga.mutasi', ['tab' => 'antar_rt', 'rt_id' => request('rt_id')]) }}" 
                   class="nav-link py-2 px-3 fw-semibold {{ $activeTab === 'antar_rt' ? 'active bg-emerald text-white' : 'text-secondary' }}" style="border-radius: 8px;">
                    <i class="fas fa-arrows-split-up-and-left me-2"></i> 3. Mutasi Antar-RT
                    @php $pendingAntarRt = $mutasiAntarRtList->where('status_verifikasi', 'menunggu_tujuan')->count(); @endphp
                    @if($pendingAntarRt > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingAntarRt }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('warga.mutasi', ['tab' => 'riwayat', 'rt_id' => request('rt_id')]) }}" 
                   class="nav-link py-2 px-3 fw-semibold {{ $activeTab === 'riwayat' ? 'active bg-emerald text-white' : 'text-secondary' }}" style="border-radius: 8px;">
                    <i class="fas fa-history me-2"></i> 4. Riwayat Mutasi
                </a>
            </li>
        </ul>
    </div>

    {{-- TAB 1: MUTASI MASUK --}}
    @if($activeTab === 'masuk')
        <div class="saas-card overflow-hidden mb-4">
            <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-inbox text-emerald me-2"></i>Antrean & Daftar Mutasi Masuk</h6>
                    <small class="text-secondary">Warga pendatang baru yang mendaftar ke wilayah RT</small>
                </div>
                <button type="button" class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalMutasiMasuk">
                    <i class="fas fa-plus me-1"></i> Tambah Mutasi Masuk
                </button>
            </div>

            <div class="table-responsive">
                <table class="table-saas">
                    <thead>
                        <tr>
                            <th>Tgl Mutasi</th>
                            <th>Kepala Keluarga / Warga</th>
                            <th>No. KK & NIK</th>
                            <th>Alamat Asal</th>
                            <th>RT Tujuan</th>
                            <th class="text-center">Status Verifikasi</th>
                            <th class="text-end">Aksi RT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiMasukList as $m)
                            <tr>
                                <td class="font-monospace small text-secondary">
                                    {{ $m->tanggal_mutasi ? $m->tanggal_mutasi->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $m->nama_warga }}</div>
                                    <small class="text-secondary">{{ $m->keterangan ?? 'Warga Pendatang Baru' }}</small>
                                </td>
                                <td class="font-monospace small">
                                    <div class="text-emerald fw-semibold">KK: {{ $m->no_kk }}</div>
                                    <div class="text-secondary">NIK: {{ $m->nik ?? '-' }}</div>
                                </td>
                                <td class="small" style="max-width: 200px;">
                                    <div class="text-truncate" title="{{ $m->alamat_asal }}">{{ $m->alamat_asal }}</div>
                                </td>
                                <td>
                                    <span class="badge-soft-info">RT {{ optional($m->rt)->kode_rt ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($m->status_verifikasi === 'menunggu')
                                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">
                                            <i class="fas fa-clock me-1"></i> Menunggu Verifikasi
                                        </span>
                                    @elseif($m->status_verifikasi === 'disetujui')
                                        <span class="badge bg-success px-3 py-1 rounded-pill">
                                            <i class="fas fa-check me-1"></i> Disetujui (Aktif)
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-1 rounded-pill">
                                            <i class="fas fa-times me-1"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($m->status_verifikasi === 'menunggu')
                                        <form action="{{ route('warga.mutasi.approve', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui mutasi masuk warga ini? Sistem akan membuat data Kartu Keluarga dan profil warga secara atomik.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-emerald text-white me-1">
                                                <i class="fas fa-check"></i> Setujui
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejectMutasi{{ $m->id }}">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    @else
                                        <span class="text-muted small">Selesai</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Modal Reject Mutasi --}}
                            <div class="modal fade" id="modalRejectMutasi{{ $m->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="{{ route('warga.mutasi.reject', $m) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-light">
                                                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-times-circle text-danger me-2"></i>Tolak Permohonan Mutasi</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <p class="small text-secondary mb-3">Masukkan alasan penolakan mutasi untuk <strong>{{ $m->nama_warga }}</strong>:</p>
                                                <textarea name="catatan_verifikasi" rows="3" class="form-control form-control-saas" placeholder="Alasan penolakan..." required></textarea>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger px-4">Tolak Permohonan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-door-open fa-3x mb-3 text-muted d-block opacity-50"></i>
                                    Belum ada data antrean mutasi masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB 2: MUTASI KELUAR --}}
    @if($activeTab === 'keluar')
        <div class="saas-card overflow-hidden mb-4">
            <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-sign-out-alt text-warning me-2"></i>Daftar Mutasi Keluar & Pindah Domisili</h6>
                    <small class="text-secondary">Pencatatan kepindahan per orangan atau sekeluarga (data riwayat tetap terjaga)</small>
                </div>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#modalMutasiKeluar">
                    <i class="fas fa-plus me-1"></i> Catat Mutasi Keluar
                </button>
            </div>

            <div class="table-responsive">
                <table class="table-saas">
                    <thead>
                        <tr>
                            <th>Tgl Keluar</th>
                            <th>Nama Warga / Keluarga</th>
                            <th>No. KK / NIK</th>
                            <th>Wilayah Asal</th>
                            <th>Alamat Tujuan Pindah</th>
                            <th>Alasan / Keterangan</th>
                            <th class="text-end">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiKeluarList as $m)
                            <tr>
                                <td class="font-monospace small text-secondary">
                                    {{ $m->tanggal_mutasi ? $m->tanggal_mutasi->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $m->nama_warga }}</div>
                                    <span class="badge {{ $m->jenis_mutasi === 'meninggal' ? 'badge-soft-danger' : 'badge-soft-warning' }} small">
                                        {{ $m->jenis_mutasi === 'meninggal' ? 'Meninggal Dunia' : 'Pindah Keluar' }}
                                    </span>
                                </td>
                                <td class="font-monospace small">
                                    <div class="text-secondary">KK: {{ $m->no_kk ?? '-' }}</div>
                                    @if($m->nik)
                                        <div class="text-muted">NIK: {{ $m->nik }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-soft-info">RT {{ optional($m->rt)->kode_rt ?? '-' }}</span>
                                </td>
                                <td class="small" style="max-width: 250px;">
                                    <div class="text-truncate" title="{{ $m->alamat_tujuan }}">{{ $m->alamat_tujuan ?: '-' }}</div>
                                </td>
                                <td class="small text-secondary" style="max-width: 250px;">
                                    {{ $m->keterangan ?? '-' }}
                                </td>
                                <td class="text-end text-secondary small">
                                    {{ optional($m->creator)->name ?? 'Pengurus RT' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-door-closed fa-3x mb-3 text-muted d-block opacity-50"></i>
                                    Belum ada data mutasi keluar yang dicatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB 3: MUTASI ANTAR-RT --}}
    @if($activeTab === 'antar_rt')
        <div class="saas-card overflow-hidden mb-4">
            <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-arrows-split-up-and-left text-primary me-2"></i>Permohonan Mutasi Antar-RT</h6>
                    <small class="text-secondary">Perpindahan warga antar-RT dalam wilayah RW (memerlukan verifikasi RT Tujuan)</small>
                </div>
                <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#modalMutasiAntarRt">
                    <i class="fas fa-plus me-1"></i> Ajukan Pindah Antar-RT
                </button>
            </div>

            <div class="table-responsive">
                <table class="table-saas">
                    <thead>
                        <tr>
                            <th>Tgl Pengajuan</th>
                            <th>Nama Warga</th>
                            <th>NIK & No. KK</th>
                            <th>RT Asal</th>
                            <th>RT Tujuan</th>
                            <th class="text-center">Status Verifikasi</th>
                            <th class="text-end">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiAntarRtList as $m)
                            <tr>
                                <td class="font-monospace small text-secondary">
                                    {{ $m->tanggal_mutasi ? $m->tanggal_mutasi->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $m->nama_warga }}</div>
                                    <small class="text-secondary">{{ $m->keterangan ?? 'Pindah domisili RT' }}</small>
                                </td>
                                <td class="font-monospace small">
                                    <div class="text-emerald fw-semibold">NIK: {{ $m->nik ?? '-' }}</div>
                                    <div class="text-secondary">KK: {{ $m->no_kk ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge-soft-info">RT {{ optional($m->rt)->kode_rt ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge-soft-success fw-bold">RT {{ optional($m->rtTujuan)->kode_rt ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($m->status_verifikasi === 'menunggu_tujuan')
                                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">
                                            <i class="fas fa-clock me-1"></i> Menunggu Verifikasi RT Tujuan
                                        </span>
                                    @elseif($m->status_verifikasi === 'disetujui')
                                        <span class="badge bg-success px-3 py-1 rounded-pill">
                                            <i class="fas fa-check me-1"></i> Disetujui (RT {{ optional($m->rtTujuan)->kode_rt }})
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-1 rounded-pill">
                                            <i class="fas fa-times me-1"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($m->status_verifikasi === 'menunggu_tujuan')
                                        @if(($user->isAdminRt() && (int)$m->rt_id_tujuan === (int)$user->managedRtId()) || $user->isAdminRw())
                                            <form action="{{ route('warga.mutasi.approve', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui mutasi masuk warga {{ $m->nama_warga }} ke wilayah RT Anda? RT domisili warga akan otomatis diperbarui.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-emerald text-white me-1">
                                                    <i class="fas fa-check"></i> Setujui Masuk
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejectAntarRt{{ $m->id }}">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        @else
                                            <span class="badge bg-light text-secondary border">Menunggu RT {{ optional($m->rtTujuan)->kode_rt }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">Selesai</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Modal Reject Antar-RT --}}
                            <div class="modal fade" id="modalRejectAntarRt{{ $m->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="{{ route('warga.mutasi.reject', $m) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-light">
                                                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-times-circle text-danger me-2"></i>Tolak Mutasi Antar-RT</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <p class="small text-secondary mb-3">Alasan penolakan mutasi kepindahan warga <strong>{{ $m->nama_warga }}</strong>:</p>
                                                <textarea name="catatan_verifikasi" rows="3" class="form-control form-control-saas" placeholder="Alasan penolakan..." required></textarea>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger px-4">Tolak Permohonan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-arrows-split-up-and-left fa-3x mb-3 text-muted d-block opacity-50"></i>
                                    Belum ada data permohonan mutasi antar-RT.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB 4: RIWAYAT MUTASI --}}
    @if($activeTab === 'riwayat')
        <div class="saas-card p-3 mb-4">
            <form method="GET" action="{{ route('warga.mutasi') }}" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="riwayat">
                @if($user->isAdminRw())
                    <div class="col-md-3">
                        <label class="form-label text-secondary small mb-1">Wilayah RT</label>
                        <select name="rt_id" class="form-select form-control-saas" onchange="this.form.submit()">
                            <option value="">Semua RT</option>
                            @foreach($rts as $r)
                                <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="{{ $user->isAdminRw() ? 'col-md-3' : 'col-md-4' }}">
                    <label class="form-label text-secondary small mb-1">Jenis Mutasi</label>
                    <select name="jenis" class="form-select form-control-saas">
                        <option value="">Semua Jenis Mutasi</option>
                        <option value="masuk" {{ request('jenis') === 'masuk' ? 'selected' : '' }}>Masuk (Pendatang)</option>
                        <option value="keluar" {{ request('jenis') === 'keluar' ? 'selected' : '' }}>Keluar (Pindah Domisili)</option>
                        <option value="pindah_rt" {{ request('jenis') === 'pindah_rt' ? 'selected' : '' }}>Pindah Antar RT</option>
                        <option value="meninggal" {{ request('jenis') === 'meninggal' ? 'selected' : '' }}>Meninggal Dunia</option>
                    </select>
                </div>
                <div class="{{ $user->isAdminRw() ? 'col-md-4' : 'col-md-6' }}">
                    <label class="form-label text-secondary small mb-1">Cari Nama / NIK / No. KK</label>
                    <input type="text" name="q" class="form-control form-control-saas" placeholder="Ketik kata kunci..." value="{{ request('q') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald text-white w-100">Filter</button>
                    @if(request()->hasAny(['q', 'rt_id', 'jenis']))
                        <a href="{{ route('warga.mutasi', ['tab' => 'riwayat']) }}" class="btn btn-outline-secondary" title="Reset"><i class="fas fa-redo"></i></a>
                    @endif
                </div>
            </form>
        </div>

        <div class="saas-card overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table-saas">
                    <thead>
                        <tr>
                            <th>Tgl Mutasi</th>
                            <th>Nama Warga</th>
                            <th>NIK & No. KK</th>
                            <th>Wilayah Asal & Tujuan</th>
                            <th class="text-center">Jenis Mutasi</th>
                            <th>Status Verifikasi</th>
                            <th class="text-end">Petugas Pencatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiRiwayatList as $m)
                            <tr>
                                <td class="font-monospace small text-secondary">
                                    {{ $m->tanggal_mutasi ? $m->tanggal_mutasi->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $m->nama_warga }}</div>
                                    <small class="text-secondary">{{ $m->keterangan ?? '-' }}</small>
                                </td>
                                <td class="font-monospace small">
                                    <div class="text-emerald fw-semibold">NIK: {{ $m->nik ?? '-' }}</div>
                                    <div class="text-secondary">KK: {{ $m->no_kk ?? '-' }}</div>
                                </td>
                                <td class="small">
                                    <div>Asal: <span class="badge-soft-info">RT {{ optional($m->rt)->kode_rt ?? '-' }}</span></div>
                                    @if($m->rt_id_tujuan)
                                        <div class="mt-1">Tujuan: <span class="badge-soft-success">RT {{ optional($m->rtTujuan)->kode_rt ?? '-' }}</span></div>
                                    @elseif($m->alamat_tujuan)
                                        <div class="text-truncate text-secondary" style="max-width: 180px;" title="{{ $m->alamat_tujuan }}">Tujuan: {{ $m->alamat_tujuan }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($m->jenis_mutasi === 'masuk')
                                        <span class="badge-soft-emerald">Masuk</span>
                                    @elseif($m->jenis_mutasi === 'keluar')
                                        <span class="badge-soft-warning">Keluar</span>
                                    @elseif($m->jenis_mutasi === 'pindah_rt')
                                        <span class="badge-soft-info">Pindah RT</span>
                                    @elseif($m->jenis_mutasi === 'meninggal')
                                        <span class="badge-soft-danger">Meninggal</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($m->jenis_mutasi) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($m->status_verifikasi === 'disetujui')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                            <i class="fas fa-check me-1"></i> Disetujui
                                        </span>
                                    @elseif($m->status_verifikasi === 'ditolak')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small">
                                            <i class="fas fa-times me-1"></i> Ditolak
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">
                                            <i class="fas fa-clock me-1"></i> Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end text-secondary small">
                                    {{ optional($m->creator)->name ?? 'Sistem' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-history fa-3x mb-3 text-muted d-block opacity-50"></i>
                                    Belum ada riwayat mutasi warga.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($mutasiRiwayatList->hasPages())
                <div class="p-3 border-top bg-white">
                    {{ $mutasiRiwayatList->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

{{-- ==================== MODALS MUTASI ==================== --}}

{{-- 1. MODAL MUTASI MASUK --}}
<div class="modal fade" id="modalMutasiMasuk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('warga.mutasi.masuk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark"><i class="fas fa-user-plus text-emerald me-2"></i>Pendaftaran Mutasi Masuk</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($user->isAdminRw())
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Wilayah RT Tujuan <span class="text-danger">*</span></label>
                            <select name="rt_id" class="form-select form-control-saas" required>
                                <option value="">-- Pilih Wilayah RT --</option>
                                @foreach($rts as $r)
                                    <option value="{{ $r->id }}" {{ $myRtId == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Nomor Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                            <input type="text" name="no_kk" class="form-control form-control-saas font-monospace" placeholder="16 digit nomor KK" maxlength="16" pattern="[0-9]{16}" required>
                            <small class="text-secondary">Wajib tepat 16 digit angka</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">NIK Kepala Keluarga <span class="text-danger">*</span></label>
                            <input type="text" name="nik" class="form-control form-control-saas font-monospace" placeholder="16 digit NIK Kepala" maxlength="16" pattern="[0-9]{16}" required>
                            <small class="text-secondary">Wajib tepat 16 digit angka</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Nama Lengkap Kepala Keluarga <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kepala_keluarga" class="form-control form-control-saas" placeholder="Nama lengkap sesuai KTP" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Alamat Asal (Sesuai Surat Pindah) <span class="text-danger">*</span></label>
                            <textarea name="alamat_asal" rows="2" class="form-control form-control-saas" placeholder="Alamat domisili sebelumnya..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Alamat Tempat Tinggal Baru</label>
                            <textarea name="alamat_tujuan" rows="2" class="form-control form-control-saas" placeholder="Alamat rumah baru di wilayah RT ini..."></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Tanggal Kedatangan / Mutasi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mutasi" class="form-control form-control-saas" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Upload Surat Pindah / Dokumen Pendukung</label>
                            <input type="file" name="dokumen_pendukung" class="form-control form-control-saas" accept=".pdf,image/*">
                            <small class="text-secondary">Format PDF, JPG, PNG. Maksimal 3MB.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Keterangan Tambahan</label>
                        <textarea name="keterangan" rows="2" class="form-control form-control-saas" placeholder="Catatan kepindahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald text-white px-4">Daftarkan Mutasi Masuk</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. MODAL MUTASI KELUAR --}}
<div class="modal fade" id="modalMutasiKeluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('warga.mutasi.keluar.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark"><i class="fas fa-sign-out-alt text-warning me-2"></i>Pencatatan Mutasi Keluar</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($user->isAdminRw())
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Wilayah RT Asal <span class="text-danger">*</span></label>
                            <select name="rt_id" class="form-select form-control-saas" required>
                                <option value="">-- Pilih Wilayah RT --</option>
                                @foreach($rts as $r)
                                    <option value="{{ $r->id }}" {{ $myRtId == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Tipe Kepindahan Keluar <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4 p-3 bg-light rounded border">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_keluar" id="tipeIndividu" value="individu" checked onchange="toggleTipeKeluar()">
                                <label class="form-check-label fw-medium text-dark" for="tipeIndividu">
                                    <i class="fas fa-user text-primary me-1"></i> Pindah Per Orangan (Individu)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_keluar" id="tipeSekeluarga" value="sekeluarga" onchange="toggleTipeKeluar()">
                                <label class="form-check-label fw-medium text-dark" for="tipeSekeluarga">
                                    <i class="fas fa-users text-emerald me-1"></i> Pindah Sekeluarga (Satu KK Penuh)
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Form Individu --}}
                    <div class="mb-3" id="groupIndividu">
                        <label class="form-label text-secondary small fw-medium">Pilih Warga Yang Pindah <span class="text-danger">*</span></label>
                        <select name="user_profile_id" id="selectWargaKeluar" class="form-select form-control-saas">
                            <option value="">-- Pilih Warga Dari Database --</option>
                            @foreach($activeWargaForKeluar as $w)
                                <option value="{{ $w->id }}">
                                    {{ $w->nama_lengkap }} — NIK: {{ $w->nik }} (KK: {{ $w->no_kk ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Form Sekeluarga --}}
                    <div class="mb-3" id="groupSekeluarga" style="display: none;">
                        <label class="form-label text-secondary small fw-medium">Pilih Kartu Keluarga Yang Pindah <span class="text-danger">*</span></label>
                        <select name="kartu_keluarga_id" id="selectKkKeluar" class="form-select form-control-saas">
                            <option value="">-- Pilih Kartu Keluarga Dari Database --</option>
                            @foreach($activeKkForKeluar as $k)
                                <option value="{{ $k->id }}">
                                    KK No. {{ $k->no_kk }} — Kepala: {{ $k->nama_kepala_keluarga }} ({{ $k->alamat }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Alamat Tujuan Kepindahan <span class="text-danger">*</span></label>
                        <textarea name="alamat_tujuan" rows="2" class="form-control form-control-saas" placeholder="Kelurahan, Kecamatan, Kota/Kabupaten domisili baru..." required></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Tanggal Mutasi Keluar <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mutasi" class="form-control form-control-saas" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Alasan Kepindahan</label>
                            <input type="text" name="keterangan" class="form-control form-control-saas" placeholder="Pindah tempat kerja / ikut suami / dll">
                        </div>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle me-1"></i> Data kependudukan warga akan diperbarui menjadi status <strong>Pindah</strong> dan tidak akan dihapus dari riwayat transaksi atau audit sistem.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-semibold px-4">Simpan Mutasi Keluar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. MODAL MUTASI ANTAR-RT --}}
<div class="modal fade" id="modalMutasiAntarRt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('warga.mutasi.antar-rt.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark"><i class="fas fa-exchange-alt text-primary me-2"></i>Permohonan Pindah Antar-RT</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($user->isAdminRw())
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">RT Asal <span class="text-danger">*</span></label>
                            <select name="rt_id_asal" class="form-select form-control-saas" required>
                                <option value="">-- Pilih RT Asal --</option>
                                @foreach($rts as $r)
                                    <option value="{{ $r->id }}" {{ $myRtId == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Pilih Warga Yang Pindah <span class="text-danger">*</span></label>
                        <select name="user_profile_id" class="form-select form-control-saas" required>
                            <option value="">-- Pilih Warga Dari Database --</option>
                            @foreach($activeWargaForKeluar as $w)
                                <option value="{{ $w->id }}">
                                    {{ $w->nama_lengkap }} — NIK: {{ $w->nik }} (RT {{ optional($w->wilayahRt)->kode_rt ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Pilih Wilayah RT Tujuan <span class="text-danger">*</span></label>
                        <select name="rt_id_tujuan" class="form-select form-control-saas" required>
                            <option value="">-- Pilih RT Tujuan --</option>
                            @foreach($rts as $r)
                                @if(!$myRtId || (int)$r->id !== (int)$myRtId)
                                    <option value="{{ $r->id }}">RT {{ $r->kode_rt }} ({{ $r->nama_rt ?? 'Wilayah RT ' . $r->kode_rt }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Tanggal Efektif Kepindahan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mutasi" class="form-control form-control-saas" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Alasan / Catatan Perpindahan</label>
                        <textarea name="keterangan" rows="2" class="form-control form-control-saas" placeholder="Pindah alamat blok / rumah baru di RT tujuan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary text-white px-4">Kirim Permohonan ke RT Tujuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleTipeKeluar() {
    const isIndividu = document.getElementById('tipeIndividu').checked;
    const groupIndividu = document.getElementById('groupIndividu');
    const groupSekeluarga = document.getElementById('groupSekeluarga');
    const selectWarga = document.getElementById('selectWargaKeluar');
    const selectKk = document.getElementById('selectKkKeluar');

    if (isIndividu) {
        groupIndividu.style.display = 'block';
        groupSekeluarga.style.display = 'none';
        if (selectWarga) selectWarga.required = true;
        if (selectKk) selectKk.required = false;
    } else {
        groupIndividu.style.display = 'none';
        groupSekeluarga.style.display = 'block';
        if (selectWarga) selectWarga.required = false;
        if (selectKk) selectKk.required = true;
    }
}
</script>
@endsection
