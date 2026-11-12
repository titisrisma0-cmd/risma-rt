@extends('layouts.app')

@section('title', 'Konfigurasi Metode Pembayaran')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-wallet text-emerald me-2"></i> Metode Pembayaran Digital RW
            </h1>
            <p class="page-subtitle">Kelola konfigurasi rekening bank, e-wallet (DANA, GoPay, OVO, ShopeePay), dan QRIS untuk pembayaran warga</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary px-3 py-2">
                <i class="fas fa-arrow-left me-1"></i> Data Pembayaran
            </a>
            <button type="button" class="btn btn-emerald px-3 py-2 text-white" data-bs-toggle="modal" data-bs-target="#modalTambahMetode">
                <i class="fas fa-plus me-1"></i> Tambah Metode Baru
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="saas-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium">Total Metode</div>
                        <div class="h3 fw-bold text-dark mb-0">{{ $metodes->count() }}</div>
                    </div>
                    <div class="p-3 bg-light rounded-circle text-primary">
                        <i class="fas fa-credit-card fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="saas-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium">Status Aktif</div>
                        <div class="h3 fw-bold text-emerald mb-0">{{ $metodes->where('is_active', true)->count() }}</div>
                    </div>
                    <div class="p-3 bg-light rounded-circle text-emerald">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="saas-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium">Nonaktif</div>
                        <div class="h3 fw-bold text-muted mb-0">{{ $metodes->where('is_active', false)->count() }}</div>
                    </div>
                    <div class="p-3 bg-light rounded-circle text-secondary">
                        <i class="fas fa-ban fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="saas-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium">QRIS & E-Wallet</div>
                        <div class="h3 fw-bold text-info mb-0">{{ $metodes->whereIn('kategori', ['qris', 'ewallet'])->count() }}</div>
                    </div>
                    <div class="p-3 bg-light rounded-circle text-info">
                        <i class="fas fa-qrcode fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Metode Pembayaran --}}
    <div class="saas-card overflow-hidden mb-4">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-emerald me-2"></i>Daftar Konfigurasi Pembayaran</h6>
            <span class="badge bg-light text-secondary border">Hanya metode aktif yang muncul pada form warga</span>
        </div>

        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>Metode & Kategori</th>
                        <th>Nomor Akun / Rekening</th>
                        <th>Nama Penerima (a.n.)</th>
                        <th class="text-center">QR Code</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metodes as $idx => $m)
                        <tr>
                            <td class="text-center fw-medium text-secondary">{{ $idx + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-2 rounded bg-light border text-center" style="width: 40px; height: 40px;">
                                        @if($m->kategori === 'qris')
                                            <i class="fas fa-qrcode text-dark fs-5"></i>
                                        @elseif($m->kategori === 'ewallet')
                                            <i class="fas fa-mobile-alt text-emerald fs-5"></i>
                                        @elseif($m->kategori === 'bank')
                                            <i class="fas fa-university text-primary fs-5"></i>
                                        @else
                                            <i class="fas fa-money-bill-wave text-success fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $m->nama }}</div>
                                        <span class="badge text-uppercase {{ $m->kategori === 'qris' ? 'badge-soft-warning' : ($m->kategori === 'ewallet' ? 'badge-soft-success' : 'badge-soft-info') }}" style="font-size: 11px;">
                                            {{ $m->kategori }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-monospace fw-bold text-dark fs-6">{{ $m->nomor_rekening ?? '-' }}</span>
                                @if($m->instruksi)
                                    <div class="text-truncate text-secondary small" style="max-width: 260px;" title="{{ $m->instruksi }}">
                                        {{ $m->instruksi }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium text-dark">{{ $m->nama_penerima ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($m->qr_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($m->qr_image))
                                    <a href="{{ asset('storage/' . $m->qr_image) }}" target="_blank" title="Lihat QR Code">
                                        <img src="{{ asset('storage/' . $m->qr_image) }}" alt="QR" class="rounded border p-1" style="width: 42px; height: 42px; object-fit: contain;">
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('rw.metode-pembayaran.toggle', $m) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $m->is_active ? 'btn-success' : 'btn-outline-secondary' }} px-3 rounded-pill" title="Klik untuk mengubah status aktif/nonaktif">
                                        <i class="fas {{ $m->is_active ? 'fa-check' : 'fa-times' }} me-1"></i>
                                        {{ $m->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditMetode{{ $m->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('rw.metode-pembayaran.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus metode pembayaran ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal Edit Metode --}}
                        <div class="modal fade" id="modalEditMetode{{ $m->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('rw.metode-pembayaran.update', $m) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-light">
                                            <h6 class="modal-title fw-bold text-dark"><i class="fas fa-edit text-emerald me-2"></i>Edit Metode Pembayaran</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-medium">Nama Provider / Bank <span class="text-danger">*</span></label>
                                                <input type="text" name="nama" class="form-control form-control-saas" value="{{ $m->nama }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-medium">Kategori Metode <span class="text-danger">*</span></label>
                                                <select name="kategori" class="form-select form-control-saas" required>
                                                    <option value="ewallet" {{ $m->kategori === 'ewallet' ? 'selected' : '' }}>E-Wallet (DANA, GoPay, OVO, ShopeePay)</option>
                                                    <option value="qris" {{ $m->kategori === 'qris' ? 'selected' : '' }}>QRIS Terintegrasi</option>
                                                    <option value="bank" {{ $m->kategori === 'bank' ? 'selected' : '' }}>Transfer Bank (BCA, Mandiri, BRI, dll)</option>
                                                    <option value="tunai" {{ $m->kategori === 'tunai' ? 'selected' : '' }}>Tunai</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-medium">Nomor Akun / Rekening / No. HP</label>
                                                <input type="text" name="nomor_rekening" class="form-control form-control-saas" value="{{ $m->nomor_rekening }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-medium">Nama Pemilik Akun (Atas Nama)</label>
                                                <input type="text" name="nama_penerima" class="form-control form-control-saas" value="{{ $m->nama_penerima }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-medium">Instruksi Pembayaran</label>
                                                <textarea name="instruksi" rows="2" class="form-control form-control-saas">{{ $m->instruksi }}</textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-medium">Upload Gambar QRIS (Opsional)</label>
                                                <input type="file" name="qr_image" class="form-control form-control-saas" accept="image/*">
                                                @if($m->qr_image)
                                                    <small class="text-emerald d-block mt-1"><i class="fas fa-check me-1"></i>QR Code saat ini sudah terunggah</small>
                                                @endif
                                            </div>

                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck{{ $m->id }}" {{ $m->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium text-dark" for="activeCheck{{ $m->id }}">
                                                    Aktifkan metode ini di formulir pembayaran warga
                                                </label>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-emerald text-white px-4">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="fas fa-wallet fa-3x mb-3 text-muted"></i>
                                <p class="mb-2">Belum ada metode pembayaran yang dikonfigurasi.</p>
                                <button type="button" class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalTambahMetode">
                                    Tambah Metode Pertama
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Metode Baru --}}
<div class="modal fade" id="modalTambahMetode" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('rw.metode-pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle text-emerald me-2"></i>Tambah Metode Pembayaran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Nama Provider / Bank <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control form-control-saas" placeholder="Contoh: DANA / QRIS RW / BCA" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Kategori Metode <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select form-control-saas" required>
                            <option value="ewallet">E-Wallet (DANA, GoPay, OVO, ShopeePay)</option>
                            <option value="qris">QRIS Terintegrasi</option>
                            <option value="bank">Transfer Bank (BCA, Mandiri, BRI, dll)</option>
                            <option value="tunai">Tunai</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Nomor Akun / Rekening / No. HP</label>
                        <input type="text" name="nomor_rekening" class="form-control form-control-saas" placeholder="Contoh: 081234567890 / 8830129841">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Nama Pemilik Akun (Atas Nama)</label>
                        <input type="text" name="nama_penerima" class="form-control form-control-saas" placeholder="Contoh: Bendahara RW 05">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Instruksi Pembayaran</label>
                        <textarea name="instruksi" rows="2" class="form-control form-control-saas" placeholder="Contoh: Cantumkan nama dan RT pada catatan transfer..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Upload Gambar QRIS (Khusus QRIS / E-Wallet)</label>
                        <input type="file" name="qr_image" class="form-control form-control-saas" accept="image/*">
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheckNew" checked>
                        <label class="form-check-label fw-medium text-dark" for="activeCheckNew">
                            Langsung aktifkan metode ini di formulir pembayaran
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald text-white px-4">Simpan Metode</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
