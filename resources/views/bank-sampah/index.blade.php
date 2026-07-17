@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3 bank-page">
    <header class="bank-head">
        <div><h2><i class="fas fa-recycle me-2"></i>Bank Sampah</h2><p>Setoran, saldo, harga, dan jadwal dalam satu halaman.</p></div>
        <a href="{{ route('dashboard') }}#layanan-masyarakat" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
    </header>

    @if(!($schemaReady ?? false))
        <div class="alert alert-warning">Database Bank Sampah belum siap. Jalankan <code>php artisan migrate</code>.</div>
    @elseif(!$isPengurus)
        <div class="bank-actions">
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalSetoran"><i class="fas fa-plus me-1"></i>Setor Sampah</button>
        </div>

        <section class="bank-summary">
            <article><i class="fas fa-wallet green"></i><div><strong>Rp {{ number_format($saldo->saldo, 0, ',', '.') }}</strong><span>Saldo Terkini</span></div></article>
            <article><i class="fas fa-weight-scale blue"></i><div><strong>{{ number_format((float) $saldo->total_berat_kg, 1, ',', '.') }} kg</strong><span>Sampah Disetujui</span></div></article>
            <article><i class="fas fa-receipt amber"></i><div><strong>{{ $riwayatSetoran->count() }}</strong><span>Total Setoran</span></div></article>
        </section>

        <nav class="nav nav-tabs bank-tabs" role="tablist">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bank-riwayat" type="button">Riwayat Setoran</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank-harga" type="button">Harga Sampah</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank-jadwal" type="button">Jadwal Layanan</button>
        </nav>

        <div class="tab-content bank-tab-content">
            <section class="tab-pane fade show active" id="bank-riwayat">
                <div class="bank-card">
                    <div class="bank-card-head"><h5><i class="fas fa-clock-rotate-left"></i> Riwayat Setoran</h5><input id="setoranSearch" class="form-control form-control-sm" type="search" placeholder="Cari jenis/status..."></div>
                    <div class="table-responsive bank-table-scroll"><table class="table table-sm align-middle mb-0" id="setoranTable"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Berat</th><th>Nilai</th><th>Status</th></tr></thead><tbody>
                        @forelse($riwayatSetoran as $setoran)
                            <tr><td>{{ $setoran->tanggal_setor?->format('d/m/Y') ?? $setoran->created_at->format('d/m/Y') }}</td><td>{{ $setoran->jenisSampah->nama }}</td><td>{{ $setoran->berat_kg }} kg</td><td>Rp {{ number_format($setoran->total_saldo, 0, ',', '.') }}</td><td><span class="badge bg-{{ $setoran->status === 'valid' ? 'success' : ($setoran->status === 'ditolak' ? 'danger' : 'warning') }}">{{ $setoran->status === 'pending' ? 'Menunggu' : ucfirst($setoran->status) }}</span></td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody></table></div>
                </div>
            </section>
            <section class="tab-pane fade" id="bank-harga"><div class="bank-card"><div class="bank-card-head"><h5><i class="fas fa-tags"></i> Harga per Kilogram</h5></div><div class="bank-price-grid">@forelse($jenisSampahs->where('status', true) as $jenis)<div><span><strong>{{ $jenis->nama }}</strong><small>{{ $jenis->kategori ?: 'Umum' }}</small></span><b>Rp {{ number_format($jenis->harga_per_kg, 0, ',', '.') }}</b></div>@empty<p class="text-muted">Belum ada harga aktif.</p>@endforelse</div></div></section>
            <section class="tab-pane fade" id="bank-jadwal"><div class="bank-card"><div class="bank-card-head"><h5><i class="fas fa-calendar-days"></i> Jadwal Setor / Jemput</h5></div><div class="bank-list-scroll">@forelse($jadwals as $jadwal)<div class="bank-schedule"><time><strong>{{ $jadwal->waktu->format('d') }}</strong><span>{{ $jadwal->waktu->format('M') }}</span></time><div><strong>{{ $jadwal->judul }}</strong><span>{{ $jadwal->waktu->format('H:i') }} · {{ $jadwal->lokasi }}</span><small>{{ $jadwal->keterangan }}</small></div></div>@empty<div class="bank-empty">Belum ada jadwal aktif.</div>@endforelse</div></div></section>
        </div>

        <div class="modal fade" id="modalSetoran" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Setor Sampah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('bank-sampah.setoran.store') }}" enctype="multipart/form-data">@csrf<div class="modal-body">
            <label class="form-label">Jenis Sampah</label><select name="jenis_sampah_id" id="jenis" class="form-select mb-2" required><option value="">Pilih jenis</option>@foreach($jenisSampahs->where('status', true) as $jenis)<option value="{{ $jenis->id }}" data-harga="{{ $jenis->harga_per_kg }}">{{ $jenis->nama }} — Rp {{ number_format($jenis->harga_per_kg, 0, ',', '.') }}/kg</option>@endforeach</select>
            <label class="form-label">Berat (kg)</label><input type="number" step=".01" min=".1" name="berat_kg" id="berat" class="form-control mb-2" required>
            <div class="bank-estimate">Estimasi nilai: <strong id="nilai">Rp 0</strong></div>
            <label class="form-label">Tanggal Setor</label><input type="date" name="tanggal_setor" value="{{ now()->format('Y-m-d') }}" class="form-control mb-2" required>
            <label class="form-label">Foto Bukti <small>(opsional)</small></label><input type="file" name="foto_bukti" accept="image/*" class="form-control mb-2">
            <label class="form-label">Catatan</label><textarea name="catatan" rows="2" class="form-control"></textarea>
        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Kirim Setoran</button></div></form></div></div></div>
    @else
        <div class="bank-actions">
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalHarga"><i class="fas fa-tag me-1"></i>Tambah Harga</button>
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="fas fa-calendar-plus me-1"></i>Tambah Jadwal</button>
        </div>

        <section class="bank-summary">
            <article><i class="fas fa-hourglass-half amber"></i><div><strong>{{ $setoranPending->count() }}</strong><span>Menunggu Verifikasi</span></div></article>
            <article><i class="fas fa-weight-scale blue"></i><div><strong>{{ number_format((float) $rekap['berat'], 1, ',', '.') }} kg</strong><span>Total Disetujui</span></div></article>
            <article><i class="fas fa-coins green"></i><div><strong>Rp {{ number_format($rekap['nilai'], 0, ',', '.') }}</strong><span>Nilai Sampah</span></div></article>
        </section>

        <div class="bank-admin-grid">
            <section class="bank-card bank-verify">
                <div class="bank-card-head"><h5><i class="fas fa-circle-check"></i> Verifikasi Setoran</h5><span class="bank-count">{{ $setoranPending->count() }}</span></div>
                <div class="table-responsive bank-verify-scroll"><table class="table table-sm align-middle mb-0"><thead><tr><th>Warga</th><th>Setoran</th><th>Nilai</th><th>Aksi</th></tr></thead><tbody>
                    @forelse($setoranPending as $setoran)
                        <tr><td><strong>{{ $setoran->user->name }}</strong><small>{{ $setoran->tanggal_setor?->format('d/m/Y') }}</small></td><td>{{ $setoran->jenisSampah->nama }} · {{ $setoran->berat_kg }} kg</td><td>Rp {{ number_format($setoran->total_saldo, 0, ',', '.') }}</td><td><form method="POST" action="{{ route('bank-sampah.setoran.verify', $setoran) }}" class="bank-verify-form">@csrf @method('PATCH')<input name="catatan" class="form-control form-control-sm" placeholder="Catatan"><div><button name="status" value="valid" class="btn btn-sm btn-success">Setujui</button><button name="status" value="ditolak" class="btn btn-sm btn-danger">Tolak</button></div></form></td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada setoran menunggu verifikasi.</td></tr>
                    @endforelse
                </tbody></table></div>
            </section>

            <aside class="bank-side">
                <section class="bank-card"><div class="bank-card-head"><h5><i class="fas fa-tags"></i> Harga Sampah</h5></div><div class="bank-list-scroll bank-price-list">@foreach($jenisSampahs as $jenis)<div><span><strong>{{ $jenis->nama }}</strong><small>{{ $jenis->kategori ?: 'Umum' }}</small></span><b>Rp {{ number_format($jenis->harga_per_kg, 0, ',', '.') }}</b></div>@endforeach</div></section>
                <section class="bank-card"><div class="bank-card-head"><h5><i class="fas fa-calendar-days"></i> Jadwal Aktif</h5></div><div class="bank-list-scroll">@forelse($jadwals as $jadwal)<div class="bank-schedule compact"><time><strong>{{ $jadwal->waktu->format('d') }}</strong><span>{{ $jadwal->waktu->format('M') }}</span></time><div><strong>{{ $jadwal->judul }}</strong><span>{{ $jadwal->waktu->format('H:i') }} · {{ $jadwal->lokasi }}</span></div></div>@empty<div class="bank-empty">Belum ada jadwal.</div>@endforelse</div></section>
            </aside>
        </div>

        <div class="modal fade" id="modalHarga" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Harga Sampah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('bank-sampah.harga.store') }}">@csrf<div class="modal-body"><label class="form-label">Jenis Sampah</label><input name="nama" class="form-control mb-2" required><label class="form-label">Kategori</label><input name="kategori" class="form-control mb-2"><label class="form-label">Harga/kg</label><input name="harga_per_kg" type="number" min="0" class="form-control" required></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Simpan Harga</button></div></form></div></div></div>
        <div class="modal fade" id="modalJadwal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Jadwal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('bank-sampah.jadwal.store') }}">@csrf<div class="modal-body"><label class="form-label">Judul</label><input name="judul" class="form-control mb-2" required><label class="form-label">Waktu</label><input name="waktu" type="datetime-local" class="form-control mb-2" required><label class="form-label">Lokasi</label><input name="lokasi" class="form-control mb-2" required><label class="form-label">Keterangan</label><textarea name="keterangan" rows="2" class="form-control"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-info">Simpan Jadwal</button></div></form></div></div></div>
    @endif
</div>

<style>
    .bank-page{--bank-surface:var(--surface,#10271e);--bank-raised:var(--surface-raised,#18382b);--bank-text:var(--text-primary,#f8fafc);--bank-muted:var(--text-muted,#b8c7be);--bank-border:var(--border-color,#526b5c);color:var(--bank-text)}
    .bank-page .bank-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:10px}.bank-page .bank-head h2{margin:0;font-size:1.4rem;color:var(--bank-text)}.bank-page .bank-head h2 i{color:#4ade80}.bank-page .bank-head p{margin:2px 0 0;color:var(--bank-muted);font-size:.82rem}.bank-page .bank-actions{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:10px}
    .bank-page .bank-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:12px}.bank-page .bank-summary article{display:flex;align-items:center;gap:11px;min-height:72px;padding:11px 13px;border:1px solid var(--bank-border);border-radius:13px;background:var(--bank-surface)}.bank-page .bank-summary article>i{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:var(--bank-raised)}.bank-page .bank-summary .green{color:#4ade80}.bank-page .bank-summary .blue{color:#60a5fa}.bank-page .bank-summary .amber{color:#fbbf24}.bank-page .bank-summary strong{display:block;color:var(--bank-text);font-size:1.18rem;line-height:1.2}.bank-page .bank-summary span{color:var(--bank-muted);font-size:.72rem}
    .bank-page .bank-tabs{border-color:var(--bank-border);gap:3px;flex-wrap:nowrap;overflow-x:auto}.bank-page .bank-tabs .nav-link{white-space:nowrap;color:var(--bank-muted)!important;font-size:.8rem;padding:7px 11px}.bank-page .bank-tabs .nav-link.active{background:var(--bank-raised)!important;color:var(--bank-text)!important;border-color:var(--bank-border)!important}.bank-page .bank-tab-content{padding-top:10px}
    .bank-page .bank-card{padding:12px;border:1px solid var(--bank-border);border-radius:13px;background:var(--bank-surface);color:var(--bank-text);overflow:hidden}.bank-page .bank-card-head{display:flex;align-items:center;justify-content:space-between;gap:9px;margin-bottom:8px}.bank-page .bank-card-head h5{margin:0;color:var(--bank-text);font-size:.92rem}.bank-page .bank-card-head h5 i{color:var(--accent,#f6d860);margin-right:5px}.bank-page .bank-card-head .form-control{width:min(230px,48%)}
    .bank-page .bank-table-scroll{max-height:360px;overflow:auto}.bank-page .bank-verify-scroll{max-height:520px;overflow:auto}.bank-page .table{font-size:.77rem;color:var(--bank-text)}.bank-page .table thead{position:sticky;top:0;z-index:1}.bank-page .table th{background:var(--bank-raised)!important;color:var(--bank-text)!important}.bank-page .table td small{display:block;color:var(--bank-muted)}
    .bank-page .bank-price-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;max-height:350px;overflow-y:auto}.bank-page .bank-price-grid>div,.bank-page .bank-price-list>div{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px;border-radius:9px;background:var(--bank-raised)}.bank-page .bank-price-grid span small,.bank-page .bank-price-list span small{display:block;color:var(--bank-muted);font-size:.68rem}.bank-page .bank-price-grid b,.bank-page .bank-price-list b{font-size:.78rem;white-space:nowrap}
    .bank-page .bank-list-scroll{max-height:290px;overflow-y:auto;padding-right:2px}.bank-page .bank-price-list{display:grid;gap:6px}.bank-page .bank-schedule{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--bank-border)}.bank-page .bank-schedule:last-child{border-bottom:0}.bank-page .bank-schedule time{width:42px;height:42px;display:grid;place-content:center;text-align:center;flex:0 0 42px;border-radius:9px;background:var(--bank-raised)}.bank-page .bank-schedule time strong{line-height:1}.bank-page .bank-schedule time span,.bank-page .bank-schedule div span,.bank-page .bank-schedule div small{display:block;color:var(--bank-muted);font-size:.69rem}.bank-page .bank-schedule div strong{display:block;font-size:.8rem}.bank-page .bank-schedule.compact{padding:6px 0}.bank-page .bank-empty{padding:20px;text-align:center;color:var(--bank-muted)}
    .bank-page .bank-estimate{margin-bottom:8px;padding:8px 10px;border-radius:8px;background:rgba(34,197,94,.14);color:var(--bank-text);font-size:.8rem}.bank-page .bank-admin-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(280px,.7fr);gap:12px}.bank-page .bank-side{display:grid;align-content:start;gap:12px}.bank-page .bank-count{padding:2px 7px;border-radius:999px;background:var(--bank-raised);font-size:.7rem}.bank-page .bank-verify-form{display:grid;gap:5px;min-width:210px}.bank-page .bank-verify-form>div{display:flex;gap:4px}.bank-page .modal .form-label{font-size:.78rem;margin-bottom:3px}
    html[data-bs-theme="light"] .bank-page .bank-summary .green{color:#166534}html[data-bs-theme="light"] .bank-page .bank-summary .blue{color:#1d4ed8}html[data-bs-theme="light"] .bank-page .bank-summary .amber{color:#92400e}
    @media(max-width:992px){.bank-page .bank-admin-grid{grid-template-columns:1fr}.bank-page .bank-price-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:576px){.bank-page{padding-left:8px!important;padding-right:8px!important}.bank-page .bank-head p{display:none}.bank-page .bank-summary{grid-template-columns:1fr}.bank-page .bank-summary article{min-height:62px;padding:8px 10px}.bank-page .bank-summary article>i{width:32px;height:32px}.bank-page .bank-price-grid{grid-template-columns:1fr}.bank-page .bank-card-head{align-items:flex-start;flex-direction:column}.bank-page .bank-card-head .form-control{width:100%}.bank-page .bank-verify-scroll{max-height:430px}}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const jenis=document.getElementById('jenis'),berat=document.getElementById('berat'),nilai=document.getElementById('nilai');
    function hitung(){if(nilai)nilai.textContent='Rp '+new Intl.NumberFormat('id-ID').format((jenis?.selectedOptions[0]?.dataset.harga||0)*(berat?.value||0));}
    jenis?.addEventListener('change',hitung);berat?.addEventListener('input',hitung);
    const search=document.getElementById('setoranSearch');const rows=document.querySelectorAll('#setoranTable tbody tr');
    search?.addEventListener('input',()=>{const term=search.value.toLowerCase();rows.forEach(row=>row.style.display=row.textContent.toLowerCase().includes(term)?'':'none');});
});
</script>
@endpush
