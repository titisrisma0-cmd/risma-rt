@extends('layouts.app')

@section('title', 'Mutasi Warga')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1">
                <i class="fas fa-exchange-alt text-amber-400 me-2"></i>
                @if($user->isAdminRt())
                    Mutasi Warga — RT {{ $user->getRtKode() ?? '01' }}
                @else
                    Mutasi Warga — Tingkat RW
                @endif
            </h1>
            <p class="text-white-50 mb-0">Pencatatan perpindahan penduduk (Masuk, Keluar, Meninggal, Pindah RT)</p>
        </div>

        @if($user->isAdminRt() || $user->isAdminRw())
            <div>
                <button type="button" class="btn btn-warning text-dark px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#tambahMutasiModal" style="border-radius: 8px;">
                    <i class="fas fa-plus me-1"></i> Catat Mutasi Warga
                </button>
            </div>
        @endif
    </div>

    {{-- Filter Card --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="GET" action="{{ route('warga.mutasi') }}" class="row g-3 align-items-center">
            @if($user->isAdminRw())
                <div class="col-md-3">
                    <label class="form-label text-white-50 small mb-1">Wilayah RT</label>
                    <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                        <option value="">Semua RT</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ $user->isAdminRw() ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label text-white-50 small mb-1">Jenis Mutasi</label>
                <select name="jenis" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
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
                    <a href="{{ route('warga.mutasi') }}" class="btn btn-outline-light w-100 py-2">Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar Mutasi --}}
    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.85); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Nama Warga</th>
                        <th class="py-3 px-3">NIK / No KK</th>
                        <th class="py-3 px-3">Wilayah</th>
                        <th class="py-3 px-3 text-center">Jenis Mutasi</th>
                        <th class="py-3 px-3">Keterangan</th>
                        <th class="py-3 px-3 text-end">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasiList as $m)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 text-white-50 font-monospace small">
                                {{ $m->tanggal_mutasi->format('d M Y') }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-white">{{ $m->nama_warga }}</div>
                            </td>
                            <td class="py-3 px-3 font-monospace text-blue-300 small">
                                <div>NIK: {{ $m->nik ?? '-' }}</div>
                                <div class="text-white-50">KK: {{ $m->no_kk ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    RT {{ optional($m->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($m->jenis_mutasi === 'masuk')
                                    <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Masuk / Datang</span>
                                @elseif($m->jenis_mutasi === 'keluar')
                                    <span class="badge bg-amber-500/20 text-amber-300 border border-amber-500/30">Pindah Keluar</span>
                                @elseif($m->jenis_mutasi === 'meninggal')
                                    <span class="badge bg-rose-500/20 text-rose-300 border border-rose-500/30">Meninggal</span>
                                @elseif($m->jenis_mutasi === 'pindah_rt')
                                    <span class="badge bg-purple-500/20 text-purple-300 border border-purple-500/30">Pindah RT</span>
                                @else
                                    <span class="badge bg-slate-700 text-white">{{ ucfirst($m->jenis_mutasi) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-white-50 small" style="max-width: 300px;">
                                {{ $m->keterangan ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-end text-white-50 small">
                                {{ optional($m->creator)->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="fas fa-inbox fa-3x mb-3 text-white-50 d-block"></i>
                                Belum ada riwayat mutasi warga yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutasiList->hasPages())
            <div class="p-3 border-top border-white-10">
                {{ $mutasiList->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Catat Mutasi --}}
@if($user->isAdminRt() || $user->isAdminRw())
    <div class="modal fade" id="tambahMutasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px;">
                <div class="modal-header border-bottom border-white-10">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt text-amber-400 me-2"></i>Catat Mutasi Warga</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('warga.mutasi.store') }}">
                    @csrf
                    <div class="modal-body">
                        @if($user->isAdminRw())
                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Wilayah RT *</label>
                                <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                    <option value="">-- Pilih RT --</option>
                                    @foreach($rts as $r)
                                        <option value="{{ $r->id }}">RT {{ $r->kode_rt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Nama Warga *</label>
                            <input type="text" name="nama_warga" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Nama lengkap warga..." required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">NIK (opsional)</label>
                                <input type="text" name="nik" maxlength="16" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="NIK 16 digit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">No. KK (opsional)</label>
                                <input type="text" name="no_kk" maxlength="16" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="No. KK 16 digit">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Jenis Mutasi *</label>
                                <select name="jenis_mutasi" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                                    <option value="masuk">Masuk / Datang Baru</option>
                                    <option value="keluar">Keluar / Pindah Rumah</option>
                                    <option value="meninggal">Meninggal Dunia</option>
                                    <option value="pindah_rt">Pindah ke RT Lain</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Tanggal Mutasi *</label>
                                <input type="date" name="tanggal_mutasi" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Keterangan / Alasan</label>
                            <textarea name="keterangan" rows="3" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Alamat baru / penyebab / keterangan lainnya..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white-10">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark fw-semibold">Simpan Mutasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
