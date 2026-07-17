<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanExport implements FromCollection, WithHeadings, WithStyles
{
    protected $pembayaran;
    protected $periodeAwal;
    protected $periodeAkhir;
    protected $jenisLaporan;
    
    public function __construct($pembayaran, $periodeAwal, $periodeAkhir, $jenisLaporan)
    {
        $this->pembayaran = $pembayaran;
        $this->periodeAwal = $periodeAwal;
        $this->periodeAkhir = $periodeAkhir;
        $this->jenisLaporan = $jenisLaporan;
    }
    
    public function collection()
    {
        $data = collect();
        
        // Header info
        $data->push(['LAPORAN IURAN RW', '', '', '', '']);
        $data->push(['Jenis Laporan', $this->jenisLaporan, '', '', '']);
        $data->push(['Periode', $this->periodeAwal . ' s/d ' . $this->periodeAkhir, '', '', '']);
        $data->push(['', '', '', '', '']);
        $data->push(['No', 'Tanggal Bayar', 'Nama Warga', 'Jenis Iuran', 'Jumlah Bayar']);
        
        $no = 1;
        $total = 0;
        
        foreach ($this->pembayaran as $item) {
            $data->push([
                $no++,
                date('d/m/Y', strtotime($item->tanggal_bayar)),
                $item->user->name,
                $item->iuran->nama_iuran,
                'Rp ' . number_format($item->jumlah_bayar, 0, ',', '.'),
            ]);
            $total += $item->jumlah_bayar;
        }
        
        $data->push(['', '', '', 'TOTAL', 'Rp ' . number_format($total, 0, ',', '.')]);
        
        return $data;
    }
    
    public function headings(): array
    {
        return [];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true]],
        ];
    }
}
