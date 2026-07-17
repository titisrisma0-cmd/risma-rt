<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPosyandu extends Model
{
    protected $fillable = ['rt_id', 'judul', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'keterangan', 'created_by'];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanBalita::class);
    }
}
