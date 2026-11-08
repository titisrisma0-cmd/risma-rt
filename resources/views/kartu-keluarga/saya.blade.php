@extends('layouts.app')

@section('title', 'Data Keluarga Saya')

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-id-card text-emerald me-2"></i>Data Keluarga Saya</h1>
        <p class="page-subtitle">Informasi resmi Kartu Keluarga dan anggota keluarga terdaftar</p>
    </div>

    @if(!$kk)
        <div class="saas-card p-5 text-center">
            <div class="stat-icon-wrapper mx-auto mb-3" style="width: 64px; height: 64px; background-color: #FEF3C7; color: #D97706; font-size: 1.75rem;">
                <i class="fas fa-address-card"></i>
            </div>
            <h4 class="text-dark fw-bold mb-2">Data Kartu Keluarga Belum Terhubung</h4>
            <p class="text-secondary max-w-lg mx-auto mb-4" style="max-width: 520px;">
                Akun Anda belum terhubung dengan nomor Kartu Keluarga resmi di wilayah RT Anda. Silakan hubungi Ketua RT untuk menghubungkan data keluarga Anda.
            </p>
            <div>
                <a href="{{ route('profile.index') }}" class="btn btn-emerald text-white px-4">
                    <i class="fas fa-user-edit me-1"></i> Perbarui Profil Saya
                </a>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="text-secondary small mb-1 fw-medium">Nomor Kartu Keluarga</div>
                    <div class="h5 text-emerald fw-bold mb-1 font-monospace">{{ $kk->no_kk }}</div>
                    <div class="small text-secondary">Resmi Terdaftar</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="text-secondary small mb-1 fw-medium">Kepala Keluarga</div>
                    <div class="h5 text-dark fw-bold mb-1">{{ $kk->nama_kepala_keluarga }}</div>
                    <div class="small text-emerald"><i class="fas fa-user-tie me-1"></i> Penanggung Jawab</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="text-secondary small mb-1 fw-medium">Wilayah Domisili</div>
                    <div class="h5 text-dark fw-bold mb-1">RT {{ optional($kk->rt)->kode_rt ?? '-' }} / RW 01</div>
                    <div class="small text-info"><i class="fas fa-map-marker-alt me-1"></i> Risma Smart RW</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="text-secondary small mb-1 fw-medium">Status Kepemilikan Rumah</div>
                    <div class="h5 text-dark fw-bold mb-1 text-capitalize">{{ $kk->status_rumah }}</div>
                    <div class="small text-warning"><i class="fas fa-home me-1"></i> Rumah {{ $kk->status_rumah }}</div>
                </div>
            </div>
        </div>

        <div class="saas-card p-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper" style="background-color: var(--soft-green); color: var(--primary-green);">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block">Alamat Tempat Tinggal:</span>
                    <span class="text-dark fw-semibold">{{ $kk->alamat }}</span>
                </div>
            </div>
        </div>

        <div class="saas-card overflow-hidden">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-users text-emerald me-2"></i>Daftar Anggota Keluarga Terdaftar</h6>
            </div>
            <div class="table-responsive">
                <table class="table-saas">
                    <thead>
                        <tr>
                            <th width="45">No</th>
                            <th>Nama Lengkap</th>
                            <th>NIK</th>
                            <th>Hubungan Keluarga</th>
                            <th>Jenis Kelamin</th>
                            <th>Pekerjaan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kk->anggota as $idx => $anggota)
                            <tr>
                                <td class="text-secondary">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $anggota->nama_lengkap }}</span>
                                    @if($anggota->user_id === Auth::id())
                                        <span class="badge-soft-emerald ms-1">Saya</span>
                                    @endif
                                </td>
                                <td class="font-monospace text-emerald fw-semibold">
                                    {{ Str::mask($anggota->nik ?? '0000000000000000', '*', 4, 8) }}
                                </td>
                                <td>
                                    <span class="badge {{ $anggota->status_hubungan_keluarga === 'Kepala Keluarga' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $anggota->status_hubungan_keluarga ?? 'Anggota' }}
                                    </span>
                                </td>
                                <td class="text-secondary">
                                    {{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : ($anggota->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                                </td>
                                <td class="text-secondary small">{{ $anggota->pekerjaan ?? '-' }}</td>
                                <td>
                                    <span class="badge-soft-emerald">Aktif</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fas fa-users fa-3x mb-3 text-muted d-block opacity-50"></i>
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
