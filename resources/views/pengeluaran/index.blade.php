@extends('layouts.app')

@section('title', 'Manajemen Pengeluaran')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0">Manajemen Pengeluaran</h1>
                    <span class="badge-rt-pill">RT {{ Auth::user()->getRtKode() ?? '01' }}</span>
                </div>
                <p class="page-subtitle mb-0">Kelola dan pantau semua pengeluaran operasional kas</p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('pengeluaran.create') }}" class="btn btn-emerald text-white">
                <i class="fas fa-plus-circle me-1"></i> Tambah Pengeluaran
            </a>
        </div>
    </div>

    <div class="table-card saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-emerald me-2"></i> Daftar Pengeluaran</h6>
        </div>
        <div class="table-responsive">
            <table class="table-saas table-mobile-cards">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                        <th>Dicatat oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengeluaran as $item)
                    <tr>
                        <td data-label="Tanggal" class="text-secondary small font-monospace">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                        <td data-label="Kategori">
                            @if($item->kategori == 'Kebersihan')
                                <span class="badge-soft-emerald"><i class="fas fa-broom me-1"></i> Kebersihan</span>
                            @elseif($item->kategori == 'Keamanan')
                                <span class="badge-soft-info"><i class="fas fa-shield-alt me-1"></i> Keamanan</span>
                            @elseif($item->kategori == 'Sosial')
                                <span class="badge-soft-warning"><i class="fas fa-hand-holding-heart me-1"></i> Sosial</span>
                            @elseif($item->kategori == 'Pembangunan')
                                <span class="badge-soft-primary"><i class="fas fa-hard-hat me-1"></i> Pembangunan</span>
                            @elseif($item->kategori == 'Perlengkapan')
                                <span class="badge-soft-secondary"><i class="fas fa-box me-1"></i> Perlengkapan</span>
                            @else
                                <span class="badge bg-light text-dark border">{{ $item->kategori }}</span>
                            @endif
                        </td>
                        <td data-label="Keterangan" class="text-dark small" style="max-width: 320px;">{{ Str::limit($item->keterangan, 70) }}</td>
                        <td data-label="Nominal" class="text-danger fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td data-label="Dicatat oleh" class="text-secondary small">{{ $item->creator->name ?? '-' }}</td>
                        <td data-label="Aksi" class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('pengeluaran.edit', $item->id) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete('{{ route('pengeluaran.destroy', $item->id) }}', 'pengeluaran')" class="btn btn-outline-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                            Belum ada catatan pengeluaran kas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($pengeluaran->count() > 0)
                <tfoot class="bg-light border-top">
                    <tr>
                        <th colspan="3" class="text-end text-dark fw-bold">Total Pengeluaran:</th>
                        <th class="text-danger fw-bold fs-6">Rp {{ number_format($pengeluaran->sum('nominal'), 0, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
