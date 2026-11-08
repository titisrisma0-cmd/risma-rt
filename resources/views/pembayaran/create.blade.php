@extends('layouts.app')

@section('title', Auth::user()->hasRole('warga') ? 'Bayar Iuran' : 'Catat Pembayaran Iuran')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-plus-circle text-emerald me-2"></i> 
                @if(Auth::user()->hasRole('warga'))
                    Bayar Iuran
                @else
                    Catat Pembayaran Iuran
                @endif
            </h1>
            <p class="page-subtitle">
                @if(Auth::user()->hasRole('warga'))
                    Lakukan pembayaran iuran Anda dengan mudah dan aman
                @else
                    Catat penerimaan pembayaran iuran dari warga secara langsung
                @endif
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="saas-card p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-receipt text-emerald me-2"></i>Formulir Pembayaran</h6>

                <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data" id="formPembayaran">
                    @csrf
                    
                    @if(Auth::user()->hasRole('warga'))
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                    @else
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Pilih Warga <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select form-control-saas" required>
                                <option value="">-- Pilih Warga --</option>
                                @foreach($warga as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->profile->no_hp ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Pilih Iuran <span class="text-danger">*</span></label>
                        <select name="iuran_id" id="iuran_id" class="form-select form-control-saas" required>
                            <option value="">-- Pilih Iuran --</option>
                            @foreach($iuran as $item)
                                <option value="{{ $item->id }}" data-nominal="{{ $item->nominal }}" @if(old('iuran_id', $selectedIuranId ?? '') == $item->id) selected @endif>
                                    {{ $item->nama_iuran }} - Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                    @if($item->jenis_iuran == 'wajib') (Wajib) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Jumlah Bayar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control form-control-saas border-start-0" placeholder="Masukkan jumlah bayar" value="{{ old('jumlah_bayar', $selectedIuran ? $selectedIuran->nominal : '') }}" required>
                        </div>
                    </div>

                    <div class="mb-3" id="dendaInfo">
                        <div class="alert alert-warning py-2 small mb-0">
                            <strong>Denda Terlambat:</strong> <span id="dendaAmountText">Rp 0</span><br>
                            <strong>Total Bayar:</strong> <span id="totalBayarText" class="fw-bold text-dark">Rp 0</span><br>
                            <small class="text-secondary">Jatuh tempo: <span id="dueDateText">-</span></small>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_bayar" id="metode_bayar" class="form-select form-control-saas" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="tunai" @if(old('metode_bayar')=='tunai') selected @endif>Tunai (Bayar Langsung)</option>
                                <option value="transfer" @if(old('metode_bayar')=='transfer') selected @endif>Transfer Bank</option>
                                <option value="ewallet" @if(old('metode_bayar')=='ewallet') selected @endif>E-Wallet</option>
                                <option value="qris" @if(old('metode_bayar')=='qris') selected @endif>QRIS</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Periode (Bulan/Tahun)</label>
                            <input type="month" name="bulan_tahun" id="bulan_tahun" class="form-control form-control-saas" value="{{ old('bulan_tahun', $selectedBulanTahun ?? date('Y-m')) }}">
                        </div>
                    </div>
                    
                    <div class="mb-3" id="bankInfo" style="display: none;">
                        <div class="alert alert-info py-2 small mb-0">
                            <h6 class="mb-1 fw-bold"><i class="fas fa-university me-1"></i>Nomor Rekening RW</h6>
                            <p class="mb-1">BCA: <strong>123-456-7890</strong> a.n. Bendahara RW</p>
                            <p class="mb-0">Mandiri: <strong>987-654-3210</strong> a.n. Bendahara RW</p>
                        </div>
                    </div>

                    <div class="mb-3" id="ewalletInfo" style="display: none;">
                        <div class="alert alert-info py-2 small mb-2">
                            <h6 class="mb-1 fw-bold"><i class="fas fa-mobile-alt me-1"></i>E-Wallet</h6>
                            <p class="mb-0">Pilih salah satu E-Wallet dan transfer sesuai nomor/ID tujuan yang tersedia.</p>
                        </div>
                        <select name="ewallet_provider" id="ewallet_provider" class="form-select form-control-saas">
                            <option value="">-- Pilih E-Wallet --</option>
                            <option value="Dana">Dana</option>
                            <option value="OVO">OVO</option>
                            <option value="GoPay">GoPay</option>
                            <option value="ShopeePay">ShopeePay</option>
                        </select>
                    </div>

                    <div class="mb-3" id="qrisInfo" style="display: none;">
                        <div class="alert alert-info py-2 small mb-3">
                            <h6 class="mb-1 fw-bold"><i class="fas fa-qrcode me-1"></i>QRIS</h6>
                            <p class="mb-0">Scan QRIS berikut dengan aplikasi mobile banking atau e-wallet Anda.</p>
                        </div>
                        <div class="text-center p-3 bg-light rounded-3 border">
                            <img id="qris_code" src="" alt="QRIS Code" class="img-fluid rounded" style="max-width: 220px;">
                        </div>
                    </div>

                    <div class="mb-3" id="buktiPembayaranDiv" style="display: none;">
                        <label class="form-label text-secondary small fw-medium">Upload Bukti Pembayaran @if(Auth::user()->hasRole('warga')) <span class="text-danger">*</span> @else (Opsional) @endif</label>
                        <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control form-control-saas" accept="image/*">
                        <small class="text-secondary d-block mt-1">
                            Format: JPG, PNG. Maksimal 2MB.
                            @if(Auth::user()->hasRole('warga'))
                                Wajib untuk Transfer, E-Wallet, dan QRIS.
                            @endif
                        </small>
                        <div id="previewBukti" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Keterangan (Opsional)</label>
                        <textarea name="keterangan" rows="2" class="form-control form-control-saas" placeholder="Catatan tambahan..."></textarea>
                    </div>
                    
                    @if(Auth::user()->hasRole('warga'))
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Pembayaran akan tercatat sebagai "Menunggu Verifikasi" hingga diverifikasi oleh bendahara RT/RW.
                        </div>
                    @else
                        <div class="alert alert-success py-2 small mb-3">
                            <i class="fas fa-check-circle me-1"></i> 
                            Sebagai bendahara/pengurus, pembayaran akan langsung tercatat sebagai <strong>LUNAS</strong>.
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-emerald text-white px-4">
                            <i class="fas fa-save me-1"></i> Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-5">
            <!-- Daftar Iuran Aktif -->
            <div class="saas-card overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ul text-emerald me-2"></i> Daftar Iuran Aktif</h6>
                </div>
                <div class="p-3">
                    @foreach($iuran as $item)
                    <div class="border-bottom mb-3 pb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="text-dark">{{ $item->nama_iuran }}</strong>
                                <div class="mt-1">
                                    @if($item->jenis_iuran == 'wajib')
                                        <span class="badge-soft-danger">Wajib</span>
                                    @elseif($item->jenis_iuran == 'sukarela')
                                        <span class="badge-soft-info">Sukarela</span>
                                    @else
                                        <span class="badge-soft-warning">Khusus</span>
                                    @endif
                                    <small class="text-secondary ms-1">{{ ucfirst($item->periode) }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-emerald fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @if($item->keterangan)
                            <small class="text-secondary d-block mt-1">{{ $item->keterangan }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Cara Pembayaran -->
            <div class="saas-card p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-question-circle text-info me-2"></i> Panduan Pembayaran</h6>
                <ol class="mb-0 small text-secondary ps-3" style="line-height: 1.8;">
                    <li>Pilih jenis iuran yang akan dibayar.</li>
                    <li>Periksa jumlah tagihan dan denda keterlambatan (jika ada).</li>
                    <li>Pilih metode pembayaran (Tunai, Transfer, E-Wallet, atau QRIS).</li>
                    <li>Jika menggunakan metode non-tunai, unggah foto bukti transfer yang jelas.</li>
                    <li>Klik tombol "Simpan Pembayaran".</li>
                    <li>Warga dapat memantau status verifikasi pada riwayat transaksi.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var iuranSelect = document.getElementById('iuran_id');
    var jumlahBayar = document.getElementById('jumlah_bayar');
    
    if (iuranSelect) {
        iuranSelect.addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var nominal = selectedOption.getAttribute('data-nominal');
            if (nominal) {
                jumlahBayar.value = nominal;
            } else {
                jumlahBayar.value = '';
            }
            updateDendaInfo();
        });
    }

    if (jumlahBayar) {
        jumlahBayar.addEventListener('input', updateDendaInfo);
    }
    
    var metodeBayar = document.getElementById('metode_bayar');
    var buktiPembayaranDiv = document.getElementById('buktiPembayaranDiv');
    var buktiTransferInput = document.getElementById('bukti_transfer');
    var previewBukti = document.getElementById('previewBukti');
    var bankInfo = document.getElementById('bankInfo');
    var ewalletInfo = document.getElementById('ewalletInfo');
    var qrisInfo = document.getElementById('qrisInfo');
    var qrisCodeImage = document.getElementById('qris_code');
    var dendaInfo = document.getElementById('dendaInfo');
    var dendaAmountText = document.getElementById('dendaAmountText');
    var dueDateText = document.getElementById('dueDateText');
    var selectedDendaAmount = {{ $dendaAmount ?? 0 }};
    var selectedDueDate = '{{ $dueDate ? $dueDate->format('d F Y') : '' }}';

    if (metodeBayar) {
        metodeBayar.addEventListener('change', function() {
            var method = this.value;
            if (bankInfo) bankInfo.style.display = method === 'transfer' ? 'block' : 'none';
            if (ewalletInfo) ewalletInfo.style.display = method === 'ewallet' ? 'block' : 'none';
            if (qrisInfo) qrisInfo.style.display = method === 'qris' ? 'block' : 'none';

            if (method === 'transfer' || method === 'ewallet' || method === 'qris') {
                if (buktiPembayaranDiv) buktiPembayaranDiv.style.display = 'block';
                @if(Auth::user()->hasRole('warga'))
                if (buktiTransferInput) buktiTransferInput.required = true;
                @endif
            } else {
                if (buktiPembayaranDiv) buktiPembayaranDiv.style.display = 'none';
                if (buktiTransferInput) buktiTransferInput.required = false;
                if (previewBukti) previewBukti.innerHTML = '';
            }

            if (method === 'qris') {
                var iuranName = $('#iuran_id option:selected').text();
                var jumlah = $('#jumlah_bayar').val();
                var bulanTahun = $('input[name="bulan_tahun"]').val();
                var qrData = 'Pembayaran ' + iuranName + ' - ' + bulanTahun + ' - Rp ' + jumlah;
                if (qrisCodeImage) qrisCodeImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(qrData);
            }
        });
    }

    function updateDendaInfo() {
        var el = document.getElementById('jumlah_bayar');
        var nominal = el ? Number(el.value) || 0 : 0;
        var totalAmount = nominal + selectedDendaAmount;
        if (dendaAmountText) dendaAmountText.textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(selectedDendaAmount);
        var totalEl = document.getElementById('totalBayarText');
        if (totalEl) totalEl.textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(totalAmount);
        if (dueDateText) dueDateText.textContent = selectedDueDate || '-';
        if (dendaInfo) dendaInfo.style.display = 'block';
    }

    updateDendaInfo();

    if (buktiTransferInput) {
        buktiTransferInput.addEventListener('change', function() {
            var file = this.files[0];
            if (file && previewBukti) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        previewBukti.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail mt-2" style="max-width: 200px; max-height: 150px;">';
                    } else {
                        previewBukti.innerHTML = '<div class="alert alert-info mt-2"><i class="fas fa-file-pdf"></i> File: ' + file.name + '</div>';
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection
