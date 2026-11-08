@extends('layouts.app')

@section('title', 'Status Audit Keuangan RT')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Keuangan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Audit RT</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-clipboard-check text-success me-2"></i>Status Audit Keuangan RT {{ $rt->kode_rt }}</h1>
            <p class="page-subtitle">Hasil evaluasi, verifikasi berkala, dan catatan rekomendasi resmi dari Pengurus RW.</p>
        </div>

        <form method="GET" action="{{ route('rw.audit.index') }}" class="d-flex gap-2">
            <select name="bulan" class="form-select-saas" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <select name="tahun" class="form-select-saas" onchange="this.form.submit()">
                @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    {{-- Status Banner from RW --}}
    <div class="saas-card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="text-secondary small d-block mb-1">Status Audit Periode {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}:</span>
                <span class="badge {{ $audit->status_badge }} px-3 py-2 fs-6">
                    <i class="fas fa-certificate me-1"></i> {{ $audit->status_label }}
                </span>
                @if($audit->auditor)
                    <small class="text-secondary d-block mt-2">
                        Diperiksa oleh: <strong class="text-dark">{{ $audit->auditor->name }}</strong> (Pengurus RW) pada {{ optional($audit->audited_at)->format('d M Y H:i') }}
                    </small>
                @endif
            </div>

            @if($audit->catatan_rw)
                <div class="p-3 rounded-3 flex-grow-1 ms-md-4 bg-light border border-warning-subtle">
                    <div class="text-warning small fw-bold mb-1"><i class="fas fa-comment-dots me-1"></i> Catatan dari Pengurus RW:</div>
                    <div class="text-dark small">{{ $audit->catatan_rw }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Kartu Ringkasan Keuangan RT --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-success-soft text-success">
                    <i class="fas fa-arrow-down-long"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Pemasukan Bulan Ini</span>
                    <h3 class="stat-value text-success">Rp {{ number_format($pemasukan, 0, ',', '.') }}</h3>
                    <span class="stat-desc">Total setoran kas RT</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-danger-soft text-danger">
                    <i class="fas fa-arrow-up-long"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Pengeluaran Bulan Ini</span>
                    <h3 class="stat-value text-danger">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</h3>
                    <span class="stat-desc">Realisasi operasional RT</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-primary-soft text-primary">
                    <i class="fas fa-vault"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Saldo Kas Kumulatif</span>
                    <h3 class="stat-value text-dark">Rp {{ number_format($saldoKumulatif, 0, ',', '.') }}</h3>
                    <span class="stat-desc">Sisa kas riil terverifikasi</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Trail Log --}}
    <div class="saas-card">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-success me-2"></i>Aktivitas Transaksi Terbaru (Audit Trail)</h6>
            <span class="badge badge-soft-secondary">{{ $auditLogs->count() }} Log</span>
        </div>
        <div class="table-responsive">
            <table class="table table-saas align-middle mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Aktor</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="text-secondary small font-monospace">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="fw-semibold text-dark">{{ optional($log->user)->name ?? 'Sistem' }}</td>
                            <td>
                                <span class="badge badge-soft-secondary small">{{ $log->action }}</span>
                            </td>
                            <td class="text-secondary small">{{ $log->deskripsi }}</td>
                            <td class="text-end font-monospace fw-bold text-success">
                                {{ $log->nominal ? 'Rp ' . number_format($log->nominal, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-clipboard-list"></i></div>
                                    <h6 class="empty-state-title">Belum Ada Log Transaksi</h6>
                                    <p class="empty-state-desc">Belum ada catatan log aktivitas keuangan pada periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
