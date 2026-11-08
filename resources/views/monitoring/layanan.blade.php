@extends('layouts.app')

@section('title', 'Monitoring Layanan RT — Pengurus RW')

@section('content')
<div class="py-2">
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Monitoring Wilayah</span>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Layanan RT</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Monitoring Layanan Administrasi RT
            </h1>
            <p class="text-secondary mb-0">Pengawasan pengajuan surat dan status pelayanan masyarakat dari seluruh wilayah RT.</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="saas-card p-3 mb-4">
        <form method="GET" action="{{ route('rw.monitoring.layanan') }}" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-secondary mb-1">Filter Wilayah RT</label>
                <select name="rt_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Wilayah RT</option>
                    @foreach($rts as $r)
                        <option value="{{ $r->id }}" {{ request('rt_id') == $r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small text-secondary mb-1">Status Permohonan</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
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
                    <a href="{{ route('rw.monitoring.layanan') }}" class="btn btn-sm btn-light w-100">Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Monitoring Layanan --}}
    <div class="saas-card mb-4">
        <div class="card-header-saas">
            <h5 class="card-title-saas">
                <i class="fas fa-list text-success"></i> Data Permohonan Surat Masuk
            </h5>
            <span class="badge badge-soft-primary px-3 py-1">{{ $pengajuans->total() }} Permohonan</span>
        </div>

        <div class="table-responsive">
            <table class="table-saas table align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>Pemohon</th>
                        <th>Wilayah</th>
                        <th>Jenis Surat</th>
                        <th>Keperluan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Tgl Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr>
                            <td class="font-monospace text-primary fw-bold small">{{ $p->nomor_pengajuan }}</td>
                            <td>
                                <div class="fw-bold text-dark small">{{ optional($p->user)->name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary">
                                    RT {{ optional($p->rt)->kode_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="small">{{ optional($p->jenisSurat)->nama }}</td>
                            <td class="text-secondary small" style="max-width: 250px;">{{ Str::limit($p->keperluan, 60) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $p->status_badge }} px-2.5 py-1">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td class="text-end text-secondary font-monospace small">
                                {{ $p->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                Belum ada data pelayanan masyarakat yang tercatat.
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
@endsection
