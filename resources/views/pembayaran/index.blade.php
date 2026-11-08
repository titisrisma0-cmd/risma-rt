@extends('layouts.app')

@section('title', Auth::user()->hasRole('warga') ? 'Riwayat Pembayaran Saya' : 'Manajemen Pembayaran')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-hand-holding-usd text-emerald me-2"></i> 
                @if(Auth::user()->hasRole('warga'))
                    Riwayat Pembayaran Saya
                @else
                    Manajemen Pembayaran
                @endif
            </h1>
            <p class="page-subtitle">
                @if(Auth::user()->hasRole('warga'))
                    Lihat histori pembayaran iuran Anda
                @else
                    Kelola dan verifikasi pembayaran iuran warga
                @endif
            </p>
        </div>
        <div>
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
        <div class="saas-card p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-file-invoice-dollar text-emerald me-2"></i>Tagihan Iuran Warga</h6>
            
            @if($tagihanGroups->isNotEmpty())
                <div class="row g-3 mb-4" role="tablist" aria-label="Pilih periode tagihan">
                    @foreach($tagihanGroups as $group)
                        <div class="col-md-6 col-lg-4">
                            <button type="button" class="period-btn w-100 text-start saas-card p-3 border {{ $loop->first ? 'active border-primary' : '' }}" data-period-target="tagihan-period-{{ $loop->index }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" style="{{ $loop->first ? 'background-color: #F0FDF4; border: 1.5px solid var(--primary-green) !important;' : 'background-color: #FAFCFA;' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark fs-6">{{ $group->period->label }}</span>
                                    <span class="badge-soft-warning">{{ $group->unpaid_count }} belum bayar</span>
                                </div>
                                <div class="text-secondary small">{{ $group->period->month_label }}</div>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            @forelse($tagihanGroups as $group)
                <section id="tagihan-period-{{ $loop->index }}" class="tagihan-group period-panel {{ $loop->first ? 'active' : '' }}" style="display: {{ $loop->first ? 'block' : 'none' }};">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 pb-2 border-bottom">
                        <div>
                            <span class="badge-soft-info mb-1 d-inline-block">{{ $group->period->label }}</span>
                            <h6 class="text-dark fw-bold mb-0">{{ $group->period->month_label }}</h6>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge-soft-emerald"><i class="fas fa-check-circle me-1"></i> {{ $group->paid_count }} lunas</span>
                            <span class="badge-soft-warning"><i class="fas fa-clock me-1"></i> {{ $group->pending_count }} proses</span>
                            <span class="badge-soft-danger"><i class="fas fa-wallet me-1"></i> {{ $group->unpaid_count }} belum</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        @forelse($group->items as $item)
                            <div class="col-lg-6">
                                <div class="saas-card p-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <div>
                                                <h6 class="text-dark fw-bold mb-1">{{ $item->iuran->nama_iuran }}</h6>
                                                <small class="text-secondary">{{ ucfirst($item->iuran->periode) }} - {{ $item->bulan_tahun }}</small>
                                            </div>
                                            @php
                                                if ($item->status == 'lunas') {
                                                    $statusBadge = 'badge-soft-emerald';
                                                    $statusLabel = 'Lunas';
                                                } elseif ($item->status == 'pending') {
                                                    $statusBadge = 'badge-soft-warning';
                                                    $statusLabel = 'Menunggu Verifikasi';
                                                } elseif ($item->status == 'gagal') {
                                                    $statusBadge = 'badge-soft-danger';
                                                    $statusLabel = 'Gagal';
                                                } else {
                                                    $statusBadge = 'badge-soft-secondary';
                                                    $statusLabel = 'Belum Dibayar';
                                                }
                                            @endphp
                                            <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                                        </div>
                                        <div class="h5 text-emerald fw-bold mb-1">Rp {{ number_format($item->iuran->nominal, 0, ',', '.') }}</div>
                                        <p class="text-secondary small mb-3">Jatuh tempo: {{ $item->iuran->due_day ?? 10 }} {{ \Carbon\Carbon::parse($item->bulan_tahun . '-01')->locale('id')->translatedFormat('F Y') }}</p>
                                    </div>
                                    <div>
                                        @if($item->status == 'pending')
                                            <button type="button" class="btn btn-outline-warning btn-sm w-100" disabled>
                                                <i class="fas fa-clock me-1"></i> Menunggu Verifikasi
                                            </button>
                                        @elseif($item->status !== 'lunas')
                                            <a href="{{ route('pembayaran.create', ['iuran_id' => $item->iuran->id, 'bulan_tahun' => $item->bulan_tahun]) }}" class="btn btn-emerald btn-sm w-100 text-white">
                                                <i class="fas fa-wallet me-1"></i> Bayar Sekarang
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                <i class="fas fa-check-circle me-1 text-emerald"></i> Sudah Lunas
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info mb-0">Tidak ada tagihan untuk periode ini.</div>
                            </div>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="alert alert-info mb-0">Tidak ada tagihan aktif saat ini.</div>
            @endforelse
        </div>
    @endif

    <div class="saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-emerald me-2"></i> Daftar Pembayaran</h6>
        </div>
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        @if(!Auth::user()->hasRole('warga'))
                            <th>Warga</th>
                        @endif
                        <th>Jenis Iuran</th>
                        <th>Tanggal Bayar</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Bukti Transfer</th>
                        <th>Status</th>
                        @if(!Auth::user()->hasRole('warga'))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $item)
                    <tr>
                        <td>
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
                                <span class="badge-soft-info">Tunai</span>
                            @elseif($item->metode_bayar == 'transfer')
                                <span class="badge-soft-primary">Transfer</span>
                            @elseif($item->metode_bayar == 'ewallet')
                                <span class="badge-soft-warning">E-Wallet</span>
                            @elseif($item->metode_bayar == 'qris')
                                <span class="badge-soft-emerald">QRIS</span>
                            @else
                                <span class="badge-soft-secondary">{{ ucfirst($item->metode_bayar) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->bukti_transfer)
                                <a href="{{ asset('storage/' . $item->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-image text-emerald me-1"></i> Lihat
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status == 'lunas')
                                <span class="badge-soft-emerald">Lunas</span>
                            @elseif($item->status == 'pending')
                                <span class="badge-soft-warning">Menunggu Verifikasi</span>
                            @else
                                <span class="badge-soft-danger">Gagal</span>
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
                    item.style.backgroundColor = '#FAFCFA';
                    item.style.border = '1px solid var(--border-color)';
                    item.setAttribute('aria-selected', 'false');
                });

                periodPanels.forEach(function (panel) {
                    panel.style.display = 'none';
                });

                button.classList.add('active', 'border-primary');
                button.style.backgroundColor = '#F0FDF4';
                button.style.border = '1.5px solid var(--primary-green)';
                button.setAttribute('aria-selected', 'true');

                if (targetPanel) {
                    targetPanel.style.display = 'block';
                }
            });
        });
    });
</script>
@endsection
