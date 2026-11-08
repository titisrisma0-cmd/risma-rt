@extends('layouts.app')

@section('title', 'Log Aktivitas dan Audit Trail')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1"><i class="fas fa-list-check text-emerald-400 me-2"></i>Log Aktivitas dan Audit Trail</h1>
            <p class="text-white-50 mb-0">Catatan kronologis seluruh tindakan, audit, transaksi keuangan, dan perubahan data wilayah</p>
        </div>

        @if($user->isAdminRw() || $user->hasRole('bendahara'))
            <div>
                <a href="{{ route('rw.audit.index') }}" class="btn btn-outline-light px-3 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Keuangan dan Audit RT
                </a>
            </div>
        @endif
    </div>

    {{-- Filter Card --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="GET" action="{{ route('rw.audit.riwayat') }}" class="row g-3 align-items-end">
            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <div class="col-md-2">
                    <label class="form-label text-white-50 small mb-1">Wilayah RT</label>
                    <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                        <option value="">Semua RT (01–08)</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Pelaku (Pengguna)</label>
                <select name="user_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <option value="">Semua Pengguna</option>
                    @foreach($actors ?? [] as $actor)
                        <option value="{{ $actor->id }}" {{ request('user_id') == $actor->id ? 'selected' : '' }}>{{ $actor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Jenis Tindakan</label>
                <select name="action" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <option value="">Semua Tindakan</option>
                    <option value="audit_review" {{ request('action') == 'audit_review' ? 'selected' : '' }}>Audit Keuangan</option>
                    <option value="pembayaran_iuran" {{ request('action') == 'pembayaran_iuran' ? 'selected' : '' }}>Pencatatan Pembayaran</option>
                    <option value="pembayaran_verifikasi" {{ request('action') == 'pembayaran_verifikasi' ? 'selected' : '' }}>Verifikasi Pembayaran</option>
                    <option value="pengeluaran_create" {{ request('action') == 'pengeluaran_create' ? 'selected' : '' }}>Pencatatan Pengeluaran</option>
                    <option value="persetujuan_surat" {{ request('action') == 'persetujuan_surat' ? 'selected' : '' }}>Persetujuan Surat</option>
                    <option value="eskalasi_rw" {{ request('action') == 'eskalasi_rw' ? 'selected' : '' }}>Eskalasi ke RW</option>
                    <option value="mutasi_warga" {{ request('action') == 'mutasi_warga' ? 'selected' : '' }}>Mutasi Warga</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
            </div>

            <div class="col-md-2">
                <label class="form-label text-white-50 small mb-1">Cari Kata Kunci</label>
                <div class="input-group">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Cari..." class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);">
                    <button type="submit" class="btn btn-emerald text-white" style="background: #10b981; border: none;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Audit Trail --}}
    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.85); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">Tanggal & Waktu</th>
                        <th class="py-3 px-3">Aktor (Pengguna)</th>
                        <th class="py-3 px-3">Wilayah</th>
                        <th class="py-3 px-3">Aksi</th>
                        <th class="py-3 px-3">Deskripsi Tindakan</th>
                        <th class="py-3 px-3 text-end">Nominal</th>
                        <th class="py-3 px-3 text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 text-white-50 font-monospace small">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-white">{{ optional($log->user)->name ?? 'Sistem' }}</div>
                                <small class="text-white-50">{{ optional($log->user)->email ?? '' }}</small>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    RT {{ optional($log->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge bg-slate-700 text-white font-monospace small">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-white-50 small" style="max-width: 320px;">
                                {{ $log->deskripsi }}
                            </td>
                            <td class="py-3 px-3 text-end font-monospace fw-semibold text-emerald-400">
                                {{ $log->nominal ? 'Rp ' . number_format($log->nominal, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-end text-white-50 font-monospace small">
                                {{ $log->ip_address ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                Belum ada riwayat audit log yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="card-footer border-top border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
