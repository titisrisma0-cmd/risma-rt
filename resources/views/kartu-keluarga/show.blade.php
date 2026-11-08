@extends('layouts.app')

@section('title', 'Detail Kartu Keluarga ' . $kartuKeluarga->no_kk)

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('kartu-keluarga.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
                <h1 class="h3 text-white mb-0">Kartu Keluarga: {{ $kartuKeluarga->no_kk }}</h1>
            </div>
            <p class="text-white-50 mb-0">Wilayah RT {{ optional($kartuKeluarga->rt)->kode_rt ?? '-' }} / RW 01</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('kartu-keluarga.cetak', $kartuKeluarga) }}" target="_blank" class="btn btn-outline-info px-3 py-2">
                <i class="fas fa-print me-1"></i> Cetak Dokumen KK
            </a>
            @if($user->isAdminRt() || $user->isAdminRw())
                <button type="button" class="btn btn-emerald text-white px-3 py-2" data-bs-toggle="modal" data-bs-target="#tambahAnggotaModal" style="background: linear-gradient(135deg, #059669, #10b981); border: none; border-radius: 8px;">
                    <i class="fas fa-user-plus me-1"></i> Tambah Anggota
                </button>
                <a href="{{ route('kartu-keluarga.edit', $kartuKeluarga) }}" class="btn btn-warning text-dark px-3 py-2" style="border-radius: 8px;">
                    <i class="fas fa-edit me-1"></i> Edit KK
                </a>
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

    {{-- Info Card KK --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="text-white-50 small mb-1">Kepala Keluarga</div>
                <div class="h5 text-white fw-bold mb-1 text-uppercase">{{ $kartuKeluarga->nama_kepala_keluarga }}</div>
                <div class="small text-emerald-400"><i class="fas fa-user-tie me-1"></i> Penanggung Jawab</div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="text-white-50 small mb-1">Wilayah RT / RW</div>
                <div class="h5 text-white fw-bold mb-1">RT {{ optional($kartuKeluarga->rt)->kode_rt ?? '-' }} / RW 01</div>
                <div class="small text-blue-400"><i class="fas fa-map-marker-alt me-1"></i> Risma Smart RW</div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="text-white-50 small mb-1">Status Kepemilikan Rumah</div>
                <div class="h5 text-white fw-bold mb-1 text-capitalize">{{ $kartuKeluarga->status_rumah }}</div>
                <div class="small text-amber-400"><i class="fas fa-home me-1"></i> Hunian {{ $kartuKeluarga->status_rumah }}</div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="text-white-50 small mb-1">Total Anggota Terdaftar</div>
                <div class="h5 text-white fw-bold mb-1">{{ $kartuKeluarga->anggota->count() }} Orang</div>
                <div class="small text-cyan-400"><i class="fas fa-users me-1"></i> Warga Aktif</div>
            </div>
        </div>
    </div>

    {{-- Alamat Lengkap --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-map-marked-alt text-emerald-400 fa-lg"></i>
            <div>
                <span class="text-white-50 small d-block">Alamat Lengkap KK:</span>
                <span class="text-white fw-semibold">{{ $kartuKeluarga->alamat }}</span>
            </div>
        </div>
    </div>

    {{-- Daftar Anggota Keluarga --}}
    <div class="card border-0 mb-4" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
            <h5 class="text-white mb-0"><i class="fas fa-users text-emerald-400 me-2"></i>Daftar Anggota Keluarga</h5>
            <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1">
                {{ $kartuKeluarga->anggota->count() }} Anggota Terdaftar
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">No</th>
                        <th class="py-3 px-3">Nama Lengkap</th>
                        <th class="py-3 px-3">NIK</th>
                        <th class="py-3 px-3">Hubungan Keluarga</th>
                        <th class="py-3 px-3">Jenis Kelamin</th>
                        <th class="py-3 px-3">Tgl Lahir / Usia</th>
                        <th class="py-3 px-3">Pekerjaan</th>
                        <th class="py-3 px-3">No. HP</th>
                        <th class="py-3 px-3">Status</th>
                        @if($user->isAdminRt() || $user->isAdminRw())
                            <th class="py-3 px-3 text-end">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($kartuKeluarga->anggota as $idx => $anggota)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 text-white-50">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-white">{{ $anggota->nama_lengkap }}</div>
                                @if($anggota->user)
                                    <small class="text-emerald-400"><i class="fas fa-check-circle me-1"></i>Akun Login Terdaftar</small>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-monospace text-emerald-400">
                                {{ $anggota->nik ?? '-' }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge {{ $anggota->status_hubungan_keluarga === 'Kepala Keluarga' ? 'bg-primary' : ($anggota->status_hubungan_keluarga === 'Istri' ? 'bg-success' : 'bg-secondary') }}">
                                    {{ $anggota->status_hubungan_keluarga ?? 'Anggota' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge {{ $anggota->jenis_kelamin === 'L' ? 'bg-info text-dark' : 'bg-danger' }}">
                                    {{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-white-50 small">
                                @if($anggota->tanggal_lahir)
                                    {{ $anggota->tanggal_lahir->format('d/m/Y') }}
                                    <span class="d-block text-white-50">({{ $anggota->tanggal_lahir->age }} thn)</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-3 text-white-50 small">{{ $anggota->pekerjaan ?? '-' }}</td>
                            <td class="py-3 px-3 text-white-50 small">{{ $anggota->no_hp ?? '-' }}</td>
                            <td class="py-3 px-3">
                                @if($anggota->status === 'aktif')
                                    <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Aktif</span>
                                @elseif($anggota->status === 'pindah')
                                    <span class="badge bg-amber-500/20 text-amber-300 border border-amber-500/30">Pindah</span>
                                @else
                                    <span class="badge bg-rose-500/20 text-rose-300 border border-rose-500/30">Meninggal</span>
                                @endif
                            </td>
                            @if($user->isAdminRt() || $user->isAdminRw())
                                <td class="py-3 px-3 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditAnggota{{ $anggota->id }}" title="Edit Anggota">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalPindahKK{{ $anggota->id }}" title="Pindahkan ke KK Lain">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <form method="POST" action="{{ route('kartu-keluarga.anggota.destroy', [$kartuKeluarga, $anggota]) }}" class="d-inline" onsubmit="return confirm('Keluarkan anggota {{ addslashes($anggota->nama_lengkap) }} dari KK ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus dari KK">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($user->isAdminRt() || $user->isAdminRw()) ? 10 : 9 }}" class="text-center py-4 text-white-50">
                                Belum ada anggota keluarga yang terdaftar dalam KK ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($user->isAdminRt() || $user->isAdminRw())
    {{-- Modal Tambah Anggota --}}
    <div class="modal fade" id="tambahAnggotaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-height: 85vh;">
                <div class="modal-header border-bottom border-white-10 sticky-top" style="background: #1e293b; z-index: 10;">
                    <h5 class="modal-title"><i class="fas fa-user-plus text-emerald-400 me-2"></i>Tambah Anggota Keluarga</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('kartu-keluarga.anggota.store', $kartuKeluarga) }}">
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

    {{-- MODALS EDIT & PINDAH UNTUK ANGGOTA --}}
    @foreach($kartuKeluarga->anggota as $anggota)
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
                    <form method="POST" action="{{ route('kartu-keluarga.anggota.update', [$kartuKeluarga, $anggota]) }}">
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
                    <form method="POST" action="{{ route('kartu-keluarga.anggota.pindah', [$kartuKeluarga, $anggota]) }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="p-3 rounded mb-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.08);">
                                <small class="text-white-50 d-block">Anggota yang Dipindahkan:</small>
                                <strong class="text-white fs-6">{{ $anggota->nama_lengkap }} (NIK: {{ $anggota->nik }})</strong>
                                <div class="small text-white-50 mt-1">KK Asal: {{ $kartuKeluarga->no_kk }} (Kepala: {{ $kartuKeluarga->nama_kepala_keluarga }})</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Pilih KK Tujuan di RT {{ optional($kartuKeluarga->rt)->kode_rt }} *</label>
                                <select name="target_kartu_keluarga_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                    <option value="">-- Pilih KK Tujuan --</option>
                                    @foreach($otherKk as $other)
                                        <option value="{{ $other->id }}">KK {{ $other->no_kk }} - {{ $other->alamat }}</option>
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
                                <textarea name="alasan_pindah" rows="2" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Contoh: Menikah, pindah alamat ke keluarga lain, dll..."></textarea>
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
@endif

@endsection
