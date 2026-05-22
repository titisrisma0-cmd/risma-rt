@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-hand-holding-usd"></i> Pembayaran Iuran
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Lakukan pembayaran iuran Anda dengan mudah
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Form Pembayaran -->
        <div class="col-md-6 mb-4">
            <div class="card animate__animated animate__fadeInLeft">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Form Pembayaran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Iuran <span class="text-danger">*</span></label>
                            <select name="iuran_id" id="iuran_id" class="form-control" required>
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
                            <label class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" readonly required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_bayar" id="metode_bayar" class="form-control" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="tunai">Tunai (Bayar Langsung ke Bendahara)</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="buktiTransferDiv" style="display: none;">
                            <label class="form-label">Upload Bukti Transfer <span class="text-danger">*</span></label>
                            <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                            <div id="previewBukti" class="mt-2"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Periode (Bulan/Tahun)</label>
                            <input type="month" name="bulan_tahun" class="form-control" value="{{ date('Y-m') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Keterangan (Opsional)</label>
                            <textarea name="keterangan" rows="2" class="form-control" placeholder="Catatan tambahan..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Jika memilih Transfer, Anda harus upload bukti transfer</li>
                                <li>Pembayaran akan diverifikasi oleh Bendahara</li>
                                <li>Status akan berubah menjadi "Lunas" setelah diverifikasi</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-university"></i>
                            <strong>Nomor Rekening untuk Transfer:</strong><br>
                            Bank BCA: 123-456-7890 a.n. Bendahara RT<br>
                            Bank Mandiri: 987-654-3210 a.n. Bendahara RT
                        </div>
                        
                        <button type="submit" class="btn btn-gradient w-100">
                            <i class="fas fa-paper-plane"></i> Ajukan Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Informasi Iuran -->
        <div class="col-md-6 mb-4">
            <div class="card animate__animated animate__fadeInRight">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Iuran Aktif</h5>
                </div>
                <div class="card-body">
                    @foreach($iuran as $item)
                    <div class="border-bottom mb-3 pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $item->nama_iuran }}</h6>
                                <small class="text-muted">
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
                                <h5 class="text-primary mb-0">Rp {{ number_format($item->nominal, 0, ',', '.') }}</h5>
                                <small class="text-muted">per {{ $item->periode }}</small>
                            </div>
                        </div>
                        @if($item->keterangan)
                            <small class="text-muted">{{ $item->keterangan }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Cara Bayar -->
            <div class="card mt-4 animate__animated animate__fadeInRight animate__delay-1s">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Cara Pembayaran</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Pilih jenis iuran yang akan dibayar</li>
                        <li>Pilih metode pembayaran (Tunai atau Transfer)</li>
                        <li>Jika Transfer, upload bukti transfer</li>
                        <li>Klik "Ajukan Pembayaran"</li>
                        <li>Tunggu verifikasi dari Bendahara</li>
                        <li>Cek status pembayaran di menu "Riwayat Pembayaran"</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Riwayat Pembayaran -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card animate__animated animate__fadeInUp">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Pembayaran Saya</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Jenis Iuran</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pembayaran as $item)
                                <tr>
                                    <td><strong>{{ $item->no_transaksi }}</strong><br>
                                        <small class="text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>{{ $item->iuran->nama_iuran }}</td>
                                    <td>{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                                    <td class="text-success">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td>
                                        @if($item->metode_bayar == 'tunai')
                                            <span class="badge bg-info">Tunai</span>
                                        @else
                                            <span class="badge bg-primary">Transfer</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status == 'lunas')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Lunas
                                            </span>
                                        @elseif($item->status == 'pending')
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Menunggu Verifikasi
                                            </span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->bukti_transfer)
                                            <a href="{{ $item->bukti_transfer_url }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-image"></i> Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p>Belum ada riwayat pembayaran</p>
                                            <a href="{{ route('pembayaran.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Bayar Iuran Sekarang
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto fill jumlah bayar
    $('#iuran_id').on('change', function() {
        var nominal = $(this).find(':selected').data('nominal');
        $('#jumlah_bayar').val(nominal);
    });
    
    // Tampilkan input bukti transfer jika metode transfer
    $('#metode_bayar').on('change', function() {
        if ($(this).val() == 'transfer') {
            $('#buktiTransferDiv').slideDown();
            $('#bukti_transfer').prop('required', true);
        } else {
            $('#buktiTransferDiv').slideUp();
            $('#bukti_transfer').prop('required', false);
            $('#previewBukti').html('');
        }
    });
    
    // Preview bukti transfer
    $('#bukti_transfer').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#previewBukti').html('<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; margin-top: 10px;" class="border rounded">');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection