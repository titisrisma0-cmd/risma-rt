@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-file-alt"></i> Laporan Keuangan
            </h2>
            <p class="text-white-50 animate__animated animate__fadeInLeft animate__delay-1s">
                Generate laporan pemasukan dan pengeluaran dalam berbagai format
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Form Generate Laporan -->
        <div class="col-md-6 mb-4">
            <div class="card animate__animated animate__fadeInLeft">
                <div class="card-header bg-primary">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-bar"></i> Generate Laporan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('laporan.generate') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-white">Jenis Laporan <span class="text-danger">*</span></label>
                            <select name="jenis_laporan" class="form-select bg-dark text-white border-secondary" required>
                                <option value="" class="bg-dark text-white">-- Pilih Jenis Laporan --</option>
                                <option value="bulanan" class="bg-dark text-white">Laporan Bulanan</option>
                                <option value="tahunan" class="bg-dark text-white">Laporan Tahunan</option>
                                <option value="per_iuran" class="bg-dark text-white">Laporan Per Iuran</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode Awal <span class="text-danger">*</span></label>
                                <input type="date" name="periode_awal" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode Akhir <span class="text-danger">*</span></label>
                                <input type="date" name="periode_akhir" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="export_excel" value="1" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export ke Excel
                            </button>
                            <button type="submit" name="export_pdf" value="1" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export ke PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form Laporan Per Warga -->
        <div class="col-md-6 mb-4">
            <div class="card animate__animated animate__fadeInRight">
                <div class="card-header bg-info">
                    <h5 class="mb-0 text-white"><i class="fas fa-user"></i> Laporan Per Warga</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('laporan.per-warga') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-white">Pilih Warga <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select bg-dark text-white border-secondary" required>
                                <option value="" class="bg-dark text-white">-- Pilih Warga --</option>
                                @foreach($warga as $item)
                                    <option value="{{ $item->id }}" class="bg-dark text-white">{{ $item->name }} - {{ $item->profile->no_hp ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode Awal <span class="text-danger">*</span></label>
                                <input type="date" name="periode_awal" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Periode Akhir <span class="text-danger">*</span></label>
                                <input type="date" name="periode_akhir" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="export_excel" value="1" class="btn btn-gradient">
                                <i class="fas fa-file-excel"></i> Export Laporan Warga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Laporan -->
    <div class="card animate__animated animate__fadeInUp">
        <div class="card-header bg-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-history"></i> Riwayat Laporan</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kode Laporan</th>
                            <th>Jenis</th>
                            <th>Periode</th>
                            <th>Total Pemasukan</th>
                            <th>Total Pengeluaran</th>
                            <th>Saldo Akhir</th>
                            <th>Tanggal Buat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $item)
                        <tr>
                            <td><span class="text-white">{{ $item->kode_laporan }}</span></td>
                            <td>
                                <span class="badge bg-primary">{{ ucfirst($item->jenis_laporan) }}</span>
                            </td>
                            <td><span class="text-white">{{ date('d/m/Y', strtotime($item->periode_awal)) }} - {{ date('d/m/Y', strtotime($item->periode_akhir)) }}</span></td>
                            <td class="text-success">Rp {{ number_format($item->total_pemasukan, 0, ',', '.') }}</td>
                            <td class="text-danger">Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</td>
                            <td class="text-primary">Rp {{ number_format($item->saldo_akhir, 0, ',', '.') }}</td>
                            <td><span class="text-white">{{ $item->created_at->format('d/m/Y H:i') }}</span></td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('laporan.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="confirmDelete('{{ route('laporan.destroy', $item->id) }}', 'laporan')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                             </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-secondary mb-2 d-block"></i>
                                <span class="text-secondary">Belum ada riwayat laporan</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(96, 165, 250, 0.1);
}

.card-header {
    background: rgba(15, 23, 42, 0.5);
    border-bottom: 1px solid rgba(96, 165, 250, 0.1);
}

.form-label {
    font-weight: 500;
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.btn-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border: none;
    color: white;
    transition: all 0.3s;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
    color: white;
}

.table-dark {
    background: transparent;
}

.table-dark th {
    border-bottom-color: rgba(96, 165, 250, 0.2);
    color: #94a3b8;
    font-weight: 500;
}

.table-dark td {
    border-bottom-color: rgba(96, 165, 250, 0.05);
    color: #f1f5f9;
}

.text-white {
    color: #f1f5f9 !important;
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}
</style>
@endsection