@extends('layouts.app')

@section('title', 'Daftar Peserta Kegiatan')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('kegiatan.index') }}">Kegiatan Warga</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Peserta Kegiatan</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-users text-success me-2"></i>Peserta: {{ $kegiatan->judul }}</h1>
            <p class="page-subtitle">Pencatatan dan verifikasi absensi kehadiran warga dalam agenda kegiatan.</p>
        </div>
        <div>
            <a href="{{ route('kegiatan.index') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Kegiatan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-saas alert-saas-success mb-4">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="saas-card">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-clipboard-check text-success me-2"></i>Presensi Kehadiran Peserta
            </h6>
            <span class="badge badge-soft-secondary">{{ $peserta->count() }} Warga Terdaftar</span>
        </div>
        <div class="table-responsive">
            <table class="table table-saas align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama Warga</th>
                        <th>Wilayah RT</th>
                        <th>Status Saat Ini</th>
                        <th style="min-width: 250px;">Ubah Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peserta as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->user->name }}</div>
                                <small class="text-muted">NIK: {{ $item->user->profile?->nik ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-secondary">RT {{ $item->user->profile?->wilayahRt?->kode_rt ?? '-' }}</span>
                            </td>
                            <td>
                                @if($item->status === 'hadir')
                                    <span class="badge badge-soft-success"><i class="fas fa-check me-1"></i>Hadir</span>
                                @elseif($item->status === 'tidak_hadir')
                                    <span class="badge badge-soft-danger"><i class="fas fa-times me-1"></i>Tidak Hadir</span>
                                @else
                                    <span class="badge badge-soft-info"><i class="fas fa-clock me-1"></i>Terdaftar</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('kegiatan.peserta.update', [$kegiatan, $item->user]) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select-saas form-select-sm" style="max-width: 170px;">
                                        <option value="terdaftar" @selected($item->status === 'terdaftar')>Terdaftar</option>
                                        <option value="hadir" @selected($item->status === 'hadir')>Hadir</option>
                                        <option value="tidak_hadir" @selected($item->status === 'tidak_hadir')>Tidak Hadir</option>
                                    </select>
                                    <button type="submit" class="btn btn-saas-primary btn-sm">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-xmark"></i></div>
                                    <h6 class="empty-state-title">Belum Ada Peserta</h6>
                                    <p class="empty-state-desc">Belum ada warga yang mendaftar pada kegiatan ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
