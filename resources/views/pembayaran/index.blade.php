@extends('layouts.app')

@section('title', Auth::user()->hasRole('warga') ? 'Riwayat Pembayaran Saya' : 'Manajemen Pembayaran')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center flex-wrap gap-2 gap-sm-3">
            <div class="page-head-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0">
                        @if(Auth::user()->hasRole('warga'))
                            Riwayat Pembayaran Saya
                        @else
                            Manajemen Pembayaran
                        @endif
                    </h1>
                    <span class="badge-rt-pill" style="position: static !important; display: inline-flex !important;">
                        @if(Auth::user()->hasRole('warga'))
                            Warga
                        @elseif(Auth::user()->isAdminRt())
                            RT {{ Auth::user()->getRtKode() ?? '01' }}
                        @else
                            Pengurus RW
                        @endif
                    </span>
                </div>
                <p class="page-subtitle mb-0">
                    @if(Auth::user()->hasRole('warga'))
                        Lihat histori pembayaran iuran Anda
                    @else
                        Kelola dan verifikasi pembayaran iuran warga
                    @endif
                </p>
            </div>
        </div>
        <div class="page-header-actions">
            @if(Auth::user()->hasRole('warga'))
                <a href="{{ route('pembayaran.create') }}" class="btn btn-emerald text-white">
                    <i class="fas fa-plus-circle me-1"></i> Bayar Iuran
                </a>
            @else
                <a href="{{ route('pembayaran.create') }}" class="btn btn-emerald text-white">
                    <i class="fas fa-plus-circle me-1"></i> Catat Pembayaran
                </a>
            @endif
        </div>
    </div>

    @if(Auth::user()->hasRole('warga'))
        <div class="saas-card rounded-2xl p-4 mb-4 border">
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-file-invoice-dollar text-emerald me-2"></i>Tagihan Iuran Warga</h6>
            
            @if($tagihanGroups->isNotEmpty())
                <div class="row g-3 mb-4" role="tablist" aria-label="Pilih periode tagihan">
                    @foreach($tagihanGroups as $group)
                        <div class="col-md-6 col-lg-4">
                            <button type="button" class="period-btn w-100 text-start saas-card rounded-xl p-3 border transition-all {{ $loop->first ? 'active' : '' }}" data-period-target="tagihan-period-{{ $loop->index }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" style="{{ $loop->first ? 'background-color: #ecfdf5; border: 1.5px solid #a7f3d0 !important; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);' : 'background-color: #ffffff; border: 1px solid #e2e8f0 !important;' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1.5 gap-2">
                                    <span class="fw-bold text-dark fs-6">{{ $group->period->label }}</span>
                                    @if($group->unpaid_count > 0)
                                        <span class="badge-soft-amber px-3 py-1 rounded-full"><i class="fas fa-clock me-1"></i> {{ $group->unpaid_count }} belum bayar</span>
                                    @else
                                        <span class="badge-soft-emerald px-3 py-1 rounded-full"><i class="fas fa-check-circle me-1"></i> Lunas Semua</span>
                                    @endif
                                </div>
                                <div class="text-secondary small">{{ $group->period->month_label }}</div>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            @forelse($tagihanGroups as $group)
                <section id="tagihan-period-{{ $loop->index }}" class="tagihan-group period-panel {{ $loop->first ? 'active' : '' }}" style="display: {{ $loop->first ? 'block' : 'none' }};">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 pb-2.5 border-bottom">
                        <div>
                            <span class="badge-soft-sky px-3 py-1 rounded-full mb-1 d-inline-block">{{ $group->period->label }}</span>
                            <h6 class="text-dark fw-bold mb-0">{{ $group->period->month_label }}</h6>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge-soft-emerald px-3 py-1 rounded-full"><i class="fas fa-check-circle me-1"></i> {{ $group->paid_count }} lunas</span>
                            <span class="badge-soft-amber px-3 py-1 rounded-full"><i class="fas fa-clock me-1"></i> {{ $group->pending_count }} proses</span>
                            <span class="badge-soft-rose px-3 py-1 rounded-full"><i class="fas fa-wallet me-1"></i> {{ $group->unpaid_count }} belum</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        @forelse($group->items as $item)
                            <div class="col-lg-6">
                                <div class="saas-card rounded-xl p-3.5 p-3 p-md-3.5 border h-100 d-flex flex-column justify-content-between shadow-xs" style="overflow: visible;">
                                    <div>
                                        <div class="flex items-start justify-between gap-2 mb-2 d-flex">
                                            <div class="flex-1 min-w-0">
                                                <h6 class="text-dark fw-bold mb-1 break-words leading-snug" style="overflow: visible; text-align: left; margin-left: 0;">{{ $item->iuran->nama_iuran }}</h6>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    @if($item->iuran->jenis_iuran === 'wajib')
                                                        <span class="badge-soft-rose px-2.5 py-0.5 rounded-full" style="font-size: 0.7rem;">Wajib</span>
                                                    @elseif($item->iuran->jenis_iuran === 'sukarela')
                                                        <span class="badge-soft-sky px-2.5 py-0.5 rounded-full" style="font-size: 0.7rem;">Sukarela</span>
                                                    @endif
                                                    <small class="text-secondary">{{ ucfirst($item->iuran->periode) }} &bull; {{ $item->bulan_tahun }}</small>
                                                </div>
                                            </div>
                                            @php
                                                if ($item->status == 'lunas') {
                                                    $statusBadge = 'badge-soft-emerald';
                                                    $statusLabel = 'Lunas';
                                                } elseif ($item->status == 'pending') {
                                                    $statusBadge = 'badge-soft-amber';
                                                    $statusLabel = 'Menunggu Verifikasi';
                                                } elseif ($item->status == 'gagal') {
                                                    $statusBadge = 'badge-soft-rose';
                                                    $statusLabel = 'Gagal';
                                                } else {
                                                    $statusBadge = 'badge-soft-secondary';
                                                    $statusLabel = 'Belum Dibayar';
                                                }
                                            @endphp
                                            <span class="{{ $statusBadge }} px-3 py-1 rounded-full shrink-0 whitespace-nowrap">{{ $statusLabel }}</span>
                                        </div>
                                        <div class="h5 text-emerald fw-bold mb-1">Rp {{ number_format($item->iuran->nominal, 0, ',', '.') }}</div>
                                        <p class="text-secondary small mb-3">Jatuh tempo: {{ $item->iuran->due_day ?? 10 }} {{ \Carbon\Carbon::parse($item->bulan_tahun . '-01')->locale('id')->translatedFormat('F Y') }}</p>
                                    </div>
                                    <div>
                                        @if($item->status == 'pending')
                                            <button type="button" class="btn btn-outline-warning btn-sm w-100 rounded-pill" disabled>
                                                <i class="fas fa-clock me-1"></i> Menunggu Verifikasi
                                            </button>
                                        @elseif($item->status !== 'lunas')
                                            <a href="{{ route('pembayaran.create', ['iuran_id' => $item->iuran->id, 'bulan_tahun' => $item->bulan_tahun]) }}" class="btn btn-emerald btn-sm w-100 text-white rounded-pill shadow-xs">
                                                <i class="fas fa-wallet me-1"></i> Bayar Sekarang
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 rounded-pill" disabled>
                                                <i class="fas fa-check-circle me-1 text-emerald"></i> Sudah Lunas
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info mb-0 rounded-xl">Tidak ada tagihan untuk periode ini.</div>
                            </div>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="alert alert-info mb-0 rounded-xl">Tidak ada tagihan aktif saat ini.</div>
            @endforelse
        </div>
    @endif

    <div class="table-card saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-emerald me-2"></i> Daftar Pembayaran</h6>
        </div>
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th style="min-width: 170px;">No. Transaksi</th>
                        @if(!Auth::user()->hasRole('warga'))
                            <th style="min-width: 160px;">Warga</th>
                        @endif
                        <th style="min-width: 150px;">Jenis Iuran</th>
                        <th style="min-width: 130px;">Tanggal Bayar</th>
                        <th style="min-width: 130px;">Jumlah</th>
                        <th style="min-width: 110px;">Metode</th>
                        <th style="min-width: 120px;">Bukti Transfer</th>
                        <th style="min-width: 140px;">Status</th>
                        @if(!Auth::user()->hasRole('warga'))
                            <th class="text-end" style="min-width: 120px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $item)
                    <tr>
                        <td style="min-width: 170px; white-space: nowrap;">
                            <strong class="text-dark font-monospace">{{ $item->no_transaksi }}</strong>
                            <br>
                            <small class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        @if(!Auth::user()->hasRole('warga'))
                            <td class="text-dark fw-medium">{{ $item->user->name }}</td>
                        @endif
                        <td class="text-dark">{{ $item->iuran->nama_iuran }}</td>
                        <td class="text-secondary small">{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                        <td class="text-emerald fw-bold">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                        <td>
                            @if($item->metode_bayar == 'tunai')
                                <span class="badge-soft-sky px-2.5 py-1 rounded-full">Tunai</span>
                            @elseif($item->metode_bayar == 'transfer')
                                <span class="badge-soft-primary px-2.5 py-1 rounded-full">Transfer</span>
                            @elseif($item->metode_bayar == 'ewallet')
                                <span class="badge-soft-amber px-2.5 py-1 rounded-full">E-Wallet</span>
                            @elseif($item->metode_bayar == 'qris')
                                <span class="badge-soft-emerald px-2.5 py-1 rounded-full">QRIS</span>
                            @else
                                <span class="badge-soft-secondary px-2.5 py-1 rounded-full">{{ ucfirst($item->metode_bayar) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->bukti_transfer)
                                <a href="{{ asset('storage/' . $item->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5">
                                    <i class="fas fa-image text-emerald me-1"></i> Lihat
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status == 'lunas')
                                <span class="badge-soft-emerald px-3 py-1 rounded-full">
                                    <i class="fas fa-check-circle me-1"></i> Lunas
                                </span>
                            @elseif($item->status == 'pending')
                                <span class="badge-soft-amber px-3 py-1 rounded-full">
                                    <i class="fas fa-clock me-1"></i> Menunggu Verifikasi
                                </span>
                            @else
                                <span class="badge-soft-rose px-3 py-1 rounded-full">
                                    <i class="fas fa-xmark me-1"></i> Gagal
                                </span>
                            @endif
                        </td>
                        @if(!Auth::user()->hasRole('warga'))
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @if($item->status == 'pending')
                                        <form action="{{ route('pembayaran.verifikasi', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" data-confirm="Verifikasi pembayaran ini?" data-confirm-title="Verifikasi Pembayaran" data-confirm-btn="Ya, Verifikasi" title="Verifikasi">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="confirmDelete('{{ route('pembayaran.destroy', $item->id) }}', 'pembayaran {{ $item->no_transaksi }}')" class="btn btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->hasRole('warga') ? '8' : '9' }}" class="text-center py-5 text-secondary">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                            <p class="text-secondary mb-2">Belum ada data pembayaran</p>
                            @if(Auth::user()->hasRole('warga'))
                                <a href="{{ route('pembayaran.create') }}" class="btn btn-emerald text-white mt-1">
                                    <i class="fas fa-plus-circle me-1"></i> Bayar Iuran Sekarang
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periodButtons = document.querySelectorAll('.period-btn');
        const periodPanels = document.querySelectorAll('.period-panel');

        periodButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.dataset.periodTarget;
                const targetPanel = document.getElementById(targetId);

                periodButtons.forEach(function (item) {
                    item.classList.remove('active', 'border-primary');
                    item.style.backgroundColor = 'var(--surface-2)';
                    item.style.border = '1px solid var(--border)';
                    item.setAttribute('aria-selected', 'false');
                });

                periodPanels.forEach(function (panel) {
                    panel.style.display = 'none';
                });

                button.classList.add('active', 'border-primary');
                button.style.backgroundColor = 'var(--primary-soft)';
                button.style.border = '1.5px solid var(--primary)';
                button.setAttribute('aria-selected', 'true');

                if (targetPanel) {
                    targetPanel.style.display = 'block';
                }
            });
        });
    });
</script>
@endsection
