@extends('layouts.app')

@section('title', 'Log Audit Sistem - Super Admin RW SMART')

@section('content')
<div class="content-container">
    {{-- Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0" style="color: var(--ink);">Log Audit Sistem</h1>
                    <span class="badge-rt-pill">Super Admin</span>
                </div>
                <p class="page-subtitle mb-0" style="color: var(--ink-soft);">Rekam jejak seluruh mutasi data, aksi beralih peran, dan aktivitas keamanan sistem</p>
            </div>
        </div>
        <div class="page-header-actions d-flex gap-2">
            <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard Super Admin
            </a>
            <button type="button" class="btn btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                <i class="fas fa-arrows-rotate me-1"></i> Ganti Role
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 p-3">
        <form method="GET" action="{{ route('superadmin.audit-logs') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text border-end-0" style="background: var(--surface); color: var(--ink-soft);"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Cari aksi, deskripsi, entitas, atau IP address...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="action" class="form-select">
                    <option value="">Semua Jenis Aksi</option>
                    <option value="switch_role" {{ request('action') === 'switch_role' ? 'selected' : '' }}>switch_role (Beralih Peran)</option>
                    <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>create (Tambah Data)</option>
                    <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>update (Ubah Data)</option>
                    <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>delete (Hapus Data)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success w-100 fw-semibold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['q', 'action']))
                    <a href="{{ route('superadmin.audit-logs') }}" class="btn btn-light" title="Reset Filter" style="background: var(--surface-2); color: var(--ink); border: 1px solid var(--border);">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="small" style="background: var(--surface-2); color: var(--ink-soft);">
                        <th class="ps-4">ID</th>
                        <th>Waktu Kejadian</th>
                        <th>Pengguna</th>
                        <th>Wilayah / RT</th>
                        <th>Aksi</th>
                        <th>Entitas Terkait</th>
                        <th>Deskripsi</th>
                        <th class="pe-4 text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 small font-monospace" style="color: var(--ink-mute);">#{{ $log->id }}</td>
                            <td class="small text-nowrap">
                                <div class="fw-semibold" style="color: var(--ink);">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</div>
                                <small style="color: var(--ink-mute);">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
                            </td>
                            <td class="small">
                                <div class="fw-semibold" style="color: var(--ink);">{{ optional($log->user)->name ?? 'System' }}</div>
                                <small style="color: var(--ink-mute);">{{ optional($log->user)->email ?? '-' }}</small>
                            </td>
                            <td class="small">
                                @if($log->rt)
                                    <span class="badge border" style="background: var(--surface-2); color: var(--ink);">RT {{ $log->rt->kode_rt }}</span>
                                @else
                                    <span class="badge border" style="background: var(--surface-2); color: var(--ink-soft);">RW / Global</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $actionClass = 'bg-secondary bg-opacity-10 text-secondary';
                                    if (str_contains($log->action, 'create') || str_contains($log->action, 'store') || str_contains($log->action, 'approve')) {
                                        $actionClass = 'bg-success bg-opacity-15 text-success';
                                    } elseif (str_contains($log->action, 'switch_role')) {
                                        $actionClass = 'bg-warning bg-opacity-20 text-dark';
                                    } elseif (str_contains($log->action, 'delete') || str_contains($log->action, 'reject')) {
                                        $actionClass = 'bg-danger bg-opacity-15 text-danger';
                                    }
                                @endphp
                                <span class="badge {{ $actionClass }} rounded-pill small">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="small font-monospace" style="color: var(--ink-mute);">
                                {{ $log->entity_type ? $log->entity_type . ($log->entity_id ? ' #' . $log->entity_id : '') : '-' }}
                            </td>
                            <td class="small" style="max-width: 300px; color: var(--ink-soft);">
                                {{ $log->deskripsi ?? '-' }}
                            </td>
                            <td class="pe-4 text-end small font-monospace" style="color: var(--ink-mute);">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color: var(--ink-mute);">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-40"></i>
                                Tidak ada log aktivitas yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-3 border-top d-flex justify-content-end" style="border-color: var(--border) !important;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
