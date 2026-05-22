<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PembayaranExport implements FromCollection, WithHeadings
{
    protected $pembayaran;
    protected $user;
    protected $periodeAwal;
    protected $periodeAkhir;
    
    public function __construct($pembayaran, $user, $periodeAwal, $periodeAkhir)
    {
        $this->pembayaran = $pembayaran;
        $this->user = $user;
        $this->periodeAwal = $periodeAwal;
        $this->periodeAkhir = $periodeAkhir;
    }
    
    public function collection()
    {
        $data = collect();
        $data->push(['LAPORAN PEMBAYARAN WARGA']);
        $data->push(['Nama Warga', $this->user->name]);
        $data->push(['Periode', $this->periodeAwal . ' s/d ' . $this->periodeAkhir]);
        $data->push(['']);
        $data->push(['No', 'Tanggal Bayar', 'Jenis Iuran', 'Jumlah Bayar', 'Status']);
        
        $no = 1;
        foreach ($this->pembayaran as $item) {
            $data->push([
                $no++,
                date('d/m/Y', strtotime($item->tanggal_bayar)),
                $item->iuran->nama_iuran,
                'Rp ' . number_format($item->jumlah_bayar, 0, ',', '.'),
                $item->status,
            ]);
        }
        
        return $data;
    }
    
    public function headings(): array
    {
        return [];
    }
}