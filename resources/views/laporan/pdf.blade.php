<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Iuran RW</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #667eea;
            margin-bottom: 5px;
        }
        .info {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RW Smart</h1>
        <p>Sistem Informasi Iuran RW</p>
        <h3>Laporan {{ ucfirst($jenis_laporan) }}</h3>
        <p>Periode: {{ date('d/m/Y', strtotime($periode_awal)) }} - {{ date('d/m/Y', strtotime($periode_akhir)) }}</p>
    </div>

    <div class="info">
        <strong>Ringkasan:</strong><br>
        Total Pemasukan: Rp {{ number_format($total_pemasukan, 0, ',', '.') }}<br>
        Total Pengeluaran: Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}<br>
        Saldo Akhir: Rp {{ number_format($saldo_akhir, 0, ',', '.') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Bayar</th>
                <th>Nama Warga</th>
                <th>Jenis Iuran</th>
                <th>Jumlah Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembayaran as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y', strtotime($item->tanggal_bayar)) }}</td>
                <td>{{ $item->user->name }}</td>
                <td>{{ $item->iuran->nama_iuran }}</td>
                <td style="text-align: right">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align: right">TOTAL:</th>
                <th style="text-align: right">Rp {{ number_format($pembayaran->sum('jumlah_bayar'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="total">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem RW Smart</p>
    </div>
</body>
</html>
