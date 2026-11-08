@extends('layouts.app')

@section('title', 'UMKM & Usaha Warga')

@section('content')
<div class="content-container umkm-page">
    <header class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1">
                <i class="fas fa-store text-emerald me-2"></i>UMKM & Usaha Warga 
                @if(isset($myRtId) && $isRt)
                    <span class="badge-soft-emerald fs-6 ms-1">RT {{ str_pad($myRtId, 2, '0', STR_PAD_LEFT) }}</span>
                @endif
            </h1>
            <p class="page-subtitle">Pemberdayaan potensi ekonomi warga lokal, etalase produk, dan promosi usaha mikro.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-4">
        <button class="btn btn-emerald text-white px-3" data-bs-toggle="modal" data-bs-target="#modalToko"><i class="fas fa-store me-1"></i>{{ $tokoSaya ? 'Kelola Toko Saya' : 'Daftarkan Toko Saya' }}</button>
        <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#modalProduk"><i class="fas fa-plus me-1 text-emerald"></i>Tambah Produk/Jasa</button>
        @if($isPengurus)
            <button class="btn btn-outline-warning text-dark px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahUmkmWarga"><i class="fas fa-user-plus me-1"></i>Daftarkan Usaha Warga</button>
        @endif
    </div>

    <section class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Produk Aktif</span>
                        <h3 class="stat-value text-emerald mb-0">{{ $produk->count() }}</h3>
                        <small class="text-secondary">Tersedia di Katalog</small>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Status Toko Saya</span>
                        <h3 class="stat-value text-dark mb-0 fs-5">{{ $tokoSaya ? 'Aktif Terdaftar' : 'Belum Terdaftar' }}</h3>
                        <small class="text-secondary">{{ $tokoSaya ? $tokoSaya->nama_usaha : 'Buka Usaha Sekarang' }}</small>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: #FEF3C7; color: #D97706;">
                        <i class="fas fa-shop"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="stat-label">Kategori Usaha</span>
                        <h3 class="stat-value text-info mb-0">{{ $kategoriProduk->count() }}</h3>
                        <small class="text-secondary">Variasi Produk & Jasa</small>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: #E0F2FE; color: #0284C7;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
        @if($isPengurus)
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Perlu Moderasi</span>
                            <h3 class="stat-value text-danger mb-0">{{ $produkPending->count() }}</h3>
                            <small class="text-secondary">Menunggu Verifikasi</small>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: #FEE2E2; color: #DC2626;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <nav class="nav nav-tabs-saas mb-4" role="tablist">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabKatalog" type="button"><i class="fas fa-store me-1"></i>Katalog Produk & Jasa</button>
        @if($isPengurus)
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabKelolaUmkm" type="button">
                <i class="fas fa-briefcase me-1"></i>Daftar Toko/UMKM RT <span class="badge-soft-secondary ms-1">{{ $umkmsRt->count() }}</span>
            </button>
        @endif
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabProdukSaya" type="button">
            <i class="fas fa-boxes-stacked me-1"></i>{{ $isPengurus ? 'Kelola Semua Produk' : 'Produk Saya' }}
            <span class="badge-soft-secondary ms-1">{{ $produkSaya->count() }}</span>
        </button>
        @if($isPengurus)
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabModerasi" type="button">
                <i class="fas fa-shield-halved me-1"></i>Moderasi Produk
                @if($produkPending->count() > 0)
                    <span class="badge-soft-danger ms-1">{{ $produkPending->count() }}</span>
                @endif
            </button>
        @endif
    </nav>

    <div class="tab-content">
        {{-- TAB 1: KATALOG --}}
        <div class="tab-pane fade show active" id="tabKatalog">
            <div class="saas-card p-3 mb-4">
                <form class="row g-2 align-items-center" method="GET" action="{{ route('layanan.umkm.index') }}">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input class="form-control form-control-saas border-start-0" name="q" value="{{ request('q') }}" placeholder="Cari produk, jasa, atau nama toko...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-control-saas" name="kategori">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoriProduk as $kat)
                                <option value="{{ $kat }}" @selected(request('kategori') === $kat)>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-control-saas" name="rt_id">
                            <option value="">Semua RT</option>
                            @foreach($rts as $rtItem)
                                <option value="{{ $rtItem->id }}" @selected(request('rt_id') == $rtItem->id)>RT {{ $rtItem->kode_rt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button class="btn btn-emerald text-white flex-grow-1"><i class="fas fa-filter me-1"></i>Filter</button>
                        @if(request()->filled('q') || request()->filled('kategori') || request()->filled('rt_id'))
                            <a href="{{ route('layanan.umkm.index') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="row g-3">
                @forelse($produk as $item)
                    @php
                        $nomorWhatsapp = preg_replace('/[^0-9]/', '', $item->umkm->whatsapp ?? '');
                        if (str_starts_with($nomorWhatsapp, '0')) $nomorWhatsapp = '62' . substr($nomorWhatsapp, 1);
                        $rtKodeItem = $item->umkm?->rt?->kode_rt ?? ($item->umkm?->pemilik?->getRtKode() ?? null);
                    @endphp
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="saas-card h-100 overflow-hidden d-flex flex-column justify-content-between">
                            <div>
                                <div class="position-relative bg-light" style="height: 180px; overflow: hidden;">
                                    @if($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama_produk }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fas fa-box-open fa-3x opacity-50"></i></div>
                                    @endif
                                    <span class="badge-soft-warning position-absolute top-0 start-0 m-2">{{ $item->kategori }}</span>
                                    @if($rtKodeItem)
                                        <span class="badge bg-dark bg-opacity-75 position-absolute top-0 end-0 m-2">RT {{ $rtKodeItem }}</span>
                                    @endif
                                </div>
                                <div class="p-3">
                                    <h6 class="fw-bold mb-1 text-dark text-truncate" title="{{ $item->nama_produk }}">{{ $item->nama_produk }}</h6>
                                    <p class="small text-secondary mb-2 text-truncate"><i class="fas fa-store me-1 text-emerald"></i>{{ $item->umkm->nama_usaha ?? 'UMKM Warga' }}</p>
                                    <p class="small text-secondary mb-3 flex-grow-1" style="font-size: 0.85rem; min-height: 40px;">{{ str($item->deskripsi)->limit(70) }}</p>
                                </div>
                            </div>
                            <div class="p-3 border-top d-flex justify-content-between align-items-center bg-white">
                                <strong class="text-emerald fs-6">Rp {{ number_format($item->harga, 0, ',', '.') }}<small class="text-muted fw-normal">/{{ $item->satuan ?: 'pcs' }}</small></strong>
                                @if($nomorWhatsapp)
                                    <a href="https://wa.me/{{ $nomorWhatsapp }}?text={{ urlencode('Halo, saya tertarik dengan produk ' . $item->nama_produk . ' yang ada di RW Smart.') }}" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="fab fa-whatsapp me-1"></i>Pesan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="saas-card text-center py-5 text-secondary">
                            <i class="fas fa-store-slash fa-3x mb-3 text-muted opacity-50 d-block"></i>
                            Belum ada produk aktif yang sesuai filter pencarian.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 2: DAFTAR TOKO / UMKM RT (KHUSUS PENGURUS) --}}
        @if($isPengurus)
            <div class="tab-pane fade" id="tabKelolaUmkm">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-briefcase text-emerald me-2"></i>Daftar Unit Usaha UMKM Warga</h6>
                        <button class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalTambahUmkmWarga"><i class="fas fa-plus me-1"></i>Tambah UMKM Warga</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
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
                                            <div class="fw-bold text-dark">{{ $u->nama_usaha }}</div>
                                            <small class="text-secondary">{{ $u->alamat ?: 'Alamat belum diatur' }}</small>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $u->pemilik->name ?? '-' }}</div>
                                            <small class="text-secondary">NIK: {{ $u->pemilik->profile->nik ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge-soft-info">RT {{ $u->rt->kode_rt ?? ($u->pemilik?->getRtKode() ?? '-') }}</span>
                                        </td>
                                        <td><span class="badge-soft-secondary">{{ $u->kategori }}</span></td>
                                        <td class="text-secondary">{{ $u->whatsapp ?: '-' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $u->produk->count() }} Item</span></td>
                                        <td>
                                            <span class="{{ $u->status === 'aktif' ? 'badge-soft-emerald' : 'badge-soft-danger' }}">{{ ucfirst($u->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('layanan.umkm.toggle', $u) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $u->status === 'aktif' ? 'danger' : 'success' }}" title="Toggle Status Aktif/Nonaktif">
                                                    {{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-5 text-secondary">Belum ada profil usaha warga yang didaftarkan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- TAB 3: PRODUK SAYA / KELOLA SEMUA PRODUK --}}
        <div class="tab-pane fade" id="tabProdukSaya">
            <div class="saas-card overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-boxes-stacked text-emerald me-2"></i>{{ $isPengurus ? 'Daftar Semua Produk UMKM' : 'Produk di Toko Saya' }}</h6>
                    <button class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalProduk"><i class="fas fa-plus me-1"></i>Tambah Produk Baru</button>
                </div>
                <div class="table-responsive">
                    <table class="table-saas">
                        <thead>
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
                                        <div class="fw-bold text-dark">{{ $item->nama_produk }}</div>
                                        <small class="text-secondary">{{ $item->kategori }} · {{ str($item->deskripsi)->limit(40) }}</small>
                                    </td>
                                    @if($isPengurus)
                                        <td>
                                            <div class="text-dark">{{ $item->umkm->nama_usaha ?? '-' }}</div>
                                            <small class="text-secondary">{{ $item->umkm->pemilik->name ?? '-' }} (RT {{ $item->umkm?->rt?->kode_rt ?? '-' }})</small>
                                        </td>
                                    @endif
                                    <td class="fw-bold text-emerald">Rp {{ number_format($item->harga, 0, ',', '.') }}<small class="text-muted fw-normal">/{{ $item->satuan ?: 'pcs' }}</small></td>
                                    <td class="text-secondary">{{ $item->stok ?? '-' }}</td>
                                    <td>
                                        @if($item->status === 'aktif')
                                            <span class="badge-soft-emerald">Aktif</span>
                                        @elseif($item->status === 'ditolak')
                                            <span class="badge-soft-danger">Ditolak</span>
                                        @else
                                            <span class="badge-soft-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-warning edit-product-btn" data-bs-toggle="modal" data-bs-target="#modalEditProduk" data-action="{{ route('layanan.umkm.produk.update', $item) }}" data-name="{{ $item->nama_produk }}" data-category="{{ $item->kategori }}" data-price="{{ $item->harga }}" data-stock="{{ $item->stok }}" data-unit="{{ $item->satuan }}" data-description="{{ $item->deskripsi }}"><i class="fas fa-edit"></i></button>
                                            <form method="POST" action="{{ route('layanan.umkm.produk.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $isPengurus ? 6 : 5 }}" class="text-center py-5 text-secondary">Belum ada produk yang didaftarkan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: MODERASI (KHUSUS PENGURUS) --}}
        @if($isPengurus)
            <div class="tab-pane fade" id="tabModerasi">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-shield-halved text-warning me-2"></i>Produk Warga Menunggu Verifikasi / Moderasi</h6>
                        <span class="badge-soft-danger">{{ $produkPending->count() }} Menunggu</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
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
                                            <div class="fw-bold text-dark">{{ $item->nama_produk }}</div>
                                            <span class="badge-soft-info">{{ $item->kategori }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $item->umkm->nama_usaha ?? '-' }}</div>
                                            <small class="text-secondary">{{ $item->umkm->pemilik->name ?? '-' }} (RT {{ $item->umkm?->rt?->kode_rt ?? '-' }})</small>
                                        </td>
                                        <td class="fw-bold text-emerald">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                        <td class="small text-secondary" style="max-width: 280px;">{{ $item->deskripsi ?: '-' }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('layanan.umkm.produk.moderate', $item) }}" class="d-inline-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="status" value="aktif" class="btn btn-sm btn-emerald text-white" title="Setujui dan Terbitkan"><i class="fas fa-check me-1"></i>Setujui</button>
                                                <button type="submit" name="status" value="ditolak" class="btn btn-sm btn-outline-danger" title="Tolak"><i class="fas fa-times me-1"></i>Tolak</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-secondary">Tidak ada produk yang menunggu moderasi.</td></tr>
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
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form method="POST" action="{{ route('layanan.umkm.toko.store') }}">
                    @csrf
                    <div class="modal-header border-bottom bg-white">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-store text-emerald me-2"></i>{{ $tokoSaya ? 'Kelola Toko Saya' : 'Daftarkan Usaha Saya' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Nama Usaha / Toko <span class="text-danger">*</span></label>
                            <input class="form-control form-control-saas" name="nama_usaha" value="{{ old('nama_usaha', $tokoSaya->nama_usaha ?? '') }}" placeholder="Contoh: Warung Berkah Bu Siti" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Kategori Usaha <span class="text-danger">*</span></label>
                            <input class="form-control form-control-saas" name="kategori" value="{{ old('kategori', $tokoSaya->kategori ?? '') }}" placeholder="Contoh: Kuliner / Jasa / Sembako / Jahit" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">No. WhatsApp Pemesanan</label>
                            <input class="form-control form-control-saas" name="whatsapp" value="{{ old('whatsapp', $tokoSaya->whatsapp ?? '') }}" placeholder="Contoh: 081234567890">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Alamat / Lokasi Usaha</label>
                            <textarea class="form-control form-control-saas" name="alamat" rows="2" placeholder="Contoh: Jl. Mawar No. 12 RT 01">{{ old('alamat', $tokoSaya->alamat ?? '') }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Deskripsi Singkat Usaha</label>
                            <textarea class="form-control form-control-saas" name="deskripsi" rows="2" placeholder="Jelaskan produk atau jasa yang ditawarkan...">{{ old('deskripsi', $tokoSaya->deskripsi ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-save me-1"></i>Simpan Profil Toko</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH PRODUK --}}
    <div class="modal fade" id="modalProduk" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form method="POST" action="{{ route('layanan.umkm.produk.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-bottom bg-white">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-plus text-emerald me-2"></i>Tambah Produk / Jasa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Nama Produk / Jasa <span class="text-danger">*</span></label>
                            <input class="form-control form-control-saas" name="nama_produk" placeholder="Contoh: Nasi Goreng Spesial / Jasa Jahit Celana" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Kategori <span class="text-danger">*</span></label>
                                <input class="form-control form-control-saas" name="kategori" placeholder="Contoh: Kuliner" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Harga (Rp) <span class="text-danger">*</span></label>
                                <input class="form-control form-control-saas" type="number" name="harga" min="0" placeholder="Contoh: 15000" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Stok (Opsional)</label>
                                <input class="form-control form-control-saas" type="number" name="stok" min="0" placeholder="Contoh: 20">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Satuan</label>
                                <input class="form-control form-control-saas" name="satuan" placeholder="Contoh: porsi / pcs / jam">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Foto Produk</label>
                            <input class="form-control form-control-saas" type="file" name="foto" accept="image/*">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Deskripsi Produk</label>
                            <textarea class="form-control form-control-saas" name="deskripsi" rows="2" placeholder="Penjelasan rasa, varian, atau ketentuan layanan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-paper-plane me-1"></i>Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT PRODUK --}}
    <div class="modal fade" id="modalEditProduk" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom bg-white">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Produk / Jasa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Nama Produk / Jasa <span class="text-danger">*</span></label>
                            <input id="editProductName" class="form-control form-control-saas" name="nama_produk" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Kategori <span class="text-danger">*</span></label>
                                <input id="editProductCategory" class="form-control form-control-saas" name="kategori" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Harga (Rp) <span class="text-danger">*</span></label>
                                <input id="editProductPrice" class="form-control form-control-saas" type="number" name="harga" min="0" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Stok</label>
                                <input id="editProductStock" class="form-control form-control-saas" type="number" name="stok" min="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Satuan</label>
                                <input id="editProductUnit" class="form-control form-control-saas" name="satuan">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Ganti Foto Produk <small class="text-muted">(Opsional)</small></label>
                            <input class="form-control form-control-saas" type="file" name="foto" accept="image/*">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Deskripsi</label>
                            <textarea id="editProductDescription" class="form-control form-control-saas" name="deskripsi" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH UMKM OLEH PENGURUS RT/RW --}}
    @if($isPengurus)
        <div class="modal fade" id="modalTambahUmkmWarga" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" action="{{ route('layanan.umkm.pengurus.store') }}">
                        @csrf
                        <div class="modal-header border-bottom bg-white">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-user-plus text-emerald me-2"></i>Daftarkan Usaha Warga RT</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Pilih Warga Pemilik Usaha <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select form-control-saas" required>
                                    <option value="">-- Pilih Warga --</option>
                                    @foreach($wargasRt as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} (NIK: {{ $w->profile->nik ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nama Usaha / Toko <span class="text-danger">*</span></label>
                                <input type="text" name="nama_usaha" class="form-control form-control-saas" placeholder="Contoh: Toko Kelontong Berkah" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Kategori Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="kategori" class="form-control form-control-saas" placeholder="Contoh: Sembako / Kuliner / Jasa" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nomor WhatsApp Pemesanan</label>
                                <input type="text" name="whatsapp" class="form-control form-control-saas" placeholder="Contoh: 081234567890">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Alamat Tempat Usaha</label>
                                <textarea name="alamat" class="form-control form-control-saas" rows="2" placeholder="Contoh: Jl. Kenanga No. 5 RT 01"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Deskripsi Singkat Usaha</label>
                                <textarea name="deskripsi" class="form-control form-control-saas" rows="2" placeholder="Keterangan mengenai usaha warga..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-save me-1"></i>Daftarkan Usaha Warga</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

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
