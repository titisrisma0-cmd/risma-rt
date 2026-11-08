@extends('layouts.app')

@section('title', 'Persetujuan Pengajuan Tingkat RW')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1">
                <i class="fas fa-stamp text-emerald-400 me-2"></i>Pengawasan Persetujuan Pengajuan RW
            </h1>
            <p class="text-white-50 mb-0">Eskalasi permohonan warga dari RT yang memerlukan otorisasi resmi tingkat RW</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-light px-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Tabs & Summary Badges --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center justify-content-between">
        <div class="btn-group" role="group">
            <a href="{{ route('rw.pengawasan.persetujuan') }}" class="btn btn-sm {{ empty($statusFilter) ? 'btn-emerald text-white' : 'btn-outline-light' }}" style="{{ empty($statusFilter) ? 'background: #10b981; border: none;' : '' }}">
                Semua ({{ $counts['total'] ?? 0 }})
            </a>
            <a href="{{ route('rw.pengawasan.persetujuan', ['status' => 'menunggu_rw']) }}" class="btn btn-sm {{ $statusFilter === 'menunggu_rw' ? 'btn-warning text-dark' : 'btn-outline-light' }}">
                <i class="fas fa-clock me-1"></i> Menunggu Putusan ({{ $counts['menunggu'] ?? 0 }})
            </a>
            <a href="{{ route('rw.pengawasan.persetujuan', ['status' => 'disetujui']) }}" class="btn btn-sm {{ $statusFilter === 'disetujui' ? 'btn-success' : 'btn-outline-light' }}">
                <i class="fas fa-check me-1"></i> Disetujui ({{ $counts['disetujui'] ?? 0 }})
            </a>
            <a href="{{ route('rw.pengawasan.persetujuan', ['status' => 'ditolak']) }}" class="btn btn-sm {{ $statusFilter === 'ditolak' ? 'btn-danger' : 'btn-outline-light' }}">
                <i class="fas fa-times me-1"></i> Ditolak ({{ $counts['ditolak'] ?? 0 }})
            </a>
        </div>
        <div class="text-white-50 small">
            Menampilkan <strong>{{ $pengajuans->total() }}</strong> pengajuan surat
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card border-0 mb-4" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.85); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">No. Pengajuan</th>
                        <th class="py-3 px-3">Pemohon</th>
                        <th class="py-3 px-3">Asal RT</th>
                        <th class="py-3 px-3">Jenis Layanan</th>
                        <th class="py-3 px-3">Keperluan & Catatan RT</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-end">Aksi Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @php
                            $isPendingDecision = in_array($p->status, ['menunggu_rw', 'menunggu_persetujuan_rw']);
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); {{ $isPendingDecision ? 'background: rgba(16, 185, 129, 0.04);' : '' }}">
                            <td class="py-3 px-3 font-monospace small">
                                <span class="text-emerald-400 fw-bold d-block">{{ $p->nomor_pengajuan }}</span>
                                <small class="text-white-50">{{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-' }}</small>
                            </td>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-white">{{ optional($p->user)->name }}</div>
                                <small class="text-white-50">NIK: {{ optional($p->user->profile)->nik ?? '-' }}</small>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    RT {{ optional($p->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="fw-semibold text-white d-block">{{ optional($p->jenisSurat)->nama }}</span>
                                @if($p->nomor_surat)
                                    <small class="text-emerald-400 font-monospace">{{ $p->nomor_surat }}</small>
                                @endif
                            </td>
                            <td class="py-3 px-3" style="max-width: 280px;">
                                <div class="text-white small mb-1 text-truncate" title="{{ $p->keperluan }}">{{ $p->keperluan }}</div>
                                @if($p->catatan_rt)
                                    <div class="p-2 rounded small" style="background: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6;">
                                        <strong class="text-blue-300">Catatan RT:</strong> <span class="text-white-50">{{ Str::limit($p->catatan_rt, 60) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge {{ $p->status_badge }} px-2 py-1">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-end">
                                @if($isPendingDecision)
                                    {{-- Tombol Putuskan HANYA untuk status menunggu RW --}}
                                    <button type="button" class="btn btn-sm btn-emerald text-white px-3" data-bs-toggle="modal" data-bs-target="#keputusanModal{{ $p->id }}" style="background: #10b981; border: none;">
                                        <i class="fas fa-stamp me-1"></i> Putuskan
                                    </button>
                                @else
                                    {{-- Status final (disetujui/ditolak/selesai) HANYA tampilkan tombol Lihat Detail --}}
                                    <button type="button" class="btn btn-sm btn-outline-info px-3" data-bs-toggle="modal" data-bs-target="#detailModal{{ $p->id }}">
                                        <i class="fas fa-eye me-1"></i> Lihat Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="fas fa-check-circle fa-3x mb-3 text-emerald-400 d-block"></i>
                                Tidak ada pengajuan warga yang memerlukan persetujuan RW saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengajuans->hasPages())
            <div class="p-3 border-top border-white-10">
                {{ $pengajuans->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ==================== SEMUA MODAL DITEMPATKAN DI LUAR TABEL ==================== --}}
@foreach($pengajuans as $p)
    @php
        $isPendingDecision = in_array($p->status, ['menunggu_rw', 'menunggu_persetujuan_rw']);
    @endphp

    @if($isPendingDecision)
        {{-- MODAL KEPUTUSAN RW --}}
        <div class="modal fade" id="keputusanModal{{ $p->id }}" tabindex="-1" aria-labelledby="keputusanModalLabel{{ $p->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-height: 85vh;">
                    <div class="modal-header border-bottom border-white-10 sticky-top" style="background: #1e293b; z-index: 10;">
                        <h5 class="modal-title" id="keputusanModalLabel{{ $p->id }}">
                            <i class="fas fa-stamp text-emerald-400 me-2"></i>Keputusan Persetujuan RW
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form method="POST" action="{{ route('rw.pengawasan.approve', $p) }}">
                        @csrf
                        <div class="modal-body overflow-y-auto p-4" style="max-height: calc(85vh - 140px);">
                            {{-- Ringkasan Pengajuan --}}
                            <div class="p-3 mb-3 rounded" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.08);">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-white-50 d-block">Nomor Pengajuan</small>
                                        <strong class="text-emerald-400 font-monospace">{{ $p->nomor_pengajuan }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-white-50 d-block">Pemohon</small>
                                        <strong class="text-white">{{ optional($p->user)->name }} (RT {{ optional($p->rt)->kode_rt }})</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-white-50 d-block">NIK Pemohon</small>
                                        <span class="text-white">{{ optional($p->user->profile)->nik ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-white-50 d-block">Jenis Layanan</small>
                                        <span class="badge bg-primary text-white">{{ optional($p->jenisSurat)->nama }}</span>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top border-white-10">
                                        <small class="text-white-50 d-block">Keperluan Permohonan</small>
                                        <span class="text-white">{{ $p->keperluan }}</span>
                                    </div>
                                    @if($p->catatan_rt)
                                        <div class="col-12 mt-2">
                                            <div class="p-2 rounded small" style="background: rgba(59, 130, 246, 0.15); border-left: 3px solid #3b82f6;">
                                                <strong class="text-blue-300">Catatan Verifikasi RT:</strong>
                                                <div class="text-white mt-1">{{ $p->catatan_rt }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Form Keputusan --}}
                            <div class="mb-3">
                                <label class="form-label text-white fw-bold small">Tentukan Keputusan RW *</label>
                                <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.2);" required>
                                    <option value="disetujui">Setujui (Otorisasi & Terbitkan Nomor Surat Resmi)</option>
                                    <option value="ditolak">Tolak Permohonan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white fw-bold small">Catatan Keputusan RW</label>
                                <textarea name="catatan_rw" rows="3" class="form-control text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.2);" placeholder="Berikan catatan persetujuan atau alasan apabila permohonan ditolak..."></textarea>
                                <small class="text-white-50">Catatan ini akan dikirimkan sebagai notifikasi kepada pemohon dan Ketua RT terkait.</small>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-0" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); color: #7dd3fc;">
                                <i class="fas fa-info-circle me-1"></i> Apabila disetujui, nomor surat resmi tingkat RW akan digenerate otomatis dan status pengajuan akan diperbarui secara permanen.
                            </div>
                        </div>

                        <div class="modal-footer border-top border-white-10 sticky-bottom" style="background: #1e293b; z-index: 10;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-emerald text-white px-4" style="background: #10b981; border: none;">
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
                <div class="modal-content text-white" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-height: 85vh;">
                    <div class="modal-header border-bottom border-white-10 sticky-top" style="background: #1e293b; z-index: 10;">
                        <h5 class="modal-title" id="detailModalLabel{{ $p->id }}">
                            <i class="fas fa-file-alt text-info me-2"></i>Detail Pengajuan Surat ({{ $p->nomor_pengajuan }})
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body overflow-y-auto p-4" style="max-height: calc(85vh - 140px);">
                        {{-- Status Banner --}}
                        <div class="d-flex justify-content-between align-items-center p-3 rounded mb-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.08);">
                            <div>
                                <small class="text-white-50 d-block">Status Permohonan</small>
                                <span class="badge {{ $p->status_badge }} px-3 py-1 fs-6">
                                    {{ $p->status_label }}
                                </span>
                            </div>
                            @if($p->nomor_surat)
                                <div class="text-end">
                                    <small class="text-white-50 d-block">Nomor Surat Resmi</small>
                                    <strong class="text-emerald-400 font-monospace">{{ $p->nomor_surat }}</strong>
                                </div>
                            @endif
                        </div>

                        {{-- Identitas Pemohon --}}
                        <div class="p-3 rounded mb-3" style="background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.05);">
                            <h6 class="text-emerald-400 fw-bold mb-2"><i class="fas fa-user me-2"></i>Informasi Pemohon</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">Nama Lengkap</small>
                                    <span class="text-white">{{ optional($p->user)->name }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">NIK</small>
                                    <span class="text-white">{{ optional($p->user->profile)->nik ?? '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">Wilayah RT</small>
                                    <span class="text-white">RT {{ optional($p->rt)->kode_rt ?? '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">Tanggal Pengajuan</small>
                                    <span class="text-white">{{ $p->created_at ? $p->created_at->translatedFormat('d F Y, H:i') : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Isi Permohonan --}}
                        <div class="p-3 rounded mb-3" style="background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.05);">
                            <h6 class="text-emerald-400 fw-bold mb-2"><i class="fas fa-file-lines me-2"></i>Isi & Keperluan Permohonan</h6>
                            <div class="mb-2">
                                <small class="text-white-50 d-block">Jenis Layanan Surat</small>
                                <span class="fw-bold text-white">{{ optional($p->jenisSurat)->nama }}</span>
                            </div>
                            <div>
                                <small class="text-white-50 d-block">Keperluan Lengkap</small>
                                <div class="p-2 rounded text-white" style="background: rgba(15,23,42,0.6); font-size: 0.9rem;">
                                    {{ $p->keperluan }}
                                </div>
                            </div>
                        </div>

                        {{-- Catatan Verifikasi RT --}}
                        <div class="p-3 rounded mb-3" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                            <h6 class="text-blue-300 fw-bold mb-2"><i class="fas fa-check-double me-2"></i>Verifikasi Tingkat RT</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">Diverifikasi Oleh</small>
                                    <span class="text-white">{{ optional($p->rtApprover)->name ?? 'Ketua RT' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">Waktu Verifikasi RT</small>
                                    <span class="text-white">{{ $p->rt_approved_at ? $p->rt_approved_at->translatedFormat('d F Y, H:i') : '-' }}</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-white-50 d-block">Catatan RT</small>
                                    <div class="text-white">{{ $p->catatan_rt ?: 'Tidak ada catatan tambahan dari RT.' }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Keputusan Tingkat RW --}}
                        <div class="p-3 rounded mb-2" style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981;">
                            <h6 class="text-emerald-400 fw-bold mb-2"><i class="fas fa-gavel me-2"></i>Keputusan Pengurus RW</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">Diputuskan Oleh</small>
                                    <span class="text-white">{{ optional($p->rwApprover)->name ?? optional($p->approver)->name ?? 'Pengurus RW' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-white-50 d-block">Waktu Putusan RW</small>
                                    <span class="text-white">{{ $p->rw_approved_at ? $p->rw_approved_at->translatedFormat('d F Y, H:i') : ($p->approved_at ? $p->approved_at->translatedFormat('d F Y, H:i') : '-') }}</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-white-50 d-block">Catatan Keputusan RW</small>
                                    <div class="text-white">{{ $p->catatan_rw ?: 'Tidak ada catatan khusus.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-white-10 sticky-bottom" style="background: #1e293b; z-index: 10;">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
