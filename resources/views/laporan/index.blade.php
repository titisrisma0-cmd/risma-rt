@extends('layouts.app')

@section('title', 'Laporan ' . (Auth::user()->isAdminRw() ? 'Wilayah RW' : 'RT'))

@section('content')
<div class="content-container">
    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">
                <i class="fas fa-file-invoice text-emerald me-2"></i>
                {{ Auth::user()->isAdminRw() ? 'Laporan Agregat Wilayah RW' : 'Laporan Operasional RT' }}
            </h1>
            <p class="page-subtitle">
                {{ Auth::user()->isAdminRw() 
                    ? 'Pusat ekspor laporan agregat kependudukan, keuangan, kegiatan, dan audit seluruh RT' 
                    : 'Generate laporan penerimaan iuran, kas, dan operasional warga di lingkungan RT' }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Form Generate Laporan Agregat --}}
        <div class="{{ Auth::user()->isAdminRt() ? 'col-lg-7' : 'col-lg-8' }}">
            <div class="saas-card overflow-hidden">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar text-emerald me-2"></i>
                        {{ Auth::user()->isAdminRw() ? 'Generate Laporan Agregat Wilayah' : 'Generate Laporan RT' }}
                    </h6>
                </div>
                <div class="p-4">
                    <form action="{{ route('laporan.generate') }}" method="POST">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Jenis Laporan <span class="text-danger">*</span></label>
                                <select name="jenis_laporan" class="form-select form-control-saas" required>
                                    @if(Auth::user()->isAdminRw())
                                        <option value="keuangan_gabungan">Laporan Keuangan Gabungan RW</option>
                                        <option value="iuran_per_rt">Laporan Iuran per RT</option>
                                        <option value="pengeluaran_per_rt">Laporan Pengeluaran per RT</option>
                                        <option value="kas_per_rt">Laporan Saldo Kas per RT</option>
                                        <option value="kk_per_rt">Laporan Jumlah KK per RT</option>
                                        <option value="warga_per_rt">Laporan Jumlah Warga per RT</option>
                                        <option value="kegiatan_wilayah">Laporan Kegiatan RT dan RW</option>
                                        <option value="layanan_per_rt">Laporan Pelayanan Masyarakat per RT</option>
                                        <option value="audit_keuangan">Laporan Hasil Audit Keuangan</option>
                                    @else
                                        <option value="bulanan">Laporan Bulanan RT</option>
                                        <option value="tahunan">Laporan Tahunan RT</option>
                                        <option value="per_iuran">Laporan Per Jenis Iuran</option>
                                    @endif
                                </select>
                            </div>

                            @if(Auth::user()->isAdminRw())
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-medium">Filter Wilayah RT</label>
                                    <select name="rt_id" class="form-select form-control-saas">
                                        <option value="">Semua RT (RT 01 – RT 08)</option>
                                        @foreach($rts as $r)
                                            <option value="{{ $r->id }}">RT {{ $r->kode_rt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Periode Awal <span class="text-danger">*</span></label>
                                <input type="date" name="periode_awal" value="{{ date('Y-m-01') }}" class="form-control form-control-saas" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-medium">Periode Akhir <span class="text-danger">*</span></label>
                                <input type="date" name="periode_akhir" value="{{ date('Y-m-d') }}" class="form-control form-control-saas" required>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 border-top pt-3">
                            <button type="submit" name="export_excel" value="1" class="btn btn-emerald text-white px-4">
                                <i class="fas fa-file-excel me-1"></i> Export ke Excel
                            </button>
                            <button type="submit" name="export_pdf" value="1" class="btn btn-danger px-4">
                                <i class="fas fa-file-pdf me-1"></i> Export ke PDF
                            </button>
                            <button type="submit" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-save me-1"></i> Simpan Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Form Laporan Per Warga (HANYA DITAMPILKAN UNTUK KETUA RT) --}}
        @if(Auth::user()->isAdminRt())
            <div class="col-lg-5">
                <div class="saas-card overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user text-emerald me-2"></i>Laporan Per Warga (RT)</h6>
                    </div>
                    <div class="p-4">
                        <form action="{{ route('laporan.per-warga') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-medium">Pilih Warga <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select form-control-saas" required>
                                    <option value="">-- Pilih Warga --</option>
                                    @foreach($warga as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->profile->no_hp ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label text-secondary small fw-medium">Periode Awal</label>
                                    <input type="date" name="periode_awal" value="{{ date('Y-01-01') }}" class="form-control form-control-saas" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-secondary small fw-medium">Periode Akhir</label>
                                    <input type="date" name="periode_akhir" value="{{ date('Y-m-d') }}" class="form-control form-control-saas" required>
                                </div>
                            </div>
                            <button type="submit" name="export_excel" value="1" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-file-excel me-1 text-emerald"></i> Export Laporan Warga
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            {{-- Info Box untuk Pengurus RW --}}
            <div class="col-lg-4">
                <div class="saas-card h-100 p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-emerald me-2"></i>Panduan Laporan RW</h6>
                    <div class="text-secondary small">
                        <p class="mb-2">Sesuai tata kelola wilayah dan RBAC RW Smart:</p>
                        <ul class="ps-3 mb-3" style="line-height: 1.8;">
                            <li>Pengurus RW berfokus pada <strong>Laporan Agregat Wilayah</strong> RT 01 – RT 08.</li>
                            <li>Laporan individu per warga dikelola langsung oleh masing-masing Ketua RT.</li>
                            <li>Ekspor dapat dilakukan ke dalam format dokumen PDF atau spreadsheet Excel.</li>
                        </ul>
                        <div class="alert alert-info py-2 px-3 small mb-0">
                            <i class="fas fa-check-double me-1"></i> Untuk audit keuangan mendalam per RT, silakan gunakan modul <strong>Keuangan dan Audit RT</strong>.
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Riwayat Laporan Tersimpan --}}
    <div class="saas-card overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-emerald me-2"></i>Riwayat Laporan Digenerate</h6>
            <span class="badge-soft-secondary">{{ $laporan->count() }} Dokumen</span>
        </div>
        <div class="table-responsive">
            <table class="table-saas">
                <thead>
                    <tr>
                        <th>Kode Laporan</th>
                        <th>Jenis Laporan</th>
                        <th>Periode</th>
                        <th class="text-end">Pemasukan</th>
                        <th class="text-end">Pengeluaran</th>
                        <th class="text-end">Saldo Akhir</th>
                        <th class="text-center">Dibuat Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $item)
                        <tr>
                            <td class="font-monospace fw-bold text-emerald small">{{ $item->kode_laporan }}</td>
                            <td>
                                <span class="badge-soft-info">
                                    {{ ucwords(str_replace('_', ' ', $item->jenis_laporan)) }}
                                </span>
                            </td>
                            <td class="text-secondary small">
                                {{ date('d/m/Y', strtotime($item->periode_awal)) }} – {{ date('d/m/Y', strtotime($item->periode_akhir)) }}
                            </td>
                            <td class="text-end font-monospace text-emerald fw-semibold">Rp {{ number_format($item->total_pemasukan, 0, ',', '.') }}</td>
                            <td class="text-end font-monospace text-danger fw-semibold">Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</td>
                            <td class="text-end font-monospace text-dark fw-bold">Rp {{ number_format($item->saldo_akhir, 0, ',', '.') }}</td>
                            <td class="text-center small text-secondary">{{ optional($item->creator)->name ?? '-' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('laporan.show', $item->id) }}" class="btn btn-sm btn-outline-secondary me-1" title="Lihat">
                                    <i class="fas fa-eye text-emerald"></i>
                                </a>
                                <form action="{{ route('laporan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus riwayat laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <i class="fas fa-file-invoice fa-3x mb-3 text-muted d-block opacity-50"></i>
                                Belum ada riwayat laporan yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
