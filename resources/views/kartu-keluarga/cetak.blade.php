<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Keluarga - {{ $kartuKeluarga->no_kk }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; background: #fff; color: #000; font-size: 13px; }
        .border-double { border: 3px double #000; }
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; padding: 4px 6px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-4 d-flex justify-content-between align-items-center">
        <a href="{{ route('kartu-keluarga.show', $kartuKeluarga) }}" class="btn btn-secondary btn-sm">
            &larr; Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            Cetak Dokumen KK (Print / PDF)
        </button>
    </div>

    <div class="border-double p-4">
        {{-- Header Dokumen --}}
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-0 text-uppercase tracking-wider">KARTU KELUARGA</h4>
            <h5 class="fw-bold mb-1 font-monospace">No. {{ $kartuKeluarga->no_kk }}</h5>
            <div class="small">SISTEM INFORMASI ADMINISTRASI KEPENDUDUKAN LINGKUNGAN RW SMART</div>
        </div>

        {{-- Data Kepala & Alamat --}}
        <div class="row g-2 mb-3 small">
            <div class="col-7">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td width="160">Nama Kepala Keluarga</td><td width="10">:</td><td class="fw-bold text-uppercase">{{ $kartuKeluarga->nama_kepala_keluarga }}</td></tr>
                    <tr><td>Alamat</td><td>:</td><td>{{ $kartuKeluarga->alamat }}</td></tr>
                    <tr><td>RT / RW</td><td>:</td><td>RT {{ optional($kartuKeluarga->rt)->kode_rt ?? '-' }} / RW 01</td></tr>
                </table>
            </div>
            <div class="col-5">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td width="140">Status Hunian</td><td width="10">:</td><td class="text-capitalize">{{ $kartuKeluarga->status_rumah }}</td></tr>
                    <tr><td>Kelurahan / Desa</td><td>:</td><td>Setia Mekar</td></tr>
                    <tr><td>Kecamatan / Kota</td><td>:</td><td>Tambun Selatan / Bekasi</td></tr>
                </table>
            </div>
        </div>

        {{-- Tabel Anggota Keluarga --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered text-center align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="30">No</th>
                        <th>Nama Lengkap</th>
                        <th>NIK</th>
                        <th>Jenis Kelamin</th>
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                        <th>Agama</th>
                        <th>Pendidikan / Pekerjaan</th>
                        <th>Status Hubungan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kartuKeluarga->anggota as $idx => $anggota)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="text-start fw-bold text-uppercase">{{ $anggota->nama_lengkap }}</td>
                            <td class="font-monospace">{{ $anggota->nik }}</td>
                            <td>{{ $anggota->jenis_kelamin === 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' }}</td>
                            <td>{{ $anggota->tempat_lahir ?: '-' }}</td>
                            <td>{{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('d-m-Y') : '-' }}</td>
                            <td>{{ $anggota->agama ?: '-' }}</td>
                            <td>{{ $anggota->pekerjaan ?: '-' }}</td>
                            <td class="fw-semibold">{{ $anggota->status_hubungan_keluarga }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-3">Belum ada anggota keluarga terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Tanda Tangan --}}
        <div class="row text-center mt-4">
            <div class="col-4">
                <div>Mengetahui,</div>
                <div>Ketua RT {{ optional($kartuKeluarga->rt)->kode_rt }}</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold">({{ optional(optional($kartuKeluarga->rt)->ketua)->name ?? 'Ketua RT' }})</div>
            </div>
            <div class="col-4">
                <div>Pengurus RW 01</div>
                <div>Ketua RW</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold">(Pengurus RW)</div>
            </div>
            <div class="col-4">
                <div>Dikeluarkan pada: {{ now()->translatedFormat('d F Y') }}</div>
                <div>Kepala Keluarga</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold text-uppercase">({{ $kartuKeluarga->nama_kepala_keluarga }})</div>
            </div>
        </div>
    </div>
</body>
</html>
