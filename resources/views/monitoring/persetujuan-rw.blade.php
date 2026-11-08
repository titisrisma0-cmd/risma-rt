@extends('layouts.app')

@section('title', 'Persetujuan Pengajuan Tingkat RW')

@section('content')
<div class="py-2">
    {{-- Header --}}
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Pengawasan</span>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Persetujuan RW</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Persetujuan Pengajuan Surat Tingkat RW
            </h1>
            <p class="text-secondary mb-0">Otorisasi berjenjang permohonan warga yang telah diverifikasi RT dan membutuhkan penetapan RW.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('dashboard') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Filter Tabs & Summary Badges --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center justify-content-between">
        <div class="btn-group" role="group">
            <a href="{{ route('rw.pengawasan.persetujuan') }}" class="btn btn-sm {{ empty($statusFilter) ? 'btn-emerald' : 'btn-light' }}">
                Semua ({{ $counts['total'] ?? 0 }})
            </a>
            <a href="{{ route('rw.pengawasan.persetujuan', ['status' => 'menunggu_rw']) }}" class="btn btn-sm {{ $statusFilter === 'menunggu_rw' ? 'btn-warning text-dark' : 'btn-light' }}">
                <i class="fas fa-clock me-1"></i> Menunggu Putusan ({{ $counts['menunggu'] ?? 0 }})
            </a>
            <a href="{{ route('rw.pengawasan.persetujuan', ['status' => 'disetujui']) }}" class="btn btn-sm {{ $statusFilter === 'disetujui' ? 'btn-emerald' : 'btn-light' }}">
                <i class="fas fa-check me-1"></i> Disetujui ({{ $counts['disetujui'] ?? 0 }})
            </a>
            <a href="{{ route('rw.pengawasan.persetujuan', ['status' => 'ditolak']) }}" class="btn btn-sm {{ $statusFilter === 'ditolak' ? 'btn-danger' : 'btn-light' }}">
                <i class="fas fa-times me-1"></i> Ditolak ({{ $counts['ditolak'] ?? 0 }})
            </a>
        </div>
        <div class="text-secondary small">
            Menampilkan <strong>{{ $pengajuans->total() }}</strong> pengajuan surat
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="saas-card mb-4">
        <div class="table-responsive">
            <table class="table-saas table align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>Pemohon</th>
                        <th>Asal RT</th>
                        <th>Jenis Layanan</th>
                        <th>Keperluan & Catatan RT</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @php
                            $isPendingDecision = in_array($p->status, ['menunggu_rw', 'menunggu_persetujuan_rw']);
                        @endphp
                        <tr style="{{ $isPendingDecision ? 'background: rgba(20, 122, 69, 0.04);' : '' }}">
                            <td class="font-monospace small">
                                <span class="text-primary fw-bold d-block">{{ $p->nomor_pengajuan }}</span>
                                <small class="text-secondary">{{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small">{{ optional($p->user)->name }}</div>
                                <small class="text-secondary font-monospace">NIK: {{ optional($p->user->profile)->nik ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary">
                                    RT {{ optional($p->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark d-block small">{{ optional($p->jenisSurat)->nama }}</span>
                                @if($p->nomor_surat)
                                    <small class="text-success font-monospace">{{ $p->nomor_surat }}</small>
                                @endif
                            </td>
                            <td style="max-width: 280px;">
                                <div class="text-dark small mb-1 text-truncate" title="{{ $p->keperluan }}">{{ $p->keperluan }}</div>
                                @if($p->catatan_rt)
                                    <div class="p-2 rounded-2 small bg-light border-start border-3 border-primary">
                                        <strong class="text-primary" style="font-size: 0.75rem;">Catatan RT:</strong> <span class="text-secondary" style="font-size: 0.78rem;">{{ Str::limit($p->catatan_rt, 60) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $p->status_badge }} px-2.5 py-1">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                @if($isPendingDecision)
                                    <button type="button" class="btn btn-sm btn-emerald px-3" data-bs-toggle="modal" data-bs-target="#keputusanModal{{ $p->id }}">
                                        <i class="fas fa-stamp me-1"></i> Putuskan
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-light text-primary px-3" data-bs-toggle="modal" data-bs-target="#detailModal{{ $p->id }}">
                                        <i class="fas fa-eye me-1"></i> Lihat Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success d-block"></i>
                                Tidak ada pengajuan warga yang memerlukan persetujuan RW saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengajuans->hasPages())
            <div class="p-3 border-top">
                {{ $pengajuans->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ==================== MODALS ==================== --}}
@foreach($pengajuans as $p)
    @php
        $isPendingDecision = in_array($p->status, ['menunggu_rw', 'menunggu_persetujuan_rw']);
    @endphp

    @if($isPendingDecision)
        {{-- MODAL KEPUTUSAN RW --}}
        <div class="modal fade" id="keputusanModal{{ $p->id }}" tabindex="-1" aria-labelledby="keputusanModalLabel{{ $p->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="keputusanModalLabel{{ $p->id }}">
                            <i class="fas fa-stamp text-success me-2"></i>Keputusan Persetujuan RW
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form method="POST" action="{{ route('rw.pengawasan.approve', $p) }}">
                        @csrf
                        <div class="modal-body p-4">
                            {{-- Ringkasan Pengajuan --}}
                            <div class="p-3 mb-3 rounded-3 border bg-light bg-opacity-50">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-secondary d-block">Nomor Pengajuan</small>
                                        <strong class="text-primary font-monospace">{{ $p->nomor_pengajuan }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-secondary d-block">Pemohon</small>
                                        <strong class="text-dark">{{ optional($p->user)->name }} (RT {{ optional($p->rt)->kode_rt }})</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-secondary d-block">NIK Pemohon</small>
                                        <span class="text-dark font-monospace">{{ optional($p->user->profile)->nik ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-secondary d-block">Jenis Layanan</small>
                                        <span class="badge badge-soft-primary">{{ optional($p->jenisSurat)->nama }}</span>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <small class="text-secondary d-block">Keperluan Permohonan</small>
                                        <span class="text-dark">{{ $p->keperluan }}</span>
                                    </div>
                                    @if($p->catatan_rt)
                                        <div class="col-12 mt-2">
                                            <div class="p-2 rounded-2 small bg-white border-start border-3 border-primary">
                                                <strong class="text-primary">Catatan Verifikasi RT:</strong>
                                                <div class="text-dark mt-1">{{ $p->catatan_rt }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Form Keputusan --}}
                            <div class="mb-3">
                                <label class="form-label">Tentukan Keputusan RW *</label>
                                <select name="status" class="form-select" required>
                                    <option value="disetujui">Setujui (Otorisasi & Terbitkan Nomor Surat Resmi)</option>
                                    <option value="ditolak">Tolak Permohonan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan Keputusan RW</label>
                                <textarea name="catatan_rw" rows="3" class="form-control" placeholder="Berikan catatan persetujuan atau alasan apabila permohonan ditolak..."></textarea>
                                <small class="text-secondary">Catatan ini akan dikirimkan sebagai notifikasi kepada pemohon dan Ketua RT terkait.</small>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-0">
                                <i class="fas fa-info-circle me-1"></i> Apabila disetujui, nomor surat resmi tingkat RW akan digenerate otomatis dan status pengajuan akan diperbarui secara permanen.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald px-4">
                                <i class="fas fa-save me-1"></i> Simpan Keputusan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        {{-- MODAL DETAIL READ-ONLY --}}
        <div class="modal fade" id="detailModal{{ $p->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $p->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModalLabel{{ $p->id }}">
                            <i class="fas fa-file-alt text-primary me-2"></i>Detail Pengajuan Surat ({{ $p->nomor_pengajuan }})
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        {{-- Status Banner --}}
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-3 border bg-light">
                            <div>
                                <small class="text-secondary d-block">Status Permohonan</small>
                                <span class="badge {{ $p->status_badge }} px-3 py-1 fs-6">
                                    {{ $p->status_label }}
                                </span>
                            </div>
                            @if($p->nomor_surat)
                                <div class="text-end">
                                    <small class="text-secondary d-block">Nomor Surat Resmi</small>
                                    <strong class="text-success font-monospace">{{ $p->nomor_surat }}</strong>
                                </div>
                            @endif
                        </div>

                        {{-- Identitas Pemohon --}}
                        <div class="p-3 rounded-3 mb-3 border bg-white">
                            <h6 class="text-success fw-bold mb-2"><i class="fas fa-user me-2"></i>Informasi Pemohon</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">Nama Lengkap</small>
                                    <span class="text-dark fw-semibold">{{ optional($p->user)->name }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">NIK</small>
                                    <span class="text-dark font-monospace">{{ optional($p->user->profile)->nik ?? '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">Wilayah RT</small>
                                    <span class="text-dark">RT {{ optional($p->rt)->kode_rt ?? '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">Tanggal Pengajuan</small>
                                    <span class="text-dark">{{ $p->created_at ? $p->created_at->translatedFormat('d F Y, H:i') : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Isi Permohonan --}}
                        <div class="p-3 rounded-3 mb-3 border bg-white">
                            <h6 class="text-success fw-bold mb-2"><i class="fas fa-file-lines me-2"></i>Isi & Keperluan Permohonan</h6>
                            <div class="mb-2">
                                <small class="text-secondary d-block">Jenis Layanan Surat</small>
                                <span class="fw-bold text-dark">{{ optional($p->jenisSurat)->nama }}</span>
                            </div>
                            <div>
                                <small class="text-secondary d-block">Keperluan Lengkap</small>
                                <div class="p-2.5 rounded-2 bg-light text-dark small">
                                    {{ $p->keperluan }}
                                </div>
                            </div>
                        </div>

                        {{-- Catatan Verifikasi RT --}}
                        <div class="p-3 rounded-3 mb-3 bg-light border-start border-4 border-primary">
                            <h6 class="text-primary fw-bold mb-2"><i class="fas fa-check-double me-2"></i>Verifikasi Tingkat RT</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">Diverifikasi Oleh</small>
                                    <span class="text-dark">{{ optional($p->rtApprover)->name ?? 'Ketua RT' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">Waktu Verifikasi RT</small>
                                    <span class="text-dark">{{ $p->rt_approved_at ? $p->rt_approved_at->translatedFormat('d F Y, H:i') : '-' }}</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-secondary d-block">Catatan RT</small>
                                    <div class="text-dark">{{ $p->catatan_rt ?: 'Tidak ada catatan tambahan dari RT.' }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Keputusan Tingkat RW --}}
                        <div class="p-3 rounded-3 mb-2 bg-light border-start border-4 border-success">
                            <h6 class="text-success fw-bold mb-2"><i class="fas fa-gavel me-2"></i>Keputusan Pengurus RW</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">Diputuskan Oleh</small>
                                    <span class="text-dark">{{ optional($p->rwApprover)->name ?? optional($p->approver)->name ?? 'Pengurus RW' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-secondary d-block">Waktu Putusan RW</small>
                                    <span class="text-dark">{{ $p->rw_approved_at ? $p->rw_approved_at->translatedFormat('d F Y, H:i') : ($p->approved_at ? $p->approved_at->translatedFormat('d F Y, H:i') : '-') }}</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-secondary d-block">Catatan Keputusan RW</small>
                                    <div class="text-dark">{{ $p->catatan_rw ?: 'Tidak ada catatan khusus.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
