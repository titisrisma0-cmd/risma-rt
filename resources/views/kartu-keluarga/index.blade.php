@extends('layouts.app')

@section('title', 'Data Keluarga & Warga')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1">
                <i class="fas fa-users-rectangle text-emerald-400 me-2"></i>
                @if($user->isAdminRt())
                    Data Keluarga & Warga — RT {{ $user->getRtKode() ?? '01' }}
                @else
                    Data Keluarga & Warga — Seluruh Wilayah RW
                @endif
            </h1>
            <p class="text-white-50 mb-0">Pusat administrasi kependudukan terpadu berbasis Kartu Keluarga dan Anggota Keluarga</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('warga.mutasi') }}" class="btn btn-outline-warning px-3 py-2">
                <i class="fas fa-exchange-alt me-1"></i> Mutasi Warga
            </a>
            @if($user->isAdminRt() || $user->isAdminRw())
                <a href="{{ route('kartu-keluarga.create') }}" class="btn btn-emerald px-3 py-2 text-white" style="background: linear-gradient(135deg, #059669, #10b981); border: none; border-radius: 8px;">
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
                        <div class="card p-3 text-center border-0" style="background: {{ request('rt_id') == $item->id ? 'rgba(16, 185, 129, 0.25)' : 'rgba(30, 41, 59, 0.7)' }}; backdrop-filter: blur(10px); border: 1px solid {{ request('rt_id') == $item->id ? '#10b981' : 'rgba(255,255,255,0.08)' }} !important; border-radius: 12px; transition: all 0.2s;">
                            <div class="text-white-50 small mb-1">RT {{ $item->kode_rt }}</div>
                            <div class="h4 text-white fw-bold mb-0">{{ $item->kartu_keluargas_count }} <span class="small fs-6 text-white-50">KK</span></div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="GET" action="{{ route('kartu-keluarga.index') }}" class="row g-2 align-items-end">
            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <div class="col-md-2">
                    <label class="form-label text-white-50 small mb-1">Pilih Wilayah</label>
                    <select name="rt_id" class="form-select text-white" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                        <option value="">Semua RT</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ ($user->isAdminRw() || $user->hasRole('bendahara')) ? 'col-md-4' : 'col-md-5' }}">
                <label class="form-label text-white-50 small mb-1">Cari Data (Nama Kepala / Anggota / NIK / No. KK)</label>
                <div class="input-group">
                    <span class="input-group-text text-white-50" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.15); border-right: none;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control text-white" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.15); border-left: none;" placeholder="Ketik kata kunci..." value="{{ request('q') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Status Kependudukan</label>
                <select name="status_kependudukan" class="form-select text-white" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status_kependudukan') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="pindah" {{ request('status_kependudukan') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                    <option value="meninggal" {{ request('status_kependudukan') === 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Kepemilikan Rumah</label>
                <select name="status_rumah" class="form-select text-white" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <option value="">Semua Status</option>
                    <option value="tetap" {{ request('status_rumah') === 'tetap' ? 'selected' : '' }}>Rumah Tetap</option>
                    <option value="kontrak" {{ request('status_rumah') === 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                    <option value="kos" {{ request('status_rumah') === 'kos' ? 'selected' : '' }}>Kos</option>
                    <option value="lainnya" {{ request('status_rumah') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-emerald text-white w-100" style="background: #10b981; border: none; border-radius: 8px;">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['q', 'rt_id', 'status_kependudukan', 'status_rumah']))
                    <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-outline-light" title="Reset filter"><i class="fas fa-redo"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar KK Terpadu (Expandable) --}}
    <div class="card border-0 mb-4" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.85); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th width="40" class="py-3 px-3 text-center">#</th>
                        <th class="py-3 px-3">Nomor KK</th>
                        <th class="py-3 px-3">Kepala Keluarga</th>
                        <th class="py-3 px-3">Wilayah RT</th>
                        <th class="py-3 px-3">Alamat & Hunian</th>
                        <th class="py-3 px-3 text-center">Anggota Keluarga</th>
                        <th class="py-3 px-3 text-end">Aksi Operasional</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kartuKeluargas as $idx => $kk)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 text-center">
                                <button class="btn btn-sm btn-outline-light p-1" type="button" data-bs-toggle="collapse" data-bs-target="#anggotaKK{{ $kk->id }}" aria-expanded="false" title="Buka/Tutup Anggota Keluarga">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </td>
                            <td class="py-3 px-3">
                                <span class="fw-bold text-emerald-400 font-monospace d-block">{{ $kk->no_kk }}</span>
                                <small class="text-white-50">{{ $kk->created_at ? $kk->created_at->format('d/m/Y') : '-' }}</small>
                            </td>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-white text-uppercase">{{ $kk->nama_kepala_keluarga }}</div>
                                <small class="text-white-50">
                                    <i class="fas fa-user-tie text-info me-1"></i> Kepala Keluarga
                                </small>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    RT {{ optional($kk->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3" style="max-width: 250px;">
                                <div class="text-truncate text-white small" title="{{ $kk->alamat }}">{{ $kk->alamat }}</div>
                                <span class="badge bg-amber-500/20 text-amber-300 border border-amber-500/30 text-capitalize small mt-1">
                                    Hunian {{ $kk->status_rumah }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <button class="btn btn-sm btn-outline-info px-3" type="button" data-bs-toggle="collapse" data-bs-target="#anggotaKK{{ $kk->id }}">
                                    <i class="fas fa-users me-1"></i> {{ $kk->anggota->count() }} Jiwa
                                </button>
                            </td>
                            <td class="py-3 px-3 text-end">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-outline-light dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                                        Kelola
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15);">
                                        <li>
                                            <a class="dropdown-item text-white" href="{{ route('kartu-keluarga.show', $kk) }}">
                                                <i class="fas fa-eye text-info me-2"></i> Lihat Detail Lengkap
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-white" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota{{ $kk->id }}">
                                                <i class="fas fa-user-plus text-emerald-400 me-2"></i> Tambah Anggota
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-white" href="{{ route('kartu-keluarga.edit', $kk) }}">
                                                <i class="fas fa-edit text-warning me-2"></i> Edit Data KK
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-white" href="{{ route('kartu-keluarga.cetak', $kk) }}" target="_blank">
                                                <i class="fas fa-print text-primary me-2"></i> Cetak Dokumen KK
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        {{-- Expanded Collapsible Row: Anggota Keluarga --}}
                        <tr class="collapse" id="anggotaKK{{ $kk->id }}" style="background: rgba(15, 23, 42, 0.4);">
                            <td colspan="7" class="p-3">
                                <div class="p-3 rounded-3" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-emerald-400 mb-0 fw-bold">
                                            <i class="fas fa-user-friends me-2"></i>Daftar Anggota Keluarga (KK No. {{ $kk->no_kk }})
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota{{ $kk->id }}" style="background: #10b981; border: none;">
                                            <i class="fas fa-plus me-1"></i> Tambah Anggota
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered text-white align-middle mb-0" style="border-color: rgba(255,255,255,0.1);">
                                            <thead style="background: rgba(30, 41, 59, 0.9);">
                                                <tr class="small text-white-50">
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
                                                        <td class="fw-bold">{{ $anggota->nama_lengkap }}</td>
                                                        <td class="font-monospace text-emerald-400">{{ $anggota->nik }}</td>
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
                                                        <td>
                                                            {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('d/m/Y') : '-' }}
                                                            @if($anggota->tanggal_lahir)
                                                                <small class="text-white-50">({{ $anggota->tanggal_lahir->age }} th)</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $anggota->pekerjaan ?: '-' }}</td>
                                                        <td>{{ $anggota->no_hp ?: '-' }}</td>
                                                        <td>
                                                            @if($anggota->status === 'aktif')
                                                                <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Aktif</span>
                                                            @elseif($anggota->status === 'pindah')
                                                                <span class="badge bg-amber-500/20 text-amber-300 border border-amber-500/30">Pindah</span>
                                                            @else
                                                                <span class="badge bg-rose-500/20 text-rose-300 border border-rose-500/30">Meninggal</span>
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
                                                                <form method="POST" action="{{ route('kartu-keluarga.anggota.destroy', [$kk, $anggota]) }}" class="d-inline" onsubmit="return confirm('Keluarkan anggota {{ addslashes($anggota->nama_lengkap) }} dari KK ini?')">
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
                                                        <td colspan="10" class="text-center py-2 text-white-50">Belum ada anggota keluarga terdaftar pada KK ini.</td>
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
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="fas fa-id-card fa-3x mb-3 text-secondary d-block"></i>
                                Tidak ditemukan data Kartu Keluarga yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kartuKeluargas->hasPages())
            <div class="p-3 border-top border-white-10">
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
            <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-height: 85vh;">
                <div class="modal-header border-bottom border-white-10 sticky-top" style="background: #1e293b; z-index: 10;">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus text-emerald-400 me-2"></i>Tambah Anggota ke KK {{ $kk->no_kk }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('kartu-keluarga.anggota.store', $kk) }}">
                    @csrf
                    <div class="modal-body overflow-y-auto p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Nama Lengkap *</label>
                                <input type="text" name="nama_lengkap" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required placeholder="Sesuai Akta / KTP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">NIK (16 Digit) *</label>
                                <input type="text" name="nik" maxlength="16" minlength="16" class="form-control text-white font-monospace" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required placeholder="Contoh: 3275010101900001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">Hubungan Keluarga *</label>
                                <select name="status_hubungan_keluarga" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
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
                                <label class="form-label text-white-50 small">Jenis Kelamin *</label>
                                <select name="jenis_kelamin" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                    <option value="L">Laki-laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">No. HP / WhatsApp</label>
                                <input type="text" name="no_hp" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Kota Kelahiran">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Agama</label>
                                <select name="agama" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
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
                                <label class="form-label text-white-50 small">Pekerjaan</label>
                                <input type="text" name="pekerjaan" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Contoh: Karyawan Swasta, Pelajar, dll">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white-10 sticky-bottom" style="background: #1e293b; z-index: 10;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald text-white px-4" style="background: #10b981; border: none;">
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
                <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-height: 85vh;">
                    <div class="modal-header border-bottom border-white-10 sticky-top" style="background: #1e293b; z-index: 10;">
                        <h5 class="modal-title">
                            <i class="fas fa-edit text-warning me-2"></i>Edit Data Anggota: {{ $anggota->nama_lengkap }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('kartu-keluarga.anggota.update', [$kk, $anggota]) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body overflow-y-auto p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Nama Lengkap *</label>
                                    <input type="text" name="nama_lengkap" value="{{ $anggota->nama_lengkap }}" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">NIK (16 Digit) *</label>
                                    <input type="text" name="nik" value="{{ $anggota->nik }}" maxlength="16" minlength="16" class="form-control text-white font-monospace" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-white-50 small">Hubungan Keluarga *</label>
                                    <select name="status_hubungan_keluarga" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                        @foreach(['Kepala Keluarga','Istri','Suami','Anak','Mertua','Orang Tua','Famili Lain','Pembantu','Lainnya'] as $hub)
                                            <option value="{{ $hub }}" {{ $anggota->status_hubungan_keluarga === $hub ? 'selected' : '' }}>{{ $hub }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-white-50 small">Jenis Kelamin *</label>
                                    <select name="jenis_kelamin" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                        <option value="L" {{ $anggota->jenis_kelamin === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                        <option value="P" {{ $anggota->jenis_kelamin === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-white-50 small">Status Kependudukan *</label>
                                    <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                        <option value="aktif" {{ $anggota->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="pindah" {{ $anggota->status === 'pindah' ? 'selected' : '' }}>Pindah Wilayah</option>
                                        <option value="meninggal" {{ $anggota->status === 'meninggal' ? 'selected' : '' }}>Meninggal Dunia</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" value="{{ $anggota->tempat_lahir }}" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" value="{{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('Y-m-d') : '' }}" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">Pekerjaan</label>
                                    <input type="text" name="pekerjaan" value="{{ $anggota->pekerjaan }}" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50 small">No. HP / WhatsApp</label>
                                    <input type="text" name="no_hp" value="{{ $anggota->no_hp }}" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-white-10 sticky-bottom" style="background: #1e293b; z-index: 10;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
                <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(59,130,246,0.3); border-radius: 12px;">
                    <div class="modal-header border-bottom border-white-10">
                        <h5 class="modal-title text-info">
                            <i class="fas fa-exchange-alt me-2"></i>Pindahkan Anggota ke KK Lain
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('kartu-keluarga.anggota.pindah', [$kk, $anggota]) }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="p-3 rounded mb-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.08);">
                                <small class="text-white-50 d-block">Anggota yang Dipindahkan:</small>
                                <strong class="text-white fs-6">{{ $anggota->nama_lengkap }} (NIK: {{ $anggota->nik }})</strong>
                                <div class="small text-white-50 mt-1">KK Asal: {{ $kk->no_kk }} (Kepala: {{ $kk->nama_kepala_keluarga }})</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Pilih KK Tujuan di RT {{ optional($kk->rt)->kode_rt }} *</label>
                                <select name="target_kartu_keluarga_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                    <option value="">-- Pilih KK Tujuan --</option>
                                    @foreach($allKkRt as $other)
                                        @if($other->id !== $kk->id)
                                            <option value="{{ $other->id }}">KK {{ $other->no_kk }} - {{ $other->alamat }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Hubungan Keluarga di KK Tujuan *</label>
                                <select name="status_hubungan_keluarga" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
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
                                <label class="form-label text-white-50 small">Alasan Kepindahan</label>
                                <textarea name="alasan_pindah" rows="2" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Contoh: Menikah, berpindah alamat ke keluarga lain, dll..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-0" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); color: #7dd3fc;">
                                <i class="fas fa-info-circle me-1"></i> Perpindahan ini akan dicatat ke dalam <strong>Riwayat Mutasi Warga</strong> secara otomatis.
                            </div>
                        </div>
                        <div class="modal-footer border-top border-white-10">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-info px-4">
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
