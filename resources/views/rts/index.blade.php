@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="text-white"><i class="fas fa-sitemap"></i> Kelola RT</h2><p class="text-white-50 mb-0">Daftar RT di bawah RW</p></div>
    </div>

    <div class="card bg-dark border-secondary mb-4"><div class="card-body">
        <form method="POST" action="{{ route('rts.store') }}" class="row g-3 align-items-end">@csrf
            <div class="col-md-3"><label class="form-label text-white">RW</label><select name="rw_id" class="form-select bg-dark text-white border-secondary" required>@foreach($rws as $rw)<option value="{{ $rw->id }}">RW {{ $rw->kode_rw }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label text-white">Kode RT</label><input name="kode_rt" class="form-control bg-dark text-white border-secondary" required></div>
            <div class="col-md-4"><label class="form-label text-white">Nama RT</label><input name="nama_rt" class="form-control bg-dark text-white border-secondary"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Tambah RT</button></div>
        </form>
    </div></div>

    <div class="card bg-dark border-secondary"><div class="table-responsive"><table class="table table-dark table-hover mb-0">
        <thead><tr><th>RW</th><th>RT</th><th>Nama</th><th>Jumlah KK</th><th>Jumlah Warga</th><th>Aksi</th></tr></thead><tbody>
        @forelse($rts as $rt)
            <tr><form method="POST" action="{{ route('rts.update', $rt) }}">@csrf @method('PUT')
                <td><select name="rw_id" class="form-select form-select-sm bg-dark text-white">@foreach($rws as $rw)<option value="{{ $rw->id }}" @selected($rt->rw_id === $rw->id)>RW {{ $rw->kode_rw }}</option>@endforeach</select></td>
                <td><input name="kode_rt" value="{{ $rt->kode_rt }}" class="form-control form-control-sm bg-dark text-white"></td>
                <td><input name="nama_rt" value="{{ $rt->nama_rt }}" class="form-control form-control-sm bg-dark text-white"></td>
                <td>
                    <span class="badge bg-info">{{ $rt->keluarga_count }} KK</span>
                    <button type="button" class="btn btn-sm btn-outline-info ms-1" data-bs-toggle="collapse" data-bs-target="#keluarga-rt-{{ $rt->id }}" aria-expanded="false">
                        <i class="fas fa-users"></i> Lihat Anggota
                    </button>
                </td>
                <td>{{ $rt->warga_profiles_count }}</td>
                <td class="text-nowrap"><button class="btn btn-sm btn-warning">Simpan</button></form>
                    <form method="POST" action="{{ route('rts.destroy', $rt) }}" class="d-inline" onsubmit="return confirm('Hapus RT ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" @disabled($rt->warga_profiles_count > 0)>Hapus</button></form>
                </td>
            </tr>
            <tr class="collapse" id="keluarga-rt-{{ $rt->id }}">
                <td colspan="6" class="bg-black bg-opacity-25 p-3">
                    <div class="row g-3">
                        @forelse($rt->keluarga as $keluarga)
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 bg-dark border-info">
                                    <div class="card-header border-info">
                                        <div class="fw-bold text-info">No. KK: {{ $keluarga['no_kk'] }}</div>
                                        <small class="text-white-50">Kepala keluarga: {{ $keluarga['kepala_keluarga'] }}</small>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="mb-2"><span class="badge bg-success">{{ $keluarga['anggota']->count() }} anggota</span></div>
                                        <ol class="mb-0 ps-3 text-white">
                                            @foreach($keluarga['anggota'] as $nama)
                                                <li class="mb-1">{{ $nama }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-secondary">Belum ada keluarga dalam RT ini.</div>
                        @endforelse
                    </div>
                </td>
            </tr>
        @empty<tr><td colspan="6" class="text-center text-secondary">Belum ada data RT.</td></tr>@endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
