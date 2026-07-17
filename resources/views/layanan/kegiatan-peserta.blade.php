@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h2>Peserta: {{ $kegiatan->judul }}</h2><p class="text-muted mb-0">Tandai kehadiran peserta kegiatan.</p></div><a href="{{ route('kegiatan.index') }}" class="btn btn-outline-secondary">Kembali</a></div>
    <div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Nama</th><th>RT</th><th>Status</th><th>Ubah Kehadiran</th></tr></thead><tbody>@forelse($peserta as $item)<tr><td>{{ $item->user->name }}</td><td>{{ $item->user->profile?->wilayahRt?->kode_rt ?? '-' }}</td><td><span class="badge bg-secondary">{{ str_replace('_', ' ', $item->status) }}</span></td><td><form method="POST" action="{{ route('kegiatan.peserta.update', [$kegiatan, $item->user]) }}" class="d-flex gap-2">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm" style="max-width:160px"><option value="terdaftar" @selected($item->status==='terdaftar')>Terdaftar</option><option value="hadir" @selected($item->status==='hadir')>Hadir</option><option value="tidak_hadir" @selected($item->status==='tidak_hadir')>Tidak hadir</option></select><button class="btn btn-sm btn-primary">Simpan</button></form></td></tr>@empty<tr><td colspan="4" class="text-center py-4">Belum ada peserta.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
