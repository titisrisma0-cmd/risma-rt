@extends('layouts.app')

@section('title', 'Manajemen RT — Pengurus RW')

@section('content')
<div class="py-2">
    {{-- Page Header --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Wilayah & Manajemen RT</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Manajemen Wilayah RT (RT 01 – RT 08)
            </h1>
            <p class="text-secondary mb-0">Pengelolaan wilayah RT, akun pengurus / Ketua RT, dan pemantauan kependudukan menyeluruh.</p>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn btn-emerald" data-bs-toggle="modal" data-bs-target="#modalTambahRt">
                <i class="fas fa-plus me-1"></i> Tambah Wilayah RT
            </button>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-xs" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-lg me-2 text-danger"></i>
                <div>
                    <strong class="d-block">Periksa kembali data Anda:</strong>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan Wilayah RW (Donezo Metric Cards) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Total RT Terdaftar</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-sitemap"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ $rts->count() }} <small class="text-secondary fw-normal fs-6">RT</small></div>
                    <div class="stat-subtext text-success"><i class="fas fa-check-circle"></i> Wilayah RW 01</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Akun Ketua RT Aktif</span>
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">
                        {{ $rts->filter(fn($r) => optional($r->ketua)->profile && $r->ketua->profile->status === 'aktif')->count() }} / {{ $rts->count() }}
                    </div>
                    <div class="stat-subtext text-secondary"><i class="fas fa-shield-alt text-primary"></i> Pengurus aktif</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Total Kepala Keluarga</span>
                    <div class="stat-icon-wrapper stat-icon-amber">
                        <i class="fas fa-house-user"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($rts->sum('keluarga_count')) }} <small class="text-secondary fw-normal fs-6">KK</small></div>
                    <div class="stat-subtext text-secondary"><i class="fas fa-id-card"></i> Terdaftar di database</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-saas">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="stat-label">Total Warga Terdata</span>
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($rts->sum('warga_profiles_count')) }} <small class="text-secondary fw-normal fs-6">Jiwa</small></div>
                    <div class="stat-subtext text-secondary"><i class="fas fa-database"></i> Penduduk se-RW</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Wilayah RT --}}
    <div class="saas-card mb-4">
        <div class="card-header-saas">
            <div>
                <h5 class="card-title-saas">
                    <i class="fas fa-list text-success"></i> Daftar Wilayah RT 01 – RT 08
                </h5>
                <small class="text-secondary">Kelola data ketua RT, akun login, dan status aktif wilayah</small>
            </div>
            <span class="badge badge-soft-primary px-3 py-1">RW {{ $rws->first()->kode_rw ?? '01' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table-saas table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 10%;">Kode RT</th>
                        <th style="width: 18%;">Ketua RT</th>
                        <th style="width: 14%;">Kontak HP</th>
                        <th style="width: 18%;">Email Login</th>
                        <th class="text-center" style="width: 9%;">Jumlah KK</th>
                        <th class="text-center" style="width: 9%;">Jumlah Warga</th>
                        <th class="text-center" style="width: 10%;">Status Akun</th>
                        <th class="text-end" style="width: 12%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rts as $rt)
                        @php
                            $ketua = $rt->ketua;
                            $profile = optional($ketua)->profile;
                            $status = $profile->status ?? 'aktif';
                        @endphp
                        <tr>
                            <td>
                                <span class="badge badge-soft-primary fw-bold" style="font-size: 0.85rem;">
                                    RT {{ $rt->kode_rt }}
                                </span>
                                @if($rt->nama_rt && $rt->nama_rt !== 'RT ' . $rt->kode_rt)
                                    <div class="small text-secondary mt-1">{{ $rt->nama_rt }}</div>
                                @endif
                            </td>
                            <td>
                                @if($ketua)
                                    <div class="fw-bold text-dark">{{ $ketua->name }}</div>
                                    <small class="text-secondary">Role: {{ $ketua->role->nama_role ?? 'rt' }}</small>
                                @else
                                    <span class="text-secondary small fst-italic">Belum Ada Akun</span>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-soft-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalCreateKetua{{ $rt->id }}" style="font-size: 0.72rem;">
                                            <i class="fas fa-plus me-1"></i> Buat Akun
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(optional($profile)->no_hp)
                                    <span class="text-dark small font-monospace"><i class="fab fa-whatsapp text-success me-1"></i> {{ $profile->no_hp }}</span>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($ketua)
                                    <span class="text-secondary font-monospace small">{{ $ketua->email }}</span>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-info font-monospace">{{ $rt->keluarga_count }} KK</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary font-monospace">{{ $rt->warga_profiles_count }} Jiwa</span>
                            </td>
                            <td class="text-center">
                                @if($ketua)
                                    <span class="badge {{ $status === 'aktif' ? 'badge-soft-success' : 'badge-soft-danger' }} px-2 py-1">
                                        <i class="fas {{ $status === 'aktif' ? 'fa-check' : 'fa-ban' }} me-1"></i> {{ ucfirst($status) }}
                                    </span>
                                @else
                                    <span class="badge badge-soft-warning px-2 py-1">Kosong</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="dropdown d-inline">
                                    <a href="{{ route('rts.show', $rt) }}" class="btn btn-sm btn-light text-primary me-1" title="Lihat Detail RT">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 12px;">
                                        <li>
                                            <button type="button" class="dropdown-item rounded-2 py-1.5" data-bs-toggle="modal" data-bs-target="#modalEditRt{{ $rt->id }}">
                                                <i class="fas fa-edit me-2 text-warning"></i> Edit Profil RT
                                            </button>
                                        </li>
                                        @if($ketua)
                                            <li>
                                                <button type="button" class="dropdown-item rounded-2 py-1.5" data-bs-toggle="modal" data-bs-target="#modalEditKetua{{ $rt->id }}">
                                                    <i class="fas fa-user-cog me-2 text-primary"></i> Kelola Akun Ketua
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item rounded-2 py-1.5" data-bs-toggle="modal" data-bs-target="#modalResetPass{{ $rt->id }}">
                                                    <i class="fas fa-key me-2 text-warning"></i> Reset Password
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form method="POST" action="{{ route('rts.ketua.toggle-status', [$rt, $ketua]) }}" onsubmit="return confirm('Ubah status akun Ketua RT {{ $rt->kode_rt }}?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item rounded-2 py-1.5 {{ $status === 'aktif' ? 'text-danger' : 'text-success' }}">
                                                        <i class="fas {{ $status === 'aktif' ? 'fa-user-slash' : 'fa-user-check' }} me-2"></i>
                                                        {{ $status === 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                                                    </button>
                                                </form>
                                            </li>
                                        @else
                                            <li>
                                                <button type="button" class="dropdown-item rounded-2 py-1.5 text-success" data-bs-toggle="modal" data-bs-target="#modalCreateKetua{{ $rt->id }}">
                                                    <i class="fas fa-user-plus me-2"></i> Buat Akun Ketua
                                                </button>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">Belum ada wilayah RT yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ==================== MODALS ==================== --}}

{{-- Modal Tambah RT --}}
<div class="modal fade" id="modalTambahRt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('rts.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle text-success me-2"></i>Tambah Wilayah RT Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">RW Induk</label>
                        <select name="rw_id" class="form-select" required>
                            @foreach($rws as $rw)
                                <option value="{{ $rw->id }}">RW {{ $rw->kode_rw }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode RT (Contoh: 01)</label>
                        <input name="kode_rt" placeholder="01" class="form-control" required maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama RT (Opsional)</label>
                        <input name="nama_rt" placeholder="Contoh: RT 01" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald"><i class="fas fa-save me-1"></i> Simpan RT</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($rts as $rt)
    {{-- Modal Edit RT --}}
    <div class="modal fade" id="modalEditRt{{ $rt->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('rts.update', $rt) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit text-warning me-2"></i>Edit Wilayah RT {{ $rt->kode_rt }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">RW Induk</label>
                            <select name="rw_id" class="form-select" required>
                                @foreach($rws as $rw)
                                    <option value="{{ $rw->id }}" @selected($rt->rw_id === $rw->id)>RW {{ $rw->kode_rw }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode RT</label>
                            <input name="kode_rt" value="{{ $rt->kode_rt }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama RT</label>
                            <input name="nama_rt" value="{{ $rt->nama_rt }}" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald"><i class="fas fa-save me-1"></i> Perbarui RT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Buat Akun Ketua RT --}}
    <div class="modal fade" id="modalCreateKetua{{ $rt->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('rts.ketua.store', $rt) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus text-success me-2"></i>Buat Akun Ketua RT {{ $rt->kode_rt }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap Ketua RT *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Login *</label>
                            <input type="email" name="email" class="form-control" required placeholder="rt{{ $rt->kode_rt }}@rw-smart.test">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Awal *</label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="fas fa-info-circle me-1"></i> Akun akan otomatis terhubung ke wilayah RT {{ $rt->kode_rt }} dengan role <code>rt_{{ $rt->kode_rt }}</code>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald"><i class="fas fa-check me-1"></i> Buat Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($rt->ketua)
        {{-- Modal Edit Akun Ketua RT --}}
        <div class="modal fade" id="modalEditKetua{{ $rt->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('rts.ketua.update', [$rt, $rt->ketua]) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit text-primary me-2"></i>Edit Data Ketua RT {{ $rt->kode_rt }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap Ketua RT</label>
                                <input type="text" name="name" value="{{ $rt->ketua->name }}" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Login</label>
                                <input type="email" name="email" value="{{ $rt->ketua->email }}" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor WhatsApp / HP</label>
                                <input type="text" name="no_hp" value="{{ optional($rt->ketua->profile)->no_hp }}" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Reset Password --}}
        <div class="modal fade" id="modalResetPass{{ $rt->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('rts.ketua.reset-password', [$rt, $rt->ketua]) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-key text-warning me-2"></i>Reset Password Ketua RT {{ $rt->kode_rt }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-secondary">Masukkan password baru untuk akun <strong>{{ $rt->ketua->email }}</strong> ({{ $rt->ketua->name }}):</p>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fas fa-check me-1"></i> Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
