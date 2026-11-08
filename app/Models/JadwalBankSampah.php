<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalBankSampah extends Model
{
    protected $fillable = [
        'rt_id',
        'judul',
        'waktu',
        'lokasi',
        'petugas_nama',
        'petugas_kontak',
        'keterangan',
        'aktif',
        'status',
    ];

    protected $casts = [
        'waktu' => 'datetime',
        'aktif' => 'boolean',
    ];

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }
}
