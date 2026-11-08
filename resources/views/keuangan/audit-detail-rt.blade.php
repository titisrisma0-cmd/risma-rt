@extends('layouts.app')

@section('title', 'Pemeriksaan Audit RT ' . $rt->kode_rt)

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('rw.audit.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Kembali ke Rekap
                </a>
                <h1 class="h3 text-white mb-0">Pemeriksaan Audit Keuangan: RT {{ $rt->kode_rt }}</h1>
            </div>
            <p class="text-white-50 mb-0">Periode: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
        </div>

        <div>
            <span class="badge {{ $audit->status_badge }} px-3 py-2 fs-6">
                Status: {{ $audit->status_label }}
            </span>
        </div>
    </div>

    {{-- Form Verifikasi Status Audit oleh RW --}}
    @if($user->isAdminRw() || $user->hasRole('bendahara'))
        <div class="card p-4 mb-4 border-0" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
            <h5 class="text-emerald-400 mb-3"><i class="fas fa-stamp me-2"></i>Formulir Verifikasi Hasil Audit</h5>
            <form method="POST" action="{{ route('rw.audit.update-status', $rt) }}">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-white-50 small">Keputusan Status Audit *</label>
                        <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" required>
                            <option value="belum_diperiksa" {{ $audit->status === 'belum_diperiksa' ? 'selected' : '' }}>Belum Diperiksa</option>
                            <option value="sedang_diperiksa" {{ $audit->status === 'sedang_diperiksa' ? 'selected' : '' }}>Sedang Diperiksa</option>
                            <option value="perlu_perbaikan" {{ $audit->status === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                            <option value="terverifikasi" {{ $audit->status === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi (Lolos Audit)</option>
                            <option value="ditolak" {{ $audit->status === 'ditolak' ? 'selected' : '' }}>Ditolak dengan Catatan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small">Catatan Audit untuk Ketua RT</label>
                        <input type="text" name="catatan_rw" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" placeholder="Catatan atau masukan untuk pembukuan RT..." value="{{ $audit->catatan_rw }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-emerald text-white w-100 py-2" style="background: #10b981; border: none; border-radius: 8px;">
                            <i class="fas fa-save me-1"></i> Simpan Status
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- Daftar Transaksi Kas RT pada Periode ini --}}
    <div class="card border-0 mb-4" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
            <h5 class="text-white mb-0"><i class="fas fa-receipt text-emerald-400 me-2"></i>Rincian Transaksi Kas RT {{ $rt->kode_rt }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Kategori / Sumber</th>
                        <th class="py-3 px-3">Keterangan</th>
                        <th class="py-3 px-3 text-end">Nominal</th>
                        <th class="py-3 px-3 text-center">Bukti Transaksi</th>
                        <th class="py-3 px-3 text-end">Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kasList as $item)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 text-white-50 font-monospace small">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                            </td>
                            <td class="py-3 px-3">
                                @if($item->jenis === 'masuk')
                                    <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Pemasukan</span>
                                @else
                                    <span class="badge bg-rose-500/20 text-rose-300 border border-rose-500/30">Pengeluaran</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 fw-semibold text-white">{{ $item->sumber }}</td>
                            <td class="py-3 px-3 text-white-50 small">{{ $item->keterangan }}</td>
                            <td class="py-3 px-3 text-end font-monospace fw-bold {{ $item->jenis === 'masuk' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $item->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($item->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($item->pembayaran && $item->pembayaran->bukti_transfer)
                                    <a href="{{ asset('storage/' . $item->pembayaran->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file-image me-1"></i> Bukti Bayar
                                    </a>
                                @else
                                    <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-end text-white-50 small">
                                {{ optional($item->creator)->name ?? 'Petugas RT' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-white-50">
                                Tidak ada transaksi kas pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kasList->hasPages())
            <div class="p-3 border-top border-white-10">
                {{ $kasList->links() }}
            </div>
        @endif
    </div>

    {{-- Audit Trail Log --}}
    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
            <h5 class="text-white mb-0"><i class="fas fa-history text-warning me-2"></i>Audit Trail Log RT {{ $rt->kode_rt }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">Waktu</th>
                        <th class="py-3 px-3">Petugas</th>
                        <th class="py-3 px-3">Aksi</th>
                        <th class="py-3 px-3">Deskripsi Perubahan</th>
                        <th class="py-3 px-3 text-end">Nominal</th>
                        <th class="py-3 px-3 text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 text-white-50 font-monospace small">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="py-3 px-3 fw-semibold text-white">{{ optional($log->user)->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-3">
                                <span class="badge bg-slate-700 text-white">{{ $log->action }}</span>
                            </td>
                            <td class="py-3 px-3 text-white-50 small">{{ $log->deskripsi }}</td>
                            <td class="py-3 px-3 text-end font-monospace text-emerald-400">
                                {{ $log->nominal ? 'Rp ' . number_format($log->nominal, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-end text-white-50 small font-monospace">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-white-50">Belum ada riwayat audit trail untuk RT ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
