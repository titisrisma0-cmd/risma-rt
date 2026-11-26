@extends('layouts.app')

@section('title', 'Manajemen Perangkat RFID - Super Admin Smart RW')

@section('content')
<div class="content-container">
    {{-- Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon" style="background: rgba(14, 165, 233, 0.15); color: #0284c7; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fas fa-microchip"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0" style="color: var(--ink);">Perangkat RFID</h1>
                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1 small">Super Admin Only</span>
                </div>
                <p class="page-subtitle mb-0" style="color: var(--ink-soft);">Konfigurasi pemetaan fungsi hardware NodeMCU ESP-32S: Gerbang, Bank Sampah, Presensi Ronda, dan Perekaman E-KTP</p>
            </div>
        </div>
        <div class="page-header-actions d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPerangkat">
                <i class="fas fa-plus-circle me-1.5"></i> Daftarkan Perangkat
            </button>
            <a href="{{ route('rfid.history') }}" class="btn btn-outline-success">
                <i class="fas fa-id-card-clip me-1"></i> Riwayat Scan
            </a>
            <a href="{{ route('superadmin.api-settings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-key me-1"></i> Token API
            </a>
            <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(($stats['unconfigured'] ?? 0) > 0)
        <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3" role="alert">
            <i class="fas fa-triangle-exclamation fs-4 text-warning flex-shrink-0"></i>
            <div class="small">
                <strong>Perhatian:</strong> Terdapat <strong>{{ $stats['unconfigured'] }}</strong> perangkat RFID baru yang berstatus <em>Belum Dikonfigurasi</em> (Nonaktif). Klik tombol <span class="badge bg-primary">Set Gerbang</span> pada baris perangkat di bawah untuk mengaktifkannya sebagai reader palang pos depan, atau klik ikon Edit untuk memilih fungsi lainnya.
            </div>
        </div>
    @endif

    {{-- Ringkasan Statistik Kartu Fungsi --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Total Unit</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-light text-secondary">
                        <i class="fas fa-network-wired"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Gerbang Portal</div>
                        <div class="fs-4 fw-bold text-primary mt-1">{{ $stats['gerbang'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-torii-gate"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Bank Sampah</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ $stats['bank_sampah'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-success bg-opacity-10 text-success">
                        <i class="fas fa-recycle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Presensi Ronda</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ $stats['presensi'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clipboard-user"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Perekaman E-KTP</div>
                        <div class="fs-4 fw-bold text-info mt-1">{{ $stats['perekaman'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-info bg-opacity-10 text-info">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Belum Diatur</div>
                        <div class="fs-4 fw-bold {{ $stats['unconfigured'] > 0 ? 'text-danger' : 'text-muted' }} mt-1">{{ $stats['unconfigured'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle {{ $stats['unconfigured'] > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-muted' }}">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('superadmin.rfid-devices.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Cari Device ID, nama lokasi, atau keterangan..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="fungsi" class="form-select bg-light border-0">
                        <option value="">Semua Fungsi</option>
                        <option value="gerbang" {{ request('fungsi') === 'gerbang' ? 'selected' : '' }}>Gerbang / Portal</option>
                        <option value="bank_sampah" {{ request('fungsi') === 'bank_sampah' ? 'selected' : '' }}>Bank Sampah</option>
                        <option value="presensi" {{ request('fungsi') === 'presensi' ? 'selected' : '' }}>Presensi Kehadiran</option>
                        <option value="perekaman" {{ request('fungsi') === 'perekaman' ? 'selected' : '' }}>Perekaman E-KTP</option>
                        <option value="belum_dikonfigurasi" {{ request('fungsi') === 'belum_dikonfigurasi' ? 'selected' : '' }}>Belum Dikonfigurasi</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select bg-light border-0">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i> Filter</button>
                    @if(request()->hasAny(['search', 'fungsi', 'status']))
                        <a href="{{ route('superadmin.rfid-devices.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fas fa-redo"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Perangkat --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-list me-2 text-primary"></i>Daftar Perangkat RFID Terdaftar ({{ $devices->total() }})
            </h5>
            <small class="text-muted">ESP32 yang belum terdaftar otomatis dicatat sebagai <em>belum dikonfigurasi</em></small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase fw-semibold">
                    <tr>
                        <th class="ps-4">Device ID</th>
                        <th>Lokasi & Keterangan</th>
                        <th>Wilayah</th>
                        <th>Fungsi Kerja</th>
                        <th>Status</th>
                        <th>Terakhir Scan</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-dark text-white font-monospace px-2.5 py-1.5 fs-6 shadow-sm">
                                    {{ $device->device_id }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $device->nama_lokasi }}</div>
                                @if($device->keterangan)
                                    <div class="small text-muted text-truncate" style="max-width: 280px;">{{ $device->keterangan }}</div>
                                @endif
                            </td>
                            <td>
                                @if($device->rt)
                                    <span class="badge bg-light text-dark border">RT {{ $device->rt->kode_rt }}</span>
                                @else
                                    <span class="badge bg-light text-secondary border">Seluruh RW</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($device->fungsi) {
                                        'gerbang' => 'bg-primary text-white',
                                        'bank_sampah' => 'bg-success text-white',
                                        'presensi' => 'bg-warning text-dark',
                                        'perekaman' => 'bg-purple text-white',
                                        default => 'bg-danger text-white',
                                    };
                                    $icon = match($device->fungsi) {
                                        'gerbang' => 'fa-torii-gate',
                                        'bank_sampah' => 'fa-recycle',
                                        'presensi' => 'fa-clipboard-user',
                                        'perekaman' => 'fa-id-card',
                                        default => 'fa-triangle-exclamation',
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }} px-2.5 py-1.5 rounded-pill shadow-xs">
                                    <i class="fas {{ $icon }} me-1"></i> {{ $device->fungsi_label }}
                                </span>
                            </td>
                            <td>
                                @if($device->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-ban me-1"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="small text-secondary">
                                @if($device->last_seen_at)
                                    <div><i class="far fa-clock me-1 text-primary"></i>{{ $device->last_seen_at->diffForHumans() }}</div>
                                    <small class="text-muted">{{ $device->last_seen_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="text-muted">Belum pernah</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    {{-- Quick Set Gerbang & Aktifkan --}}
                                    @if($device->fungsi !== 'gerbang' || ! $device->is_active)
                                        <form method="POST" action="{{ route('superadmin.rfid-devices.set-gerbang', $device) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-primary" title="Atur sebagai Palang Pos / Gerbang dan Aktifkan">
                                                <i class="fas fa-torii-gate me-1"></i> Set Gerbang
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Toggle Status --}}
                                    <form method="POST" action="{{ route('superadmin.rfid-devices.toggle', $device) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $device->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $device->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas {{ $device->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Edit Modal Button --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $device->id }}" title="Edit Konfigurasi">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>

                                    {{-- Delete Button --}}
                                    <form method="POST" action="{{ route('superadmin.rfid-devices.destroy', $device) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat {{ $device->device_id }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Perangkat">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                {{-- Modal Edit Perangkat --}}
                                <div class="modal fade text-start" id="modalEdit{{ $device->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form method="POST" action="{{ route('superadmin.rfid-devices.update', $device) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-light border-0 py-3 px-4">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        <i class="fas fa-edit text-primary me-2"></i>Edit Konfigurasi Perangkat
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Device ID (Hardware ID)</label>
                                                        <input type="text" name="device_id" class="form-control font-monospace fw-bold" value="{{ old('device_id', $device->device_id) }}" required>
                                                        <div class="form-text small">Harus sama dengan `cfg_device_id` di ESP32.</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Nama Lokasi Perangkat</label>
                                                        <input type="text" name="nama_lokasi" class="form-control" value="{{ old('nama_lokasi', $device->nama_lokasi) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Fungsi Kerja Operasional</label>
                                                        <select name="fungsi" class="form-select" required>
                                                            <option value="gerbang" {{ $device->fungsi === 'gerbang' ? 'selected' : '' }}>Palang Pos / Gerbang (Buka Palang jika Warga Aktif)</option>
                                                            <option value="bank_sampah" {{ $device->fungsi === 'bank_sampah' ? 'selected' : '' }}>Bank Sampah (Deteksi Warga & Setoran Sampah)</option>
                                                            <option value="presensi" {{ $device->fungsi === 'presensi' ? 'selected' : '' }}>Presensi Kehadiran / Ronda Malam</option>
                                                            <option value="perekaman" {{ $device->fungsi === 'perekaman' ? 'selected' : '' }}>Perekaman Kartu RFID Baru</option>
                                                            <option value="belum_dikonfigurasi" {{ $device->fungsi === 'belum_dikonfigurasi' ? 'selected' : '' }}>Belum Dikonfigurasi (Tidak Jalankan Aksi)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Cakupan Wilayah RT (Opsional)</label>
                                                        <select name="rt_id" class="form-select">
                                                            <option value="">Semua RT / Sentral RW</option>
                                                            @foreach($rts as $r)
                                                                <option value="{{ $r->id }}" {{ (string)$device->rt_id === (string)$r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Keterangan / Catatan Tambahan</label>
                                                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $device->keterangan) }}</textarea>
                                                    </div>
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="switchActive{{ $device->id }}" {{ $device->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-medium" for="switchActive{{ $device->id }}">Status Perangkat Aktif</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-0 py-3 px-4">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-microchip fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                Belum ada perangkat RFID yang terdaftar. Klik tombol <strong>Daftarkan Perangkat</strong> untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($devices->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $devices->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Tambah Perangkat --}}
<div class="modal fade" id="modalTambahPerangkat" tabindex="-1" aria-labelledby="modalTambahPerangkatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" action="{{ route('superadmin.rfid-devices.store') }}">
                @csrf
                <div class="modal-header bg-light border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahPerangkatLabel">
                        <i class="fas fa-plus-circle text-primary me-2"></i>Daftarkan Perangkat RFID Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Device ID (Hardware ID) <span class="text-danger">*</span></label>
                        <input type="text" name="device_id" class="form-control font-monospace fw-bold" placeholder="Contoh: ESP32-GATE-RW01" required>
                        <div class="form-text small">Harus sesuai dengan nama ID yang dipancarkan oleh NodeMCU ESP-32S.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Lokasi Perangkat <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lokasi" class="form-control" placeholder="Contoh: Pintu Gerbang Utama RW" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Fungsi Kerja Operasional <span class="text-danger">*</span></label>
                        <select name="fungsi" class="form-select" required>
                            <option value="gerbang">Portal Gerbang (Buka Gerbang jika Warga Aktif)</option>
                            <option value="bank_sampah">Bank Sampah (Deteksi Warga & Setoran Sampah)</option>
                            <option value="presensi">Presensi Kehadiran / Ronda Malam</option>
                            <option value="perekaman">Perekaman E-KTP / Kartu Baru</option>
                            <option value="belum_dikonfigurasi">Belum Dikonfigurasi (Tidak Jalankan Aksi)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Cakupan Wilayah RT (Opsional)</label>
                        <select name="rt_id" class="form-select">
                            <option value="">Semua RT / Sentral RW</option>
                            @foreach($rts as $r)
                                <option value="{{ $r->id }}">RT {{ $r->kode_rt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Keterangan / Catatan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan lokasi fisik, nomor IP, atau penanggung jawab..."></textarea>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="switchNewActive" checked>
                        <label class="form-check-label small fw-medium" for="switchNewActive">Aktifkan Perangkat Segera</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Simpan Perangkat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
