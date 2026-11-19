@extends('layouts.app')

@section('title', 'Riwayat Scan RFID - Smart RW')

@section('content')
<div class="content-container">
    {{-- Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fas fa-id-card-clip"></i>
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0" style="color: var(--ink);">Riwayat Scan RFID</h1>
                    @if($isRtScoped)
                        <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-2.5 py-1 fw-semibold small">
                            Wilayah RT {{ $managedRtKode ?? '-' }}
                        </span>
                    @else
                        <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1 fw-semibold small">
                            Semua Wilayah RW
                        </span>
                    @endif
                </div>
                <p class="page-subtitle mb-0" style="color: var(--ink-soft);">
                    Pemantauan real-time pemindaian kartu E-KTP / RFID dari perangkat NodeMCU ESP-32S
                </p>
            </div>
        </div>
        <div class="page-header-actions d-flex flex-wrap align-items-center gap-2">
            <span id="liveStatusBadge" class="badge bg-success bg-opacity-15 text-success px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center gap-2">
                <span class="rfid-live-dot"></span>
                <span>Live &bull; Auto-refresh 3 detik</span>
            </span>
            <button type="button" id="btnToggleAutoRefresh" class="btn btn-outline-secondary btn-sm fw-semibold" title="Jeda atau lanjutkan pembaruan otomatis">
                <i class="fas fa-pause me-1"></i> <span>Jeda</span>
            </button>
            <button type="button" id="btnManualRefresh" class="btn btn-success btn-sm fw-semibold">
                <i class="fas fa-rotate me-1"></i> Muat Ulang
            </button>
        </div>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold mb-1" style="color: var(--ink-soft);">Total Scan</div>
                    <div class="h3 fw-bold mb-0" id="statTotal" style="color: var(--ink);">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold mb-1 text-success">Akses Diizinkan</div>
                    <div class="h3 fw-bold mb-0 text-success" id="statGranted">{{ $stats['granted'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold mb-1 text-danger">Akses Ditolak</div>
                    <div class="h3 fw-bold mb-0 text-danger" id="statDenied">{{ $stats['denied'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold mb-1 text-warning">Tidak Terdaftar</div>
                    <div class="h3 fw-bold mb-0 text-warning" id="statUnregistered">{{ $stats['unregistered'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Wilayah (khusus Super Admin & RW) --}}
    @if(! $isRtScoped)
        <div class="card border-0 rounded-4 shadow-sm mb-4 p-3">
            <form method="GET" action="{{ route('rfid.history') }}" id="rfidFilterForm" class="row g-2 align-items-center">
                <div class="col-12 col-md-5 col-lg-4">
                    <label for="filterRtSelect" class="form-label small fw-semibold mb-1" style="color: var(--ink-soft);">Filter Wilayah RT</label>
                    <select name="rt_id" id="filterRtSelect" class="form-select">
                        <option value="">Semua Wilayah (Termasuk Tidak Terdaftar)</option>
                        <option value="unregistered" {{ request('rt_id') === 'unregistered' ? 'selected' : '' }}>Hanya Kartu Tidak Terdaftar</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" {{ (string) request('rt_id') === (string) $rt->id ? 'selected' : '' }}>
                                RT {{ $rt->kode_rt }}{{ $rt->nama_rt ? ' - ' . $rt->nama_rt : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto align-self-end d-flex gap-2">
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="fas fa-filter me-1"></i> Terapkan
                    </button>
                    @if(request()->filled('rt_id'))
                        <a href="{{ route('rfid.history') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    @endif
                </div>
                <div class="col-12 col-md text-md-end align-self-end">
                    <small style="color: var(--ink-mute);">
                        Terakhir diperbarui: <strong id="lastUpdatedTime">{{ now()->format('H:i:s') }}</strong>
                    </small>
                </div>
            </form>
        </div>
    @else
        <div class="d-flex justify-content-end mb-2">
            <small style="color: var(--ink-mute);">
                Terakhir diperbarui: <strong id="lastUpdatedTime">{{ now()->format('H:i:s') }}</strong>
            </small>
        </div>
    @endif

    {{-- Tabel Riwayat Scan RFID --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border) !important;">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--ink);">
                    <i class="fas fa-clock-rotate-left text-success me-2"></i> Log Pemindaian Terbaru
                </h5>
                <small style="color: var(--ink-soft);">Diurutkan dari scan terbaru (maksimal 50 entri terakhir ditampilkan langsung)</small>
            </div>
            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1.5 rounded-pill fw-semibold" id="rowCountBadge">
                {{ $scans->count() }} Entri Ditampilkan
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rfidHistoryTable">
                <thead>
                    <tr class="small" style="background: var(--surface-2); color: var(--ink-soft);">
                        <th class="ps-4">Waktu</th>
                        <th>UID</th>
                        <th>Profil & Jenis Kartu</th>
                        <th>Nama Warga</th>
                        <th>Perangkat</th>
                        <th>Fungsi</th>
                        <th class="pe-4">Status Akses</th>
                    </tr>
                </thead>
                <tbody id="rfidHistoryBody">
                    @forelse($scans as $scan)
                        @php
                            $namaWarga = $scan->nama_warga ?: (optional($scan->userProfile)->nama_lengkap ?: 'Tidak terdaftar');
                            $isRegistered = (bool) ($scan->user_profile_id || ($scan->nama_warga && $scan->nama_warga !== 'Tidak terdaftar'));
                            $rtKode = optional($scan->rt)->kode_rt ?? optional(optional($scan->userProfile)->wilayahRt)->kode_rt;
                            $buzzer = strtoupper((string) ($scan->buzzer_signal ?: ($scan->access_granted ? 'GRANTED' : 'DENIED')));
                            $perangkat = $scan->device_id ?: (optional($scan->deviceToken)->name ?: 'ESP32-RC522');
                        @endphp
                        <tr>
                            <td class="ps-4 small text-nowrap">
                                <div class="fw-semibold" style="color: var(--ink);">
                                    {{ $scan->scanned_at ? $scan->scanned_at->format('d/m/Y H:i:s') : '-' }}
                                </div>
                                <small style="color: var(--ink-mute);">
                                    {{ $scan->scanned_at ? $scan->scanned_at->diffForHumans() : '' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge font-monospace border px-2.5 py-1.5" style="background: var(--surface-2); color: var(--ink);">
                                    {{ $scan->formatted_uid ?: $scan->uid }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold small" style="color: var(--ink);">
                                    <i class="fas fa-credit-card text-muted me-1"></i> {{ $scan->card_type ?: 'Smart Card / RFID' }}
                                </div>
                                <div class="d-flex flex-wrap gap-1 mt-1 font-monospace" style="font-size: 0.75rem;">
                                    @if($scan->sak)
                                        <span class="badge border bg-light text-secondary">SAK: 0x{{ $scan->sak }}</span>
                                    @endif
                                    @if($scan->atqa)
                                        <span class="badge border bg-light text-secondary">ATQA: 0x{{ $scan->atqa }}</span>
                                    @endif
                                    @if($scan->uid_size)
                                        <span class="badge border bg-light text-secondary">{{ $scan->uid_size }}B</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($isRegistered)
                                    <div class="fw-semibold" style="color: var(--ink);">{{ $namaWarga }}</div>
                                    @if($rtKode)
                                        <small class="badge border" style="background: var(--surface-2); color: var(--ink-soft);">RT {{ $rtKode }}</small>
                                    @endif
                                @else
                                    <span class="fw-semibold text-muted fst-italic">Tidak terdaftar</span>
                                @endif
                            </td>
                            <td class="small">
                                <div class="fw-semibold" style="color: var(--ink);">
                                    <i class="fas fa-microchip text-primary me-1"></i> {{ $perangkat }}
                                </div>
                                @if($scan->deviceToken && $scan->deviceToken->name)
                                    <small style="color: var(--ink-mute);">{{ $scan->deviceToken->name }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $fColor = match($scan->fungsi) {
                                        'gerbang' => 'bg-primary text-white',
                                        'bank_sampah' => 'bg-success text-white',
                                        'presensi' => 'bg-warning text-dark',
                                        'perekaman' => 'bg-purple text-white',
                                        default => 'bg-secondary text-white',
                                    };
                                    $fLabel = match($scan->fungsi) {
                                        'gerbang' => 'Gerbang',
                                        'bank_sampah' => 'Bank Sampah',
                                        'presensi' => 'Presensi',
                                        'perekaman' => 'Perekaman',
                                        default => 'Belum Diatur',
                                    };
                                @endphp
                                <span class="badge {{ $fColor }} px-2 py-1 rounded-pill small">
                                    {{ $fLabel }}
                                </span>
                            </td>
                            <td class="pe-4">
                                @if($scan->access_granted)
                                    <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1.5 fw-bold">
                                        <i class="fas fa-circle-check me-1"></i> Diizinkan
                                    </span>
                                @elseif($buzzer === 'PENDING')
                                    <span class="badge bg-warning bg-opacity-20 text-dark rounded-pill px-2.5 py-1.5 fw-bold">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-15 text-danger rounded-pill px-2.5 py-1.5 fw-bold">
                                        <i class="fas fa-circle-xmark me-1"></i> Ditolak
                                    </span>
                                @endif
                                @if($scan->access_message)
                                    <div class="small mt-1" style="color: var(--ink-mute); max-width: 320px;">
                                        {{ $scan->access_message }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5" style="color: var(--ink-mute);">
                                <i class="fas fa-id-card-clip fa-3x mb-3 d-block opacity-40"></i>
                                Belum ada riwayat scan RFID yang tercatat. Tempelkan kartu pada perangkat ESP32 RC522 untuk mulai memantau.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($scans instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $scans->hasPages())
            <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-center">
                {{ $scans->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.rfid-live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #10b981;
    display: inline-block;
    animation: rfidPulse 1.6s ease-in-out infinite;
}
.rfid-live-dot.paused {
    background-color: #9ca3af;
    animation: none;
}
@keyframes rfidPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}
@media (prefers-reduced-motion: reduce) {
    .rfid-live-dot {
        animation: none;
    }
}
</style>

<script>
(function () {
    const DATA_URL = @json(route('rfid.history.data'));
    const POLL_INTERVAL_MS = 3000;

    let isAutoRefreshEnabled = true;
    let isFetching = false;
    let pollTimer = null;

    const tbody = document.getElementById('rfidHistoryBody');
    const lastUpdatedEl = document.getElementById('lastUpdatedTime');
    const rowCountBadge = document.getElementById('rowCountBadge');
    const statTotal = document.getElementById('statTotal');
    const statGranted = document.getElementById('statGranted');
    const statDenied = document.getElementById('statDenied');
    const statUnregistered = document.getElementById('statUnregistered');
    const filterRtSelect = document.getElementById('filterRtSelect');
    const btnToggle = document.getElementById('btnToggleAutoRefresh');
    const btnManual = document.getElementById('btnManualRefresh');
    const liveBadge = document.getElementById('liveStatusBadge');

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderStatusBadge(item) {
        if (item.access_granted) {
            return '<span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1.5 fw-bold">' +
                '<i class="fas fa-circle-check me-1"></i> Diizinkan</span>';
        }
        if (item.buzzer_signal === 'PENDING') {
            return '<span class="badge bg-warning bg-opacity-20 text-dark rounded-pill px-2.5 py-1.5 fw-bold">' +
                '<i class="fas fa-clock me-1"></i> Pending</span>';
        }
        return '<span class="badge bg-danger bg-opacity-15 text-danger rounded-pill px-2.5 py-1.5 fw-bold">' +
            '<i class="fas fa-circle-xmark me-1"></i> Ditolak</span>';
    }

    function renderFungsiBadge(item) {
        const f = item.fungsi || 'belum_dikonfigurasi';
        let colorClass = 'bg-secondary text-white';
        let label = item.fungsi_label || 'Belum Diatur';
        if (f === 'gerbang') { colorClass = 'bg-primary text-white'; label = 'Gerbang'; }
        else if (f === 'bank_sampah') { colorClass = 'bg-success text-white'; label = 'Bank Sampah'; }
        else if (f === 'presensi') { colorClass = 'bg-warning text-dark'; label = 'Presensi'; }
        else if (f === 'perekaman') { colorClass = 'bg-purple text-white'; label = 'Perekaman'; }
        return '<span class="badge ' + colorClass + ' px-2 py-1 rounded-pill small">' + escapeHtml(label) + '</span>';
    }

    function renderRows(items) {
        if (!items || items.length === 0) {
            tbody.innerHTML =
                '<tr>' +
                '<td colspan="7" class="text-center py-5" style="color: var(--ink-mute);">' +
                '<i class="fas fa-id-card-clip fa-3x mb-3 d-block opacity-40"></i>' +
                'Belum ada riwayat scan RFID yang tercatat. Tempelkan kartu pada perangkat ESP32 RC522 untuk mulai memantau.' +
                '</td>' +
                '</tr>';
            return;
        }

        tbody.innerHTML = items.map(function (item) {
            const cardTypeHtml = escapeHtml(item.card_type || 'Smart Card / RFID');
            const sakBadge = item.sak ? '<span class="badge border bg-light text-secondary">SAK: 0x' + escapeHtml(item.sak) + '</span>' : '';
            const atqaBadge = item.atqa ? '<span class="badge border bg-light text-secondary">ATQA: 0x' + escapeHtml(item.atqa) + '</span>' : '';
            const sizeBadge = item.uid_size ? '<span class="badge border bg-light text-secondary">' + escapeHtml(item.uid_size) + 'B</span>' : '';

            const cardSpecHtml = '<div class="fw-semibold small" style="color: var(--ink);"><i class="fas fa-credit-card text-muted me-1"></i> ' + cardTypeHtml + '</div>' +
                '<div class="d-flex flex-wrap gap-1 mt-1 font-monospace" style="font-size: 0.75rem;">' + sakBadge + atqaBadge + sizeBadge + '</div>';

            const wargaHtml = item.is_registered
                ? '<div class="fw-semibold" style="color: var(--ink);">' + escapeHtml(item.nama_warga) + '</div>' +
                  (item.rt_kode ? '<small class="badge border" style="background: var(--surface-2); color: var(--ink-soft);">RT ' + escapeHtml(item.rt_kode) + '</small>' : '')
                : '<span class="fw-semibold text-muted fst-italic">Tidak terdaftar</span>';

            const tokenSub = item.perangkat_token
                ? '<small style="color: var(--ink-mute);">' + escapeHtml(item.perangkat_token) + '</small>'
                : '';

            const msgSub = item.access_message
                ? '<div class="small mt-1" style="color: var(--ink-mute); max-width: 320px;">' + escapeHtml(item.access_message) + '</div>'
                : '';

            return '<tr>' +
                '<td class="ps-4 small text-nowrap">' +
                    '<div class="fw-semibold" style="color: var(--ink);">' + escapeHtml(item.waktu) + '</div>' +
                    '<small style="color: var(--ink-mute);">' + escapeHtml(item.waktu_relatif) + '</small>' +
                '</td>' +
                '<td>' +
                    '<span class="badge font-monospace border px-2.5 py-1.5" style="background: var(--surface-2); color: var(--ink);">' +
                        escapeHtml(item.uid) +
                    '</span>' +
                '</td>' +
                '<td>' + cardSpecHtml + '</td>' +
                '<td>' + wargaHtml + '</td>' +
                '<td class="small">' +
                    '<div class="fw-semibold" style="color: var(--ink);">' +
                        '<i class="fas fa-microchip text-primary me-1"></i> ' + escapeHtml(item.perangkat) +
                    '</div>' +
                    tokenSub +
                '</td>' +
                '<td>' + renderFungsiBadge(item) + '</td>' +
                '<td class="pe-4">' +
                    renderStatusBadge(item) +
                    msgSub +
                '</td>' +
            '</tr>';
        }).join('');
    }

    async function fetchHistory() {
        if (isFetching) return;
        isFetching = true;

        try {
            const url = new URL(DATA_URL, window.location.origin);
            if (filterRtSelect && filterRtSelect.value) {
                url.searchParams.set('rt_id', filterRtSelect.value);
            }

            const resp = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!resp.ok) return;
            const payload = await resp.json();

            if (payload && payload.status === 'success') {
                renderRows(payload.data || []);
                if (payload.stats) {
                    if (statTotal) statTotal.textContent = payload.stats.total ?? 0;
                    if (statGranted) statGranted.textContent = payload.stats.granted ?? 0;
                    if (statDenied) statDenied.textContent = payload.stats.denied ?? 0;
                    if (statUnregistered) statUnregistered.textContent = payload.stats.unregistered ?? 0;
                }
                if (rowCountBadge) {
                    rowCountBadge.textContent = (payload.data ? payload.data.length : 0) + ' Entri Ditampilkan';
                }
                if (lastUpdatedEl && payload.updated_at) {
                    lastUpdatedEl.textContent = payload.updated_at;
                }
            }
        } catch (err) {
            // Abaikan error jaringan sementara; polling berikutnya akan mencoba lagi
        } finally {
            isFetching = false;
        }
    }

    function startPolling() {
        stopPolling();
        pollTimer = setInterval(function () {
            if (isAutoRefreshEnabled && !document.hidden) {
                fetchHistory();
            }
        }, POLL_INTERVAL_MS);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    if (btnToggle) {
        btnToggle.addEventListener('click', function () {
            isAutoRefreshEnabled = !isAutoRefreshEnabled;
            const icon = btnToggle.querySelector('i');
            const label = btnToggle.querySelector('span');
            const dot = liveBadge ? liveBadge.querySelector('.rfid-live-dot') : null;
            const badgeText = liveBadge ? liveBadge.querySelector('span:last-child') : null;

            if (isAutoRefreshEnabled) {
                if (icon) icon.className = 'fas fa-pause me-1';
                if (label) label.textContent = 'Jeda';
                if (dot) dot.classList.remove('paused');
                if (badgeText) badgeText.textContent = 'Live • Auto-refresh 3 detik';
                fetchHistory();
            } else {
                if (icon) icon.className = 'fas fa-play me-1';
                if (label) label.textContent = 'Lanjut';
                if (dot) dot.classList.add('paused');
                if (badgeText) badgeText.textContent = 'Dijeda';
            }
        });
    }

    if (btnManual) {
        btnManual.addEventListener('click', function () {
            fetchHistory();
        });
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && isAutoRefreshEnabled) {
            fetchHistory();
        }
    });

    startPolling();
})();
</script>
@endsection
