@extends('layouts.app')

@section('title', 'Monitoring Layanan RT — Pengurus RW')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="h3 text-white mb-1"><i class="fas fa-handshake-angle text-emerald-400 me-2"></i>Monitoring Layanan RT</h1>
        <p class="text-white-50 mb-0">Pengawasan permohonan administrasi dan pelayanan masyarakat di seluruh RT</p>
    </div>

    {{-- Filter Card --}}
    <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
        <form method="GET" action="{{ route('rw.monitoring.layanan') }}" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label text-white-50 small mb-1">Filter Wilayah RT</label>
                <select name="rt_id" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                    <option value="">Semua RT</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-white-50 small mb-1">Status Permohonan</label>
                <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Verifikasi RT</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Sedang Diproses RT</option>
                    <option value="menunggu_rw" {{ request('status') == 'menunggu_rw' ? 'selected' : '' }}>Menunggu Persetujuan RW</option>
                    <option value="siap_diambil" {{ request('status') == 'siap_diambil' ? 'selected' : '' }}>Disetujui / Selesai</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                @if(request()->hasAny(['rt_id', 'status']))
                    <a href="{{ route('rw.monitoring.layanan') }}" class="btn btn-outline-light w-100 py-2">Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Monitoring Layanan --}}
    <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead style="background: rgba(15, 23, 42, 0.85); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <tr>
                        <th class="py-3 px-3">No. Pengajuan</th>
                        <th class="py-3 px-3">Pemohon</th>
                        <th class="py-3 px-3">Wilayah</th>
                        <th class="py-3 px-3">Jenis Surat</th>
                        <th class="py-3 px-3">Keperluan</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-end">Tgl Pengajuan</th>
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
                                    RT {{ optional($p->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">{{ optional($p->jenisSurat)->nama }}</td>
                            <td class="py-3 px-3 text-white-50 small" style="max-width: 250px;">{{ Str::limit($p->keperluan, 60) }}</td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge {{ $p->status_badge }} px-2 py-1">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-end text-white-50 font-monospace small">
                                {{ $p->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                Belum ada data pelayanan masyarakat yang tercatat.
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
