@extends('layouts.app')

@section('title', 'Pengumuman Resmi RW')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 text-white mb-1"><i class="fas fa-bullhorn text-amber-400 me-2"></i>Pengumuman Resmi RW</h1>
            <p class="text-white-50 mb-0">Pusat informasi, edaran resmi, dan instruksi koordinasi pengurus RW</p>
        </div>

        @if($user->isAdminRw() || $user->hasRole('bendahara'))
            <div>
                <a href="{{ route('pengumuman.create') }}" class="btn btn-emerald text-white px-3 py-2" style="background: linear-gradient(135deg, #059669, #10b981); border: none; border-radius: 8px;">
                    <i class="fas fa-plus me-1"></i> Buat Pengumuman Baru
                </a>
            </div>
        @endif
    </div>

    @if($user->isAdminRw() || $user->hasRole('bendahara'))
        {{-- Filter Card untuk Pengurus RW --}}
        <div class="card p-3 mb-4 border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
            <form method="GET" action="{{ route('pengumuman.index') }}" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label text-white-50 small mb-1">Kategori</label>
                    <select name="kategori" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="Umum" {{ request('kategori') == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Keamanan" {{ request('kategori') == 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                        <option value="Kerja Bakti" {{ request('kategori') == 'Kerja Bakti' ? 'selected' : '' }}>Kerja Bakti</option>
                        <option value="Iuran" {{ request('kategori') == 'Iuran' ? 'selected' : '' }}>Iuran & Keuangan</option>
                        <option value="Darurat" {{ request('kategori') == 'Darurat' ? 'selected' : '' }}>Darurat / Penting</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white-50 small mb-1">Status Publikasi</label>
                    <select name="status" class="form-select text-white" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15);" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="arsip" {{ request('status') == 'arsip' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    @if(request()->hasAny(['kategori', 'status']))
                        <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-light w-100 py-2">Reset Filter</a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    {{-- Grid Pengumuman --}}
    <div class="row g-4">
        @forelse($pengumumans as $p)
            <div class="col-md-6 col-xl-4">
                <div class="card p-3 border-0 h-100 d-flex flex-column" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge {{ $p->kategori === 'Darurat' ? 'bg-danger' : ($p->kategori === 'Keamanan' ? 'bg-warning text-dark' : 'bg-primary') }}">
                            {{ $p->kategori }}
                        </span>
                        <span class="badge bg-slate-700 text-white-50 font-monospace small">
                            {{ optional($p->tanggal_publikasi)->format('d M Y') }}
                        </span>
                    </div>

                    <h5 class="text-white fw-bold mb-2">{{ $p->judul }}</h5>
                    <p class="text-white-50 small flex-grow-1 mb-3">
                        {{ Str::limit($p->isi, 120) }}
                    </p>

                    <div class="p-2 rounded mb-3" style="background: rgba(15,23,42,0.6);">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-white-50"><i class="fas fa-bullseye me-1 text-emerald-400"></i>{{ $p->target_label }}</small>
                            <span class="badge {{ $p->status === 'published' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600 text-white' }} small">
                                {{ ucfirst($p->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="border-top border-white-10 pt-2 d-flex justify-content-between align-items-center">
                        <a href="{{ route('pengumuman.show', $p) }}" class="btn btn-sm btn-outline-info text-white">
                            <i class="fas fa-eye me-1"></i> Baca Lengkap
                        </a>

                        @if($user->isAdminRw() || $user->hasRole('bendahara'))
                            <div class="d-flex gap-1">
                                <a href="{{ route('pengumuman.edit', $p) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('pengumuman.destroy', $p) }}" onsubmit="return confirm('Hapus pengumuman ini secara permanen?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center border-0" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px;">
                    <i class="fas fa-bullhorn fa-3x text-white-50 mb-3"></i>
                    <h5 class="text-white">Belum Ada Pengumuman</h5>
                    <p class="text-white-50 mb-0">Belum ada pengumuman resmi yang diterbitkan saat ini.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($pengumumans->hasPages())
        <div class="p-3 mt-4 border-top border-white-10">
            {{ $pengumumans->links() }}
        </div>
    @endif
</div>
@endsection
