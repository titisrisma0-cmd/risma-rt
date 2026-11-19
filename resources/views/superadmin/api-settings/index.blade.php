@extends('layouts.app')

@section('title', 'Pengaturan Token API Perangkat IoT - Super Admin Smart RW')

@section('content')
<div class="content-container">
    {{-- Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fas fa-key"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0" style="color: var(--ink);">Pengaturan Token API</h1>
                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1 small">Super Admin Only</span>
                </div>
                <p class="page-subtitle mb-0" style="color: var(--ink-soft);">Manajemen otentikasi Device Security Token hardware NodeMCU ESP-32S & RFID RC522</p>
            </div>
        </div>
        <div class="page-header-actions d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGenerateToken">
                <i class="fas fa-plus-circle me-1.5"></i> Generate Token Baru
            </button>
            <a href="{{ route('superadmin.audit-logs') }}" class="btn btn-outline-secondary">
                <i class="fas fa-shield-halved me-1"></i> Log Audit
            </a>
            <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard Super Admin
            </a>
        </div>
    </div>

    {{-- Status Alert Biasa --}}
    @if(session('status') && !session('generated_token'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('status') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ==================== ALERT TOKEN BARU DITAMPILKAN SEKALI ==================== --}}
    @if(session('generated_token'))
        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 6px solid #f59e0b !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="p-2 rounded-circle bg-warning text-dark flex-shrink-0 mt-1">
                        <i class="fas fa-triangle-exclamation fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <h5 class="fw-bold text-dark mb-0">
                                <i class="fas fa-shield-alt text-warning me-1.5"></i> Token API Baru Berhasil Dibuat: <span class="text-primary">{{ session('generated_name') }}</span>
                            </h5>
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold">
                                <i class="fas fa-eye-slash me-1"></i> HANYA DITAMPILKAN SEKALI
                            </span>
                        </div>
                        <p class="text-dark small mb-3">
                            <strong>PERINGATAN KEAMANAN:</strong> Demi menjaga keamanan tingkat tinggi, server <em>hanya menyimpan hash SHA-256</em> dari token ini. Kunci rahasia asli ini <strong>tidak akan pernah dapat ditampilkan lagi</strong> setelah Anda meninggalkan atau memuat ulang halaman ini. Segera salin sekarang!
                        </p>
                        
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-9">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-0 fw-bold font-monospace text-muted">X-Device-Token</span>
                                    <input type="text" id="plainTokenInput" class="form-control font-monospace fw-bold bg-white border-0 fs-6 text-dark" value="{{ session('generated_token') }}" readonly>
                                    <button class="btn btn-warning text-dark fw-bold px-4" type="button" id="btnCopyToken" onclick="copyGeneratedToken()">
                                        <i class="fas fa-copy me-1.5"></i> <span id="copyBtnText">Salin Token</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i> Masukkan token ini pada web portal captive hotspot ESP-32S (<code>HotSpot_ESP32S</code>) pada kolom <strong>Device Security Token</strong>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== CARD: PROFIL KARTU YANG DITERIMA UNTUK E-KTP ==================== --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border) !important;">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--ink);">
                    <i class="fas fa-id-card text-success me-2"></i> Profil Kartu yang Diterima untuk E-KTP
                </h5>
                <small style="color: var(--ink-soft);">Filter spesifikasi hardware kartu RFID (SAK, UID Size, Card Type) yang diizinkan untuk perekaman & akses warga</small>
            </div>
            <span class="badge {{ (empty($cardProfile['sak']) && empty($cardProfile['uid_size']) && empty($cardProfile['card_type'])) ? 'bg-success' : 'bg-primary' }} bg-opacity-15 {{ (empty($cardProfile['sak']) && empty($cardProfile['uid_size']) && empty($cardProfile['card_type'])) ? 'text-success' : 'text-primary' }} px-3 py-1.5 rounded-pill fw-semibold">
                {{ (empty($cardProfile['sak']) && empty($cardProfile['uid_size']) && empty($cardProfile['card_type'])) ? 'Menerima Semua Kartu (Default)' : 'Profil Filter Aktif' }}
            </span>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('superadmin.api-settings.card-profile') }}">
                @csrf
                <div class="alert alert-light border rounded-3 p-3 mb-3 small" style="color: var(--ink-soft); background: var(--surface-2);">
                    <i class="fas fa-info-circle text-primary me-1.5"></i>
                    <strong>Petunjuk:</strong> Secara default seluruh kolom di bawah dapat dikosongkan (menerima semua jenis kartu). Anda dapat mengisinya setelah menempelkan kartu KTP fisik asli dan melihat spesifikasi SAK, ATQA, UID Size, dan Card Type di <strong>Riwayat Scan RFID</strong>. Jika kartu yang ditempelkan tidak sesuai profil, server akan menolak akses dengan pesan <em>"Jenis kartu tidak sesuai"</em>.
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="inputSak" class="form-label small fw-semibold" style="color: var(--ink);">
                            SAK (Hexadecimal)
                        </label>
                        <input type="text" name="sak" id="inputSak" class="form-control font-monospace" placeholder="Contoh: 20 atau 08, 20" value="{{ old('sak', $cardProfile['sak'] ?? '') }}">
                        <div class="form-text small" style="color: var(--ink-mute);">Biarkan kosong untuk menerima semua nilai SAK. Pisahkan koma jika lebih dari satu.</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="inputUidSize" class="form-label small fw-semibold" style="color: var(--ink);">
                            UID Size (Jumlah Byte)
                        </label>
                        <select name="uid_size" id="inputUidSize" class="form-select font-monospace">
                            <option value="" {{ empty($cardProfile['uid_size']) ? 'selected' : '' }}>Semua Ukuran (Default)</option>
                            <option value="4" {{ (string)($cardProfile['uid_size'] ?? '') === '4' ? 'selected' : '' }}>4 Byte (Mifare Classic 1K / Ultralight)</option>
                            <option value="7" {{ (string)($cardProfile['uid_size'] ?? '') === '7' ? 'selected' : '' }}>7 Byte (E-KTP / Desfire / NTAG)</option>
                            <option value="10" {{ (string)($cardProfile['uid_size'] ?? '') === '10' ? 'selected' : '' }}>10 Byte</option>
                        </select>
                        <div class="form-text small" style="color: var(--ink-mute);">Pilih panjang byte UID fisik chip kartu.</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="inputCardType" class="form-label small fw-semibold" style="color: var(--ink);">
                            Card Type (PICC_GetType)
                        </label>
                        <input type="text" name="card_type" id="inputCardType" class="form-control" placeholder="Contoh: ISO 14443-4 atau MIFARE" value="{{ old('card_type', $cardProfile['card_type'] ?? '') }}">
                        <div class="form-text small" style="color: var(--ink-mute);">Tipe kartu dari pembaca RC522. Biarkan kosong untuk menerima semua tipe.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-success fw-semibold px-4">
                        <i class="fas fa-save me-1.5"></i> Simpan Profil Kartu
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== CARD 1: DAFTAR TOKEN PERANGKAT IOT ==================== --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border) !important;">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--ink);">
                    <i class="fas fa-microchip text-primary me-2"></i> Daftar Perangkat & Kunci API Terdaftar
                </h5>
                <small style="color: var(--ink-soft);">Endpoint pemindaian RFID: <code>POST /api/rfid/scan</code> (Dibatasi 60 request/menit)</small>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold">
                Total: {{ $tokens->count() }} Perangkat
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="small" style="background: var(--surface-2); color: var(--ink-soft);">
                        <th class="ps-4" style="min-width: 60px;">No</th>
                        <th style="min-width: 200px;">Nama Perangkat / Lokasi</th>
                        <th style="min-width: 130px;">Status API</th>
                        <th style="min-width: 200px;">Hash Token (SHA-256)</th>
                        <th style="min-width: 170px;">Terakhir Digunakan</th>
                        <th style="min-width: 160px;">Dibuat Oleh</th>
                        <th class="pe-4 text-end" style="min-width: 270px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tokens as $index => $item)
                        <tr>
                            <td class="ps-4 small font-monospace" style="color: var(--ink-mute);">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold" style="color: var(--ink);">{{ $item->name }}</div>
                                <small style="color: var(--ink-mute);">ID: #{{ $item->id }} &bull; Didaftarkan {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</small>
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success bg-opacity-15 text-success px-2.5 py-1.5 rounded-pill fw-bold">
                                        <i class="fas fa-circle-check me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-15 text-danger px-2.5 py-1.5 rounded-pill fw-bold">
                                        <i class="fas fa-circle-xmark me-1"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="font-monospace small text-truncate" style="max-width: 220px; color: var(--ink-soft);" title="{{ $item->token_hash }}">
                                    {{ substr($item->token_hash, 0, 16) }}...{{ substr($item->token_hash, -8) }}
                                </div>
                            </td>
                            <td class="small">
                                @if($item->last_used_at)
                                    <div class="fw-semibold text-success">
                                        <i class="fas fa-clock me-1"></i> {{ $item->last_used_at->diffForHumans() }}
                                    </div>
                                    <small style="color: var(--ink-mute);">{{ $item->last_used_at->format('d/m/Y H:i:s') }}</small>
                                @else
                                    <span class="text-muted fst-italic">Belum pernah digunakan</span>
                                @endif
                            </td>
                            <td class="small">
                                <div class="fw-semibold" style="color: var(--ink);">{{ optional($item->creator)->name ?? 'Sistem Default' }}</div>
                                <small style="color: var(--ink-mute);">{{ optional($item->creator)->email ?? '-' }}</small>
                            </td>
                            <td class="pe-4 text-end" style="min-width: 270px;">
                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                    {{-- Form Toggle Aktif/Nonaktif --}}
                                    <form method="POST" action="{{ route('superadmin.api-settings.toggle', $item->id) }}" class="d-inline m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $item->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} text-nowrap rounded-3 px-2.5 py-1.5 fw-semibold" style="font-size: 0.825rem;" title="{{ $item->is_active ? 'Nonaktifkan Penerimaan API' : 'Aktifkan Penerimaan API' }}">
                                            <i class="fas {{ $item->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                            <span>{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                        </button>
                                    </form>

                                    {{-- Tombol Regenerate Token --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary text-nowrap rounded-3 px-2.5 py-1.5 fw-semibold" style="font-size: 0.825rem;" data-bs-toggle="modal" data-bs-target="#modalRegenerate{{ $item->id }}" title="Regenerate Token Baru">
                                        <i class="fas fa-arrows-rotate me-1"></i> <span>Regenerate</span>
                                    </button>

                                    {{-- Tombol Hapus Token --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2.5 py-1.5" style="font-size: 0.825rem;" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $item->id }}" title="Hapus Perangkat">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                {{-- Modal Regenerate --}}
                                <div class="modal fade" id="modalRegenerate{{ $item->id }}" tabindex="-1" aria-labelledby="modalRegenerateLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <form method="POST" action="{{ route('superadmin.api-settings.regenerate', $item->id) }}">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold" id="modalRegenerateLabel{{ $item->id }}">
                                                        <i class="fas fa-arrows-rotate text-primary me-2"></i> Regenerate Token API
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="text-muted mb-3">
                                                        Apakah Anda yakin ingin memperbarui token untuk perangkat <strong>{{ $item->name }}</strong>?
                                                    </p>
                                                    <div class="alert alert-warning border-0 rounded-3 small mb-0">
                                                        <i class="fas fa-triangle-exclamation me-1"></i> Token lama pada perangkat fisik ESP32 ini akan <strong>segera tidak berlaku</strong>. Anda harus memperbarui token di captive portal ESP32 agar dapat terhubung kembali.
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold rounded-3">
                                                        <i class="fas fa-arrows-rotate me-1.5"></i> Ya, Regenerate Token
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Delete --}}
                                <div class="modal fade" id="modalDelete{{ $item->id }}" tabindex="-1" aria-labelledby="modalDeleteLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <form method="POST" action="{{ route('superadmin.api-settings.destroy', $item->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-danger" id="modalDeleteLabel{{ $item->id }}">
                                                        <i class="fas fa-trash me-2"></i> Hapus Perangkat API
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="text-muted mb-0">
                                                        Apakah Anda yakin ingin menghapus token perangkat <strong>{{ $item->name }}</strong>? Perangkat ini tidak akan lagi dapat mengirimkan data RFID ke server SMART RW.
                                                    </p>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger fw-semibold rounded-3">
                                                        <i class="fas fa-trash me-1.5"></i> Hapus Permanen
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5" style="color: var(--ink-mute);">
                                <i class="fas fa-key fa-3x mb-3 d-block opacity-40"></i>
                                Belum ada token perangkat API yang didaftarkan. Klik tombol "Generate Token Baru" di atas untuk membuat token pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== CARD 2: TABEL LOG PERCOBAAN DITOLAK ==================== --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border) !important;">
            <div>
                <h5 class="fw-bold mb-0 text-danger">
                    <i class="fas fa-shield-virus me-2"></i> Log Percobaan Akses API Ditolak (Audit Log)
                </h5>
                <small style="color: var(--ink-soft);">Merekam otomatis setiap request scan RFID dengan token kosong, salah, atau perangkat nonaktif</small>
            </div>
            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1.5 rounded-pill fw-semibold">
                {{ $rejectedLogs->total() }} Percobaan Ditolak
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="small" style="background: var(--surface-2); color: var(--ink-soft);">
                        <th class="ps-4">Waktu Kejadian</th>
                        <th>IP Pengirim</th>
                        <th>Alasan Penolakan</th>
                        <th>Perangkat Terkait</th>
                        <th class="pe-4 text-end">Respons HTTP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rejectedLogs as $log)
                        <tr>
                            <td class="ps-4 small text-nowrap">
                                <div class="fw-semibold" style="color: var(--ink);">
                                    {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                                </div>
                                <small style="color: var(--ink-mute);">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
                            </td>
                            <td class="small font-monospace fw-semibold text-danger">
                                <i class="fas fa-network-wired me-1"></i> {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="small" style="max-width: 420px; color: var(--ink);">
                                {{ $log->deskripsi ?? 'Percobaan scan RFID ditolak oleh middleware VerifyDeviceToken' }}
                            </td>
                            <td class="small font-monospace" style="color: var(--ink-mute);">
                                {{ $log->entity_id ? 'DeviceToken #' . $log->entity_id : 'Anonim / Tanpa Token' }}
                            </td>
                            <td class="pe-4 text-end">
                                <span class="badge bg-danger bg-opacity-15 text-danger rounded-pill fw-bold px-2.5 py-1">
                                    401 Unauthorized
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5" style="color: var(--ink-mute);">
                                <i class="fas fa-shield-check fa-3x mb-3 d-block text-success opacity-60"></i>
                                Tidak ada catatan percobaan akses ditolak. Sistem IoT aman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rejectedLogs->hasPages())
            <div class="p-3 border-top d-flex justify-content-end" style="border-color: var(--border) !important;">
                {{ $rejectedLogs->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ==================== MODAL GENERATE TOKEN BARU ==================== --}}
<div class="modal fade" id="modalGenerateToken" tabindex="-1" aria-labelledby="modalGenerateTokenLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="POST" action="{{ route('superadmin.api-settings.generate') }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalGenerateTokenLabel">
                        <i class="fas fa-key text-success me-2"></i> Generate Token API Perangkat Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">
                        Token rahasia 40 karakter akan dibuat otomatis dan di-hash SHA-256. Plain text hanya akan ditampilkan satu kali setelah disimpan.
                    </p>
                    <div class="mb-3">
                        <label for="inputDeviceName" class="form-label fw-semibold text-dark">Nama Perangkat / Lokasi Pemasangan <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="inputDeviceName" class="form-control rounded-3" placeholder="Contoh: NodeMCU ESP-32S Pos Ronda RT 01" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold rounded-3 px-4">
                        <i class="fas fa-check-circle me-1.5"></i> Buat Token Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyGeneratedToken() {
    const input = document.getElementById('plainTokenInput');
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(input.value).then(() => {
        const btnText = document.getElementById('copyBtnText');
        const origText = btnText.innerText;
        btnText.innerText = 'Tersalin!';
        setTimeout(() => {
            btnText.innerText = origText;
        }, 2500);
    }).catch(err => {
        // Fallback
        document.execCommand('copy');
        alert('Token berhasil disalin ke clipboard.');
    });
}
</script>
@endsection
