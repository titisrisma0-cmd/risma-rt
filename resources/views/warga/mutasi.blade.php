@extends('layouts.app')

@section('title', 'Mutasi Warga')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-exchange-alt text-warning me-2"></i>
                @if($user->isAdminRt())
                    Mutasi Warga — RT {{ $user->getRtKode() ?? '01' }}
                @else
                    Mutasi Warga — Tingkat RW
                @endif
            </h1>
            <p class="page-subtitle">Pencatatan perpindahan penduduk (Masuk, Keluar, Meninggal, Pindah RT)</p>
        </div>

        @if($user->isAdminRt() || $user->isAdminRw())
            <div>
                <button type="button" class="btn btn-warning text-dark px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#tambahMutasiModal">
                    <i class="fas fa-plus me-1"></i> Catat Mutasi Warga
                </button>
            </div>
        @endif
    </div>

    {{-- Filter Card --}}
    <div class="saas-card p-3 mb-4">
        <form method="GET" action="{{ route('warga.mutasi') }}" class="row g-3 align-items-center">
            @if($user->isAdminRw())
                <div class="col-md-3">
                    <label class="form-label text-secondary small mb-1 fw-medium">Wilayah RT</label>
                    <select name="rt_id" class="form-select form-control-saas" onchange="this.form.submit()">
                        <option value="">Semua RT</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ $user->isAdminRw() ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label text-secondary small mb-1 fw-medium">Jenis Mutasi</label>
                <select name="jenis" class="form-select form-control-saas" onchange="this.form.submit()">
                    <option value="">Semua Jenis Mutasi</option>
                    <option value="masuk" {{ request('jenis') == 'masuk' ? 'selected' : '' }}>Warga Masuk (Pendatang)</option>
                    <option value="keluar" {{ request('jenis') == 'keluar' ? 'selected' : '' }}>Warga Keluar (Pindah Domisili)</option>
                    <option value="meninggal" {{ request('jenis') == 'meninggal' ? 'selected' : '' }}>Meninggal Dunia</option>
                    <option value="pindah_rt" {{ request('jenis') == 'pindah_rt' ? 'selected' : '' }}>Pindah Antar RT</option>
                    <option value="lainnya" {{ request('jenis') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                @if(request()->hasAny(['rt_id', 'jenis']))
                    <a href="{{ route('warga.mutasi') }}" class="btn btn-outline-secondary w-100 py-2">Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar Mutasi --}}
    <div class="saas-card overflow-hidden">
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Warga</th>
                        <th>NIK / No KK</th>
                        <th>Wilayah</th>
                        <th class="text-center">Jenis Mutasi</th>
                        <th>Keterangan</th>
                        <th class="text-end">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasiList as $m)
                        <tr>
                            <td class="text-secondary font-monospace small">
                                {{ $m->tanggal_mutasi->format('d M Y') }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $m->nama_warga }}</div>
                            </td>
                            <td class="font-monospace small">
                                <div class="text-emerald fw-semibold">NIK: {{ $m->nik ?? '-' }}</div>
                                <div class="text-secondary">KK: {{ $m->no_kk ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge-soft-info">
                                    RT {{ optional($m->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($m->jenis_mutasi === 'masuk')
                                    <span class="badge-soft-emerald">Masuk / Datang</span>
                                @elseif($m->jenis_mutasi === 'keluar')
                                    <span class="badge-soft-warning">Pindah Keluar</span>
                                @elseif($m->jenis_mutasi === 'meninggal')
                                    <span class="badge-soft-danger">Meninggal</span>
                                @elseif($m->jenis_mutasi === 'pindah_rt')
                                    <span class="badge-soft-info">Pindah RT</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($m->jenis_mutasi) }}</span>
                                @endif
                            </td>
                            <td class="text-secondary small" style="max-width: 300px;">
                                {{ $m->keterangan ?? '-' }}
                            </td>
                            <td class="text-end text-secondary small">
                                {{ optional($m->creator)->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="fas fa-inbox fa-3x mb-3 text-muted d-block opacity-50"></i>
                                Belum ada riwayat mutasi warga yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutasiList->hasPages())
            <div class="p-3 border-top bg-white">
                {{ $mutasiList->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Catat Mutasi --}}
@if($user->isAdminRt() || $user->isAdminRw())
    <div class="modal fade" id="tambahMutasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom bg-white">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-exchange-alt text-warning me-2"></i>Catat Mutasi Warga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('warga.mutasi.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @if($user->isAdminRw())
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-medium">Wilayah RT *</label>
                                <select name="rt_id" class="form-select form-control-saas" required>
                                    <option value="">-- Pilih RT --</option>
                                    @foreach($rts as $r)
                                        <option value="{{ $r->id }}">RT {{ $r->kode_rt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Nama Warga *</label>
                            <input type="text" name="nama_warga" class="form-control form-control-saas" placeholder="Nama lengkap warga..." required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">NIK (opsional)</label>
                                <input type="text" name="nik" maxlength="16" class="form-control form-control-saas font-monospace" placeholder="NIK 16 digit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">No. KK (opsional)</label>
                                <input type="text" name="no_kk" maxlength="16" class="form-control form-control-saas font-monospace" placeholder="No. KK 16 digit">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Jenis Mutasi *</label>
                                <select name="jenis_mutasi" class="form-select form-control-saas" required>
                                    <option value="masuk">Masuk / Datang Baru</option>
                                    <option value="keluar">Keluar / Pindah Rumah</option>
                                    <option value="meninggal">Meninggal Dunia</option>
                                    <option value="pindah_rt">Pindah ke RT Lain</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Tanggal Mutasi *</label>
                                <input type="date" name="tanggal_mutasi" class="form-control form-control-saas" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Keterangan / Alasan</label>
                            <textarea name="keterangan" rows="3" class="form-control form-control-saas" placeholder="Alamat baru / penyebab / keterangan lainnya..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark fw-semibold px-4">Simpan Mutasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
