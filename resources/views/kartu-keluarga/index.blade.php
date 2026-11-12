@extends('layouts.app')

@section('title', 'Data Keluarga & Warga')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users-rectangle text-emerald me-2"></i>
                @if($user->isAdminRt())
                    Data Keluarga & Warga — RT {{ $user->getRtKode() ?? '01' }}
                @else
                    Data Keluarga & Warga — Seluruh Wilayah RW
                @endif
            </h1>
            <p class="page-subtitle">Pusat administrasi kependudukan terpadu berbasis Kartu Keluarga dan Anggota Keluarga</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('warga.mutasi') }}" class="btn btn-outline-warning px-3 py-2">
                <i class="fas fa-exchange-alt me-1"></i> Mutasi Warga
            </a>
            @if($user->isAdminRt() || $user->isAdminRw())
                <a href="{{ route('kartu-keluarga.create') }}" class="btn btn-emerald px-3 py-2 text-white">
                    <i class="fas fa-plus me-1"></i> Tambah KK Baru
                </a>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan KK per RT untuk RW --}}
    @if(($user->isAdminRw() || $user->hasRole('bendahara')) && count($rtSummary))
        <div class="row g-3 mb-4">
            @foreach($rtSummary as $item)
                <div class="col-6 col-md-3 col-xl-3">
                    <a href="{{ route('kartu-keluarga.index', ['rt_id' => $item->id]) }}" class="text-decoration-none">
                        <div class="saas-card p-3 text-center {{ request('rt_id') == $item->id ? 'border-primary' : '' }}" style="{{ request('rt_id') == $item->id ? 'background-color: #F0FDF4; border: 1.5px solid var(--primary-green) !important;' : '' }}">
                            <div class="text-secondary small mb-1 fw-semibold">RT {{ $item->kode_rt }}</div>
                            <div class="h4 text-dark fw-bold mb-0">{{ $item->kartu_keluargas_count }} <span class="small fs-6 text-muted fw-normal">KK</span></div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="saas-card p-3 mb-4">
        <form method="GET" action="{{ route('kartu-keluarga.index') }}" class="row g-2 align-items-end">
            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <div class="col-md-2">
                    <label class="form-label text-secondary small mb-1">Pilih Wilayah</label>
                    <select name="rt_id" class="form-select form-control-saas" onchange="this.form.submit()">
                        <option value="">Semua RT</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ ($user->isAdminRw() || $user->hasRole('bendahara')) ? 'col-md-4' : 'col-md-5' }}">
                <label class="form-label text-secondary small mb-1">Cari Data (Nama Kepala / Anggota / NIK / No. KK)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control form-control-saas border-start-0" placeholder="Ketik kata kunci..." value="{{ request('q') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label text-secondary small mb-1">Status Kependudukan</label>
                <select name="status_kependudukan" class="form-select form-control-saas">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status_kependudukan') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="pindah" {{ request('status_kependudukan') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                    <option value="meninggal" {{ request('status_kependudukan') === 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-secondary small mb-1">Kepemilikan Rumah</label>
                <select name="status_rumah" class="form-select form-control-saas">
                    <option value="">Semua Status</option>
                    <option value="tetap" {{ request('status_rumah') === 'tetap' ? 'selected' : '' }}>Rumah Tetap</option>
                    <option value="kontrak" {{ request('status_rumah') === 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                    <option value="kos" {{ request('status_rumah') === 'kos' ? 'selected' : '' }}>Kos</option>
                    <option value="lainnya" {{ request('status_rumah') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-emerald text-white w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['q', 'rt_id', 'status_kependudukan', 'status_rumah']))
                    <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fas fa-redo"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar KK Terpadu (Expandable) --}}
    <div class="saas-card overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th width="45" class="text-center">#</th>
                        <th>Nomor KK</th>
                        <th>Kepala Keluarga</th>
                        <th>Wilayah RT</th>
                        <th>Alamat & Hunian</th>
                        <th class="text-center">Anggota Keluarga</th>
                        <th class="text-end">Aksi Operasional</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kartuKeluargas as $idx => $kk)
                        <tr>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light border p-1" type="button" data-bs-toggle="collapse" data-bs-target="#anggotaKK{{ $kk->id }}" aria-expanded="false" title="Buka/Tutup Anggota Keluarga">
                                    <i class="fas fa-chevron-down text-secondary"></i>
                                </button>
                            </td>
                            <td>
                                <span class="fw-bold text-emerald font-monospace d-block">{{ $kk->no_kk }}</span>
                                <small class="text-secondary">{{ $kk->created_at ? $kk->created_at->format('d/m/Y') : '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-uppercase">{{ $kk->nama_kepala_keluarga }}</div>
                                <small class="text-secondary">
                                    <i class="fas fa-user-tie text-muted me-1"></i> Kepala Keluarga
                                </small>
                            </td>
                            <td>
                                <span class="badge-soft-info">
                                    RT {{ optional($kk->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="text-truncate text-dark small" title="{{ $kk->alamat }}">{{ $kk->alamat }}</div>
                                <span class="badge-soft-warning text-capitalize small mt-1 d-inline-block">
                                    Hunian {{ $kk->status_rumah }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $activeCount = $kk->anggota->where('status', 'aktif')->count();
                                    $totalCount = $kk->anggota->count();
                                @endphp
                                @if($totalCount === 0)
                                    <span class="badge bg-light text-warning border border-warning px-2 py-1 small">
                                        <i class="fas fa-exclamation-triangle me-1"></i> KK Kosong
                                    </span>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary px-3" type="button" data-bs-toggle="collapse" data-bs-target="#anggotaKK{{ $kk->id }}">
                                        <i class="fas fa-users me-1 text-emerald"></i> {{ $activeCount }} Jiwa
                                        @if($totalCount > $activeCount)
                                            <span class="text-muted small">({{ $totalCount }} total)</span>
                                        @endif
                                    </button>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                                        Kelola
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('kartu-keluarga.show', $kk) }}">
                                                <i class="fas fa-eye text-info me-2"></i> Lihat Detail Lengkap
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota{{ $kk->id }}">
                                                <i class="fas fa-user-plus text-emerald me-2"></i> Tambah Anggota
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('kartu-keluarga.edit', $kk) }}">
                                                <i class="fas fa-edit text-warning me-2"></i> Edit Data KK
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('kartu-keluarga.cetak', $kk) }}" target="_blank">
                                                <i class="fas fa-print text-primary me-2"></i> Cetak Dokumen KK
                                            </a>
                                        </li>
                                        @if($totalCount === 0 || $activeCount === 0)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('kartu-keluarga.destroy', $kk) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Kartu Keluarga kosong {{ $kk->no_kk }} ini? Tindakan ini tidak dapat dibatalkan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash-alt me-2"></i> Hapus KK Kosong
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        {{-- Expanded Collapsible Row: Anggota Keluarga --}}
                        <tr class="collapse" id="anggotaKK{{ $kk->id }}" style="background-color: #F8FAFC;">
                            <td colspan="7" class="p-3">
                                <div class="saas-card p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-emerald mb-0 fw-bold">
                                            <i class="fas fa-user-friends me-2"></i>Daftar Anggota Keluarga (KK No. {{ $kk->no_kk }})
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota{{ $kk->id }}">
                                            <i class="fas fa-plus me-1"></i> Tambah Anggota
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr class="small text-secondary">
                                                    <th>No</th>
                                                    <th>Nama Lengkap</th>
                                                    <th>NIK</th>
                                                    <th>Hubungan</th>
                                                    <th>Gender</th>
                                                    <th>Tgl Lahir / Usia</th>
                                                    <th>Pekerjaan</th>
                                                    <th>No. HP</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Aksi Anggota</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kk->anggota as $idxAnggota => $anggota)
                                                    <tr class="small">
                                                        <td class="text-center">{{ $idxAnggota + 1 }}</td>
                                                        <td class="fw-bold text-dark">{{ $anggota->nama_lengkap }}</td>
                                                        <td class="font-monospace text-emerald fw-semibold">{{ $anggota->nik }}</td>
                                                        <td>
                                                            <span class="badge {{ $anggota->status_hubungan_keluarga === 'Kepala Keluarga' ? 'bg-primary' : ($anggota->status_hubungan_keluarga === 'Istri' ? 'bg-success' : 'bg-secondary') }}">
                                                                {{ $anggota->status_hubungan_keluarga }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $anggota->jenis_kelamin === 'L' ? 'bg-info text-dark' : 'bg-danger' }}">
                                                                {{ $anggota->jenis_kelamin === 'L' ? 'L' : 'P' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-secondary">
                                                            {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('d/m/Y') : '-' }}
                                                            @if($anggota->tanggal_lahir)
                                                                <small class="text-muted">({{ $anggota->tanggal_lahir->age }} th)</small>
                                                            @endif
                                                        </td>
                                                        <td class="text-secondary">{{ $anggota->pekerjaan ?: '-' }}</td>
                                                        <td class="text-secondary">{{ $anggota->no_hp ?: '-' }}</td>
                                                        <td>
                                                            @if($anggota->status === 'aktif')
                                                                <span class="badge-soft-emerald">Aktif</span>
                                                            @elseif($anggota->status === 'pindah')
                                                                <span class="badge-soft-warning">Pindah</span>
                                                            @else
                                                                <span class="badge-soft-danger">Meninggal</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditAnggota{{ $anggota->id }}" title="Edit Anggota">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalPindahKK{{ $anggota->id }}" title="Pindahkan ke KK Lain">
                                                                    <i class="fas fa-exchange-alt"></i>
                                                                </button>
                                                                <form method="POST" action="{{ route('kartu-keluarga.anggota.destroy', [$kk, $anggota]) }}" class="d-inline" data-confirm="Keluarkan anggota {{ $anggota->nama_lengkap }} dari KK ini?" data-confirm-title="Keluarkan Anggota Keluarga" data-confirm-btn="Ya, Keluarkan">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Keluarkan dari KK">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="10" class="text-center py-3 text-secondary">Belum ada anggota keluarga terdaftar pada KK ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="fas fa-id-card fa-3x mb-3 text-muted d-block opacity-50"></i>
                                Tidak ditemukan data Kartu Keluarga yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kartuKeluargas->hasPages())
            <div class="p-3 border-top">
                {{ $kartuKeluargas->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ==================== MODALS UNTUK SETIAP KK DAN ANGGOTA ==================== --}}
@foreach($kartuKeluargas as $kk)
    {{-- MODAL TAMBAH ANGGOTA KE KK --}}
    <div class="modal fade" id="modalTambahAnggota{{ $kk->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; max-height: 85vh;">
                <div class="modal-header border-bottom bg-white sticky-top">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fas fa-user-plus text-emerald me-2"></i>Tambah Anggota ke KK {{ $kk->no_kk }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('kartu-keluarga.anggota.store', $kk) }}">
                    @csrf
                    <div class="modal-body overflow-y-auto p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Nama Lengkap *</label>
                                <input type="text" name="nama_lengkap" class="form-control form-control-saas" required placeholder="Sesuai Akta / KTP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">NIK (16 Digit) *</label>
                                <input type="text" name="nik" maxlength="16" minlength="16" class="form-control form-control-saas font-monospace" required placeholder="Contoh: 3275010101900001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary small fw-medium">Hubungan Keluarga *</label>
                                <select name="status_hubungan_keluarga" class="form-select form-control-saas" required>
                                    <option value="Istri">Istri</option>
                                    <option value="Anak" selected>Anak</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Kepala Keluarga">Kepala Keluarga</option>
                                    <option value="Mertua">Mertua</option>
                                    <option value="Orang Tua">Orang Tua</option>
                                    <option value="Famili Lain">Famili Lain</option>
                                    <option value="Pembantu">Pembantu</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary small fw-medium">Jenis Kelamin *</label>
                                <select name="jenis_kelamin" class="form-select form-control-saas" required>
                                    <option value="L">Laki-laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary small fw-medium">No. HP / WhatsApp</label>
                                <input type="text" name="no_hp" class="form-control form-control-saas" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control form-control-saas" placeholder="Kota Kelahiran">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control form-control-saas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Agama</label>
                                <select name="agama" class="form-select form-control-saas">
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen Protestan">Kristen Protestan</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Pekerjaan</label>
                                <input type="text" name="pekerjaan" class="form-control form-control-saas" placeholder="Contoh: Karyawan Swasta, Pelajar, dll">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light sticky-bottom">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald text-white px-4">
                            <i class="fas fa-save me-1"></i> Simpan Anggota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODALS EDIT & PINDAH UNTUK ANGGOTA DI KK INI --}}
    @foreach($kk->anggota as $anggota)
        {{-- MODAL EDIT ANGGOTA --}}
        <div class="modal fade" id="modalEditAnggota{{ $anggota->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; max-height: 85vh;">
                    <div class="modal-header border-bottom bg-white sticky-top">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="fas fa-edit text-warning me-2"></i>Edit Data Anggota: {{ $anggota->nama_lengkap }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('kartu-keluarga.anggota.update', [$kk, $anggota]) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body overflow-y-auto p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-medium">Nama Lengkap *</label>
                                    <input type="text" name="nama_lengkap" value="{{ $anggota->nama_lengkap }}" class="form-control form-control-saas" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-medium">NIK (16 Digit) *</label>
                                    <input type="text" name="nik" value="{{ $anggota->nik }}" maxlength="16" minlength="16" class="form-control form-control-saas font-monospace" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-secondary small fw-medium">Hubungan Keluarga *</label>
                                    <select name="status_hubungan_keluarga" class="form-select form-control-saas" required>
                                        @foreach(['Kepala Keluarga','Istri','Suami','Anak','Mertua','Orang Tua','Famili Lain','Pembantu','Lainnya'] as $hub)
                                            <option value="{{ $hub }}" {{ $anggota->status_hubungan_keluarga === $hub ? 'selected' : '' }}>{{ $hub }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-secondary small fw-medium">Jenis Kelamin *</label>
                                    <select name="jenis_kelamin" class="form-select form-control-saas" required>
                                        <option value="L" {{ $anggota->jenis_kelamin === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                        <option value="P" {{ $anggota->jenis_kelamin === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-secondary small fw-medium">Status Kependudukan *</label>
                                    <select name="status" class="form-select form-control-saas" required>
                                        <option value="aktif" {{ $anggota->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="pindah" {{ $anggota->status === 'pindah' ? 'selected' : '' }}>Pindah Wilayah</option>
                                        <option value="meninggal" {{ $anggota->status === 'meninggal' ? 'selected' : '' }}>Meninggal Dunia</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-medium">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" value="{{ $anggota->tempat_lahir }}" class="form-control form-control-saas">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-medium">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" value="{{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('Y-m-d') : '' }}" class="form-control form-control-saas">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-medium">Pekerjaan</label>
                                    <input type="text" name="pekerjaan" value="{{ $anggota->pekerjaan }}" class="form-control form-control-saas">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-medium">No. HP / WhatsApp</label>
                                    <input type="text" name="no_hp" value="{{ $anggota->no_hp }}" class="form-control form-control-saas">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light sticky-bottom">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning text-dark px-4 fw-bold">
                                <i class="fas fa-save me-1"></i> Perbarui Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL PINDAH KK --}}
        <div class="modal fade" id="modalPindahKK{{ $anggota->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom bg-white">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="fas fa-exchange-alt text-info me-2"></i>Pindahkan Anggota ke KK Lain
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('kartu-keluarga.anggota.pindah', [$kk, $anggota]) }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="p-3 rounded-3 mb-3 bg-light border">
                                <small class="text-secondary d-block">Anggota yang Dipindahkan:</small>
                                <strong class="text-dark fs-6">{{ $anggota->nama_lengkap }} (NIK: {{ $anggota->nik }})</strong>
                                <div class="small text-secondary mt-1">KK Asal: {{ $kk->no_kk }} (Kepala: {{ $kk->nama_kepala_keluarga }})</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-medium">Pilih KK Tujuan di RT {{ optional($kk->rt)->kode_rt }} *</label>
                                <select name="target_kartu_keluarga_id" class="form-select form-control-saas" required>
                                    <option value="">-- Pilih KK Tujuan --</option>
                                    @foreach($allKkRt as $other)
                                        @if($other->id !== $kk->id)
                                            <option value="{{ $other->id }}">KK {{ $other->no_kk }} - {{ $other->alamat }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-medium">Hubungan Keluarga di KK Tujuan *</label>
                                <select name="status_hubungan_keluarga" class="form-select form-control-saas" required>
                                    <option value="Istri">Istri</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Famili Lain">Famili Lain</option>
                                    <option value="Kepala Keluarga">Kepala Keluarga</option>
                                    <option value="Mertua">Mertua</option>
                                    <option value="Orang Tua">Orang Tua</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-medium">Alasan Kepindahan</label>
                                <textarea name="alasan_pindah" rows="2" class="form-control form-control-saas" placeholder="Contoh: Menikah, berpindah alamat ke keluarga lain, dll..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-0">
                                <i class="fas fa-info-circle me-1"></i> Perpindahan ini akan dicatat ke dalam <strong>Riwayat Mutasi Warga</strong> secara otomatis.
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-info text-white px-4">
                                <i class="fas fa-exchange-alt me-1"></i> Pindahkan Warga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endforeach

@endsection
