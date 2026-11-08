@extends('layouts.app')

@section('title', 'Manajemen Permohonan RT')

@section('content')
<div class="content-container permohonan-page">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-file-signature text-emerald me-2"></i>Manajemen Permohonan — RT {{ $user->getRtKode() ?? '01' }}
            </h1>
            <p class="page-subtitle">Verifikasi langsung tingkat RT, eskalasi permohonan ke RW, dan arsip riwayat persetujuan warga.</p>
        </div>

        <form method="GET" action="{{ route('rt.verifikasi.permohonan') }}" class="d-flex align-items-center gap-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="q" value="{{ $search }}" class="form-control form-control-saas border-start-0" placeholder="Cari nama, NIK, no pengajuan...">
            </div>
            @if($search)
                <a href="{{ route('rt.verifikasi.permohonan') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 7 TABS + RIWAYAT ACCORDION --}}
    <ul class="nav nav-tabs-saas mb-4" id="permohonanTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'baru' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-baru" type="button">
                Permohonan Baru
                @if($permohonanBaru->count() > 0)
                    <span class="badge-soft-warning ms-1">{{ $permohonanBaru->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'menunggu_rt' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-menunggu-rt" type="button">
                Menunggu Verifikasi RT
                @if($menungguRt->count() > 0)
                    <span class="badge-soft-danger ms-1">{{ $menungguRt->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'proses' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-proses" type="button">
                Sedang Diproses
                @if($sedangDiproses->count() > 0)
                    <span class="badge-soft-info ms-1">{{ $sedangDiproses->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'diteruskan' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-diteruskan" type="button">
                Diteruskan ke RW
                @if($diteruskanRw->count() > 0)
                    <span class="badge bg-secondary ms-1">{{ $diteruskanRw->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'disetujui' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-disetujui" type="button">
                Disetujui
                @if($disetujui->count() > 0)
                    <span class="badge-soft-emerald ms-1">{{ $disetujui->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'ditolak' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-ditolak" type="button">
                Ditolak
                @if($ditolak->count() > 0)
                    <span class="badge-soft-danger ms-1">{{ $ditolak->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'selesai' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-selesai" type="button">
                Selesai
                @if($selesai->count() > 0)
                    <span class="badge-soft-emerald ms-1">{{ $selesai->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'riwayat' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-riwayat" type="button">
                <i class="fas fa-history me-1"></i>Riwayat Persetujuan
            </button>
        </li>
    </ul>

    <div class="tab-content" id="permohonanTabContent">
        {{-- TAB 1: PERMOHONAN BARU --}}
        <div class="tab-pane fade {{ $activeTab === 'baru' ? 'show active' : '' }}" id="tab-baru">
            @include('rt.verifikasi.partials.table', ['items' => $permohonanBaru, 'title' => 'Permohonan Baru Masuk', 'badgeColor' => 'warning'])
        </div>

        {{-- TAB 2: MENUNGGU VERIFIKASI RT --}}
        <div class="tab-pane fade {{ $activeTab === 'menunggu_rt' ? 'show active' : '' }}" id="tab-menunggu-rt">
            @include('rt.verifikasi.partials.table', ['items' => $menungguRt, 'title' => 'Permohonan Menunggu Verifikasi RT', 'badgeColor' => 'danger'])
        </div>

        {{-- TAB 3: SEDANG DIPROSES --}}
        <div class="tab-pane fade {{ $activeTab === 'proses' ? 'show active' : '' }}" id="tab-proses">
            @include('rt.verifikasi.partials.table', ['items' => $sedangDiproses, 'title' => 'Permohonan Sedang Diproses', 'badgeColor' => 'info'])
        </div>

        {{-- TAB 4: DITERUSKAN KE RW --}}
        <div class="tab-pane fade {{ $activeTab === 'diteruskan' ? 'show active' : '' }}" id="tab-diteruskan">
            <div class="saas-card overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-paper-plane text-emerald me-2"></i>Permohonan yang Diteruskan ke RW</h6>
                    <span class="badge-soft-info">{{ $diteruskanRw->count() }} Pengajuan</span>
                </div>
                <div class="table-responsive">
                    <table class="table-saas">
                        <thead>
                            <tr>
                                <th>No. Pengajuan</th>
                                <th>Pemohon</th>
                                <th>Jenis Surat</th>
                                <th>Catatan Pengantar RT</th>
                                <th>Status di RW</th>
                                <th>Keputusan RW</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($diteruskanRw as $p)
                                <tr>
                                    <td class="font-monospace fw-bold text-emerald">{{ $p->nomor_pengajuan }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $p->user->name ?? '-' }}</div>
                                        <small class="text-secondary">NIK: {{ $p->user->profile->nik ?? '-' }}</small>
                                    </td>
                                    <td><span class="badge-soft-info">{{ $p->jenisSurat->nama ?? '-' }}</span></td>
                                    <td class="small text-secondary">{{ $p->catatan_rt ?: '-' }}</td>
                                    <td>
                                        <span class="badge-soft-warning">{{ $p->status_label }}</span>
                                    </td>
                                    <td>
                                        @if($p->rw_approved_at)
                                            <span class="text-emerald small fw-semibold"><i class="fas fa-check-circle me-1"></i>Disetujui RW ({{ $p->rwApprover->name ?? 'Admin RW' }})</span>
                                        @else
                                            <span class="text-secondary small"><i class="fas fa-clock me-1"></i>Menunggu Verifikasi RW</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-secondary">Belum ada permohonan yang diteruskan ke RW.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 5: DISETUJUI --}}
        <div class="tab-pane fade {{ $activeTab === 'disetujui' ? 'show active' : '' }}" id="tab-disetujui">
            @include('rt.verifikasi.partials.table', ['items' => $disetujui, 'title' => 'Permohonan yang Telah Disetujui', 'badgeColor' => 'emerald'])
        </div>

        {{-- TAB 6: DITOLAK --}}
        <div class="tab-pane fade {{ $activeTab === 'ditolak' ? 'show active' : '' }}" id="tab-ditolak">
            @include('rt.verifikasi.partials.table', ['items' => $ditolak, 'title' => 'Permohonan yang Ditolak', 'badgeColor' => 'danger'])
        </div>

        {{-- TAB 7: SELESAI --}}
        <div class="tab-pane fade {{ $activeTab === 'selesai' ? 'show active' : '' }}" id="tab-selesai">
            @include('rt.verifikasi.partials.table', ['items' => $selesai, 'title' => 'Permohonan Selesai Diproses', 'badgeColor' => 'emerald'])
        </div>

        {{-- TAB 8: RIWAYAT PERSETUJUAN (ACCORDION BULANAN) --}}
        <div class="tab-pane fade {{ $activeTab === 'riwayat' ? 'show active' : '' }}" id="tab-riwayat">
            <div class="saas-card overflow-hidden mb-3">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-check text-emerald me-2"></i>Riwayat Persetujuan Permohonan (Dikelompokkan per Bulan & Tahun)</h6>
                    <span class="badge-soft-secondary">{{ $riwayatGrouped->flatten()->count() }} Total Riwayat</span>
                </div>
                <div class="p-3">
                    <div class="accordion" id="accordionRiwayat">
                        @forelse($riwayatGrouped as $bulanTahun => $itemsBulan)
                            @php
                                $accId = 'collapse' . \Illuminate\Support\Str::slug($bulanTahun);
                            @endphp
                            <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="heading{{ $accId }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} py-3 px-3 fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accId }}">
                                        <i class="fas fa-calendar-alt text-emerald me-2"></i>{{ $bulanTahun }}
                                        <span class="badge-soft-emerald ms-2">{{ $itemsBulan->count() }} Permohonan</span>
                                    </button>
                                </h2>
                                <div id="{{ $accId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#accordionRiwayat">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table-saas mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>No. Surat / Pengajuan</th>
                                                        <th>Pemohon</th>
                                                        <th>Jenis Permohonan</th>
                                                        <th>Status Akhir</th>
                                                        <th>Keputusan Oleh</th>
                                                        <th class="text-end">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemsBulan as $rb)
                                                        <tr>
                                                            <td class="text-secondary small">{{ $rb->created_at->format('d/m/Y') }}</td>
                                                            <td>
                                                                <div class="font-monospace fw-bold text-emerald">{{ $rb->nomor_surat ?: $rb->nomor_pengajuan }}</div>
                                                            </td>
                                                            <td class="fw-medium text-dark">{{ $rb->user->name ?? '-' }}</td>
                                                            <td><span class="badge-soft-info">{{ $rb->jenisSurat->nama ?? '-' }}</span></td>
                                                            <td>
                                                                @if(in_array($rb->status, ['selesai', 'siap_diambil', 'disetujui']))
                                                                    <span class="badge-soft-emerald">{{ ucfirst(str_replace('_', ' ', $rb->status)) }}</span>
                                                                @else
                                                                    <span class="badge-soft-danger">{{ ucfirst(str_replace('_', ' ', $rb->status)) }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="small text-secondary">{{ $rb->approver->name ?? $rb->rwApprover->name ?? 'Pengurus RT/RW' }}</td>
                                                            <td class="text-end">
                                                                @if($rb->nomor_surat)
                                                                    <a href="{{ route('layanan.surat.cetak', $rb) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print me-1 text-emerald"></i>Cetak</a>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-secondary">Belum ada riwayat persetujuan permohonan surat.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
