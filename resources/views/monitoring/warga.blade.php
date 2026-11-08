@extends('layouts.app')

@section('title', 'Monitoring Warga per RT — Pengurus RW')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="h3 text-white mb-1"><i class="fas fa-users-viewfinder text-emerald-400 me-2"></i>Monitoring Warga per RT</h1>
        <p class="text-white-50 mb-0">Pengawasan kependudukan berjenjang — Buka wilayah RT untuk melihat daftar warga</p>
    </div>

    {{-- Pemilihan Wilayah RT --}}
    <div class="row g-3 mb-4">
        @foreach($rts as $r)
            @php $isSelected = ($selectedRt && $selectedRt->id === $r->id); @endphp
            <div class="col-6 col-md-3 col-xl-3">
                <a href="{{ route('rw.monitoring.warga', ['rt_id' => $r->id]) }}" class="text-decoration-none">
                    <div class="card p-3 text-center border-0 h-100" style="background: {{ $isSelected ? 'rgba(16, 185, 129, 0.25)' : 'rgba(30, 41, 59, 0.7)' }}; backdrop-filter: blur(10px); border: 1px solid {{ $isSelected ? '#10b981' : 'rgba(255,255,255,0.08)' }} !important; border-radius: 12px; transition: all 0.2s;">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <i class="fas fa-landmark text-warning"></i>
                            <span class="h5 text-white fw-bold mb-0">RT {{ $r->kode_rt }}</span>
                        </div>
                        <div class="small text-white-50 mb-1">Ketua: {{ optional($r->ketua)->name ?? '-' }}</div>
                        <span class="badge bg-slate-700 text-white-50 px-2 py-1 rounded-pill">
                            {{ $r->kartu_keluargas_count }} Kartu Keluarga
                        </span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    @if(!$selectedRt)
        <div class="card p-5 text-center border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
            <i class="fas fa-map-marked-alt fa-3x text-emerald-400 mb-3"></i>
            <h4 class="text-white mb-2">Pilih Wilayah RT Terlebih Dahulu</h4>
            <p class="text-white-50 max-w-lg mx-auto mb-0">
                Sesuai prinsip pemisahan kewenangan berjenjang, silakan klik salah satu kartu RT di atas untuk memantau data warga dan kartu keluarga di RT tersebut.
            </p>
        </div>
    @else
        {{-- Statistik Kependudukan RT Terpilih --}}
        @if($stats)
            <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-venus-mars text-warning"></i>
                        <span class="text-white fw-bold">Statistik Kependudukan RT {{ $selectedRt->kode_rt }}:</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 text-white small">
                        <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-id-card me-1"></i> Total KK: <strong>{{ $stats['total_kk'] }}</strong></span>
                        <span class="badge bg-primary px-3 py-2"><i class="fas fa-users me-1"></i> Total Jiwa: <strong>{{ $stats['total_jiwa'] }}</strong></span>
                        <span class="badge bg-success px-3 py-2"><i class="fas fa-male me-1"></i> Laki-laki: <strong>{{ $stats['laki_laki'] }}</strong></span>
                        <span class="badge bg-danger px-3 py-2"><i class="fas fa-female me-1"></i> Perempuan: <strong>{{ $stats['perempuan'] }}</strong></span>
                        <span class="badge bg-secondary px-3 py-2"><i class="fas fa-question-circle me-1"></i> Belum Diketahui: <strong>{{ $stats['belum_diketahui'] }}</strong></span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filter & Search di RT Terpilih --}}
        <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
            <form method="GET" action="{{ route('rw.monitoring.warga') }}" class="row g-3 align-items-center">
                <input type="hidden" name="rt_id" value="{{ $selectedRt->id }}">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text text-white-50" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.15); border-right: none;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control text-white" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.15); border-left: none;" placeholder="Cari nama warga, NIK, No KK di RT {{ $selectedRt->kode_rt }}..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Cari Warga</button>
                    @if(request('q'))
                        <a href="{{ route('rw.monitoring.warga', ['rt_id' => $selectedRt->id]) }}" class="btn btn-outline-light"><i class="fas fa-redo"></i></a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Data Warga RT Terpilih --}}
        <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
            <div class="card-header border-bottom border-white-10 p-3 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.85);">
                <h5 class="text-white mb-0">
                    <i class="fas fa-users text-emerald-400 me-2"></i>Daftar Warga RT {{ $selectedRt->kode_rt }}
                </h5>
                <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1">
                    {{ $wargas->total() }} Warga Ditemukan
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white">
                    <thead style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <tr>
                            <th class="py-3 px-3">Nama Lengkap</th>
                            <th class="py-3 px-3">NIK</th>
                            <th class="py-3 px-3">No. KK</th>
                            <th class="py-3 px-3">Status Hubungan</th>
                            <th class="py-3 px-3">Alamat</th>
                            <th class="py-3 px-3">No. HP</th>
                            <th class="py-3 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wargas as $w)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="py-3 px-3">
                                    <div class="fw-bold text-white">{{ $w->nama_lengkap }}</div>
                                    @php
                                        $jkW = strtoupper(trim((string)($w->jenis_kelamin ?: optional($w->user)->jenis_kelamin)));
                                        $labelJk = ($jkW === 'L' || $jkW === 'LAKI-LAKI') ? 'Laki-laki' : (($jkW === 'P' || $jkW === 'PEREMPUAN') ? 'Perempuan' : 'Belum Diketahui');
                                    @endphp
                                    <small class="text-white-50">{{ $labelJk }}</small>
                                </td>
                                <td class="py-3 px-3 font-monospace text-blue-300 small">{{ $w->nik ?? '-' }}</td>
                                <td class="py-3 px-3 font-monospace text-emerald-400 small">
                                    @if($w->kartuKeluarga)
                                        <a href="{{ route('kartu-keluarga.show', $w->kartuKeluarga) }}" class="text-emerald-400 text-decoration-none">
                                            {{ $w->kartuKeluarga->no_kk }}
                                        </a>
                                    @else
                                        {{ $w->no_kk ?? '-' }}
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="badge bg-slate-700 text-white-50">
                                        {{ $w->status_hubungan_keluarga ?? 'Warga' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-white-50 small" style="max-width: 250px;">{{ Str::limit($w->alamat, 50) }}</td>
                                <td class="py-3 px-3 text-white-50 small">{{ $w->no_hp ?? '-' }}</td>
                                <td class="py-3 px-3 text-center">
                                    <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-capitalize">
                                        {{ $w->status ?? 'aktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-white-50">
                                    Belum ada data warga di RT {{ $selectedRt->kode_rt }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($wargas->hasPages())
                <div class="p-3 border-top border-white-10">
                    {{ $wargas->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
