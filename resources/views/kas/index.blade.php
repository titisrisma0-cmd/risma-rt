@extends('layouts.app')

@section('title', 'Laporan Kas RW')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-chart-line text-emerald me-2"></i> Laporan Kas RW
        </h1>
        <p class="page-subtitle">Ringkasan arus kas, pemasukan iuran warga, dan pengeluaran operasional</p>
    </div>

    <!-- Statistik Kas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Total Pemasukan</span>
                        <h3 class="stat-value text-emerald mb-0">Rp {{ number_format($saldoMasuk, 0, ',', '.') }}</h3>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Total Pengeluaran</span>
                        <h3 class="stat-value text-danger mb-0">Rp {{ number_format($saldoKeluar, 0, ',', '.') }}</h3>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: #FEE2E2; color: #DC2626;">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Saldo Akhir</span>
                        <h3 class="stat-value text-dark mb-0">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</h3>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: #E0F2FE; color: #0284C7;">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Pemasukan -->
    <div class="saas-card overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-arrow-down text-emerald me-2"></i> Riwayat Pemasukan Kas</h6>
        </div>
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Sumber</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kasMasuk as $item)
                    <tr>
                        <td class="text-secondary small font-monospace">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                        <td>
                            @if($item->sumber == 'Iuran')
                                <span class="badge-soft-emerald"><i class="fas fa-hand-holding-usd me-1"></i> Iuran</span>
                            @else
                                <span class="badge-soft-info">{{ $item->sumber }}</span>
                            @endif
                        </td>
                        <td class="text-dark small">{{ $item->keterangan }}</td>
                        <td class="text-end text-emerald fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-secondary">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                            Belum ada data pemasukan kas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($kasMasuk->count() > 0)
                <tfoot class="bg-light border-top">
                    <tr>
                        <th colspan="3" class="text-end text-dark fw-bold">Total Pemasukan:</th>
                        <th class="text-end text-emerald fw-bold fs-6">Rp {{ number_format($saldoMasuk, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Tabel Pengeluaran -->
    <div class="saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-arrow-up text-danger me-2"></i> Riwayat Pengeluaran Kas</h6>
        </div>
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kasKeluar as $item)
                    <tr>
                        <td class="text-secondary small font-monospace">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                        <td>
                            @if($item->kategori == 'Kebersihan')
                                <span class="badge-soft-emerald"><i class="fas fa-broom me-1"></i> Kebersihan</span>
                            @elseif($item->kategori == 'Keamanan')
                                <span class="badge-soft-info"><i class="fas fa-shield-alt me-1"></i> Keamanan</span>
                            @elseif($item->kategori == 'Sosial')
                                <span class="badge-soft-warning"><i class="fas fa-hand-holding-heart me-1"></i> Sosial</span>
                            @elseif($item->kategori == 'Pembangunan')
                                <span class="badge-soft-primary"><i class="fas fa-hard-hat me-1"></i> Pembangunan</span>
                            @else
                                <span class="badge-soft-secondary">{{ $item->kategori }}</span>
                            @endif
                        </td>
                        <td class="text-dark small">{{ $item->keterangan }}</td>
                        <td class="text-end text-danger fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-secondary">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                            Belum ada data pengeluaran kas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($kasKeluar->count() > 0)
                <tfoot class="bg-light border-top">
                    <tr>
                        <th colspan="3" class="text-end text-dark fw-bold">Total Pengeluaran:</th>
                        <th class="text-end text-danger fw-bold fs-6">Rp {{ number_format($saldoKeluar, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
