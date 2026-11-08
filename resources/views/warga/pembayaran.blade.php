@extends('layouts.app')

@section('title', 'Pembayaran Iuran Warga')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Keuangan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pembayaran Iuran</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-hand-holding-dollar text-success me-2"></i>Pembayaran Iuran Warga</h1>
            <p class="page-subtitle">Lakukan setoran iuran lingkungan bulanan atau kegiatan khusus secara transparan dan terverifikasi.</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-saas alert-saas-success mb-4">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Form Pembayaran -->
        <div class="col-12 col-lg-6">
            <div class="saas-card h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-plus-circle text-success me-2"></i>Form Pengajuan Pembayaran</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        
                        <div class="mb-3">
                            <label class="form-label-saas">Pilih Pos Iuran <span class="text-danger">*</span></label>
                            <select name="iuran_id" id="iuran_id" class="form-select-saas" required>
                                <option value="">-- Pilih Jenis Iuran --</option>
                                @foreach($iuran as $item)
                                    <option value="{{ $item->id }}" data-nominal="{{ $item->nominal }}">
                                        {{ $item->nama_iuran }} - Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                        @if($item->jenis_iuran == 'wajib') (Wajib) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-saas">Nominal yang Dibayar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-semibold text-secondary">Rp</span>
                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control-saas border-start-0" readonly required placeholder="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-saas">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_bayar" id="metode_bayar" class="form-select-saas" required>
                                <option value="">-- Pilih Metode Pembayaran --</option>
                                <option value="tunai">Tunai (Bayar Langsung ke Pengurus / Bendahara)</option>
                                <option value="transfer">Transfer Bank (Upload Bukti Transfer)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="buktiTransferDiv" style="display: none;">
                            <label class="form-label-saas">Upload Bukti Transfer <span class="text-danger">*</span></label>
                            <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control-saas" accept="image/*">
                            <small class="text-muted mt-1 d-block">Format: JPG, PNG. Maksimal 2MB</small>
                            <div id="previewBukti" class="mt-2"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-saas">Periode Pembayaran (Bulan / Tahun)</label>
                            <input type="month" name="bulan_tahun" class="form-control-saas" value="{{ date('Y-m') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-saas">Keterangan Tambahan (Opsional)</label>
                            <textarea name="keterangan" rows="2" class="form-control-saas" placeholder="Catatan atau nomor referensi pembayaran..."></textarea>
                        </div>
                        
                        <div class="alert alert-saas alert-saas-info mb-3">
                            <i class="fas fa-circle-info fs-5"></i>
                            <div class="small">
                                <strong>Panduan:</strong> Untuk metode transfer, mohon lampirkan struk atau screenshot bukti transfer. Pembayaran akan diverifikasi oleh Bendahara RT/RW sebelum status berubah menjadi <em>Lunas</em>.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-saas-primary w-100 py-2">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Informasi Iuran & Rekening -->
        <div class="col-12 col-lg-6">
            <div class="saas-card mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-success me-2"></i>Daftar Iuran Lingkungan Aktif</h6>
                </div>
                <div class="card-body p-4">
                    @forelse($iuran as $item)
                        <div class="p-3 mb-2 rounded-3 border bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $item->nama_iuran }}</h6>
                                <div class="d-flex gap-1 align-items-center">
                                    @if($item->jenis_iuran == 'wajib') 
                                        <span class="badge badge-soft-danger">Wajib</span>
                                    @elseif($item->jenis_iuran == 'sukarela')
                                        <span class="badge badge-soft-info">Sukarela</span>
                                    @else
                                        <span class="badge badge-soft-warning">Khusus</span>
                                    @endif
                                    <small class="text-muted">· Periode {{ ucfirst($item->periode) }}</small>
                                </div>
                                @if($item->keterangan)
                                    <small class="text-secondary d-block mt-1">{{ $item->keterangan }}</small>
                                @endif
                            </div>
                            <div class="text-end">
                                <h5 class="fw-bold text-success mb-0">Rp {{ number_format($item->nominal, 0, ',', '.') }}</h5>
                                <small class="text-muted">per {{ $item->periode }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 mb-0">Tidak ada pos iuran aktif saat ini.</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Rekening Transfer -->
            <div class="saas-card p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-university text-success me-2"></i>Rekening Resmi RW / RT</h6>
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 border bg-light">
                            <span class="badge badge-soft-primary mb-1">Bank BCA</span>
                            <div class="fw-bold text-dark fs-6 font-monospace">123-456-7890</div>
                            <small class="text-muted">a.n. Bendahara RW 05</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 border bg-light">
                            <span class="badge badge-soft-warning mb-1">Bank Mandiri</span>
                            <div class="fw-bold text-dark fs-6 font-monospace">987-654-3210</div>
                            <small class="text-muted">a.n. Bendahara RW 05</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Riwayat Pembayaran Saya -->
    <div class="saas-card">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-success me-2"></i>Riwayat Pembayaran Saya</h6>
            <span class="badge badge-soft-secondary">{{ $pembayaran->count() }} Transaksi</span>
        </div>
        <div class="table-responsive">
            <table class="table table-saas align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Pos Iuran</th>
                        <th>Tanggal Bayar</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Status Verifikasi</th>
                        <th class="text-center">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $item)
                        <tr>
                            <td>
                                <strong class="text-dark font-monospace">{{ $item->no_transaksi }}</strong>
                                <small class="text-muted d-block">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>{{ $item->iuran->nama_iuran ?? '-' }}</td>
                            <td>{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                            <td class="fw-bold text-success">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                            <td>
                                @if($item->metode_bayar == 'tunai')
                                    <span class="badge badge-soft-info">Tunai</span>
                                @else
                                    <span class="badge badge-soft-primary">Transfer</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status == 'lunas')
                                    <span class="badge badge-soft-success">
                                        <i class="fas fa-check-circle me-1"></i> Lunas
                                    </span>
                                @elseif($item->status == 'pending')
                                    <span class="badge badge-soft-warning">
                                        <i class="fas fa-clock me-1"></i> Menunggu Verifikasi
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger">Ditolak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->bukti_transfer)
                                    <a href="{{ $item->bukti_transfer_url }}" target="_blank" class="btn btn-outline-saas btn-sm text-info">
                                        <i class="fas fa-image me-1"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                                    <h6 class="empty-state-title">Belum Ada Riwayat Pembayaran</h6>
                                    <p class="empty-state-desc">Gunakan formulir di atas untuk mengajukan setoran iuran pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const iuranSelect = document.getElementById('iuran_id');
    const jumlahBayar = document.getElementById('jumlah_bayar');
    const metodeSelect = document.getElementById('metode_bayar');
    const buktiDiv = document.getElementById('buktiTransferDiv');
    const buktiInput = document.getElementById('bukti_transfer');
    const previewBukti = document.getElementById('previewBukti');

    if (iuranSelect && jumlahBayar) {
        iuranSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            jumlahBayar.value = selectedOpt.dataset.nominal || '';
        });
    }

    if (metodeSelect && buktiDiv && buktiInput) {
        metodeSelect.addEventListener('change', function() {
            if (this.value === 'transfer') {
                buktiDiv.style.display = 'block';
                buktiInput.required = true;
            } else {
                buktiDiv.style.display = 'none';
                buktiInput.required = false;
                if (previewBukti) previewBukti.innerHTML = '';
            }
        });
    }

    if (buktiInput && previewBukti) {
        buktiInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewBukti.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; margin-top: 10px;" class="border rounded-3 shadow-sm">';
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection
