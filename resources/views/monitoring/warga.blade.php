@extends('layouts.app')

@section('title', 'Monitoring Warga per RT — Pengurus RW')

@section('content')
<div class="py-2">
    <div class="page-header-saas mb-4">
        <div class="page-header-info">
            <div class="breadcrumb-saas">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Monitoring Wilayah</span>
                <i class="fas fa-chevron-right small text-muted"></i>
                <span>Warga per RT</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">
                Monitoring Warga per Wilayah RT
            </h1>
            <p class="text-secondary mb-0">Pengawasan kependudukan berjenjang — Pilih RT untuk melihat data kependudukan dan keluarga.</p>
        </div>
    </div>

    {{-- Pemilihan Wilayah RT --}}
    <div class="row g-3 mb-4">
        @foreach($rts as $r)
            @php $isSelected = ($selectedRt && $selectedRt->id === $r->id); @endphp
            <div class="col-6 col-md-3 col-xl-3">
                <a href="{{ route('rw.monitoring.warga', ['rt_id' => $r->id]) }}" class="text-decoration-none">
                    <div class="saas-card p-3 text-center h-100" style="{{ $isSelected ? 'border: 2px solid var(--primary-green) !important; background: var(--soft-green) !important;' : '' }}">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <i class="fas fa-landmark {{ $isSelected ? 'text-success' : 'text-secondary' }}"></i>
                            <span class="h5 {{ $isSelected ? 'text-success fw-bold' : 'text-dark fw-bold' }} mb-0">RT {{ $r->kode_rt }}</span>
                        </div>
                        <div class="small text-secondary mb-1">Ketua: {{ optional($r->ketua)->name ?? '-' }}</div>
                        <span class="badge {{ $isSelected ? 'badge-soft-success' : 'badge-soft-primary' }}">
                            {{ $r->kartu_keluargas_count }} KK Terdata
                        </span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    @if(!$selectedRt)
        <div class="empty-state-saas p-5 my-4">
            <div class="empty-state-icon">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h4 class="empty-state-title">Pilih Wilayah RT Terlebih Dahulu</h4>
            <p class="empty-state-desc">
                Sesuai prinsip pemisahan data kependudukan berjenjang, silakan klik salah satu kartu RT di atas untuk memantau data warga dan kartu keluarga di RT tersebut.
            </p>
        </div>
    @else
        {{-- Statistik Kependudukan RT Terpilih --}}
        @if($stats)
            <div class="saas-card p-3 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-venus-mars text-warning"></i>
                        <span class="text-dark fw-bold">Statistik Kependudukan RT {{ $selectedRt->kode_rt }}:</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small">
                        <span class="badge badge-soft-info"><i class="fas fa-id-card me-1"></i> Total KK: <strong>{{ $stats['total_kk'] }}</strong></span>
                        <span class="badge badge-soft-primary"><i class="fas fa-users me-1"></i> Total Jiwa: <strong>{{ $stats['total_jiwa'] }}</strong></span>
                        <span class="badge badge-soft-success"><i class="fas fa-male me-1"></i> Laki-laki: <strong>{{ $stats['laki_laki'] }}</strong></span>
                        <span class="badge badge-soft-danger"><i class="fas fa-female me-1"></i> Perempuan: <strong>{{ $stats['perempuan'] }}</strong></span>
                        <span class="badge badge-soft-warning"><i class="fas fa-question-circle me-1"></i> N/A: <strong>{{ $stats['belum_diketahui'] }}</strong></span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filter & Search di RT Terpilih --}}
        <div class="saas-card p-3 mb-4">
            <form method="GET" action="{{ route('rw.monitoring.warga') }}" class="row g-2 align-items-center">
                <input type="hidden" name="rt_id" value="{{ $selectedRt->id }}">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama warga, NIK, No KK di RT {{ $selectedRt->kode_rt }}..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-grow-1">Cari Warga</button>
                    @if(request('q'))
                        <a href="{{ route('rw.monitoring.warga', ['rt_id' => $selectedRt->id]) }}" class="btn btn-light"><i class="fas fa-redo"></i></a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Data Warga RT Terpilih --}}
        <div class="saas-card mb-4">
            <div class="card-header-saas">
                <h5 class="card-title-saas">
                    <i class="fas fa-users text-success"></i> Daftar Warga RT {{ $selectedRt->kode_rt }}
                </h5>
                <span class="badge badge-soft-primary px-3 py-1">
                    {{ $wargas->total() }} Warga Ditemukan
                </span>
            </div>

            <div class="table-responsive">
                <table class="table-saas table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>NIK</th>
                            <th>No. KK</th>
                            <th>Status Hubungan</th>
                            <th>Alamat</th>
                            <th>No. HP</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wargas as $w)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark small">{{ $w->nama_lengkap }}</div>
                                    @php
                                        $jkW = strtoupper(trim((string)($w->jenis_kelamin ?: optional($w->user)->jenis_kelamin)));
                                        $labelJk = ($jkW === 'L' || $jkW === 'LAKI-LAKI') ? 'Laki-laki' : (($jkW === 'P' || $jkW === 'PEREMPUAN') ? 'Perempuan' : 'Belum Diketahui');
                                    @endphp
                                    <small class="text-secondary" style="font-size: 0.75rem;">{{ $labelJk }}</small>
                                </td>
                                <td class="font-monospace text-secondary small">{{ $w->nik ?? '-' }}</td>
                                <td class="font-monospace text-primary small">
                                    @if($w->kartuKeluarga)
                                        <a href="{{ route('kartu-keluarga.show', $w->kartuKeluarga) }}" class="text-primary text-decoration-none fw-semibold">
                                            {{ $w->kartuKeluarga->no_kk }}
                                        </a>
                                    @else
                                        {{ $w->no_kk ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft-info">
                                        {{ $w->status_hubungan_keluarga ?? 'Warga' }}
                                    </span>
                                </td>
                                <td class="text-secondary small" style="max-width: 250px;">{{ Str::limit($w->alamat, 50) }}</td>
                                <td class="text-secondary small font-monospace">{{ $w->no_hp ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-soft-success text-capitalize">
                                        {{ $w->status ?? 'aktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    Belum ada data warga di RT {{ $selectedRt->kode_rt }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($wargas->hasPages())
                <div class="p-3 border-top">
                    {{ $wargas->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
