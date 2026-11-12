@extends('layouts.app')

@section('title', 'Verifikasi Pendaftaran Warga')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title d-flex align-items-center gap-2">
                <i class="fas fa-user-check text-emerald"></i>
                <span>Verifikasi Pendaftaran Warga</span>
            </h1>
            <p class="page-subtitle mb-0">
                {{ $user->isAdminRt() ? 'Validasi dan konfirmasi pendaftaran akun warga mandiri untuk wilayah RT Anda' : 'Monitoring verifikasi pendaftaran warga di seluruh wilayah RW 05' }}
            </p>
        </div>

        @if($user->isAdminRw())
            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('warga.verifikasi.index') }}" class="d-flex align-items-center gap-2">
                    <label class="small text-secondary fw-semibold text-nowrap mb-0">Filter RT:</label>
                    <select name="rt_id" class="form-select form-select-sm form-control-saas" onchange="this.form.submit()">
                        <option value="">Semua RT (RW 05)</option>
                        @foreach($rts as $rtOpt)
                            <option value="{{ $rtOpt->id }}" {{ $selectedRtId == $rtOpt->id ? 'selected' : '' }}>
                                RT {{ $rtOpt->kode_rt }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert-saas alert-saas-success mb-4">
            <i class="fas fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert-saas alert-saas-danger mb-4">
            <i class="fas fa-circle-exclamation fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="saas-card p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium">Menunggu Verifikasi</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="badge-soft-warning p-3 rounded-circle">
                        <i class="fas fa-user-clock fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="saas-card p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium">Warga Aktif</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $stats['aktif'] }}</h3>
                    </div>
                    <div class="badge-soft-emerald p-3 rounded-circle">
                        <i class="fas fa-user-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="saas-card p-3 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium">Pendaftaran Ditolak</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $stats['ditolak'] }}</h3>
                    </div>
                    <div class="badge-soft-danger p-3 rounded-circle">
                        <i class="fas fa-user-xmark fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Verification Card --}}
    <div class="saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-list-check text-emerald"></i>
                <span>Permohonan Akun Warga Baru</span>
                <span class="badge bg-warning text-dark">{{ $pendingList->count() }} Permohonan</span>
            </h6>
        </div>

        <div class="p-0">
            @if($pendingList->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama Lengkap & Akun</th>
                                <th>NIK & No. KK</th>
                                <th>Wilayah RT</th>
                                <th>Kontak & Alamat</th>
                                <th>Waktu Daftar</th>
                                <th class="text-end pe-4">Aksi Konfirmasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingList as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-circle-sm bg-light-emerald text-emerald fw-bold">
                                                {{ strtoupper(substr($item->nama_lengkap ?? 'W', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item->nama_lengkap }}</div>
                                                <div class="text-secondary small">
                                                    {{ optional($item->user)->email ?? 'Belum ada email' }}
                                                </div>
                                                <span class="badge bg-light text-secondary border mt-1">
                                                    <i class="fas {{ $item->jenis_kelamin === 'L' ? 'fa-mars text-primary' : 'fa-venus text-danger' }} me-1"></i>
                                                    {{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="text-muted">NIK:</div>
                                            <span class="font-monospace fw-semibold text-dark">{{ $item->nik ?? '-' }}</span>
                                            <div class="text-muted mt-1">No. KK:</div>
                                            <span class="font-monospace text-secondary">{{ $item->no_kk ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-soft-info">
                                            RT {{ $item->wilayahRt->kode_rt ?? str_pad($item->rt_id, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        @if($item->catatan_verifikasi)
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-info-circle me-1"></i>{{ $item->catatan_verifikasi }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><i class="fas fa-phone text-muted me-1"></i> {{ $item->no_hp ?? '-' }}</div>
                                            <div class="text-muted text-truncate" style="max-width: 220px;" title="{{ $item->alamat }}">
                                                <i class="fas fa-map-marker-alt text-muted me-1"></i> {{ $item->alamat ?? '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-secondary">
                                            {{ $item->created_at ? $item->created_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($user->isAdminRt() || $user->isAdminRw())
                                            <div class="d-inline-flex gap-2">
                                                {{-- Tombol Setujui --}}
                                                <form method="POST" action="{{ route('warga.verifikasi.approve', $item->id) }}" onsubmit="return confirm('Setujui pendaftaran warga {{ $item->nama_lengkap }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-emerald text-white d-inline-flex align-items-center gap-1">
                                                        <i class="fas fa-check"></i>
                                                        <span>Setujui</span>
                                                    </button>
                                                </form>

                                                {{-- Tombol Tolak (Modal Trigger) --}}
                                                <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                                    onclick="openRejectModal('{{ $item->id }}', '{{ addslashes($item->nama_lengkap) }}')">
                                                    <i class="fas fa-times"></i>
                                                    <span>Tolak</span>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="p-3 bg-light rounded-circle d-inline-flex mb-3">
                        <i class="fas fa-circle-check fs-2 text-emerald"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Tidak Ada Pendaftaran Pending</h6>
                    <p class="text-secondary small mb-0">Semua pendaftaran warga telah diproses dan diverifikasi.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Riwayat Verifikasi Terakhir --}}
    @if($historyList->count() > 0)
        <div class="saas-card overflow-hidden mt-4">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-history text-secondary"></i>
                    <span>Riwayat Verifikasi Terakhir</span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nama Warga</th>
                            <th>NIK</th>
                            <th>Wilayah RT</th>
                            <th>Status Hasil</th>
                            <th>Catatan / Alasan</th>
                            <th>Diverifikasi Oleh</th>
                            <th class="text-end pe-4">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historyList as $hist)
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">{{ $hist->nama_lengkap }}</td>
                                <td class="font-monospace small">{{ $hist->nik }}</td>
                                <td><span class="badge-soft-info">RT {{ $hist->wilayahRt->kode_rt ?? '-' }}</span></td>
                                <td>
                                    @if($hist->status === 'aktif')
                                        <span class="badge-soft-emerald"><i class="fas fa-check-circle me-1"></i> Disetujui</span>
                                    @elseif($hist->status === 'ditolak')
                                        <span class="badge-soft-danger"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $hist->status }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $hist->catatan_verifikasi ?? '-' }}</td>
                                <td class="small text-secondary">{{ optional($hist->verifiedBy)->name ?? 'Sistem' }}</td>
                                <td class="text-end pe-4 small text-secondary">
                                    {{ $hist->verified_at ? $hist->verified_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Modal Penolakan Verifikasi --}}
<div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-user-xmark text-danger"></i>
                    <span>Tolak Pendaftaran Warga</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReject" method="POST" action="">
                @csrf
                <div class="modal-body px-4 py-3">
                    <p class="small text-secondary mb-3">
                        Anda akan menolak pendaftaran akun untuk <strong id="rejectNamaWarga" class="text-dark"></strong>.
                        Silakan tuliskan alasan penolakan agar warga dapat mengetahui penyebabnya.
                    </p>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan" class="form-control form-control-saas" rows="3" required placeholder="Contoh: Bukan warga RT setempat, data KK tidak cocok, NIK salah..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRejectModal(profileId, nama) {
        document.getElementById('rejectNamaWarga').innerText = nama;
        document.getElementById('formReject').action = "{{ url('warga-verifikasi') }}/" + profileId + "/reject";
        const modal = new bootstrap.Modal(document.getElementById('modalReject'));
        modal.show();
    }
</script>
@endsection
