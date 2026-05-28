@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Notifikasi Saya</h3>
        @if($notifikasis->where('dibaca', false)->count())
            <form method="POST" action="{{ route('notifikasi.read-all') }}">
                @csrf
                <button class="btn btn-sm btn-outline-primary">Tandai semua dibaca</button>
            </form>
        @endif
    </div>
    <div class="list-group">
        @forelse($notifikasis as $n)
            <div class="list-group-item d-flex justify-content-between align-items-start {{ $n->dibaca ? '' : 'bg-warning bg-opacity-10' }}">
                <div>
                    <strong>{{ $n->judul }}</strong>
                    <div class="small text-secondary">{{ $n->pesan }}</div>
                    <div class="small text-muted">{{ $n->created_at->diffForHumans() }}</div>
                </div>
                <div>
                    @if(!$n->dibaca)
                    <form method="POST" action="{{ route('notifikasi.read', $n->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-primary">Tandai Dibaca</button>
                    </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-secondary p-4">Belum ada notifikasi</div>
        @endforelse
    </div>
</div>
@endsection
