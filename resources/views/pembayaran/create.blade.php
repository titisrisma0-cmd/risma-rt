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
                                <option value="">-- Pilih Warga Aktif --</option>
                                @foreach($warga as $item)
                                    @php
                                        $prof = $item->profile;
                                        $rtCode = optional(optional($prof)->wilayahRt)->kode_rt ?? (optional($prof)->rt_id ? str_pad($prof->rt_id, 2, '0', STR_PAD_LEFT) : '-');
                                    @endphp
                                    <option value="{{ $item->id }}" {{ old('user_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }} — NIK: {{ optional($prof)->nik ?? '-' }} — {{ optional($prof)->no_hp ?? '-' }} (RT {{ $rtCode }})
                                    </option>
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
                                @if(isset($bankMetodes) && $bankMetodes->count() > 0)
                                    <option value="transfer" @if(old('metode_bayar')=='transfer') selected @endif>Transfer Bank</option>
                                @endif
                                @if(isset($ewalletMetodes) && $ewalletMetodes->count() > 0)
                                    <option value="ewallet" @if(old('metode_bayar')=='ewallet') selected @endif>E-Wallet (DANA, GoPay, OVO, ShopeePay)</option>
                                @endif
                                @if(isset($qrisMetode) && $qrisMetode)
                                    <option value="qris" @if(old('metode_bayar')=='qris') selected @endif>QRIS (Semua Pembayaran Digital)</option>
                                @endif
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Periode (Bulan/Tahun)</label>
                            <input type="month" name="bulan_tahun" id="bulan_tahun" class="form-control form-control-saas" value="{{ old('bulan_tahun', $selectedBulanTahun ?? date('Y-m')) }}">
                        </div>
                    </div>
                    
                    @if(isset($bankMetodes) && $bankMetodes->count() > 0)
                    <div class="mb-3" id="bankInfo" style="display: none;">
                        <div class="alert alert-info py-2 small mb-0">
                            <h6 class="mb-2 fw-bold"><i class="fas fa-university me-1"></i>Nomor Rekening Resmi RW</h6>
                            @foreach($bankMetodes as $bank)
                                <div class="mb-2 p-2 bg-white rounded border">
                                    <span class="badge bg-primary me-1">{{ $bank->nama }}</span>
                                    <strong>{{ $bank->nomor_rekening }}</strong> a.n. {{ $bank->nama_penerima ?? 'Pengurus RW' }}
                                    @if($bank->instruksi)
                                        <div class="text-muted small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $bank->instruksi }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($ewalletMetodes) && $ewalletMetodes->count() > 0)
                    <div class="mb-3" id="ewalletInfo" style="display: none;">
                        <div class="alert alert-info py-2 small mb-2">
                            <h6 class="mb-1 fw-bold"><i class="fas fa-mobile-alt me-1"></i>E-Wallet Resmi RW</h6>
                            <p class="mb-0">Pilih salah satu e-wallet dan transfer sesuai nomor akun tujuan di bawah ini.</p>
                        </div>
                        <select name="ewallet_provider" id="ewallet_provider" class="form-select form-control-saas mb-2">
                            <option value="">-- Pilih E-Wallet --</option>
                            @foreach($ewalletMetodes as $ew)
                                <option value="{{ $ew->nama }}" data-account="{{ $ew->nomor_rekening }}" data-holder="{{ $ew->nama_penerima }}">
                                    {{ $ew->nama }} ({{ $ew->nomor_rekening }} a.n. {{ $ew->nama_penerima }})
                                </option>
                            @endforeach
                        </select>
                        <div id="ewalletDetails" class="p-2 bg-light rounded border small" style="display: none;"></div>
                    </div>
                    @endif

                    @if(isset($qrisMetode) && $qrisMetode)
                    <div class="mb-3" id="qrisInfo" style="display: none;">
                        <div class="alert alert-info py-2 small mb-3">
                            <h6 class="mb-1 fw-bold"><i class="fas fa-qrcode me-1"></i>{{ $qrisMetode->nama }}</h6>
                            <p class="mb-0">{{ $qrisMetode->instruksi ?? 'Scan QRIS berikut dengan aplikasi mobile banking atau e-wallet Anda.' }}</p>
                        </div>
                        <div class="text-center p-3 bg-light rounded-3 border">
                            @if($qrisMetode->qr_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($qrisMetode->qr_image))
                                <img src="{{ asset('storage/' . $qrisMetode->qr_image) }}" alt="QRIS Code" class="img-fluid rounded shadow-sm" style="max-width: 240px;">
                            @else
                                <img id="qris_code" src="" alt="QRIS Code" class="img-fluid rounded" style="max-width: 220px;">
                            @endif
                            <div class="mt-2 fw-semibold text-dark">{{ $qrisMetode->nama_penerima ?? 'RW SMART' }}</div>
                            <small class="text-secondary">{{ $qrisMetode->nomor_rekening ?? 'NMID: Terverifikasi' }}</small>
                        </div>
                    </div>
                    @endif

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

    var ewalletProviderSelect = document.getElementById('ewallet_provider');
    var ewalletDetails = document.getElementById('ewalletDetails');
    if (ewalletProviderSelect && ewalletDetails) {
        ewalletProviderSelect.addEventListener('change', function() {
            var selectedOpt = this.options[this.selectedIndex];
            var account = selectedOpt.getAttribute('data-account');
            var holder = selectedOpt.getAttribute('data-holder');
            if (account) {
                ewalletDetails.innerHTML = '<i class="fas fa-arrow-right text-emerald me-1"></i> Transfer ke: <strong>' + account + '</strong> a.n. <strong>' + (holder || 'Pengurus') + '</strong>';
                ewalletDetails.style.display = 'block';
            } else {
                ewalletDetails.style.display = 'none';
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
