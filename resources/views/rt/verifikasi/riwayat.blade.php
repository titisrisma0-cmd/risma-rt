@extends('layouts.app')

@section('title', 'Riwayat Persetujuan Permohonan')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('rt.verifikasi.permohonan') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Permohonan RT
                </a>
                <h1 class="page-title mb-0">Riwayat Persetujuan Permohonan</h1>
            </div>
            <p class="page-subtitle">Arsip permohonan surat & layanan warga RT {{ $user->getRtKode() ?? '01' }} yang telah selesai diproses</p>
        </div>

        <div>
            <a href="{{ route('rt.verifikasi.diteruskan') }}" class="btn btn-outline-secondary">
                <i class="fas fa-paper-plane me-1 text-primary"></i> Diteruskan ke RW
            </a>
        </div>
    </div>

    <div class="saas-card overflow-hidden">
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>No. Surat Terbit</th>
                        <th>Pemohon</th>
                        <th>Jenis Layanan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Disetujui Oleh</th>
                        <th class="text-end">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr>
                            <td class="font-monospace fw-bold text-emerald small">{{ $p->nomor_pengajuan }}</td>
                            <td class="font-monospace text-primary small">{{ $p->nomor_surat ?? '-' }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ optional($p->user)->name }}</div>
                            </td>
                            <td>
                                <span class="badge-soft-info">
                                    {{ optional($p->jenisSurat)->nama }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(in_array($p->status, ['disetujui', 'selesai', 'siap_diambil']))
                                    <span class="badge-soft-emerald">{{ $p->status_label }}</span>
                                @elseif($p->status === 'ditolak')
                                    <span class="badge-soft-danger">{{ $p->status_label }}</span>
                                @else
                                    <span class="badge-soft-warning">{{ $p->status_label }}</span>
                                @endif
                            </td>
                            <td class="text-center text-secondary small">
                                {{ optional($p->approver)->name ?? (optional($p->rwApprover)->name ?? 'Pengurus') }}
                            </td>
                            <td class="text-end">
                                @if($p->nomor_surat)
                                    <a href="{{ route('layanan.surat.cetak', $p) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-print me-1 text-emerald"></i> Cetak PDF
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="fas fa-archive fa-3x mb-3 text-muted d-block opacity-50"></i>
                                Belum ada arsip riwayat permohonan yang selesai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengajuans->hasPages())
            <div class="p-3 border-top bg-white">
                {{ $pengajuans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
