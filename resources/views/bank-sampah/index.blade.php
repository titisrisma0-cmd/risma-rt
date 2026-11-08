@extends('layouts.app')

@section('title', 'Bank Sampah RT')

@section('content')
<div class="content-container bank-page">
    <header class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1">
                <i class="fas fa-recycle text-emerald me-2"></i>Bank Sampah RT 
                @if(isset($rtId) && $rtId)
                    <span class="badge-soft-emerald fs-6 ms-1">RT {{ str_pad($rtId, 2, '0', STR_PAD_LEFT) }}</span>
                @endif
            </h1>
            <p class="page-subtitle">Pengelolaan jadwal pengumpulan, verifikasi setoran, saldo, pencairan dana, dan edukasi daur ulang.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
        </div>
    </header>

    @if(!($schemaReady ?? false))
        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Tabel Bank Sampah belum siap. Silakan periksa konfigurasi database.</div>
    @elseif(!$isPengurus)
        {{-- VIEW UNTUK WARGA --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button class="btn btn-emerald text-white px-3" data-bs-toggle="modal" data-bs-target="#modalSetoran"><i class="fas fa-plus me-1"></i>Setor Sampah</button>
            <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#modalTarikSaldo"><i class="fas fa-money-bill-wave me-1 text-emerald"></i>Tarik Saldo</button>
        </div>

        <section class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Saldo Tersedia</span>
                            <h3 class="stat-value text-emerald mb-0">Rp {{ number_format($saldo->saldo, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Total Sampah Terkumpul</span>
                            <h3 class="stat-value text-dark mb-0">{{ number_format((float) $saldo->total_berat_kg, 1, ',', '.') }} kg</h3>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: #E0F2FE; color: #0284C7;">
                            <i class="fas fa-weight-scale"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Riwayat Setoran Saya</span>
                            <h3 class="stat-value text-warning mb-0">{{ $riwayatSetoran->count() }} Transaksi</h3>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: #FEF3C7; color: #D97706;">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <nav class="nav nav-tabs-saas mb-4" role="tablist">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-setoran" type="button"><i class="fas fa-clock-rotate-left me-1"></i>Riwayat Setoran</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-penarikan-warga" type="button"><i class="fas fa-money-bill-transfer me-1"></i>Riwayat Pencairan</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-jadwal-warga" type="button"><i class="fas fa-calendar-days me-1"></i>Jadwal Penjemputan</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-harga-warga" type="button"><i class="fas fa-tags me-1"></i>Daftar Harga Sampah</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edukasi" type="button"><i class="fas fa-book-open me-1"></i>Panduan & Edukasi</button>
        </nav>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-setoran">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-1 text-emerald"></i>Riwayat Setoran Sampah Saya</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis Sampah</th>
                                    <th>Berat (kg)</th>
                                    <th>Nilai (Rp)</th>
                                    <th>Status</th>
                                    <th>Catatan Petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayatSetoran as $s)
                                    <tr>
                                        <td class="text-secondary small">{{ $s->tanggal_setor ? \Carbon\Carbon::parse($s->tanggal_setor)->format('d/m/Y') : $s->created_at->format('d/m/Y') }}</td>
                                        <td><span class="fw-semibold text-dark">{{ $s->jenisSampah->nama ?? 'Sampah Campur' }}</span></td>
                                        <td class="text-secondary">{{ number_format($s->berat_kg, 1, ',', '.') }} kg</td>
                                        <td class="text-emerald fw-bold">Rp {{ number_format($s->total_saldo, 0, ',', '.') }}</td>
                                        <td>
                                            @if($s->status === 'valid')
                                                <span class="badge-soft-emerald">Disetujui</span>
                                            @elseif($s->status === 'ditolak')
                                                <span class="badge-soft-danger">Ditolak</span>
                                            @else
                                                <span class="badge-soft-warning">Menunggu Verifikasi</span>
                                            @endif
                                        </td>
                                        <td class="small text-secondary">{{ $s->catatan ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-secondary">Belum ada riwayat setoran. Klik tombol "Setor Sampah" untuk mulai berkontribusi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-penarikan-warga">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-hand-holding-dollar me-1 text-emerald"></i>Riwayat Pengajuan Pencairan Dana</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                    <th>Tujuan</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayatPenarikan ?? [] as $p)
                                    <tr>
                                        <td class="text-secondary small">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="fw-bold text-emerald">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                        <td><span class="badge-soft-info text-uppercase">{{ $p->metode }}</span></td>
                                        <td class="text-dark">{{ $p->rekening_tujuan ?: 'Tunai di Pos RT' }}</td>
                                        <td>
                                            @if($p->status === 'dibayar')
                                                <span class="badge-soft-emerald">Sudah Dibayar</span>
                                            @elseif($p->status === 'disetujui')
                                                <span class="badge-soft-info">Disetujui</span>
                                            @elseif($p->status === 'ditolak')
                                                <span class="badge-soft-danger">Ditolak</span>
                                            @else
                                                <span class="badge-soft-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td class="small text-secondary">{{ $p->catatan ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-secondary">Belum ada pengajuan pencairan saldo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-jadwal-warga">
                <div class="row g-3">
                    @forelse($jadwals as $j)
                        <div class="col-md-6 col-lg-4">
                            <div class="saas-card h-100 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0 text-emerald">{{ $j->judul }}</h6>
                                    @php
                                        $badgeClass = match($j->status) {
                                            'berlangsung' => 'badge-soft-primary',
                                            'selesai' => 'badge-soft-secondary',
                                            'ditunda' => 'badge-soft-danger',
                                            default => 'badge-soft-emerald',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">
                                        {{ ucfirst($j->status ?: 'terjadwal') }}
                                    </span>
                                </div>
                                <div class="small text-secondary mb-2"><i class="fas fa-calendar me-1 text-emerald"></i>{{ \Carbon\Carbon::parse($j->waktu)->isoFormat('dddd, D MMMM Y HH:mm') }} WIB</div>
                                <div class="small mb-1 text-dark"><i class="fas fa-location-dot me-1 text-danger"></i><strong>Titik Kumpul:</strong> {{ $j->lokasi }}</div>
                                @if($j->petugas_nama)
                                    <div class="small mb-1 text-secondary"><i class="fas fa-truck me-1 text-info"></i><strong>Petugas/Armada:</strong> {{ $j->petugas_nama }} ({{ $j->petugas_kontak ?: 'Hubungi RT' }})</div>
                                @endif
                                @if($j->keterangan)
                                    <p class="small text-muted mt-2 mb-0">{{ $j->keterangan }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="saas-card p-4 text-center text-secondary">Belum ada jadwal pengumpulan sampah aktif di RT Anda.</div></div>
                    @endforelse
                </div>
            </div>

            <div class="tab-pane fade" id="tab-harga-warga">
                <div class="row g-3">
                    @forelse($jenisSampahs->where('status', true) as $js)
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="saas-card text-center p-4 h-100">
                                <span class="badge-soft-secondary align-self-center mb-2">{{ $js->kategori ?: 'Umum' }}</span>
                                <h6 class="fw-bold mb-1 text-dark">{{ $js->nama }}</h6>
                                <p class="h4 fw-bold text-emerald mb-0">Rp {{ number_format($js->harga_per_kg, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">/kg</span></p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="saas-card p-4 text-center text-secondary">Belum ada daftar harga sampah aktif.</div></div>
                    @endforelse
                </div>
            </div>

            <div class="tab-pane fade" id="tab-edukasi">
                @include('bank-sampah.edukasi-content')
            </div>
        </div>

        {{-- Modal Setor Sampah --}}
        <div class="modal fade" id="modalSetoran" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" action="{{ route('bank-sampah.setoran.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header border-bottom bg-white">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-recycle text-emerald me-2"></i>Form Setoran Sampah Warga</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Pilih Jenis Sampah <span class="text-danger">*</span></label>
                                <select name="jenis_sampah_id" id="warga_jenis_sampah" class="form-select form-control-saas" required>
                                    <option value="">-- Pilih Jenis Sampah --</option>
                                    @foreach($jenisSampahs->where('status', true) as $jenis)
                                        <option value="{{ $jenis->id }}" data-harga="{{ $jenis->harga_per_kg }}">{{ $jenis->nama }} (Rp {{ number_format($jenis->harga_per_kg, 0, ',', '.') }}/kg)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Estimasi Berat (kg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0.1" name="berat_kg" id="warga_berat_kg" class="form-control form-control-saas" placeholder="Contoh: 2.5" required>
                            </div>
                            <div class="p-3 mb-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="small text-secondary">Estimasi Nilai Saldo:</span>
                                <span id="warga_estimasi_nilai" class="fw-bold text-emerald fs-6">Rp 0</span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Tanggal Penyerahan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_setor" value="{{ now()->format('Y-m-d') }}" class="form-control form-control-saas" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Foto Sampah / Timbangan <small class="text-muted">(Opsional)</small></label>
                                <input type="file" name="foto_bukti" accept="image/*" class="form-control form-control-saas">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Catatan Tambahan</label>
                                <textarea name="catatan" rows="2" class="form-control form-control-saas" placeholder="Contoh: Sudah dipilah bersih dalam kardus"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-paper-plane me-1"></i>Kirim Setoran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Tarik Saldo Warga --}}
        <div class="modal fade" id="modalTarikSaldo" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" action="{{ route('bank-sampah.penarikan.store') }}">
                        @csrf
                        <div class="modal-header border-bottom bg-white">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-money-bill-transfer text-emerald me-2"></i>Pengajuan Pencairan Saldo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info small py-2 mb-3">Saldo Anda saat ini: <strong>Rp {{ number_format($saldo->saldo, 0, ',', '.') }}</strong></div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nominal Penarikan (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="nominal" min="1000" max="{{ $saldo->saldo }}" class="form-control form-control-saas" placeholder="Minimal Rp 1.000" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Metode Pencairan <span class="text-danger">*</span></label>
                                <select name="metode" class="form-select form-control-saas" required>
                                    <option value="tunai">Tunai di Pengurus RT</option>
                                    <option value="transfer">Transfer Bank / E-Wallet</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Rekening / E-Wallet Tujuan <small class="text-muted">(Jika Transfer)</small></label>
                                <input type="text" name="rekening_tujuan" class="form-control form-control-saas" placeholder="Contoh: BCA 12345678 a.n Budi / GoPay 0812...">
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4" {{ $saldo->saldo < 1000 ? 'disabled' : '' }}><i class="fas fa-check me-1"></i>Ajukan Pencairan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @else
        {{-- VIEW UNTUK PENGURUS RT / RW --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button class="btn btn-emerald text-white px-3" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="fas fa-calendar-plus me-1"></i>Buat Jadwal Pengumpulan</button>
            <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#modalHarga"><i class="fas fa-tags me-1 text-emerald"></i>Atur Harga Sampah</button>
        </div>

        <section class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Menunggu Setoran</span>
                            <h3 class="stat-value text-warning mb-0">{{ $setoranPending->count() }}</h3>
                            <small class="text-secondary">Verifikasi Setoran Warga</small>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: #FEF3C7; color: #D97706;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Menunggu Pencairan</span>
                            <h3 class="stat-value text-info mb-0">{{ $penarikanPending->count() }}</h3>
                            <small class="text-secondary">Pencairan Dana Warga</small>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: #E0F2FE; color: #0284C7;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Sampah Tervalidasi</span>
                            <h3 class="stat-value text-emerald mb-0">{{ number_format((float) ($rekap['berat'] ?? 0), 1, ',', '.') }} kg</h3>
                            <small class="text-secondary">Total Sampah RT</small>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                            <i class="fas fa-weight-scale"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="stat-label">Nasabah Aktif</span>
                            <h3 class="stat-value text-dark mb-0">{{ $rekap['warga_count'] ?? 0 }}</h3>
                            <small class="text-secondary">Warga Berpartisipasi</small>
                        </div>
                        <div class="stat-icon-wrapper" style="background-color: #F1F5F9; color: #475569;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <nav class="nav nav-tabs-saas mb-4" role="tablist">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-admin-setoran" type="button">
                <i class="fas fa-circle-check me-1"></i>Verifikasi Setoran
                @if($setoranPending->count() > 0)
                    <span class="badge-soft-danger ms-1">{{ $setoranPending->count() }}</span>
                @endif
            </button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-admin-pencairan" type="button">
                <i class="fas fa-money-bill-transfer me-1"></i>Pencairan Saldo
                @if($penarikanPending->count() > 0)
                    <span class="badge-soft-warning ms-1">{{ $penarikanPending->count() }}</span>
                @endif
            </button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-admin-jadwal" type="button"><i class="fas fa-calendar-days me-1"></i>Jadwal Pengumpulan</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-admin-nasabah" type="button"><i class="fas fa-users me-1"></i>Saldo Nasabah Warga</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-admin-harga" type="button"><i class="fas fa-tags me-1"></i>Kategori & Harga</button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-admin-edukasi" type="button"><i class="fas fa-book-open me-1"></i>Panduan & Edukasi</button>
        </nav>

        <div class="tab-content">
            {{-- TAB 1: VERIFIKASI SETORAN --}}
            <div class="tab-pane fade show active" id="tab-admin-setoran">
                <div class="saas-card overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-clock me-1 text-warning"></i>Setoran Menunggu Verifikasi ({{ $setoranPending->count() }})</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Warga</th>
                                    <th>Tanggal Setor</th>
                                    <th>Jenis Sampah</th>
                                    <th>Berat (kg)</th>
                                    <th>Nilai Saldo</th>
                                    <th>Foto Bukti</th>
                                    <th>Catatan Warga</th>
                                    <th class="text-center" style="min-width: 220px;">Aksi Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($setoranPending as $sp)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $sp->user->name }}</div>
                                            <div class="small text-secondary">{{ $sp->user->phone ?: '-' }}</div>
                                        </td>
                                        <td class="text-secondary small">{{ $sp->tanggal_setor ? \Carbon\Carbon::parse($sp->tanggal_setor)->format('d/m/Y') : $sp->created_at->format('d/m/Y') }}</td>
                                        <td><span class="badge-soft-info">{{ $sp->jenisSampah->nama ?? 'Sampah' }}</span></td>
                                        <td class="fw-bold text-dark">{{ number_format($sp->berat_kg, 1, ',', '.') }} kg</td>
                                        <td class="fw-bold text-emerald">Rp {{ number_format($sp->total_saldo, 0, ',', '.') }}</td>
                                        <td>
                                            @if($sp->foto_bukti)
                                                <a href="{{ asset('storage/' . $sp->foto_bukti) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-image me-1 text-emerald"></i>Lihat</a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="small text-secondary">{{ $sp->catatan ?: '-' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('bank-sampah.setoran.verify', $sp) }}" class="d-flex gap-1 align-items-center">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="catatan" class="form-control form-control-sm form-control-saas" placeholder="Catatan opsional">
                                                <button type="submit" name="status" value="valid" class="btn btn-sm btn-emerald text-white" title="Setujui Setoran"><i class="fas fa-check"></i></button>
                                                <button type="submit" name="status" value="ditolak" class="btn btn-sm btn-outline-danger" title="Tolak Setoran"><i class="fas fa-times"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-5 text-secondary">Tidak ada setoran yang menunggu verifikasi saat ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Riwayat Setoran Terakhir --}}
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-1 text-secondary"></i>20 Setoran Terakhir yang Telah Diverifikasi</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Warga</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Berat</th>
                                    <th>Nilai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($setoranTerbaru->where('status', '!=', 'pending') as $st)
                                    <tr>
                                        <td class="fw-medium text-dark">{{ $st->user->name }}</td>
                                        <td class="text-secondary small">{{ $st->tanggal_setor ? \Carbon\Carbon::parse($st->tanggal_setor)->format('d/m/Y') : $st->created_at->format('d/m/Y') }}</td>
                                        <td><span class="badge-soft-info">{{ $st->jenisSampah->nama ?? '-' }}</span></td>
                                        <td class="text-dark">{{ number_format($st->berat_kg, 1, ',', '.') }} kg</td>
                                        <td class="text-emerald fw-semibold">Rp {{ number_format($st->total_saldo, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="{{ $st->status === 'valid' ? 'badge-soft-emerald' : 'badge-soft-danger' }}">{{ ucfirst($st->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada riwayat setoran terverifikasi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 2: PENCAIRAN SALDO --}}
            <div class="tab-pane fade" id="tab-admin-pencairan">
                <div class="saas-card overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom p-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-hand-holding-dollar me-1 text-emerald"></i>Permohonan Pencairan Dana ({{ $penarikanPending->count() }})</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Warga Nasabah</th>
                                    <th>Tanggal</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                    <th>Rekening/E-Wallet Tujuan</th>
                                    <th style="min-width: 250px;">Aksi Petugas RT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penarikanPending as $pp)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $pp->user->name }}</div>
                                            <div class="small text-secondary">{{ $pp->user->phone ?: '-' }}</div>
                                        </td>
                                        <td class="text-secondary small">{{ $pp->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="fw-bold text-emerald fs-6">Rp {{ number_format($pp->nominal, 0, ',', '.') }}</td>
                                        <td><span class="badge-soft-info text-uppercase">{{ $pp->metode }}</span></td>
                                        <td class="text-dark">{{ $pp->rekening_tujuan ?: 'Tunai di Kantor/Pos RT' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('bank-sampah.penarikan.proses', $pp) }}" class="d-flex gap-1 align-items-center">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="catatan" class="form-control form-control-sm form-control-saas" placeholder="No ref/catatan">
                                                <button type="submit" name="status" value="dibayar" class="btn btn-sm btn-emerald text-white text-nowrap" title="Tandai Sudah Dibayar"><i class="fas fa-check-double me-1"></i>Bayar</button>
                                                <button type="submit" name="status" value="ditolak" class="btn btn-sm btn-outline-danger" title="Tolak Pencairan"><i class="fas fa-times"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-secondary">Tidak ada pengajuan pencairan pending.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Riwayat Pencairan Selesai --}}
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt me-1 text-secondary"></i>Riwayat Pembayaran Pencairan</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Warga</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Diproses Oleh</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penarikanList->where('status', '!=', 'pending') as $pl)
                                    <tr>
                                        <td class="fw-medium text-dark">{{ $pl->user->name }}</td>
                                        <td class="fw-bold text-emerald">Rp {{ number_format($pl->nominal, 0, ',', '.') }}</td>
                                        <td><span class="badge-soft-info">{{ ucfirst($pl->metode) }}</span></td>
                                        <td><span class="{{ $pl->status === 'dibayar' ? 'badge-soft-emerald' : 'badge-soft-danger' }}">{{ ucfirst($pl->status) }}</span></td>
                                        <td class="small text-secondary">{{ $pl->processor->name ?? '-' }}</td>
                                        <td class="small text-secondary">{{ $pl->catatan ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada riwayat pembayaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 3: JADWAL PENGUMPULAN --}}
            <div class="tab-pane fade" id="tab-admin-jadwal">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-check me-1 text-emerald"></i>Daftar Jadwal Penjemputan / Setor Sampah RT</h6>
                        <button class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="fas fa-plus me-1"></i>Tambah Jadwal Baru</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Waktu & Hari</th>
                                    <th>Judul Kegiatan</th>
                                    <th>Titik Kumpul / Lokasi</th>
                                    <th>Petugas / Kontak Armada</th>
                                    <th>Status</th>
                                    <th>Ubah Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwals as $j)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($j->waktu)->isoFormat('dddd, D MMMM Y') }}</div>
                                            <div class="small text-secondary">{{ \Carbon\Carbon::parse($j->waktu)->format('H:i') }} WIB</div>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $j->judul }}</td>
                                        <td><i class="fas fa-location-dot text-danger me-1"></i>{{ $j->lokasi }}</td>
                                        <td>
                                            <div class="text-dark">{{ $j->petugas_nama ?: 'Petugas RT' }}</div>
                                            <div class="small text-secondary">{{ $j->petugas_kontak ?: '-' }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($j->status) {
                                                    'berlangsung' => 'badge-soft-primary',
                                                    'selesai' => 'badge-soft-secondary',
                                                    'ditunda' => 'badge-soft-danger',
                                                    default => 'badge-soft-emerald',
                                                };
                                            @endphp
                                            <span class="{{ $badgeClass }}">{{ ucfirst($j->status ?: 'terjadwal') }}</span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('bank-sampah.jadwal.status', $j) }}" class="d-inline-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="form-select form-select-sm form-control-saas" onchange="this.form.submit()">
                                                    <option value="terjadwal" {{ ($j->status ?: 'terjadwal') === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                                                    <option value="berlangsung" {{ $j->status === 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                                                    <option value="selesai" {{ $j->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                    <option value="ditunda" {{ $j->status === 'ditunda' ? 'selected' : '' }}>Ditunda</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-secondary">Belum ada jadwal pengumpulan. Klik tombol "Tambah Jadwal Baru".</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 4: SALDO NASABAH WARGA --}}
            <div class="tab-pane fade" id="tab-admin-nasabah">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-1 text-emerald"></i>Daftar Nasabah & Saldo Sampah Warga RT</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Nama Warga</th>
                                    <th>No. Telepon / WhatsApp</th>
                                    <th>Total Berat Disetujui</th>
                                    <th>Saldo Aktif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wargaBalances ?? [] as $wb)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $wb->user->name ?? 'Warga' }}</td>
                                        <td class="text-secondary">{{ $wb->user->phone ?: '-' }}</td>
                                        <td class="text-dark">{{ number_format((float) $wb->total_berat_kg, 1, ',', '.') }} kg</td>
                                        <td class="fw-bold text-emerald fs-6">Rp {{ number_format($wb->saldo, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-5 text-secondary">Belum ada saldo nasabah terdaftar di RT ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 5: HARGA & KATEGORI --}}
            <div class="tab-pane fade" id="tab-admin-harga">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-tags me-1 text-emerald"></i>Daftar Jenis & Tarif Sampah per Kilogram</h6>
                        <button class="btn btn-sm btn-emerald text-white" data-bs-toggle="modal" data-bs-target="#modalHarga"><i class="fas fa-plus me-1"></i>Tambah Jenis Sampah</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table-saas">
                            <thead>
                                <tr>
                                    <th>Jenis Sampah</th>
                                    <th>Kategori</th>
                                    <th>Tarif per Kilogram</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jenisSampahs as $js)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $js->nama }}</td>
                                        <td><span class="badge-soft-secondary">{{ $js->kategori ?: 'Umum' }}</span></td>
                                        <td class="fw-bold text-emerald">Rp {{ number_format($js->harga_per_kg, 0, ',', '.') }} /kg</td>
                                        <td>
                                            <span class="{{ $js->status ? 'badge-soft-emerald' : 'badge-soft-danger' }}">{{ $js->status ? 'Aktif' : 'Nonaktif' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditHarga{{ $js->id }}"><i class="fas fa-edit me-1"></i>Edit</button>
                                        </td>
                                    </tr>

                                    {{-- Modal Edit Harga --}}
                                    <div class="modal fade" id="modalEditHarga{{ $js->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                <form method="POST" action="{{ route('bank-sampah.harga.store') }}">
                                                    @csrf
                                                    <input type="hidden" name="jenis_sampah_id" value="{{ $js->id }}">
                                                    <div class="modal-header border-bottom bg-white">
                                                        <h5 class="modal-title fw-bold text-dark">Edit Jenis Sampah</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary">Nama Jenis Sampah</label>
                                                            <input type="text" name="nama" class="form-control form-control-saas" value="{{ $js->nama }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary">Kategori</label>
                                                            <input type="text" name="kategori" class="form-control form-control-saas" value="{{ $js->kategori }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary">Harga per Kilogram (Rp)</label>
                                                            <input type="number" name="harga_per_kg" class="form-control form-control-saas" value="{{ (int) $js->harga_per_kg }}" required>
                                                        </div>
                                                        <div class="form-check form-switch mt-3">
                                                            <input class="form-check-input" type="checkbox" name="status" value="1" id="sw{{ $js->id }}" {{ $js->status ? 'checked' : '' }}>
                                                            <label class="form-check-label small text-secondary" for="sw{{ $js->id }}">Status Aktif Menerima</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-emerald text-white px-4">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 6: EDUKASI ADMIN --}}
            <div class="tab-pane fade" id="tab-admin-edukasi">
                @include('bank-sampah.edukasi-content')
            </div>
        </div>

        {{-- Modal Tambah Jadwal --}}
        <div class="modal fade" id="modalJadwal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" action="{{ route('bank-sampah.jadwal.store') }}">
                        @csrf
                        <div class="modal-header border-bottom bg-white">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-plus text-emerald me-2"></i>Tambah Jadwal Pengumpulan Sampah RT</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Judul Kegiatan / Penjemputan <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control form-control-saas" placeholder="Contoh: Pengangkutan Rutin Sampah Anorganik RT" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Hari, Tanggal & Jam Pengumpulan <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="waktu" class="form-control form-control-saas" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Titik Kumpul / Lokasi Penjemputan <span class="text-danger">*</span></label>
                                <input type="text" name="lokasi" class="form-control form-control-saas" placeholder="Contoh: Pos Ronda RT 01 / Depan Lapangan" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-secondary">Nama Petugas / Armada Truk</label>
                                    <input type="text" name="petugas_nama" class="form-control form-control-saas" placeholder="Contoh: Pak Joko / Truk DLH">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-secondary">Kontak Petugas (WA/HP)</label>
                                    <input type="text" name="petugas_kontak" class="form-control form-control-saas" placeholder="Contoh: 081234567890">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Status Awal</label>
                                <select name="status" class="form-select form-control-saas">
                                    <option value="terjadwal" selected>Terjadwal</option>
                                    <option value="berlangsung">Berlangsung</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="ditunda">Ditunda</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Keterangan / Instruksi ke Warga</label>
                                <textarea name="keterangan" rows="2" class="form-control form-control-saas" placeholder="Contoh: Harap sampah anorganik sudah dikemas dalam karung/kardus kering"></textarea>
                            </div>
                            <p class="small text-muted mb-0"><i class="fas fa-bell text-warning me-1"></i>Notifikasi otomatis akan dikirim ke seluruh akun warga di RT ini.</p>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-save me-1"></i>Simpan & Siarkan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Tambah Harga --}}
        <div class="modal fade" id="modalHarga" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" action="{{ route('bank-sampah.harga.store') }}">
                        @csrf
                        <div class="modal-header border-bottom bg-white">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-tag text-emerald me-2"></i>Tambah Jenis Sampah Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nama Jenis Sampah <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control form-control-saas" placeholder="Contoh: Minyak Jelantah / Botol Kaca" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Kategori</label>
                                <input type="text" name="kategori" class="form-control form-control-saas" placeholder="Contoh: Anorganik / Limbah / B3">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Harga per Kilogram (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="harga_per_kg" min="0" class="form-control form-control-saas" placeholder="Contoh: 3000" required>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-save me-1"></i>Simpan Harga</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const jenisSelect = document.getElementById('warga_jenis_sampah');
    const beratInput = document.getElementById('warga_berat_kg');
    const estimasiDisplay = document.getElementById('warga_estimasi_nilai');

    function updateEstimasi() {
        if (!jenisSelect || !beratInput || !estimasiDisplay) return;
        const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];
        const harga = parseFloat(selectedOption?.dataset?.harga || 0);
        const berat = parseFloat(beratInput.value || 0);
        const total = Math.round(harga * berat);
        estimasiDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    if (jenisSelect && beratInput) {
        jenisSelect.addEventListener('change', updateEstimasi);
        beratInput.addEventListener('input', updateEstimasi);
    }
});
</script>
@endsection
