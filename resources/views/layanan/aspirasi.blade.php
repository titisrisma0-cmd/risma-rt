@extends('layouts.app')

@section('title', 'Aspirasi & Pengaduan Warga')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Layanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Aspirasi Warga</li>
                </ol>
            </nav>
            <h1 class="page-title">
                <i class="fas fa-comment-dots text-success me-2"></i>{{ $canManage ? 'Pusat Pengelolaan Aspirasi & Aduan Warga' : 'Aspirasi & Pengaduan Warga' }}
            </h1>
            <p class="page-subtitle">
                {{ $canManage ? 'Monitoring aspirasi, verifikasi penanganan keluhan, respon resmi RT, dan eskalasi ke tingkat RW.' : 'Sampaikan masukan, aspirasi fasilitas, atau keluhan lingkungan kepada pengurus RT secara cepat dan transparan.' }}
            </p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-saas alert-saas-success d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-check-circle fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-saas alert-saas-danger d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-circle fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-saas alert-saas-danger d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-circle fs-5"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    {{-- SUMMARY STATS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-primary-soft text-primary">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Aspirasi</span>
                    <h3 class="stat-value">{{ $rekap['total'] ?? 0 }}</h3>
                    <span class="stat-desc">Aspirasi masuk ke sistem</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-warning-soft text-warning">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Baru / Belum Ditanggapi</span>
                    <h3 class="stat-value">{{ $rekap['baru'] ?? 0 }}</h3>
                    <span class="stat-desc">Menunggu respon pengurus</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-info-soft text-info">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Sedang Ditindaklanjuti</span>
                    <h3 class="stat-value">{{ $rekap['diproses'] ?? 0 }}</h3>
                    <span class="stat-desc">Dalam proses penanganan</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-success-soft text-success">
                    <i class="fas fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Aspirasi Selesai</span>
                    <h3 class="stat-value">{{ $rekap['selesai'] ?? 0 }}</h3>
                    <span class="stat-desc">Penanganan tuntas</span>
                </div>
            </div>
        </div>
    </div>

    @if($canManage)
        {{-- VIEW PENGELOLAAN UNTUK KETUA RT / RW --}}
        <div class="saas-card mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('aspirasi.index') }}" class="btn btn-sm {{ empty($statusFilter) ? 'btn-saas-primary' : 'btn-outline-saas' }}">
                        Semua ({{ $aspirasis->count() }})
                    </a>
                    <a href="{{ route('aspirasi.index', ['status' => 'terkirim']) }}" class="btn btn-sm {{ $statusFilter === 'terkirim' ? 'btn-saas-primary' : 'btn-outline-saas' }}">
                        Baru Masuk
                    </a>
                    <a href="{{ route('aspirasi.index', ['status' => 'diproses']) }}" class="btn btn-sm {{ $statusFilter === 'diproses' ? 'btn-saas-primary' : 'btn-outline-saas' }}">
                        Sedang Ditinjau
                    </a>
                    <a href="{{ route('aspirasi.index', ['status' => 'ditindaklanjuti']) }}" class="btn btn-sm {{ $statusFilter === 'ditindaklanjuti' ? 'btn-saas-primary' : 'btn-outline-saas' }}">
                        Ditindaklanjuti
                    </a>
                    <a href="{{ route('aspirasi.index', ['status' => 'dieskalasi']) }}" class="btn btn-sm {{ $statusFilter === 'dieskalasi' ? 'btn-saas-primary' : 'btn-outline-saas' }}">
                        Diteruskan ke RW
                    </a>
                    <a href="{{ route('aspirasi.index', ['status' => 'selesai']) }}" class="btn btn-sm {{ $statusFilter === 'selesai' ? 'btn-saas-primary' : 'btn-outline-saas' }}">
                        Selesai
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-saas align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Waktu & Kategori</th>
                            <th>Pelapor</th>
                            <th>Aspirasi & Masukan</th>
                            <th>Status Saat Ini</th>
                            <th>Respon Resmi RT</th>
                            <th class="text-end" style="min-width: 220px;">Aksi Pengelolaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aspirasis as $a)
                            <tr>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $a->created_at->format('d/m/Y H:i') }}</div>
                                    <span class="badge badge-soft-secondary mt-1">{{ ucwords(str_replace('_', ' ', $a->kategori)) }}</span>
                                </td>
                                <td>
                                    @if($a->anonim)
                                        <span class="badge badge-soft-secondary"><i class="fas fa-user-secret me-1"></i>Anonim</span>
                                    @else
                                        <div class="fw-semibold text-dark">{{ $a->pelapor->name ?? 'Warga' }}</div>
                                        <small class="text-muted">RT {{ $a->pelapor?->getRtKode() ?? ($a->rt?->kode_rt ?? '-') }}</small>
                                    @endif
                                </td>
                                <td style="max-width: 280px;">
                                    <div class="fw-bold text-dark mb-1">{{ $a->judul }}</div>
                                    <p class="small text-muted mb-1">{{ str($a->deskripsi)->limit(90) }}</p>
                                    @if($a->lampirans->count() > 0)
                                        <span class="badge badge-soft-info"><i class="fas fa-image me-1"></i>{{ $a->lampirans->count() }} Foto Bukti</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeSt = match($a->status) {
                                            'terkirim' => 'badge-soft-warning',
                                            'diproses', 'ditanggapi' => 'badge-soft-info',
                                            'ditindaklanjuti' => 'badge-soft-primary',
                                            'dieskalasi' => 'badge-soft-secondary',
                                            'selesai' => 'badge-soft-success',
                                            'ditolak' => 'badge-soft-danger',
                                            default => 'badge-soft-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeSt }}">
                                        {{ ucwords(str_replace('_', ' ', $a->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($a->tanggapans->count() > 0)
                                        <div class="small text-success fw-semibold"><i class="fas fa-check-double me-1"></i>{{ $a->tanggapans->count() }} Tanggapan</div>
                                        <small class="text-muted">{{ str($a->tanggapans->last()->isi)->limit(45) }}</small>
                                    @else
                                        <span class="text-muted small">Belum ada respon</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        {{-- Beri Tanggapan --}}
                                        <button class="btn btn-outline-saas btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#modalTanggapan{{ $a->id }}" title="Beri Tanggapan Resmi">
                                            <i class="fas fa-reply me-1"></i>Respon
                                        </button>

                                        {{-- Teruskan ke RW --}}
                                        @if($a->status !== 'dieskalasi' && $a->status !== 'selesai')
                                            <button class="btn btn-outline-saas btn-sm text-info" data-bs-toggle="modal" data-bs-target="#modalEskalasi{{ $a->id }}" title="Teruskan ke RW">
                                                <i class="fas fa-paper-plane me-1"></i>Ke RW
                                            </button>
                                        @endif

                                        {{-- Tandai Selesai --}}
                                        @if($a->status !== 'selesai')
                                            <form method="POST" action="{{ route('aspirasi.status', $a) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="selesai">
                                                <button type="submit" class="btn btn-outline-saas btn-sm text-success" title="Tandai Selesai Ditangani" data-confirm="Tandai aspirasi ini telah selesai ditindaklanjuti?" data-confirm-title="Tindak Lanjut Aspirasi" data-confirm-btn="Ya, Selesai">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Beri Tanggapan --}}
                            <div class="modal fade" id="modalTanggapan{{ $a->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <form method="POST" action="{{ route('aspirasi.tanggapan', $a) }}">
                                            @csrf
                                            <div class="modal-header border-bottom py-3 px-4">
                                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-reply text-success me-2"></i>Tanggapan Resmi Pengurus RT</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start p-4">
                                                <div class="p-3 mb-3 rounded-3 bg-light border small">
                                                    <strong>Aspirasi:</strong> {{ $a->judul }}
                                                    <p class="text-muted mb-0 mt-1">{{ $a->deskripsi }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label-saas">Ubah Status Aspirasi Sekaligus</label>
                                                    <select name="status" class="form-select-saas form-select-sm">
                                                        <option value="diproses" {{ $a->status === 'diproses' ? 'selected' : '' }}>Sedang Ditinjau / Diproses</option>
                                                        <option value="ditindaklanjuti" selected>Ditindaklanjuti oleh RT</option>
                                                        <option value="selesai">Selesai Ditangani</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label-saas">Isi Tanggapan / Tindak Lanjut Resmi <span class="text-danger">*</span></label>
                                                    <textarea name="isi" rows="4" class="form-control-saas" placeholder="Tuliskan respon atau langkah yang telah / sedang diambil RT..." required></textarea>
                                                </div>
                                                <small class="text-muted"><i class="fas fa-bell me-1"></i>Warga pelapor akan menerima notifikasi otomatis atas tanggapan ini.</small>
                                            </div>
                                            <div class="modal-footer border-top py-3 px-4">
                                                <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-saas-primary"><i class="fas fa-paper-plane me-1"></i>Kirim Tanggapan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Eskalasi ke RW --}}
                            <div class="modal fade" id="modalEskalasi{{ $a->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <form method="POST" action="{{ route('aspirasi.eskalasi', $a) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header border-bottom py-3 px-4">
                                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-paper-plane text-info me-2"></i>Teruskan Aspirasi ke Tingkat RW</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start p-4">
                                                <p class="small text-muted mb-3">Aspirasi <strong>{{ $a->judul }}</strong> membutuhkan koordinasi atau penanganan di tingkat RW.</p>
                                                <div class="mb-3">
                                                    <label class="form-label-saas">Target Eskalasi</label>
                                                    <input type="text" name="target" value="Pengurus RW" class="form-control-saas" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label-saas">Catatan / Alasan Diteruskan ke RW</label>
                                                    <textarea name="catatan" rows="3" class="form-control-saas" placeholder="Jelaskan kebutuhan koordinasi RW atau instansi terkait..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top py-3 px-4">
                                                <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-saas-primary"><i class="fas fa-paper-plane me-1"></i>Kirim ke RW</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-comment-dots"></i></div>
                                        <h6 class="empty-state-title">Belum Ada Aspirasi</h6>
                                        <p class="empty-state-desc">Belum ada aspirasi warga dalam status ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- VIEW UNTUK WARGA --}}
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="saas-card">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square text-success me-2"></i>Form Pengiriman Aspirasi / Keluhan Warga</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('aspirasi.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label-saas">Judul Aspirasi / Pengaduan <span class="text-danger">*</span></label>
                                <input class="form-control-saas" name="judul" placeholder="Contoh: Perbaikan Lampu Jalan Gang 2 Mati" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-saas">Kategori Masalah <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select-saas" required>
                                    @foreach(['infrastruktur', 'kebersihan', 'keamanan', 'sosial', 'keluhan_pelayanan', 'usulan', 'lainnya'] as $x)
                                        <option value="{{ $x }}">{{ ucwords(str_replace('_', ' ', $x)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-saas">Deskripsi / Detail Laporan <span class="text-danger">*</span></label>
                                <textarea name="deskripsi" class="form-control-saas" rows="4" placeholder="Jelaskan detail permasalahan, dampak, dan harapan warga..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-saas">Lokasi / Patokan</label>
                                <input name="lokasi" class="form-control-saas" placeholder="Contoh: Depan rumah No. 14">
                            </div>
                            <div class="mb-3">
                                <label class="form-label-saas">Foto Bukti Pendukung <small class="text-muted">(Bisa lebih dari 1)</small></label>
                                <input type="file" name="foto[]" multiple accept="image/*" class="form-control-saas">
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" name="anonim" id="anonim">
                                <label class="form-check-label small text-secondary" for="anonim">
                                    <strong class="text-dark">Kirim sebagai Anonim</strong> (Nama dan identitas Anda disembunyikan dari pengurus)
                                </label>
                            </div>
                            <button type="submit" class="btn btn-saas-primary w-100">
                                <i class="fas fa-paper-plane me-1"></i> Kirim Aspirasi ke RT
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="saas-card">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-clock-rotate-left text-success me-2"></i>Riwayat Aspirasi Saya</h6>
                        <span class="badge badge-soft-secondary">{{ $aspirasis->count() }} Aspirasi</span>
                    </div>
                    <div class="card-body p-4">
                        @forelse($aspirasis as $a)
                            <div class="p-3 mb-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge badge-soft-secondary me-1">{{ ucwords(str_replace('_', ' ', $a->kategori)) }}</span>
                                        @php
                                            $badgeSt = match($a->status) {
                                                'terkirim' => 'badge-soft-warning',
                                                'diproses', 'ditanggapi' => 'badge-soft-info',
                                                'ditindaklanjuti' => 'badge-soft-primary',
                                                'dieskalasi' => 'badge-soft-secondary',
                                                'selesai' => 'badge-soft-success',
                                                default => 'badge-soft-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeSt }}">{{ ucwords(str_replace('_', ' ', $a->status)) }}</span>
                                    </div>
                                    <small class="text-muted">{{ $a->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ $a->judul }}</h6>
                                <p class="small text-secondary mb-2">{{ $a->deskripsi }}</p>

                                {{-- Tanggapan Resmi RT/RW --}}
                                @if($a->tanggapans->count() > 0)
                                    <div class="mt-2 p-3 rounded-3 bg-white border border-success-subtle small">
                                        <div class="fw-bold text-success mb-2"><i class="fas fa-comments me-1"></i>Tanggapan Resmi Pengurus:</div>
                                        @foreach($a->tanggapans as $t)
                                            <div class="mb-2 pb-2 border-bottom">
                                                <strong class="text-dark">{{ $t->user->name ?? 'Pengurus' }}:</strong> <span class="text-secondary">{{ $t->isi }}</span>
                                                <small class="text-muted d-block mt-1">{{ $t->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-2 small text-muted"><i class="fas fa-hourglass-start me-1 text-warning"></i>Menunggu tanggapan dari Ketua RT.</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-5 text-secondary">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-comment-dots"></i></div>
                                    <h6 class="empty-state-title">Belum Ada Aspirasi</h6>
                                    <p class="empty-state-desc">Anda belum pernah mengirimkan aspirasi atau pengaduan.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
