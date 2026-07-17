@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3 umkm-page">
    <header class="umkm-head">
        <div><h2><i class="fas fa-store me-2"></i>UMKM Warga</h2><p>Temukan produk dan jasa warga sekitar.</p></div>
        <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
    </header>

    @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

    <div class="umkm-actions">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalToko"><i class="fas fa-store me-1"></i>{{ $tokoSaya ? 'Kelola Toko' : 'Buat Toko' }}</button>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalProduk"><i class="fas fa-plus me-1"></i>Tambah Produk</button>
    </div>

    <section class="umkm-summary">
        <article><i class="fas fa-box-open orange"></i><div><strong>{{ $produk->count() }}</strong><span>Produk Aktif</span></div></article>
        <article><i class="fas fa-layer-group blue"></i><div><strong>{{ $kategoriProduk->count() }}</strong><span>Kategori</span></div></article>
        <article><i class="fas fa-shop green"></i><div><strong>{{ $tokoSaya ? 'Aktif' : 'Belum' }}</strong><span>Toko Saya</span></div></article>
        @if($isPengurus)
            <article><i class="fas fa-hourglass-half amber"></i><div><strong>{{ $produkPending->count() }}</strong><span>Perlu Moderasi</span></div></article>
        @endif
    </section>

    <nav class="nav nav-tabs umkm-tabs" role="tablist">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabKatalog" type="button">Katalog</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabProdukSaya" type="button">{{ $isPengurus ? 'Kelola Semua Produk' : 'Produk Saya' }} <span class="badge bg-secondary">{{ $produkSaya->count() }}</span></button>
        @if($isPengurus)
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabModerasi" type="button">Moderasi <span class="badge bg-warning text-dark">{{ $produkPending->count() }}</span></button>
        @endif
    </nav>

    <div class="tab-content umkm-tab-content">
        <section class="tab-pane fade show active" id="tabKatalog">
            <div class="umkm-card">
                <form class="umkm-filter" method="GET">
                    <div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-search"></i></span><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari produk, jasa, atau toko"></div>
                    <select class="form-select form-select-sm" name="kategori"><option value="">Semua kategori</option>@foreach($kategoriProduk as $kategori)<option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>@endforeach</select>
                    <button class="btn btn-sm btn-primary">Terapkan</button>
                    @if(request()->filled('q') || request()->filled('kategori'))<a href="{{ route('layanan.umkm.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>@endif
                </form>

                <div class="umkm-product-scroll">
                    <div class="umkm-product-grid">
                        @forelse($produk as $item)
                            @php
                                $nomorWhatsapp = preg_replace('/[^0-9]/', '', $item->umkm->whatsapp ?? '');
                                if (str_starts_with($nomorWhatsapp, '0')) $nomorWhatsapp = '62' . substr($nomorWhatsapp, 1);
                            @endphp
                            <article class="umkm-product">
                                <div class="umkm-photo">@if($item->foto)<img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama_produk }}">@else<i class="fas fa-box-open"></i>@endif</div>
                                <div class="umkm-product-body">
                                    <span class="umkm-category">{{ $item->kategori }}</span>
                                    <h5>{{ $item->nama_produk }}</h5>
                                    <small>{{ $item->umkm->nama_usaha ?? 'UMKM Warga' }}</small>
                                    <strong>Rp {{ number_format($item->harga, 0, ',', '.') }}@if($item->satuan)<em>/{{ $item->satuan }}</em>@endif</strong>
                                    <p>{{ str($item->deskripsi)->limit(65) }}</p>
                                    @if($nomorWhatsapp)<a class="btn btn-sm btn-success" target="_blank" rel="noopener" href="https://wa.me/{{ $nomorWhatsapp }}"><i class="fab fa-whatsapp me-1"></i>Hubungi</a>@endif
                                </div>
                            </article>
                        @empty
                            <div class="umkm-empty"><i class="fas fa-store-slash"></i><span>Belum ada produk yang sesuai.</span></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="tab-pane fade" id="tabProdukSaya">
            <div class="umkm-card">
                <div class="umkm-card-head"><h5><i class="fas fa-boxes-stacked"></i> {{ $isPengurus ? 'Kelola Semua Produk UMKM' : 'Kelola Produk Saya' }}</h5></div>
                <div class="table-responsive umkm-table-scroll"><table class="table table-sm align-middle mb-0"><thead><tr><th>Produk</th>@if($isPengurus)<th>Toko / Pemilik</th>@endif<th>Harga / Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                    @forelse($produkSaya as $item)
                        <tr>
                            <td><strong>{{ $item->nama_produk }}</strong><small>{{ $item->kategori }} · {{ str($item->deskripsi)->limit(45) }}</small></td>
                            @if($isPengurus)<td>{{ $item->umkm->nama_usaha ?? '-' }}<small>{{ $item->umkm->pemilik->name ?? '-' }}</small></td>@endif
                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}<small>Stok: {{ $item->stok ?? '-' }} {{ $item->satuan }}</small></td>
                            <td><span class="badge {{ $item->status === 'aktif' ? 'bg-success' : ($item->status === 'ditolak' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ ucfirst($item->status) }}</span>@if($item->catatan_moderasi)<small>{{ $item->catatan_moderasi }}</small>@endif</td>
                            <td><div class="umkm-product-actions">
                                <button type="button" class="btn btn-sm btn-warning edit-product-btn" data-bs-toggle="modal" data-bs-target="#modalEditProduk" data-action="{{ route('layanan.umkm.produk.update', $item) }}" data-name="{{ $item->nama_produk }}" data-category="{{ $item->kategori }}" data-price="{{ $item->harga }}" data-stock="{{ $item->stok }}" data-unit="{{ $item->satuan }}" data-description="{{ $item->deskripsi }}"><i class="fas fa-pen"></i> Edit</button>
                                <form method="POST" action="{{ route('layanan.umkm.produk.destroy', $item) }}" onsubmit="return confirm('Hapus produk ini? Tindakan ini tidak dapat dibatalkan.')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isPengurus ? 5 : 4 }}" class="text-center py-4">Belum ada produk. Gunakan tombol Tambah Produk.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
        </section>

        @if($isPengurus)
            <section class="tab-pane fade" id="tabModerasi">
                <div class="umkm-card">
                    <div class="umkm-card-head"><h5><i class="fas fa-shield-halved"></i> Produk Menunggu Moderasi</h5><input id="moderationSearch" class="form-control form-control-sm" type="search" placeholder="Cari produk/toko..."></div>
                    <div class="table-responsive umkm-table-scroll"><table class="table table-sm align-middle mb-0" id="moderationTable"><thead><tr><th>Produk</th><th>Toko</th><th>Harga</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody>
                        @forelse($produkPending as $item)
                            <tr><td><strong>{{ $item->nama_produk }}</strong><small>{{ $item->kategori }}</small></td><td>{{ $item->umkm->nama_usaha ?? '-' }}<small>{{ $item->umkm->pemilik->name ?? '-' }}</small></td><td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td><td>{{ str($item->deskripsi)->limit(55) }}</td><td><form method="POST" action="{{ route('layanan.umkm.produk.moderate', $item) }}" class="umkm-moderation">@csrf @method('PATCH')<button name="status" value="aktif" class="btn btn-sm btn-success">Setujui</button><button name="status" value="ditolak" class="btn btn-sm btn-danger">Tolak</button></form></td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center">Tidak ada produk menunggu moderasi.</td></tr>
                        @endforelse
                    </tbody></table></div>
                </div>
            </section>
        @endif
    </div>

    <div class="modal fade" id="modalToko" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">{{ $tokoSaya ? 'Kelola Toko Saya' : 'Buat Toko' }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('layanan.umkm.toko.store') }}">@csrf<div class="modal-body">
        <label class="form-label">Nama Toko/Usaha</label><input class="form-control mb-2" name="nama_usaha" value="{{ old('nama_usaha', $tokoSaya->nama_usaha ?? '') }}" required>
        <label class="form-label">Kategori</label><input class="form-control mb-2" name="kategori" value="{{ old('kategori', $tokoSaya->kategori ?? '') }}" placeholder="Kuliner, Jasa, Jahit" required>
        <label class="form-label">WhatsApp</label><input class="form-control mb-2" name="whatsapp" value="{{ old('whatsapp', $tokoSaya->whatsapp ?? '') }}">
        <label class="form-label">Alamat</label><textarea class="form-control mb-2" name="alamat" rows="2">{{ old('alamat', $tokoSaya->alamat ?? '') }}</textarea>
        <div class="row g-2 mb-2"><div class="col-sm-6"><label class="form-label">Latitude (opsional)</label><input class="form-control" type="number" step="0.0000001" min="-90" max="90" name="latitude" value="{{ old('latitude', $tokoSaya->latitude ?? '') }}"></div><div class="col-sm-6"><label class="form-label">Longitude (opsional)</label><input class="form-control" type="number" step="0.0000001" min="-180" max="180" name="longitude" value="{{ old('longitude', $tokoSaya->longitude ?? '') }}"></div></div>
        <label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="2">{{ old('deskripsi', $tokoSaya->deskripsi ?? '') }}</textarea>
    </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Toko</button></div></form></div></div></div>

    <div class="modal fade" id="modalProduk" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Produk/Jasa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('layanan.umkm.produk.store') }}" enctype="multipart/form-data">@csrf<div class="modal-body">
        <label class="form-label">Nama Produk/Jasa</label><input class="form-control mb-2" name="nama_produk" required>
        <div class="row g-2"><div class="col-sm-6"><label class="form-label">Kategori</label><input class="form-control" name="kategori" required></div><div class="col-sm-6"><label class="form-label">Harga</label><input class="form-control" type="number" name="harga" min="0" required></div></div>
        <div class="row g-2 mt-1"><div class="col-sm-6"><label class="form-label">Stok</label><input class="form-control" type="number" name="stok" min="0"></div><div class="col-sm-6"><label class="form-label">Satuan</label><input class="form-control" name="satuan" placeholder="pcs/porsi/jam"></div></div>
        <label class="form-label mt-2">Foto</label><input class="form-control mb-2" type="file" name="foto" accept="image/*">
        <label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="2"></textarea>
    </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Kirim untuk Moderasi</button></div></form></div></div></div>

    <div class="modal fade" id="modalEditProduk" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Produk/Jasa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="editProductForm" method="POST" enctype="multipart/form-data">@csrf @method('PUT')<div class="modal-body">
        <label class="form-label">Nama Produk/Jasa</label><input id="editProductName" class="form-control mb-2" name="nama_produk" required>
        <div class="row g-2"><div class="col-sm-6"><label class="form-label">Kategori</label><input id="editProductCategory" class="form-control" name="kategori" required></div><div class="col-sm-6"><label class="form-label">Harga</label><input id="editProductPrice" class="form-control" type="number" name="harga" min="0" required></div></div>
        <div class="row g-2 mt-1"><div class="col-sm-6"><label class="form-label">Stok</label><input id="editProductStock" class="form-control" type="number" name="stok" min="0"></div><div class="col-sm-6"><label class="form-label">Satuan</label><input id="editProductUnit" class="form-control" name="satuan" placeholder="pcs/porsi/jam"></div></div>
        <label class="form-label mt-2">Ganti Foto <small>(opsional)</small></label><input class="form-control mb-2" type="file" name="foto" accept="image/*">
        <label class="form-label">Deskripsi</label><textarea id="editProductDescription" class="form-control" name="deskripsi" rows="2"></textarea>
        @unless($isPengurus)<small class="text-muted d-block mt-2">Produk yang diedit akan dikirim ulang untuk moderasi.</small>@endunless
    </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-warning">Simpan Perubahan</button></div></form></div></div></div>
</div>

<style>
    .umkm-page{--u-surface:var(--surface,#10271e);--u-raised:var(--surface-raised,#18382b);--u-text:var(--text-primary,#f8fafc);--u-muted:var(--text-muted,#b8c7be);--u-border:var(--border-color,#526b5c);color:var(--u-text)}
    .umkm-page .umkm-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:10px}.umkm-page .umkm-head h2{margin:0;font-size:1.4rem;color:var(--u-text)}.umkm-page .umkm-head h2 i{color:#fbbf24}.umkm-page .umkm-head p{margin:2px 0 0;color:var(--u-muted);font-size:.82rem}.umkm-page .umkm-actions{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:10px}
    .umkm-page .umkm-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}.umkm-page .umkm-summary article{display:flex;align-items:center;gap:10px;min-height:68px;padding:10px 12px;border:1px solid var(--u-border);border-radius:13px;background:var(--u-surface)}.umkm-page .umkm-summary i{width:34px;height:34px;display:grid;place-items:center;border-radius:9px;background:var(--u-raised)}.umkm-page .umkm-summary .orange{color:#fb923c}.umkm-page .umkm-summary .blue{color:#60a5fa}.umkm-page .umkm-summary .green{color:#4ade80}.umkm-page .umkm-summary .amber{color:#fbbf24}.umkm-page .umkm-summary strong{display:block;color:var(--u-text);font-size:1.15rem;line-height:1.15}.umkm-page .umkm-summary span{color:var(--u-muted);font-size:.7rem}
    .umkm-page .umkm-tabs{border-color:var(--u-border);gap:3px}.umkm-page .umkm-tabs .nav-link{color:var(--u-muted)!important;font-size:.8rem;padding:7px 11px}.umkm-page .umkm-tabs .nav-link.active{background:var(--u-raised)!important;color:var(--u-text)!important;border-color:var(--u-border)!important}.umkm-page .umkm-tab-content{padding-top:10px}
    .umkm-page .umkm-card{padding:12px;border:1px solid var(--u-border);border-radius:13px;background:var(--u-surface);color:var(--u-text);overflow:hidden}.umkm-page .umkm-filter{display:grid;grid-template-columns:minmax(220px,1fr) 210px auto auto;gap:7px;margin-bottom:10px}.umkm-page .umkm-product-scroll{max-height:620px;overflow-y:auto;padding-right:3px}.umkm-page .umkm-product-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.umkm-page .umkm-product{display:grid;grid-template-columns:94px minmax(0,1fr);min-height:145px;border:1px solid var(--u-border);border-radius:11px;background:var(--u-raised);overflow:hidden}.umkm-page .umkm-photo{display:grid;place-items:center;min-height:100%;background:rgba(255,255,255,.05);color:var(--u-muted);font-size:1.5rem}.umkm-page .umkm-photo img{width:100%;height:100%;object-fit:cover}.umkm-page .umkm-product-body{padding:9px;min-width:0}.umkm-page .umkm-product-body h5{margin:3px 0 0;color:var(--u-text);font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.umkm-page .umkm-product-body small{display:block;color:var(--u-muted);font-size:.67rem}.umkm-page .umkm-product-body>strong{display:block;margin:4px 0;color:#4ade80;font-size:.84rem}.umkm-page .umkm-product-body strong em{font-size:.65rem;font-style:normal;color:var(--u-muted)}.umkm-page .umkm-product-body p{height:30px;margin:0 0 5px;color:var(--u-muted);font-size:.68rem;line-height:1.35;overflow:hidden}.umkm-page .umkm-category{display:inline-block;padding:2px 6px;border-radius:999px;background:rgba(251,191,36,.16);color:#fbbf24;font-size:.61rem}.umkm-page .umkm-product .btn{padding:3px 7px;font-size:.67rem}.umkm-page .umkm-empty{grid-column:1/-1;display:grid;place-items:center;gap:8px;min-height:220px;color:var(--u-muted)}
    .umkm-page .umkm-card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px}.umkm-page .umkm-card-head h5{margin:0;color:var(--u-text);font-size:.92rem}.umkm-page .umkm-card-head h5 i{color:var(--accent,#f6d860);margin-right:5px}.umkm-page .umkm-card-head .form-control{width:min(230px,48%)}.umkm-page .umkm-table-scroll{max-height:500px;overflow:auto}.umkm-page .table{color:var(--u-text);font-size:.76rem}.umkm-page .table thead{position:sticky;top:0;z-index:1}.umkm-page .table th{background:var(--u-raised)!important;color:var(--u-text)!important}.umkm-page .table td small{display:block;color:var(--u-muted)}.umkm-page .umkm-moderation,.umkm-page .umkm-product-actions{display:flex;gap:4px;align-items:center}.umkm-page .umkm-product-actions form{margin:0}.umkm-page .modal .form-label{font-size:.77rem;margin-bottom:3px}
    html[data-theme="light"] .umkm-page .umkm-summary .orange{color:#9a3412}html[data-theme="light"] .umkm-page .umkm-summary .blue{color:#1d4ed8}html[data-theme="light"] .umkm-page .umkm-summary .green{color:#166534}html[data-theme="light"] .umkm-page .umkm-summary .amber{color:#92400e}html[data-theme="light"] .umkm-page .umkm-product-body>strong{color:#166534}html[data-theme="light"] .umkm-page .umkm-category{color:#854d0e}
    @media(max-width:1200px){.umkm-page .umkm-product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.umkm-page .umkm-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:768px){.umkm-page .umkm-filter{grid-template-columns:1fr 1fr}.umkm-page .umkm-product-grid{grid-template-columns:1fr}.umkm-page .umkm-product-scroll{max-height:560px}}
    @media(max-width:576px){.umkm-page{padding-left:8px!important;padding-right:8px!important}.umkm-page .umkm-head p{display:none}.umkm-page .umkm-summary{gap:7px}.umkm-page .umkm-summary article{min-height:60px;padding:8px}.umkm-page .umkm-summary i{display:none}.umkm-page .umkm-filter{grid-template-columns:1fr}.umkm-page .umkm-product{grid-template-columns:82px minmax(0,1fr)}.umkm-page .umkm-card-head{align-items:flex-start;flex-direction:column}.umkm-page .umkm-card-head .form-control{width:100%}}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const search=document.getElementById('moderationSearch');const rows=document.querySelectorAll('#moderationTable tbody tr');search?.addEventListener('input',()=>{const term=search.value.toLowerCase();rows.forEach(row=>row.style.display=row.textContent.toLowerCase().includes(term)?'':'none')});
    document.querySelectorAll('.edit-product-btn').forEach(button=>button.addEventListener('click',()=>{
        document.getElementById('editProductForm').action=button.dataset.action;
        document.getElementById('editProductName').value=button.dataset.name || '';
        document.getElementById('editProductCategory').value=button.dataset.category || '';
        document.getElementById('editProductPrice').value=button.dataset.price || '';
        document.getElementById('editProductStock').value=button.dataset.stock || '';
        document.getElementById('editProductUnit').value=button.dataset.unit || '';
        document.getElementById('editProductDescription').value=button.dataset.description || '';
    }));
});
</script>
@endpush
