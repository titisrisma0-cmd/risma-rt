<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $fillable = [
        'kode_laporan', 'jenis_laporan', 'periode_awal',
        'periode_akhir', 'total_pemasukan', 'total_pengeluaran',
        'saldo_akhir', 'file_path', 'created_by'
    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}