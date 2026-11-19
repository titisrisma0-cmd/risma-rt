@extends('layouts.app')

@section('title', 'Setoran Bank Sampah - Tap RFID')

@section('content')
<div class="content-container">
    {{-- Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fas fa-recycle"></i>
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0" style="color: var(--ink);">Setoran Bank Sampah</h1>
                    @if($isRt)
                        <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-2.5 py-1 fw-semibold small">
                            Wilayah RT {{ str_pad((string)($selectedRtId ?? Auth::user()->managedRtId()), 2, '0', STR_PAD_LEFT) }}
                        </span>
                    @else
                        <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1 fw-semibold small">
                            Semua Wilayah RW
                        </span>
                    @endif
                </div>
                <p class="page-subtitle mb-0" style="color: var(--ink-soft);">
                    Tap kartu RFID / E-KTP warga pada sensor untuk otomatis mendeteksi nasabah, timbang sampah, dan kredit saldo secara langsung.
                </p>
            </div>
        </div>
        <div class="page-header-actions d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('bank-sampah.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard Bank Sampah
            </a>
            <a href="{{ route('rfid.history') }}" class="btn btn-outline-success">
                <i class="fas fa-id-card-clip me-1"></i> Riwayat Scan RFID
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle fs-5 text-danger"></i>
                <div class="fw-semibold">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Rekap Harian Setoran --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Berat Hari Ini</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ number_format((float)$rekapHarian['berat'], 1, ',', '.') }} kg</div>
                    </div>
                    <div class="p-2 rounded-circle bg-success bg-opacity-10 text-success">
                        <i class="fas fa-weight-scale fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Nilai Saldo Hari Ini</div>
                        <div class="fs-4 fw-bold text-emerald mt-1">Rp {{ number_format((float)$rekapHarian['nilai'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-emerald bg-opacity-10 text-emerald">
                        <i class="fas fa-wallet fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Transaksi Hari Ini</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $rekapHarian['transaksi'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-light text-secondary">
                        <i class="fas fa-receipt fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100" style="background: var(--surface-1);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-medium">Warga Menyetor</div>
                        <div class="fs-4 fw-bold text-primary mt-1">{{ $rekapHarian['warga_count'] }}</div>
                    </div>
                    <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid: Area Scanner & Form Setoran --}}
    <div class="row g-4 mb-4">
        {{-- Sisi Kiri: Scanner RFID Radar & Info Nasabah --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-4" style="background: linear-gradient(180deg, #f8fbf9 0%, #edf7f0 100%); border: 2px dashed #10b981 !important;">
                <div class="text-center py-2">
                    <div class="mb-3 position-relative d-inline-block">
                        <div id="scannerIconWrapper" class="rounded-circle d-flex align-items-center justify-content-center text-emerald mx-auto pulse-ring" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.15);">
                            <i class="fas fa-satellite-dish fa-2x" id="scannerIcon"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" id="scannerTitle">Menunggu Scan Kartu Warga...</h5>
                    <p class="small text-muted mb-3" id="scannerSubtitle">Silakan tempelkan kartu E-KTP atau RFID warga pada perangkat sensor Bank Sampah</p>
                    
                    {{-- Alert Peringatan RT jika warga beda RT --}}
                    <div id="alertBedaRt" class="alert alert-warning py-2 px-3 small d-none text-start rounded-3 mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i> <span id="alertBedaRtText"></span>
                    </div>

                    {{-- Info Nasabah Terdeteksi --}}
                    <div id="boxWargaTerdeteksi" class="card bg-white border-0 rounded-3 p-3 shadow-sm text-start d-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-emerald bg-opacity-15 text-emerald d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width: 52px; height: 52px;" id="nasabahAvatar">
                                W
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="fw-bold mb-0 text-dark text-truncate" id="nasabahNama">-</h6>
                                <div class="small text-muted font-monospace" id="nasabahNik">NIK: -</div>
                                <div class="small text-secondary">
                                    Wilayah: <strong class="text-dark" id="nasabahRt">RT -</strong>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2.5">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Saldo Bank Sampah:</small>
                                <div class="fs-5 fw-bold text-emerald" id="nasabahSaldo">Rp 0</div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Total Terkumpul:</small>
                                <span class="badge bg-light text-dark border font-monospace" id="nasabahTotalBerat">0.0 kg</span>
                            </div>
                        </div>

                        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                            <span>UID Kartu:</span>
                            <span class="font-monospace fw-bold text-dark" id="nasabahUid">-</span>
                        </div>
                    </div>

                    {{-- Tombol Reset / Scan Ulang --}}
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetScanner">
                            <i class="fas fa-rotate me-1"></i> Reset / Tunggu Kartu Lain
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Form Penimbangan & Setoran --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-cart-flatbed-suitcase text-success me-2"></i>Formulir Penimbangan Sampah
                    </h5>
                    <span class="badge bg-light text-secondary border font-monospace">Validasi Instan</span>
                </div>

                <form method="POST" action="{{ route('bank-sampah.setoran-rfid.store') }}" id="formSetoranSampah">
                    @csrf
                    
                    {{-- Input Hidden User ID (diisi otomatis oleh scan RFID atau dipilih manual) --}}
                    <input type="hidden" name="user_id" id="hiddenUserId" value="{{ old('user_id') }}" required>

                    {{-- Fallback Pilihan Warga Manual jika tidak memakai RFID --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-secondary mb-0">Nasabah Warga <span class="text-danger">*</span></label>
                            <small><a href="javascript:void(0)" id="toggleManualWarga" class="text-decoration-none">Pilih Warga Manual</a></small>
                        </div>
                        <div id="manualWargaWrapper" class="d-none">
                            <select id="selectManualWarga" class="form-select">
                                <option value="">-- Pilih Warga dari Database --</option>
                                @foreach($wargas as $w)
                                    <option value="{{ $w->id }}" data-nama="{{ $w->name }}" data-nik="{{ $w->profile?->nik }}" data-rt="{{ optional($w->profile?->wilayahRt)->kode_rt ?? $w->profile?->rt_id }}">
                                        {{ $w->name }} (NIK: {{ $w->profile?->nik ?? '-' }}) - RT {{ optional($w->profile?->wilayahRt)->kode_rt ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="activeWargaLabel" class="form-control bg-light border-0 text-muted fst-italic">
                            Menunggu kartu warga ditempelkan pada scanner...
                        </div>
                    </div>

                    {{-- Pilihan Jenis Sampah --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            Kategori / Jenis Sampah <span class="text-danger">*</span>
                        </label>
                        <select name="jenis_sampah_id" id="selectJenisSampah" class="form-select" required>
                            <option value="">-- Pilih Jenis Sampah --</option>
                            @foreach($jenisSampahs as $js)
                                <option value="{{ $js->id }}" data-harga="{{ $js->harga_per_kg }}" {{ old('jenis_sampah_id') == $js->id ? 'selected' : '' }}>
                                    {{ $js->nama }} (Rp {{ number_format($js->harga_per_kg, 0, ',', '.') }} / kg) - {{ $js->kategori ?: 'Umum' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input Berat Sampah (kg) --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-secondary">
                                Berat Timbangan (kg) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" name="berat_kg" id="inputBeratKg" class="form-control fs-5 fw-bold text-dark font-monospace" placeholder="0.00" value="{{ old('berat_kg') }}" required>
                                <span class="input-group-text bg-light fw-bold">kg</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-secondary">
                                Tanggal Setoran
                            </label>
                            <input type="date" name="tanggal_setor" class="form-control" value="{{ old('tanggal_setor', now()->toDateString()) }}">
                        </div>
                    </div>

                    {{-- Kalkulasi Nilai Tambah Saldo --}}
                    <div class="card bg-light border-0 rounded-3 p-3 mb-3" style="background: rgba(16, 185, 129, 0.08) !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-medium d-block">Estimasi Nilai Saldo yang Didapat:</small>
                                <div class="fs-4 fw-bold text-success" id="displayTotalSaldo">Rp 0</div>
                            </div>
                            <div class="text-end small text-muted font-monospace" id="calcFormula">
                                0 kg &times; Rp 0
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Opsional --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Catatan Petugas (Opsional)</label>
                        <input type="text" name="catatan" class="form-control" placeholder="Contoh: Plastik bersih, kardus kering..." value="{{ old('catatan') }}">
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-emerald text-white py-2.5 fw-bold shadow-sm" id="btnSubmitSetoran" disabled>
                            <i class="fas fa-plus-circle me-1.5"></i> Simpan Setoran & Tambah Saldo Warga
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Riwayat Setoran & Filter Per Warga --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-list text-primary me-2"></i>Riwayat Transaksi Setoran Sampah
                </h5>
                <small class="text-muted">Daftar setoran terbaru yang telah dicatat dan diverifikasi oleh petugas</small>
            </div>
            
            {{-- Filter Per Warga --}}
            <form method="GET" action="{{ route('bank-sampah.setoran-rfid') }}" class="d-flex gap-2">
                @if(!$isRt && count($rts) > 0)
                    <select name="rt_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua RT</option>
                        @foreach($rts as $r)
                            <option value="{{ $r->id }}" {{ (string)$selectedRtId === (string)$r->id ? 'selected' : '' }}>RT {{ $r->kode_rt }}</option>
                        @endforeach
                    </select>
                @endif
                <select name="warga_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Warga</option>
                    @foreach($wargas as $w)
                        <option value="{{ $w->id }}" {{ (string)request('warga_id') === (string)$w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
                @if(request()->hasAny(['warga_id', 'rt_id']))
                    <a href="{{ route('bank-sampah.setoran-rfid') }}" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="fas fa-redo"></i></a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase fw-semibold">
                    <tr>
                        <th class="ps-4">Waktu / Tanggal</th>
                        <th>Nasabah Warga</th>
                        <th>Jenis Sampah</th>
                        <th>Berat (kg)</th>
                        <th>Harga / kg</th>
                        <th>Total Saldo</th>
                        <th>Status</th>
                        <th class="pe-4">Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatSetoran as $setoran)
                        <tr>
                            <td class="ps-4 small text-nowrap">
                                <div class="fw-semibold text-dark">
                                    {{ $setoran->tanggal_setor ? \Carbon\Carbon::parse($setoran->tanggal_setor)->format('d/m/Y') : $setoran->created_at->format('d/m/Y') }}
                                </div>
                                <small class="text-muted">{{ $setoran->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $setoran->user?->name ?? 'Warga' }}</div>
                                @php
                                    $wRt = optional($setoran->user?->profile?->wilayahRt)->kode_rt ?? $setoran->user?->profile?->rt_id;
                                @endphp
                                @if($wRt)
                                    <span class="badge bg-light text-secondary border">RT {{ $wRt }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                    {{ $setoran->jenisSampah?->nama ?? 'Sampah' }}
                                </span>
                            </td>
                            <td class="fw-bold text-dark font-monospace">
                                {{ number_format($setoran->berat_kg, 2, ',', '.') }} kg
                            </td>
                            <td class="small text-muted font-monospace">
                                Rp {{ number_format($setoran->harga_per_kg, 0, ',', '.') }}
                            </td>
                            <td class="fw-bold text-emerald font-monospace">
                                + Rp {{ number_format($setoran->total_saldo, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1">
                                    <i class="fas fa-check-circle me-1"></i> Valid
                                </span>
                            </td>
                            <td class="pe-4 small text-secondary">
                                {{ $setoran->validator?->name ?? 'Petugas RT' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-recycle fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                Belum ada riwayat setoran sampah yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.pulse-ring {
    animation: pulseRing 1.6s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
@keyframes pulseRing {
    0% { transform: scale(0.96); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    70% { transform: scale(1.04); box-shadow: 0 0 0 14px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.96); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>

<script>
(function() {
    let rfidPollTimer = null;
    let listenStartTime = Math.floor(Date.now() / 1000) - 3;
    let lastDetectedUid = null;

    function playBeep() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(650, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.12);

            gain.gain.setValueAtTime(0.25, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.14);

            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch (e) {}
    }

    function calculateTotalSaldo() {
        const beratInput = document.getElementById('inputBeratKg');
        const jenisSelect = document.getElementById('selectJenisSampah');
        const displayTotal = document.getElementById('displayTotalSaldo');
        const formulaDisplay = document.getElementById('calcFormula');
        const submitBtn = document.getElementById('btnSubmitSetoran');
        const userId = document.getElementById('hiddenUserId').value;

        const berat = parseFloat(beratInput.value) || 0;
        const selectedOpt = jenisSelect.options[jenisSelect.selectedIndex];
        const harga = selectedOpt ? (parseFloat(selectedOpt.dataset.harga) || 0) : 0;

        const total = berat * harga;
        displayTotal.textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
        formulaDisplay.textContent = berat.toFixed(2) + ' kg \u00D7 Rp ' + harga.toLocaleString('id-ID');

        if (userId && berat > 0 && harga > 0) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    function onWargaDetected(wargaData) {
        if (!wargaData || !wargaData.user_id) return;

        if (lastDetectedUid !== wargaData.uid) {
            playBeep();
            lastDetectedUid = wargaData.uid;
        }

        document.getElementById('hiddenUserId').value = wargaData.user_id;
        document.getElementById('nasabahNama').textContent = wargaData.nama_warga || 'Warga';
        document.getElementById('nasabahNik').textContent = 'NIK: ' + (wargaData.nik || '-');
        document.getElementById('nasabahRt').textContent = 'RT ' + (wargaData.rt_kode || wargaData.rt_id || '-');
        document.getElementById('nasabahSaldo').textContent = 'Rp ' + Math.round(wargaData.saldo || 0).toLocaleString('id-ID');
        document.getElementById('nasabahTotalBerat').textContent = (wargaData.total_berat_kg || 0).toFixed(1) + ' kg';
        document.getElementById('nasabahUid').textContent = wargaData.formatted_uid || wargaData.uid;
        document.getElementById('nasabahAvatar').textContent = (wargaData.nama_warga || 'W').charAt(0).toUpperCase();

        document.getElementById('scannerIconWrapper').className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-emerald mx-auto';
        document.getElementById('scannerIcon').className = 'fas fa-check fa-2x text-white';
        document.getElementById('scannerTitle').textContent = 'Kartu Warga Terdeteksi';
        document.getElementById('scannerSubtitle').textContent = wargaData.nama_warga + ' (RT ' + (wargaData.rt_kode || '-') + ')';

        document.getElementById('boxWargaTerdeteksi').classList.remove('d-none');
        document.getElementById('activeWargaLabel').innerHTML = '<strong class="text-dark">' + wargaData.nama_warga + '</strong> (NIK: ' + (wargaData.nik || '-') + ', RT ' + (wargaData.rt_kode || '-') + ')';

        if (wargaData.warning) {
            document.getElementById('alertBedaRtText').textContent = wargaData.warning;
            document.getElementById('alertBedaRt').classList.remove('d-none');
        } else {
            document.getElementById('alertBedaRt').classList.add('d-none');
        }

        calculateTotalSaldo();
    }

    function resetScanner() {
        document.getElementById('hiddenUserId').value = '';
        document.getElementById('boxWargaTerdeteksi').classList.add('d-none');
        document.getElementById('alertBedaRt').classList.add('d-none');
        document.getElementById('scannerIconWrapper').className = 'rounded-circle d-flex align-items-center justify-content-center text-emerald mx-auto pulse-ring';
        document.getElementById('scannerIcon').className = 'fas fa-satellite-dish fa-2x text-emerald';
        document.getElementById('scannerTitle').textContent = 'Menunggu Scan Kartu Warga...';
        document.getElementById('scannerSubtitle').textContent = 'Silakan tempelkan kartu E-KTP atau RFID warga pada perangkat sensor Bank Sampah';
        document.getElementById('activeWargaLabel').textContent = 'Menunggu kartu warga ditempelkan pada scanner...';
        document.getElementById('activeWargaLabel').className = 'form-control bg-light border-0 text-muted fst-italic';
        lastDetectedUid = null;
        listenStartTime = Math.floor(Date.now() / 1000) - 2;
        calculateTotalSaldo();
    }

    function pollRfidScan() {
        fetch(`{{ route('bank-sampah.rfid.latest') }}?since=${listenStartTime}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(payload => {
            if (payload && payload.status === 'detected' && payload.data) {
                onWargaDetected(payload.data);
            }
        })
        .catch(err => {
            console.log('Polling RFID Bank Sampah error:', err);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('inputBeratKg').addEventListener('input', calculateTotalSaldo);
        document.getElementById('selectJenisSampah').addEventListener('change', calculateTotalSaldo);

        document.getElementById('btnResetScanner').addEventListener('click', resetScanner);

        // Toggle manual selection
        const toggleBtn = document.getElementById('toggleManualWarga');
        const manualWrapper = document.getElementById('manualWargaWrapper');
        const activeLabel = document.getElementById('activeWargaLabel');
        const selectWarga = document.getElementById('selectManualWarga');

        toggleBtn.addEventListener('click', function() {
            if (manualWrapper.classList.contains('d-none')) {
                manualWrapper.classList.remove('d-none');
                activeLabel.classList.add('d-none');
                toggleBtn.textContent = 'Batal Pilih Manual';
            } else {
                manualWrapper.classList.add('d-none');
                activeLabel.classList.remove('d-none');
                toggleBtn.textContent = 'Pilih Warga Manual';
            }
        });

        selectWarga.addEventListener('change', function() {
            const opt = selectWarga.options[selectWarga.selectedIndex];
            if (opt && opt.value) {
                onWargaDetected({
                    user_id: opt.value,
                    nama_warga: opt.dataset.nama,
                    nik: opt.dataset.nik,
                    rt_kode: opt.dataset.rt,
                    saldo: 0,
                    total_berat_kg: 0,
                    uid: 'MANUAL',
                    formatted_uid: 'INPUT MANUAL'
                });
            }
        });

        // Start polling
        rfidPollTimer = setInterval(pollRfidScan, 1500);
        pollRfidScan();
    });
})();
</script>
@endsection
