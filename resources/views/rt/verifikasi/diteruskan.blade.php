@extends('layouts.app')

@section('title', 'Pengajuan yang Diteruskan ke RW')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('rt.verifikasi.permohonan') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Permohonan RT
                </a>
                <h1 class="page-title mb-0">Pengajuan yang Diteruskan ke RW</h1>
            </div>
            <p class="page-subtitle">Status permohonan warga RT {{ $user->getRtKode() ?? '01' }} yang sedang atau telah diproses oleh Pengurus RW</p>
        </div>

        <div>
            <a href="{{ route('rt.verifikasi.riwayat') }}" class="btn btn-outline-secondary">
                <i class="fas fa-history me-1"></i> Riwayat Selesai
            </a>
        </div>
    </div>

    <div class="saas-card overflow-hidden">
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>Pemohon</th>
                        <th>Jenis Layanan</th>
                        <th>Catatan RT</th>
                        <th>Keputusan & Catatan RW</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr>
                            <td class="font-monospace fw-bold text-emerald">{{ $p->nomor_pengajuan }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ optional($p->user)->name }}</div>
                            </td>
                            <td>
                                <span class="badge-soft-info">
                                    {{ optional($p->jenisSurat)->nama }}
                                </span>
                            </td>
                            <td class="text-secondary small" style="max-width: 250px;">
                                {{ $p->catatan_rt ?? '-' }}
                            </td>
                            <td class="text-secondary small" style="max-width: 250px;">
                                @if($p->catatan_rw)
                                    <div class="text-dark fw-medium">{{ $p->catatan_rw }}</div>
                                    <small class="text-secondary">Oleh: {{ optional($p->rwApprover)->name ?? 'RW' }}</small>
                                @else
                                    <span class="text-muted fst-italic">Menunggu telaah Pengurus RW</span>
                                @endif
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <i class="fas fa-paper-plane fa-3x mb-3 text-muted d-block opacity-50"></i>
                                Belum ada permohonan yang diteruskan ke RW.
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
