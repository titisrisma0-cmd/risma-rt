@extends('layouts.app')

@section('title', 'Pengajuan yang Diteruskan ke RW')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('rt.verifikasi.permohonan') }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Permohonan RT
                </a>
                <h1 class="h3 text-white mb-0">Pengajuan yang Diteruskan ke RW</h1>
            </div>
            <p class="text-white-50 mb-0">Status permohonan warga RT {{ $user->getRtKode() ?? '01' }} yang sedang atau telah diproses oleh Pengurus RW</p>
        </div>

        <div>
            <a href="{{ route('rt.verifikasi.riwayat') }}" class="btn btn-outline-light">
                <i class="fas fa-history me-1"></i> Riwayat Selesai
            </a>
        </div>
    </div>

    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.85); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">No. Pengajuan</th>
                        <th class="py-3 px-3">Pemohon</th>
                        <th class="py-3 px-3">Jenis Layanan</th>
                        <th class="py-3 px-3">Catatan RT</th>
                        <th class="py-3 px-3">Keputusan & Catatan RW</th>
                        <th class="py-3 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-3 font-monospace text-emerald-400 small">{{ $p->nomor_pengajuan }}</td>
                            <td class="py-3 px-3">
                                <div class="fw-bold text-white">{{ optional($p->user)->name }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="badge bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    {{ optional($p->jenisSurat)->nama }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-white-50 small" style="max-width: 250px;">
                                {{ $p->catatan_rt ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-white-50 small" style="max-width: 250px;">
                                @if($p->catatan_rw)
                                    <div class="text-white">{{ $p->catatan_rw }}</div>
                                    <small class="text-white-50">Oleh: {{ optional($p->rwApprover)->name ?? 'RW' }}</small>
                                @else
                                    <span class="text-white-50 font-italic">Menunggu telaah Pengurus RW</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge {{ $p->status_badge }} px-2 py-1">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-white-50">
                                <i class="fas fa-paper-plane fa-3x mb-3 text-white-50 d-block"></i>
                                Belum ada permohonan yang diteruskan ke RW.
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
@endsection
