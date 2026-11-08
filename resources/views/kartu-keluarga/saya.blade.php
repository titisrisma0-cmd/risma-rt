@extends('layouts.app')

@section('title', 'Data Keluarga Saya')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="h3 text-white mb-1"><i class="fas fa-id-card text-emerald-400 me-2"></i>Data Keluarga Saya</h1>
        <p class="text-white-50 mb-0">Informasi resmi Kartu Keluarga dan anggota keluarga terdaftar</p>
    </div>

    @if(!$kk)
        <div class="card p-5 text-center border-0" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
            <i class="fas fa-address-card fa-4x text-amber-400 mb-3"></i>
            <h4 class="text-white mb-2">Data Kartu Keluarga Belum Terhubung</h4>
            <p class="text-white-50 max-w-lg mx-auto mb-4">
                Akun Anda belum terhubung dengan nomor Kartu Keluarga resmi di wilayah RT Anda. Silakan hubungi Ketua RT untuk menghubungkan data keluarga Anda.
            </p>
            <div>
                <a href="{{ route('profile.index') }}" class="btn btn-outline-light px-4">
                    <i class="fas fa-user-edit me-1"></i> Perbarui Profil Saya
                </a>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <div class="text-white-50 small mb-1">Nomor Kartu Keluarga</div>
                    <div class="h5 text-white fw-bold mb-1 font-monospace text-emerald-400">{{ $kk->no_kk }}</div>
                    <div class="small text-white-50">Resmi Terdaftar</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <div class="text-white-50 small mb-1">Kepala Keluarga</div>
                    <div class="h5 text-white fw-bold mb-1">{{ $kk->nama_kepala_keluarga }}</div>
                    <div class="small text-emerald-400"><i class="fas fa-user-tie me-1"></i> Penanggung Jawab</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <div class="text-white-50 small mb-1">Wilayah Domisili</div>
                    <div class="h5 text-white fw-bold mb-1">RT {{ optional($kk->rt)->kode_rt ?? '-' }} / RW 01</div>
                    <div class="small text-blue-400"><i class="fas fa-map-marker-alt me-1"></i> Risma Smart RW</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card p-3 border-0 h-100" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <div class="text-white-50 small mb-1">Status Kepemilikan Rumah</div>
                    <div class="h5 text-white fw-bold mb-1 text-capitalize">{{ $kk->status_rumah }}</div>
                    <div class="small text-amber-400"><i class="fas fa-home me-1"></i> Rumah {{ $kk->status_rumah }}</div>
                </div>
            </div>
        </div>

        <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-map-marked-alt text-emerald-400 fa-lg"></i>
                <div>
                    <span class="text-white-50 small d-block">Alamat Tempat Tinggal:</span>
                    <span class="text-white fw-semibold">{{ $kk->alamat }}</span>
                </div>
            </div>
        </div>

        <div class="card border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px; overflow: hidden;">
            <div class="card-header border-bottom border-white-10 p-3" style="background: rgba(15, 23, 42, 0.85);">
                <h5 class="text-white mb-0"><i class="fas fa-users text-emerald-400 me-2"></i>Daftar Anggota Keluarga Terdaftar</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white">
                    <thead style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <tr>
                            <th class="py-3 px-3">No</th>
                            <th class="py-3 px-3">Nama Lengkap</th>
                            <th class="py-3 px-3">NIK</th>
                            <th class="py-3 px-3">Hubungan Keluarga</th>
                            <th class="py-3 px-3">Jenis Kelamin</th>
                            <th class="py-3 px-3">Pekerjaan</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kk->anggota as $idx => $anggota)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="py-3 px-3 text-white-50">{{ $idx + 1 }}</td>
                                <td class="py-3 px-3">
                                    <span class="fw-bold text-white">{{ $anggota->nama_lengkap }}</span>
                                    @if($anggota->user_id === Auth::id())
                                        <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 ms-1">Saya</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-monospace text-blue-300">
                                    {{ Str::mask($anggota->nik ?? '0000000000000000', '*', 4, 8) }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="badge {{ $anggota->status_hubungan_keluarga === 'Kepala Keluarga' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-slate-700 text-white-50' }}">
                                        {{ $anggota->status_hubungan_keluarga ?? 'Anggota' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    {{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : ($anggota->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                                </td>
                                <td class="py-3 px-3 text-white-50 small">{{ $anggota->pekerjaan ?? '-' }}</td>
                                <td class="py-3 px-3">
                                    <span class="badge bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Aktif</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-white-50">
                                    Belum ada data anggota keluarga. Hubungi Ketua RT Anda untuk melengkapi data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
