@extends('layouts.app')

@section('title', 'Pemeriksaan Audit RT ' . $rt->kode_rt)

@section('content')
<div class="py-2">
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <a href="{{ route('rw.audit.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}">Audit RT</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>RT {{ $rt->kode_rt }}</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Pemeriksaan Audit Keuangan: RT {{ $rt->kode_rt }}
            </h1>
            <p class="text-secondary mb-0">Periode: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
        </div>

        <div class="page-header-actions">
            <span class="badge {{ $audit->status_badge }} px-3 py-2 fs-6">
                Status: {{ $audit->status_label }}
            </span>
            <a href="{{ route('rw.audit.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Rekap
            </a>
        </div>
    </div>

    {{-- Form Verifikasi Status Audit oleh RW --}}
    @if($user->isAdminRw() || $user->hasRole('bendahara'))
        <div class="saas-card p-4 mb-4">
            <h5 class="card-title-saas mb-3"><i class="fas fa-stamp text-success"></i> Formulir Verifikasi Hasil Audit</h5>
            <form method="POST" action="{{ route('rw.audit.update-status', $rt) }}">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Keputusan Status Audit *</label>
                        <select name="status" class="form-select" required>
                            <option value="belum_diperiksa" {{ $audit->status === 'belum_diperiksa' ? 'selected' : '' }}>Belum Diperiksa</option>
                            <option value="sedang_diperiksa" {{ $audit->status === 'sedang_diperiksa' ? 'selected' : '' }}>Sedang Diperiksa</option>
                            <option value="perlu_perbaikan" {{ $audit->status === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                            <option value="terverifikasi" {{ $audit->status === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi (Lolos Audit)</option>
                            <option value="ditolak" {{ $audit->status === 'ditolak' ? 'selected' : '' }}>Ditolak dengan Catatan</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Catatan Audit untuk Ketua RT</label>
                        <input type="text" name="catatan_rw" class="form-control" placeholder="Catatan atau masukan untuk pembukuan RT..." value="{{ $audit->catatan_rw }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-emerald w-100">
                            <i class="fas fa-save me-1"></i> Simpan Status
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- Daftar Transaksi Kas RT pada Periode ini --}}
    <div class="saas-card mb-4">
        <div class="card-header-saas">
            <h5 class="card-title-saas"><i class="fas fa-receipt text-success"></i> Rincian Transaksi Kas RT {{ $rt->kode_rt }}</h5>
            <span class="badge badge-soft-primary">{{ $kasList->total() }} Transaksi</span>
        </div>
        <div class="table-responsive">
            <table class="table-saas table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kategori / Sumber</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center">Bukti Transaksi</th>
                        <th class="text-end">Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kasList as $item)
                        <tr>
                            <td class="text-secondary font-monospace small">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                            </td>
                            <td>
                                @if($item->jenis === 'masuk')
                                    <span class="badge badge-soft-success">Pemasukan</span>
                                @else
                                    <span class="badge badge-soft-danger">Pengeluaran</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-dark small">{{ $item->sumber }}</td>
                            <td class="text-secondary small">{{ $item->keterangan }}</td>
                            <td class="text-end font-monospace fw-bold small {{ $item->jenis === 'masuk' ? 'text-success' : 'text-danger' }}">
                                {{ $item->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($item->nominal, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($item->pembayaran && $item->pembayaran->bukti_transfer)
                                    <a href="{{ asset('storage/' . $item->pembayaran->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-light text-primary">
                                        <i class="fas fa-file-image me-1"></i> Bukti Bayar
                                    </a>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="text-end text-secondary small">
                                {{ optional($item->creator)->name ?? 'Petugas RT' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">
                                Tidak ada transaksi kas pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kasList->hasPages())
            <div class="p-3 border-top">
                {{ $kasList->links() }}
            </div>
        @endif
    </div>

    {{-- Audit Trail Log --}}
    <div class="saas-card mb-4">
        <div class="card-header-saas">
            <h5 class="card-title-saas"><i class="fas fa-history text-primary"></i> Audit Trail Log RT {{ $rt->kode_rt }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table-saas table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                        <th>Deskripsi Perubahan</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="text-secondary font-monospace small" style="width: 18%;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="fw-semibold text-dark small">{{ optional($log->user)->name ?? 'Sistem' }}</td>
                            <td>
                                <span class="badge badge-soft-info font-monospace small">{{ $log->action }}</span>
                            </td>
                            <td class="text-secondary small">{{ $log->deskripsi }}</td>
                            <td class="text-end font-monospace small">
                                {{ $log->nominal ? 'Rp ' . number_format($log->nominal, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end font-monospace text-secondary small">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">Belum ada catatan log aktivitas untuk RT ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
