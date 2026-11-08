@extends('layouts.app')

@section('title', 'Status Audit Keuangan RT')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1"><i class="fas fa-clipboard-check text-warning me-2"></i>Status Audit Keuangan RT {{ $rt->kode_rt }}</h1>
            <p class="text-white-50 mb-0">Hasil pemeriksaan dan verifikasi berkala oleh Pengurus RW</p>
        </div>

        <form method="GET" action="{{ route('rw.audit.index') }}" class="d-flex gap-2">
            <select name="bulan" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <select name="tahun" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    {{-- Status Banner from RW --}}
    <div class="card p-4 mb-4 border-0" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="text-white-50 small d-block mb-1">Status Audit Periode {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}:</span>
                <span class="badge {{ $audit->status_badge }} px-3 py-2 fs-6">
                    <i class="fas fa-certificate me-1"></i> {{ $audit->status_label }}
                </span>
                @if($audit->auditor)
                    <small class="text-white-50 d-block mt-2">
                        Diperiksa oleh: <strong>{{ $audit->auditor->name }}</strong> (Pengurus RW) pada {{ optional($audit->audited_at)->format('d M Y H:i') }}
                    </small>
                @endif
            </div>

            @if($audit->catatan_rw)
                <div class="p-3 rounded flex-grow-1 ms-md-4" style="background: rgba(15, 23, 42, 0.7); border: 1px dashed rgba(245, 158, 11, 0.3);">
                    <div class="text-warning small fw-bold mb-1"><i class="fas fa-comment-dots me-1"></i> Catatan dari Pengurus RW:</div>
                    <div class="text-white small">{{ $audit->catatan_rw }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Kartu Ringkasan Keuangan RT --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="text-white-50 small mb-1">Total Pemasukan (Bulan Ini)</div>
                <div class="h4 text-emerald-400 fw-bold font-monospace mb-0">Rp {{ number_format($pemasukan, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="text-white-50 small mb-1">Total Pengeluaran (Bulan Ini)</div>
                <div class="h4 text-rose-400 fw-bold font-monospace mb-0">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="text-white-50 small mb-1">Saldo Kas RT Kumulatif</div>
                <div class="h4 text-white fw-bold font-monospace mb-0">Rp {{ number_format($saldoKumulatif, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    {{-- Audit Trail Log --}}
    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
            <h5 class="text-white mb-0"><i class="fas fa-history text-warning me-2"></i>Aktivitas Transaksi Terbaru (Audit Trail)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">Waktu</th>
                        <th class="py-3 px-3">Aktor</th>
                        <th class="py-3 px-3">Aksi</th>
                        <th class="py-3 px-3">Deskripsi</th>
                        <th class="py-3 px-3 text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 text-white-50 small font-monospace">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="py-3 px-3 fw-semibold text-white">{{ optional($log->user)->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-3">
                                <span class="badge bg-slate-700 text-white small">{{ $log->action }}</span>
                            </td>
                            <td class="py-3 px-3 text-white-50 small">{{ $log->deskripsi }}</td>
                            <td class="py-3 px-3 text-end font-monospace text-emerald-400">
                                {{ $log->nominal ? 'Rp ' . number_format($log->nominal, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-white-50">Belum ada catatan log aktivitas keuangan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
