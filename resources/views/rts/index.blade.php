@extends('layouts.app')

@section('title', 'Manajemen RT — Pengurus RW')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1"><i class="fas fa-sitemap text-emerald-400 me-2"></i>Manajemen RT (RT 01 – RT 08)</h1>
            <p class="text-white-50 mb-0">Pengelolaan wilayah RT, akun pengurus/Ketua RT, dan pemantauan menyeluruh</p>
        </div>
        <div>
            <button type="button" class="btn btn-emerald text-white px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTambahRt" style="background: #10b981; border: none;">
                <i class="fas fa-plus-circle me-1"></i> Tambah Wilayah RT
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan Wilayah RW --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Total RT Terdaftar</span>
                <h3 class="text-white fw-bold mb-0">{{ $rts->count() }} RT</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Akun Ketua RT Aktif</span>
                <h3 class="text-emerald-400 fw-bold mb-0">
                    {{ $rts->filter(fn($r) => optional($r->ketua)->profile && $r->ketua->profile->status === 'aktif')->count() }} / {{ $rts->count() }}
                </h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Total Kepala Keluarga</span>
                <h3 class="text-info fw-bold mb-0">{{ $rts->sum('keluarga_count') }} KK</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <span class="text-white-50 small mb-1">Total Warga Terdata</span>
                <h3 class="text-warning fw-bold mb-0">{{ $rts->sum('warga_profiles_count') }} Jiwa</h3>
            </div>
        </div>
    </div>

    {{-- Tabel Wilayah RT --}}
    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
            <h5 class="text-white mb-0">
                <i class="fas fa-list text-emerald-400 me-2"></i>Daftar Wilayah RT 01 – RT 08
            </h5>
            <span class="badge bg-slate-700 text-white px-3 py-1">RW {{ $rws->first()->kode_rw ?? '01' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr class="text-secondary small">
                        <th class="py-3 px-3" style="width: 10%;">Kode RT</th>
                        <th class="py-3 px-3" style="width: 18%;">Nama Ketua RT</th>
                        <th class="py-3 px-3" style="width: 14%;">Nomor HP</th>
                        <th class="py-3 px-3" style="width: 18%;">Email Akun</th>
                        <th class="py-3 px-3 text-center" style="width: 9%;">Jumlah KK</th>
                        <th class="py-3 px-3 text-center" style="width: 9%;">Jumlah Warga</th>
                        <th class="py-3 px-3 text-center" style="width: 10%;">Status Akun</th>
                        <th class="py-3 px-3 text-end" style="width: 12%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rts as $rt)
                        @php
                            $ketua = $rt->ketua;
                            $profile = optional($ketua)->profile;
                            $status = $profile->status ?? 'aktif';
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3">
                                <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.95rem; font-weight: 600;">
                                    RT {{ $rt->kode_rt }}
                                </span>
                                @if($rt->nama_rt && $rt->nama_rt !== 'RT ' . $rt->kode_rt)
                                    <div class="small text-white-50 mt-1">{{ $rt->nama_rt }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($ketua)
                                    <div class="fw-bold text-white">{{ $ketua->name }}</div>
                                    <small class="text-white-50">Role: {{ $ketua->role->nama_role ?? 'rt' }}</small>
                                @else
                                    <span class="text-secondary small fst-italic">Belum Ada Akun</span>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalCreateKetua{{ $rt->id }}" style="font-size: 0.75rem;">
                                            <i class="fas fa-plus me-1"></i> Buat Akun
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if(optional($profile)->no_hp)
                                    <span class="text-white"><i class="fab fa-whatsapp text-success me-1"></i> {{ $profile->no_hp }}</span>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($ketua)
                                    <span class="text-white-50 font-monospace small">{{ $ketua->email }}</span>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge bg-info text-dark font-monospace px-2 py-1">{{ $rt->keluarga_count }} KK</span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge bg-secondary font-monospace px-2 py-1">{{ $rt->warga_profiles_count }} Jiwa</span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($ketua)
                                    <span class="badge {{ $status === 'aktif' ? 'bg-success' : 'bg-danger' }} px-2 py-1">
                                        <i class="fas {{ $status === 'aktif' ? 'fa-check' : 'fa-ban' }} me-1"></i> {{ ucfirst($status) }}
                                    </span>
                                @else
                                    <span class="badge bg-dark border border-secondary text-secondary px-2 py-1">Kosong</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-end text-nowrap">
                                <div class="dropdown d-inline">
                                    <a href="{{ route('rts.show', $rt) }}" class="btn btn-sm btn-outline-info text-white me-1" title="Lihat Detail RT">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary">
                                        <li>
                                            <button type="button" class="dropdown-item text-white" data-bs-toggle="modal" data-bs-target="#modalEditRt{{ $rt->id }}">
                                                <i class="fas fa-edit me-2 text-warning"></i> Edit Profil RT
                                            </button>
                                        </li>
                                        @if($ketua)
                                            <li>
                                                <button type="button" class="dropdown-item text-white" data-bs-toggle="modal" data-bs-target="#modalEditKetua{{ $rt->id }}">
                                                    <i class="fas fa-user-cog me-2 text-info"></i> Kelola Akun Ketua
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item text-white" data-bs-toggle="modal" data-bs-target="#modalResetPass{{ $rt->id }}">
                                                    <i class="fas fa-key me-2 text-warning"></i> Reset Password
                                                </button>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('rts.ketua.toggle-status', [$rt, $ketua]) }}" onsubmit="return confirm('Ubah status akun Ketua RT {{ $rt->kode_rt }}?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item {{ $status === 'aktif' ? 'text-danger' : 'text-success' }}">
                                                        <i class="fas {{ $status === 'aktif' ? 'fa-user-slash' : 'fa-user-check' }} me-2"></i>
                                                        {{ $status === 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                                                    </button>
                                                </form>
                                            </li>
                                        @else
                                            <li>
                                                <button type="button" class="dropdown-item text-success" data-bs-toggle="modal" data-bs-target="#modalCreateKetua{{ $rt->id }}">
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

{{-- ==================== MODALS OUTSIDE TABLE ==================== --}}

{{-- Modal Tambah RT --}}
<div class="modal fade" id="modalTambahRt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <form method="POST" action="{{ route('rts.store') }}">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><i class="fas fa-plus-circle text-success me-2"></i>Tambah Wilayah RT Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">RW</label>
                        <select name="rw_id" class="form-select bg-dark text-white border-secondary" required>
                            @foreach($rws as $rw)
                                <option value="{{ $rw->id }}">RW {{ $rw->kode_rw }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Kode RT (Contoh: 01)</label>
                        <input name="kode_rt" placeholder="01" class="form-control bg-dark text-white border-secondary" required maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Nama RT (Opsional)</label>
                        <input name="nama_rt" placeholder="Contoh: RT 01" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan RT</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($rts as $rt)
    {{-- Modal Edit RT --}}
    <div class="modal fade" id="modalEditRt{{ $rt->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white border-secondary">
                <form method="POST" action="{{ route('rts.update', $rt) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title"><i class="fas fa-edit text-warning me-2"></i>Edit Wilayah RT {{ $rt->kode_rt }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">RW</label>
                            <select name="rw_id" class="form-select bg-dark text-white border-secondary" required>
                                @foreach($rws as $rw)
                                    <option value="{{ $rw->id }}" @selected($rt->rw_id === $rw->id)>RW {{ $rw->kode_rw }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Kode RT</label>
                            <input name="kode_rt" value="{{ $rt->kode_rt }}" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Nama RT</label>
                            <input name="nama_rt" value="{{ $rt->nama_rt }}" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Perbarui RT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Buat Akun Ketua RT --}}
    <div class="modal fade" id="modalCreateKetua{{ $rt->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white border-secondary">
                <form method="POST" action="{{ route('rts.ketua.store', $rt) }}">
                    @csrf
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title"><i class="fas fa-user-plus text-success me-2"></i>Buat Akun Ketua RT {{ $rt->kode_rt }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Nama Lengkap Ketua RT *</label>
                            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Email Login *</label>
                            <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required placeholder="rt{{ $rt->kode_rt }}@rw-smart.test">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" class="form-control bg-dark text-white border-secondary" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Password Awal *</label>
                            <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="fas fa-info-circle me-1"></i> Akun akan otomatis terhubung ke wilayah RT {{ $rt->kode_rt }} dengan role <code>rt_{{ $rt->kode_rt }}</code>.
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Buat Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($rt->ketua)
        {{-- Modal Edit Akun Ketua RT --}}
        <div class="modal fade" id="modalEditKetua{{ $rt->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-white border-secondary">
                    <form method="POST" action="{{ route('rts.ketua.update', [$rt, $rt->ketua]) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title"><i class="fas fa-edit text-info me-2"></i>Edit Data Ketua RT {{ $rt->kode_rt }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small">Nama Lengkap Ketua RT</label>
                                <input type="text" name="name" value="{{ $rt->ketua->name }}" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Email Login</label>
                                <input type="email" name="email" value="{{ $rt->ketua->email }}" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Nomor WhatsApp / HP</label>
                                <input type="text" name="no_hp" value="{{ optional($rt->ketua->profile)->no_hp }}" class="form-control bg-dark text-white border-secondary">
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-info text-white"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Reset Password --}}
        <div class="modal fade" id="modalResetPass{{ $rt->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-white border-secondary">
                    <form method="POST" action="{{ route('rts.ketua.reset-password', [$rt, $rt->ketua]) }}">
                        @csrf
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title"><i class="fas fa-key text-warning me-2"></i>Reset Password Ketua RT {{ $rt->kode_rt }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-white-50">Masukkan password baru untuk akun <strong>{{ $rt->ketua->email }}</strong> ({{ $rt->ketua->name }}):</p>
                            <div class="mb-3">
                                <label class="form-label small">Password Baru</label>
                                <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required minlength="6" placeholder="Minimal 6 karakter">
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-check me-1"></i> Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
