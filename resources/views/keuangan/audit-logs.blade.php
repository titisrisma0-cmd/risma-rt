@extends('layouts.app')

@section('title', 'Log Aktivitas dan Audit Trail')

@section('content')
<div class="py-2">
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Keuangan & Pengawasan</span>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Audit Trail</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Log Aktivitas & Audit Trail
            </h1>
            <p class="text-secondary mb-0">Catatan kronologis seluruh tindakan, audit, transaksi keuangan, dan perubahan data wilayah.</p>
        </div>

        @if($user->isAdminRw() || $user->hasRole('bendahara'))
            <div class="page-header-actions">
                <a href="{{ route('rw.audit.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Keuangan dan Audit RT
                </a>
            </div>
        @endif
    </div>

    {{-- Filter Card --}}
    <div class="saas-card p-3 mb-4">
        <form method="GET" action="{{ route('rw.audit.riwayat') }}" class="row g-2 align-items-end">
            @if($user->isAdminRw() || $user->hasRole('bendahara'))
                <div class="col-md-2">
                    <label class="form-label small text-secondary mb-1">Wilayah RT</label>
                    <select name="rt_id" class="form-select form-select-sm">
                        <option value="">Semua RT (01–08)</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-2">
                <label class="form-label small text-secondary mb-1">Pengguna</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Semua Pengguna</option>
                    @foreach($actors ?? [] as $actor)
                        <option value="{{ $actor->id }}" {{ request('user_id') == $actor->id ? 'selected' : '' }}>{{ $actor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-secondary mb-1">Jenis Tindakan</label>
                <select name="action" class="form-select form-select-sm">
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
                <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small text-secondary mb-1">Kata Kunci</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Cari..." class="form-control">
                    <button type="submit" class="btn btn-emerald">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Audit Trail --}}
    <div class="saas-card mb-4">
        <div class="card-header-saas">
            <h5 class="card-title-saas"><i class="fas fa-list text-success"></i> Catatan Audit Trail Wilayah</h5>
            <span class="badge badge-soft-primary">{{ $logs->total() }} Log Tercatat</span>
        </div>
        <div class="table-responsive">
            <table class="table-saas table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Aktor (Pengguna)</th>
                        <th>Wilayah</th>
                        <th>Aksi</th>
                        <th>Deskripsi Tindakan</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-secondary font-monospace small" style="width: 17%;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td style="width: 20%;">
                                <div class="fw-semibold text-dark small">{{ optional($log->user)->name ?? 'Sistem' }}</div>
                                <small class="text-secondary">{{ optional($log->user)->email }}</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary">
                                    RT {{ optional($log->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-soft-info font-monospace small">{{ $log->action }}</span>
                            </td>
                            <td class="small text-secondary">{{ $log->deskripsi }}</td>
                            <td class="text-end font-monospace small fw-bold">
                                {{ $log->nominal ? 'Rp ' . number_format($log->nominal, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end font-monospace text-secondary small">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                Tidak ada log aktivitas yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-3 border-top">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
