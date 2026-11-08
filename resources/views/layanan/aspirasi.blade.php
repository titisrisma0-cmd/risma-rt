@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3 aspirasi-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="h4 mb-1 fw-bold text-warning">
                <i class="fas fa-comment-dots me-2"></i>{{ $canManage ? 'Pusat Pengelolaan Aspirasi & Aduan Warga' : 'Aspirasi & Pengaduan Warga' }}
            </h2>
            <p class="text-muted small mb-0">
                {{ $canManage ? 'Monitoring aspirasi, pemberian respon resmi, penanganan keluhan, dan eskalasi ke tingkat RW.' : 'Sampaikan saran, aspirasi, atau keluhan lingkungan kepada pengurus RT secara cepat dan aman.' }}
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
    </div>

    @if(session('success'))<div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2 small mb-3">{{ $errors->first() }}</div>@endif

    {{-- SUMMARY STATS --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-md-3">
            <div class="p-3 rounded border bg-card d-flex align-items-center gap-3">
                <i class="fas fa-inbox fa-2x text-primary"></i>
                <div>
                    <strong class="fs-5 d-block text-primary">{{ $rekap['total'] ?? 0 }}</strong>
                    <span class="text-muted small">Total Aspirasi</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="p-3 rounded border bg-card d-flex align-items-center gap-3">
                <i class="fas fa-bell fa-2x text-warning"></i>
                <div>
                    <strong class="fs-5 d-block text-warning">{{ $rekap['baru'] ?? 0 }}</strong>
                    <span class="text-muted small">Baru / Belum Ditanggapi</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="p-3 rounded border bg-card d-flex align-items-center gap-3">
                <i class="fas fa-spinner fa-2x text-info"></i>
                <div>
                    <strong class="fs-5 d-block text-info">{{ $rekap['diproses'] ?? 0 }}</strong>
                    <span class="text-muted small">Sedang Ditindaklanjuti</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="p-3 rounded border bg-card d-flex align-items-center gap-3">
                <i class="fas fa-circle-check fa-2x text-success"></i>
                <div>
                    <strong class="fs-5 d-block text-success">{{ $rekap['selesai'] ?? 0 }}</strong>
                    <span class="text-muted small">Aspirasi Selesai</span>
                </div>
            </div>
        </div>
    </div>

    @if($canManage)
        {{-- VIEW PENGELOLAAN UNTUK KETUA RT / RW (TANPA FORM KIRIM) --}}
        <div class="card border mb-3">
            <div class="card-header bg-card d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex flex-wrap gap-1">
                    <a href="{{ route('aspirasi.index') }}" class="btn btn-xs {{ empty($statusFilter) ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">Semua ({{ $aspirasis->count() }})</a>
                    <a href="{{ route('aspirasi.index', ['status' => 'terkirim']) }}" class="btn btn-xs {{ $statusFilter === 'terkirim' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">Baru Masuk</a>
                    <a href="{{ route('aspirasi.index', ['status' => 'diproses']) }}" class="btn btn-xs {{ $statusFilter === 'diproses' ? 'btn-info text-white fw-bold' : 'btn-outline-secondary' }}">Sedang Ditinjau</a>
                    <a href="{{ route('aspirasi.index', ['status' => 'ditindaklanjuti']) }}" class="btn btn-xs {{ $statusFilter === 'ditindaklanjuti' ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}">Ditindaklanjuti</a>
                    <a href="{{ route('aspirasi.index', ['status' => 'dieskalasi']) }}" class="btn btn-xs {{ $statusFilter === 'dieskalasi' ? 'btn-purple text-white fw-bold' : 'btn-outline-secondary' }}" style="{{ $statusFilter === 'dieskalasi' ? 'background: #a855f7;' : '' }}">Diteruskan ke RW</a>
                    <a href="{{ route('aspirasi.index', ['status' => 'selesai']) }}" class="btn btn-xs {{ $statusFilter === 'selesai' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">Selesai</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
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
                                    <div class="small fw-bold">{{ $a->created_at->format('d/m/Y H:i') }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ ucwords(str_replace('_', ' ', $a->kategori)) }}</span>
                                </td>
                                <td>
                                    @if($a->anonim)
                                        <span class="badge bg-dark-subtle text-muted"><i class="fas fa-user-secret me-1"></i>Anonim</span>
                                    @else
                                        <div class="fw-semibold">{{ $a->pelapor->name ?? 'Warga' }}</div>
                                        <small class="text-muted">RT {{ $a->pelapor?->getRtKode() ?? ($a->rt?->kode_rt ?? '-') }}</small>
                                    @endif
                                </td>
                                <td style="max-width: 280px;">
                                    <div class="fw-bold mb-1">{{ $a->judul }}</div>
                                    <p class="small text-muted mb-1">{{ str($a->deskripsi)->limit(90) }}</p>
                                    @if($a->lampirans->count() > 0)
                                        <span class="badge bg-info-subtle text-info"><i class="fas fa-image me-1"></i>{{ $a->lampirans->count() }} Foto Lampiran</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeSt = match($a->status) {
                                            'terkirim' => 'bg-warning text-dark',
                                            'diproses', 'ditanggapi' => 'bg-info text-white',
                                            'ditindaklanjuti' => 'bg-primary',
                                            'dieskalasi' => 'bg-purple text-white',
                                            'selesai' => 'bg-success',
                                            'ditolak' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeSt }}" style="{{ $a->status === 'dieskalasi' ? 'background: #a855f7;' : '' }}">
                                        {{ ucwords(str_replace('_', ' ', $a->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($a->tanggapans->count() > 0)
                                        <div class="small text-success fw-semibold"><i class="fas fa-check-double me-1"></i>{{ $a->tanggapans->count() }} Tanggapan Resmi</div>
                                        <small class="text-muted">{{ str($a->tanggapans->last()->isi)->limit(45) }}</small>
                                    @else
                                        <span class="text-muted small">Belum ada respon</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        {{-- Beri Tanggapan --}}
                                        <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTanggapan{{ $a->id }}" title="Beri Tanggapan Resmi">
                                            <i class="fas fa-reply me-1"></i>Respon
                                        </button>

                                        {{-- Teruskan ke RW --}}
                                        @if($a->status !== 'dieskalasi' && $a->status !== 'selesai')
                                            <button class="btn btn-xs btn-outline-purple text-purple" style="border-color: #a855f7; color: #a855f7;" data-bs-toggle="modal" data-bs-target="#modalEskalasi{{ $a->id }}" title="Teruskan ke RW">
                                                <i class="fas fa-paper-plane me-1"></i>Ke RW
                                            </button>
                                        @endif

                                        {{-- Tandai Selesai --}}
                                        @if($a->status !== 'selesai')
                                            <form method="POST" action="{{ route('aspirasi.status', $a) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="selesai">
                                                <button type="submit" class="btn btn-xs btn-success" title="Tandai Selesai Ditangani" onclick="return confirm('Tandai aspirasi ini telah selesai ditindaklanjuti?')">
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
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('aspirasi.tanggapan', $a) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-reply me-2"></i>Tanggapan Resmi Pengurus RT</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="p-2 mb-3 rounded bg-light border small">
                                                    <strong>Aspirasi:</strong> {{ $a->judul }}
                                                    <p class="text-muted mb-0 mt-1">{{ $a->deskripsi }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Ubah Status Aspirasi Sekaligus</label>
                                                    <select name="status" class="form-select form-select-sm">
                                                        <option value="diproses" {{ $a->status === 'diproses' ? 'selected' : '' }}>Sedang Ditinjau / Diproses</option>
                                                        <option value="ditindaklanjuti" selected>Ditindaklanjuti oleh RT</option>
                                                        <option value="selesai">Selesai Ditangani</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Isi Tanggapan / Tindak Lanjut Resmi <span class="text-danger">*</span></label>
                                                    <textarea name="isi" rows="4" class="form-control" placeholder="Tuliskan respon atau langkah yang telah / sedang diambil RT..." required></textarea>
                                                </div>
                                                <small class="text-muted"><i class="fas fa-bell me-1"></i>Warga pelapor akan menerima notifikasi otomatis atas tanggapan ini.</small>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-paper-plane me-1"></i>Kirim Tanggapan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Eskalasi ke RW --}}
                            <div class="modal fade" id="modalEskalasi{{ $a->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('aspirasi.eskalasi', $a) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold" style="color: #a855f7;"><i class="fas fa-paper-plane me-2"></i>Teruskan Aspirasi ke Tingkat RW</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p class="small text-muted mb-2">Aspirasi <strong>{{ $a->judul }}</strong> membutuhkan koordinasi atau penanganan di tingkat RW.</p>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Target Eskalasi</label>
                                                    <input type="text" name="target" value="Pengurus RW" class="form-control" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Catatan / Alasan Diteruskan ke RW</label>
                                                    <textarea name="catatan" rows="3" class="form-control" placeholder="Jelaskan kebutuhan koordinasi RW atau instansi terkait..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-sm text-white" style="background: #a855f7;"><i class="fas fa-paper-plane me-1"></i>Kirim ke RW</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada aspirasi dalam status ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- VIEW UNTUK WARGA: FORM DI KIRI, RIWAYAT ASPIRASI SAYA DI KANAN --}}
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card border shadow-sm">
                    <div class="card-header bg-card">
                        <h6 class="mb-0 fw-semibold text-warning"><i class="fas fa-pen-to-square me-2"></i>Form Pengiriman Aspirasi / Keluhan Warga</h6>
                    </div>
                    <div class="card-body p-3">
                        <form method="POST" action="{{ route('aspirasi.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Judul Aspirasi / Pengaduan <span class="text-danger">*</span></label>
                                <input class="form-control" name="judul" placeholder="Contoh: Perbaikan Lampu Jalan Gang 2 Mati" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kategori Masalah <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    @foreach(['infrastruktur', 'kebersihan', 'keamanan', 'sosial', 'keluhan_pelayanan', 'usulan', 'lainnya'] as $x)
                                        <option value="{{ $x }}">{{ ucwords(str_replace('_', ' ', $x)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Deskripsi / Detail Laporan <span class="text-danger">*</span></label>
                                <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan detail permasalahan, dampak, dan harapan warga..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Lokasi / Patokan</label>
                                <input name="lokasi" class="form-control" placeholder="Contoh: Depan rumah No. 14">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Foto Bukti Pendukung <small class="text-muted">(Bisa lebih dari 1)</small></label>
                                <input type="file" name="foto[]" multiple accept="image/*" class="form-control">
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" name="anonim" id="anonim">
                                <label class="form-check-label small" for="anonim">
                                    <strong>Kirim sebagai Anonim</strong> (Nama dan identitas Anda disembunyikan dari pengurus)
                                </label>
                            </div>
                            <button type="submit" class="btn btn-warning w-100 fw-bold"><i class="fas fa-paper-plane me-1"></i>Kirim Aspirasi ke RT</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border shadow-sm">
                    <div class="card-header bg-card d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-clock-rotate-left me-2"></i>Riwayat Aspirasi Saya & Respon RT/RW</h6>
                        <span class="badge bg-secondary">{{ $aspirasis->count() }} Aspirasi</span>
                    </div>
                    <div class="card-body p-3">
                        @forelse($aspirasis as $a)
                            <div class="p-3 mb-3 border rounded bg-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-secondary-subtle text-secondary me-1">{{ ucwords(str_replace('_', ' ', $a->kategori)) }}</span>
                                        @php
                                            $badgeSt = match($a->status) {
                                                'terkirim' => 'bg-warning text-dark',
                                                'diproses', 'ditanggapi' => 'bg-info text-white',
                                                'ditindaklanjuti' => 'bg-primary',
                                                'dieskalasi' => 'bg-purple text-white',
                                                'selesai' => 'bg-success',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeSt }}" style="{{ $a->status === 'dieskalasi' ? 'background: #a855f7;' : '' }}">{{ ucwords(str_replace('_', ' ', $a->status)) }}</span>
                                    </div>
                                    <small class="text-muted">{{ $a->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <h6 class="fw-bold mb-1">{{ $a->judul }}</h6>
                                <p class="small text-muted mb-2">{{ $a->deskripsi }}</p>

                                {{-- Tanggapan Resmi RT/RW --}}
                                @if($a->tanggapans->count() > 0)
                                    <div class="mt-2 p-2 rounded bg-success-subtle text-success border border-success-subtle small">
                                        <div class="fw-bold mb-1"><i class="fas fa-comments me-1"></i>Tanggapan Resmi Pengurus:</div>
                                        @foreach($a->tanggapans as $t)
                                            <div class="mb-1">
                                                <strong>{{ $t->user->name ?? 'Pengurus' }}:</strong> {{ $t->isi }}
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $t->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-2 small text-muted"><i class="fas fa-hourglass-start me-1 text-warning"></i>Menunggu tanggapan dari Ketua RT.</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-comment-dots fa-3x mb-2 d-block"></i>
                                Anda belum pernah mengirimkan aspirasi atau pengaduan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.btn-xs {
    padding: 0.18rem 0.45rem;
    font-size: 0.75rem;
}
</style>
@endsection
