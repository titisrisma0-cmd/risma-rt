@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-white">
                        <i class="fas fa-hand-holding-usd"></i> 
                        @if(Auth::user()->hasRole('warga'))
                            Riwayat Pembayaran Saya
                        @else
                            Manajemen Pembayaran
                        @endif
                    </h2>
                    <p class="text-white-50">
                        @if(Auth::user()->hasRole('warga'))
                            Lihat histori pembayaran iuran Anda
                        @else
                            Kelola dan verifikasi pembayaran iuran warga
                        @endif
                    </p>
                </div>
                <div>
                    @if(Auth::user()->hasRole('warga'))
                        <a href="{{ route('pembayaran.create') }}" class="btn btn-gradient">
                            <i class="fas fa-plus-circle"></i> Bayar Iuran
                        </a>
                    @else
                        <a href="{{ route('pembayaran.create') }}" class="btn btn-gradient">
                            <i class="fas fa-plus-circle"></i> Catat Pembayaran
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->hasRole('warga'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0 text-white"><i class="fas fa-file-invoice-dollar"></i> Tagihan Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($tagihan as $item)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-secondary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="text-white mb-1">{{ $item->iuran->nama_iuran }}</h6>
                                                    <small class="text-secondary">{{ ucfirst($item->iuran->periode) }} - {{ $item->bulan_tahun }}</small>
                                                </div>
                                                @php
                                                    if ($item->status == 'lunas') {
                                                        $statusLabel = 'Lunas';
                                                        $statusClass = 'bg-success';
                                                    } elseif ($item->status == 'pending') {
                                                        $statusLabel = 'Menunggu Verifikasi';
                                                        $statusClass = 'bg-warning text-dark';
                                                    } elseif ($item->status == 'gagal') {
                                                        $statusLabel = 'Gagal';
                                                        $statusClass = 'bg-danger';
                                                    } else {
                                                        $statusLabel = 'Belum Dibayar';
                                                        $statusClass = 'bg-secondary';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                            </div>
                                            <p class="text-white mb-2">Jumlah Tagihan: <strong>Rp {{ number_format($item->iuran->nominal, 0, ',', '.') }}</strong></p>
                                            <p class="text-secondary mb-3">Jatuh tempo: {{ $item->iuran->due_day ?? 10 }} {{ date('F Y', strtotime($item->bulan_tahun . '-01')) }}</p>
                                            @if($item->status == 'pending')
                                                <button type="button" class="btn btn-warning btn-sm w-100" disabled>
                                                    <i class="fas fa-clock"></i> Menunggu Verifikasi
                                                </button>
                                            @elseif($item->status !== 'lunas')
                                                <a href="{{ route('pembayaran.create', ['iuran_id' => $item->iuran->id, 'bulan_tahun' => $item->bulan_tahun]) }}" class="btn btn-gradient btn-sm w-100">
                                                    <i class="fas fa-wallet"></i> Bayar
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                                    <i class="fas fa-check-circle"></i> Sudah Lunas
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">Tidak ada tagihan aktif saat ini.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 text-white"><i class="fas fa-list"></i> Daftar Pembayaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
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
                                <strong class="text-white">{{ $item->no_transaksi }}</strong>
                                <br>
                                <small class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            @if(!Auth::user()->hasRole('warga'))
                                <td class="text-white">{{ $item->user->name }}</td>
                            @endif
                            <td class="text-white">{{ $item->iuran->nama_iuran }}</td>
                            <td class="text-white">{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                            <td class="text-success fw-bold">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                            <td>
                                @if($item->metode_bayar == 'tunai')
                                    <span class="badge bg-info">Tunai</span>
                                @elseif($item->metode_bayar == 'transfer')
                                    <span class="badge bg-primary">Transfer</span>
                                @elseif($item->metode_bayar == 'ewallet')
                                    <span class="badge bg-warning text-dark">E-Wallet</span>
                                @elseif($item->metode_bayar == 'qris')
                                    <span class="badge bg-success">QRIS</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($item->metode_bayar) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->bukti_transfer)
                                    <a href="{{ asset('storage/' . $item->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status == 'lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @elseif($item->status == 'pending')
                                    <span class="badge bg-warning">Menunggu Verifikasi</span>
                                @else
                                    <span class="badge bg-danger">Gagal</span>
                                @endif
                            </td>
                            @if(!Auth::user()->hasRole('warga'))
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($item->status == 'pending')
                                            <form action="{{ route('pembayaran.verifikasi', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Verifikasi pembayaran ini?')">
                                                    <i class="fas fa-check"></i> Verifikasi
                                                </button>
                                            </form>
                                        @endif
                                        <button onclick="confirmDelete('{{ route('pembayaran.destroy', $item->id) }}', 'pembayaran {{ $item->no_transaksi }}')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ Auth::user()->hasRole('warga') ? '8' : '9' }}" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-secondary mb-3 d-block"></i>
                                <p class="text-secondary">Belum ada data pembayaran</p>
                                @if(Auth::user()->hasRole('warga'))
                                    <a href="{{ route('pembayaran.create') }}" class="btn btn-gradient mt-2">
                                        <i class="fas fa-plus-circle"></i> Bayar Iuran Sekarang
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
</div>

<style>
.card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(96, 165, 250, 0.1);
}
.card-header {
    background: rgba(15, 23, 42, 0.5);
    border-bottom: 1px solid rgba(96, 165, 250, 0.1);
}
.table-dark {
    background: transparent;
}
.table-dark th {
    border-bottom-color: rgba(96, 165, 250, 0.2);
    color: #94a3b8;
    font-weight: 500;
}
.table-dark td {
    border-bottom-color: rgba(96, 165, 250, 0.05);
    color: #f1f5f9;
}
.btn-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
    transition: all 0.3s;
}
.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59,130,246,0.4);
    color: white;
}
.text-white {
    color: #f1f5f9 !important;
}
.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}
</style>

<script>
    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }

    function updatePaymentMethod(method) {
        $('#bankInfo').toggleClass('d-none', method !== 'transfer');
        $('#ewalletInfo').toggleClass('d-none', method !== 'ewallet');
        $('#qrisInfo').toggleClass('d-none', method !== 'qris');

        if (method === 'transfer' || method === 'ewallet' || method === 'qris') {
            $('#buktiPembayaranDiv').show();
            $('#modal_bukti_transfer').prop('required', true);
        } else {
            $('#buktiPembayaranDiv').hide();
            $('#modal_bukti_transfer').prop('required', false);
        }

        if (method !== 'ewallet') {
            $('#modal_ewallet_provider').val('');
        }

        if (method === 'qris') {
            const iuranName = $('#modal_iuran_nama').text();
            const amount = $('#modal_jumlah_bayar').val();
            const period = $('#modal_bulan_tahun').val();
            const qrData = `Pembayaran ${iuranName} - ${period} - Rp ${amount}`;
            const qrSrc = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrData)}`;
            $('#modal_qris_code').attr('src', qrSrc);
        }
    }

    $(document).ready(function () {
        $('.bayar-tagihan-btn').on('click', function () {
            const button = $(this);
            const iuranId = button.data('iuran-id');
            const iuranNama = button.data('iuran-nama');
            const iuranNominal = Number(button.data('iuran-nominal')) || 0;
            const bulanTahun = button.data('bulan-tahun');

            $('#modal_iuran_id').val(iuranId);
            $('#modal_iuran_nama').text(iuranNama);
            $('#modal_jumlah_text').text(formatRupiah(iuranNominal));
            $('#modal_jumlah_bayar').val(iuranNominal);
            $('#modal_bulan_tahun').text(bulanTahun);
            $('#modal_bulan_tahun').val(bulanTahun);
            $('#modal_metode_bayar').val('transfer');
            updatePaymentMethod('transfer');
            $('#modal_preview_bukti').html('');
        });

        $('#modal_metode_bayar').on('change', function () {
            updatePaymentMethod($(this).val());
        });

        $('#modal_bukti_transfer').on('change', function () {
            const file = this.files[0];
            if (!file) {
                $('#modal_preview_bukti').html('');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#modal_preview_bukti').html(`<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 180px;">`);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endsection
