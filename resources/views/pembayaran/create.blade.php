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
                                    <option value="{{ $item->id }}" data-nominal="{{ $item->nominal }}">
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
                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control bg-dark text-white border-secondary" placeholder="Masukkan jumlah bayar" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select name="metode_bayar" id="metode_bayar" class="form-control bg-dark text-white border-secondary" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="tunai">Tunai (Bayar Langsung)</option>
                                    <option value="transfer">Transfer Bank</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode (Bulan/Tahun)</label>
                                <input type="month" name="bulan_tahun" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m') }}">
                            </div>
                        </div>
                        
                        <!-- Upload Bukti Transfer - AWALNYA HIDDEN, AKAN MUNCUL SAAT PILIH TRANSFER -->
                        <div class="mb-3" id="buktiTransferDiv" style="display: none;">
                            <label class="form-label text-white">Upload Bukti Transfer <span class="text-danger" id="buktiRequired">*</span></label>
                            <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control bg-dark text-white border-secondary" accept="image/*">
                            <small class="text-secondary">Format: JPG, PNG. Maksimal 2MB</small>
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
                            - Pembayaran akan diverifikasi oleh bendahara<br>
                            - Status akan berubah menjadi "Lunas" setelah diverifikasi<br>
                            - Jika transfer, upload bukti transfer yang jelas
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-university"></i>
                            <strong>Nomor Rekening untuk Transfer:</strong><br>
                            Bank BCA: 123-456-7890 a.n. Bendahara RT<br>
                            Bank Mandiri: 987-654-3210 a.n. Bendahara RT
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
                        <li>Pilih metode pembayaran (Tunai/Transfer)</li>
                        <li>Jika Transfer, upload bukti transfer</li>
                        <li>Klik "Simpan Pembayaran"</li>
                        <li>Tunggu verifikasi dari bendahara</li>
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
    });
    
    // Tampilkan input bukti transfer jika pilih transfer
    var metodeBayar = document.getElementById('metode_bayar');
    var buktiTransferDiv = document.getElementById('buktiTransferDiv');
    var buktiTransferInput = document.getElementById('bukti_transfer');
    var previewBukti = document.getElementById('previewBukti');
    
    metodeBayar.addEventListener('change', function() {
        if (this.value == 'transfer') {
            buktiTransferDiv.style.display = 'block';
            buktiTransferInput.setAttribute('required', 'required');
        } else {
            buktiTransferDiv.style.display = 'none';
            buktiTransferInput.removeAttribute('required');
            previewBukti.innerHTML = '';
        }
    });
    
    // Preview bukti transfer
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