@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white">
                <i class="fas fa-plus-circle"></i> 
                @if(Auth::user()->hasRole('warga'))
                    Bayar Iuran
                @else
                    Catat Pembayaran Iuran
                @endif
            </h2>
            <p class="text-white-50">
                @if(Auth::user()->hasRole('warga'))
                    Lakukan pembayaran iuran Anda dengan mudah
                @else
                    Catat pembayaran iuran dari warga
                @endif
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Form Pembayaran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data" id="formPembayaran">
                        @csrf
                        
                        @if(Auth::user()->hasRole('warga'))
                            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        @else
                            <div class="mb-3">
                                <label class="form-label text-white">Pilih Warga <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control bg-dark text-white border-secondary" required>
                                    <option value="">Pilih Warga</option>
                                    @foreach($warga as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->profile->no_hp ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Pilih Iuran <span class="text-danger">*</span></label>
                            <select name="iuran_id" id="iuran_id" class="form-control bg-dark text-white border-secondary" required>
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
                            <label class="form-label text-white">Jumlah Bayar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white border-secondary">Rp</span>
                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control bg-dark text-white border-secondary" placeholder="Masukkan jumlah bayar" value="{{ old('jumlah_bayar', $selectedIuran ? $selectedIuran->nominal : '') }}" required>
                            </div>
                        </div>

                        <div class="mb-3" id="dendaInfo">
                            <div class="alert alert-warning">
                                <strong>Denda Terlambat:</strong> <span id="dendaAmountText">Rp 0</span><br>
                                <strong>Total Bayar:</strong> <span id="totalBayarText">Rp 0</span><br>
                                <small>Jatuh tempo: <span id="dueDateText">-</span></small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select name="metode_bayar" id="metode_bayar" class="form-control bg-dark text-white border-secondary" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="tunai" @if(old('metode_bayar')=='tunai') selected @endif>Tunai (Bayar Langsung)</option>
                                    <option value="transfer" @if(old('metode_bayar')=='transfer') selected @endif>Transfer Bank</option>
                                    <option value="ewallet" @if(old('metode_bayar')=='ewallet') selected @endif>E-Wallet</option>
                                    <option value="qris" @if(old('metode_bayar')=='qris') selected @endif>QRIS</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode (Bulan/Tahun)</label>
                                <input type="month" name="bulan_tahun" id="bulan_tahun" class="form-control bg-dark text-white border-secondary" value="{{ old('bulan_tahun', $selectedBulanTahun ?? date('Y-m')) }}">
                            </div>
                        </div>
                        
                        <div class="mb-3" id="bankInfo" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="mb-2">Nomor Rekening RT</h6>
                                <p class="mb-1">BCA: <strong>123-456-7890</strong> a.n. Bendahara RT</p>
                                <p class="mb-1">Mandiri: <strong>987-654-3210</strong> a.n. Bendahara RT</p>
                            </div>
                        </div>

                        <div class="mb-3" id="ewalletInfo" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="mb-2">E-Wallet</h6>
                                <p class="mb-1">Pilih salah satu E-Wallet dan transfer sesuai nomor/ID tujuan yang tersedia di bawah.</p>
                            </div>
                            <select name="ewallet_provider" id="ewallet_provider" class="form-control bg-dark text-white border-secondary">
                                <option value="">-- Pilih E-Wallet --</option>
                                <option value="Dana">Dana</option>
                                <option value="OVO">OVO</option>
                                <option value="GoPay">GoPay</option>
                                <option value="ShopeePay">ShopeePay</option>
                            </select>
                        </div>

                        <div class="mb-3" id="qrisInfo" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="mb-2">QRIS</h6>
                                <p class="mb-1">Scan QRIS berikut dengan aplikasi e-wallet Anda.</p>
                            </div>
                            <div class="text-center">
                                <img id="qris_code" src="" alt="QRIS Code" class="img-fluid rounded" style="max-width: 240px;">
                            </div>
                        </div>

                        <div class="mb-3" id="buktiPembayaranDiv" style="display: none;">
                            <label class="form-label text-white">Upload Bukti Pembayaran @if(Auth::user()->hasRole('warga')) <span class="text-danger">*</span> @else (Opsional) @endif</label>
                            <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control bg-dark text-white border-secondary" accept="image/*">
                            <small class="text-secondary">
                                Format: JPG, PNG. Maksimal 2MB.
                                @if(Auth::user()->hasRole('warga'))
                                    Wajib untuk Transfer, E-Wallet, dan QRIS.
                                @endif
                            </small>
                            <div id="previewBukti" class="mt-2"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Keterangan (Opsional)</label>
                            <textarea name="keterangan" rows="2" class="form-control bg-dark text-white border-secondary" placeholder="Catatan tambahan..."></textarea>
                        </div>
                        
                        @if(Auth::user()->hasRole('warga'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Informasi:</strong><br>
                            - Pembayaran akan tercatat sebagai "Menunggu Verifikasi"<br>
                            - Admin atau bendahara akan mengecek bukti pembayaran terlebih dahulu<br>
                            - Status berubah menjadi "Lunas" setelah pembayaran diverifikasi
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-university"></i>
                            <strong>Nomor Rekening untuk Transfer:</strong><br>
                            Bank BCA: 123-456-7890 a.n. Bendahara RT<br>
                            Bank Mandiri: 987-654-3210 a.n. Bendahara RT
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-mobile-alt"></i>
                            <strong>Nomor E-Wallet:</strong><br>
                            Dana: 0812-3456-7890 a.n. Bendahara RT<br>
                            OVO: 0812-3456-7890 a.n. Bendahara RT<br>
                            GoPay: 0812-3456-7890 a.n. Bendahara RT<br>
                            ShopeePay: 0812-3456-7890 a.n. Bendahara RT
                        </div>
                        @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            Sebagai bendahara, pembayaran akan langsung tercatat LUNAS
                        </div>
                        @endif
                        
                        <div class="text-end">
                            <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save"></i> Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-5">
            <!-- Daftar Iuran Aktif -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-list-ul"></i> Daftar Iuran Aktif</h5>
                </div>
                <div class="card-body">
                    @foreach($iuran as $item)
                    <div class="border-bottom mb-3 pb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong class="text-white">{{ $item->nama_iuran }}</strong>
                                <br>
                                <small>
                                    @if($item->jenis_iuran == 'wajib')
                                        <span class="badge bg-danger">Wajib</span>
                                    @elseif($item->jenis_iuran == 'sukarela')
                                        <span class="badge bg-info">Sukarela</span>
                                    @else
                                        <span class="badge bg-warning">Khusus</span>
                                    @endif
                                    {{ ucfirst($item->periode) }}
                                </small>
                            </div>
                            <div class="text-end">
                                <strong class="text-primary">Rp {{ number_format($item->nominal, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        @if($item->keterangan)
                            <small class="text-secondary">{{ $item->keterangan }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Cara Pembayaran -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Cara Pembayaran</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0 small text-white">
                        <li>Pilih jenis iuran yang akan dibayar</li>
                        <li>Masukkan jumlah bayar sesuai iuran</li>
                        <li>Pilih metode pembayaran (Tunai/Transfer/E-Wallet/QRIS)</li>
                        <li>Jika menggunakan metode digital, upload bukti pembayaran</li>
                        <li>Klik "Simpan Pembayaran"</li>
                        <li>Warga menunggu verifikasi admin/bendahara sebelum status menjadi Lunas</li>
                    </ol>
                </div>
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
.btn-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
}
.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59,130,246,0.4);
    color: white;
}
</style>

<script>
// Gunakan event listener DOMContentLoaded agar jQuery siap
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto fill jumlah bayar ketika pilih iuran
    var iuranSelect = document.getElementById('iuran_id');
    var jumlahBayar = document.getElementById('jumlah_bayar');
    
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

    jumlahBayar.addEventListener('input', updateDendaInfo);
    
    // Tampilkan input bukti pembayaran sesuai metode
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

    metodeBayar.addEventListener('change', function() {
        var method = this.value;
        bankInfo.style.display = method === 'transfer' ? 'block' : 'none';
        ewalletInfo.style.display = method === 'ewallet' ? 'block' : 'none';
        qrisInfo.style.display = method === 'qris' ? 'block' : 'none';

        if (method === 'transfer' || method === 'ewallet' || method === 'qris') {
            buktiPembayaranDiv.style.display = 'block';
            @if(Auth::user()->hasRole('warga'))
            buktiTransferInput.required = true;
            @endif
        } else {
            buktiPembayaranDiv.style.display = 'none';
            buktiTransferInput.required = false;
            previewBukti.innerHTML = '';
        }

        if (method === 'qris') {
            var iuranName = $('#iuran_id option:selected').text();
            var jumlah = $('#jumlah_bayar').val();
            var bulanTahun = $('input[name="bulan_tahun"]').val();
            var qrData = 'Pembayaran ' + iuranName + ' - ' + bulanTahun + ' - Rp ' + jumlah;
            qrisCodeImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(qrData);
        }
    });

    function updateDendaInfo() {
        var nominal = Number(document.getElementById('jumlah_bayar').value) || 0;
        var totalAmount = nominal + selectedDendaAmount;
        dendaAmountText.textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(selectedDendaAmount);
        document.getElementById('totalBayarText').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(totalAmount);
        dueDateText.textContent = selectedDueDate || '-';
        dendaInfo.style.display = 'block';
    }

    updateDendaInfo();

    // Preview bukti pembayaran
    buktiTransferInput.addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    previewBukti.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">';
                } else {
                    previewBukti.innerHTML = '<div class="alert alert-info mt-2"><i class="fas fa-file-pdf"></i> File: ' + file.name + '</div>';
                }
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection
