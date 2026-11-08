@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3 umkm-page">
    <header class="umkm-head mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h2 class="h4 mb-1 fw-bold text-warning"><i class="fas fa-store me-2"></i>UMKM & Usaha Warga @if(isset($myRtId) && $isRt)<span class="badge bg-warning-subtle text-dark fs-6 ms-1">RT {{ str_pad($myRtId, 2, '0', STR_PAD_LEFT) }}</span>@endif</h2>
            <p class="text-muted small mb-0">Pemberdayaan potensi ekonomi warga lokal, etalase produk, dan promosi usaha mikro.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
        </div>
    </header>

    @if(session('success'))<div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2 small mb-3">{{ $errors->first() }}</div>@endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalToko"><i class="fas fa-store me-1"></i>{{ $tokoSaya ? 'Kelola Toko Saya' : 'Daftarkan Toko Saya' }}</button>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalProduk"><i class="fas fa-plus me-1"></i>Tambah Produk/Jasa</button>
        @if($isPengurus)
            <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalTambahUmkmWarga"><i class="fas fa-user-plus me-1"></i>Daftarkan Usaha Warga</button>
        @endif
    </div>

    <section class="umkm-summary mb-3">
        <article class="p-3 rounded border bg-card d-flex align-items-center gap-3">
            <i class="fas fa-box-open fa-2x text-warning"></i>
            <div>
                <strong class="fs-5 d-block text-warning">{{ $produk->count() }}</strong>
                <span class="text-muted small">Produk Aktif di Katalog</span>
            </div>
        </article>
        <article class="p-3 rounded border bg-card d-flex align-items-center gap-3">
            <i class="fas fa-shop fa-2x text-success"></i>
            <div>
                <strong class="fs-5 d-block text-success">{{ $tokoSaya ? 'Aktif Terdaftar' : 'Belum Terdaftar' }}</strong>
                <span class="text-muted small">Status Toko Saya</span>
            </div>
        </article>
        <article class="p-3 rounded border bg-card d-flex align-items-center gap-3">
            <i class="fas fa-layer-group fa-2x text-primary"></i>
            <div>
                <strong class="fs-5 d-block text-primary">{{ $kategoriProduk->count() }} Kategori</strong>
                <span class="text-muted small">Kategori Usaha</span>
            </div>
        </article>
        @if($isPengurus)
            <article class="p-3 rounded border bg-card d-flex align-items-center gap-3">
                <i class="fas fa-hourglass-half fa-2x text-danger"></i>
                <div>
                    <strong class="fs-5 d-block text-danger">{{ $produkPending->count() }} Produk</strong>
                    <span class="text-muted small">Perlu Moderasi RT/RW</span>
                </div>
            </article>
        @endif
    </section>

    <nav class="nav nav-tabs mb-3" role="tablist">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabKatalog" type="button"><i class="fas fa-store me-1"></i>Katalog Produk & Jasa</button>
        @if($isPengurus)
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabKelolaUmkm" type="button">
                <i class="fas fa-briefcase me-1"></i>Daftar Toko/UMKM RT <span class="badge bg-secondary ms-1">{{ $umkmsRt->count() }}</span>
            </button>
        @endif
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabProdukSaya" type="button">
            <i class="fas fa-boxes-stacked me-1"></i>{{ $isPengurus ? 'Kelola Semua Produk' : 'Produk Saya' }}
            <span class="badge bg-secondary ms-1">{{ $produkSaya->count() }}</span>
        </button>
        @if($isPengurus)
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabModerasi" type="button">
                <i class="fas fa-shield-halved me-1"></i>Moderasi Produk
                @if($produkPending->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $produkPending->count() }}</span>
                @endif
            </button>
        @endif
    </nav>

    <div class="tab-content">
        {{-- TAB 1: KATALOG --}}
        <div class="tab-pane fade show active" id="tabKatalog">
            <div class="card border mb-3">
                <div class="card-body p-3">
                    <form class="row g-2 align-items-center" method="GET" action="{{ route('layanan.umkm.index') }}">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari produk, jasa, atau nama toko...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" name="kategori">
                                <option value="">Semua Kategori</option>
                                @foreach($kategoriProduk as $kat)
                                    <option value="{{ $kat }}" @selected(request('kategori') === $kat)>{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" name="rt_id">
                                <option value="">Semua RT</option>
                                @foreach($rts as $rtItem)
                                    <option value="{{ $rtItem->id }}" @selected(request('rt_id') == $rtItem->id)>RT {{ $rtItem->kode_rt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button class="btn btn-sm btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filter</button>
                            @if(request()->filled('q') || request()->filled('kategori') || request()->filled('rt_id'))
                                <a href="{{ route('layanan.umkm.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3">
                @forelse($produk as $item)
                    @php
                        $nomorWhatsapp = preg_replace('/[^0-9]/', '', $item->umkm->whatsapp ?? '');
                        if (str_starts_with($nomorWhatsapp, '0')) $nomorWhatsapp = '62' . substr($nomorWhatsapp, 1);
                        $rtKodeItem = $item->umkm?->rt?->kode_rt ?? ($item->umkm?->pemilik?->getRtKode() ?? null);
                    @endphp
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="card h-100 border shadow-sm product-card">
                            <div class="product-thumb position-relative" style="height: 180px; overflow: hidden; background: #1e293b;">
                                @if($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama_produk }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 d-grid place-content-center text-muted"><i class="fas fa-box-open fa-3x"></i></div>
                                @endif
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">{{ $item->kategori }}</span>
                                @if($rtKodeItem)
                                    <span class="badge bg-dark bg-opacity-75 position-absolute top-0 end-0 m-2">RT {{ $rtKodeItem }}</span>
                                @endif
                            </div>
                            <div class="card-body d-flex flex-column p-3">
                                <h6 class="fw-bold mb-1 text-truncate" title="{{ $item->nama_produk }}">{{ $item->nama_produk }}</h6>
                                <p class="small text-muted mb-2 text-truncate"><i class="fas fa-store me-1"></i>{{ $item->umkm->nama_usaha ?? 'UMKM Warga' }}</p>
                                <p class="small text-secondary mb-3 flex-grow-1 line-clamp-2" style="font-size: 0.8rem;">{{ str($item->deskripsi)->limit(70) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <strong class="text-success fs-6">Rp {{ number_format($item->harga, 0, ',', '.') }}<small class="text-muted fw-normal">/{{ $item->satuan ?: 'pcs' }}</small></strong>
                                    @if($nomorWhatsapp)
                                        <a href="https://wa.me/{{ $nomorWhatsapp }}?text={{ urlencode('Halo, saya tertarik dengan produk ' . $item->nama_produk . ' yang ada di RW Smart.') }}" target="_blank" class="btn btn-xs btn-success"><i class="fab fa-whatsapp me-1"></i>Pesan</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><div class="alert alert-light text-center py-5 text-muted"><i class="fas fa-store-slash fa-2x mb-2 d-block"></i>Belum ada produk aktif yang sesuai filter.</div></div>
                @endforelse
            </div>
        </div>

        {{-- TAB 2: DAFTAR TOKO / UMKM RT (KHUSUS PENGURUS) --}}
        @if($isPengurus)
            <div class="tab-pane fade" id="tabKelolaUmkm">
                <div class="card border">
                    <div class="card-header bg-card d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold text-warning"><i class="fas fa-briefcase me-2"></i>Daftar Unit Usaha UMKM Warga</h6>
                        <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalTambahUmkmWarga"><i class="fas fa-plus me-1"></i>Tambah UMKM Warga</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Usaha</th>
                                    <th>Pemilik Warga</th>
                                    <th>Wilayah</th>
                                    <th>Kategori Usaha</th>
                                    <th>WhatsApp</th>
                                    <th>Total Produk</th>
                                    <th>Status Toko</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($umkmsRt as $u)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $u->nama_usaha }}</div>
                                            <small class="text-muted">{{ $u->alamat ?: 'Alamat belum diatur' }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $u->pemilik->name ?? '-' }}</div>
                                            <small class="text-muted">NIK: {{ $u->pemilik->profile->nik ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">RT {{ $u->rt->kode_rt ?? ($u->pemilik?->getRtKode() ?? '-') }}</span>
                                        </td>
                                        <td><span class="badge bg-info-subtle text-info">{{ $u->kategori }}</span></td>
                                        <td>{{ $u->whatsapp ?: '-' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $u->produk->count() }} Item</span></td>
                                        <td>
                                            <span class="badge bg-{{ $u->status === 'aktif' ? 'success' : 'danger' }}">{{ ucfirst($u->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('layanan.umkm.toggle', $u) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-xs btn-outline-{{ $u->status === 'aktif' ? 'danger' : 'success' }}" title="Toggle Status Aktif/Nonaktif">
                                                    {{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada profil usaha warga yang didaftarkan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- TAB 3: PRODUK SAYA / KELOLA SEMUA PRODUK --}}
        <div class="tab-pane fade" id="tabProdukSaya">
            <div class="card border">
                <div class="card-header bg-card d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-boxes-stacked me-2"></i>{{ $isPengurus ? 'Daftar Semua Produk UMKM' : 'Produk di Toko Saya' }}</h6>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalProduk"><i class="fas fa-plus me-1"></i>Tambah Produk Baru</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                @if($isPengurus)<th>Toko / Pemilik</th>@endif
                                <th>Harga & Satuan</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produkSaya as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $item->nama_produk }}</div>
                                        <small class="text-muted">{{ $item->kategori }} · {{ str($item->deskripsi)->limit(40) }}</small>
                                    </td>
                                    @if($isPengurus)
                                        <td>
                                            <div>{{ $item->umkm->nama_usaha ?? '-' }}</div>
                                            <small class="text-muted">{{ $item->umkm->pemilik->name ?? '-' }} (RT {{ $item->umkm?->rt?->kode_rt ?? '-' }})</small>
                                        </td>
                                    @endif
                                    <td class="fw-semibold text-success">Rp {{ number_format($item->harga, 0, ',', '.') }}<small class="text-muted">/{{ $item->satuan ?: 'pcs' }}</small></td>
                                    <td>{{ $item->stok ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'aktif' ? 'success' : ($item->status === 'ditolak' ? 'danger' : 'warning text-dark') }}">{{ ucfirst($item->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-xs btn-outline-warning text-dark edit-product-btn" data-bs-toggle="modal" data-bs-target="#modalEditProduk" data-action="{{ route('layanan.umkm.produk.update', $item) }}" data-name="{{ $item->nama_produk }}" data-category="{{ $item->kategori }}" data-price="{{ $item->harga }}" data-stock="{{ $item->stok }}" data-unit="{{ $item->satuan }}" data-description="{{ $item->deskripsi }}"><i class="fas fa-edit me-1"></i>Edit</button>
                                            <form method="POST" action="{{ route('layanan.umkm.produk.destroy', $item) }}" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $isPengurus ? 6 : 5 }}" class="text-center py-4 text-muted">Belum ada produk yang didaftarkan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: MODERASI (KHUSUS PENGURUS) --}}
        @if($isPengurus)
            <div class="tab-pane fade" id="tabModerasi">
                <div class="card border">
                    <div class="card-header bg-card d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold text-danger"><i class="fas fa-shield-halved me-2"></i>Produk Warga Menunggu Verifikasi / Moderasi</h6>
                        <span class="badge bg-danger">{{ $produkPending->count() }} Menunggu</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Toko & Pemilik</th>
                                    <th>Harga</th>
                                    <th>Deskripsi</th>
                                    <th class="text-end" style="min-width: 180px;">Aksi Moderasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($produkPending as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $item->nama_produk }}</div>
                                            <span class="badge bg-info-subtle text-info">{{ $item->kategori }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $item->umkm->nama_usaha ?? '-' }}</div>
                                            <small class="text-muted">{{ $item->umkm->pemilik->name ?? '-' }} (RT {{ $item->umkm?->rt?->kode_rt ?? '-' }})</small>
                                        </td>
                                        <td class="fw-semibold text-success">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                        <td class="small text-muted" style="max-width: 280px;">{{ $item->deskripsi ?: '-' }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('layanan.umkm.produk.moderate', $item) }}" class="d-inline-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="status" value="aktif" class="btn btn-sm btn-success" title="Setujui dan Terbitkan"><i class="fas fa-check me-1"></i>Setujui</button>
                                                <button type="submit" name="status" value="ditolak" class="btn btn-sm btn-danger" title="Tolak"><i class="fas fa-times me-1"></i>Tolak</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada produk yang menunggu moderasi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL TOKO SAYA (WARGA) --}}
    <div class="modal fade" id="modalToko" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('layanan.umkm.toko.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-primary"><i class="fas fa-store me-1"></i>{{ $tokoSaya ? 'Kelola Toko Saya' : 'Daftarkan Usaha Saya' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Usaha / Toko <span class="text-danger">*</span></label>
                            <input class="form-control" name="nama_usaha" value="{{ old('nama_usaha', $tokoSaya->nama_usaha ?? '') }}" placeholder="Contoh: Warung Berkah Bu Siti" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kategori Usaha <span class="text-danger">*</span></label>
                            <input class="form-control" name="kategori" value="{{ old('kategori', $tokoSaya->kategori ?? '') }}" placeholder="Contoh: Kuliner / Jasa / Sembako / Jahit" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">No. WhatsApp Pemesanan</label>
                            <input class="form-control" name="whatsapp" value="{{ old('whatsapp', $tokoSaya->whatsapp ?? '') }}" placeholder="Contoh: 081234567890">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alamat / Lokasi Usaha</label>
                            <textarea class="form-control" name="alamat" rows="2" placeholder="Contoh: Jl. Mawar No. 12 RT 01">{{ old('alamat', $tokoSaya->alamat ?? '') }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Deskripsi Singkat Usaha</label>
                            <textarea class="form-control" name="deskripsi" rows="2" placeholder="Jelaskan produk atau jasa yang ditawarkan...">{{ old('deskripsi', $tokoSaya->deskripsi ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Simpan Profil Toko</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH PRODUK --}}
    <div class="modal fade" id="modalProduk" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('layanan.umkm.produk.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-success"><i class="fas fa-plus me-1"></i>Tambah Produk / Jasa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Produk / Jasa <span class="text-danger">*</span></label>
                            <input class="form-control" name="nama_produk" placeholder="Contoh: Nasi Goreng Spesial / Jasa Jahit Celana" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <input class="form-control" name="kategori" placeholder="Contoh: Kuliner" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="harga" min="0" placeholder="Contoh: 15000" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Stok (Opsional)</label>
                                <input class="form-control" type="number" name="stok" min="0" placeholder="Contoh: 20">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Satuan</label>
                                <input class="form-control" name="satuan" placeholder="Contoh: porsi / pcs / jam">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Foto Produk</label>
                            <input class="form-control" type="file" name="foto" accept="image/*">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Deskripsi Produk</label>
                            <textarea class="form-control" name="deskripsi" rows="2" placeholder="Penjelasan rasa, varian, atau ketentuan layanan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-paper-plane me-1"></i>Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT PRODUK --}}
    <div class="modal fade" id="modalEditProduk" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-warning"><i class="fas fa-edit me-1"></i>Edit Produk / Jasa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Produk / Jasa <span class="text-danger">*</span></label>
                            <input id="editProductName" class="form-control" name="nama_produk" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <input id="editProductCategory" class="form-control" name="kategori" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <input id="editProductPrice" class="form-control" type="number" name="harga" min="0" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Stok</label>
                                <input id="editProductStock" class="form-control" type="number" name="stok" min="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Satuan</label>
                                <input id="editProductUnit" class="form-control" name="satuan">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Ganti Foto Produk <small class="text-muted">(Opsional)</small></label>
                            <input class="form-control" type="file" name="foto" accept="image/*">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Deskripsi</label>
                            <textarea id="editProductDescription" class="form-control" name="deskripsi" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH UMKM OLEH PENGURUS RT/RW --}}
    @if($isPengurus)
        <div class="modal fade" id="modalTambahUmkmWarga" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('layanan.umkm.pengurus.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-warning"><i class="fas fa-user-plus me-1"></i>Daftarkan Usaha Warga RT</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Warga Pemilik Usaha <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">-- Pilih Warga --</option>
                                    @foreach($wargasRt as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} (NIK: {{ $w->profile->nik ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nama Usaha / Toko <span class="text-danger">*</span></label>
                                <input type="text" name="nama_usaha" class="form-control" placeholder="Contoh: Toko Kelontong Berkah" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kategori Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="kategori" class="form-control" placeholder="Contoh: Sembako / Kuliner / Jasa" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nomor WhatsApp Pemesanan</label>
                                <input type="text" name="whatsapp" class="form-control" placeholder="Contoh: 081234567890">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Alamat Tempat Usaha</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Contoh: Jl. Kenanga No. 5 RT 01"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Deskripsi Singkat Usaha</label>
                                <textarea name="deskripsi" class="form-control" rows="2" placeholder="Keterangan mengenai usaha warga..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-save me-1"></i>Daftarkan Usaha Warga</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.umkm-page .umkm-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
}
.btn-xs {
    padding: 0.18rem 0.45rem;
    font-size: 0.75rem;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-product-btn').forEach(button => {
        button.addEventListener('click', () => {
            const form = document.getElementById('editProductForm');
            if (form) {
                form.action = button.dataset.action;
                document.getElementById('editProductName').value = button.dataset.name || '';
                document.getElementById('editProductCategory').value = button.dataset.category || '';
                document.getElementById('editProductPrice').value = button.dataset.price || '';
                document.getElementById('editProductStock').value = button.dataset.stock || '';
                document.getElementById('editProductUnit').value = button.dataset.unit || '';
                document.getElementById('editProductDescription').value = button.dataset.description || '';
            }
        });
    });
});
</script>
@endpush
@endsection
