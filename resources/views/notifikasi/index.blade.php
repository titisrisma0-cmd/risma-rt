@extends('layouts.app')

@section('title', 'Notifikasi Saya')

@section('content')
<div class="content-container" style="max-width: 900px;">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notifikasi</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-bell text-success me-2"></i>Pusat Notifikasi</h1>
            <p class="page-subtitle">Pemberitahuan aktivitas, update status permohonan, jadwal ronda, dan pengumuman.</p>
        </div>
        <div class="d-flex gap-2">
            @if($notifikasis->where('dibaca', false)->count())
                <form method="POST" action="{{ route('notifikasi.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-saas btn-sm">
                        <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
                    </button>
                </form>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline-saas btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
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
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-inbox text-success me-2"></i>Daftar Pemberitahuan</h6>
            <span class="badge badge-soft-secondary">{{ $notifikasis->count() }} Notifikasi</span>
        </div>
        <div class="p-3">
            @forelse($notifikasis as $n)
                <div class="p-3 mb-2 rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-3 {{ $n->dibaca ? 'bg-white' : 'bg-success bg-opacity-10 border-success-subtle' }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon-wrapper {{ $n->dibaca ? 'bg-light text-secondary' : 'bg-success text-white' }} p-2 rounded-circle" style="width: 40px; height: 40px; display: grid; place-items: center;">
                            <i class="fas {{ $n->dibaca ? 'fa-bell' : 'fa-bell fa-shake' }} fs-6"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="text-dark">{{ $n->judul }}</strong>
                                @if(!$n->dibaca)
                                    <span class="badge badge-soft-success small">Baru</span>
                                @endif
                            </div>
                            <p class="small text-secondary mb-1" style="line-height: 1.4;">{{ $n->pesan }}</p>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ $n->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <div>
                        @if(!$n->dibaca)
                            <form method="POST" action="{{ route('notifikasi.read', $n->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-saas-primary btn-sm">
                                    <i class="fas fa-check me-1"></i> Tandai Dibaca
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-secondary">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
                        <h6 class="empty-state-title">Belum Ada Notifikasi</h6>
                        <p class="empty-state-desc">Pemberitahuan baru akan muncul di sini saat ada pembaruan layanan.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
