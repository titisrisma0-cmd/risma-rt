<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    protected $fillable = [
        'kode_iuran', 'nama_iuran', 'jenis_iuran',
        'nominal', 'periode', 'keterangan', 'status'
    ];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }
}